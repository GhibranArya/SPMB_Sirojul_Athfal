<?php

namespace Database\Seeders;

use App\Models\Galeri;
use Illuminate\Database\Seeder;

/**
 * Foto kegiatan MI (dari folder "foto kegiatan MI", disalin ke public/images/foto).
 * Idempoten: foto yang sudah ada di galeri dilewati. Judul dan keterangan hanya menggambarkan isi foto; ubah lewat admin > Galeri.
 */
class GaleriMiSeeder extends Seeder
{
    public function run(): void
    {
        $urutan = (int) Galeri::max('urutan');

        // [berkas, judul, unit, keterangan untuk pembaca layar]
        foreach ([
            ['mi-perpisahan-pencak-silat.jpg', 'Perpisahan dan Pelepasan 2025/2026', null, 'Dua siswa menampilkan pencak silat di panggung acara Perpisahan dan Pelepasan MI-SMP Islam Sirojul Athfal'],
            ['mi-barisan-halaman.jpg', 'Barisan Siswa di Halaman Sekolah', 'MI', 'Siswa berbaris rapi di halaman sekolah berlantai paving'],
            ['mi-pramuka-halaman.jpg', 'Pramuka di Halaman Sekolah', 'MI', 'Barisan siswa berseragam Pramuka membawa tongkat di halaman sekolah'],
            ['mi-kegiatan-halaman.jpg', 'Kegiatan Bersama di Halaman', 'MI', 'Siswa dan guru berkegiatan bersama di halaman sekolah di bawah pohon'],
            ['mi-kunjungan-masjid.jpg', 'Kunjungan: Foto Bersama di Masjid', 'MI', 'Siswa berseragam batik duduk berfoto bersama di depan masjid'],
            ['mi-kunjungan-pelataran-masjid.jpg', 'Kunjungan: Pelataran Masjid', 'MI', 'Siswa dan guru berfoto bersama di pelataran masjid'],
            ['mi-kunjungan-peta-suku-bangsa.jpg', 'Kunjungan: Peta Suku Bangsa Indonesia', 'MI', 'Siswa berfoto bersama di depan peta suku bangsa di Indonesia'],
            ['mi-kunjungan-ruang-pameran.jpg', 'Kunjungan: Ruang Pameran', 'MI', 'Sekelompok siswa laki-laki berfoto di dalam ruang pameran'],
            ['mi-siswa-halaman.jpg', 'Siswa di Halaman Sekolah', 'MI', 'Siswa berkumpul dan melambaikan tangan di halaman sekolah'],
            ['mi-foto-bersama-depan-kelas.jpg', 'Foto Bersama di Depan Kelas', 'MI', 'Siswa berfoto bersama di depan ruang kelas'],
            ['mi-foto-bersama-kelas.jpg', 'Foto Bersama di Ruang Kelas', 'MI', 'Siswa dan guru berfoto bersama di dalam ruang kelas'],
            ['mi-pramuka-latihan.jpg', 'Kegiatan Pramuka', 'MI', 'Siswa Pramuka duduk berkelompok di halaman sekolah'],
        ] as [$berkas, $judul, $unit, $alt]) {
            $path = 'images/foto/'.$berkas;
            if (Galeri::where('gambar', $path)->exists() || ! is_file(public_path($path))) {
                continue;
            }
            Galeri::create([
                'gambar' => $path, 'judul' => $judul, 'unit' => $unit, 'keterangan_gambar' => $alt,
                'posisi_gambar' => '50% 50%', 'urutan' => ++$urutan,
                'untuk_hero' => false, // foto WhatsApp kurang tajam untuk latar layar penuh
            ]);
        }
    }
}
