<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KuotaJenjang extends Model
{
    protected $table = 'kuota_jenjang';

    protected $fillable = ['gelombang_id', 'jenjang_id', 'kuota'];

    public function gelombang(): BelongsTo
    {
        return $this->belongsTo(Gelombang::class);
    }

    public function jenjang(): BelongsTo
    {
        return $this->belongsTo(Jenjang::class);
    }
}
