<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Gelombang extends Model
{
    protected $table = 'gelombang';

    protected $fillable = [
        'nama', 'tahun_ajaran', 'tgl_buka', 'tgl_tutup', 'tgl_pengumuman',
        'daftar_ulang_mulai', 'daftar_ulang_selesai', 'is_aktif',
    ];

    protected $casts = [
        'tgl_buka' => 'date',
        'tgl_tutup' => 'date',
        'tgl_pengumuman' => 'date',
        'daftar_ulang_mulai' => 'date',
        'daftar_ulang_selesai' => 'date',
        'is_aktif' => 'boolean',
    ];

    public function kuota(): HasMany
    {
        return $this->hasMany(KuotaJenjang::class);
    }

    public function pendaftaran(): HasMany
    {
        return $this->hasMany(Pendaftaran::class);
    }

    public function biaya(): HasMany
    {
        return $this->hasMany(BiayaGelombang::class)->orderBy('urutan')->orderBy('id');
    }

    /** Rincian biaya dikelompokkan per kode jenjang: ['MI' => [['nama', 'tampil', 'gratis']...], 'SMP' => ...]. */
    public function biayaPerJenjang(): array
    {
        $hasil = Jenjang::urut()->pluck('kode')->mapWithKeys(fn ($k) => [$k => []])->all();
        foreach ($this->biaya()->with('jenjang')->get() as $b) {
            $hasil[$b->jenjang->kode][] = ['nama' => $b->nama, 'tampil' => $b->tampil(), 'gratis' => ! $b->nominal];
        }

        return $hasil;
    }

    public function pengumuman(): HasOne
    {
        return $this->hasOne(Pengumuman::class);
    }

    /** Gelombang yang sedang ditandai aktif (satu saja). */
    public static function aktif(): ?self
    {
        return static::where('is_aktif', true)->latest('tgl_buka')->first();
    }

    /** BR-01: formulir hanya bisa dikirim saat aktif dan dalam rentang tanggal. */
    public function sedangDibuka(): bool
    {
        return $this->is_aktif && today()->betweenIncluded($this->tgl_buka, $this->tgl_tutup);
    }

    /** Posisi jadwal terhadap hari ini, untuk informasi di halaman publik: belum | dibuka | ditutup. */
    public function statusJadwal(): string
    {
        if (today()->lt($this->tgl_buka)) {
            return 'belum';
        }

        return today()->lte($this->tgl_tutup) ? 'dibuka' : 'ditutup';
    }

    /** Tanggal berbahasa Indonesia, mis. "10 November 2026". */
    public static function tglId($tanggal): string
    {
        return $tanggal ? $tanggal->copy()->locale('id')->translatedFormat('j F Y') : '-';
    }

    /** Bentuk array yang dipakai view (tanggal berbahasa Indonesia). */
    public static function untukView(?self $g): array
    {
        if (! $g) {
            return [
                'nama' => '-', 'tahun_ajaran' => '-', 'buka' => '-', 'tutup' => '-', 'pengumuman' => '-',
                'pengumuman_iso' => now()->toIso8601String(), 'daftar_ulang' => '-', 'dibuka' => false, 'ada' => false,
            ];
        }

        $f = fn ($d) => $d ? $d->copy()->locale('id')->translatedFormat('j M Y') : '-';
        $ulang = $g->daftar_ulang_mulai
            ? $f($g->daftar_ulang_mulai).($g->daftar_ulang_selesai ? ' – '.$f($g->daftar_ulang_selesai) : '')
            : '-';

        return [
            'ada' => true,
            'nama' => $g->nama,
            'tahun_ajaran' => $g->tahun_ajaran,
            'buka' => $f($g->tgl_buka),
            'tutup' => $f($g->tgl_tutup),
            'pengumuman' => $f($g->tgl_pengumuman),
            'pengumuman_iso' => $g->tgl_pengumuman->copy()->setTime(8, 0)->toIso8601String(),
            'daftar_ulang' => $ulang,
            'dibuka' => $g->sedangDibuka(),
        ];
    }

    /** Hasil seleksi boleh dilihat pendaftar/publik hanya setelah pengumuman diterbitkan (BR-07). */
    public function hasilTerbit(): bool
    {
        return (bool) optional($this->pengumuman)->published_at;
    }
}
