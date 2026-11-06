@extends('layouts.pendaftar')

@section('title', 'Pengumuman')

@section('content')
@php $s = $pendaftaran['hasil'] ?? null; @endphp

<div class="page-title">
    <h1>Pengumuman Hasil Seleksi</h1>
    <p>Hasil seleksi {{ $gelombang['nama'] }} · TA {{ $gelombang['tahun_ajaran'] }}</p>
</div>

<div class="pd-grid">
    <div>
        @if ($s === 'diterima')
            <div class="result win">
                <div class="ico"><i class="fa-solid fa-check" aria-hidden="true"></i></div>
                <h2>Alhamdulillah, {{ $pendaftaran['nama'] }} diterima</h2>
                <p>Selamat bergabung di keluarga besar SMP Islam Sirojul Athfal.</p>
            </div>
            <div class="card">
                <h3>Langkah daftar ulang</h3>
                <p class="sub">Lakukan pada {{ $gelombang['daftar_ulang'] }}.</p>
                <ul class="todo">
                    <li><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Datang ke SMP Islam Sirojul Athfal, Jl. KH. Muh Sanusi Kp. Lio Masjid RT 04/03 Desa Kedung Waringin Kecamatan Bojong Gede Kabupaten Bogor Provinsi Jawa Barat</li>
                    <li><i class="fa-solid fa-print" aria-hidden="true"></i> Bawa cetakan bukti ini dan berkas asli untuk dicocokkan</li>
                    <li><i class="fa-solid fa-wallet" aria-hidden="true"></i> Selesaikan administrasi daftar ulang langsung di sekolah</li>
                </ul>
                <div class="form-actions" style="border:0;padding-top:12px;margin-top:8px">
                    <span></span>
                    <a class="btn btn-primary" href="/pendaftar/bukti-diterima"><i class="fa-solid fa-print" aria-hidden="true"></i> Cetak Bukti Diterima</a>
                </div>
            </div>
        @elseif ($s === 'ditolak')
            <div class="result lose">
                <div class="ico"><i class="fa-solid fa-heart-crack" aria-hidden="true"></i></div>
                <h2>Mohon maaf, belum diterima</h2>
                <p>Terima kasih telah mendaftar. Kuota pada gelombang ini sudah terpenuhi. Anda dapat mendaftar kembali pada gelombang berikutnya.</p>
            </div>
            @if ($daftarLagi)
                <div class="card">
                    <div class="card-head" style="margin:0">
                        <div>
                            <h3>Daftar lagi di {{ $daftarLagi['nama'] }}</h3>
                            <p class="sub" style="margin:0">Dibuka sampai {{ $daftarLagi['tutup'] }}. Gunakan akun yang sama; data pendaftaran sebelumnya tetap tersimpan.</p>
                        </div>
                        <a href="/pendaftar/form-data" class="btn btn-primary">Daftar Lagi <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </div>
            @endif
        @elseif ($terbit && $pendaftaran)
            <div class="result wait">
                <div class="ico"><i class="fa-regular fa-folder-open" aria-hidden="true"></i></div>
                <h2>Pendaftaran Anda belum mendapat hasil</h2>
                <p>
                    @if ($terlewat)
                        Pengumuman {{ $gelombang['nama'] }} sudah terbit, tetapi pendaftaran Anda belum lengkap saat gelombang ditutup sehingga tidak ikut diseleksi.
                        Pendaftaran Anda tersimpan dan dapat dilanjutkan di gelombang berikutnya.
                    @else
                        Pengumuman {{ $gelombang['nama'] }} sudah terbit, tetapi pendaftaran Anda belum selesai diproses panitia pada gelombang ini.
                        Silakan hubungi panitia untuk informasi lebih lanjut.
                    @endif
                </p>
                @if ($lanjutKe)
                    <form action="/pendaftar/lanjutkan-gelombang" method="POST" data-once style="margin-top:14px">
                        @csrf
                        <button type="submit" class="btn btn-primary">Lanjutkan di {{ $lanjutKe['nama'] }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
                    </form>
                @endif
            </div>
        @else
            <div class="result wait">
                <div class="ico"><i class="fa-regular fa-hourglass-half" aria-hidden="true"></i></div>
                <h2>{{ $terbit ? 'Tidak ada hasil untuk Anda' : 'Hasil belum diumumkan' }}</h2>
                <p>
                    @if (! $pendaftaran) Anda belum mengisi formulir pendaftaran.
                    @else Pengumuman dijadwalkan pada <b>{{ $gelombang['pengumuman'] }}</b>. Hasil hanya muncul setelah panitia mempublikasikannya.
                    @endif
                </p>
                @if (! $terbit)
                    <div class="countdown" aria-label="Hitung mundur pengumuman">
                        <div><b id="cd-d">0</b><span>Hari</span></div>
                        <div><b id="cd-h">0</b><span>Jam</span></div>
                        <div><b id="cd-m">0</b><span>Menit</span></div>
                    </div>
                @endif
                @if (! $pendaftaran)
                    <p style="margin-top:18px"><a href="/pendaftar/form-data" class="btn btn-primary">Isi Formulir</a></p>
                @endif
            </div>
        @endif
    </div>

    <aside class="pd-side">
        <div class="card">
            <h3>Jadwal</h3>
            <p class="sub">{{ $gelombang['nama'] }} · TA {{ $gelombang['tahun_ajaran'] }}</p>
            <div class="info-list">
                <div><span>Pengumuman</span><b>{{ $gelombang['pengumuman'] }}</b></div>
                <div><span>Daftar ulang</span><b>{{ $gelombang['daftar_ulang'] }}</b></div>
            </div>
        </div>
        <div class="card">
            <h3>Butuh bantuan?</h3>
            <p class="sub">Hubungi panitia {{ config('sekolah.spmb') }} pada hari kerja, 08.30–15.30 WIB.</p>
            <a href="#" class="btn btn-outline-light w-100"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Chat Panitia</a>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var target = new Date(@json($gelombang['pengumuman_iso'])).getTime();
        var d = document.getElementById('cd-d');
        if (!d) return;
        function tick() {
            var diff = Math.max(0, target - Date.now());
            d.textContent = Math.floor(diff / 864e5);
            document.getElementById('cd-h').textContent = Math.floor(diff % 864e5 / 36e5);
            document.getElementById('cd-m').textContent = Math.floor(diff % 36e5 / 6e4);
        }
        tick(); setInterval(tick, 30000);
    })();
</script>
@endpush
