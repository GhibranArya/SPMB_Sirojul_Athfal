<?php

namespace App\Http\Controllers;

use App\Models\BiayaGelombang;
use App\Models\Gelombang;
use App\Models\JenisBerkas;
use App\Models\Jenjang;
use App\Models\Pendaftaran;
use App\Models\Pengumuman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    private function aktif(): ?Gelombang
    {
        return Gelombang::aktif();
    }

    /**
     * Gelombang yang sedang dikelola: pilihan admin (?gelombang=ID atau gelombang_id), bila kosong gelombang aktif.
     * Dengan begitu pendaftar di gelombang yang tidak lagi aktif tetap bisa diverifikasi dan ditetapkan hasilnya.
     */
    private function dipilih(Request $request): ?Gelombang
    {
        $id = $request->input('gelombang', $request->input('gelombang_id'));

        return ($id ? Gelombang::find($id) : null) ?? $this->aktif();
    }

    /** Daftar gelombang untuk pilihan di halaman admin, beserta jumlah pendaftar terkirim. */
    private function pilihanGelombang(): array
    {
        return Gelombang::withCount(['pendaftaran as terkirim_count' => fn ($q) => $q->whereNotNull('submitted_at')])
            ->orderByDesc('tgl_buka')->get()
            ->map(fn (Gelombang $g) => [
                'id' => $g->id,
                'label' => $g->nama.' · TA '.$g->tahun_ajaran.($g->is_aktif ? ' (aktif)' : '').' · '.$g->terkirim_count.' pendaftar',
            ])->all();
    }

    /** Pendaftaran terkirim pada gelombang yang dipilih (kosong bila tidak ada gelombang). */
    private function daftarGelombang(?Gelombang $g, array $with = [])
    {
        return Pendaftaran::with(array_merge(['jenjang', 'user'], $with))
            ->whereNotNull('submitted_at')
            ->when($g, fn ($q) => $q->where('gelombang_id', $g->id), fn ($q) => $q->whereRaw('1 = 0'))
            ->orderByDesc('submitted_at');
    }

    // ------------------------------------------------------------- Pendaftar

    public function pendaftar(Request $request)
    {
        $g = $this->dipilih($request);
        $rows = $this->daftarGelombang($g, ['berkas.jenis'])->get();

        $list = $rows->map(function (Pendaftaran $p) {
            $byJenis = $p->berkas->keyBy('jenis_berkas_id');
            $jenisAktif = JenisBerkas::aktif($p->jenjang->kode); // berkas yang berlaku untuk jenjang pendaftar
            $idWajib = $jenisAktif->where('wajib', true)->pluck('id');

            return [
                'id' => $p->id,
                'kode' => $p->kode,
                'nama' => $p->nama_siswa,
                'jenjang' => $p->jenjang->kode,
                'status' => $p->status,
                'tgl' => $p->submitted_at->locale('id')->translatedFormat('j M Y'),
                'berkas' => $p->berkas->whereIn('jenis_berkas_id', $idWajib)->count(),
                'wajib' => $idWajib->count(),
                'wali' => $p->nama_ibu ?: $p->nama_ayah,
                'hp' => $p->hp_ortu,
                'catatan' => $p->catatan,
                // pasangan [label, isi] untuk modal Periksa; kosong tampil "—"
                'siswa' => [
                    ['Jenjang', $p->jenjang->nama],
                    ['NIK', $p->nik],
                    ['Jenis kelamin', ['L' => 'Laki-laki', 'P' => 'Perempuan'][$p->jenis_kelamin] ?? null],
                    ['Tempat, tanggal lahir', collect([$p->tempat_lahir, $p->tgl_lahir?->locale('id')->translatedFormat('j F Y')])->filter()->implode(', ')],
                    ['Asal sekolah', $p->asal_sekolah],
                    ['Alamat', $p->alamat_siswa],
                    ['Tanggal daftar', $p->submitted_at->locale('id')->translatedFormat('j F Y, H.i')],
                ],
                'ortu' => [
                    ['Ayah', $p->nama_ayah],
                    ['Ibu', $p->nama_ibu],
                    ['Pekerjaan', $p->pekerjaan_ortu],
                    ['No. HP', $p->hp_ortu],
                    ['Alamat', $p->alamat_ortu],
                ],
                'docs' => $jenisAktif->map(fn ($j) => [
                    'id' => $byJenis[$j->id]->id ?? null,
                    'nama' => $j->nama,
                    'wajib' => $j->wajib,
                    'status' => $byJenis[$j->id]->status ?? 'kosong',
                    'catatan' => $byJenis[$j->id]->catatan ?? null,
                ])->values()->all(),
            ];
        })->all();

        $hitung = fn ($s) => collect($list)->where('status', $s)->count();

        return view('admin.pendaftar', [
            'list' => $list,
            'stat' => [
                'total' => count($list),
                'menunggu' => $hitung('menunggu'),
                'perbaikan' => $hitung('perbaikan'),
                'terverifikasi' => $hitung('terverifikasi'),
                'diterima' => $hitung('diterima'),
            ],
            'status' => Pendaftaran::STATUS,
            'jenjang' => Jenjang::pluck('nama', 'kode')->all(),
            'pilihan' => $this->pilihanGelombang(),
            'dipilih' => $g?->id,
        ]);
    }

    public function verifikasi(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $data = $request->validate([
            'aksi' => ['required', 'in:setujui,perbaikan'],
            'catatan' => ['required_if:aksi,perbaikan', 'nullable', 'string', 'max:1000'],
            'berkas_ditolak' => ['nullable', 'array'],
            'berkas_ditolak.*' => ['integer'],
        ], ['catatan.required_if' => 'Catatan perbaikan wajib diisi (BR-05).']);

        if ($pendaftaran->status !== 'menunggu') {
            return back()->with('error', 'Hanya pendaftar berstatus "Menunggu Verifikasi" yang dapat diperiksa.');
        }

        $adminId = $request->user()->id;

        DB::transaction(function () use ($data, $pendaftaran, $adminId) {
            if ($data['aksi'] === 'setujui') {
                $pendaftaran->berkas()->update(['status' => 'valid', 'catatan' => null]);
                $pendaftaran->update(['catatan' => null]);
                $pendaftaran->ubahStatus('terverifikasi', 'Data dan berkas sesuai', $adminId);

                return;
            }

            $pendaftaran->berkas()->whereIn('id', $data['berkas_ditolak'] ?? [])
                ->update(['status' => 'ditolak', 'catatan' => $data['catatan']]);
            $pendaftaran->update(['catatan' => $data['catatan']]);
            $pendaftaran->ubahStatus('perbaikan', $data['catatan'], $adminId);
        });

        return back()->with('status', $data['aksi'] === 'setujui'
            ? $pendaftaran->nama_siswa.' diverifikasi.'
            : 'Permintaan perbaikan untuk '.$pendaftaran->nama_siswa.' dikirim.');
    }

    /**
     * Reset kata sandi akun wali oleh panitia (tanpa email): dibuatkan sandi sementara acak yang ditampilkan sekali,
     * lalu wali wajib menggantinya saat masuk berikutnya. Panitia tidak pernah mengetahui sandi akhir.
     */
    public function resetSandi(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $akun = $pendaftaran->user;
        if (! $akun || $akun->isAdmin()) {
            return back()->with('error', 'Akun wali tidak ditemukan.');
        }

        $alfabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789'; // tanpa 0/O/1/l/I yang membingungkan
        do {
            $sandi = '';
            for ($i = 0; $i < 10; $i++) {
                $sandi .= $alfabet[random_int(0, strlen($alfabet) - 1)];
            }
        } while (! preg_match('/[A-Za-z]/', $sandi) || ! preg_match('/\d/', $sandi));

        $akun->forceFill(['password' => $sandi, 'must_change_password' => true])->save();
        $pendaftaran->riwayat()->create([
            'user_id' => $request->user()->id, 'status_lama' => $pendaftaran->status, 'status_baru' => $pendaftaran->status,
            'keterangan' => 'Panitia mereset kata sandi akun wali',
        ]);

        $hp = preg_replace('/\D+/', '', (string) ($akun->phone ?: $pendaftaran->hp_ortu));
        $hp = str_starts_with($hp, '0') ? '62'.substr($hp, 1) : $hp;

        return back()->with('sandi_sementara', [
            'nama' => $akun->name, 'email' => $akun->email, 'phone' => $akun->phone, 'sandi' => $sandi, 'wa' => $hp,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $rows = $this->daftarGelombang($this->dipilih($request))->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
            fputcsv($out, ['Kode', 'Nama Siswa', 'NIK', 'Status', 'Tgl Kirim', 'Orang Tua', 'No. HP']);
            foreach ($rows as $p) {
                fputcsv($out, [
                    $p->kode, $p->nama_siswa, "'".$p->nik, Pendaftaran::STATUS[$p->status][0],
                    $p->submitted_at->format('Y-m-d H:i'), $p->nama_ibu ?: $p->nama_ayah, "'".$p->hp_ortu,
                ]);
            }
            fclose($out);
        }, 'pendaftar-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // -------------------------------------------------------------- Gelombang

    public function gelombang()
    {
        $jenjang = Jenjang::urut()->get();

        $list = Gelombang::with(['kuota', 'biaya'])->withCount(['pendaftaran as terkirim_count' => fn ($q) => $q->whereNotNull('submitted_at')])
            ->orderByDesc('tgl_buka')->get()
            ->map(fn (Gelombang $g) => [
                'id' => $g->id, 'nama' => $g->nama, 'ta' => $g->tahun_ajaran,
                'buka' => $g->tgl_buka->toDateString(), 'tutup' => $g->tgl_tutup->toDateString(),
                'pengumuman' => $g->tgl_pengumuman->toDateString(),
                'ulang_mulai' => $g->daftar_ulang_mulai?->toDateString(), 'ulang_selesai' => $g->daftar_ulang_selesai?->toDateString(),
                'aktif' => $g->is_aktif, 'dibuka' => $g->sedangDibuka(), 'jadwal' => $g->tgl_buka->isFuture(),
                'pendaftar' => $g->terkirim_count,
                'kuota' => $jenjang->mapWithKeys(fn ($j) => [$j->kode => (int) ($g->kuota->firstWhere('jenjang_id', $j->id)->kuota ?? 0)])->all(),
                'biaya' => $jenjang->mapWithKeys(fn ($j) => [$j->kode => BiayaGelombang::keBaris($g->biaya->where('jenjang_id', $j->id)->sortBy('urutan'))])->all(),
            ])->all();

        return view('admin.gelombang', ['gelombang' => $list, 'jenjang' => $jenjang->pluck('nama', 'kode')->all()]);
    }

    private function validasiGelombang(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:60'],
            'tahun_ajaran' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'tgl_buka' => ['required', 'date'],
            'tgl_tutup' => ['required', 'date', 'after_or_equal:tgl_buka'],
            'tgl_pengumuman' => ['required', 'date', 'after:tgl_tutup'],
            'daftar_ulang_mulai' => ['nullable', 'date', 'after_or_equal:tgl_pengumuman'],
            'daftar_ulang_selesai' => ['nullable', 'date', 'after_or_equal:daftar_ulang_mulai'],
            'kuota' => ['required', 'array'],
            'kuota.*' => ['required', 'integer', 'min:0', 'max:5000'],
            'biaya_ada' => ['nullable', 'array'],   // penanda: jenjang ini ikut dikirim dari editor (walau semua barisnya dihapus)
            'biaya_ada.*' => ['nullable', 'in:1'],
            'biaya' => ['nullable', 'array'],
            'biaya.*' => ['nullable', 'array'],
            'biaya.*.*.nama' => ['nullable', 'string', 'max:100'],
            'biaya.*.*.nominal' => ['nullable', 'integer', 'min:1', 'max:99999999'],
            'biaya.*.*.gratis' => ['nullable', 'boolean'],
        ], [
            'tahun_ajaran.regex' => 'Format tahun ajaran: 2026/2027.',
            'tgl_tutup.after_or_equal' => 'Tanggal tutup tidak boleh sebelum tanggal buka.',
            'tgl_pengumuman.after' => 'Tanggal pengumuman harus setelah pendaftaran ditutup.',
            'biaya.*.*.nominal.integer' => 'Nominal biaya harus berupa angka tanpa titik atau huruf.',
            'biaya.*.*.nominal.min' => 'Nominal biaya minimal 1. Untuk biaya tanpa bayar, centang Gratis.',
        ]);

        // Tiap baris biaya harus lengkap: nama + (nominal atau Gratis). Baris yang sama sekali kosong diabaikan.
        $validator->after(function ($v) use ($request) {
            foreach ((array) $request->input('biaya', []) as $kode => $baris) {
                foreach ((array) $baris as $i => $b) {
                    $b = is_array($b) ? $b : [];
                    $nama = trim((string) ($b['nama'] ?? ''));
                    $adaNominal = trim((string) ($b['nominal'] ?? '')) !== '';
                    $gratis = ! empty($b['gratis']);

                    if ($nama !== '' && ! $adaNominal && ! $gratis) {
                        $v->errors()->add("biaya.$kode.$i.nominal", "Biaya $kode \"$nama\": isi nominal atau centang Gratis.");
                    } elseif ($nama === '' && ($adaNominal || $gratis)) {
                        $v->errors()->add("biaya.$kode.$i.nama", "Biaya $kode: ada baris yang belum diberi nama.");
                    }
                }
            }
        });

        return $validator->validate();
    }

    private function simpanGelombang(Gelombang $g, array $d, bool $aktif): void
    {
        DB::transaction(function () use ($g, $d, $aktif) {
            $g->fill([
                'nama' => $d['nama'], 'tahun_ajaran' => $d['tahun_ajaran'], 'tgl_buka' => $d['tgl_buka'],
                'tgl_tutup' => $d['tgl_tutup'], 'tgl_pengumuman' => $d['tgl_pengumuman'],
                'daftar_ulang_mulai' => $d['daftar_ulang_mulai'] ?? null, 'daftar_ulang_selesai' => $d['daftar_ulang_selesai'] ?? null,
                'is_aktif' => $aktif,
            ])->save();

            if ($aktif) { // hanya satu gelombang aktif
                Gelombang::where('id', '!=', $g->id)->update(['is_aktif' => false]);
            }

            foreach (Jenjang::pluck('id', 'kode') as $kode => $jid) {
                $g->kuota()->updateOrCreate(['jenjang_id' => $jid], ['kuota' => (int) ($d['kuota'][$kode] ?? 0)]);

                if (! empty($d['biaya_ada'][$kode])) { // rincian biaya diganti sesuai isian editor
                    $g->biaya()->where('jenjang_id', $jid)->delete();
                    foreach (BiayaGelombang::dariBaris($d['biaya'][$kode] ?? []) as $i => $b) {
                        $g->biaya()->create(['jenjang_id' => $jid, 'nama' => $b['nama'], 'nominal' => $b['nominal'], 'urutan' => $i + 1]);
                    }
                }
            }
        });
    }

    public function simpanGelombangBaru(Request $request): RedirectResponse
    {
        $this->simpanGelombang(new Gelombang, $this->validasiGelombang($request), $request->boolean('is_aktif'));

        return redirect('/admin/gelombang')->with('status', 'Gelombang ditambahkan.');
    }

    public function ubahGelombang(Request $request, Gelombang $gelombang): RedirectResponse
    {
        $this->simpanGelombang($gelombang, $this->validasiGelombang($request), $request->boolean('is_aktif'));

        return redirect('/admin/gelombang')->with('status', 'Gelombang diperbarui.');
    }

    // ---------------------------------------------------------- Hasil seleksi

    public function hasilSeleksi(Request $request)
    {
        $g = $this->dipilih($request);
        $semua = $this->daftarGelombang($g)->get();
        $terbit = (bool) ($g && $g->hasilTerbit());

        $list = $semua->whereIn('status', ['terverifikasi', 'diterima', 'ditolak'])->map(fn (Pendaftaran $p) => [
            'id' => $p->id, 'kode' => $p->kode, 'nama' => $p->nama_siswa, 'jenjang' => $p->jenjang->kode,
            'status' => $p->status, 'wali' => $p->nama_ibu ?: $p->nama_ayah,
        ])->values()->all();

        $kuota = [];
        if ($g) {
            foreach (Jenjang::urut()->get() as $j) {
                $kuota[$j->kode] = [
                    'kuota' => (int) ($g->kuota()->where('jenjang_id', $j->id)->value('kuota') ?? 0),
                    'diterima' => $semua->where('jenjang_id', $j->id)->where('status', 'diterima')->count(),
                ];
            }
        }

        return view('admin.hasil-seleksi', [
            'list' => $list,
            'kuota' => $kuota,
            'status' => Pendaftaran::STATUS,
            'jenjang' => Jenjang::pluck('nama', 'kode')->all(),
            'gelombang' => Gelombang::untukView($g),
            'terbit' => $terbit,
            'menunggu' => $semua->where('status', 'menunggu')->count(),
            'belumSelesai' => [
                'Menunggu Verifikasi' => $semua->where('status', 'menunggu')->count(),
                'Perlu Perbaikan' => $semua->where('status', 'perbaikan')->count(),
                'Draft (berkas belum lengkap)' => $semua->where('status', 'draft')->count(),
            ],
            'pilihan' => $this->pilihanGelombang(),
            'dipilih' => $g?->id,
        ]);
    }

    public function simpanHasil(Request $request): RedirectResponse
    {
        $g = $this->dipilih($request);
        if (! $g) {
            return back()->with('error', 'Tidak ada gelombang yang dipilih.');
        }
        if ($g->hasilTerbit()) {
            return back()->with('error', 'Pengumuman sudah dipublikasikan, hasil tidak dapat diubah.');
        }

        $input = collect($request->validate([
            'hasil' => ['required', 'array'],
            'hasil.*' => ['in:terverifikasi,diterima,ditolak'],
        ])['hasil'])->mapWithKeys(fn ($v, $k) => [(int) $k => $v])->all();
        $diubah = 0;

        DB::transaction(function () use ($input, $request, $g, &$diubah) {
            foreach (Pendaftaran::whereIn('id', array_keys($input))->where('gelombang_id', $g->id)->whereIn('status', ['terverifikasi', 'diterima', 'ditolak'])->get() as $p) {
                $baru = $input[$p->id];
                if ($baru !== $p->status) {
                    $p->ubahStatus($baru, $baru === 'terverifikasi' ? 'Hasil dibatalkan' : 'Hasil seleksi ditetapkan', $request->user()->id);
                    $diubah++;
                }
            }
        });

        // FR-37: peringatan kuota terlampaui
        $lewat = [];
        if ($g) {
            foreach (Jenjang::all() as $j) {
                $kuota = (int) ($g->kuota()->where('jenjang_id', $j->id)->value('kuota') ?? 0);
                $n = Pendaftaran::where('gelombang_id', $g->id)->where('jenjang_id', $j->id)->where('status', 'diterima')->count();
                if ($n > $kuota) {
                    $lewat[] = $j->kode.' ('.$n.'/'.$kuota.')';
                }
            }
        }

        $pesan = $diubah ? $diubah.' hasil seleksi disimpan.' : 'Tidak ada perubahan.';

        return back()->with('status', $pesan)->with('warning', $lewat ? 'Jumlah Diterima melebihi kuota: '.implode(', ', $lewat).'.' : null);
    }

    public function publikasi(Request $request): RedirectResponse
    {
        $g = $this->dipilih($request);
        if (! $g) {
            return back()->with('error', 'Tidak ada gelombang yang dipilih.');
        }
        if ($g->hasilTerbit()) {
            return back()->with('error', 'Pengumuman sudah dipublikasikan.');
        }

        $sisa = Pendaftaran::where('gelombang_id', $g->id)->where('status', 'terverifikasi')->count();
        if ($sisa > 0) {
            return back()->with('error', $sisa.' pendaftar terverifikasi belum ditetapkan hasilnya. Tetapkan dulu sebelum mempublikasikan.');
        }

        Pengumuman::updateOrCreate(['gelombang_id' => $g->id], [
            'judul' => 'Hasil seleksi '.config('sekolah.spmb').' '.$g->tahun_ajaran.' '.$g->nama,
            'published_at' => now(),
        ]);

        return back()->with('status', 'Pengumuman dipublikasikan. Pendaftar kini dapat melihat hasilnya.');
    }
}
