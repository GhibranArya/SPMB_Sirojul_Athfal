@extends('layouts.admin')

@section('title', 'Profil & Kontak')
@section('crumb', 'Profil & Kontak')

@section('content')
<div class="page-title">
    <h1>Profil &amp; Kontak</h1>
    <p>Visi, misi, alamat, dan kontak sekolah. Perubahan langsung tampil di beranda, halaman profil, dan footer situs.</p>
</div>

@if ($errors->any())
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><span>Ada isian yang belum benar. Periksa kolom bertanda merah.</span></div>
@endif

<form method="POST" action="/admin/konten/profil" data-once>
    @csrf
    @method('PUT')

    <div class="card">
        <div class="form-section">Visi dan misi</div>
        <div class="field {{ $errors->has('visi') ? 'has-error' : '' }}">
            <label for="visi">Visi <span class="req">*</span></label>
            <textarea class="form-control" id="visi" name="visi" rows="3" required>{{ old('visi', $profil->visi) }}</textarea>
            @error('visi')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
        </div>
        <div class="field {{ $errors->has('misi') ? 'has-error' : '' }}">
            <label for="misi">Misi <span class="req">*</span></label>
            <textarea class="form-control" id="misi" name="misi" rows="12" required>{{ old('misi', $profil->misi) }}</textarea>
            <div class="hint">Satu butir misi per baris. Setiap baris menjadi satu kartu bernomor di halaman Profil.</div>
            @error('misi')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="card">
        <div class="form-section">Alamat dan identitas</div>
        <div class="form-grid">
            <div class="field full {{ $errors->has('alamat') ? 'has-error' : '' }}">
                <label for="alamat">Alamat <span class="req">*</span></label>
                <textarea class="form-control" id="alamat" name="alamat" rows="2" required>{{ old('alamat', $profil->alamat) }}</textarea>
                @error('alamat')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
            </div>
            <div class="field {{ $errors->has('npsn') ? 'has-error' : '' }}">
                <label for="npsn">NPSN</label>
                <input class="form-control" id="npsn" name="npsn" value="{{ old('npsn', $profil->npsn) }}" maxlength="20">
                @error('npsn')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="card">
        <div class="form-section">Kontak</div>
        <p class="sub">Kontak hanya tampil di situs bila diisi. Nomor WhatsApp dipakai pada tombol "Hubungi via WhatsApp" dan tautan bantuan kata sandi.</p>
        <div class="form-grid">
            <div class="field {{ $errors->has('telepon') ? 'has-error' : '' }}">
                <label for="telepon">Telepon</label>
                <input class="form-control" id="telepon" name="telepon" value="{{ old('telepon', $profil->telepon) }}" placeholder="021-1234567" inputmode="tel">
                @error('telepon')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
            </div>
            <div class="field {{ $errors->has('whatsapp') ? 'has-error' : '' }}">
                <label for="whatsapp">WhatsApp panitia / admin sekolah</label>
                <input class="form-control" id="whatsapp" name="whatsapp" value="{{ old('whatsapp', $profil->whatsapp) }}" placeholder="0812-3456-7890" inputmode="tel">
                @error('whatsapp')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
            </div>
            <div class="field full {{ $errors->has('email') ? 'has-error' : '' }}">
                <label for="email">Email</label>
                <input class="form-control" type="email" id="email" name="email" value="{{ old('email', $profil->email) }}" placeholder="info@sekolah.sch.id">
                @error('email')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="card">
        <div class="form-section">Media sosial</div>
        <p class="sub">Tempel alamat lengkap akun (diawali <code>https://</code>). Hanya akun yang diisi yang tampil sebagai ikon di footer situs.</p>
        <div class="form-grid">
            @foreach (['instagram' => ['Instagram', 'https://www.instagram.com/…'], 'tiktok' => ['TikTok', 'https://www.tiktok.com/@…'], 'facebook' => ['Facebook', 'https://www.facebook.com/…'], 'youtube' => ['YouTube', 'https://www.youtube.com/@…']] as $kolom => [$label, $contoh])
                <div class="field {{ $errors->has($kolom) ? 'has-error' : '' }}">
                    <label for="{{ $kolom }}">{{ $label }}</label>
                    <input class="form-control" type="url" id="{{ $kolom }}" name="{{ $kolom }}" value="{{ old($kolom, $profil->{$kolom}) }}" placeholder="{{ $contoh }}" inputmode="url" autocomplete="off">
                    @error($kolom)<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
                </div>
            @endforeach
        </div>
    </div>

    <div class="form-actions">
        <a href="/" target="_blank" rel="noopener" class="btn btn-white"><i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat situs</a>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan perubahan</button>
    </div>
</form>
@endsection
