<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use App\Models\Fasilitas;
use App\Models\Galeri;
use App\Models\ProfilYayasan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Pengelolaan konten halaman publik dari panel admin: profil & kontak, fasilitas, galeri, ekstrakurikuler. */
class KontenController extends Controller
{
    /** Ikon yang boleh dipilih (kelas Font Awesome 6 => nama yang dibaca panitia). */
    public const IKON = [
        'fa-chalkboard' => 'Papan tulis (ruang kelas)',
        'fa-user-tie' => 'Pimpinan (ruang kepala sekolah)',
        'fa-people-group' => 'Sekelompok orang (ruang guru)',
        'fa-folder-open' => 'Map terbuka (tata usaha)',
        'fa-book-open' => 'Buku terbuka (perpustakaan)',
        'fa-book' => 'Buku',
        'fa-futbol' => 'Bola (lapangan, futsal)',
        'fa-basketball' => 'Bola basket',
        'fa-volleyball' => 'Bola voli',
        'fa-person-running' => 'Orang berlari (olahraga)',
        'fa-seedling' => 'Tunas (pertanian)',
        'fa-tree' => 'Pohon (taman)',
        'fa-restroom' => 'Toilet',
        'fa-utensils' => 'Sendok garpu (kantin)',
        'fa-warehouse' => 'Gudang',
        'fa-square-parking' => 'Parkir',
        'fa-tv' => 'Layar (perangkat digital)',
        'fa-computer' => 'Komputer',
        'fa-flask' => 'Labu kimia (laboratorium)',
        'fa-microscope' => 'Mikroskop',
        'fa-mosque' => 'Masjid',
        'fa-school' => 'Gedung sekolah',
        'fa-graduation-cap' => 'Toga',
        'fa-campground' => 'Tenda (Pramuka)',
        'fa-masks-theater' => 'Topeng teater (seni tari)',
        'fa-hand-fist' => 'Kepalan tangan (bela diri)',
        'fa-robot' => 'Robot',
        'fa-music' => 'Musik',
        'fa-guitar' => 'Gitar',
        'fa-microphone' => 'Mikrofon',
        'fa-palette' => 'Palet warna (seni rupa)',
        'fa-code' => 'Kode (pemrograman)',
        'fa-medal' => 'Medali',
        'fa-heart-pulse' => 'Kesehatan',
        'fa-star' => 'Bintang (umum)',
    ];

    private const FOTO_DIR = 'images/foto';

    // ------------------------------------------------------------------ Profil

    public function profil()
    {
        return view('admin.konten-profil', ['profil' => ProfilYayasan::first() ?? new ProfilYayasan]);
    }

    public function simpanProfil(Request $request): RedirectResponse
    {
        $kontak = ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'];
        $data = $request->validate([
            'visi' => ['required', 'string', 'max:1000'],
            'misi' => ['required', 'string', 'max:6000'],
            'alamat' => ['required', 'string', 'max:255'],
            'npsn' => ['nullable', 'string', 'max:20'],
            'telepon' => $kontak,
            'whatsapp' => $kontak,
            'email' => ['nullable', 'email', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'tiktok' => ['nullable', 'url', 'max:255'],
            'facebook' => ['nullable', 'url', 'max:255'],
            'youtube' => ['nullable', 'url', 'max:255'],
        ], [
            'required' => 'Kolom ini wajib diisi.',
            'regex' => 'Hanya angka, spasi, +, - dan tanda kurung.',
            'email.email' => 'Format email belum benar.',
            'url' => 'Isi dengan alamat lengkap, diawali https:// (mis. https://www.instagram.com/akun_sekolah).',
        ]);

        $profil = ProfilYayasan::first() ?? new ProfilYayasan;
        $profil->fill($data)->save();

        return back()->with('status', 'Profil dan kontak disimpan. Perubahan langsung tampil di situs.');
    }

    // --------------------------------------------------------------- Fasilitas

    public function fasilitas()
    {
        $list = Fasilitas::orderBy('urutan')->orderBy('id')->get();

        return view('admin.konten-fasilitas', ['list' => $list, 'ikon' => self::IKON]);
    }

    private function aturanFasilitas(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'ikon' => ['required', Rule::in(array_keys(self::IKON))],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:250'],
        ], ['required' => 'Kolom ini wajib diisi.', 'ikon.in' => 'Pilih ikon dari daftar.']);
    }

    public function simpanFasilitas(Request $request): RedirectResponse
    {
        $d = $this->aturanFasilitas($request);
        Fasilitas::create(['nama' => $d['nama'], 'ikon' => $d['ikon'], 'urutan' => $d['urutan'] ?? $this->urutanBerikut(Fasilitas::class)]);

        return redirect('/admin/konten/fasilitas')->with('status', 'Fasilitas ditambahkan.');
    }

    public function ubahFasilitas(Request $request, Fasilitas $fasilitas): RedirectResponse
    {
        $d = $this->aturanFasilitas($request);
        $fasilitas->update(['nama' => $d['nama'], 'ikon' => $d['ikon'], 'urutan' => $d['urutan'] ?? $fasilitas->urutan]);

        return redirect('/admin/konten/fasilitas')->with('status', 'Fasilitas diperbarui.');
    }

    public function hapusFasilitas(Fasilitas $fasilitas): RedirectResponse
    {
        $nama = $fasilitas->nama;
        $fasilitas->delete();

        return redirect('/admin/konten/fasilitas')->with('status', '"'.$nama.'" dihapus dari daftar fasilitas.');
    }

    // ------------------------------------------------------------------ Galeri

    public function galeri()
    {
        $list = Galeri::orderBy('urutan')->orderBy('id')->get()->map(function (Galeri $g) {
            [$fx, $fy] = $this->pecahPosisi($g->posisi_gambar);

            return [
                'id' => $g->id, 'judul' => $g->judul, 'unit' => $g->unit, 'keterangan_gambar' => $g->keterangan_gambar, 'urutan' => $g->urutan,
                'untuk_hero' => (bool) $g->untuk_hero, 'gambar' => $g->gambar, 'fx' => $fx, 'fy' => $fy,
                'layak_hero' => $this->layakHero(public_path($g->gambar)),
            ];
        })->all();

        return view('admin.konten-galeri', ['list' => $list]);
    }

    private function aturanGaleri(Request $request, bool $baru): array
    {
        return $request->validate([
            'foto' => [$baru ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'judul' => ['required', 'string', 'max:120'],
            'unit' => ['nullable', Rule::in(['MI', 'SMP'])],
            'keterangan_gambar' => ['nullable', 'string', 'max:255'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:250'],
            'fx' => ['nullable', 'integer', 'min:0', 'max:100'],
            'fy' => ['nullable', 'integer', 'min:0', 'max:100'],
        ], [
            'required' => 'Kolom ini wajib diisi.',
            'foto.required' => 'Pilih foto yang akan diunggah.',
            'foto.image' => 'Berkas harus berupa gambar.',
            'foto.mimes' => 'Format foto harus JPG, PNG, atau WebP.',
            'foto.max' => 'Ukuran foto maksimal 5 MB.',
            'foto.uploaded' => 'Foto gagal diunggah. Pastikan ukurannya maksimal 5 MB.',
        ]);
    }

    public function simpanGaleri(Request $request): RedirectResponse
    {
        $d = $this->aturanGaleri($request, true);
        $path = self::FOTO_DIR.'/'.$this->simpanFoto($request->file('foto'), $d['judul']);

        $hero = $request->boolean('untuk_hero');
        $layak = $this->layakHero(public_path($path));

        Galeri::create([
            'gambar' => $path, 'judul' => $d['judul'], 'unit' => $d['unit'] ?? null, 'keterangan_gambar' => $d['keterangan_gambar'] ?? null,
            'posisi_gambar' => $this->posisi($d), 'urutan' => $d['urutan'] ?? $this->urutanBerikut(Galeri::class),
            'untuk_hero' => $hero && $layak,
        ]);

        $pesan = redirect('/admin/konten/galeri')->with('status', 'Foto ditambahkan ke galeri.');

        return $hero && ! $layak ? $pesan->with('warning', $this->pesanHeroTidakLayak()) : $pesan;
    }

    public function ubahGaleri(Request $request, Galeri $galeri): RedirectResponse
    {
        $d = $this->aturanGaleri($request, false);
        $lama = $galeri->gambar;
        $path = $lama;

        if ($request->hasFile('foto')) {
            $path = self::FOTO_DIR.'/'.$this->simpanFoto($request->file('foto'), $d['judul']);
        }

        $hero = $request->boolean('untuk_hero');
        $layak = $this->layakHero(public_path($path));

        $galeri->update([
            'gambar' => $path, 'judul' => $d['judul'], 'unit' => $d['unit'] ?? null, 'keterangan_gambar' => $d['keterangan_gambar'] ?? null,
            'posisi_gambar' => $this->posisi($d), 'urutan' => $d['urutan'] ?? $galeri->urutan,
            'untuk_hero' => $hero && $layak,
        ]);

        if ($path !== $lama) {
            $this->hapusFotoBilaTakTerpakai(basename($lama));
        }

        $pesan = redirect('/admin/konten/galeri')->with('status', 'Foto galeri diperbarui.');

        return $hero && ! $layak ? $pesan->with('warning', $this->pesanHeroTidakLayak()) : $pesan;
    }

    public function hapusGaleri(Galeri $galeri): RedirectResponse
    {
        $judul = $galeri->judul;
        $berkas = basename($galeri->gambar);
        $galeri->delete();
        $this->hapusFotoBilaTakTerpakai($berkas);

        return redirect('/admin/konten/galeri')->with('status', 'Foto "'.$judul.'" dihapus dari galeri.');
    }

    // --------------------------------------------------------- Ekstrakurikuler

    public function ekstrakurikuler()
    {
        $list = Ekstrakurikuler::orderBy('urutan')->orderBy('id')->get()->map(function (Ekstrakurikuler $e) {
            [$fx, $fy] = $this->pecahPosisi($e->posisi_foto);

            return [
                'id' => $e->id, 'nama' => $e->nama, 'ikon' => $e->ikon, 'waktu' => $e->waktu, 'pembimbing' => $e->pembimbing,
                'foto_pilih' => $e->foto, 'fx' => $fx, 'fy' => $fy, 'urutan' => $e->urutan,
                'tujuan' => $e->tujuan, 'target' => $e->target, 'profil' => $e->profil,
            ];
        })->all();

        return view('admin.konten-ekstrakurikuler', ['list' => $list, 'ikon' => self::IKON, 'fotoTersedia' => $this->fotoTersedia()]);
    }

    private function aturanEskul(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:80'],
            'ikon' => ['required', Rule::in(array_keys(self::IKON))],
            'waktu' => ['nullable', 'string', 'max:60'],
            'pembimbing' => ['nullable', 'string', 'max:120'],
            'tujuan' => ['nullable', 'string', 'max:4000'],
            'target' => ['nullable', 'string', 'max:4000'],
            'profil' => ['nullable', 'string', 'max:1000'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:250'],
            'foto_pilih' => ['nullable', 'string', Rule::in($this->fotoTersedia())],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'fx' => ['nullable', 'integer', 'min:0', 'max:100'],
            'fy' => ['nullable', 'integer', 'min:0', 'max:100'],
        ], [
            'required' => 'Kolom ini wajib diisi.',
            'ikon.in' => 'Pilih ikon dari daftar.',
            'foto_pilih.in' => 'Foto yang dipilih tidak ditemukan.',
            'foto.image' => 'Berkas harus berupa gambar.',
            'foto.mimes' => 'Format foto harus JPG, PNG, atau WebP.',
            'foto.max' => 'Ukuran foto maksimal 5 MB.',
            'foto.uploaded' => 'Foto gagal diunggah. Pastikan ukurannya maksimal 5 MB.',
        ]);
    }

    private function isiEskul(Request $request, array $d, ?string $fotoLama): array
    {
        $foto = $d['foto_pilih'] ?? null;
        if ($request->hasFile('foto')) {
            $foto = $this->simpanFoto($request->file('foto'), $d['nama']);
        }

        return [
            'nama' => $d['nama'], 'ikon' => $d['ikon'], 'waktu' => $d['waktu'] ?? null, 'pembimbing' => $d['pembimbing'] ?? null,
            'tujuan' => $d['tujuan'] ?? null, 'target' => $d['target'] ?? null, 'profil' => $d['profil'] ?? null,
            'foto' => $foto ?: null, 'posisi_foto' => $this->posisi($d),
        ];
    }

    public function simpanEkstrakurikuler(Request $request): RedirectResponse
    {
        $d = $this->aturanEskul($request);
        Ekstrakurikuler::create($this->isiEskul($request, $d, null) + ['urutan' => $d['urutan'] ?? $this->urutanBerikut(Ekstrakurikuler::class)]);

        return redirect('/admin/konten/ekstrakurikuler')->with('status', 'Ekstrakurikuler ditambahkan.');
    }

    public function ubahEkstrakurikuler(Request $request, Ekstrakurikuler $ekstrakurikuler): RedirectResponse
    {
        $d = $this->aturanEskul($request);
        $lama = $ekstrakurikuler->foto;
        $ekstrakurikuler->update($this->isiEskul($request, $d, $lama) + ['urutan' => $d['urutan'] ?? $ekstrakurikuler->urutan]);

        if ($lama && $lama !== $ekstrakurikuler->foto) {
            $this->hapusFotoBilaTakTerpakai($lama);
        }

        return redirect('/admin/konten/ekstrakurikuler')->with('status', 'Ekstrakurikuler diperbarui.');
    }

    public function hapusEkstrakurikuler(Ekstrakurikuler $ekstrakurikuler): RedirectResponse
    {
        $nama = $ekstrakurikuler->nama;
        $foto = $ekstrakurikuler->foto;
        $ekstrakurikuler->delete();
        if ($foto) {
            $this->hapusFotoBilaTakTerpakai($foto);
        }

        return redirect('/admin/konten/ekstrakurikuler')->with('status', '"'.$nama.'" dihapus dari daftar ekstrakurikuler.');
    }

    // ------------------------------------------------------------------ Bantuan

    private function urutanBerikut(string $model): int
    {
        return min(250, (int) $model::max('urutan') + 1);
    }

    /** "62% 50%" → [62, 50] */
    private function pecahPosisi(?string $posisi): array
    {
        preg_match_all('/\d+/', (string) $posisi, $m);

        return [(int) ($m[0][0] ?? 50), (int) ($m[0][1] ?? 50)];
    }

    private function posisi(array $d): string
    {
        return ($d['fx'] ?? 50).'% '.($d['fy'] ?? 50).'%';
    }

    /** Menyimpan foto unggahan ke public/images/foto dengan nama acak; mengembalikan nama berkasnya. */
    private function simpanFoto(UploadedFile $file, string $dasar): string
    {
        $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
        $ext = $ext === 'jpeg' ? 'jpg' : $ext;
        $nama = (Str::slug($dasar) ?: 'foto').'-'.Str::lower(Str::random(6)).'.'.$ext;

        $file->move(public_path(self::FOTO_DIR), $nama);

        return $nama;
    }

    /** Layak jadi latar hero layar penuh: landscape dan lebar minimal 1280 px (di bawah itu pecah saat direntangkan). */
    private function layakHero(string $path): bool
    {
        $info = is_file($path) ? @getimagesize($path) : false;

        return $info && $info[0] >= 1280 && $info[0] > $info[1];
    }

    private function pesanHeroTidakLayak(): string
    {
        return 'Foto disimpan, tetapi tidak dipakai sebagai latar hero karena bukan landscape atau lebarnya kurang dari 1280 px (disarankan 1600 px). Foto tetap tampil di galeri.';
    }

    /** Daftar nama berkas foto yang ada di public/images/foto. */
    private function fotoTersedia(): array
    {
        $dir = public_path(self::FOTO_DIR);
        if (! is_dir($dir)) {
            return [];
        }

        $daftar = array_values(array_filter(scandir($dir), fn ($f) => preg_match('/\.(jpe?g|png|webp)$/i', $f)));
        sort($daftar, SORT_NATURAL | SORT_FLAG_CASE);

        return $daftar;
    }

    /** Berkas foto dihapus dari disk hanya bila tidak dipakai lagi oleh galeri maupun ekstrakurikuler. */
    private function hapusFotoBilaTakTerpakai(string $berkas): void
    {
        $berkas = basename($berkas);
        if ($berkas === '' || Galeri::where('gambar', self::FOTO_DIR.'/'.$berkas)->exists() || Ekstrakurikuler::where('foto', $berkas)->exists()) {
            return;
        }

        $path = public_path(self::FOTO_DIR.'/'.$berkas);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
