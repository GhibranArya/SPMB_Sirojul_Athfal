<?php

namespace Database\Seeders;

use App\Models\Gelombang;
use App\Models\JenisBerkas;
use App\Models\Jenjang;
use App\Models\Pendaftaran;
use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Contoh pengumuman yang SUDAH terbit: satu gelombang (tidak aktif) dengan 4 pendaftar, 2 Diterima dan 2 Tidak Diterima
 * (kuota 2). Untuk mencoba tampilan "Pengumuman" di sisi pendaftar tanpa menunggu atau mengubah gelombang aktif.
 * Hanya data contoh: jangan dijalankan di produksi. Aman dijalankan ulang (dilewati bila gelombangnya sudah ada).
 *
 * Login contoh: email pada daftar di bawah, kata sandi = SANDI_CONTOH.
 */
class ContohHasilSeleksiSeeder extends Seeder
{
    public const SANDI_CONTOH = 'contoh12345';

    // PNG 1×1 sebagai isi berkas contoh
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    public function run(): void
    {
        if (Gelombang::where('nama', 'Gelombang Contoh')->exists()) {
            $this->command?->warn('Gelombang Contoh sudah ada, dilewati.');

            return;
        }

        $admin = User::where('role', 'admin')->firstOrFail();
        $smp = Jenjang::where('kode', 'SMP')->firstOrFail();
        $wajib = JenisBerkas::where('wajib', true)->where('is_aktif', true)->orderBy('urutan')->get();

        $g = Gelombang::create([
            'nama' => 'Gelombang Contoh', 'tahun_ajaran' => '2025/2026',
            'tgl_buka' => '2025-11-01', 'tgl_tutup' => '2025-11-30', 'tgl_pengumuman' => '2025-12-10',
            'daftar_ulang_mulai' => '2025-12-15', 'daftar_ulang_selesai' => '2025-12-20', 'is_aktif' => false,
        ]);
        $g->kuota()->create(['jenjang_id' => $smp->id, 'kuota' => 2]);
        Pengumuman::create(['gelombang_id' => $g->id, 'judul' => 'Hasil seleksi PPDB 2025/2026 Gelombang Contoh', 'published_at' => '2025-12-10 08:00:00']);

        // email, nama wali, hp, nama siswa, L/P, hasil
        $contoh = [
            ['nurhayati@contoh.test', 'Nurhayati', '081290001001', 'Aulia Rahmawati', 'P', 'diterima'],
            ['hasan.basri@contoh.test', 'Hasan Basri', '081290001002', 'Muhammad Rizky Basri', 'L', 'diterima'],
            ['sumiati@contoh.test', 'Sumiati', '081290001003', 'Dimas Prasetyo', 'L', 'ditolak'],
            ['ratna.dewi@contoh.test', 'Ratna Dewi', '081290001004', 'Nabila Safitri', 'P', 'ditolak'],
        ];

        foreach ($contoh as $i => [$email, $wali, $hp, $siswa, $jk, $hasil]) {
            $user = User::create(['name' => $wali, 'email' => $email, 'phone' => $hp, 'password' => self::SANDI_CONTOH]);
            $user->forceFill(['role' => 'pendaftar'])->save();

            $kirim = now()->setDate(2025, 11, 3 + $i)->setTime(9, 10 + $i);

            $p = Pendaftaran::create([
                'user_id' => $user->id, 'gelombang_id' => $g->id, 'jenjang_id' => $smp->id,
                'kode' => Pendaftaran::buatKode(), 'status' => 'draft',
                'nama_siswa' => $siswa, 'nik' => '32770145'.str_pad((string) (9100000 + $i * 41), 8, '0', STR_PAD_LEFT),
                'jenis_kelamin' => $jk, 'tempat_lahir' => 'Bogor', 'tgl_lahir' => '2013-0'.(3 + $i).'-1'.$i,
                'asal_sekolah' => 'SD Negeri Contoh '.($i + 1),
                'alamat_siswa' => 'Kp. Lio Masjid RT 04/03 No. '.(40 + $i).', Desa Kedung Waringin, Bojong Gede, Kabupaten Bogor',
                'nama_ayah' => 'Bapak '.explode(' ', $wali)[0], 'nama_ibu' => $wali,
                'pekerjaan_ortu' => 'Wiraswasta', 'hp_ortu' => $hp, 'alamat_ortu' => 'Bojong Gede, Kabupaten Bogor',
                'setuju_data' => true, 'submitted_at' => $kirim,
            ]);
            $p->riwayat()->create(['status_lama' => null, 'status_baru' => 'draft', 'keterangan' => 'Formulir dikirim', 'created_at' => $kirim, 'updated_at' => $kirim]);

            foreach ($wajib as $jb) {
                $path = "berkas/{$p->id}/{$jb->kode}.png";
                Storage::put($path, base64_decode(self::PNG));
                $p->berkas()->create([
                    'jenis_berkas_id' => $jb->id, 'path' => $path, 'nama_asli' => $jb->kode.'-'.Str::slug($siswa).'.png',
                    'mime' => 'image/png', 'ukuran' => 70, 'status' => 'valid',
                ]);
            }

            $at = $kirim->copy();
            foreach ([
                ['menunggu', 'Semua berkas wajib diunggah', null],
                ['terverifikasi', 'Data dan berkas sesuai', $admin->id],
                [$hasil, 'Hasil seleksi ditetapkan', $admin->id],
            ] as [$baru, $ket, $uid]) {
                $at = $at->copy()->addMinutes(31);
                $lama = $p->status;
                $p->forceFill(['status' => $baru])->save();
                $p->riwayat()->create(['user_id' => $uid, 'status_lama' => $lama, 'status_baru' => $baru, 'keterangan' => $ket, 'created_at' => $at, 'updated_at' => $at]);
            }
        }
    }
}
