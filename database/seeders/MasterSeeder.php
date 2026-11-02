<?php

namespace Database\Seeders;

use App\Models\Gelombang;
use App\Models\JenisBerkas;
use App\Models\Jenjang;
use Illuminate\Database\Seeder;

class MasterSeeder extends Seeder
{
    public function run(): void
    {
        // Yayasan Sirojul Athfal: dua jenjang, MI dan SMP.
        $jenjang = [];
        foreach ([
            ['MI', 'MI', 'MIS Sirojul Athfal Kabupaten Bogor.'],
            ['SMP', 'SMP', 'SMP Islam Sirojul Athfal Kabupaten Bogor.'],
        ] as [$kode, $nama, $desk]) {
            $jenjang[$kode] = Jenjang::updateOrCreate(['kode' => $kode], ['nama' => $nama, 'deskripsi' => $desk]);
        }

        // Berkas: KK, Akta, KTP ayah dan ibu untuk semua jenjang; Ijazah/SKL tambahan khusus SMP.
        foreach ([
            ['kk', 'Kartu Keluarga (KK)', null, 'fa-people-roof'],
            ['akta', 'Akta Kelahiran', null, 'fa-file-lines'],
            ['ktp_ayah', 'KTP Ayah', null, 'fa-id-card'],
            ['ktp_ibu', 'KTP Ibu', null, 'fa-id-card'],
            ['ijazah', 'Ijazah Terakhir / SKL', 'SMP', 'fa-graduation-cap'],
        ] as $i => [$kode, $nama, $untuk, $icon]) {
            JenisBerkas::updateOrCreate(['kode' => $kode], [
                'nama' => $nama, 'jenjang' => $untuk, 'wajib' => true, 'icon' => $icon, 'urutan' => $i + 1, 'is_aktif' => true,
            ]);
        }
        JenisBerkas::whereIn('kode', ['foto', 'sehat'])->update(['is_aktif' => false]);

        // [nama, TA, buka, tutup, pengumuman, daftar ulang mulai, selesai, aktif, kuota MI, kuota SMP]
        $gelombang = [
            ['Gelombang 1', '2027/2028', '2027-01-04', '2027-03-31', '2027-04-05', '2027-04-06', '2027-04-15', true, 40, 40],
            ['Gelombang 2', '2027/2028', '2027-04-01', '2027-05-31', '2027-06-05', '2027-06-06', '2027-06-15', false, 30, 30],
            ['Gelombang 3', '2027/2028', '2027-06-01', '2027-07-31', '2027-08-02', '2027-08-03', '2027-08-10', false, 20, 20],
        ];

        foreach ($gelombang as [$nama, $ta, $buka, $tutup, $umum, $ulangA, $ulangB, $aktif, $kMi, $kSmp]) {
            $g = Gelombang::updateOrCreate(['nama' => $nama, 'tahun_ajaran' => $ta], [
                'tgl_buka' => $buka, 'tgl_tutup' => $tutup, 'tgl_pengumuman' => $umum,
                'daftar_ulang_mulai' => $ulangA, 'daftar_ulang_selesai' => $ulangB, 'is_aktif' => $aktif,
            ]);
            $g->kuota()->updateOrCreate(['jenjang_id' => $jenjang['MI']->id], ['kuota' => $kMi]);
            $g->kuota()->updateOrCreate(['jenjang_id' => $jenjang['SMP']->id], ['kuota' => $kSmp]);
        }

        // Rincian biaya (data sementara menurut brosur; ubah lewat admin > Gelombang). Hanya diisi bila gelombang belum punya biaya.
        $biaya = [
            ['Gelombang 1', [
                'MI' => [['Pendaftaran / Formulir + Bet', 100000], ['SPP bulan Jul - Des', null], ['Baju Olahraga', 150000], ['Baju Batik', 115000], ['LKS Semester 1', null]],
                'SMP' => [['Pendaftaran / Formulir + Bet + Dasi', 150000], ['SPP bulan Jul - Des', null], ['Baju Olahraga', 200000], ['Baju Batik', 130000], ['LKS Semester 1', null]],
            ]],
            ['Gelombang 2', [
                'MI' => [['Pendaftaran / Formulir + Bet', 150000], ['SPP bulan Jul - Sep', null], ['Baju Olahraga', 150000], ['Baju Batik', 115000], ['LKS Semester 1', 50000]],
                'SMP' => [['Pendaftaran / Formulir + Bet + Dasi', 200000], ['SPP bulan Jul - Sep', null], ['Baju Olahraga', 200000], ['Baju Batik', 130000], ['LKS Semester 1', 75000]],
            ]],
            ['Gelombang 3', [
                'MI' => [['Pendaftaran / Formulir + Bet', 150000], ['SPP bulan Juli', null], ['Baju Olahraga', 150000], ['Baju Batik', 115000], ['LKS Semester 1', 100000]],
                'SMP' => [['Pendaftaran / Formulir + Bet + Dasi', 250000], ['SPP bulan Juli', null], ['Baju Olahraga', 200000], ['Baju Batik', 130000], ['LKS Semester 1', 120000]],
            ]],
        ];
        foreach ($biaya as [$nama, $perJenjang]) {
            $g = Gelombang::where('nama', $nama)->where('tahun_ajaran', '2027/2028')->first();
            if (! $g || $g->biaya()->exists()) {
                continue;
            }
            foreach ($perJenjang as $kode => $baris) {
                foreach ($baris as $i => [$butir, $nominal]) {
                    $g->biaya()->create(['jenjang_id' => $jenjang[$kode]->id, 'nama' => $butir, 'nominal' => $nominal, 'urutan' => $i + 1]);
                }
            }
        }
    }
}
