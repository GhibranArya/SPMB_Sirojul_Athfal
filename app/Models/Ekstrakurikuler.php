<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ekstrakurikuler extends Model
{
    protected $table = 'ekstrakurikuler';

    protected $guarded = [];

    /** Tujuan, target, dan profil disimpan satu butir per baris. */
    public static function butir(?string $teks): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $teks))));
    }

    public function daftar(string $kolom): array
    {
        return self::butir($this->{$kolom});
    }

    /** Seluruh eskul berurutan, dalam bentuk array yang dipakai view publik. */
    public static function untukView(): array
    {
        return static::orderBy('urutan')->orderBy('id')->get()->map(fn (self $e) => [
            'nama' => $e->nama, 'ikon' => $e->ikon, 'waktu' => $e->waktu, 'pembimbing' => $e->pembimbing,
            'foto' => $e->foto, 'posisi' => $e->posisi_foto,
            'tujuan' => $e->daftar('tujuan'), 'target' => $e->daftar('target'), 'profil' => $e->daftar('profil'),
        ])->all();
    }
}
