@extends('layouts.pendaftar')

@section('title', 'Form Data')

@section('content')
@php
    $p = $pendaftaran;
    $sw = $p['siswa'] ?? [];
    $ot = $p['ortu'] ?? [];
    $locked = $p && ! in_array($p['status'], ['draft', 'perbaikan'], true);
    $tutup = ! $p && ! $gelombang['dibuka'];
    $disabled = $locked || $tutup || ($terlewat ?? false) || ($terkunciPengumuman ?? false);
    $v = fn ($key, $fallback = '') => old($key, $fallback);
    $err = fn ($f) => $errors->first($f);
@endphp

<div class="page-title">
    <h1>Formulir Pendaftaran {{ config('sekolah.nama') }}</h1>
    <p>{{ $gelombang['nama'] }} · TA {{ $gelombang['tahun_ajaran'] }} · ditutup {{ $gelombang['tutup'] }}</p>
</div>

@include('partials.pendaftar-terlewat')
@if ($daftarLagi ?? null)
    <div class="alert alert-success"><i class="fa-solid fa-rotate-right" aria-hidden="true"></i><span>Anda mendaftar kembali pada <b>{{ $gelombang['nama'] }}</b>. Pendaftaran sebelumnya ({{ $sebelumnya['kode'] }} · {{ $sebelumnya['gelombang'] }}) tetap tersimpan sebagai riwayat dan tidak terhapus.</span></div>
@endif
@if ($locked)
    <div class="alert alert-success"><i class="fa-solid fa-lock" aria-hidden="true"></i><span>Formulir sudah dikirim dan sedang diproses, jadi tidak dapat diubah. Hubungi panitia bila ada data yang salah.</span></div>
@elseif ($tutup)
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span>Pendaftaran sedang ditutup. Silakan menunggu gelombang berikutnya.</span></div>
@endif
@if ($errors->any())
    <div class="alert alert-error" role="alert"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span>Ada isian yang perlu diperbaiki. Periksa kolom bertanda merah.</span></div>
@endif

<form action="/pendaftar/form-data" method="POST" novalidate>
    @csrf
    <fieldset @disabled($disabled) style="border:0;padding:0;min-width:0">

        <div class="card">
            <div class="form-section">1 · Jenjang yang dituju</div>
            @php
                $jenjangDipilih = $v('jenjang', $p['jenjang'] ?? '');
                $jenjangKunci = $p['terkirim'] ?? false;
            @endphp
            <div class="field {{ $err('jenjang') ? 'has-error' : '' }}" style="max-width:420px">
                <label for="jenjang">Jenjang <span class="req">*</span></label>
                <select class="form-control" id="jenjang" name="jenjang" autocomplete="off" @disabled($jenjangKunci)>
                    <option value="">Pilih jenjang…</option>
                    @foreach ($daftarJenjang as $kode => $nama)
                        <option value="{{ $kode }}" {{ $jenjangDipilih === $kode ? 'selected' : '' }}>{{ config('sekolah.unit.'.$kode, $nama) }}</option>
                    @endforeach
                </select>
                @if ($err('jenjang'))<div class="field-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $err('jenjang') }}</div>
                @elseif ($jenjangKunci)<div class="hint">Jenjang tidak dapat diubah setelah formulir dikirim. Hubungi panitia bila salah pilih.</div>
                @else<div class="hint">Berkas yang diminta menyesuaikan jenjang: MI (KK, Akta, KTP ayah, KTP ibu); SMP ditambah Ijazah terakhir / SKL.</div>@endif
            </div>
            @foreach ($biaya as $kode => $baris)
                @if ($baris)
                    <details class="biaya-rinci" data-jenjang="{{ $kode }}" @if ($jenjangDipilih !== $kode) hidden @endif>
                        <summary><i class="fa-solid fa-receipt" aria-hidden="true"></i> Rincian biaya {{ config('sekolah.unit.'.$kode, $kode) }} · {{ $gelombang['nama'] }}</summary>
                        <table class="biaya-tbl">
                            @foreach ($baris as $b)
                                <tr><td>{{ $b['nama'] }}</td><td class="{{ $b['gratis'] ? 'gratis' : '' }}">{{ $b['tampil'] }}</td></tr>
                            @endforeach
                        </table>
                        <p class="hint" style="margin:8px 0 0">Data biaya masih bersifat sementara dan dapat berubah sesuai ketetapan panitia.</p>
                    </details>
                @endif
            @endforeach
        </div>

        <div class="card">
            <div class="form-section">2 · Data calon siswa</div>
            <div class="form-grid">
                <div class="field full {{ $err('nama') ? 'has-error' : '' }}">
                    <label for="nama">Nama lengkap <span class="req">*</span></label>
                    <input class="form-control" id="nama" name="nama" autocomplete="off" value="{{ $v('nama', $sw['nama'] ?? '') }}" placeholder="Sesuai akta, mis. Ahmad Fauzi…">
                    @if ($err('nama'))<div class="field-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $err('nama') }}</div>@endif
                </div>
                <div class="field {{ $err('nik') ? 'has-error' : '' }}">
                    <label for="nik">NIK <span class="req">*</span></label>
                    <input class="form-control" id="nik" name="nik" inputmode="numeric" maxlength="16" autocomplete="off" spellcheck="false" value="{{ $v('nik', $sw['nik'] ?? '') }}" placeholder="16 digit, mis. 3201xxxxxxxxxxxx…">
                    @if ($err('nik'))<div class="field-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $err('nik') }}</div>
                    @else<div class="hint">Tertera di Kartu Keluarga. Satu NIK hanya bisa mendaftar sekali per tahun ajaran.</div>@endif
                </div>
                <div class="field {{ $err('jk') ? 'has-error' : '' }}">
                    <label for="jk">Jenis kelamin <span class="req">*</span></label>
                    <select class="form-control" id="jk" name="jk" autocomplete="off">
                        @php $jk = $v('jk', $sw['jk'] ?? ''); @endphp
                        <option value="">Pilih…</option>
                        <option value="L" {{ $jk === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ $jk === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @if ($err('jk'))<div class="field-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $err('jk') }}</div>@endif
                </div>
                <div class="field {{ $err('tempat_lahir') ? 'has-error' : '' }}">
                    <label for="tl">Tempat lahir <span class="req">*</span></label>
                    <input class="form-control" id="tl" name="tempat_lahir" autocomplete="off" placeholder="Mis. Bogor…" value="{{ $v('tempat_lahir', $sw['tempat_lahir'] ?? '') }}">
                    @if ($err('tempat_lahir'))<div class="field-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $err('tempat_lahir') }}</div>@endif
                </div>
                <div class="field {{ $err('tgl_lahir') ? 'has-error' : '' }}">
                    <label for="tgl">Tanggal lahir <span class="req">*</span></label>
                    <input class="form-control" type="date" id="tgl" name="tgl_lahir" autocomplete="off" max="{{ now()->subDay()->toDateString() }}" value="{{ $v('tgl_lahir', $sw['tgl_lahir'] ?? '') }}">
                    @if ($err('tgl_lahir'))<div class="field-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $err('tgl_lahir') }}</div>@endif
                </div>
                <div class="field full">
                    <label for="asal">Asal sekolah</label>
                    <input class="form-control" id="asal" name="asal_sekolah" autocomplete="off" value="{{ $v('asal_sekolah', $sw['asal_sekolah'] ?? '') }}" placeholder="TK / PAUD / SD / MI asal, mis. SDN 01 Cibinong…">
                </div>
                <div class="field full {{ $err('alamat') ? 'has-error' : '' }}">
                    <label for="almt">Alamat lengkap <span class="req">*</span></label>
                    <textarea class="form-control" id="almt" name="alamat" autocomplete="street-address">{{ $v('alamat', $sw['alamat'] ?? '') }}</textarea>
                    @if ($err('alamat'))<div class="field-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $err('alamat') }}</div>@endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="form-section">3 · Data orang tua / wali</div>
            <div class="form-grid">
                <div class="field {{ $err('ayah') ? 'has-error' : '' }}">
                    <label for="ayah">Nama ayah <span class="req">*</span></label>
                    <input class="form-control" id="ayah" name="ayah" autocomplete="off" value="{{ $v('ayah', $ot['ayah'] ?? '') }}">
                    @if ($err('ayah'))<div class="field-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $err('ayah') }}</div>@endif
                </div>
                <div class="field {{ $err('ibu') ? 'has-error' : '' }}">
                    <label for="ibu">Nama ibu <span class="req">*</span></label>
                    <input class="form-control" id="ibu" name="ibu" autocomplete="off" value="{{ $v('ibu', $ot['ibu'] ?? '') }}">
                    @if ($err('ibu'))<div class="field-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $err('ibu') }}</div>@endif
                </div>
                <div class="field">
                    <label for="kerja">Pekerjaan</label>
                    <input class="form-control" id="kerja" name="pekerjaan" autocomplete="organization-title" value="{{ $v('pekerjaan', $ot['pekerjaan'] ?? '') }}">
                </div>
                <div class="field {{ $err('hp') ? 'has-error' : '' }}">
                    <label for="hp">No. WhatsApp <span class="req">*</span></label>
                    <input class="form-control" type="tel" id="hp" name="hp" inputmode="tel" autocomplete="tel" placeholder="0812xxxxxxxx…" value="{{ $v('hp', $ot['hp'] ?? ($authUser->phone ?? '')) }}">
                    @if ($err('hp'))<div class="field-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $err('hp') }}</div>@endif
                    <div class="hint wa-note"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i><span><b>Catatan:</b> nomor ini harus nomor <b>WhatsApp</b> yang aktif. Panitia menghubungi orang tua/wali lewat WhatsApp.</span></div>
                </div>
                <div class="field full">
                    <label for="aot">Alamat orang tua</label>
                    <textarea class="form-control" id="aot" name="alamat_ortu" autocomplete="off">{{ $v('alamat_ortu', $ot['alamat'] ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="field {{ $err('setuju') ? 'has-error' : '' }}" style="margin:0">
                <label class="check" for="setuju" style="font-weight:400">
                    <input type="checkbox" id="setuju" name="setuju" value="1" {{ old('setuju', $p ? ($p['terkirim'] ? 1 : 0) : 0) ? 'checked' : '' }}>
                    <span>Saya menyatakan data di atas benar dan setuju data calon siswa digunakan untuk proses {{ config('sekolah.spmb') }} sesuai <a href="#">Kebijakan Privasi</a>.</span>
                </label>
                @if ($err('setuju'))<div class="field-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $err('setuju') }}</div>@endif
            </div>
            <div class="form-actions">
                <a href="/pendaftar/dashboard" class="btn btn-white">Kembali</a>
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    @if (! ($p['terkirim'] ?? false))
                        <button type="submit" name="aksi" value="draft" class="btn btn-outline-light">Simpan Draft</button>
                        <button type="submit" name="aksi" value="kirim" class="btn btn-primary">Kirim Formulir <i class="fa-solid fa-paper-plane" aria-hidden="true"></i></button>
                    @else
                        <button type="submit" name="aksi" value="kirim" class="btn btn-primary">Simpan Perubahan <i class="fa-solid fa-check" aria-hidden="true"></i></button>
                    @endif
                </div>
            </div>
        </div>
    </fieldset>
</form>
@endsection

@push('scripts')
<script>
    (function () {
        var form = document.querySelector('form[action="/pendaftar/form-data"]');
        if (!form) return;

        // Rincian biaya mengikuti jenjang yang dipilih
        var pilih = document.getElementById('jenjang');
        function tampilBiaya() {
            document.querySelectorAll('.biaya-rinci').forEach(function (d) { d.hidden = !pilih || d.dataset.jenjang !== pilih.value; });
        }
        if (pilih) { pilih.addEventListener('change', tampilBiaya); tampilBiaya(); }

        // Fokus ke kolom pertama yang salah setelah submit gagal
        var salah = form.querySelector('.has-error input, .has-error select, .has-error textarea');
        if (salah) salah.focus();

        // Peringatan bila meninggalkan halaman dengan isian belum tersimpan
        var kotor = false;
        form.addEventListener('input', function () { kotor = true; });
        form.addEventListener('submit', function () { kotor = false; });
        window.addEventListener('beforeunload', function (e) {
            if (!kotor) return;
            e.preventDefault();
            e.returnValue = '';
        });
    })();
</script>
@endpush
