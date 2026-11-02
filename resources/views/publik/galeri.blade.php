<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Kegiatan | {{ config('sekolah.nama') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/favicon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;1,9..144,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/galeri.css') }}">
</head>
<body>

    @include('partials.publik-nav')

    <main id="main" tabindex="-1">

    <section class="section text-center" style="background-color: var(--bg-cream); padding-top: 140px; padding-bottom: 30px;">
        <div class="container">
            <div class="section-badge-sm" style="background: var(--white); border: 1px solid var(--primary-pale); padding: 8px 16px; border-radius: 50px;">
                <i class="fa-solid fa-images" aria-hidden="true"></i> GALERI
            </div>
            <h1 class="section-title" style="font-size: 2.6rem; max-width: 800px; margin: 0 auto 16px; text-wrap: balance;">
                Galeri <span>Kegiatan</span>
            </h1>
            <p class="section-desc">Momen belajar, ibadah, dan kegiatan siswa di {{ config('sekolah.unit.MI') }} dan {{ config('sekolah.unit.SMP') }}.</p>
        </div>
    </section>

    @if ($galeri->isNotEmpty())
        @include('partials.publik-galeri', ['ringkas' => false])
    @else
        <section class="section"><div class="container"><p class="gl-empty">Belum ada foto di galeri.</p></div></section>
    @endif

    </main>

    @include('partials.publik-footer')

    <script>
        const navToggle = document.getElementById('navToggle');
        const navbarNav = document.getElementById('navbarNav');
        navToggle.addEventListener('click', () => {
            const open = navbarNav.classList.toggle('active');
            navToggle.setAttribute('aria-expanded', open);
            navToggle.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
        });
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
        });
    </script>
</body>
</html>
