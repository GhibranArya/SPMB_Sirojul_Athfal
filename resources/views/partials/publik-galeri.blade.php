{{-- Galeri kegiatan: kisi seragam 4:3 (dipotong di kisi, utuh di penampil), penampil foto <dialog>.
     Mode ringkas ($ringkas, dipakai di beranda): beberapa foto + tombol ke /galeri.
     Mode penuh (halaman /galeri): semua foto + filter MI/SMP lewat tautan (?unit=mi). Butuh $galeri, $unitGaleri (null|MI|SMP), $galeriTotal. --}}
@php
    $ringkas = $ringkas ?? false;
    $unitGaleri = $ringkas ? null : $unitGaleri;
    $hitung = ['semua' => $galeri->count()];
    foreach (['MI', 'SMP'] as $u) { // foto tanpa unit tampil di semua filter
        $hitung[$u] = $galeri->filter(fn ($g) => $g->unit === null || $g->unit === $u)->count();
    }
    $tampil = fn ($g) => ! $unitGaleri || $g->unit === null || $g->unit === $unitGaleri;
@endphp
<section class="section gl{{ $ringkas ? '' : ' gl-penuh' }}" id="galeri" aria-labelledby="gl-judul">
    <div class="container">
        @if ($ringkas)
            <header class="gl-head">
                <div>
                    <div class="section-badge-sm">GALERI</div>
                    <h2 class="section-title" id="gl-judul">Galeri <span>Kegiatan</span></h2>
                    <p class="gl-desc">Momen belajar, ibadah, dan kegiatan siswa di {{ config('sekolah.unit.MI') }} dan {{ config('sekolah.unit.SMP') }}.</p>
                </div>
                <a href="/galeri" class="btn btn-outline-light">Lihat Semua Galeri <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </header>
        @else
            <h2 class="gl-sr" id="gl-judul">Daftar foto</h2>
            <div class="gl-bar">
                <nav class="gl-filter" aria-label="Filter galeri per unit">
                    <a href="/galeri" data-unit="" @if (! $unitGaleri) aria-current="true" @endif>Semua <span>{{ $hitung['semua'] }}</span></a>
                    <a href="/galeri?unit=mi" data-unit="MI" @if ($unitGaleri === 'MI') aria-current="true" @endif>MI <span>{{ $hitung['MI'] }}</span></a>
                    <a href="/galeri?unit=smp" data-unit="SMP" @if ($unitGaleri === 'SMP') aria-current="true" @endif>SMP <span>{{ $hitung['SMP'] }}</span></a>
                </nav>
                <p class="gl-count" aria-live="polite"><b data-gl-jumlah>{{ $galeri->filter($tampil)->count() }}</b>&nbsp;foto</p>
            </div>
        @endif

        <ul class="gl-grid" role="list">
            @foreach ($galeri as $g)
                @php [$lebar, $tinggi] = $g->ukuran(); @endphp
                <li class="gl-item" data-unit="{{ $g->unit }}" @unless ($tampil($g)) hidden @endunless>
                    <button type="button" class="gl-open" aria-haspopup="dialog" aria-label="Perbesar foto: {{ $g->judul }}"
                            data-src="{{ asset($g->gambar) }}" data-judul="{{ $g->judul }}" data-ket="{{ $g->keterangan_gambar }}" data-unit="{{ $g->unit }}">
                        <img src="{{ asset($g->gambar) }}" width="{{ $lebar }}" height="{{ $tinggi }}" alt="{{ $g->keterangan_gambar ?: $g->judul }}" loading="lazy" decoding="async">
                    </button>
                    <p class="gl-cap">
                        @if ($g->unit)<span class="gl-tag">{{ $g->unit }}</span>@endif
                        <span class="gl-judul">{{ $g->judul }}</span>
                    </p>
                </li>
            @endforeach
        </ul>

        @if ($ringkas)
            <p class="gl-more"><a href="/galeri" class="btn btn-primary btn-lg">Lihat Semua {{ $galeriTotal }} Foto <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></p>
        @else
            <p class="gl-empty" hidden>Belum ada foto untuk unit ini.</p>
        @endif
    </div>

    <dialog class="gl-lb" id="gl-lb" aria-label="Penampil foto galeri">
        <div class="gl-lb-bar">
            <p class="gl-lb-no" aria-live="polite"></p>
            <button type="button" class="gl-lb-btn" data-gl="tutup" aria-label="Tutup penampil foto"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>
        <div class="gl-lb-stage">
            <button type="button" class="gl-lb-btn gl-lb-prev" data-gl="sebelumnya" aria-label="Foto sebelumnya"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
            <figure class="gl-lb-fig">
                <img src="" alt="" class="gl-lb-img">
                <figcaption><b class="gl-lb-judul"></b><span class="gl-lb-ket"></span></figcaption>
            </figure>
            <button type="button" class="gl-lb-btn gl-lb-next" data-gl="berikutnya" aria-label="Foto berikutnya"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
        </div>
    </dialog>
</section>
<script src="{{ asset('js/galeri.js') }}" defer></script>
