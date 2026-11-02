<?php

namespace Tests\Feature;

use App\Models\Gelombang;
use App\Models\Jenjang;
use App\Models\Pendaftaran;
use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Unduh bukti diterima: hanya untuk yang Diterima dan hanya setelah pengumuman dipublikasikan (BR-07). */
class BuktiDiterimaTest extends TestCase
{
    use DatabaseTransactions;

    private function pendaftar(string $status, bool $terbit, string $email): User
    {
        $g = Gelombang::create([
            'nama' => 'Gelombang Uji '.$email, 'tahun_ajaran' => '2024/2025', 'tgl_buka' => '2024-11-01', 'tgl_tutup' => '2024-11-30',
            'tgl_pengumuman' => '2024-12-10', 'daftar_ulang_mulai' => '2024-12-15', 'daftar_ulang_selesai' => '2024-12-20', 'is_aktif' => false,
        ]);
        if ($terbit) {
            Pengumuman::create(['gelombang_id' => $g->id, 'judul' => 'Hasil uji', 'published_at' => '2024-12-10 08:00:00']);
        }

        $u = User::create(['name' => 'Wali Uji', 'email' => $email, 'phone' => '0819'.random_int(10000000, 99999999), 'password' => 'rahasia123']);
        $u->forceFill(['role' => 'pendaftar'])->save();

        Pendaftaran::create([
            'user_id' => $u->id, 'gelombang_id' => $g->id, 'jenjang_id' => Jenjang::where('kode', 'SMP')->value('id'),
            'kode' => Pendaftaran::buatKode(), 'status' => $status, 'nama_siswa' => 'Siswa Uji', 'nik' => '3277014500000'.random_int(100, 999),
            'jenis_kelamin' => 'P', 'tempat_lahir' => 'Bogor', 'tgl_lahir' => '2013-05-05', 'asal_sekolah' => 'SD Uji',
            'alamat_siswa' => 'Bogor', 'nama_ayah' => 'Ayah Uji', 'nama_ibu' => 'Ibu Uji', 'hp_ortu' => '081200001111',
            'setuju_data' => true, 'submitted_at' => now(),
        ]);

        return $u;
    }

    public function test_diterima_dan_sudah_terbit_bisa_mengunduh_pdf(): void
    {
        $r = $this->actingAs($this->pendaftar('diterima', true, 'a@uji.test'))->get('/pendaftar/bukti-diterima');

        $r->assertOk();
        $this->assertStringContainsString('application/pdf', $r->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $r->getContent() ?: $r->streamedContent());
        $this->assertStringContainsString('bukti-diterima-SA-', $r->headers->get('content-disposition'));
    }

    public function test_belum_terbit_ditolak_atau_bukan_diterima_dialihkan(): void
    {
        $this->actingAs($this->pendaftar('diterima', false, 'b@uji.test'))->get('/pendaftar/bukti-diterima')
            ->assertRedirect('/pendaftar/pengumuman')->assertSessionHas('error');

        $this->actingAs($this->pendaftar('ditolak', true, 'c@uji.test'))->get('/pendaftar/bukti-diterima')
            ->assertRedirect('/pendaftar/pengumuman');

        $this->actingAs($this->pendaftar('terverifikasi', true, 'd@uji.test'))->get('/pendaftar/bukti-diterima')
            ->assertRedirect('/pendaftar/pengumuman');
    }

    public function test_tamu_harus_login(): void
    {
        $this->get('/pendaftar/bukti-diterima')->assertRedirect('/login');
    }

    public function test_tombol_cetak_menautkan_ke_unduhan_bukan_pesan_demo(): void
    {
        $this->actingAs($this->pendaftar('diterima', true, 'e@uji.test'))->get('/pendaftar/pengumuman')
            ->assertOk()->assertSee('href="/pendaftar/bukti-diterima"', false)->assertDontSee('Mode demo');
    }
}
