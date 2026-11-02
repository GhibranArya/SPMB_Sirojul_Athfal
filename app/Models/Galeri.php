<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Galeri extends Model
{
    protected $table = 'galeri';

    protected $guarded = [];

    /** [lebar, tinggi] berkas foto, disimpan di cache agar halaman tidak membuka berkas tiap kali (mencegah layout bergeser). */
    public function ukuran(): array
    {
        $berkas = public_path($this->gambar);

        return Cache::rememberForever('galeri-ukuran-'.md5($this->gambar).'-'.(@filemtime($berkas) ?: 0), function () use ($berkas) {
            $d = @getimagesize($berkas);

            return $d ? [$d[0], $d[1]] : [1280, 960];
        });
    }
}
