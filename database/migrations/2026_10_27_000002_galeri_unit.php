<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Galeri: foto bisa ditandai milik unit MI atau SMP (kosong = semua unit), dipakai untuk filter di beranda. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galeri', function (Blueprint $t) {
            $t->string('unit', 10)->nullable()->after('judul'); // MI | SMP | null
        });

        // Foto yang sudah ada berasal dari dokumen dan kegiatan SMP.
        DB::table('galeri')->whereIn('gambar', array_map(fn ($f) => 'images/foto/'.$f, [
            'tari-tradisional.jpg', 'gedung-sekolah.jpg', 'pencak-silat.jpg', 'robotik.jpg', 'kelas-belajar.jpg',
            'pramuka.jpg', 'juara-tari.jpg', 'halaman-sekolah.jpg', 'pelatihan-guru.jpg',
        ]))->update(['unit' => 'SMP']);
    }

    public function down(): void
    {
        Schema::table('galeri', fn (Blueprint $t) => $t->dropColumn('unit'));
    }
};
