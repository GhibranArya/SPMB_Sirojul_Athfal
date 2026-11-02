<?php

namespace Tests\Feature;

use App\Models\Gelombang;
use App\Models\Jenjang;
use App\Models\Pendaftaran;
use App\Models\ProfilYayasan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Kata sandi: ganti sandi, reset sementara oleh panitia (tanpa email), dan halaman lupa kata sandi. */
class SandiTest extends TestCase
{
    use DatabaseTransactions;

    private function wali(string $email = 'wali@sandi.test', string $sandi = 'LamaSandi123'): User
    {
        $u = User::create(['name' => 'Wali Sandi', 'email' => $email, 'phone' => '0815'.random_int(10000000, 99999999), 'password' => $sandi]);
        $u->forceFill(['role' => 'pendaftar'])->save();

        return $u;
    }

    private function pendaftaranUntuk(User $u): Pendaftaran
    {
        $g = Gelombang::aktif() ?? Gelombang::create(['nama' => 'G Uji', 'tahun_ajaran' => '2031/2032', 'tgl_buka' => today(), 'tgl_tutup' => today()->addDays(5), 'tgl_pengumuman' => today()->addDays(9), 'is_aktif' => true]);

        return Pendaftaran::create([
            'user_id' => $u->id, 'gelombang_id' => $g->id, 'jenjang_id' => Jenjang::where('kode', 'SMP')->value('id'),
            'kode' => Pendaftaran::buatKode(), 'status' => 'menunggu', 'nama_siswa' => 'Anak Sandi', 'nik' => '32770145'.random_int(10000000, 99999999),
            'jenis_kelamin' => 'L', 'tempat_lahir' => 'Bogor', 'tgl_lahir' => '2013-05-05', 'alamat_siswa' => 'Bogor', 'nama_ayah' => 'A', 'nama_ibu' => 'I',
            'hp_ortu' => '081200001111', 'setuju_data' => true, 'submitted_at' => now(),
        ]);
    }

    public function test_halaman_lupa_sandi_dan_tautan_di_login(): void
    {
        $this->get('/login')->assertOk()->assertSee('href="/lupa-sandi"', false);

        $profil = ProfilYayasan::first() ?? new ProfilYayasan;
        $profil->whatsapp = '0812-3456-7890';
        $profil->save();

        $this->get('/lupa-sandi')->assertOk()->assertSee('kata sandi sementara')->assertSee('https://wa.me/6281234567890?text=', false);

        $profil->whatsapp = null;
        $profil->save();
        $this->get('/lupa-sandi')->assertOk()->assertDontSee('wa.me')->assertSee('langsung di sekolah');
    }

    public function test_pendaftar_dapat_mengganti_sandi(): void
    {
        $u = $this->wali();
        $a = $this->actingAs($u);

        $a->get('/ganti-sandi')->assertOk()->assertSee('Kata sandi saat ini');

        $a->post('/ganti-sandi', ['current_password' => 'Salah12345', 'password' => 'BaruSandi456', 'password_confirmation' => 'BaruSandi456'])->assertSessionHasErrors('current_password');
        $a->post('/ganti-sandi', ['current_password' => 'LamaSandi123', 'password' => 'lemah', 'password_confirmation' => 'lemah'])->assertSessionHasErrors('password');
        $a->post('/ganti-sandi', ['current_password' => 'LamaSandi123', 'password' => 'LamaSandi123', 'password_confirmation' => 'LamaSandi123'])->assertSessionHasErrors('password');
        $a->post('/ganti-sandi', ['current_password' => 'LamaSandi123', 'password' => 'BaruSandi456', 'password_confirmation' => 'beda'])->assertSessionHasErrors('password');

        $a->post('/ganti-sandi', ['current_password' => 'LamaSandi123', 'password' => 'BaruSandi456', 'password_confirmation' => 'BaruSandi456'])
            ->assertRedirect('/pendaftar/dashboard')->assertSessionHas('status');
        $this->assertTrue(Hash::check('BaruSandi456', $u->fresh()->password));
    }

    public function test_tamu_tidak_bisa_membuka_ganti_sandi(): void
    {
        $this->get('/ganti-sandi')->assertRedirect('/login');
        $this->post('/ganti-sandi', [])->assertRedirect('/login');
    }

    public function test_admin_mereset_sandi_wali_lalu_wali_wajib_menggantinya(): void
    {
        $wali = $this->wali();
        $p = $this->pendaftaranUntuk($wali);
        $admin = User::where('role', 'admin')->firstOrFail();

        $r = $this->actingAs($admin)->from('/admin/pendaftar')->post('/admin/pendaftar/'.$p->id.'/reset-sandi')->assertRedirect('/admin/pendaftar');
        $r->assertSessionHas('sandi_sementara');
        $sementara = session('sandi_sementara');
        $this->assertSame(10, strlen($sementara['sandi']));
        $this->assertMatchesRegularExpression('/[A-Za-z]/', $sementara['sandi']);
        $this->assertMatchesRegularExpression('/\d/', $sementara['sandi']);
        $this->assertStringStartsWith('62', $sementara['wa']);

        $wali->refresh();
        $this->assertTrue($wali->must_change_password);
        $this->assertFalse(Hash::check('LamaSandi123', $wali->password), 'sandi lama tidak berlaku lagi');
        $this->assertTrue(Hash::check($sementara['sandi'], $wali->password));
        $this->assertStringContainsString('mereset kata sandi', $p->riwayat()->latest('id')->value('keterangan'));

        // sandi sementara tampil di halaman admin (sekali)
        $this->get('/admin/pendaftar')->assertOk()->assertSee($sementara['sandi'])->assertSee('Kata sandi sementara untuk Wali Sandi');
        $this->get('/admin/pendaftar')->assertDontSee($sementara['sandi']);
    }

    public function test_akun_dengan_sandi_sementara_diarahkan_ke_ganti_sandi(): void
    {
        $wali = $this->wali();
        $wali->forceFill(['must_change_password' => true])->save();

        foreach (['/pendaftar/dashboard', '/pendaftar/form-data', '/pendaftar/upload-berkas', '/pendaftar/pengumuman'] as $url) {
            $this->actingAs($wali)->get($url)->assertRedirect('/ganti-sandi');
        }
        $this->actingAs($wali)->get('/ganti-sandi')->assertOk()->assertSee('kata sandi sementara');

        $this->actingAs($wali)->post('/ganti-sandi', ['current_password' => 'LamaSandi123', 'password' => 'SandiBaru789', 'password_confirmation' => 'SandiBaru789'])
            ->assertRedirect('/pendaftar/dashboard');
        $this->assertFalse($wali->fresh()->must_change_password);
        $this->actingAs($wali->fresh())->get('/pendaftar/dashboard')->assertOk();
    }

    public function test_pendaftar_tidak_bisa_mereset_sandi_orang_lain(): void
    {
        $korban = $this->wali('korban@sandi.test');
        $p = $this->pendaftaranUntuk($korban);
        $penyerang = $this->wali('penyerang@sandi.test');

        $this->actingAs($penyerang)->post('/admin/pendaftar/'.$p->id.'/reset-sandi')->assertRedirect('/pendaftar/dashboard');
        $this->assertFalse($korban->fresh()->must_change_password);
        $this->assertTrue(Hash::check('LamaSandi123', $korban->fresh()->password));
    }
}
