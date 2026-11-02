<?php

namespace Tests\Feature;

use App\Models\Ekstrakurikuler;
use App\Models\Fasilitas;
use App\Models\Galeri;
use App\Models\ProfilYayasan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Pengelolaan konten situs dari admin. Memakai DatabaseTransactions (perubahan DB dibatalkan setelah tiap uji)
 * dan membersihkan foto yang diunggah selama uji.
 */
class KontenAdminTest extends TestCase
{
    use DatabaseTransactions;

    private array $berkasUji = [];

    protected function tearDown(): void
    {
        foreach ($this->berkasUji as $nama) {
            @unlink(public_path('images/foto/'.$nama));
        }
        parent::tearDown();
    }

    private function admin(): User
    {
        return User::where('role', 'admin')->firstOrFail();
    }

    /** PNG sah (hanya header IHDR) dengan ukuran tertentu, cukup untuk validasi gambar dan getimagesize. */
    private function png(string $nama, int $w, int $h): UploadedFile
    {
        $ihdr = pack('NN', $w, $h)."\x08\x02\x00\x00\x00";
        $chunk = fn (string $tipe, string $data) => pack('N', strlen($data)).$tipe.$data.pack('N', crc32($tipe.$data));
        $isi = "\x89PNG\r\n\x1a\n".$chunk('IHDR', $ihdr).$chunk('IDAT', "\x78\x9c\x63\x00\x00\x00\x02\x00\x01").$chunk('IEND', '');

        return UploadedFile::fake()->createWithContent($nama, $isi);
    }

    private function catat(string $path): string
    {
        $this->berkasUji[] = basename($path);

        return $path;
    }

    public function test_halaman_konten_hanya_untuk_admin(): void
    {
        $this->get('/admin/konten/profil')->assertRedirect('/login');

        $wali = User::where('role', 'pendaftar')->firstOrFail();
        $this->actingAs($wali)->get('/admin/konten/galeri')->assertRedirect('/pendaftar/dashboard');

        foreach (['profil', 'fasilitas', 'galeri', 'ekstrakurikuler'] as $h) {
            $this->actingAs($this->admin())->get('/admin/konten/'.$h)->assertOk();
        }
    }

    public function test_profil_dan_kontak_tersimpan_dan_tampil_di_footer(): void
    {
        $this->actingAs($this->admin())->put('/admin/konten/profil', [
            'visi' => 'Visi uji.', 'misi' => "Misi satu\nMisi dua", 'alamat' => 'Alamat uji', 'npsn' => '20200558',
            'telepon' => '021-555', 'whatsapp' => '0812-3456-7890', 'email' => 'info@sekolah.test',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertSame(['Misi satu', 'Misi dua'], ProfilYayasan::first()->daftarMisi());
        $this->get('/profil')->assertOk()->assertSee('Misi dua');
        $this->get('/fasilitas')->assertOk()->assertSee('info@sekolah.test')->assertSee('021-555');

        $this->actingAs($this->admin())->put('/admin/konten/profil', ['visi' => '', 'misi' => 'x', 'alamat' => 'x', 'email' => 'bukan-email'])
            ->assertSessionHasErrors(['visi', 'email']);
    }

    public function test_fasilitas_tambah_ubah_hapus(): void
    {
        $a = $this->actingAs($this->admin());
        $a->post('/admin/konten/fasilitas', ['nama' => 'Laboratorium Uji', 'ikon' => 'fa-flask'])->assertRedirect('/admin/konten/fasilitas');
        $f = Fasilitas::where('nama', 'Laboratorium Uji')->firstOrFail();
        $this->assertGreaterThan(0, $f->urutan);

        $this->get('/fasilitas')->assertSee('Laboratorium Uji');
        $a->put('/admin/konten/fasilitas/'.$f->id, ['nama' => 'Lab Uji 2', 'ikon' => 'fa-microscope', 'urutan' => 5])->assertSessionHasNoErrors();
        $this->assertSame(['Lab Uji 2', 'fa-microscope', 5], [$f->fresh()->nama, $f->fresh()->ikon, $f->fresh()->urutan]);

        $a->post('/admin/konten/fasilitas', ['nama' => 'X', 'ikon' => 'fa-tidak-ada'])->assertSessionHasErrors('ikon');

        $a->delete('/admin/konten/fasilitas/'.$f->id)->assertRedirect('/admin/konten/fasilitas');
        $this->assertNull(Fasilitas::find($f->id));
    }

    public function test_galeri_unggah_menentukan_kelayakan_hero_dan_hapus_berkas(): void
    {
        $a = $this->actingAs($this->admin());

        $a->post('/admin/konten/galeri', ['foto' => $this->png('besar.png', 1600, 900), 'judul' => 'Foto Besar Uji', 'untuk_hero' => 1, 'fx' => 40, 'fy' => 60])
            ->assertSessionHasNoErrors()->assertSessionMissing('warning');
        $besar = Galeri::where('judul', 'Foto Besar Uji')->firstOrFail();
        $this->catat($besar->gambar);
        $this->assertTrue((bool) $besar->untuk_hero);
        $this->assertSame('40% 60%', $besar->posisi_gambar);
        $this->assertFileExists(public_path($besar->gambar));
        $this->assertMatchesRegularExpression('#^images/foto/foto-besar-uji-[a-z0-9]{6}\.png$#', $besar->gambar);

        $a->post('/admin/konten/galeri', ['foto' => $this->png('potret.png', 720, 1280), 'judul' => 'Foto Potret Uji', 'untuk_hero' => 1])
            ->assertSessionHas('warning');
        $potret = Galeri::where('judul', 'Foto Potret Uji')->firstOrFail();
        $this->catat($potret->gambar);
        $this->assertFalse((bool) $potret->untuk_hero);

        $a->post('/admin/konten/galeri', ['foto' => UploadedFile::fake()->createWithContent('x.php', '<?php echo 1;'), 'judul' => 'Jahat'])
            ->assertSessionHasErrors('foto');
        $a->post('/admin/konten/galeri', ['judul' => 'Tanpa foto'])->assertSessionHasErrors('foto');

        // berkas masih dipakai eskul → tidak boleh terhapus saat baris galeri dihapus
        $a->post('/admin/konten/ekstrakurikuler', ['nama' => 'Eskul Uji', 'ikon' => 'fa-star', 'foto_pilih' => basename($besar->gambar)])->assertSessionHasNoErrors();
        $a->delete('/admin/konten/galeri/'.$besar->id);
        $this->assertFileExists(public_path($besar->gambar));

        $eskul = Ekstrakurikuler::where('nama', 'Eskul Uji')->firstOrFail();
        $a->delete('/admin/konten/ekstrakurikuler/'.$eskul->id);
        $this->assertFileDoesNotExist(public_path($besar->gambar));

        $a->delete('/admin/konten/galeri/'.$potret->id);
        $this->assertFileDoesNotExist(public_path($potret->gambar));
    }

    public function test_ekstrakurikuler_tampil_di_situs_dan_bisa_diubah(): void
    {
        $a = $this->actingAs($this->admin());
        $a->post('/admin/konten/ekstrakurikuler', [
            'nama' => 'Catur Uji', 'ikon' => 'fa-star', 'waktu' => 'Hari Senin', 'pembimbing' => 'Bu Uji',
            'tujuan' => "Melatih strategi\nMelatih sabar", 'target' => 'Ikut lomba', 'profil' => "Kemandirian\nKolaborasi",
        ])->assertSessionHasNoErrors();
        $e = Ekstrakurikuler::where('nama', 'Catur Uji')->firstOrFail();

        $this->get('/unit-pendidikan')->assertOk()->assertSee('Catur Uji')->assertSee('Melatih sabar')->assertSee('Bu Uji');
        $this->get('/')->assertOk()->assertSee('Catur Uji');

        $a->put('/admin/konten/ekstrakurikuler/'.$e->id, ['nama' => 'Catur Uji B', 'ikon' => 'fa-star', 'foto_pilih' => 'tidak-ada.jpg'])
            ->assertSessionHasErrors('foto_pilih');
        $a->put('/admin/konten/ekstrakurikuler/'.$e->id, ['nama' => 'Catur Uji B', 'ikon' => 'fa-star', 'tujuan' => 'Baru'])->assertSessionHasNoErrors();
        $this->assertSame('Catur Uji B', $e->fresh()->nama);

        $a->delete('/admin/konten/ekstrakurikuler/'.$e->id);
        $this->get('/unit-pendidikan')->assertDontSee('Catur Uji B');
    }

    public function test_seeder_tidak_menimpa_perubahan_admin(): void
    {
        $profil = ProfilYayasan::first();
        $profil->update(['visi' => 'Visi hasil edit admin.', 'telepon' => '021-999']);
        Fasilitas::query()->delete();
        Fasilitas::create(['nama' => 'Hanya Satu', 'ikon' => 'fa-star', 'urutan' => 1]);

        $this->seed(\Database\Seeders\KontenSeeder::class);

        $this->assertSame('Visi hasil edit admin.', $profil->fresh()->visi);
        $this->assertSame('021-999', $profil->fresh()->telepon);
        $this->assertSame(1, Fasilitas::count());
    }
}
