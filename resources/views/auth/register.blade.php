@extends('layouts.auth')

@section('title', 'Daftar Akun')

@section('top-link')
    <a href="{{ route('login') }}" class="pill">Masuk</a>
@endsection

@section('card-class', 'auth-card--wide')

@section('content')
    <h2>Buat akun pendaftaran</h2>
    <p class="lead">Akun ini dipakai orang tua/wali untuk mengisi formulir dan memantau status {{ config('sekolah.spmb') }}.</p>

    @if ($errors->any())
        <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span>Ada isian yang perlu diperbaiki. Periksa kolom bertanda merah.</span></div>
    @endif

    <form action="{{ route('register') }}" method="POST" novalidate>
        @csrf

        <div class="field {{ $errors->has('name') ? 'has-error' : '' }}">
            <label for="name">Nama lengkap orang tua/wali <span class="req">*</span></label>
            <div class="input-wrap">
                <i class="fa-regular fa-user lead-icon" aria-hidden="true"></i>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Sesuai KTP" autocomplete="name" autofocus required>
            </div>
            @error('name')
                <div class="field-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $message }}</div>
            @enderror
        </div>

        <div class="field-row">
            <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
                <label for="email">Email <span class="req">*</span></label>
                <div class="input-wrap">
                    <i class="fa-regular fa-envelope lead-icon" aria-hidden="true"></i>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="wali@email.com" autocomplete="email" required>
                </div>
                @error('email')
                    <div class="field-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $message }}</div>
                @enderror
            </div>

            <div class="field {{ $errors->has('phone') ? 'has-error' : '' }}">
                <label for="phone">No. WhatsApp <span class="req">*</span></label>
                <div class="input-wrap">
                    <i class="fa-solid fa-mobile-screen lead-icon" aria-hidden="true"></i>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="0812xxxxxxxx" inputmode="numeric" autocomplete="tel" required>
                </div>
                @error('phone')
                    <div class="field-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $message }}</div>
                @enderror
                <div class="hint"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> <b>Catatan:</b> gunakan nomor <b>WhatsApp</b> yang aktif, karena panitia menghubungi lewat WhatsApp.</div>
            </div>
        </div>

        <div class="field {{ $errors->has('password') ? 'has-error' : '' }}">
            <label for="password">Kata sandi <span class="req">*</span></label>
            <div class="input-wrap">
                <i class="fa-solid fa-lock lead-icon" aria-hidden="true"></i>
                <input type="password" id="password" name="password" placeholder="Minimal 8 karakter" autocomplete="new-password" required>
                <button type="button" class="toggle-pass" data-target="password" aria-label="Tampilkan kata sandi"><i class="fa-regular fa-eye" aria-hidden="true"></i></button>
            </div>
            @error('password')
                <div class="field-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $message }}</div>
            @else
                <div class="hint">Gunakan huruf dan angka, minimal 8 karakter.</div>
            @enderror
        </div>

        <div class="field">
            <label for="password_confirmation">Ulangi kata sandi <span class="req">*</span></label>
            <div class="input-wrap">
                <i class="fa-solid fa-lock lead-icon" aria-hidden="true"></i>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ketik ulang kata sandi" autocomplete="new-password" required>
                <button type="button" class="toggle-pass" data-target="password_confirmation" aria-label="Tampilkan kata sandi"><i class="fa-regular fa-eye" aria-hidden="true"></i></button>
            </div>
        </div>

        <div class="field {{ $errors->has('agree') ? 'has-error' : '' }}">
            <label class="check" for="agree">
                <input type="checkbox" id="agree" name="agree" value="1" {{ old('agree') ? 'checked' : '' }}>
                <span>Saya setuju data saya dan data calon siswa digunakan untuk proses {{ config('sekolah.spmb') }} sesuai <a href="#">Kebijakan Privasi</a>.</span>
            </label>
            @error('agree')
                <div class="field-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-lg">Buat Akun <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
    </form>

    <div class="auth-switch">
        Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
    </div>
@endsection
