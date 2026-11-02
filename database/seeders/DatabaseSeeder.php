<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            MasterSeeder::class,
            KontenSeeder::class,
            GaleriMiSeeder::class,
            // PendaftaranSeeder (peserta contoh) tidak dijalankan lagi: akun dibatasi 1 admin, 1 kepala sekolah, 1 peserta.
        ]);
    }
}
