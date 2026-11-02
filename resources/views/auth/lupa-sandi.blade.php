@extends('layouts.auth')

@section('title', 'Lupa Kata Sandi')

@section('top-link')
    <a href="{{ route('register') }}" class="pill">Daftar</a>
@endsection

@section('content')
    <h2>Lupa kata sandi?</h2>
    <p class="lead">Panitia {{ config('sekolah.spmb') }} akan membantu membuatkan kata sandi sementara untuk akun Anda.</p>

    <ol style="margin:0 0 20px 18px;display:grid;gap:10px;font-size:.95rem;line-height:1.5">
        <li>Hubungi panitia {{ config('sekolah.spmb') }} lewat WhatsApp dan sebutkan <b>email atau nomor HP</b> yang Anda daftarkan.</li>
        <li>Panitia mengirimkan <b>kata sandi sementara</b> kepada Anda.</li>
        <li>Masuk memakai kata sandi sementara itu, lalu buat kata sandi baru milik Anda sendiri.</li>
    </ol>

    @if ($waLink)
        <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn btn-primary btn-lg" style="width:100%;justify-content:center">
            <i class="fa-brands fa-whatsapp"></i> Hubungi Panitia lewat WhatsApp
        </a>
    @else
        <div class="alert alert-success" style="background:#FBF3DE;border-color:#D29D29;color:#6B5210"><i class="fa-solid fa-circle-info"></i>
            <span>Hubungi panitia {{ config('sekolah.spmb') }} langsung di sekolah pada hari kerja untuk dibantu mereset kata sandi.</span></div>
    @endif

    <div class="auth-switch">
        Sudah ingat? <a href="{{ route('login') }}">Kembali masuk</a>
    </div>
@endsection
