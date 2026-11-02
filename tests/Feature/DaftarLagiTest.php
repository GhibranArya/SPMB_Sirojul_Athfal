<?php

namespace Tests\Feature;

use App\Models\Gelombang;
use App\Models\Jenjang;
use App\Models\Pendaftaran;
use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Pendaftar yang Tidak Diterima boleh mendaftar lagi di gelombang aktif berikutnya memakai akun yang sama. */
class DaftarLagiTest extends TestCase
{
    use DatabaseTransactions;

    private Gelombang $lama;

    private Gelombang $aktif;

    protected function setUp(): void
    {
        parent::setUp();

        Gelombang::query()->update(['is_aktif' => false]);
        $this->lama = Gelombang::create([
            'nama' => 'Gelombang Lama Uji', 'tahun_ajaran' => '2026/2027', 'tgl_buka' => '2026-01-01', 'tgl_tutup' => '2026-01-31',
            'tgl_pengumuman' => '2026-02-10', 'is_aktif' => false,
        ]);
        $this->aktif = Gelombang::create([
            'nama' => 'Gelombang Baru Uji', 'tahun_ajaran' => '2026/2027', 'tgl_buka' => today()->subDay(), 'tgl_tutup' => today()->addDays(10),
            'tgl_pengumuman' => today()->addDays(20), 'is_aktif' => true,
        ]);
    }

    private function terbitkan(Gelombang $g): void
    {
        Pengumuman::create(['gelombang_id' => $g->id, 'judul' => 'Hasil uji', 'published_at' => now()]);
    }

    private function peserta(string $status, ?Gelombang $g = null, string $nik = '3277014511110001', string $email = 'lagi@uji.test'): User
    {
        $u = User::create(['name' => 'Wali Uji', 'email' => $email, 'phone' => '0818'.random_int(10000000, 99999999), 'password' => 'rahasia123']);
        $u->forceFill(['role' => 'pendaftar'])->save();

        Pendaftaran::create([
            'user_id' => $u->id, 'gelombang_id' => ($g ?? $this->lama)->id, 'jenjang_id' => Jenjang::where('kode', 'SMP')->value('id'),
            'kode' => Pendaftaran::buatKode(), 'status' => $status, 'nama_siswa' => 'Anak Uji', 'nik' => $nik,
            'jenis_kelamin' => 'L', 'tempat_lahir' => 'Bogor', 'tgl_lahir' => '2013-05-05', 'alamat_siswa' => 'Bogor',
            'nama_ayah' => 'Ayah', 'nama_ibu' => 'Ibu', 'hp_ortu' => '081200001111', 'setuju_data' => true, 'submitted_at' => now()->subMonths(8),
        ]);

        return $u;
    }

    private function formKirim(string $nik = '3277014511110001'): array
    {
        return ['aksi' => 'kirim', 'jenjang' => 'SMP', 'nama' => 'Anak Uji', 'nik' => $nik, 'jk' => 'L', 'tempat_lahir' => 'Bogor', 'tgl_lahir' => '2013-05-05',
            'alamat' => 'Bogor', 'ayah' => 'Ayah', 'ibu' => 'Ibu', 'hp' => '081200001111', 'setuju' => 1];
    }

    public function test_tidak_diterima_bisa_daftar_lagi_dengan_akun_yang_sama(): void
    {
        $this->terbitkan($this->lama);
        $u = $this->peserta('ditolak');

        $this->actingAs($u)->get('/pendaftar/dashboard')->assertOk()->assertSee('Daftar lagi di Gelombang Baru Uji');
        $this->actingAs($u)->get('/pendaftar/pengumuman')->assertOk()->assertSee('Daftar lagi di Gelombang Baru Uji');
        $this->actingAs($u)->get('/pendaftar/form-data')->assertOk()
            ->assertSee('Anda mendaftar kembali pada')->assertDontSee('sedang diproses');

        $this->actingAs($u)->post('/pendaftar/form-data', $this->formKirim())->assertRedirect('/pendaftar/upload-berkas')->assertSessionHasNoErrors();

        $this->assertSame(2, $u->pendaftaran()->count());
        $baru = $u->pendaftaran()->where('gelombang_id', $this->aktif->id)->firstOrFail();
        $this->assertTrue($baru->terkirim());
        $this->assertSame('ditolak', $u->pendaftaran()->where('gelombang_id', $this->lama->id)->value('status'), 'pendaftaran lama tidak berubah');

        // setelah mendaftar, dashboard memakai pendaftaran baru dan tawaran daftar lagi hilang
        $this->actingAs($u)->get('/pendaftar/dashboard')->assertOk()->assertSee($baru->kode)->assertDontSee('Daftar lagi di');

        // admin melihatnya di gelombang aktif
        $admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($admin)->get('/admin/pendaftar')->assertOk()->assertSee($baru->kode);
    }

    public function test_yang_diterima_tidak_bisa_daftar_lagi(): void
    {
        $this->terbitkan($this->lama);
        $u = $this->peserta('diterima');

        $this->actingAs($u)->get('/pendaftar/dashboard')->assertDontSee('Daftar lagi di');
        $this->actingAs($u)->post('/pendaftar/form-data', $this->formKirim())->assertSessionHas('error');
        $this->assertSame(1, $u->pendaftaran()->count());
    }

    public function test_hasil_yang_belum_terbit_tidak_membuka_pendaftaran_ulang(): void
    {
        $u = $this->peserta('ditolak'); // pengumuman gelombang lama belum diterbitkan

        $this->actingAs($u)->get('/pendaftar/dashboard')->assertDontSee('Daftar lagi di');
        $this->actingAs($u)->post('/pendaftar/form-data', $this->formKirim())->assertSessionHas('error');
        $this->assertSame(1, $u->pendaftaran()->count());
    }

    public function test_gelombang_aktif_yang_sama_tidak_membuka_pendaftaran_ulang(): void
    {
        $this->terbitkan($this->aktif);
        $u = $this->peserta('ditolak', $this->aktif);

        $this->actingAs($u)->get('/pendaftar/dashboard')->assertDontSee('Daftar lagi di');
        $this->actingAs($u)->post('/pendaftar/form-data', $this->formKirim())->assertSessionHas('error');
    }

    public function test_nik_yang_dipakai_pendaftar_lain_tetap_ditolak(): void
    {
        $this->terbitkan($this->lama);
        $u = $this->peserta('ditolak', $this->lama, '3277014511110001', 'a@uji.test');
        $this->peserta('menunggu', $this->aktif, '3277014522220002', 'b@uji.test'); // orang lain, NIK 2222…

        $this->actingAs($u)->post('/pendaftar/form-data', $this->formKirim('3277014522220002'))->assertSessionHasErrors('nik');
        $this->assertSame(1, $u->pendaftaran()->count());
    }
}
