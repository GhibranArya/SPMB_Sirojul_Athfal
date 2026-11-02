<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilYayasan extends Model
{
    protected $table = 'profil_yayasan';

    protected $guarded = [];

    /** Tautan wa.me dari nomor WhatsApp panitia (0851… → 62851…); null bila nomor belum diisi. */
    public function waLink(?string $pesan = null): ?string
    {
        $digit = preg_replace('/\D+/', '',(string) $this->whatsapp);
        if ($digit === '') {
            return null;
        }
        if (str_starts_with($digit, '0')) {
            $digit = '62'.substr($digit, 1);
        }

        return 'https://wa.me/'.$digit.($pesan ? '?text='.rawurlencode($pesan) : '');
    }

    /** Akun media sosial yang sudah diisi: [label, ikon Font Awesome, url]. */
    public function sosial(): array
    {
        $daftar = [];
        foreach ([
            'instagram' => ['Instagram', 'fa-brands fa-instagram'],
            'tiktok' => ['TikTok', 'fa-brands fa-tiktok'],
            'facebook' => ['Facebook', 'fa-brands fa-facebook-f'],
            'youtube' => ['YouTube', 'fa-brands fa-youtube'],
        ] as $kolom => [$label, $ikon]) {
            if (filled($this->{$kolom})) {
                $daftar[] = [$label, $ikon, $this->{$kolom}];
            }
        }

        return $daftar;
    }

    /** Misi disimpan satu butir per baris. */
    public function daftarMisi(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $this->misi))));
    }
}
