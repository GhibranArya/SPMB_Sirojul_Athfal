{{-- Navbar halaman publik. Opsi: $scrolled (true = langsung bergaya "scrolled", dipakai di halaman berhero gelap). --}}
@php $path = request()->path(); @endphp
<a href="#main" class="skip-link">Lewati ke konten utama</a>
<nav class="navbar{{ ($scrolled ?? false) ? ' scrolled' : '' }}" id="navbar">
    <div class="container navbar-container">
        <a href="/" class="navbar-brand">
            <img src="{{ asset('images/logo/logo.png') }}" alt="Logo {{ config('sekolah.nama') }}" class="logo-icon" width="44" height="44">
            <div class="brand-text">
                <span class="brand-name">{{ config('sekolah.nama') }}</span>
                <span class="brand-sub">MI &amp; SMP · {{ config('sekolah.wilayah') }}</span>
            </div>
        </a>

        <div class="navbar-nav" id="navbarNav">
            <a href="/" class="{{ $path === '/' ? 'active' : '' }}">Beranda</a>
            <a href="/profil" class="{{ $path === 'profil' ? 'active' : '' }}">Profil Sekolah</a>
            <a href="/fasilitas" class="{{ $path === 'fasilitas' ? 'active' : '' }}">Fasilitas</a>
            <a href="/unit-pendidikan" class="{{ $path === 'unit-pendidikan' ? 'active' : '' }}">Unit Pendidikan</a>
            <a href="/struktur-organisasi" class="{{ $path === 'struktur-organisasi' ? 'active' : '' }}">Struktur Organisasi</a>
            <a href="/login" class="nav-daftar"><i class="fa-solid fa-user-plus"></i> Daftar {{ config('sekolah.spmb') }} Online</a>
        </div>

        <div class="navbar-cta">
            <a href="/login" class="btn btn-accent"><i class="fa-solid fa-user-plus"></i> Daftar {{ config('sekolah.spmb') }} Online</a>
        </div>

        <button type="button" class="navbar-toggle" id="navToggle" aria-label="Buka menu" aria-expanded="false" aria-controls="navbarNav">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</nav>
