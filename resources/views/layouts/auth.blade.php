<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ config('sekolah.nama') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/favicon.png') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;1,9..144,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body class="auth-page">
    <a href="#main" class="skip-link">Lewati ke konten utama</a>
    <div class="auth-shell">

        <div class="auth-bg" aria-hidden="true"></div>

        <header class="auth-top">
            <a href="/" class="auth-brand">
                <img src="{{ asset('images/logo/logo.png') }}" alt="Logo {{ config('sekolah.nama') }}" width="44" height="44">
                <div>
                    <strong>{{ config('sekolah.nama') }}</strong>
                    <small>KABUPATEN BOGOR</small>
                </div>
            </a>
            <nav class="auth-top-links">
                <a href="/" class="back"><i class="fa-solid fa-arrow-left"></i> <span>Beranda</span></a>
                @yield('top-link')
            </nav>
        </header>

        <main class="auth-main" id="main" tabindex="-1">
            <div class="auth-card @yield('card-class')">
                @yield('content')
            </div>

            <ol class="auth-steps" aria-label="Alur pendaftaran">
                <li><b>1</b> Buat akun</li>
                <li><b>2</b> Isi formulir</li>
                <li><b>3</b> Unggah berkas</li>
            </ol>
        </main>

        <footer class="auth-foot">
            &copy; {{ date('Y') }} {{ config('sekolah.nama') }} &middot; <a href="#">Kebijakan Privasi</a> &middot; <a href="#">Syarat &amp; Ketentuan</a>
        </footer>
    </div>

    <script>
        document.querySelectorAll('.toggle-pass').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.dataset.target);
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                btn.querySelector('i').className = show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
            });
        });
    </script>
</body>
</html>
