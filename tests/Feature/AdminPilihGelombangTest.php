<?php

namespace Tests\Feature;

use App\Models\Gelombang;
use App\Models\Jenjang;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Admin dapat mengelola pendaftar gelombang mana pun, bukan hanya yang sedang aktif. */
class AdminPilihGelombangTest extends TestCase
{
    use DatabaseTransactions;

    private Gelombang $aktif;

    private Gelombang $lain;

    private Pendaftaran $pA;

    private Pendaftaran $pB;

    protected function setUp(): void
    {
        parent::setUp();

        Gelombang::query()->update(['is_aktif' => false]);
        $this->aktif = Gelombang::create(['nama' => 'Gelombang Aktif Uji', 'tahun_ajaran' => '2030/2031', 'tgl_buka' => '2030-01-01', 'tgl_tutup' => '2030-01-31', 'tgl_pengumuman' => '2030-02-10', 'is_aktif' => true]);
        $this->lain = Gelombang::create(['nama' => 'Gelombang Lain Uji', 'tahun_ajaran' => '2030/2031', 'tgl_buka' => '2029-01-01', 'tgl_tutup' => '2029-01-31', 'tgl_pengumuman' => '2029-02-10', 'is_aktif' => false]);

        $this->pA = $this->pendaftaran($this->aktif, 'Anak Gelombang Aktif', '3277014533330001');
        $this->pB = $this->pendaftaran($this->lain, 'Anak Gelombang Lain', '3277014533330002');
    }

    private function pendaftaran(Gelombang $g, string $nama, string $nik): Pendaftaran
    {
        $u = User::create(['name' => 'Wali '.$nama, 'email' => $nik.'@uji.test', 'phone' => '0817'.random_int(10000000, 99999999), 'password' => 'rahasia123']);
        $u->forceFill(['role' => 'pendaftar'])->save();

        return Pendaftaran::create([
            'user_id' => $u->id, 'gelombang_id' => $g->id, 'jenjang_id' => Jenjang::where('kode', 'SMP')->value('id'),
            'kode' => Pendaftaran::buatKode(), 'status' => 'menunggu', 'nama_siswa' => $nama, 'nik' => $nik, 'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bogor', 'tgl_lahir' => '2013-05-05', 'alamat_siswa' => 'Bogor', 'nama_ayah' => 'Ayah', 'nama_ibu' => 'Ibu '.$nama,
            'hp_ortu' => '081200001111', 'setuju_data' => true, 'submitted_at' => now(),
        ]);
    }

    private function admin(): User
    {
        return User::where('role', 'admin')->firstOrFail();
    }

    public function test_daftar_pendaftar_mengikuti_gelombang_yang_dipilih(): void
    {
        $a = $this->actingAs($this->admin());

        $a->get('/admin/pendaftar')->assertOk()->assertSee('Anak Gelombang Aktif')->assertDontSee('Anak Gelombang Lain');
        $a->get('/admin/pendaftar?gelombang='.$this->lain->id)->assertOk()
            ->assertSee('Anak Gelombang Lain')->assertDontSee('Anak Gelombang Aktif')
            ->assertSee('Gelombang Lain Uji · TA 2030/2031');

        $csv = $a->get('/admin/pendaftar/export?gelombang='.$this->lain->id)->assertOk()->streamedContent();
        $this->assertStringContainsString($this->pB->kode, $csv);
        $this->assertStringNotContainsString($this->pA->kode, $csv);
    }

    public function test_modal_periksa_memuat_data_siswa_dan_orang_tua(): void
    {
        $this->pA->update(['asal_sekolah' => 'SDN Uji 7', 'pekerjaan_ortu' => 'Guru Uji']);

        $this->actingAs($this->admin())->get('/admin/pendaftar')->assertOk()
            ->assertSee('3277014533330001', false)   // NIK lengkap, untuk dicocokkan dengan KK
            ->assertSee('Laki-laki', false)->assertSee('Bogor, 5 Mei 2013', false)
            ->assertSee('SDN Uji 7', false)->assertSee('Guru Uji', false)->assertSee('Jenjang', false);
    }

    public function test_verifikasi_dan_hasil_di_gelombang_yang_tidak_aktif(): void
    {
        $a = $this->actingAs($this->admin());

        $a->post('/admin/pendaftar/'.$this->pB->id.'/verifikasi', ['aksi' => 'setujui'])->assertSessionHas('status');
        $this->assertSame('terverifikasi', $this->pB->fresh()->status);

        $a->get('/admin/hasil-seleksi?gelombang='.$this->lain->id)->assertOk()->assertSee('Anak Gelombang Lain');
        $a->get('/admin/hasil-seleksi')->assertOk()->assertDontSee('Anak Gelombang Lain');

        // hasil ditetapkan di gelombang pilihan; id dari gelombang lain diabaikan
        $a->post('/admin/hasil-seleksi', ['gelombang_id' => $this->lain->id, 'hasil' => [$this->pB->id => 'diterima', $this->pA->id => 'diterima']])
            ->assertSessionHas('status');
        $this->assertSame('diterima', $this->pB->fresh()->status);
        $this->assertSame('menunggu', $this->pA->fresh()->status, 'pendaftar gelombang lain tidak ikut berubah');

        // publikasi hanya untuk gelombang yang dipilih
        $a->post('/admin/hasil-seleksi/publikasi', ['gelombang_id' => $this->lain->id])->assertSessionHas('status');
        $this->assertTrue($this->lain->fresh()->hasilTerbit());
        $this->assertFalse($this->aktif->fresh()->hasilTerbit());

        // hasil yang sudah terbit tidak bisa diubah lagi
        $a->post('/admin/hasil-seleksi', ['gelombang_id' => $this->lain->id, 'hasil' => [$this->pB->id => 'ditolak']])->assertSessionHas('error');
        $this->assertSame('diterima', $this->pB->fresh()->status);
    }
}
