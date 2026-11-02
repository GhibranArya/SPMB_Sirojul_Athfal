<?php

namespace Database\Seeders;

use App\Models\Fasilitas;
use App\Models\Galeri;
use App\Models\ProfilYayasan;
use Illuminate\Database\Seeder;

/**
 * Konten awal halaman publik. Seluruh teks bersumber dari dokumen KSP SMP Islam Sirojul Athfal 2026/2027.
 * Hanya mengisi yang masih kosong, sehingga aman dijalankan ulang dan tidak menimpa perubahan panitia lewat admin.
 * Ekstrakurikuler diisi oleh migrasi create_ekstrakurikuler_table. Berita dan agenda sengaja tidak di-seed
 * karena dokumen tidak memuatnya.
 */
class KontenSeeder extends Seeder
{
    public function run(): void
    {
        $profil = ProfilYayasan::first() ?? new ProfilYayasan;
        $awal = [
            'visi' => 'Terwujudnya Murid Berakhlakul Karimah, Berprestasi, Mandiri, Kreatif dan Berwawasan Lingkungan.',
            'misi' => implode("\n", [
                'Meningkatkan dan mengembangkan Pendidikan akhlakul karimah.',
                'Mengintegrasikan akhlakul karimah dalam pembelajaran.',
                'Menumbuhkan penghayatan dan pengamalan agama Islam.',
                'Menyelenggarakan pembelajaran yang berkualitas.',
                'Mengembangkan budaya berprestasi dalam diri Murid.',
                'Meningkatkan prestasi akademik Murid.',
                'Meningkatkan prestasi non akademik Murid.',
                'Mengembangkan karakter mandiri dalam diri Murid',
                'Mengembangkan pembelajaran untuk membentuk karakter mandiri Murid.',
                'Mengembangkan karakter kreatif dalam diri Murid',
                'Mengembangkan pembelajaran untuk membentuk karakter kreatif Murid',
                'Menumbuhkembangkan sikap, perilaku yang peduli dan tanggap lingkungan.',
            ]),
            'alamat' => 'Jl. KH. Muh Sanusi Kp. Lio Masjid RT 04/03 Desa Kedung Waringin Kecamatan Bojong Gede Kabupaten Bogor Provinsi Jawa Barat',
            'npsn' => '20200558',
        ];
        foreach ($awal as $kolom => $nilai) {
            if (blank($profil->{$kolom})) {
                $profil->{$kolom} = $nilai;
            }
        }
        $profil->save();

        if (Fasilitas::count() === 0) {
            foreach ([
                ['fa-chalkboard', 'Ruang kelas'],
                ['fa-user-tie', 'Ruang Kepala Sekolah'],
                ['fa-people-group', 'Ruang Guru'],
                ['fa-folder-open', 'Ruang Tata Usaha'],
                ['fa-book-open', 'Perpustakaan'],
                ['fa-futbol', 'Lapangan'],
                ['fa-seedling', 'Lahan percontohan pertanian'],
                ['fa-restroom', 'Kamar mandi/toilet'],
                ['fa-utensils', 'Kantin sekolah'],
                ['fa-warehouse', 'Gudang'],
                ['fa-square-parking', 'Area parkir'],
                ['fa-tree', 'Area taman dan ruang terbuka'],
                ['fa-tv', 'Perangkat pembelajaran digital, termasuk Papan Interaktif Digital (PID)'],
            ] as $i => [$ikon, $nama]) {
                Fasilitas::create(['nama' => $nama, 'ikon' => $ikon, 'urutan' => $i + 1]);
            }
        }

        if (Galeri::count() === 0) {
            foreach ([
                ['tari-tradisional.jpg', 'Seni Tari', '50% 40%', 'Siswi menampilkan tari tradisional dengan payung warna-warni'],
                ['gedung-sekolah.jpg', 'Gedung Sekolah', '50% 45%', 'Gedung kelas SMP Islam Sirojul Athfal dengan tiang bendera di halaman'],
                ['pencak-silat.jpg', 'Pencak Silat', '50% 45%', 'Empat siswa pencak silat menunjukkan medali'],
                ['robotik.jpg', 'Robotic', '50% 55%', 'Siswa bersama perangkat robotik di dalam kelas'],
                ['kelas-belajar.jpg', 'Kegiatan Belajar di Kelas', '50% 50%', 'Murid mengerjakan soal di dalam ruang kelas'],
                ['pramuka.jpg', 'Pramuka', '62% 50%', 'Siswi Pramuka membawa bendera semaphore di halaman sekolah'],
                ['juara-tari.jpg', 'Seni Tari: Juara 3', '50% 40%', 'Tim tari memegang piala juara 3'],
                ['halaman-sekolah.jpg', 'Halaman Sekolah', '50% 55%', 'Halaman dan deretan ruang kelas SMP Islam Sirojul Athfal'],
                ['pelatihan-guru.jpg', 'Pelatihan Peningkatan Kompetensi Guru', '50% 55%', 'Para guru berfoto bersama usai pelatihan peningkatan kompetensi'],
            ] as $i => [$berkas, $judul, $posisi, $alt]) {
                Galeri::create([
                    'gambar' => 'images/foto/'.$berkas,
                    'judul' => $judul, 'unit' => 'SMP', 'keterangan_gambar' => $alt, 'posisi_gambar' => $posisi, 'urutan' => $i + 1,
                    // Foto potret (720x1280) pecah bila direntangkan ke latar hero lebar.
                    'untuk_hero' => $berkas !== 'pencak-silat.jpg',
                ]);
            }
        }
    }
}
