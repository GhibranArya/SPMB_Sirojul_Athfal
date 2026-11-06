<?php

namespace Tests\Feature;

use App\Models\Gelombang;
use App\Models\Jenjang;
use App\Models\Pendaftaran;
use App\Models\ProfilYayasan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Perbaikan hasil tinjauan keamanan: header, CSV injection, dan tautan sosial hanya https. */
class KeamananTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        $n = random_int(10000000, 99999999);
        $u = new User(['name' => 'Uji admin', 'email' => "uji.admin.{$n}@uji.test", 'phone' => '0815'.$n, 'password' => 'rahasia123']);
        $u->forceFill(['role' => 'admin'])->save();

        return $u;
    }

    public function test_respons_memuat_header_keamanan_dasar(): void
    {
        $r = $this->get('/login')->assertOk();

        $r->assertHeader('X-Content-Type-Options', 'nosniff');
        $r->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $r->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $r->assertHeader('Permissions-Policy');
        $r->assertHeaderMissing('Strict-Transport-Security'); // hanya untuk koneksi HTTPS
    }

    public function test_hsts_hanya_dikirim_lewat_https(): void
    {
        $this->get('https://localhost/login')->assertHeader('Strict-Transport-Security');
    }

    public function test_ekspor_csv_menetralkan_rumus_pada_isian_pendaftar(): void
    {
        Gelombang::query()->update(['is_aktif' => false]);
        $g = Gelombang::create(['nama' => 'Gelombang CSV Uji', 'tahun_ajaran' => '2031/2032', 'tgl_buka' => '2031-01-01', 'tgl_tutup' => '2031-01-31', 'tgl_pengumuman' => '2031-02-10', 'is_aktif' => true]);

        $wali = User::create(['name' => 'Wali CSV', 'email' => 'csv@uji.test', 'phone' => '0817'.random_int(10000000, 99999999), 'password' => 'rahasia123']);
        $wali->forceFill(['role' => 'pendaftar'])->save();
        Pendaftaran::create([
            'user_id' => $wali->id, 'gelombang_id' => $g->id, 'jenjang_id' => Jenjang::where('kode', 'SMP')->value('id'),
            'kode' => Pendaftaran::buatKode(), 'status' => 'menunggu', 'nama_siswa' => '=HYPERLINK("http://x.test","klik")', 'nik' => '3277014533330099',
            'jenis_kelamin' => 'L', 'tempat_lahir' => 'Bogor', 'tgl_lahir' => '2013-05-05', 'alamat_siswa' => 'Bogor',
            'nama_ayah' => '@SUM(1+1)', 'nama_ibu' => null, 'hp_ortu' => '081200001111', 'setuju_data' => true, 'submitted_at' => now(),
        ]);

        $csv = $this->actingAs($this->admin())->get('/admin/pendaftar/export?gelombang='.$g->id)->assertOk()->streamedContent();

        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString("'@SUM(1+1)", $csv);
        $this->assertStringNotContainsString(',=HYPERLINK', $csv);
        $this->assertStringNotContainsString(',@SUM', $csv);
        $this->assertStringNotContainsString('"=HYPERLINK', $csv);
    }

    public function test_tautan_sosial_wajib_https(): void
    {
        $profil = ProfilYayasan::first();
        $isi = ['visi' => $profil->visi, 'misi' => $profil->misi, 'alamat' => $profil->alamat];
        $admin = $this->admin();

        foreach (['http://www.instagram.com/uji', 'ftp://contoh.test/uji', 'javascript:alert(1)'] as $buruk) {
            $this->actingAs($admin)->put('/admin/konten/profil', $isi + ['instagram' => $buruk])->assertSessionHasErrors('instagram');
        }

        $this->actingAs($admin)->put('/admin/konten/profil', $isi + ['instagram' => 'https://www.instagram.com/uji'])->assertSessionHasNoErrors();
        $this->assertSame('https://www.instagram.com/uji', $profil->fresh()->instagram);
    }
}
