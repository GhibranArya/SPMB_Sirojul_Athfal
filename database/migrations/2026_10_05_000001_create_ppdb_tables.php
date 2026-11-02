<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Periode pendaftaran (tahun ajaran + gelombang)
        Schema::create('gelombang', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 60);
            $t->string('tahun_ajaran', 9);               // 2026/2027
            $t->date('tgl_buka');
            $t->date('tgl_tutup');
            $t->date('tgl_pengumuman');
            $t->date('daftar_ulang_mulai')->nullable();
            $t->date('daftar_ulang_selesai')->nullable();
            $t->boolean('is_aktif')->default(false);
            $t->timestamps();
            $t->index('is_aktif');
        });

        Schema::create('jenjang', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 10)->unique();             // TK, MI, MTs
            $t->string('nama', 60);
            $t->text('deskripsi')->nullable();
            $t->timestamps();
        });

        // Kuota berbeda tiap gelombang
        Schema::create('kuota_jenjang', function (Blueprint $t) {
            $t->id();
            $t->foreignId('gelombang_id')->constrained('gelombang')->cascadeOnDelete();
            $t->foreignId('jenjang_id')->constrained('jenjang')->cascadeOnDelete();
            $t->unsignedSmallInteger('kuota')->default(0);
            $t->timestamps();
            $t->unique(['gelombang_id', 'jenjang_id']);
        });

        Schema::create('jenis_berkas', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 20)->unique();             // akta, kk, foto, rapor, sehat
            $t->string('nama', 80);
            $t->boolean('wajib')->default(true);
            $t->string('icon', 40)->default('fa-file-lines');
            $t->unsignedTinyInteger('urutan')->default(0);
            $t->boolean('is_aktif')->default(true);
            $t->timestamps();
        });

        Schema::create('pendaftaran', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('gelombang_id')->constrained('gelombang');
            $t->foreignId('jenjang_id')->constrained('jenjang');
            $t->string('kode', 20)->nullable()->unique();  // terbit saat formulir dikirim
            $t->string('status', 20)->default('draft');    // draft|menunggu|perbaikan|terverifikasi|diterima|ditolak
            $t->text('catatan')->nullable();               // catatan perbaikan dari panitia

            // data calon siswa
            $t->string('nama_siswa', 100)->nullable();
            $t->string('nik', 16)->nullable();
            $t->char('jenis_kelamin', 1)->nullable();      // L|P
            $t->string('tempat_lahir', 60)->nullable();
            $t->date('tgl_lahir')->nullable();
            $t->string('asal_sekolah', 100)->nullable();
            $t->text('alamat_siswa')->nullable();

            // data orang tua / wali
            $t->string('nama_ayah', 100)->nullable();
            $t->string('nama_ibu', 100)->nullable();
            $t->string('pekerjaan_ortu', 80)->nullable();
            $t->string('hp_ortu', 20)->nullable();
            $t->text('alamat_ortu')->nullable();

            $t->boolean('setuju_data')->default(false);
            $t->timestamp('submitted_at')->nullable();     // null = baru draft formulir
            $t->timestamps();

            $t->index(['gelombang_id', 'jenjang_id', 'status']);
            $t->index('nik');
        });

        Schema::create('berkas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pendaftaran_id')->constrained('pendaftaran')->cascadeOnDelete();
            $t->foreignId('jenis_berkas_id')->constrained('jenis_berkas');
            $t->string('path');                            // storage privat, bukan public
            $t->string('nama_asli');
            $t->string('mime', 60)->nullable();
            $t->unsignedInteger('ukuran')->default(0);     // byte
            $t->string('status', 12)->default('menunggu'); // menunggu|valid|ditolak
            $t->string('catatan')->nullable();
            $t->timestamps();
            $t->unique(['pendaftaran_id', 'jenis_berkas_id']);
        });

        Schema::create('riwayat_status', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pendaftaran_id')->constrained('pendaftaran')->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // pelaku
            $t->string('status_lama', 20)->nullable();
            $t->string('status_baru', 20);
            $t->string('keterangan')->nullable();
            $t->timestamps();
            $t->index('pendaftaran_id');
        });

        // Hasil seleksi baru terlihat setelah published_at terisi
        Schema::create('pengumuman', function (Blueprint $t) {
            $t->id();
            $t->foreignId('gelombang_id')->unique()->constrained('gelombang')->cascadeOnDelete();
            $t->string('judul', 150);
            $t->text('isi')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['pengumuman', 'riwayat_status', 'berkas', 'pendaftaran', 'jenis_berkas', 'kuota_jenjang', 'jenjang', 'gelombang'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
