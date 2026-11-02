<?php

namespace Tests\Feature;

use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Peran kepala sekolah: melihat pendaftar + ekspor + mengelola konten, tanpa verifikasi/seleksi/gelombang.
 * Akun dibuat di dalam transaksi (dibatalkan setelah tiap uji), tidak bergantung pada seeder.
 */
class PeranKepsekTest extends TestCase
{
    use DatabaseTransactions;

    private function akun(string $role): User
    {
        $n = random_int(10000000, 99999999);
        $u = new User(['name' => 'Uji '.$role, 'email' => "uji.{$role}.{$n}@uji.test", 'phone' => '0815'.$n, 'password' => 'rahasia123']);
        $u->forceFill(['role' => $role])->save();

        return $u;
    }

    public function test_kepsek_bisa_membuka_pendaftar_ekspor_dan_konten(): void
    {
        $k = $this->akun('kepsek');

        $this->actingAs($k)->get('/admin/pendaftar')->assertOk()->assertSee('Kepala Sekolah');
        $this->actingAs($k)->get('/admin/pendaftar/export')->assertOk();
        foreach (['profil', 'fasilitas', 'galeri', 'ekstrakurikuler'] as $halaman) {
            $this->actingAs($k)->get("/admin/konten/{$halaman}")->assertOk();
        }
    }

    public function test_kepsek_tidak_melihat_menu_admin_di_sidebar(): void
    {
        $html = $this->actingAs($this->akun('kepsek'))->get('/admin/pendaftar')->assertOk()->getContent();

        $this->assertStringNotContainsString('href="/admin/hasil-seleksi"', $html);
        $this->assertStringNotContainsString('href="/admin/gelombang"', $html);
        $this->assertStringContainsString('href="/admin/konten/galeri"', $html);
    }

    public function test_kepsek_ditolak_di_halaman_dan_aksi_khusus_admin(): void
    {
        $k = $this->akun('kepsek');

        $this->actingAs($k)->get('/admin/gelombang')->assertRedirect('/admin/pendaftar');
        $this->actingAs($k)->get('/admin/hasil-seleksi')->assertRedirect('/admin/pendaftar');
        $this->actingAs($k)->post('/admin/hasil-seleksi/publikasi')->assertRedirect('/admin/pendaftar');

        // Route model binding berjalan sebelum middleware peran, jadi butuh pendaftaran yang benar-benar ada.
        $id = Pendaftaran::query()->value('id');
        if ($id) {
            $status = Pendaftaran::find($id)->status;
            $this->actingAs($k)->post("/admin/pendaftar/{$id}/verifikasi", ['aksi' => 'setujui'])->assertRedirect('/admin/pendaftar');
            $this->actingAs($k)->post("/admin/pendaftar/{$id}/reset-sandi")->assertRedirect('/admin/pendaftar');
            $this->assertSame($status, Pendaftaran::find($id)->status);
        }
    }

    public function test_admin_tetap_punya_akses_penuh(): void
    {
        $a = $this->akun('admin');

        $this->actingAs($a)->get('/admin/pendaftar')->assertOk()->assertSee('href="/admin/hasil-seleksi"', false);
        $this->actingAs($a)->get('/admin/gelombang')->assertOk();
        $this->actingAs($a)->get('/admin/hasil-seleksi')->assertOk();
    }

    public function test_pendaftar_tidak_bisa_masuk_panel_dan_kepsek_tidak_masuk_area_pendaftar(): void
    {
        $this->actingAs($this->akun('pendaftar'))->get('/admin/konten/galeri')->assertRedirect('/pendaftar/dashboard');
        $this->actingAs($this->akun('kepsek'))->get('/pendaftar/dashboard')->assertRedirect('/admin/pendaftar');
    }

    public function test_login_kepsek_diarahkan_ke_panel(): void
    {
        $k = $this->akun('kepsek');

        $this->post('/login', ['username' => $k->email, 'password' => 'rahasia123'])->assertRedirect('/admin/pendaftar');
    }

    public function test_perintah_admin_buat_membuat_akun_kepsek_dengan_sandi_ter_hash(): void
    {
        $email = 'kepsek.baru.'.random_int(1000, 9999).'@uji.test';
        $hp = '0816'.random_int(10000000, 99999999);

        $this->artisan('admin:buat', ['--peran' => 'kepsek', '--nama' => 'Kepala Baru', '--email' => $email, '--hp' => $hp, '--wajib-ganti' => true])
            ->expectsQuestion('Kata sandi (min. 8 karakter, huruf dan angka)', 'SandiKuat123')
            ->expectsQuestion('Ulangi kata sandi', 'SandiKuat123')
            ->assertSuccessful();

        $u = User::where('email', $email)->firstOrFail();
        $this->assertSame('kepsek', $u->role);
        $this->assertTrue($u->must_change_password);
        $this->assertNotSame('SandiKuat123', $u->getRawOriginal('password'));
        $this->assertTrue(\Hash::check('SandiKuat123', $u->password));
    }

    public function test_perintah_admin_buat_menolak_sandi_lemah_dan_peran_aneh(): void
    {
        $email = 'lemah.'.random_int(1000, 9999).'@uji.test';

        $this->artisan('admin:buat', ['--peran' => 'admin', '--nama' => 'X', '--email' => $email, '--hp' => '0817'.random_int(10000000, 99999999)])
            ->expectsQuestion('Kata sandi (min. 8 karakter, huruf dan angka)', 'pendek')
            ->expectsQuestion('Ulangi kata sandi', 'pendek')
            ->assertFailed();
        $this->assertDatabaseMissing('users', ['email' => $email]);

        $this->artisan('admin:buat', ['--peran' => 'superuser'])->assertFailed();
    }
}
