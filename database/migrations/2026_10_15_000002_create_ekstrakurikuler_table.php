<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ekstrakurikuler dipindah dari config/ekstrakurikuler.php ke tabel agar panitia bisa mengelolanya dari admin.
 * Isi awal (5 eskul) bersumber dari dokumen KSP SMP Islam Sirojul Athfal 2026/2027.
 * Kolom tujuan, target, dan profil menyimpan satu butir per baris.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ekstrakurikuler', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 80);
            $t->string('ikon', 40)->default('fa-star');
            $t->string('waktu', 60)->nullable();
            $t->string('pembimbing', 120)->nullable();
            $t->string('foto')->nullable();                  // nama berkas di public/images/foto/
            $t->string('posisi_foto', 20)->default('50% 50%');
            $t->text('tujuan')->nullable();
            $t->text('target')->nullable();
            $t->text('profil')->nullable();
            $t->unsignedTinyInteger('urutan')->default(0);
            $t->timestamps();
        });

        $now = now();
        $awal = [
            ['Pramuka', 'fa-campground', 'Hari Jumat', 'Siti Muzayyana Mukaromah, S.Pd.I', 'pramuka.jpg', '62% 50%',
                ['Menumbuhkan jiwa kepemimpinan, kemandirian, dan cinta tanah air'],
                ['Siswa mampu menunjukkan sikap disiplin, kepedulian sosial, dan tanggung jawab dalam kelompok', 'Juara Umum Tingkat Kwarran Bojonggede 2025'],
                ['Kemandirian', 'Kewargaan', 'Kreativitas', 'Kolaborasi']],
            ['Futsal', 'fa-futbol', 'Rabu', 'Fikri Fakhrudin, S.Pd', 'futsal.jpg', '50% 68%',
                ['Mengembangkan keterampilan olahraga dan kebugaran jasmani siswa', 'Menumbuhkan sportivitas, disiplin, dan kerja sama tim', 'Membentuk karakter tangguh dan semangat berkompetisi yang sehat'],
                ['Menunjukkan sikap sportif dan tanggung jawab dalam pertandingan dan latihan', 'Berpartisipasi aktif dalam kejuaraan futsal internal maupun eksternal sekolah'],
                ['Kesehatan', 'Kolaborasi']],
            ['Seni Tari', 'fa-masks-theater', 'Rabu', 'Tutilawati, S.Pd', 'tari-tradisional.jpg', '50% 40%',
                ['Mengembangkan bakat, minat, kreativitas, dan kemampuan Murid dalam bidang seni tari serta menumbuhkan rasa percaya diri, disiplin, kerja sama, dan kecintaan terhadap seni dan budaya Indonesia.'],
                ['Murid mampu mengenal dan mempraktikkan gerak dasar tari, memahami pola lantai dan irama, menampilkan tari secara berkelompok dengan baik, serta mampu mengikuti pertunjukan seni di lingkungan sekolah maupun kegiatan lainnya.'],
                ['Kreativitas', 'Kolaborasi']],
            ['Pencak Silat', 'fa-hand-fist', 'Kamis', 'Tutilawati, S.Pd', 'pencak-silat.jpg', '50% 40%',
                ['Membentuk karakter disiplin, sportivitas, dan penguasaan seni bela diri tradisional'],
                ['Siswa menunjukkan peningkatan keterampilan gerak serta menghargai budaya lokal', 'Medali Emas Kejuaraan Nasional'],
                ['Kemandirian', 'Kreativitas', 'Kolaborasi']],
            ['Robotic', 'fa-robot', 'Kamis', 'Alvin Maulana Sani', 'robotik.jpg', '50% 55%',
                ['Mengembangkan kemampuan Murid dalam berpikir logis, kreatif, kritis, dan sistematis melalui kegiatan perancangan, perakitan, pemrograman, dan pengoperasian robot. Kegiatan ini juga bertujuan menumbuhkan keterampilan pemecahan masalah, kerja sama, ketelitian, serta pengenalan teknologi dan kecerdasan komputasional.'],
                ['Murid mampu mengenal komponen dasar robotik, merakit robot sederhana, memahami dasar pemrograman dan sensor, mengoperasikan robot sesuai instruksi, menyelesaikan permasalahan sederhana melalui proyek robotik, serta menghasilkan minimal satu karya/proyek robotik sederhana.'],
                ['Penalaran Kritis', 'Kreativitas']],
        ];

        foreach ($awal as $i => [$nama, $ikon, $waktu, $pembimbing, $foto, $posisi, $tujuan, $target, $profil]) {
            DB::table('ekstrakurikuler')->insert([
                'nama' => $nama, 'ikon' => $ikon, 'waktu' => $waktu, 'pembimbing' => $pembimbing,
                'foto' => $foto, 'posisi_foto' => $posisi,
                'tujuan' => implode("\n", $tujuan), 'target' => implode("\n", $target), 'profil' => implode("\n", $profil),
                'urutan' => $i + 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ekstrakurikuler');
    }
};
