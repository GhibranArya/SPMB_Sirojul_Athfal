<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#FFFFFF">
    <title>Profil Sekolah - {{ config('sekolah.nama') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/favicon.png') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tegas.css') }}">
</head>
<body>

    @include('partials.publik-nav', ['scrolled' => true])

    <main id="main" tabindex="-1">

    <!-- PAGE HERO HEADER -->
    <section class="page-hero">
        <div class="page-hero-overlay"></div>
        <div class="container page-hero-container">
            <div class="breadcrumb">
                <a href="/"><i class="fa-solid fa-house" aria-hidden="true"></i> Beranda</a>
                <span class="separator">/</span>
                <span class="current">Profil Sekolah</span>
            </div>
            <div class="hero-badge-pill">
                <i class="fa-solid fa-star" aria-hidden="true"></i> NPSN {{ $profil->npsn }}
            </div>
            <h1 class="page-hero-title">Profil SMP Islam Sirojul Athfal</h1>
            <p class="page-hero-desc">
                SMP Islam Sirojul Athfal berada di lingkungan yang kondusif untuk mendukung penyelenggaraan proses pembelajaran.
            </p>
        </div>
    </section>

    <!-- LETAK GEOGRAFIS -->
    <section class="section sejarah-section">
        <div class="container">
            <div class="sejarah-grid">
                <div class="sejarah-content">
                    <div class="section-badge-sm">KELEMBAGAAN</div>
                    <h2 class="section-title text-left">Analisis Letak Geografis</h2>
                    <p class="paragraph-text">
                        SMP Islam Sirojul Athfal didirikan pada tahun 1991 dan mendapatkan ijin operasionalnya yang diterbitkan oleh Dinas Pendidikan Kabupaten Bogor pada tanggal 10 Juli 2001 dengan nomor 1855/I02.5/PR/2001. Sekolah ini berdiri untuk memenuhi aspirasi masyarakat sekitar yang menginginkan adanya sekolah jenjang SMP di sekitar.
                    </p>
                    <p class="paragraph-text">
                        Sebagai salah satu SMP swasta yang beralamat di Jl. KH. Muh Sanusi Kp. Lio Masjid RT 04/03 Desa Kedung Waringin Kecamatan Bojong Gede Kabupaten Bogor Provinsi Jawa Barat dengan NPSN 20200558 sudah turut serta menyelenggarakan layanan dan memajukan pendidikan Kabupaten Bogor selama 24 tahun dan sudah meluluskan 21 angkatan alumni.
                    </p>

                    <div class="sejarah-stats-row">
                        <div class="stat-mini-card">
                            <div class="stat-num">24</div>
                            <div class="stat-lbl">Tahun Pengabdian</div>
                        </div>
                        <div class="stat-mini-card">
                            <div class="stat-num">21</div>
                            <div class="stat-lbl">Angkatan Alumni</div>
                        </div>
                    </div>
                </div>

                <div class="sejarah-image-box">
                    <div class="image-wrapper">
                        <img src="{{ asset('images/foto/gedung-sekolah.jpg') }}" alt="Gedung SMP Islam Sirojul Athfal" style="object-position: 40% 50%;" loading="lazy">
                        <div class="image-floating-badge">
                            <div class="badge-icon"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></div>
                            <div class="badge-info">
                                <strong>Sarana dan Prasarana</strong>
                                <p>Sekolah memiliki ruang kelas, perpustakaan, lapangan, IFP (Interactive Flat Panel), laptop, serta akses internet yang dapat dimanfaatkan sebagai sumber dan media pembelajaran.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- TONGGAK -->
    <section class="section timeline-section">
        <div class="container">
            <div class="section-header">
                <div class="section-badge-sm">REKAM JEJAK</div>
                <h2 class="section-title">Tonggak <span>1991 – 2026</span></h2>
            </div>

            <div class="timeline-grid">
                <div class="timeline-card">
                    <div class="card-top">
                        <div class="year-badge">1991</div>
                        <div class="timeline-icon"><i class="fa-solid fa-home" aria-hidden="true"></i></div>
                    </div>
                    <h4>Didirikan</h4>
                    <p>SMP Islam Sirojul Athfal didirikan pada tahun 1991. Sekolah ini berdiri untuk memenuhi aspirasi masyarakat sekitar yang menginginkan adanya sekolah jenjang SMP di sekitar.</p>
                    <div class="card-sub-badge"><i class="fa-solid fa-leaf" aria-hidden="true"></i> Kabupaten Bogor</div>
                </div>

                <div class="timeline-card">
                    <div class="card-top">
                        <div class="year-badge">2001</div>
                        <div class="timeline-icon"><i class="fa-solid fa-stamp" aria-hidden="true"></i></div>
                    </div>
                    <h4>Ijin Operasional</h4>
                    <p>Ijin operasional diterbitkan oleh Dinas Pendidikan Kabupaten Bogor pada tanggal 10 Juli 2001 dengan nomor 1855/I02.5/PR/2001.</p>
                    <div class="card-sub-badge"><i class="fa-solid fa-building" aria-hidden="true"></i> 10 Juli 2001</div>
                </div>

                <div class="timeline-card">
                    <div class="card-top">
                        <div class="year-badge">2025</div>
                        <div class="timeline-icon"><i class="fa-solid fa-award" aria-hidden="true"></i></div>
                    </div>
                    <h4>Pramuka</h4>
                    <p>Juara Umum Tingkat Kwarran Bojonggede 2025</p>
                    <div class="card-sub-badge"><i class="fa-solid fa-flask" aria-hidden="true"></i> Ekstrakurikuler</div>
                </div>

                <div class="timeline-card highlighted">
                    <div class="card-top">
                        <div class="year-badge">2026</div>
                        <div class="timeline-icon"><i class="fa-solid fa-book-open" aria-hidden="true"></i></div>
                    </div>
                    <h4>Kurikulum Satuan Pendidikan</h4>
                    <p>Kurikulum Sekolah Menengah Pertama Islam Sirojul Athfal ditetapkan untuk dijalankan pada Tahun Ajaran 2026/2027.</p>
                    <div class="card-sub-badge light"><i class="fa-solid fa-star" aria-hidden="true"></i> 15 Juli 2026</div>
                </div>
            </div>
        </div>
    </section>

    <!-- VISI & MISI -->
    <section class="section visi-misi-section">
        <div class="container">
            <div class="section-header">
                <div class="section-badge-sm">VISI, MISI, DAN TUJUAN SATUAN PENDIDIKAN</div>
                <h2 class="section-title">Visi & Misi <span>Sekolah</span></h2>
            </div>

            <!-- Visi: banner berlatar foto -->
            <div class="vm-visi">
                <img class="vm-visi-bg" src="{{ asset('images/foto/halaman-pohon.jpg') }}" alt="" loading="lazy">
                <div class="vm-visi-body">
                    <div class="visi-badge"><i class="fa-solid fa-eye" aria-hidden="true"></i> VISI SEKOLAH</div>
                    <blockquote class="vm-quote">“{{ $profil->visi }}”</blockquote>
                </div>
            </div>

            <!-- Misi: kartu bernomor, dua kolom -->
            <div class="vm-misi-head">
                <span class="misi-badge"><i class="fa-solid fa-bullseye" aria-hidden="true"></i> MISI SEKOLAH</span>
            </div>
            <ol class="vm-misi-list">
                @foreach ($profil->daftarMisi() as $misi)
                    <li>
                        <span class="vm-no">{{ $loop->iteration }}</span>
                        <span>{{ $misi }}</span>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <!-- PRINSIP PENGEMBANGAN KURIKULUM -->
    <section class="section values-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Prinsip Pengembangan <span>Kurikulum</span></h2>
            </div>

            <div class="values-grid values-7">
                @foreach ([
                    ['fa-child-reaching', 'Berpusat pada Murid', 'Kurikulum dirancang sesuai dengan kebutuhan, potensi, minat, bakat, perkembangan, dan karakteristik murid.'],
                    ['fa-map-location-dot', 'Kontekstual', 'Kurikulum disesuaikan dengan kondisi lingkungan, budaya, potensi daerah, serta kebutuhan masyarakat.'],
                    ['fa-sliders', 'Fleksibel', 'Kurikulum dapat disesuaikan dengan perkembangan ilmu pengetahuan, teknologi, kebutuhan murid, dan kondisi sekolah.'],
                    ['fa-bullseye', 'Relevan', 'Kurikulum dikembangkan sesuai dengan perkembangan ilmu pengetahuan, teknologi, dan kebutuhan murid serta tuntutan kehidupan di masyarakat.'],
                    ['fa-arrows-rotate', 'Berkelanjutan', 'Kurikulum dikembangkan secara berkesinambungan berdasarkan hasil evaluasi, refleksi, dan kebutuhan satuan pendidikan.'],
                    ['fa-handshake', 'Kolaboratif', 'Pengembangan dan pelaksanaan kurikulum melibatkan kepala sekolah, guru, tenaga kependidikan, murid, orang tua, dan pihak terkait sesuai dengan peran masing-masing.'],
                    ['fa-mosque', 'Berkarakter dan Berbasis Nilai Keagamaan', 'Kurikulum dikembangkan dengan mengintegrasikan nilai-nilai keimanan, ketakwaan, akhlakul karimah, kedisiplinan, tanggung jawab, dan kepedulian sosial dalam kehidupan sekolah.'],
                ] as [$ikon, $judul, $isi])
                    <div class="value-card">
                        <div class="val-icon"><i class="fa-solid {{ $ikon }}" aria-hidden="true"></i></div>
                        <h4>{{ $judul }}</h4>
                        <p>{{ $isi }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- KEPALA SEKOLAH & PEMBIMBING -->
    <section class="section org-section">
        <div class="container">
            <div class="section-header">
                <div class="section-badge-sm">PENANGGUNG JAWAB</div>
                <h2 class="section-title">Kepala Sekolah & <span>Pembimbing Ekstrakurikuler</span></h2>
            </div>

            <div class="org-tree">
                <div class="org-level level-1">
                    <div class="org-card top-pembina">
                        <div class="org-role-badge">PENANGGUNG JAWAB</div>
                        <h4>Wildan Abdul Aziz, S.M</h4>
                        <span>Kepala SMP Islam Sirojul Athfal</span>
                    </div>
                </div>

                <div class="org-line"></div>

                <div class="org-level level-4">
                    <div class="org-card unit-head-card">
                        <span class="unit-tag">Pramuka</span>
                        <h4>Siti Muzayyana Mukaromah, S.Pd.I</h4>
                        <small>Hari Jumat</small>
                    </div>

                    <div class="org-card unit-head-card">
                        <span class="unit-tag">Futsal</span>
                        <h4>Fikri Fakhrudin, S.Pd</h4>
                        <small>Rabu</small>
                    </div>

                    <div class="org-card unit-head-card">
                        <span class="unit-tag">Seni Tari</span>
                        <h4>Tutilawati, S.Pd</h4>
                        <small>Rabu</small>
                    </div>

                    <div class="org-card unit-head-card">
                        <span class="unit-tag">Pencak Silat</span>
                        <h4>Tutilawati, S.Pd</h4>
                        <small>Kamis</small>
                    </div>

                    <div class="org-card unit-head-card">
                        <span class="unit-tag">Robotic</span>
                        <h4>Alvin Maulana Sani</h4>
                        <small>Kamis</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- LEGALITAS -->
    <section class="section legal-section">
        <div class="container">
            <div class="section-header-flex">
                <div class="left">
                    <div class="section-badge-sm">DINAS PENDIDIKAN KABUPATEN BOGOR</div>
                    <h2 class="section-title">Legalitas <span>Sekolah</span></h2>
                </div>
            </div>

            <div class="legal-grid">
                <div class="legal-card">
                    <div class="legal-icon"><i class="fa-solid fa-stamp" aria-hidden="true"></i></div>
                    <h4>Ijin Operasional</h4>
                    <p>Ijin operasionalnya yang diterbitkan oleh Dinas Pendidikan Kabupaten Bogor pada tanggal 10 Juli 2001</p>
                    <div class="legal-badge-number">NO: 1855/I02.5/PR/2001</div>
                </div>

                <div class="legal-card">
                    <div class="legal-icon"><i class="fa-solid fa-id-card" aria-hidden="true"></i></div>
                    <h4>NPSN</h4>
                    <p>SMP swasta yang beralamat di Desa Kedung Waringin Kecamatan Bojong Gede Kabupaten Bogor Provinsi Jawa Barat</p>
                    <div class="legal-badge-number">NPSN {{ $profil->npsn }}</div>
                </div>

                <div class="legal-card">
                    <div class="legal-icon"><i class="fa-solid fa-file-contract" aria-hidden="true"></i></div>
                    <h4>Penetapan Kurikulum</h4>
                    <p>Kurikulum Sekolah Menengah Pertama Islam Sirojul Athfal ditetapkan untuk dijalankan pada Tahun Ajaran 2026/2027.</p>
                    <div class="legal-badge-number">Bogor, 15 Juli 2026</div>
                </div>
            </div>
        </div>
    </section>

    @include('partials.publik-cta')

    </main>

    @include('partials.publik-footer')

    <script>
        // Mobile Menu Toggle
        const navToggle = document.getElementById('navToggle');
        const navbarNav = document.getElementById('navbarNav');

        navToggle.addEventListener('click', () => {
            const open = navbarNav.classList.toggle('active');
            navToggle.setAttribute('aria-expanded', open);
            navToggle.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
        });
    </script>
</body>
</html>
