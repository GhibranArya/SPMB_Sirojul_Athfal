<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Revisi 26 Okt 2026: situs untuk Yayasan Sirojul Athfal dengan dua jenjang (MI dan SMP).
 * - jenjang MI ditambahkan (SMP tetap), kuota MI per gelombang disalin dari kuota SMP sebagai angka sementara
 * - biaya_gelombang: rincian biaya per gelombang dan jenjang (nominal kosong = Gratis)
 * - jenis_berkas.jenjang: berkas khusus satu jenjang (kosong = semua); KTP ayah/ibu ditambahkan, Rapor jadi Ijazah/SKL (SMP)
 * - profil_yayasan: tautan media sosial; WhatsApp panitia diisi bila masih kosong
 * Tidak ada data yang dihapus.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('jenjang')->updateOrInsert(['kode' => 'MI'], [
            'nama' => 'MI', 'deskripsi' => 'MIS Sirojul Athfal Kabupaten Bogor.', 'updated_at' => $now, 'created_at' => $now,
        ]);
        DB::table('jenjang')->where('kode', 'SMP')->update(['deskripsi' => 'SMP Islam Sirojul Athfal Kabupaten Bogor.']);
        $mi = DB::table('jenjang')->where('kode', 'MI')->value('id');
        $smp = DB::table('jenjang')->where('kode', 'SMP')->value('id');

        foreach (DB::table('kuota_jenjang')->where('jenjang_id', $smp)->get() as $k) {
            if (! DB::table('kuota_jenjang')->where('gelombang_id', $k->gelombang_id)->where('jenjang_id', $mi)->exists()) {
                DB::table('kuota_jenjang')->insert([
                    'gelombang_id' => $k->gelombang_id, 'jenjang_id' => $mi, 'kuota' => $k->kuota,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        Schema::create('biaya_gelombang', function (Blueprint $t) {
            $t->id();
            $t->foreignId('gelombang_id')->constrained('gelombang')->cascadeOnDelete();
            $t->foreignId('jenjang_id')->constrained('jenjang')->cascadeOnDelete();
            $t->string('nama', 100);
            $t->unsignedInteger('nominal')->nullable();   // null = Gratis
            $t->unsignedTinyInteger('urutan')->default(0);
            $t->timestamps();
            $t->index(['gelombang_id', 'jenjang_id']);
        });

        Schema::table('jenis_berkas', function (Blueprint $t) {
            $t->string('jenjang', 10)->nullable()->after('kode'); // null = semua jenjang
        });

        // Berkas: KK, Akta, KTP ayah, KTP ibu (semua jenjang) + Ijazah/SKL (SMP). Pas foto dan surat sehat dinonaktifkan, datanya tetap.
        DB::table('jenis_berkas')->where('kode', 'rapor')->update([
            'kode' => 'ijazah', 'nama' => 'Ijazah Terakhir / SKL', 'jenjang' => 'SMP', 'wajib' => true, 'icon' => 'fa-graduation-cap',
        ]);
        DB::table('jenis_berkas')->whereIn('kode', ['foto', 'sehat'])->update(['is_aktif' => false]);
        foreach ([
            ['kk', 'Kartu Keluarga (KK)', 'fa-people-roof', 1],
            ['akta', 'Akta Kelahiran', 'fa-file-lines', 2],
            ['ktp_ayah', 'KTP Ayah', 'fa-id-card', 3],
            ['ktp_ibu', 'KTP Ibu', 'fa-id-card', 4],
            ['ijazah', 'Ijazah Terakhir / SKL', 'fa-graduation-cap', 5],
        ] as [$kode, $nama, $icon, $urutan]) {
            DB::table('jenis_berkas')->updateOrInsert(['kode' => $kode], [
                'nama' => $nama, 'icon' => $icon, 'urutan' => $urutan, 'wajib' => true, 'is_aktif' => true,
                'jenjang' => $kode === 'ijazah' ? 'SMP' : null, 'updated_at' => $now, 'created_at' => $now,
            ]);
        }

        Schema::table('profil_yayasan', function (Blueprint $t) {
            foreach (['instagram', 'tiktok', 'facebook', 'youtube'] as $kolom) {
                $t->string($kolom, 255)->nullable();
            }
        });

        $profil = DB::table('profil_yayasan')->first();
        if ($profil && blank($profil->whatsapp)) {
            DB::table('profil_yayasan')->where('id', $profil->id)->update(['whatsapp' => '0851-5825-6411']);
        }
    }

    public function down(): void
    {
        Schema::table('profil_yayasan', fn (Blueprint $t) => $t->dropColumn(['instagram', 'tiktok', 'facebook', 'youtube']));
        Schema::table('jenis_berkas', fn (Blueprint $t) => $t->dropColumn('jenjang'));
        Schema::dropIfExists('biaya_gelombang');
    }
};
