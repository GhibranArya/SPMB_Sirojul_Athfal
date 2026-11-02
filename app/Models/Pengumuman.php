<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengumuman extends Model
{
    protected $table = 'pengumuman';

    protected $fillable = ['gelombang_id', 'judul', 'isi', 'published_at'];

    protected $casts = ['published_at' => 'datetime'];

    public function gelombang(): BelongsTo
    {
        return $this->belongsTo(Gelombang::class);
    }
}
