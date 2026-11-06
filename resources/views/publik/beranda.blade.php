<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#FFFFFF">
    <title>{{ config('sekolah.nama') }} · MI &amp; SMP Kabupaten Bogor</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/favicon.png') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/hero.css') }}">
    <link rel="stylesheet" href="{{ asset('css/galeri.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tegas.css') }}">
</head>
<body>

    @include('partials.publik-nav')

    <main id="main" tabindex="-1">

    <!-- HERO SECTION: foto kegiatan layar penuh sebagai latar -->
    @php
        $tgl = fn ($d) => \App\Models\Gelombang::tglId($d);
        $status = $gelombang?->statusJadwal();
    @endphp
    <section class="hx" id="beranda">
        <div class="hx-bg" @if ($hero->isNotEmpty()) id="heroCarousel" role="region" aria-roledescription="carousel" aria-label="Foto kegiatan sekolah" @endif>
            @if ($hero->isNotEmpty())
                @foreach ($hero as $i => $g)
                    <figure class="hc-slide{{ $i === 0 ? ' is-active' : '' }}" role="group" aria-roledescription="slide" aria-label="Foto {{ $i + 1 }} dari {{ $hero->count() }}" data-caption="{{ $g->judul }}" @if ($i !== 0) aria-hidden="true" @endif>
                        <img src="{{ asset($g->gambar) }}" alt="{{ $g->keterangan_gambar ?? $g->judul }}" style="object-position: {{ $g->posisi_gambar }};" @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif>
                    </figure>
                @endforeach
            @endif
            <div class="hx-shade" aria-hidden="true"></div>

            @if ($hero->isNotEmpty())
                <div class="hx-dock">
                    <div class="hx-dock-row">
                        <span class="hx-caption-wrap"><i class="fa-solid fa-camera" aria-hidden="true"></i> <span class="hc-caption">{{ $hero->first()->judul }}</span></span>
                        <div class="hx-ctrl">
                            <button type="button" class="hc-arrow hc-prev" aria-label="Foto sebelumnya"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
                            <button type="button" class="hc-toggle" aria-label="Jeda geser otomatis"><i class="fa-solid fa-pause" aria-hidden="true"></i></button>
                            <button type="button" class="hc-arrow hc-next" aria-label="Foto berikutnya"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
                        </div>
                    </div>
                    <div class="hc-thumbs">
                        @foreach ($hero as $i => $g)
                            <button type="button" class="hc-dot{{ $i === 0 ? ' is-active' : '' }}" aria-label="Tampilkan foto {{ $i + 1 }}: {{ $g->judul }}"@if ($i === 0) aria-current="true" @endif>
                                <img src="{{ asset($g->gambar) }}" alt="" style="object-position: {{ $g->posisi_gambar }};" loading="lazy" draggable="false">
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="container hx-inner">
            <div class="hx-copy">
                <div class="hx-badge">
                    @if (! $gelombang)
                        PENERIMAAN SISWA BARU
                    @elseif ($status === 'belum')
                        {{ config('sekolah.spmb') }} {{ $gelombang->tahun_ajaran }} · {{ mb_strtoupper($gelombang->nama) }} DIBUKA {{ mb_strtoupper($tgl($gelombang->tgl_buka)) }}
                    @elseif ($status === 'dibuka')
                        {{ config('sekolah.spmb') }} {{ $gelombang->tahun_ajaran }} · {{ mb_strtoupper($gelombang->nama) }} SEDANG DIBUKA
                    @else
                        {{ config('sekolah.spmb') }} {{ $gelombang->tahun_ajaran }} · {{ mb_strtoupper($gelombang->nama) }} TELAH DITUTUP
                    @endif
                </div>

                <h1 class="hx-title">
                    Murid <span class="hl">Berakhlakul Karimah</span>, Berprestasi dan Mandiri
                </h1>
                <p class="hx-desc">
                    SMP Islam Sirojul Athfal berada di lingkungan yang kondusif dan nyaman untuk belajar serta mengembangkan diri.
                </p>

                @if ($gelombang)
                    <dl class="hx-jadwal" aria-label="Jadwal {{ config('sekolah.spmb') }} {{ $gelombang->nama }} {{ $gelombang->tahun_ajaran }}">
                        <div>
                            <dt><i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Pendaftaran</dt>
                            <dd>{{ $tgl($gelombang->tgl_buka) }} – {{ $tgl($gelombang->tgl_tutup) }}</dd>
                        </div>
                        <div>
                            <dt><i class="fa-solid fa-bullhorn" aria-hidden="true"></i> Pengumuman</dt>
                            <dd>{{ $tgl($gelombang->tgl_pengumuman) }}</dd>
                        </div>
                        @if ($gelombang->daftar_ulang_mulai)
                            <div>
                                <dt><i class="fa-solid fa-file-signature" aria-hidden="true"></i> Daftar ulang</dt>
                                <dd>{{ $tgl($gelombang->daftar_ulang_mulai) }}@if ($gelombang->daftar_ulang_selesai) – {{ $tgl($gelombang->daftar_ulang_selesai) }}@endif</dd>
                            </div>
                        @endif
                    </dl>
                @endif

                <div class="hx-actions">
                    <a href="/login" class="btn btn-accent btn-lg">Daftar Sekarang <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    <!-- <a href="#ekstrakurikuler" class="btn btn-ghost-light btn-lg"><i class="fa-solid fa-book-open" aria-hidden="true"></i> Pelajari Program Kami</a> -->
                </div>
            </div>
        </div>
    </section>

    <!-- MEDIA SOSIAL: kartu yang menumpuk di tepi bawah hero. Tautan diisi admin lewat menu Profil & Kontak. -->
    <div class="container hx-stats-wrap">
        <div class="hx-stats hx-stats--sosial">
            {{-- Nama akun samaran (dummy) tampil selama tautan belum diisi di admin; setelah diisi, nama akun diambil dari tautannya. --}}
            @foreach ([
                ['instagram', 'Instagram', 'fa-brands fa-instagram', '@sirojulathfal'],
                ['tiktok', 'TikTok', 'fa-brands fa-tiktok', '@sirojulathfal'],
                ['youtube', 'YouTube', 'fa-brands fa-youtube', '@SirojulAthfal'],
            ] as [$kolom, $nama, $ikon, $samaran])
                @if (filled($profil->{$kolom}))
                    @php $akun = '@'.ltrim(basename(rtrim(parse_url($profil->{$kolom}, PHP_URL_PATH) ?? '', '/')), '@'); @endphp
                    <a href="{{ $profil->{$kolom} }}" class="hx-stat" target="_blank" rel="noopener">
                        <span class="hx-stat-icon"><i class="{{ $ikon }}"></i></span>
                        <div><strong>{{ $akun !== '@' ? $akun : $nama }}</strong><span>{{ $nama }}</span></div>
                    </a>
                @else
                    <div class="hx-stat">
                        <span class="hx-stat-icon"><i class="{{ $ikon }}"></i></span>
                        <div><strong>{{ $samaran }}</strong><span>{{ $nama }}</span></div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- DUA UNIT PENDIDIKAN -->
    <section class="section" id="unit" style="padding-bottom: 10px;">
        <div class="container">
            <div class="section-header text-center">
                <div class="section-badge-sm"><i class="fa-solid fa-school-flag" aria-hidden="true"></i> {{ mb_strtoupper(config('sekolah.nama_yayasan')) }}</div>
                <h2 class="section-title">Dua Unit <span>Pendidikan</span></h2>
                <p class="section-desc">Pendaftaran {{ config('sekolah.spmb') }} online tersedia untuk Madrasah Ibtidaiyah (MI) dan Sekolah Menengah Pertama (SMP).</p>
            </div>
            <div class="unit-panel">
                <div class="unit-panel-grid">
                    @foreach (config('struktur.unit') as $kode => $u)
                        <article class="unit-col">
                            <div class="unit-col-top">
                                <span class="unit-col-ico"><i class="fa-solid {{ $kode === 'MI' ? 'fa-book-quran' : 'fa-graduation-cap' }}" aria-hidden="true"></i></span>
                                <span class="unit-col-tag">{{ $kode === 'MI' ? 'Madrasah Ibtidaiyah' : 'Sekolah Menengah Pertama' }}</span>
                            </div>
                            <h3>{{ config('sekolah.unit.'.$kode) }}</h3>
                            <div class="unit-col-head">
                                <i class="fa-solid fa-user-tie" aria-hidden="true"></i>
                                <div>
                                    <small>{{ preg_replace('/\s+(MI|SMP)$/', '', $u['pimpinan']['jabatan']) }}</small>
                                    <b>{{ $u['pimpinan']['nama'] }}</b>
                                </div>
                            </div>
                            <div class="unit-col-act">
                                <a href="/login" class="btn btn-primary">Daftar {{ $kode }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                                <a href="/biaya-pendaftaran#{{ strtolower($kode) }}" class="unit-col-link"><i class="fa-solid fa-receipt" aria-hidden="true"></i> Gelombang &amp; Biaya</a>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="unit-panel-foot">
                    <span><i class="fa-solid fa-school-flag" aria-hidden="true"></i> Satu yayasan, dua unit pendidikan</span>
                    <a href="/struktur-organisasi" class="link-more">Lihat Struktur Organisasi <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
    </section>
    <!-- GELOMBANG PENDAFTARAN (ringkas; rincian biaya ada di halaman detail) -->
    @if ($daftarGelombang->isNotEmpty())
    <section class="section" id="gelombang" style="padding-top: 50px; padding-bottom: 20px;">
        <div class="container">
            <div class="section-header text-center">
                <div class="section-badge-sm"><i class="fa-solid fa-calendar-days" aria-hidden="true"></i> {{ config('sekolah.spmb') }} TAHUN AJARAN {{ $daftarGelombang->first()->tahun_ajaran }}</div>
                <h2 class="section-title">Gelombang <span>Pendaftaran</span></h2>
                <p class="section-desc">Pendaftaran dibuka dalam {{ $daftarGelombang->count() }} gelombang. Biaya tiap gelombang berbeda untuk MI dan SMP.</p>
            </div>
            <div class="gb-grid">
                @foreach ($daftarGelombang as $g)
                    @php
                        $st = $g->statusJadwal();
                        $b = $g->tgl_buka->copy()->locale('id');
                        $t = $g->tgl_tutup->copy()->locale('id');
                        $periode = $b->year === $t->year ? $b->translatedFormat('M').' – '.$t->translatedFormat('M Y') : $b->translatedFormat('M Y').' – '.$t->translatedFormat('M Y');
                    @endphp
                    <article class="gb-card {{ $g->is_aktif && $st === 'dibuka' ? 'is-open' : '' }}">
                        <span class="gb-st {{ $st }}">{{ ['belum' => 'AKAN DIBUKA', 'dibuka' => 'DIBUKA', 'ditutup' => 'DITUTUP'][$st] }}</span>
                        <h3>{{ $g->nama }}</h3>
                        <div class="gb-periode">{{ $periode }}</div>
                        <dl>
                            <div><dt>Pendaftaran</dt><dd>{{ $tgl($g->tgl_buka) }} – {{ $tgl($g->tgl_tutup) }}</dd></div>
                            <div><dt>Pengumuman</dt><dd>{{ $tgl($g->tgl_pengumuman) }}</dd></div>
                        </dl>
                        <a href="/biaya-pendaftaran#gelombang-{{ $g->id }}" class="gb-detail">Lihat detail &amp; biaya <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    <style>
        .gb-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; max-width: 900px; margin: 28px auto 0; }
        .gb-card { position: relative; background: #fff; border: 1px solid var(--gray-300); border-radius: var(--radius-lg); padding: 22px; }
        .gb-card.is-open { border-color: var(--primary); box-shadow: 0 0 0 2px var(--primary-pale); }
        .gb-card h3 { margin: 0 0 2px; font-size: 1.2rem; }
        .gb-periode { color: var(--primary); font-weight: 700; font-size: .95rem; margin-bottom: 12px; }
        .gb-card dl { margin: 0 0 14px; }
        .gb-card dl div { padding: 6px 0; border-top: 1px dashed var(--gray-300); }
        .gb-card dt { font-size: .72rem; letter-spacing: .05em; text-transform: uppercase; color: var(--gray-500); font-weight: 700; }
        .gb-card dd { margin: 0; font-size: .9rem; font-weight: 600; color: var(--gray-900); }
        .gb-detail { font-weight: 700; font-size: .88rem; color: var(--primary); }
        .gb-st { display: inline-block; margin-bottom: 10px; font-size: .68rem; font-weight: 700; letter-spacing: .05em; padding: 4px 10px; border-radius: 999px; }
        .gb-st.dibuka { background: #DDF5E7; color: #146B3A; } .gb-st.belum { background: #FFF1CC; color: #8A5A00; } .gb-st.ditutup { background: #ECEEF0; color: #5B6670; }
    </style>
    <style>
        /* Dua unit dalam satu panel: garis aksen hijau di atas, dua kolom dipisah garis tipis, strip bawah untuk tautan umum. */
        .unit-panel { max-width: 1000px; margin: 30px auto 0; background: #fff; border: 1px solid var(--gray-300); border-radius: var(--radius-lg); overflow: hidden; box-shadow: 0 20px 44px -30px rgba(5, 38, 23, .45); position: relative; }
        .unit-panel::before { content: ""; position: absolute; inset: 0 0 auto 0; height: 4px; background: linear-gradient(90deg, var(--primary-dark), var(--primary) 55%, var(--accent, #c9a227)); }
        .unit-panel-grid { display: grid; grid-template-columns: 1fr 1fr; }
        .unit-col { padding: 38px 36px 32px; display: flex; flex-direction: column; align-items: flex-start; }
        .unit-col + .unit-col { border-left: 1px solid var(--gray-300); }
        .unit-col-top { display: flex; align-items: center; gap: 12px; }
        .unit-col-ico { display: grid; place-items: center; width: 48px; height: 48px; border-radius: 14px; background: var(--primary-pale); color: var(--primary); font-size: 1.25rem; }
        .unit-col-tag { font-size: .72rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--gray-500); }
        .unit-col h3 { margin: 18px 0 16px; font-size: 1.45rem; line-height: 1.25; color: var(--primary-dark); }
        .unit-col-head { display: flex; align-items: center; gap: 12px; width: 100%; padding: 12px 14px; margin-bottom: 24px; background: var(--bg-cream); border-radius: 12px; }
        .unit-col-head > i { color: var(--primary); font-size: 1.05rem; }
        .unit-col-head small { display: block; font-size: .74rem; color: var(--gray-500); }
        .unit-col-head b { font-size: .95rem; color: var(--gray-900); }
        .unit-col-act { display: flex; flex-wrap: wrap; align-items: center; gap: 12px 20px; margin-top: auto; }
        .unit-col-link { font-size: .88rem; font-weight: 600; color: var(--primary); text-decoration: none; }
        .unit-col-link:hover { text-decoration: underline; }
        .unit-col-link i { margin-right: 4px; }
        .unit-panel-foot { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 8px 20px; padding: 16px 36px; background: var(--bg-cream); border-top: 1px solid var(--gray-300); font-size: .88rem; color: var(--gray-700); }
        .unit-panel-foot > span i { color: var(--primary); margin-right: 6px; }
        .unit-panel-foot .link-more { display: inline-flex; }
        @media (max-width: 720px) {
            .unit-panel-grid { grid-template-columns: 1fr; }
            .unit-col + .unit-col { border-left: 0; border-top: 1px solid var(--gray-300); }
            .unit-col { padding: 30px 22px 26px; }
            .unit-panel-foot { padding: 14px 22px; }
        }
    </style>

    <!-- PROFIL SEKOLAH -->
    <section class="section sambutan-section" id="profil">
        <div class="container">
            <div class="sambutan-card">
                <div class="sambutan-photo">
                    <div class="photo-placeholder" style="overflow: hidden; padding: 0;">
                        <img src="{{ asset('images/foto/halaman-pohon.jpg') }}" alt="Halaman dan gedung kelas SMP Islam Sirojul Athfal di bawah pohon rindang" style="width: 100%; height: 100%; object-fit: cover; object-position: 45% 50%;" loading="lazy">
                    </div>
                </div>
                <div class="sambutan-content">
                    <div class="section-badge-sm">
                        <i class="fa-solid fa-school" aria-hidden="true"></i> PROFIL SEKOLAH
                    </div>
                    <h3>Analisis <span>Letak Geografis</span></h3>
                    <p>
                        SMP Islam Sirojul Athfal didirikan pada tahun 1991 dan mendapatkan ijin operasionalnya yang diterbitkan oleh Dinas Pendidikan Kabupaten Bogor pada tanggal 10 Juli 2001 dengan nomor 1855/I02.5/PR/2001. Sekolah ini berdiri untuk memenuhi aspirasi masyarakat sekitar yang menginginkan adanya sekolah jenjang SMP di sekitar.
                    </p>
                    <a href="/profil" class="link-more">Profil Sekolah <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- EKSTRAKURIKULER -->
    @if (count($eskul))
    <section class="section unit-pendidikan" id="ekstrakurikuler">
        <div class="container">
            <div class="section-header">
                <div class="section-badge-sm">
                    EKSTRAKURIKULER
                </div>
                <h2 class="section-title">Ekstrakurikuler <span>SMP Islam Sirojul Athfal</span></h2>
                <p class="section-desc">Kegiatan ekstrakurikuler yang dilaksanakan di luar jam belajar intrakurikuler di bawah bimbingan dan pengawasan satuan pendidikan.</p>
            </div>

            <div class="eks-carousel" id="eksCarousel" role="region" aria-roledescription="carousel" aria-label="Ekstrakurikuler">
                <button type="button" class="eks-arrow eks-prev" aria-label="Ekstrakurikuler sebelumnya"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
                <div class="eks-track" tabindex="0">
                    @foreach ($eskul as $i => $e)
                        <article class="unit-card eks-slide" role="group" aria-roledescription="slide" aria-label="{{ $i + 1 }} dari {{ count($eskul) }}">
                            <div class="card-image{{ $e['foto'] ? '' : ' no-foto' }}">
                                @if ($e['waktu'])<div class="card-badge">{{ $e['waktu'] }}</div>@endif
                                @if ($e['foto'])
                                    <img src="{{ asset('images/foto/'.$e['foto']) }}" alt="Ekstrakurikuler {{ $e['nama'] }}" style="object-position: {{ $e['posisi'] }};" loading="lazy" draggable="false">
                                @else
                                    <i class="fa-solid {{ $e['ikon'] }}" aria-hidden="true"></i>
                                @endif
                            </div>
                            <div class="card-body">
                                <h4>{{ $e['nama'] }}</h4>
                                <p class="clamp-4">{{ $e['tujuan'][0] ?? '' }}</p>
                                <ul class="feature-list">
                                    @foreach (array_slice($e['target'], 0, 2) as $t)
                                        <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> <span class="clamp-3">{{ $t }}</span></li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="card-footer">
                                <a href="/unit-pendidikan#ekstrakurikuler" class="btn btn-outline-light w-100">@if ($e['pembimbing'])Pembimbing: {{ $e['pembimbing'] }}@else Selengkapnya @endif</a>
                            </div>
                        </article>
                    @endforeach
                </div>
                <button type="button" class="eks-arrow eks-next" aria-label="Ekstrakurikuler berikutnya"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
            </div>
            <div class="eks-dots" id="eksDots"></div>

            <div class="text-center" style="margin-top: 32px;">
                <a href="/unit-pendidikan#ekstrakurikuler" class="link-more">Seluruh Ekstrakurikuler <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </div>
        </div>
    </section>
    @endif

    <!-- PROGRAM UNGGULAN -->
    <section class="section keunggulan">
        <div class="container">
            <div class="section-header">
                <div class="section-badge-sm">
                    PROGRAM UNGGULAN
                </div>
                <h2 class="section-title">Program <span>Unggulan Sekolah</span></h2>
                <p class="section-desc">Seluruh warga SMP Islam Sirojul Athfal berkomitmen untuk menjaga budaya disiplin, tanggung jawab, sopan santun, kepedulian terhadap lingkungan, serta membangun karakter murid yang beriman, berakhlak mulia, mandiri, kreatif, dan bertanggung jawab sesuai dengan visi dan misi sekolah.</p>
            </div>

            <div class="keunggulan-grid">
                <div class="keunggulan-card">
                    <div class="card-icon-wrapper">
                        <i class="fa-solid fa-mosque" aria-hidden="true"></i>
                    </div>
                    <h4>Program Keagamaan dan Pembiasaan Harian</h4>
                    <p>Kegiatan Shalat Dhuha, Shalat Zuhur berjamaah, tadarus dan membaca Al-Qur'an, pembelajaran Tajwid, hafalan doa, serta kegiatan keagamaan lainnya dilaksanakan sebagai bagian dari upaya membangun budaya sekolah yang religius dan membentuk akhlakul karimah murid.</p>
                </div>
                <div class="keunggulan-card">
                    <div class="card-icon-wrapper">
                        <i class="fa-solid fa-book-journal-whills" aria-hidden="true"></i>
                    </div>
                    <h4>Literasi dan Numerasi</h4>
                    <p>Dalam mendukung peningkatan kemampuan akademik, sekolah juga mengembangkan budaya literasi dan numerasi melalui kegiatan pembiasaan.</p>
                </div>
                <div class="keunggulan-card">
                    <div class="card-icon-wrapper">
                        <i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i>
                    </div>
                    <h4>Sarana dan Prasarana</h4>
                    <p>Sekolah memiliki ruang kelas, perpustakaan, lapangan, IFP (Interactive Flat Panel), laptop, serta akses internet yang dapat dimanfaatkan sebagai sumber dan media pembelajaran.</p>
                </div>
                <div class="keunggulan-card">
                    <div class="card-icon-wrapper">
                        <i class="fa-solid fa-leaf" aria-hidden="true"></i>
                    </div>
                    <h4>Lingkungan Sekolah</h4>
                    <p>Lingkungan sekolah yang aman, bersih, tertib, dan nyaman menjadi salah satu faktor pendukung dalam menciptakan suasana belajar yang efektif.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- GALERI -->
    @if ($galeri->isNotEmpty())
        @include('partials.publik-galeri', ['ringkas' => true, 'unitGaleri' => null])
    @endif

    @include('partials.publik-cta')

    </main>

    @include('partials.publik-footer')

    <script src="{{ asset('js/hero-carousel.js') }}" defer></script>
    <script src="{{ asset('js/eks-carousel.js') }}" defer></script>
    <script>
        // Navbar Scroll Effect
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 20) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Mobile Menu Toggle
        const navToggle = document.getElementById('navToggle');
        const navbarNav = document.getElementById('navbarNav');

        navToggle.addEventListener('click', () => {
            const open = navbarNav.classList.toggle('active');
            navToggle.setAttribute('aria-expanded', open);
            navToggle.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
        });
    </script>
</body>
</html>
