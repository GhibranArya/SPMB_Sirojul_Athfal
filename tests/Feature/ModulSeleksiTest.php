<?php

namespace Tests\Feature;

use App\Models\Gelombang;
use App\Models\Jenjang;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Modul Seleksi & Pengumuman: verifikasi berkas, penetapan hasil, kuota, publikasi, dan riwayat status. */
class ModulSeleksiTest extends TestCase
{
    use DatabaseTransactions;

    private Gelombang $g;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Gelombang::query()->update(['is_aktif' => false]);
        $this->g = Gelombang::create(['nama' => 'Gelombang Modul Uji', 'tahun_ajaran' => '2031/2032', 'tgl_buka' => '2031-01-01', 'tgl_tutup' => '2031-01-31', 'tgl_pengumuman' => '2031-02-10', 'is_aktif' => true]);
        $this->admin = User::where('role', 'admin')->firstOrFail();
    }

    private function pendaftar(string $nama, string $status = 'menunggu', string $jenjang = 'SMP'): Pendaftaran
    {
        static $n = 0;
        $n++;
        $u = User::create(['name' => 'Wali '.$nama, 'email' => "modul{$n}@uji.test", 'phone' => '08188'.str_pad((string) $n, 7, '0', STR_PAD_LEFT), 'password' => 'rahasia123']);
        $u->forceFill(['role' => 'pendaftar'])->save();

        return Pendaftaran::create([
            'user_id' => $u->id, 'gelombang_id' => $this->g->id, 'jenjang_id' => Jenjang::where('kode', $jenjang)->value('id'),
            'kode' => Pendaftaran::buatKode(), 'status' => $status, 'nama_siswa' => $nama, 'nik' => '32770145'.str_pad((string) $n, 8, '0', STR_PAD_LEFT),
            'jenis_kelamin' => 'L', 'tempat_lahir' => 'Bogor', 'tgl_lahir' => '2013-05-05', 'alamat_siswa' => 'Bogor',
            'nama_ayah' => 'Ayah', 'nama_ibu' => 'Ibu', 'hp_ortu' => '081200001111', 'setuju_data' => true, 'submitted_at' => now(),
        ]);
    }

    public function test_perbaikan_wajib_bercatatan_lalu_status_dan_riwayat_tercatat(): void
    {
        $p = $this->pendaftar('Anak A');
        $a = $this->actingAs($this->admin);

        $a->post("/admin/pendaftar/{$p->id}/verifikasi", ['aksi' => 'perbaikan'])->assertSessionHasErrors('catatan');
        $this->assertSame('menunggu', $p->fresh()->status);

        $a->post("/admin/pendaftar/{$p->id}/verifikasi", ['aksi' => 'perbaikan', 'catatan' => 'KK kurang jelas'])->assertSessionHasNoErrors();
        $p->refresh();
        $this->assertSame('perbaikan', $p->status);
        $this->assertSame('KK kurang jelas', $p->catatan);
        $this->assertSame($this->admin->id, $p->riwayat()->latest('id')->first()->user_id);
    }

    public function test_hanya_status_menunggu_yang_dapat_diverifikasi(): void
    {
        $p = $this->pendaftar('Anak B', 'diterima');

        $this->actingAs($this->admin)->post("/admin/pendaftar/{$p->id}/verifikasi", ['aksi' => 'setujui'])
            ->assertSessionHas('error');
        $this->assertSame('diterima', $p->fresh()->status);
    }

    public function test_hasil_ditetapkan_dan_peringatan_muncul_bila_melebihi_kuota(): void
    {
        $smp = Jenjang::where('kode', 'SMP')->value('id');
        $this->g->kuota()->create(['jenjang_id' => $smp, 'kuota' => 1]);
        $a = $this->pendaftar('Anak C', 'terverifikasi');
        $b = $this->pendaftar('Anak D', 'terverifikasi');

        $this->actingAs($this->admin)->post('/admin/hasil-seleksi', ['gelombang_id' => $this->g->id, 'hasil' => [$a->id => 'diterima', $b->id => 'diterima']])
            ->assertSessionHas('status', '2 hasil seleksi disimpan.')
            ->assertSessionHas('warning', 'Jumlah Diterima melebihi kuota: SMP (2/1).');

        $this->assertSame('diterima', $a->fresh()->status);
        $this->assertSame('Hasil seleksi ditetapkan', $a->riwayat()->latest('id')->first()->keterangan);
    }

    public function test_publikasi_ditolak_selama_masih_ada_yang_belum_ditetapkan(): void
    {
        $this->pendaftar('Anak E', 'terverifikasi');

        $this->actingAs($this->admin)->post('/admin/hasil-seleksi/publikasi', ['gelombang_id' => $this->g->id])
            ->assertSessionHas('error');
        $this->assertFalse($this->g->fresh()->hasilTerbit());
    }

    public function test_hasil_tersembunyi_dari_pendaftar_sampai_dipublikasikan_lalu_terkunci(): void
    {
        $p = $this->pendaftar('Anak F', 'diterima');
        $a = $this->actingAs($this->admin);

        $this->assertSame('terverifikasi', $p->untukView()['status']); // belum terbit: pendaftar belum boleh tahu (BR-07)
        $this->assertNull($p->untukView()['hasil']);

        $a->post('/admin/hasil-seleksi/publikasi', ['gelombang_id' => $this->g->id])->assertSessionHas('status');
        $p = $p->fresh();
        $this->assertTrue($p->gelombang->fresh()->hasilTerbit());
        $this->assertSame('diterima', $p->untukView()['hasil']);

        // sesudah terbit hasil tidak boleh diubah lagi
        $a->post('/admin/hasil-seleksi', ['gelombang_id' => $this->g->id, 'hasil' => [$p->id => 'ditolak']])->assertSessionHas('error');
        $this->assertSame('diterima', $p->fresh()->status);
    }

    public function test_wali_tidak_dapat_menetapkan_hasil(): void
    {
        $p = $this->pendaftar('Anak G', 'terverifikasi');

        $this->actingAs($p->user)->post('/admin/hasil-seleksi', ['gelombang_id' => $this->g->id, 'hasil' => [$p->id => 'diterima']])
            ->assertRedirect('/pendaftar/dashboard');
        $this->assertSame('terverifikasi', $p->fresh()->status);
    }
}
