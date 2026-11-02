<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Membuat akun panel (master admin / kepala sekolah) langsung di server, tanpa menaruh sandi di repo.
 * Sandi diketik tersembunyi, tidak lewat argumen perintah (supaya tidak tersimpan di riwayat shell).
 */
class BuatAdmin extends Command
{
    protected $signature = 'admin:buat
        {--peran=admin : admin (master admin) atau kepsek (kepala sekolah)}
        {--nama= : Nama lengkap}
        {--email= : Email untuk masuk}
        {--hp= : Nomor HP (08…), juga bisa dipakai untuk masuk}
        {--wajib-ganti : Paksa ganti kata sandi saat masuk pertama (cocok bila akun diserahkan ke orang lain)}';

    protected $description = 'Buat akun master admin atau kepala sekolah (sandi diketik tersembunyi)';

    public function handle(): int
    {
        $peran = (string) $this->option('peran');
        if (! in_array($peran, ['admin', 'kepsek'], true)) {
            $this->error('Peran harus "admin" atau "kepsek".');

            return self::FAILURE;
        }

        $data = [
            'name' => $this->option('nama') ?: $this->ask('Nama lengkap'),
            'email' => strtolower((string) ($this->option('email') ?: $this->ask('Email'))),
            'phone' => preg_replace('/\D+/', '', (string) ($this->option('hp') ?: $this->ask('Nomor HP (08…)'))),
        ];
        $data['password'] = $this->secret('Kata sandi (min. 8 karakter, huruf dan angka)');
        $data['password_confirmation'] = $this->secret('Ulangi kata sandi');

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'regex:/^08\d{8,12}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $pesan) {
                $this->error($pesan);
            }

            return self::FAILURE;
        }

        $user = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
        ]);
        $user->forceFill(['role' => $peran, 'must_change_password' => $this->option('wajib-ganti')])->save();

        $this->info(sprintf('Akun %s dibuat: %s (%s).', $peran === 'kepsek' ? 'kepala sekolah' : 'master admin', $user->name, $user->email));

        return self::SUCCESS;
    }
}
