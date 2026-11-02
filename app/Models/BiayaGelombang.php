<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Satu baris rincian biaya pada suatu gelombang untuk satu jenjang. Nominal kosong berarti Gratis. */
class BiayaGelombang extends Model
{
    protected $table = 'biaya_gelombang';

    protected $fillable = ['gelombang_id', 'jenjang_id', 'nama', 'nominal', 'urutan'];

    public function gelombang(): BelongsTo
    {
        return $this->belongsTo(Gelombang::class);
    }

    public function jenjang(): BelongsTo
    {
        return $this->belongsTo(Jenjang::class);
    }

    /** "Rp. 100.000,-" atau "Gratis". */
    public function tampil(): string
    {
        return self::format($this->nominal);
    }

    public static function format(?int $nominal): string
    {
        return $nominal ? 'Rp. '.number_format($nominal, 0, ',', '.').',-' : 'Gratis';
    }

    /**
     * Isian editor admin (nama, nominal, gratis per baris) → baris biaya. Baris tanpa nama dilewati;
     * baris yang dicentang Gratis (atau bernominal kosong) disimpan dengan nominal null.
     *
     * @param  array<int, array{nama?: ?string, nominal?: mixed, gratis?: mixed}>|null  $baris
     * @return array<int, array{nama: string, nominal: ?int}>
     */
    public static function dariBaris(?array $baris): array
    {
        $hasil = [];
        foreach ($baris ?? [] as $b) {
            $nama = trim((string) ($b['nama'] ?? ''));
            if ($nama === '') {
                continue;
            }
            $nominal = empty($b['gratis']) ? (int) ($b['nominal'] ?? 0) : 0;
            $hasil[] = ['nama' => $nama, 'nominal' => $nominal > 0 ? $nominal : null];
        }

        return $hasil;
    }

    /**
     * Baris biaya → isian editor admin (kebalikan dariBaris).
     *
     * @return array<int, array{nama: string, nominal: ?int, gratis: bool}>
     */
    public static function keBaris($baris): array
    {
        return collect($baris)->map(fn ($b) => ['nama' => $b->nama, 'nominal' => $b->nominal, 'gratis' => ! $b->nominal])->values()->all();
    }
}
