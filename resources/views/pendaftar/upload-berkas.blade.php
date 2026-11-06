@extends('layouts.pendaftar')

@section('title', 'Upload Berkas')

@section('content')
@php
    $berkas = $pendaftaran['berkas'] ?? [];
    $berkasStatus = [
        'kosong' => ['Belum diunggah', 'st-gray'],
        'menunggu' => ['Menunggu diperiksa', 'st-gold'],
        'valid' => ['Valid', 'st-green'],
        'ditolak' => ['Perlu diunggah ulang', 'st-orange'],
    ];
    $terkirim = $pendaftaran['terkirim'] ?? false;
    $bisaUbah = $terkirim && in_array($pendaftaran['status'], ['draft', 'perbaikan'], true)
        && ! ($terlewat ?? false) && ! ($terkunciPengumuman ?? false);
@endphp

<div class="page-title">
    <h1>Upload Berkas</h1>
    <p>Unggah berkas persyaratan dalam bentuk <b>PDF</b> atau foto (JPG/PNG), maksimal 2 MB per berkas. Berkas yang diminta: {{ collect($jenisBerkas)->pluck('nama')->implode(', ') }}.</p>
</div>

@include('partials.pendaftar-terlewat')

@if ($errors->has('berkas'))
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span>{{ $errors->first('berkas') }}</span></div>
@endif

@if (! $terkirim)
    <div class="card empty">
        <div class="ico"><i class="fa-solid fa-folder-open" aria-hidden="true"></i></div>
        <h3>Kirim formulir lebih dulu</h3>
        <p>Berkas bisa diunggah setelah formulir pendaftaran dikirim dan kode pendaftaran terbit.</p>
        <a href="/pendaftar/form-data" class="btn btn-primary btn-lg">Buka Formulir <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </div>
@else
    <div class="pd-grid">
        <div>
            @if ($pendaftaran['status'] === 'perbaikan')
                <div class="note-box"><b><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> Catatan dari panitia</b>{{ $pendaftaran['catatan'] }}</div>
            @elseif (! $bisaUbah && ! ($terlewat ?? false) && ! ($terkunciPengumuman ?? false))
                <div class="alert alert-success"><i class="fa-solid fa-lock" aria-hidden="true"></i><span>Berkas terkunci karena pendaftaran sedang/sudah diproses panitia.</span></div>
            @endif

            <div class="up-list">
                @foreach ($jenisBerkas as $j)
                    @php
                        $b = $berkas[$j['key']] ?? ['id' => null, 'status' => 'kosong', 'file' => null, 'catatan' => null];
                        $st = $berkasStatus[$b['status']];
                        $canUpload = $bisaUbah && $b['status'] !== 'valid';
                    @endphp
                    <div class="up-item {{ $b['status'] === 'ditolak' ? 'bad' : ($b['status'] === 'valid' ? 'ok' : '') }}">
                        <div class="ico"><i class="fa-solid {{ $j['icon'] }}" aria-hidden="true"></i></div>
                        <div>
                            <h4>{{ $j['nama'] }} <em>{{ $j['wajib'] ? 'Wajib' : 'Opsional' }}</em></h4>
                            <div class="file">
                                @if ($b['id'])
                                    <a href="/berkas/{{ $b['id'] }}" target="_blank" rel="noopener" style="color:var(--primary);font-weight:600"><i class="fa-regular fa-eye" aria-hidden="true"></i> {{ $b['file'] }}</a>
                                @else
                                    Belum ada berkas
                                @endif
                            </div>
                            @if ($b['catatan'])<div class="why"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $b['catatan'] }}</div>@endif
                        </div>
                        <div class="up-actions">
                            <span class="st {{ $st[1] }}">{{ $st[0] }}</span>
                            @if ($canUpload)
                                <form action="/pendaftar/upload-berkas/{{ $j['id'] }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="file" name="berkas" id="in-{{ $j['key'] }}" data-autosubmit accept=".pdf,.jpg,.jpeg,.png">
                                    <label for="in-{{ $j['key'] }}" class="btn btn-sm {{ $b['status'] === 'ditolak' ? 'btn-primary' : 'btn-outline-light' }}" style="cursor:pointer">
                                        <i class="fa-solid fa-upload" aria-hidden="true"></i> {{ $b['id'] ? 'Ganti' : 'Pilih PDF / foto' }}
                                    </label>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="form-actions" style="border:0">
                <a href="/pendaftar/dashboard" class="btn btn-white">Kembali</a>
                @if ($pendaftaran['status'] === 'perbaikan')
                    <form action="/pendaftar/kirim-perbaikan" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Kirim Perbaikan untuk Diperiksa</button>
                    </form>
                @endif
            </div>
        </div>

        <aside class="pd-side">
            <div class="card">
                <h3>Tips unggah dari HP</h3>
                <p class="sub">Agar berkas cepat disetujui.</p>
                <ul class="guide">
                    <li><i class="fa-solid fa-sun" aria-hidden="true"></i> Foto di tempat terang, tanpa bayangan.</li>
                    <li><i class="fa-solid fa-expand" aria-hidden="true"></i> Seluruh halaman dokumen harus terlihat, tidak terpotong.</li>
                    <li><i class="fa-solid fa-eye" aria-hidden="true"></i> Pastikan tulisan jelas dan tidak buram.</li>
                    <li><i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Beberapa halaman? Gabungkan jadi satu PDF.</li>
                </ul>
            </div>
            <div class="card">
                <h3>Kode pendaftaran</h3>
                <p class="sub">Simpan untuk cek status.</p>
                <div class="kode" style="text-align:center;padding:12px;border:1px dashed var(--accent);border-radius:6px;background:var(--paper)">
                    <b style="font-family:var(--font-display);font-size:1.3rem;color:var(--ink);letter-spacing:.05em">{{ $pendaftaran['kode'] }}</b>
                </div>
                <a href="/pendaftar/bukti-pendaftaran" class="btn btn-outline-light btn-sm w-100" style="margin-top:12px">
                    <i class="fa-solid fa-file-arrow-down" aria-hidden="true"></i> Unduh Bukti Pendaftaran
                </a>
            </div>
        </aside>
    </div>
@endif
@endsection
