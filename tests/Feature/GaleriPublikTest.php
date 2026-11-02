<?php

namespace Tests\Feature;

use App\Models\Galeri;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Galeri beranda: filter unit lewat URL, penampil foto, dan penandaan unit dari admin. */
class GaleriPublikTest extends TestCase
{
    use DatabaseTransactions;

    private function foto(string $judul, ?string $unit): Galeri
    {
        return Galeri::create([
            'gambar' => 'images/foto/gedung-sekolah.jpg', 'judul' => $judul, 'unit' => $unit, 'keterangan_gambar' => $judul.' (uji)',
            'posisi_gambar' => '50% 50%', 'urutan' => 250, 'untuk_hero' => false,
        ]);
    }

    public function test_filter_unit_menyembunyikan_foto_unit_lain_di_server(): void
    {
        $this->foto('Uji Khusus MI', 'MI');
        $this->foto('Uji Khusus SMP', 'SMP');
        $this->foto('Uji Semua Unit', null);

        $mi = $this->get('/galeri?unit=mi')->assertOk();
        $html = $mi->getContent();
        $this->assertMatchesRegularExpression('/data-unit="SMP"\s+hidden\s*>\s*<button.*?Uji Khusus SMP/s', $html);
        $this->assertDoesNotMatchRegularExpression('/data-unit="MI"\s+hidden/', $html);
        $this->assertDoesNotMatchRegularExpression('/data-unit=""\s+hidden/', $html);
        $mi->assertSee('aria-current="true"', false)->assertSee('Perbesar foto: Uji Khusus MI');

        $semua = $this->get('/galeri?unit=lain')->assertOk()->getContent();
        $this->assertDoesNotMatchRegularExpression('/data-unit="SMP"\s+hidden/', $semua);
    }

    public function test_beranda_hanya_menampilkan_cuplikan_dan_tautan_ke_galeri_penuh(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $this->foto('Uji Cuplikan '.$i, $i % 2 ? 'MI' : 'SMP');
        }

        $beranda = $this->get('/')->assertOk()->assertSee('href="/galeri"', false)->assertDontSee('gl-filter', false);
        $this->assertSame(8, substr_count($beranda->getContent(), 'class="gl-open"'));

        $penuh = $this->get('/galeri')->assertOk()->assertSee('gl-filter', false);
        $this->assertSame(Galeri::count(), substr_count($penuh->getContent(), 'class="gl-open"'));
    }

    public function test_admin_menandai_unit_foto(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $g = $this->foto('Uji Tandai', null);

        $this->actingAs($admin)->put('/admin/konten/galeri/'.$g->id, ['judul' => 'Uji Tandai', 'unit' => 'MI'])->assertSessionHasNoErrors();
        $this->assertSame('MI', $g->fresh()->unit);

        $this->actingAs($admin)->put('/admin/konten/galeri/'.$g->id, ['judul' => 'Uji Tandai', 'unit' => 'TK'])->assertSessionHasErrors('unit');
        $this->actingAs($admin)->get('/admin/konten/galeri')->assertOk()->assertSee('Unit');
    }
}
