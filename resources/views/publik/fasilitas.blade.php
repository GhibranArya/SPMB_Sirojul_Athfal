<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#FFFFFF">
    <title>Fasilitas | {{ config('sekolah.nama') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/favicon.png') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tambahan kecil khusus halaman fasilitas -->
    <style>
        .sarpras-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 16px; }
        .sarpras-item { display: flex; align-items: center; gap: 14px; background: var(--white); border: 1px solid var(--gray-300); border-radius: var(--radius-md); padding: 18px 20px; transition: var(--transition); }
        .sarpras-item:hover { border-color: var(--primary-pale); transform: translateY(-3px); }
        .sarpras-item .s-icon { flex-shrink: 0; width: 44px; height: 44px; border-radius: 50%; background: var(--primary-pale); color: var(--primary); display: grid; place-items: center; font-size: 1.05rem; }
        .sarpras-item span { font-size: 0.92rem; font-weight: 600; color: var(--gray-900); line-height: 1.35; }
        .f-list li { display: flex; align-items: flex-start; gap: 8px; font-size: 0.85rem; margin-bottom: 10px; color: var(--gray-700); }
        .f-list li i { color: var(--primary); margin-top: 3px; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tegas.css') }}">
</head>
<body>

    @include('partials.publik-nav')

    <main id="main" tabindex="-1">

    <!-- HERO FASILITAS -->
    <section class="section text-center hero-halaman" style="background-color: var(--bg-cream); padding-top: 140px;">
        <div class="container">
            <div class="section-badge-sm" style="background: var(--white); border: 1px solid var(--primary-pale); padding: 8px 16px; border-radius: 50px;">
                <i class="fa-solid fa-building-columns" aria-hidden="true"></i> SARANA DAN PRASARANA
            </div>
            <h1 class="section-title" style="font-size: 2.8rem; max-width: 800px; margin: 0 auto 20px;">
                Sarana dan <span>Prasarana</span>
            </h1>
            <p class="section-desc">
                SMP Islam Sirojul Athfal memiliki sarana dan prasarana yang digunakan untuk mendukung pelaksanaan proses pembelajaran, kegiatan administrasi, pengembangan minat dan bakat Murid, serta kegiatan pembiasaan dan pengembangan karakter.
            </p>
        </div>
    </section>

    <!-- DAFTAR SARANA DAN PRASARANA -->
    <section class="section">
        <div class="container">
            <div class="section-header-flex">
                <div class="left">
                    <div class="section-badge-sm">Sarana dan prasarana yang tersedia meliputi</div>
                    <h2 class="section-title">Sarana dan Prasarana <span>Sekolah</span></h2>
                </div>
            </div>

            <div class="sarpras-grid">
                @foreach ($fasilitas as $f)
                    <div class="sarpras-item">
                        <div class="s-icon"><i class="fa-solid {{ $f->ikon ?? 'fa-building' }}" aria-hidden="true"></i></div>
                        <span>{{ $f->nama }}</span>
                    </div>
                @endforeach
            </div>

            <p class="paragraph-text" style="margin-top: 32px; max-width: 900px;">
                Secara umum, sarana dan prasarana yang dimiliki sekolah telah dimanfaatkan untuk mendukung kegiatan pembelajaran dan kegiatan pengembangan Murid. Sekolah memiliki ruang kelas, perpustakaan, lapangan, IFP (Interactive Flat Panel), laptop, serta akses internet yang dapat dimanfaatkan sebagai sumber dan media pembelajaran. Pemanfaatan sarana tersebut diarahkan untuk menciptakan pembelajaran yang lebih interaktif, menarik, kontekstual, dan sesuai dengan kebutuhan murid.
            </p>
        </div>
    </section>

    <!-- LINGKUNGAN SEKOLAH -->
    <section class="section" style="background-color: var(--bg-cream);">
        <div class="container text-center mb-5">
            <div class="section-badge-sm">Kelembagaan</div>
            <h2 class="section-title">Lingkungan Sekolah</h2>
        </div>

        <div class="container grid-3 text-left">
            <div class="stat-mini-card" style="border-top: 4px solid var(--primary); text-align: left;">
                <div class="badge-icon" style="margin-bottom: 20px;">
                    <i class="fa-solid fa-school" aria-hidden="true"></i>
                </div>
                <h4 style="font-size: 1.1rem; margin-bottom: 12px;">Lingkungan yang Kondusif</h4>
                <p style="font-size: 0.85rem; color: var(--gray-500); line-height: 1.6;">SMP Islam Sirojul Athfal berada di lingkungan yang kondusif untuk mendukung penyelenggaraan proses pembelajaran. Lingkungan sekolah memberikan suasana yang cukup nyaman bagi murid untuk mengikuti kegiatan pembelajaran maupun berbagai kegiatan pengembangan diri.</p>
            </div>
            <div class="stat-mini-card" style="border-top: 4px solid var(--accent); text-align: left;">
                <div class="badge-icon" style="background: #FFF9E6; color: var(--accent); margin-bottom: 20px;">
                    <i class="fa-solid fa-seedling" aria-hidden="true"></i>
                </div>
                <h4 style="font-size: 1.1rem; margin-bottom: 12px;">Potensi Lingkungan Sekitar</h4>
                <p style="font-size: 0.85rem; color: var(--gray-500); line-height: 1.6;">Potensi lingkungan sekitar sekolah dimanfaatkan sebagai sumber belajar kontekstual yang dapat mendukung pengalaman belajar murid.</p>
            </div>
            <div class="stat-mini-card" style="border-top: 4px solid var(--primary-light); text-align: left;">
                <div class="badge-icon" style="margin-bottom: 20px;">
                    <i class="fa-solid fa-leaf" aria-hidden="true"></i>
                </div>
                <h4 style="font-size: 1.1rem; margin-bottom: 12px;">Aman, Bersih, Tertib, dan Nyaman</h4>
                <p style="font-size: 0.85rem; color: var(--gray-500); line-height: 1.6;">Lingkungan sekolah yang aman, bersih, tertib, dan nyaman menjadi salah satu faktor pendukung dalam menciptakan suasana belajar yang efektif.</p>
            </div>
        </div>
    </section>

    <!-- LOKASI -->
    <section class="section" id="lokasi">
        <div class="container grid-2 align-center">
            <div>
                <div class="section-badge-sm"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Analisis Letak Geografis</div>
                <h2 class="section-title">SMP Islam Sirojul Athfal Kabupaten Bogor</h2>
                <p class="paragraph-text">Sebagai salah satu SMP swasta yang beralamat di Jl. KH. Muh Sanusi Kp. Lio Masjid RT 04/03 Desa Kedung Waringin Kecamatan Bojong Gede Kabupaten Bogor Provinsi Jawa Barat dengan NPSN 20200558 sudah turut serta menyelenggarakan layanan dan memajukan pendidikan Kabupaten Bogor selama 24 tahun dan sudah meluluskan 21 angkatan alumni.</p>

                <a href="https://www.google.com/maps/search/?api=1&query=SMP+Islam+Sirojul+Athfal+Kedung+Waringin+Bojong+Gede" target="_blank" rel="noopener" class="btn btn-outline-light" style="margin-top: 10px;"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i> Buka Petunjuk Arah di Google Maps</a>
            </div>
            <div class="sejarah-image-box">
                <div class="image-wrapper" style="height: 400px;">
                    <img src="{{ asset('images/foto/halaman-sekolah.jpg') }}" alt="Halaman dan deretan ruang kelas SMP Islam Sirojul Athfal" style="width: 100%; height: 100%; object-fit: cover; object-position: 40% 50%;" loading="lazy">
                    <div class="image-floating-badge" style="justify-content: center; text-align: center; flex-direction: column; gap: 5px;">
                        <div style="color: var(--primary); font-size: 2rem;"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></div>
                        <h4 style="font-size: 1.1rem; color: var(--gray-900);">SMP Islam Sirojul Athfal</h4>
                        <p style="font-size: 0.85rem; color: var(--gray-500);">Desa Kedung Waringin Kecamatan Bojong Gede Kabupaten Bogor</p>
                    </div>
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

        // Navbar Scroll
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>
