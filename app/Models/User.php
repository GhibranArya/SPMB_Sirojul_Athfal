<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'must_change_password' => 'boolean',
    ];

    public function pendaftaran(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Pendaftaran::class);
    }

    /** Master admin: akses penuh panel (verifikasi, gelombang, seleksi, konten). */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Kepala sekolah: melihat data pendaftar/ekspor dan mengelola konten situs, tanpa aksi verifikasi/seleksi/gelombang. */
    public function isKepsek(): bool
    {
        return $this->role === 'kepsek';
    }

    /** Pengguna panel (admin atau kepala sekolah), bukan wali pendaftar. */
    public function isStaf(): bool
    {
        return $this->isAdmin() || $this->isKepsek();
    }
}
