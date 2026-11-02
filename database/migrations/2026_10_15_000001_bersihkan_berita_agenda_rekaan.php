<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menghapus berita dan agenda rekaan lama (TK/RA, MI, MTs) yang tidak ada di dokumen KSP dan tidak dipakai halaman mana pun.
 * Hanya baris awal yang dicocokkan lewat slug/judul; tabelnya dipertahankan.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('berita')->where('slug', 'ppdb-2026-2027-gelombang-1-dibuka')->delete();
        DB::table('agenda')->where('judul', 'Pengumuman Hasil Seleksi Gelombang 1')->delete();
    }

    public function down(): void
    {
        // Data rekaan tidak dikembalikan.
    }
};
