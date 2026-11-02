<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pendaftaran extends Model
{
    protected $table = 'pendaftaran';

    /** status => [label, kelas badge] */
    public const STATUS = [
        'draft' => ['Draft', 'st-gray'],
        'menunggu' => ['Menunggu Verifikasi', 'st-gold'],
        'perbaikan' => ['Perlu Perbaikan', 'st-orange'],
        'terverifikasi' => ['Terverifikasi', 'st-blue'],
        'diterima' => ['Diterima', 'st-green'],
        'ditolak' => ['Tidak Diterima', 'st-red'],
    ];

    protected $fillable = [
        'user_id', 'gelombang_id', 'jenjang_id', 'kode', 'status', 'catatan',
        'nama_siswa', 'nik', 'jenis_kelamin', 'tempat_lahir', 'tgl_lahir', 'asal_sekolah', 'alamat_siswa',
        'nama_ayah', 'nama_ibu', 'pekerjaan_ortu', 'hp_ortu', 'alamat_ortu',
        'setuju_data', 'submitted_at',
    ];

    protected $casts = [
        'tgl_lahir' => 'date',
        'setuju_data' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gelombang(): BelongsTo
    {
        return $this->belongsTo(Gelombang::class);
    }

    public function jenjang(): BelongsTo
    {
        return $this->belongsTo(Jenjang::class);
    }

    public function berkas(): HasMany
    {
        return $this->hasMany(Berkas::class);
    }

    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatStatus::class)->orderBy('id');
    }

    public function terkirim(): bool
    {
        return $this->submitted_at !== null;
    }

    /** Ubah status sekaligus mencatat riwayat (BR-09). */
    public function ubahStatus(string $baru, ?string $keterangan = null, ?int $userId = null): void
    {
        $lama = $this->status;
        $this->update(['status' => $baru]);

        $this->riwayat()->create([
            'user_id' => $userId,
            'status_lama' => $lama,
            'status_baru' => $baru,
            'keterangan' => $keterangan,
        ]);
    }

    /** Kode acak tanpa karakter membingungkan (0/O, 1/I); tidak berurutan (BR-03). */
    public static function buatKode(): string
    {
        do {
            $acak = '';
            for ($i = 0; $i < 5; $i++) {
                $acak .= 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'[random_int(0, 31)];
            }
            $kode = 'SA-'.now()->format('y').'-'.$acak;
        } while (static::where('kode', $kode)->exists());

        return $kode;
    }

    /** Berkas wajib yang belum diunggah. */
    public function berkasWajibKurang(): int
    {
        $ada = $this->berkas()->pluck('jenis_berkas_id');

        return $this->berkasWajib()->whereNotIn('id', $ada)->count();
    }

    /** Berkas wajib untuk jenjang pendaftar ini. */
    public function berkasWajib()
    {
        return JenisBerkas::aktif($this->jenjang->kode)->where('wajib', true);
    }

    /**
     * Bentuk array yang dipakai view pendaftar dan PDF.
     * Hasil Diterima/Tidak Diterima disembunyikan sampai pengumuman diterbitkan (BR-07).
     */
    public function untukView(): array
    {
        $this->loadMissing(['jenjang', 'gelombang.pengumuman', 'berkas.jenis', 'riwayat']);
        $terbit = $this->gelombang->hasilTerbit();
        $final = in_array($this->status, ['diterima', 'ditolak'], true);
        $f = fn ($d) => $d->copy()->locale('id')->translatedFormat('j M Y, H.i');

        $berkas = [];
        foreach ($this->berkas as $b) {
            $berkas[$b->jenis->kode] = ['id' => $b->id, 'status' => $b->status, 'file' => $b->nama_asli, 'catatan' => $b->catatan];
        }

        $riwayat = $this->riwayat
            ->reject(fn ($r) => ! $terbit && in_array($r->status_baru, ['diterima', 'ditolak'], true))
            ->map(fn ($r) => [$f($r->created_at), $r->keterangan ?: self::STATUS[$r->status_baru][0], self::STATUS[$r->status_baru][0]])
            ->values()->all();

        return [
            'id' => $this->id,
            'kode' => $this->kode,
            'terkirim' => $this->terkirim(),
            'nama' => $this->nama_siswa,
            'jenjang' => $this->jenjang->kode,
            'gelombang' => $this->gelombang->nama.' · TA '.$this->gelombang->tahun_ajaran,
            'tgl_kirim' => $this->submitted_at?->copy()->locale('id')->translatedFormat('j M Y'),
            'status' => $final && ! $terbit ? 'terverifikasi' : $this->status,
            'hasil' => $final && $terbit ? $this->status : null,
            'catatan' => $this->status === 'perbaikan' ? $this->catatan : null,
            'berkas' => $berkas,
            'riwayat' => $riwayat,
            'siswa' => [
                'nama' => $this->nama_siswa, 'nik' => $this->nik, 'jk' => $this->jenis_kelamin,
                'tempat_lahir' => $this->tempat_lahir, 'tgl_lahir' => $this->tgl_lahir?->toDateString(),
                'asal_sekolah' => $this->asal_sekolah, 'alamat' => $this->alamat_siswa,
            ],
            'ortu' => [
                'ayah' => $this->nama_ayah, 'ibu' => $this->nama_ibu, 'pekerjaan' => $this->pekerjaan_ortu,
                'hp' => $this->hp_ortu, 'alamat' => $this->alamat_ortu,
            ],
        ];
    }
}
