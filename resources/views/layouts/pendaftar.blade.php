<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.panel-head')
    <link rel="stylesheet" href="{{ asset('css/tegas.css') }}">
</head>
<body class="panel">
    <a href="#main" class="skip-link">Lewati ke konten utama</a>


    <header class="pd-header">
        <div class="pd-bar">
            <a href="/pendaftar/dashboard" class="pd-brand">
                <img src="{{ asset('images/logo/logo.png') }}" alt="Logo Yayasan" class="logo-icon" width="44" height="44">
                <div><strong>{{ config('sekolah.nama') }}</strong><small>PORTAL PENDAFTAR {{ config('sekolah.spmb') }}</small></div>
            </a>
            <div class="user-chip">
                <div class="avatar">{{ strtoupper(mb_substr($authUser->name, 0, 1)) }}</div>
                <div class="who"><b>{{ $authUser->name }}</b><span>Orang tua / wali</span></div>
                <a href="/ganti-sandi" class="link-btn" title="Ganti kata sandi"><i class="fa-solid fa-key" aria-hidden="true"></i></a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="link-btn" title="Keluar"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Keluar</button>
                </form>
            </div>
        </div>
        @php $path = request()->path(); @endphp
        <nav class="pd-nav" aria-label="Menu pendaftar">
            <a href="/pendaftar/dashboard" class="{{ $path === 'pendaftar/dashboard' ? 'active' : '' }}"><i class="fa-solid fa-gauge" aria-hidden="true"></i> Ringkasan</a>
            <a href="/pendaftar/form-data" class="{{ $path === 'pendaftar/form-data' ? 'active' : '' }}"><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i> Form Data</a>
            <a href="/pendaftar/upload-berkas" class="{{ $path === 'pendaftar/upload-berkas' ? 'active' : '' }}"><i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i> Upload Berkas</a>
            <a href="/pendaftar/pengumuman" class="{{ $path === 'pendaftar/pengumuman' ? 'active' : '' }}"><i class="fa-regular fa-bell" aria-hidden="true"></i> Pengumuman</a>
        </nav>
    </header>

    <main class="pd-wrap" id="main" tabindex="-1">
        @include('partials.flash')
        @yield('content')
    </main>

    <script src="{{ asset('js/panel.js') }}"></script>
    @stack('scripts')
</body>
</html>
