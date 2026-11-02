<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Modul Autentikasi & Otorisasi: login (email/nomor HP), registrasi, dan pembatasan akses per peran. */
class ModulAutentikasiTest extends TestCase
{
    use DatabaseTransactions;

    private function wali(string $email = 'wali.modul@uji.test', string $hp = '081277700001'): User
    {
        $u = User::create(['name' => 'Wali Modul', 'email' => $email, 'phone' => $hp, 'password' => 'rahasia123']);
        $u->forceFill(['role' => 'pendaftar'])->save();

        return $u;
    }

    public function test_login_dengan_email_dan_dengan_nomor_hp_berformat_bebas(): void
    {
        $this->wali();

        $this->post('/login', ['username' => 'WALI.Modul@uji.test', 'password' => 'rahasia123'])
            ->assertRedirect('/pendaftar/dashboard');
        $this->post('/logout');

        // Awalan +62 dan tanda hubung dinormalkan menjadi 081277700001.
        $this->post('/login', ['username' => '+62 8127-7700-001', 'password' => 'rahasia123'])
            ->assertRedirect('/pendaftar/dashboard');
    }

    public function test_sandi_salah_ditolak_tanpa_membocorkan_mana_yang_salah(): void
    {
        $this->wali();

        $this->from('/login')->post('/login', ['username' => 'wali.modul@uji.test', 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['username' => 'Email/nomor HP atau kata sandi salah.']);
        $this->assertGuest();

        $this->from('/login')->post('/login', ['username' => 'tidak.ada@uji.test', 'password' => 'rahasia123'])
            ->assertSessionHasErrors(['username' => 'Email/nomor HP atau kata sandi salah.']);
    }

    public function test_registrasi_selalu_membuat_peran_pendaftar_walau_dikirim_role_admin(): void
    {
        $this->post('/register', [
            'name' => 'Wali Baru', 'email' => 'Baru.Modul@Uji.test', 'phone' => '+62 812-7770-0002',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123', 'agree' => '1', 'role' => 'admin',
        ])->assertRedirect('/pendaftar/dashboard');

        $u = User::where('email', 'baru.modul@uji.test')->firstOrFail();
        $this->assertSame('pendaftar', $u->role);
        $this->assertSame('081277700002', $u->phone);
        $this->assertNotSame('rahasia123', $u->password); // tersimpan sebagai hash
    }

    public function test_registrasi_menolak_sandi_lemah_dan_email_ganda(): void
    {
        $this->wali();

        $this->post('/register', [
            'name' => 'X', 'email' => 'wali.modul@uji.test', 'phone' => '081277700009',
            'password' => 'abcdefgh', 'password_confirmation' => 'abcdefgh', 'agree' => '1',
        ])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_peran_membatasi_halaman_yang_boleh_dibuka(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        $this->get('/pendaftar/dashboard')->assertRedirect('/login');            // tamu
        $this->actingAs($this->wali())->get('/admin/pendaftar')->assertRedirect('/pendaftar/dashboard'); // wali ke area admin
        $this->actingAs($admin)->get('/pendaftar/dashboard')->assertRedirect('/admin/pendaftar');         // admin ke area wali
        $this->actingAs($admin)->get('/admin/pendaftar')->assertOk();
    }

    public function test_logout_mengakhiri_sesi(): void
    {
        $this->actingAs($this->wali())->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }
}
