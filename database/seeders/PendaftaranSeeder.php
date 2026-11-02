<?php

namespace Database\Seeders;

use App\Models\Gelombang;
use App\Models\JenisBerkas;
use App\Models\Jenjang;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** 12 pendaftar contoh pada gelombang aktif, lengkap dengan berkas placeholder dan riwayat status. */
class PendaftaranSeeder extends Seeder
{
    // PNG 1×1 sebagai isi berkas contoh
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    public function run(): void
    {
        if (Pendaftaran::exists()) {
            return;
        }

        $g = Gelombang::aktif();
        $jenjang = Jenjang::pluck('id', 'kode');
        $wajib = JenisBerkas::where('wajib', true)->orderBy('urutan')->get();
        $admin = User::where('role', 'admin')->first();
        $walidemo = User::where('email', 'wali@amanah.sch.id')->first();

        // kode, nama siswa, status, hari kirim, wali, hp, jml berkas
        $rows = [
            ['SA-26-K7P4Q', 'Zahra Aulia Putri', 'perbaikan', 2, 'Siti Rahma', '081200000002', 4],
            ['SA-26-B2M9X', 'Muhammad Fadhil Ramadhan', 'menunggu', 2, 'Hj. Aminah', '081311112222', 4],
            ['SA-26-T5R1C', 'Aisyah Nur Azizah', 'terverifikasi', 1, 'Dewi Lestari', '082133334444', 4],
            ['SA-26-H8W3L', 'Rafif Alamsyah', 'terverifikasi', 1, 'Ahmad Fauzi', '085255556666', 4],
            ['SA-26-Q4D7N', 'Naura Khairunnisa', 'terverifikasi', 3, 'Rina Marlina', '081377778888', 4],
            ['SA-26-F9J2V', 'Alif Hidayatullah', 'draft', 4, 'Yusuf Hidayat', '081899990000', 1],
            ['SA-26-S3Z6E', 'Keisha Maharani', 'menunggu', 3, 'Maya Sari', '087811223344', 4],
            ['SA-26-P1G8Y', 'Daffa Al Ghifari', 'diterima', 1, 'Hendra Gunawan', '081355667788', 4],
            ['SA-26-N6U4A', 'Salsabila Putri', 'diterima', 1, 'Eka Wahyuni', '082199887766', 4],
            ['SA-26-R7K5T', 'Ghazi Mubarak', 'ditolak', 2, 'Bambang Susilo', '081233445566', 4],
            ['SA-26-M2X9B', 'Humaira Zahrani', 'menunggu', 4, 'Lilis Suryani', '085711223399', 4],
            ['SA-26-C8V1D', 'Fathir Akbar', 'draft', 4, 'Anwar Hasan', '081544332211', 2],
        ];

        foreach ($rows as $i => [$kode, $nama, $status, $hari, $wali, $hp, $jmlBerkas]) {
            $wd = $i === 0
                ? $walidemo
                : tap(User::create([
                    'name' => $wali, 'email' => 'wali'.($i + 1).'@contoh.test', 'phone' => $hp,
                    'password' => Str::random(24),
                ]), fn ($u) => $u->forceFill(['role' => 'pendaftar'])->save());

            $kirim = now()->startOfDay()->setDate(2026, 10, $hari)->setTime(9, 14 + $i);
            $umur = 12; // lulusan SD/MI, masuk kelas VII

            $p = Pendaftaran::create([
                'user_id' => $wd->id, 'gelombang_id' => $g->id, 'jenjang_id' => $jenjang['SMP'],
                'kode' => $kode, 'status' => 'draft',
                'nama_siswa' => $nama, 'nik' => '32770145'.str_pad((string) (6150000 + $i * 37), 8, '0', STR_PAD_LEFT),
                'jenis_kelamin' => in_array($i, [1, 3, 5, 7, 9, 11], true) ? 'L' : 'P',
                'tempat_lahir' => 'Bogor', 'tgl_lahir' => now()->setDate(2026 - $umur, 6, 5)->toDateString(),
                'asal_sekolah' => 'SD Contoh '.($i + 1),
                'alamat_siswa' => 'Kp. Lio Masjid RT 04/03 No. '.(21 + $i).', Desa Kedung Waringin, Bojong Gede, Kabupaten Bogor',
                'nama_ayah' => 'Bapak '.explode(' ', $wali)[0], 'nama_ibu' => $wali,
                'pekerjaan_ortu' => 'Wiraswasta', 'hp_ortu' => $hp, 'alamat_ortu' => 'Bojong Gede, Kabupaten Bogor',
                'setuju_data' => true, 'submitted_at' => $kirim,
            ]);
            $p->riwayat()->create(['status_lama' => null, 'status_baru' => 'draft', 'keterangan' => 'Formulir dikirim', 'created_at' => $kirim, 'updated_at' => $kirim]);

            // berkas
            foreach ($wajib->take($jmlBerkas) as $k => $jb) {
                $st = match (true) {
                    in_array($status, ['terverifikasi', 'diterima', 'ditolak'], true) => 'valid',
                    $status === 'perbaikan' => $jb->kode === 'akta' ? 'ditolak' : ($jb->kode === 'ijazah' ? 'menunggu' : 'valid'),
                    default => 'menunggu',
                };
                $path = "berkas/{$p->id}/{$jb->kode}.png";
                Storage::put($path, base64_decode(self::PNG));
                $p->berkas()->create([
                    'jenis_berkas_id' => $jb->id, 'path' => $path, 'nama_asli' => $jb->kode.'-'.Str::slug($nama).'.png',
                    'mime' => 'image/png', 'ukuran' => 70, 'status' => $st,
                    'catatan' => $st === 'ditolak' ? 'Foto buram, mohon unggah ulang' : null,
                ]);
            }

            // riwayat status mengikuti status akhir
            $at = $kirim->copy();
            $langkah = [];
            if ($status !== 'draft') {
                $langkah[] = ['menunggu', 'Semua berkas wajib diunggah', null];
            }
            if ($status === 'perbaikan') {
                $langkah[] = ['perbaikan', 'Panitia meminta perbaikan', $admin->id];
            }
            if (in_array($status, ['terverifikasi', 'diterima', 'ditolak'], true)) {
                $langkah[] = ['terverifikasi', 'Data dan berkas sesuai', $admin->id];
            }
            if (in_array($status, ['diterima', 'ditolak'], true)) {
                $langkah[] = [$status, 'Hasil seleksi ditetapkan', $admin->id];
            }
            foreach ($langkah as [$baru, $ket, $uid]) {
                $at = $at->copy()->addMinutes(27);
                $lama = $p->status;
                $p->forceFill(['status' => $baru])->save();
                $p->riwayat()->create(['user_id' => $uid, 'status_lama' => $lama, 'status_baru' => $baru, 'keterangan' => $ket, 'created_at' => $at, 'updated_at' => $at]);
            }

            if ($status === 'perbaikan') {
                $p->update(['catatan' => 'Foto Akta Kelahiran buram dan sebagian teks terpotong. Mohon unggah ulang dengan foto yang jelas.']);
            }
        }
    }
}
