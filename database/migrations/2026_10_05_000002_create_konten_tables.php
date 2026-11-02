<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Konten company profile yang nanti dikelola panitia dari dashboard (PRD Fase 3–4). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profil_yayasan', function (Blueprint $t) {
            $t->id();
            $t->string('nama_sambutan', 100)->nullable();
            $t->string('jabatan_sambutan', 120)->nullable();
            $t->text('sambutan')->nullable();
            $t->text('sejarah')->nullable();
            $t->text('visi')->nullable();
            $t->text('misi')->nullable();
            $t->string('alamat')->nullable();
            $t->string('telepon', 30)->nullable();
            $t->string('whatsapp', 30)->nullable();
            $t->string('email')->nullable();
            $t->timestamps();
        });

        Schema::create('fasilitas', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 100);
            $t->text('deskripsi')->nullable();
            $t->string('gambar')->nullable();
            $t->unsignedTinyInteger('urutan')->default(0);
            $t->timestamps();
        });

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

        Schema::create('galeri', function (Blueprint $t) {
            $t->id();
            $t->string('judul', 120);
            $t->string('kategori', 40)->nullable();
            $t->string('gambar');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['galeri', 'agenda', 'berita', 'fasilitas', 'profil_yayasan'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
