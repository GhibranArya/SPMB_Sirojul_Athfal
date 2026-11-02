<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Kolom tambahan agar halaman publik (beranda, profil, fasilitas) bisa dibaca dari tabel konten. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profil_yayasan', function (Blueprint $t) {
            $t->string('npsn', 20)->nullable()->after('alamat');
        });

        Schema::table('fasilitas', function (Blueprint $t) {
            $t->string('ikon', 40)->nullable()->after('deskripsi');
        });

        Schema::table('galeri', function (Blueprint $t) {
            $t->string('keterangan_gambar')->nullable()->after('gambar');
            $t->string('posisi_gambar', 20)->default('50% 50%')->after('keterangan_gambar');
            $t->unsignedTinyInteger('urutan')->default(0)->after('posisi_gambar');
        });
    }

    public function down(): void
    {
        Schema::table('galeri', fn (Blueprint $t) => $t->dropColumn(['keterangan_gambar', 'posisi_gambar', 'urutan']));
        Schema::table('fasilitas', fn (Blueprint $t) => $t->dropColumn('ikon'));
        Schema::table('profil_yayasan', fn (Blueprint $t) => $t->dropColumn('npsn'));
    }
};
