<?php

namespace App\Http\Controllers;

use App\Models\Berkas;
use App\Models\Gelombang;
use App\Models\JenisBerkas;
use App\Models\Jenjang;
use App\Models\Pendaftaran;
use App\Models\ProfilYayasan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PendaftarController extends Controller
{
    /** Status saat pendaftar masih boleh mengubah formulir. */
    private const BISA_UBAH_FORM = ['draft', 'perbaikan'];

    /** Pendaftaran milik pengguna pada gelombang aktif (atau yang terakhir bila gelombang sudah berganti). */
    private function pendaftaranSaya(Request $request): ?Pendaftaran
    {
        $user = $request->user();
        $aktif = Gelombang::aktif();

        return $user->pendaftaran()
            ->when($aktif, fn ($q) => $q->orderByRaw('gelombang_id = ? desc', [$aktif->id]))
            ->latest('id')->first();
    }

    /**
     * Pendaftar yang dinyatakan Tidak Diterima (dan hasilnya sudah terbit) boleh mendaftar lagi pada gelombang aktif yang
     * berbeda dan sedang dibuka, memakai akun yang sama. Pendaftaran lama tetap tersimpan sebagai riwayat.
     */
    private function bolehDaftarLagi(?Pendaftaran $p, ?Gelombang $aktif): bool
    {
        return $p && $aktif
            && $p->status === 'ditolak'
            && $p->gelombang_id !== $aktif->id
            && $p->gelombang->hasilTerbit()
            && $aktif->sedangDibuka();
    }

    /**
     * Pendaftaran Draft (belum lengkap) pada gelombang yang sudah tidak dibuka (lewat tanggal tutup atau dinonaktifkan).
     * Formulir dan berkasnya terkunci; pendaftar dapat melanjutkannya di gelombang aktif yang sedang dibuka.
     */
    private function terlewat(?Pendaftaran $p): bool
    {
        return $p && $p->status === 'draft' && ! $p->gelombang->sedangDibuka();
    }

    private function bolehLanjut(?Pendaftaran $p, ?Gelombang $aktif): bool
    {
        return $this->terlewat($p) && $aktif && $aktif->id !== $p->gelombang_id && $aktif->sedangDibuka();
    }

    /** Pengumuman gelombangnya sudah terbit, jadi pendaftaran yang belum final tidak boleh diubah lagi. */
    private function terkunciPengumuman(?Pendaftaran $p): bool
    {
        return $p && $p->status === 'perbaikan' && $p->gelombang->hasilTerbit();
    }

    private function pesanTerlewat(Pendaftaran $p, ?Gelombang $aktif): string
    {
        $g = $p->gelombang;
        $dasar = $g->nama.' sudah ditutup (batas '.Gelombang::tglId($g->tgl_tutup).'), jadi formulir dan berkas tidak dapat diubah. Pendaftaran Anda tersimpan.';

        return $dasar.($this->bolehLanjut($p, $aktif)
            ? ' Gunakan tombol "Lanjutkan di '.$aktif->nama.'" untuk meneruskannya.'
            : ' Anda dapat melanjutkannya saat gelombang berikutnya dibuka.');
    }

    private function base(Request $request): array
    {
        $p = $this->pendaftaranSaya($request);
        $aktif = Gelombang::aktif();
        $gel = $p ? $p->gelombang : $aktif;
        $ulang = $this->bolehDaftarLagi($p, $aktif);

        return [
            'terlewat' => $this->terlewat($p),
            'lanjutKe' => $this->bolehLanjut($p, $aktif) ? Gelombang::untukView($aktif) : null,
            'terkunciPengumuman' => $this->terkunciPengumuman($p),
            'terbit' => (bool) ($gel && $gel->hasilTerbit()),
            'user' => $request->user(),
            'model' => $p,
            'pendaftaran' => $p?->untukView(),
            'gelombang' => Gelombang::untukView($gel),
            'status' => Pendaftaran::STATUS,
            'daftarLagi' => $ulang ? Gelombang::untukView($aktif) : null,
            'sebelumnya' => $ulang ? ['kode' => $p->kode, 'gelombang' => $p->gelombang->nama.' · TA '.$p->gelombang->tahun_ajaran] : null,
        ];
    }

    public function dashboard(Request $request)
    {
        $data = $this->base($request);

        return view('pendaftar.dashboard', $data + ['jenisBerkas' => JenisBerkas::aktif($data['model']?->jenjang->kode)]);
    }

    // ---------------------------------------------------------------- Form data

    public function formData(Request $request)
    {
        $data = $this->base($request);

        $gel = $data['model']?->gelombang ?? Gelombang::aktif();
        if ($data['daftarLagi']) { // formulir kosong untuk gelombang yang sedang dibuka
            $data['model'] = null;
            $data['pendaftaran'] = null;
            $data['gelombang'] = $data['daftarLagi'];
            $gel = Gelombang::aktif();
        }

        return view('pendaftar.form-data', $data + [
            'daftarJenjang' => Jenjang::urut()->pluck('nama', 'kode')->all(),
            'biaya' => $gel ? $gel->biayaPerJenjang() : [],
        ]);
    }

    public function simpanForm(Request $request): RedirectResponse
    {
        $aksi = $request->input('aksi') === 'kirim' ? 'kirim' : 'draft';
        $p = $this->pendaftaranSaya($request);
        $aktif = Gelombang::aktif();
        if ($this->bolehDaftarLagi($p, $aktif)) {
            $p = null; // daftar lagi: buat pendaftaran baru di gelombang aktif
        }
        $gel = $p ? $p->gelombang : $aktif;

        if ($p && $this->terlewat($p)) {
            return back()->with('error', $this->pesanTerlewat($p, $aktif));
        }
        if ($this->terkunciPengumuman($p)) {
            return back()->with('error', 'Pengumuman '.$gel->nama.' sudah terbit, jadi formulir tidak dapat diubah lagi. Hubungi panitia bila ada kendala.');
        }
        if ($p && ! in_array($p->status, self::BISA_UBAH_FORM, true)) {
            return back()->with('error', 'Formulir sudah diproses panitia dan tidak dapat diubah.');
        }
        // BR-01: pendaftaran baru hanya saat gelombang dibuka
        if (! $p && ! ($gel && $gel->sedangDibuka())) {
            return back()->with('error', 'Pendaftaran sedang ditutup. Silakan menunggu gelombang berikutnya.');
        }
        if ($p && ! $p->terkirim() && ! $gel->sedangDibuka()) {
            return back()->with('error', 'Pendaftaran sudah ditutup, formulir tidak dapat dikirim.');
        }

        $kirim = $aksi === 'kirim';
        $req = $kirim ? 'required' : 'nullable';
        // Jenjang dipilih sebelum formulir dikirim; sesudahnya terkunci karena daftar berkas bergantung padanya.
        $jenjangTetap = $p && $p->terkirim();

        $data = $request->validate([
            'jenjang' => $jenjangTetap ? ['nullable'] : ['required', Rule::in(Jenjang::pluck('kode')->all())],
            'nama' => [$req, 'string', 'max:100'],
            'nik' => [$req, 'digits:16'],
            'jk' => [$req, Rule::in(['L', 'P'])],
            'tempat_lahir' => [$req, 'string', 'max:60'],
            'tgl_lahir' => [$req, 'date', 'before:today'],
            'asal_sekolah' => ['nullable', 'string', 'max:100'],
            'alamat' => [$req, 'string', 'max:500'],
            'ayah' => [$req, 'string', 'max:100'],
            'ibu' => [$req, 'string', 'max:100'],
            'pekerjaan' => ['nullable', 'string', 'max:80'],
            'hp' => [$req, 'regex:/^08\d{8,12}$/'],
            'alamat_ortu' => ['nullable', 'string', 'max:500'],
            'setuju' => $kirim ? ['accepted'] : ['nullable'],
        ], [
            'required' => 'Kolom ini wajib diisi.',
            'jenjang.in' => 'Pilih jenjang MI atau SMP.',
            'nik.digits' => 'NIK harus 16 angka.',
            'tgl_lahir.before' => 'Tanggal lahir tidak valid.',
            'hp.regex' => 'Nomor WhatsApp harus diawali 08 dan terdiri dari 10–14 angka.',
            'setuju.accepted' => 'Centang pernyataan persetujuan untuk mengirim formulir.',
        ]);

        // BR-02: satu NIK satu pendaftaran per tahun ajaran
        if (! empty($data['nik'])) {
            $ganda = Pendaftaran::where('nik', $data['nik'])
                ->whereHas('gelombang', fn ($q) => $q->where('tahun_ajaran', $gel->tahun_ajaran))
                ->where('status', '!=', 'ditolak') // yang Tidak Diterima boleh mendaftar lagi
                ->when($p, fn ($q) => $q->where('id', '!=', $p->id))
                ->exists();
            if ($ganda) {
                return back()->withInput()->withErrors(['nik' => 'NIK ini sudah terdaftar pada tahun ajaran '.$gel->tahun_ajaran.'.']);
            }
        }

        $isi = [
            'jenjang_id' => $jenjangTetap ? $p->jenjang_id : Jenjang::where('kode', $data['jenjang'])->value('id'),
            'nama_siswa' => $data['nama'] ?? null, 'nik' => $data['nik'] ?? null, 'jenis_kelamin' => $data['jk'] ?? null,
            'tempat_lahir' => $data['tempat_lahir'] ?? null, 'tgl_lahir' => $data['tgl_lahir'] ?? null,
            'asal_sekolah' => $data['asal_sekolah'] ?? null, 'alamat_siswa' => $data['alamat'] ?? null,
            'nama_ayah' => $data['ayah'] ?? null, 'nama_ibu' => $data['ibu'] ?? null, 'pekerjaan_ortu' => $data['pekerjaan'] ?? null,
            'hp_ortu' => $data['hp'] ?? null, 'alamat_ortu' => $data['alamat_ortu'] ?? null,
            'setuju_data' => $request->boolean('setuju'),
        ];

        if ($p) {
            $p->update($isi);
        } else {
            $p = $request->user()->pendaftaran()->create($isi + ['gelombang_id' => $gel->id, 'status' => 'draft']);
        }

        if (! $kirim) {
            return redirect('/pendaftar/form-data')->with('status', 'Draft formulir disimpan. Lengkapi lalu kirim sebelum '.$gel->tgl_tutup->locale('id')->translatedFormat('j F Y').'.');
        }

        if (! $p->terkirim()) {
            $p->update(['kode' => Pendaftaran::buatKode(), 'submitted_at' => now()]);
            $p->riwayat()->create(['user_id' => $request->user()->id, 'status_lama' => null, 'status_baru' => 'draft', 'keterangan' => 'Formulir dikirim']);

            return redirect('/pendaftar/upload-berkas')->with('status', 'Formulir terkirim. Kode pendaftaran Anda: '.$p->kode.'. Lanjutkan dengan mengunggah berkas.');
        }

        return redirect('/pendaftar/dashboard')->with('status', 'Perubahan formulir disimpan.');
    }

    // ------------------------------------------------------------ Upload berkas

    public function uploadBerkas(Request $request)
    {
        $p = $this->pendaftaranSaya($request);
        $jenis = JenisBerkas::aktif($p?->jenjang->kode)->map(fn ($j) => ['id' => $j->id, 'key' => $j->kode, 'nama' => $j->nama, 'wajib' => $j->wajib, 'icon' => $j->icon])->all();

        return view('pendaftar.upload-berkas', $this->base($request) + ['jenisBerkas' => $jenis]);
    }

    public function simpanBerkas(Request $request, JenisBerkas $jenis): RedirectResponse
    {
        $p = $this->pendaftaranSaya($request);

        if (! $p || ! $p->terkirim()) {
            return redirect('/pendaftar/form-data')->with('error', 'Kirim formulir lebih dulu sebelum mengunggah berkas.');
        }
        if ($this->terlewat($p)) {
            return back()->with('error', $this->pesanTerlewat($p, Gelombang::aktif()));
        }
        if ($this->terkunciPengumuman($p)) {
            return back()->with('error', 'Pengumuman '.$p->gelombang->nama.' sudah terbit, jadi berkas tidak dapat diubah lagi. Hubungi panitia bila ada kendala.');
        }
        if (! in_array($p->status, self::BISA_UBAH_FORM, true)) {
            return back()->with('error', 'Berkas terkunci karena pendaftaran sedang/sudah diproses panitia.');
        }

        $request->validate(
            ['berkas' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048']],
            [
                'berkas.required' => 'Pilih berkas yang akan diunggah.',
                'berkas.mimes' => 'Format berkas harus PDF, JPG, atau PNG.',
                'berkas.max' => 'Ukuran berkas maksimal 2 MB.',
                'berkas.uploaded' => 'Berkas gagal diunggah. Pastikan ukurannya maksimal 2 MB.',
            ]
        );

        $file = $request->file('berkas');
        $lama = $p->berkas()->where('jenis_berkas_id', $jenis->id)->first();
        if ($lama) {
            Storage::delete($lama->path);
        }

        $p->berkas()->updateOrCreate(['jenis_berkas_id' => $jenis->id], [
            'path' => $file->store("berkas/{$p->id}"), // storage privat
            'nama_asli' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'ukuran' => $file->getSize(),
            'status' => 'menunggu',
            'catatan' => null,
        ]);

        // FR-13: semua berkas wajib lengkap → Menunggu Verifikasi
        if ($p->status === 'draft' && $p->berkasWajibKurang() === 0) {
            $p->ubahStatus('menunggu', 'Semua berkas wajib diunggah', $request->user()->id);

            return back()->with('status', $jenis->nama.' diunggah. Semua berkas wajib lengkap, pendaftaran Anda kini menunggu verifikasi panitia.');
        }

        return back()->with('status', $jenis->nama.' berhasil diunggah.');
    }

    /**
     * Meneruskan pendaftaran Draft dari gelombang yang sudah ditutup ke gelombang aktif yang sedang dibuka.
     * Data formulir, berkas, dan kode pendaftaran ikut terbawa; perpindahan dicatat di riwayat status.
     */
    public function lanjutkanGelombang(Request $request): RedirectResponse
    {
        $p = $this->pendaftaranSaya($request);
        $aktif = Gelombang::aktif();

        if (! $this->bolehLanjut($p, $aktif)) {
            return back()->with('error', 'Belum ada gelombang yang dibuka untuk melanjutkan pendaftaran Anda.');
        }

        if ($p->nik) { // BR-02 pada tahun ajaran gelombang tujuan
            $ganda = Pendaftaran::where('nik', $p->nik)
                ->whereHas('gelombang', fn ($q) => $q->where('tahun_ajaran', $aktif->tahun_ajaran))
                ->where('status', '!=', 'ditolak')
                ->where('id', '!=', $p->id)
                ->exists();
            if ($ganda) {
                return back()->with('error', 'NIK ini sudah terdaftar pada tahun ajaran '.$aktif->tahun_ajaran.', pendaftaran tidak dapat dilanjutkan.');
            }
        }

        $asal = $p->gelombang;
        $p->update(['gelombang_id' => $aktif->id]);
        $p->riwayat()->create([
            'user_id' => $request->user()->id, 'status_lama' => 'draft', 'status_baru' => 'draft',
            'keterangan' => 'Dilanjutkan dari '.$asal->nama.' ke '.$aktif->nama,
        ]);

        return redirect('/pendaftar/dashboard')->with('status', 'Pendaftaran dilanjutkan di '.$aktif->nama.'. Lengkapi formulir dan berkas sebelum '.Gelombang::tglId($aktif->tgl_tutup).'.');
    }

    /** Pendaftar selesai memperbaiki dan mengirim ulang untuk diperiksa. */
    public function kirimPerbaikan(Request $request): RedirectResponse
    {
        $p = $this->pendaftaranSaya($request);

        if (! $p || $p->status !== 'perbaikan') {
            return back()->with('error', 'Tidak ada perbaikan yang perlu dikirim.');
        }
        if ($this->terkunciPengumuman($p)) {
            return back()->with('error', 'Pengumuman '.$p->gelombang->nama.' sudah terbit, perbaikan tidak dapat dikirim lagi. Hubungi panitia bila ada kendala.');
        }
        if ($p->berkas()->where('status', 'ditolak')->exists() || $p->berkasWajibKurang() > 0) {
            return back()->with('error', 'Masih ada berkas yang perlu diunggah ulang atau belum lengkap.');
        }

        $p->ubahStatus('menunggu', 'Pendaftar mengirim perbaikan', $request->user()->id);

        return redirect('/pendaftar/dashboard')->with('status', 'Perbaikan terkirim. Panitia akan memeriksa kembali.');
    }

    // ------------------------------------------------------ Bukti & pengumuman

    /** Unduh bukti pendaftaran (PDF) berisi kode pendaftaran dan data yang sudah diisi. */
    public function buktiPendaftaran(Request $request)
    {
        $model = $this->pendaftaranSaya($request);

        if (! $model || ! $model->terkirim()) {
            return redirect('/pendaftar/dashboard')->with('status', 'Kirim formulir lebih dulu untuk mendapatkan bukti pendaftaran.');
        }

        $p = $model->untukView();

        return Pdf::loadView('pendaftar.bukti-pdf', [
            'p' => $p,
            'sw' => $p['siswa'],
            'ot' => $p['ortu'],
            'g' => Gelombang::untukView($model->gelombang),
            'status' => Pendaftaran::STATUS,
            'jenjang' => Jenjang::pluck('nama', 'kode')->all(),
            'jenisBerkas' => JenisBerkas::aktif($model->jenjang->kode)->map(fn ($j) => ['key' => $j->kode, 'nama' => $j->nama, 'wajib' => $j->wajib])->all(),
            'tglLahir' => Carbon::parse($p['siswa']['tgl_lahir'])->locale('id')->translatedFormat('j F Y'),
            'dicetak' => now()->locale('id')->translatedFormat('j F Y'),
        ])->setPaper('a4')->download('bukti-pendaftaran-'.$p['kode'].'.pdf');
    }

    /** Unduh bukti diterima (PDF). Hanya untuk pendaftar yang dinyatakan Diterima setelah pengumuman dipublikasikan (BR-07). */
    public function buktiDiterima(Request $request)
    {
        $model = $this->pendaftaranSaya($request);
        $p = $model?->untukView();

        if (! $p || $p['hasil'] !== 'diterima') {
            return redirect('/pendaftar/pengumuman')->with('error', 'Bukti diterima hanya tersedia bagi pendaftar yang dinyatakan diterima setelah pengumuman dipublikasikan.');
        }

        return Pdf::loadView('pendaftar.bukti-diterima-pdf', [
            'p' => $p,
            'sw' => $p['siswa'],
            'ot' => $p['ortu'],
            'g' => Gelombang::untukView($model->gelombang),
            'alamat' => ProfilYayasan::first()?->alamat ?: config('sekolah.nama_yayasan'),
            'jenisBerkas' => JenisBerkas::aktif($model->jenjang->kode)->where('wajib', true)->map(fn ($j) => ['nama' => $j->nama])->values()->all(),
            'tglLahir' => Carbon::parse($p['siswa']['tgl_lahir'])->locale('id')->translatedFormat('j F Y'),
            'dicetak' => now()->locale('id')->translatedFormat('j F Y'),
        ])->setPaper('a4')->download('bukti-diterima-'.$p['kode'].'.pdf');
    }

    public function pengumuman(Request $request)
    {
        $data = $this->base($request);
        $gel = $data['model']?->gelombang ?? Gelombang::aktif();

        // Hasil tiap pendaftar hanya tampil di akunnya sendiri; tidak ada daftar publik agar yang belum diterima tidak melihat hasil orang lain.
        return view('pendaftar.pengumuman', $data + ['terbit' => (bool) ($gel && $gel->hasilTerbit())]);
    }

    /** Pratinjau/unduh berkas milik sendiri (admin memakai rute admin). */
    public function berkas(Request $request, Berkas $berkas)
    {
        $milikSaya = $berkas->pendaftaran->user_id === $request->user()->id;
        abort_unless($milikSaya || $request->user()->isStaf(), 403);
        abort_unless(Storage::exists($berkas->path), 404);

        return response()->file(Storage::path($berkas->path), [
            'Content-Type' => $berkas->mime ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.addslashes($berkas->nama_asli).'"',
        ]);
    }
}
