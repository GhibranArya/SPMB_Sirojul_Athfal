<?php

namespace App\Http\Controllers;

use App\Models\Fasilitas;
use App\Models\Galeri;
use App\Models\Gelombang;
use App\Models\JenisBerkas;
use App\Models\Jenjang;
use Illuminate\Http\Request;

/** Halaman publik yang isinya dibaca dari tabel konten. ($profil dibagikan lewat view composer.) */
class PublikController extends Controller
{
    private const FOTO_BERANDA = 8;

    public function beranda()
    {
        $galeri = Galeri::orderBy('urutan')->orderBy('id')->get();

        return view('publik.beranda', [
            'galeri' => $this->cuplikanGaleri($galeri),
            'galeriTotal' => $galeri->count(),
            'hero' => $galeri->where('untuk_hero', true)->values(),   // foto tajam untuk latar layar penuh
            'gelombang' => Gelombang::aktif(),
            'daftarGelombang' => $this->gelombangTahunBerjalan(),
        ]);
    }

    /** Semua foto galeri, dengan filter unit lewat ?unit=mi|smp. */
    public function galeri(Request $request)
    {
        $galeri = Galeri::orderBy('urutan')->orderBy('id')->get();

        return view('publik.galeri', [
            'galeri' => $galeri,
            'galeriTotal' => $galeri->count(),
            'unitGaleri' => in_array($u = strtoupper((string) $request->query('unit')), ['MI', 'SMP'], true) ? $u : null,
        ]);
    }

    /** Beberapa foto untuk beranda, bergantian antara SMP, MI, dan foto semua unit agar kedua unit tampak. */
    private function cuplikanGaleri($galeri)
    {
        $antrean = [$galeri->where('unit', 'SMP')->values(), $galeri->where('unit', 'MI')->values(), $galeri->whereNull('unit')->values()];
        $pilih = collect();
        for ($i = 0; $i < $galeri->count() && $pilih->count() < self::FOTO_BERANDA; $i++) {
            foreach ($antrean as $daftar) {
                if (isset($daftar[$i]) && $pilih->count() < self::FOTO_BERANDA) {
                    $pilih->push($daftar[$i]);
                }
            }
        }

        return $pilih;
    }

    /** Gelombang pada tahun ajaran yang sedang berjalan (gelombang aktif, atau yang terbaru), urut tanggal buka. */
    private function gelombangTahunBerjalan()
    {
        $tahun = Gelombang::aktif()?->tahun_ajaran ?? Gelombang::orderByDesc('tgl_buka')->value('tahun_ajaran');

        return Gelombang::where('tahun_ajaran', $tahun)->orderBy('tgl_buka')->get();
    }

    public function profil()
    {
        return view('publik.profil');
    }

    public function strukturOrganisasi()
    {
        return view('publik.struktur-organisasi', ['s' => config('struktur')]);
    }

    /** Gelombang pada tahun ajaran yang sedang berjalan (gelombang aktif, atau yang terbaru) beserta rincian biayanya. */
    public function biayaPendaftaran()
    {
        $gelombang = $this->gelombangTahunBerjalan();
        $tahun = $gelombang->first()?->tahun_ajaran;

        // Berkas yang diminta per jenjang
        $berkas = [];
        foreach (Jenjang::urut()->pluck('kode') as $kode) {
            $berkas[$kode] = JenisBerkas::aktif($kode)->where('wajib', true)->pluck('nama')->all();
        }

        return view('publik.biaya-pendaftaran', [
            'tahun' => $tahun,
            'gelombang' => $gelombang,
            'biaya' => $gelombang->mapWithKeys(fn (Gelombang $g) => [$g->id => $g->biayaPerJenjang()])->all(),
            'jenjang' => Jenjang::urut()->pluck('nama', 'kode')->all(),
            'berkas' => $berkas,
            // "Jan – Mar 2027"
            'periode' => function (Gelombang $g) {
                $b = $g->tgl_buka->copy()->locale('id');
                $t = $g->tgl_tutup->copy()->locale('id');

                return $b->year === $t->year
                    ? $b->translatedFormat('M').' – '.$t->translatedFormat('M Y')
                    : $b->translatedFormat('M Y').' – '.$t->translatedFormat('M Y');
            },
        ]);
    }

    public function fasilitas()
    {
        return view('publik.fasilitas', ['fasilitas' => Fasilitas::orderBy('urutan')->orderBy('id')->get()]);
    }
}
