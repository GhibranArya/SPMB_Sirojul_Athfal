{{-- Form ganti kata sandi (dipakai portal pendaftar dan panel admin). Butuh $wajib (bool). --}}
<div class="page-title">
    <h1>Ganti Kata Sandi</h1>
    <p>Gunakan kata sandi yang sulit ditebak dan jangan dibagikan kepada siapa pun.</p>
</div>

@if ($wajib)
    <div class="note-box"><b><i class="fa-solid fa-key"></i> Anda memakai kata sandi sementara</b>
        Untuk keamanan akun, buat kata sandi baru Anda sendiri sebelum melanjutkan.</div>
@endif

@if ($errors->any())
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><span>Ada isian yang perlu diperbaiki. Periksa kolom bertanda merah.</span></div>
@endif

<form action="/ganti-sandi" method="POST" data-once novalidate style="max-width:520px">
    @csrf
    <div class="card">
        <div class="field {{ $errors->has('current_password') ? 'has-error' : '' }}">
            <label for="current_password">{{ $wajib ? 'Kata sandi sementara' : 'Kata sandi saat ini' }} <span class="req">*</span></label>
            <input class="form-control" type="password" id="current_password" name="current_password" autocomplete="current-password" required>
            @error('current_password')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
        </div>
        <div class="field {{ $errors->has('password') ? 'has-error' : '' }}">
            <label for="password">Kata sandi baru <span class="req">*</span></label>
            <input class="form-control" type="password" id="password" name="password" autocomplete="new-password" required>
            @error('password')<div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
            @else<div class="hint">Minimal 8 karakter, memuat huruf dan angka.</div>@enderror
        </div>
        <div class="field">
            <label for="password_confirmation">Ulangi kata sandi baru <span class="req">*</span></label>
            <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
        </div>
        <div class="form-actions" style="margin-top:6px">
            <span></span>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan kata sandi</button>
        </div>
    </div>
</form>
