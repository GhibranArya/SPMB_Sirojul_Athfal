<?php

/** Struktur organisasi MI dan SMP, TA 2026/2027. Sumber: dokumen "Struktur Organisasi Sekolah MI dan SMP" dari yayasan. */
return [
    'tahun_ajaran' => '2026/2027',

    'ketua' => ['jabatan' => 'Ketua Yayasan', 'nama' => 'H. Ahmad Baidowi'],

    // Layanan lintas unit (garis fungsional).
    'penunjang' => [
        ['jabatan' => 'Tata Usaha (TU)', 'nama' => 'Risnawati, S.Pd.I'],
        ['jabatan' => 'Sarpras MI & SMP', 'nama' => 'Fikri Fakhrudin, S.Pd'],
    ],

    'unit' => [
        'MI' => [
            'judul' => 'Unit Madrasah Ibtidaiyah (MI)',
            'pimpinan' => ['jabatan' => 'Kepala Madrasah', 'nama' => 'Hj. Titin Rostini, S.Ag'],
            'komite' => ['jabatan' => 'Komite MI', 'nama' => 'Euis Farida, S.Pd'],
            'bendahara' => ['jabatan' => 'Bendahara MI', 'nama' => 'Siti Kharidatul B.'],
            'kurikulum' => ['jabatan' => 'Kurikulum MI', 'nama' => 'Rosmayanti, S.Pd'],
            'kesiswaan' => ['jabatan' => 'Kesiswaan MI', 'nama' => 'Hartati, S.Pd.I'],
            'pelaksana' => 'Wali Kelas & Guru Pengajar MI',
            'peserta' => 'Siswa / Siswi MI',
        ],
        'SMP' => [
            'judul' => 'Unit Sekolah Menengah Pertama (SMP)',
            'pimpinan' => ['jabatan' => 'Kepala Sekolah SMP', 'nama' => 'Wildan Abdul Aziz, S.M'],
            'komite' => ['jabatan' => 'Komite SMP', 'nama' => 'Lisnawati'],
            'bendahara' => ['jabatan' => 'Bendahara SMP', 'nama' => 'Mahani Saman M., S.Pd'],
            'kurikulum' => ['jabatan' => 'Kurikulum SMP', 'nama' => 'Tuti Chairiyah, S.E'],
            'kesiswaan' => ['jabatan' => 'Kesiswaan SMP', 'nama' => 'Santi, S.E'],
            'pelaksana' => 'Wali Kelas & Guru Pengajar SMP',
            'peserta' => 'Siswa / Siswi SMP',
        ],
    ],

    // [jabatan, [label => nama] atau nama tunggal, tugas]
    'tugas' => [
        ['Ketua Yayasan', ['H. Ahmad Baidowi'], 'Penanggung jawab tertinggi lembaga, menetapkan kebijakan umum, pengawasan operasional MI & SMP.'],
        ['Kepala Madrasah (MI)', ['Hj. Titin Rostini, S.Ag'], 'Pemimpin operasional pendidikan MI, manajerial kurikulum, kepegawaian, dan pembinaan madrasah.'],
        ['Kepala Sekolah (SMP)', ['Wildan Abdul Aziz, S.M'], 'Pemimpin operasional pendidikan SMP, pengembangan mutu akademik, serta manajemen sekolah.'],
        ['Tata Usaha (TU)', ['Risnawati, S.Pd.I'], 'Pusat layanan administrasi, persuratan, kearsipan, dan pendataan lintas unit MI & SMP.'],
        ['Sarpras MI & SMP', ['Fikri Fakhrudin, S.Pd'], 'Pengelolaan, pemeliharaan, dan perencanaan sarana prasarana fasilitas pembelajaran kedua unit.'],
        ['Bendahara', ['MI' => 'Siti Kharidatul B.', 'SMP' => 'Mahani Saman M., S.Pd'], 'Pengelolaan keuangan internal unit masing-masing, pencatatan kas, dan pelaporan anggaran.'],
        ['Ur. Kurikulum', ['MI' => 'Rosmayanti, S.Pd', 'SMP' => 'Tuti Chairiyah, S.E'], 'Penyusunan jadwal, kalender akademik, perangkat pembelajaran, dan evaluasi kurikulum unit.'],
        ['Ur. Kesiswaan', ['MI' => 'Hartati, S.Pd.I', 'SMP' => 'Santi, S.E'], 'Pembinaan karakter siswa, kegiatan ekstrakurikuler, Kedisiplinan, dan kedinasan peserta didik.'],
        ['Komite Sekolah', ['MI' => 'Euis Farida, S.Pd', 'SMP' => 'Lisnawati'], 'Mitra strategis perwakilan orang tua murid dalam memberikan pertimbangan & dukungan program sekolah.'],
    ],

    'garis' => [
        ['kelas' => 'wewenang', 'judul' => '1. Garis Komando / Wewenang (Garis Tegas Utuh)', 'isi' => 'Menunjukkan jalur instruksi langsung dari Ketua Yayasan kepada Kepala Unit (MI & SMP), serta dari Kepala Unit kepada struktural di bawahnya (Kurikulum, Kesiswaan, Bendahara, Wali Kelas, Guru, hingga Siswa).'],
        ['kelas' => 'koordinasi', 'judul' => '2. Garis Koordinasi (Garis Putus-Putus)', 'isi' => 'Menunjukkan hubungan kemitraan dan konsultasi sejajar. Komite Sekolah berkoordinasi secara aktif dengan Kepala Sekolah/Madrasah dalam perencanaan dan evaluasi program tanpa memiliki wewenang instruksi langsung.'],
        ['kelas' => 'fungsional', 'judul' => '3. Garis Fungsional / Layanan (Garis Titik-Titik / Lintas Layanan)', 'isi' => 'Menunjukkan hubungan kerja lintas fungsi penunjang. Unit Tata Usaha (TU) dan Sarpras melayani kebutuhan administrasi dan sarana untuk unit MI maupun SMP secara terpadu demi kelancaran operasional pembelajaran.'],
    ],
];
