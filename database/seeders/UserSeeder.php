<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Akun demo untuk pengembangan lokal. Tidak dijalankan di production: di server buat akun asli dengan `php artisan admin:buat`.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('UserSeeder dilewati di production. Buat akun dengan: php artisan admin:buat');

            return;
        }

        User::updateOrCreate(['email' => 'admin@amanah.sch.id'], [
            'name' => 'Panitia PPDB',
            'phone' => '081200000001',
            'password' => 'admin12345',
        ])->forceFill(['role' => 'admin'])->save();

        // Kepala sekolah: melihat pendaftar/ekspor dan mengelola konten situs (lihat routes/web.php).
        User::updateOrCreate(['email' => 'kepsek@amanah.sch.id'], [
            'name' => 'Kepala Sekolah',
            'phone' => '081200000004',
            'password' => 'kepsek12345',
        ])->forceFill(['role' => 'kepsek'])->save();

        User::updateOrCreate(['email' => 'peserta@amanah.sch.id'], [
            'name' => 'Wali Murid',
            'phone' => '081200000003',
            'password' => 'peserta12345',
        ])->forceFill(['role' => 'pendaftar'])->save();
    }
}
