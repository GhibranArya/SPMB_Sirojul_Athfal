<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jenjang extends Model
{
    protected $table = 'jenjang';

    protected $fillable = ['kode', 'nama', 'deskripsi'];

    /** Urutan tampil: MI lebih dulu, lalu SMP (seperti brosur), bukan urutan id. */
    public static function urut()
    {
        return static::orderByRaw("CASE kode WHEN 'MI' THEN 0 ELSE 1 END")->orderBy('id');
    }

    public function kuota(): HasMany
    {
        return $this->hasMany(KuotaJenjang::class);
    }

    public function pendaftaran(): HasMany
    {
        return $this->hasMany(Pendaftaran::class);
    }
}
