<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.panel-head')
    <link rel="stylesheet" href="{{ asset('css/tegas.css') }}">
</head>
<body class="panel">
    <a href="#main" class="skip-link">Lewati ke konten utama</a>
    @php $path = request()->path(); @endphp
    <div class="ad-shell">
        <aside class="ad-side" id="adSide">
            <a href="/admin/pendaftar" class="pd-brand">
                <img src="{{ asset('images/logo/logo.png') }}" alt="Logo Yayasan" class="logo-icon" width="44" height="44">
                <div><strong>{{ config('sekolah.nama') }}</strong><small>PANEL PANITIA {{ config('sekolah.spmb') }}</small></div>
            </a>
            <nav aria-label="Menu admin">
                <div class="grp">{{ config('sekolah.spmb') }}</div>
                <a href="/admin/pendaftar" class="{{ $path === 'admin/pendaftar' ? 'active' : '' }}"><i class="fa-solid fa-users" aria-hidden="true"></i> Pendaftar</a>
                @if ($authUser->isAdmin())
                <a href="/admin/hasil-seleksi" class="{{ $path === 'admin/hasil-seleksi' ? 'active' : '' }}"><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i> Hasil Seleksi</a>
                <div class="grp">Pengaturan</div>
                <a href="/admin/gelombang" class="{{ $path === 'admin/gelombang' ? 'active' : '' }}"><i class="fa-solid fa-calendar-days" aria-hidden="true"></i> Gelombang &amp; Kuota</a>
                @endif
                <div class="grp">Konten Situs</div>
                <a href="/admin/konten/profil" class="{{ $path === 'admin/konten/profil' ? 'active' : '' }}"><i class="fa-solid fa-address-card" aria-hidden="true"></i> Profil &amp; Kontak</a>
                <a href="/admin/konten/fasilitas" class="{{ $path === 'admin/konten/fasilitas' ? 'active' : '' }}"><i class="fa-solid fa-building" aria-hidden="true"></i> Fasilitas</a>
                <a href="/admin/konten/galeri" class="{{ $path === 'admin/konten/galeri' ? 'active' : '' }}"><i class="fa-solid fa-images" aria-hidden="true"></i> Galeri</a>
                <a href="/admin/konten/ekstrakurikuler" class="{{ $path === 'admin/konten/ekstrakurikuler' ? 'active' : '' }}"><i class="fa-solid fa-people-group" aria-hidden="true"></i> Ekstrakurikuler</a>
                <div class="grp">Situs</div>
                <a href="/" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> Lihat Website</a>
            </nav>
            <div class="foot">
                <b>{{ $authUser->name }}</b>
                <span>{{ $authUser->isKepsek() ? 'Kepala Sekolah' : 'Admin' }}</span>
                <a href="/ganti-sandi" class="link-btn" style="display:block;padding-left:0"><i class="fa-solid fa-key" aria-hidden="true"></i> Ganti Kata Sandi</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="link-btn"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Keluar</button>
                </form>
            </div>
        </aside>

        <div class="ad-main">

            <div class="ad-top">
                <div style="display:flex;gap:12px;align-items:center">
                    <button type="button" class="ad-menu-btn" id="menuBtn" aria-label="Menu" aria-expanded="false" aria-controls="adSide"><i class="fa-solid fa-bars" aria-hidden="true"></i></button>
                    <div class="crumb"><span class="crumb-root">Admin / </span><b>@yield('crumb')</b></div>
                </div>
                @php $ga = \App\Models\Gelombang::aktif(); @endphp
                <div class="crumb">
                    @if ($ga)
                        <span class="crumb-ta">TA <b>{{ $ga->tahun_ajaran }}</b> · {{ $ga->nama }}</span>
                        <span class="st {{ $ga->sedangDibuka() ? 'st-green' : 'st-gray' }}" style="margin-left:6px">{{ $ga->sedangDibuka() ? 'Dibuka' : 'Ditutup' }}</span>
                    @else
                        <span class="st st-gray">Tidak ada gelombang aktif</span>
                    @endif
                </div>
            </div>
            <div class="ad-content" id="main" tabindex="-1" role="main">
                @include('partials.flash')
                @yield('content')
            </div>
        </div>
    </div>

    <script src="{{ asset('js/panel.js') }}"></script>
    @stack('scripts')
</body>
</html>
