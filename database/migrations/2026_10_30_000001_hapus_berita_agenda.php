<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menghapus tabel berita dan agenda. Keduanya tidak dipakai halaman, controller, maupun seeder mana pun,
 * kosong sejak 15 Okt 2026, dan dokumen KSP tidak memuat isinya. down() membuat tabelnya kembali (kosong).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('berita');
        Schema::dropIfExists('agenda');
    }

    public function down(): void
    {
        Schema::create('berita', function (Blueprint $t) {
            $t->id();
            $t->string('judul');
            $t->string('slug')->unique();
            $t->string('kategori', 40)->nullable();
            $t->text('ringkasan')->nullable();
            $t->longText('isi')->nullable();
            $t->string('gambar')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->timestamps();
        });

        Schema::create('agenda', function (Blueprint $t) {
            $t->id();
            $t->string('judul');
            $t->text('deskripsi')->nullable();
            $t->date('tgl_mulai');
            $t->date('tgl_selesai')->nullable();
            $t->string('lokasi', 120)->nullable();
            $t->timestamps();
        });
    }
};
