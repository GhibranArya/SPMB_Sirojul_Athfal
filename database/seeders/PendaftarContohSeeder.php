<?php

namespace Database\Seeders;

use App\Models\Gelombang;
use App\Models\JenisBerkas;
use App\Models\Jenjang;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Satu pendaftar contoh (akun peserta@amanah.sch.id) di gelombang aktif, SMP, status Menunggu Verifikasi.
 * Berkasnya memakai foto dari galeri (public/images/foto) sebagai isi contoh; dipilih yang berupa bangunan/peta, tanpa wajah.
 * Hanya data contoh: jangan dijalankan di produksi. Aman dijalankan ulang (dilewati bila akunnya sudah punya pendaftaran).
 */
class PendaftarContohSeeder extends Seeder
{
    /** kode jenis berkas => [foto galeri, nama berkas yang tampil] */
    private const BERKAS = [
        'kk' => ['halaman-sekolah.jpg', 'kartu-keluarga-contoh.jpg'],
        'akta' => ['gedung-sekolah.jpg', 'akta-kelahiran-contoh.jpg'],
        'ktp_ayah' => ['halaman-pohon.jpg', 'ktp-ayah-contoh.jpg'],
        'ktp_ibu' => ['mi-kunjungan-pelataran-masjid.jpg', 'ktp-ibu-contoh.jpg'],
        'ijazah' => ['mi-kunjungan-peta-suku-bangsa.jpg', 'ijazah-contoh.jpg'],
    ];

    public function run(): void
    {
        $wali = User::where('email', 'peserta@amanah.sch.id')->first();
        $g = Gelombang::aktif();

        if (! $wali || ! $g) {
            $this->command?->warn('Akun peserta@amanah.sch.id atau gelombang aktif tidak ada, dilewati.');

            return;
        }
        if (Pendaftaran::where('user_id', $wali->id)->exists()) {
            $this->command?->warn('peserta@amanah.sch.id sudah punya pendaftaran, dilewati.');

            return;
        }

        DB::transaction(fn () => $this->buat($wali, $g)); // semua atau tidak sama sekali
    }

    private function buat(User $wali, Gelombang $g): void
    {
        $p = Pendaftaran::create([
            'user_id' => $wali->id, 'gelombang_id' => $g->id, 'jenjang_id' => Jenjang::where('kode', 'SMP')->value('id'),
            'kode' => Pendaftaran::buatKode(), 'status' => 'draft',
            'nama_siswa' => 'Muhammad Rizki Pratama', 'nik' => '3201011203140001', 'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bogor', 'tgl_lahir' => '2014-03-12', 'asal_sekolah' => 'SD Negeri Contoh 1 Bojong Gede',
            'alamat_siswa' => 'Kp. Lio Masjid RT 04/03 No. 21, Desa Kedung Waringin, Bojong Gede, Kabupaten Bogor',
            'nama_ayah' => 'Budi Santoso', 'nama_ibu' => 'Siti Aminah', 'pekerjaan_ortu' => 'Wiraswasta',
            'hp_ortu' => $wali->phone, 'alamat_ortu' => 'Kp. Lio Masjid RT 04/03 No. 21, Desa Kedung Waringin, Bojong Gede, Kabupaten Bogor',
            'setuju_data' => true, 'submitted_at' => now(),
        ]);
        $p->riwayat()->create(['status_lama' => null, 'status_baru' => 'draft', 'keterangan' => 'Formulir dikirim']);

        foreach (JenisBerkas::aktif('SMP')->where('wajib', true) as $jb) {
            [$foto, $nama] = self::BERKAS[$jb->kode] ?? [null, null];
            $sumber = $foto ? public_path('images/foto/'.$foto) : null;
            if (! $sumber || ! is_file($sumber)) {
                continue;
            }

            $path = "berkas/{$p->id}/".Str::random(40).'.jpg'; // storage privat, nama acak seperti unggahan asli
            Storage::put($path, file_get_contents($sumber));
            $p->berkas()->create([
                'jenis_berkas_id' => $jb->id, 'path' => $path, 'nama_asli' => $nama,
                'mime' => 'image/jpeg', 'ukuran' => filesize($sumber), 'status' => 'menunggu',
            ]);
        }

        if ($p->berkasWajibKurang() === 0) {
            $p->ubahStatus('menunggu', 'Semua berkas wajib diunggah');
        }
    }
}
