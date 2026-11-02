@extends('layouts.auth')

@section('title', 'Masuk')

@section('top-link')
    <a href="{{ route('register') }}" class="pill">Daftar</a>
@endsection

@section('content')
    <h2>Masuk ke akun Anda</h2>
    <p class="lead">Lanjutkan pendaftaran atau lihat status {{ config('sekolah.spmb') }} putra-putri Anda.</p>

    @if (session('status'))
        <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif

    <form action="{{ route('login') }}" method="POST" novalidate>
        @csrf

        <div class="field {{ $errors->has('username') ? 'has-error' : '' }}">
            <label for="username">Email atau No. HP</label>
            <div class="input-wrap">
                <i class="fa-regular fa-user lead-icon"></i>
                <input type="text" id="username" name="username" value="{{ old('username') }}"
                       placeholder="wali@email.com atau 0812xxxxxxxx" autocomplete="username" autofocus required>
            </div>
            @error('username')
                <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
            @enderror
        </div>

        <div class="field {{ $errors->has('password') ? 'has-error' : '' }}">
            <label for="password">Kata Sandi</label>
            <div class="input-wrap">
                <i class="fa-solid fa-lock lead-icon"></i>
                <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" autocomplete="current-password" required>
                <button type="button" class="toggle-pass" data-target="password" aria-label="Tampilkan kata sandi"><i class="fa-regular fa-eye"></i></button>
            </div>
            @error('password')
                <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
            @enderror
        </div>

        <div class="row-between">
            <label class="check" for="remember">
                <input type="checkbox" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                <span>Ingat saya</span>
            </label>
            <a href="/lupa-sandi">Lupa kata sandi?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-lg">Masuk <i class="fa-solid fa-arrow-right"></i></button>
    </form>

    <div class="auth-switch">
        Orang tua baru? <a href="{{ route('register') }}">Buat akun pendaftaran</a>
    </div>
    <p class="auth-help"><i class="fa-brands fa-whatsapp"></i> Butuh bantuan? Hubungi panitia {{ config('sekolah.spmb') }} lewat WhatsApp.</p>
@endsection
