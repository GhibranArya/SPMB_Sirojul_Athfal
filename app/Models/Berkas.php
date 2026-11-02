<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Berkas extends Model
{
    protected $table = 'berkas';

    protected $fillable = [
        'pendaftaran_id', 'jenis_berkas_id', 'path', 'nama_asli', 'mime', 'ukuran', 'status', 'catatan',
    ];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class);
    }

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(JenisBerkas::class, 'jenis_berkas_id');
    }
}
