@extends('layouts.pendaftar')

@section('title', 'Ringkasan')

@section('content')
@php
    $s = $pendaftaran['status'] ?? null;
    // index langkah aktif: 0 Akun, 1 Form, 2 Berkas, 3 Verifikasi, 4 Pengumuman
    $now = match (true) {
        $s === null, ! ($pendaftaran['terkirim'] ?? false) => 1,
        $s === 'draft' => 2,
        in_array($s, ['menunggu', 'perbaikan'], true) => 3,
        $s === 'terverifikasi' => 4,
        default => 5,
    };
    $terkirim = $pendaftaran['terkirim'] ?? false;
    $kata = preg_split('/\s+/', trim($authUser->name));
    $panggil = count($kata) > 1 ? $kata[1] : $kata[0];
    $steps = ['Akun', 'Form Data', 'Berkas', 'Verifikasi', 'Pengumuman'];
    $berkas = $pendaftaran['berkas'] ?? [];
@endphp

<div class="page-title">
    <h1>Assalamu'alaikum, {{ $panggil }}</h1>
    <p>Pantau perkembangan pendaftaran {{ config('sekolah.spmb') }} putra-putri Anda di sini.</p>
</div>

@include('partials.pendaftar-terlewat')

@if ($daftarLagi)
    <div class="card" style="margin-bottom:20px;border-color:var(--accent)">
        <div class="card-head" style="margin:0">
            <div>
                <h3>Daftar lagi di {{ $daftarLagi['nama'] }}</h3>
                <p class="sub" style="margin:0">Pendaftaran {{ $daftarLagi['nama'] }} TA {{ $daftarLagi['tahun_ajaran'] }} sedang dibuka sampai {{ $daftarLagi['tutup'] }}. Anda dapat mengisi formulir baru memakai akun yang sama.</p>
            </div>
            <a href="/pendaftar/form-data" class="btn btn-accent">Daftar Lagi <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
    </div>
@endif

@if (! $pendaftaran)
    <div class="card empty">
        <div class="ico"><i class="fa-solid fa-file-pen" aria-hidden="true"></i></div>
        <h3>Anda belum mengisi formulir</h3>
        <p>Mulai dengan mengisi data calon siswa. Pendaftaran {{ $gelombang['nama'] }} TA {{ $gelombang['tahun_ajaran'] }} dibuka sampai {{ $gelombang['tutup'] }}.</p>
        <a href="/pendaftar/form-data" class="btn btn-primary btn-lg">Mulai Isi Formulir <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </div>
@else
    <section class="hero-status">
        <div>
            <div class="eyebrow">Status pendaftaran</div>
            <h2>{{ $pendaftaran['nama'] ?: 'Calon siswa' }}</h2>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                <span class="st {{ $terkirim ? $status[$s][1] : 'st-gray' }}" style="background:#fff">{{ $terkirim ? $status[$s][0] : 'Draft formulir (belum dikirim)' }}</span>
                <span>{{ $pendaftaran['gelombang'] }}</span>
            </div>
        </div>
        <div>
            <div class="kode"><small>KODE PENDAFTARAN</small><b>{{ $pendaftaran['kode'] ?? 'Terbit setelah dikirim' }}</b></div>
            @if ($terkirim)
                <a href="/pendaftar/bukti-pendaftaran" class="btn btn-accent btn-sm w-100" style="margin-top:10px">
                    <i class="fa-solid fa-file-arrow-down" aria-hidden="true"></i> Unduh Bukti Pendaftaran
                </a>
            @else
                <a href="/pendaftar/form-data" class="btn btn-accent btn-sm w-100" style="margin-top:10px">Lanjutkan Formulir <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            @endif
        </div>
    </section>

    <div class="pd-grid">
        <div>
            <div class="card">
                <h3>Tahapan pendaftaran</h3>
                <p class="sub">Anda berada di tahap <b>{{ $steps[min($now, 4)] }}</b>.</p>
                <div class="stepper">
                    @foreach ($steps as $i => $label)
                        <div class="step {{ $i < $now ? 'done' : ($i === $now ? 'now' : '') }}">
                            <span class="dot">@if ($i < $now)<i class="fa-solid fa-check" aria-hidden="true"></i>@else{{ $i + 1 }}@endif</span>
                            {{ $label }}
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card">
                <h3>Yang perlu Anda lakukan</h3>
                <p class="sub">Selesaikan semua langkah agar berkas dapat diperiksa panitia.</p>

                @if ($s === 'perbaikan')
                    <div class="note-box"><b><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> Catatan dari panitia</b>{{ $pendaftaran['catatan'] }}</div>
                @endif

                <ul class="todo">
                    <li class="ok"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Buat akun pendaftaran</li>
                    <li class="{{ $now > 1 ? 'ok' : '' }}"><i class="fa-{{ $now > 1 ? 'solid fa-circle-check' : 'regular fa-circle' }}" aria-hidden="true"></i> Isi formulir data calon siswa dan orang tua
                        @if ($now <= 1)<a href="/pendaftar/form-data">Isi sekarang →</a>@endif</li>
                    @foreach ($jenisBerkas->where('wajib', true)->pluck('nama', 'kode') as $k => $nm)
                        @php $b = $berkas[$k]['status'] ?? 'kosong'; @endphp
                        <li class="{{ $b === 'valid' ? 'ok' : ($b === 'ditolak' ? 'warn' : '') }}">
                            <i class="fa-{{ $b === 'valid' ? 'solid fa-circle-check' : ($b === 'ditolak' ? 'solid fa-circle-exclamation' : 'regular fa-circle') }}" aria-hidden="true"></i>
                            Unggah {{ $nm }}
                            @if ($b === 'ditolak')<a href="/pendaftar/upload-berkas">Unggah ulang →</a>
                            @elseif ($b === 'menunggu')<span class="st st-gold" style="margin-left:auto">Diperiksa</span>
                            @elseif ($b === 'kosong')<a href="/pendaftar/upload-berkas">Unggah →</a>@endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <aside class="pd-side">
            <div class="card">
                <h3>Jadwal penting</h3>
                <p class="sub">{{ $gelombang['nama'] }} · TA {{ $gelombang['tahun_ajaran'] }}</p>
                <div class="info-list">
                    <div><span>Pendaftaran ditutup</span><b>{{ $gelombang['tutup'] }}</b></div>
                    <div><span>Pengumuman hasil</span><b>{{ $gelombang['pengumuman'] }}</b></div>
                    <div><span>Daftar ulang</span><b>{{ $gelombang['daftar_ulang'] }}</b></div>
                </div>
            </div>
            <div class="card">
                <h3>Riwayat status</h3>
                <p class="sub">Dicatat otomatis oleh sistem.</p>
                <ul class="timeline">
                    @forelse (array_reverse($pendaftaran['riwayat']) as $r)
                        <li><time>{{ $r[0] }}</time><b>{{ $r[1] }}</b><br><span style="color:var(--gray-500)">→ {{ $r[2] }}</span></li>
                    @empty
                        <li><span style="color:var(--gray-500)">Belum ada riwayat. Kirim formulir untuk memulai.</span></li>
                    @endforelse
                </ul>
            </div>
        </aside>
    </div>
@endif

@if (! $pendaftaran)
    <div class="card" style="margin-top:20px">
        <h3>Jadwal penting</h3>
        <p class="sub">{{ $gelombang['nama'] }} · TA {{ $gelombang['tahun_ajaran'] }}</p>
        <div class="info-list">
            <div><span>Pendaftaran dibuka</span><b>{{ $gelombang['buka'] }}</b></div>
            <div><span>Pendaftaran ditutup</span><b>{{ $gelombang['tutup'] }}</b></div>
            <div><span>Pengumuman hasil</span><b>{{ $gelombang['pengumuman'] }}</b></div>
            <div><span>Daftar ulang</span><b>{{ $gelombang['daftar_ulang'] }}</b></div>
        </div>
    </div>
@endif
@endsection
