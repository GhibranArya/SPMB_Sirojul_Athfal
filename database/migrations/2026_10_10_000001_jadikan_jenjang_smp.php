<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sekolah ini hanya SMP (SMP Islam Sirojul Athfal). Jenjang TK/MI/MTs diganti satu jenjang "SMP".
 * Data yang sudah ada dipindahkan, bukan dihapus: pendaftar lama diarahkan ke SMP, dan kuota SMP tiap
 * gelombang diambil dari kuota terkecil yang pernah diisi (dapat diubah admin di halaman Gelombang).
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('jenjang')->updateOrInsert(
            ['kode' => 'SMP'],
            ['nama' => 'SMP', 'deskripsi' => 'SMP Islam Sirojul Athfal Kabupaten Bogor.', 'updated_at' => $now, 'created_at' => $now]
        );
        $smp = DB::table('jenjang')->where('kode', 'SMP')->value('id');

        // Kuota SMP per gelombang: bila belum ada, pakai kuota terkecil dari jenjang lama.
        $kuotaLama = DB::table('kuota_jenjang')->where('jenjang_id', '!=', $smp)
            ->select('gelombang_id', DB::raw('MIN(kuota) as kuota'))->groupBy('gelombang_id')->get();
        foreach ($kuotaLama as $row) {
            if (! DB::table('kuota_jenjang')->where('gelombang_id', $row->gelombang_id)->where('jenjang_id', $smp)->exists()) {
                DB::table('kuota_jenjang')->insert([
                    'gelombang_id' => $row->gelombang_id, 'jenjang_id' => $smp, 'kuota' => $row->kuota,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        DB::table('pendaftaran')->where('jenjang_id', '!=', $smp)->update(['jenjang_id' => $smp]);
        DB::table('kuota_jenjang')->where('jenjang_id', '!=', $smp)->delete();
        DB::table('jenjang')->where('id', '!=', $smp)->delete();

        // Kode pendaftaran: awalan AM- (Amanah) diganti SA- (Sirojul Athfal).
        DB::table('pendaftaran')->where('kode', 'like', 'AM-%')
            ->update(['kode' => DB::raw("CONCAT('SA-', SUBSTRING(kode, 4))")]);
    }

    public function down(): void
    {
        // Tidak dapat dikembalikan: asal jenjang (TK/MI/MTs) tiap pendaftar tidak disimpan.
    }
};
