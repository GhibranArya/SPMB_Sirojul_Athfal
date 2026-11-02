<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisBerkas extends Model
{
    protected $table = 'jenis_berkas';

    protected $fillable = ['kode', 'nama', 'wajib', 'icon', 'urutan', 'is_aktif'];

    protected $casts = ['wajib' => 'boolean', 'is_aktif' => 'boolean'];

    public function berkas(): HasMany
    {
        return $this->hasMany(Berkas::class);
    }

    /** Jenis berkas yang aktif; dengan $jenjang (kode MI/SMP) hanya yang berlaku untuk jenjang itu. */
    public static function aktif(?string $jenjang = null)
    {
        return static::where('is_aktif', true)
            ->when($jenjang, fn ($q) => $q->where(fn ($w) => $w->whereNull('jenjang')->orWhere('jenjang', $jenjang)))
            ->orderBy('urutan')->get();
    }
}
