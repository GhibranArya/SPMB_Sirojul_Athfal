<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gelombang &amp; Biaya Pendaftaran | {{ config('sekolah.nama') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/favicon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;1,9..144,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .bg-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start; }
        .bg-col > h2 { margin: 0 0 16px; padding: 14px 18px; border-radius: var(--radius-md); color: #fff; font-size: 1.15rem; letter-spacing: .03em; }
        .bg-col.mi > h2 { background: #0B4A22; }
        .bg-col.smp > h2 { background: #0B2F6B; }
        .bg-card { background: #fff; border: 1px solid var(--gray-300); border-radius: var(--radius-lg); margin-bottom: 18px; overflow: hidden; }
        .bg-card.dibuka { border-color: var(--primary); box-shadow: 0 0 0 2px var(--primary-pale); }
        .bg-head { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 14px 18px; background: var(--bg-cream); border-bottom: 1px solid var(--gray-300); }
        .bg-head h3 { margin: 0; font-size: 1.1rem; }
        .bg-head small { display: block; color: var(--gray-500); font-weight: 600; }
        .bg-st { font-size: .72rem; font-weight: 700; letter-spacing: .04em; padding: 4px 10px; border-radius: 999px; white-space: nowrap; }
        .bg-st.dibuka { background: #DDF5E7; color: #146B3A; } .bg-st.belum { background: #FFF1CC; color: #8A5A00; } .bg-st.ditutup { background: #ECEEF0; color: #5B6670; }
        .bg-rows { padding: 6px 18px 14px; }
        .bg-rows .r { display: flex; justify-content: space-between; gap: 14px; padding: 9px 0; border-bottom: 1px dashed var(--gray-300); font-size: .92rem; color: var(--gray-700); }
        .bg-rows .r:last-child { border-bottom: 0; }
        .bg-rows .r b { white-space: nowrap; color: var(--gray-900); }
        .bg-rows .r b.gratis { color: #1B7F46; }
        .bg-kosong { padding: 18px; color: var(--gray-500); font-size: .9rem; }
        .bg-note { margin-top: 8px; padding: 14px 18px; background: #fff; border: 1px solid var(--gray-300); border-left: 4px solid var(--accent, #C8963E); border-radius: 8px; font-size: .88rem; line-height: 1.6; color: var(--gray-700); }
        .bg-syarat { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-top: 30px; }
        .bg-syarat .box { background: #fff; border: 1px solid var(--gray-300); border-radius: var(--radius-lg); padding: 18px 20px; }
        .bg-syarat h3 { margin: 0 0 10px; font-size: 1.05rem; }
        .bg-syarat ul { margin: 0; padding-left: 20px; line-height: 1.8; color: var(--gray-700); font-size: .92rem; }
        @media (max-width: 820px) { .bg-grid, .bg-syarat { grid-template-columns: 1fr; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body>

    @include('partials.publik-nav')

    <main id="main" tabindex="-1">

    <section class="section text-center" style="background-color: var(--bg-cream); padding-top: 140px; padding-bottom: 40px;">
        <div class="container">
            <div class="section-badge-sm" style="background: var(--white); border: 1px solid var(--primary-pale); padding: 8px 16px; border-radius: 50px;">
                <i class="fa-solid fa-receipt"></i> {{ config('sekolah.spmb') }} @if ($tahun) TAHUN AJARAN {{ $tahun }} @endif
            </div>
            <h1 class="section-title" style="font-size: 2.6rem; max-width: 820px; margin: 0 auto 16px;">
                Gelombang &amp; <span>Biaya Pendaftaran</span>
            </h1>
            <p class="section-desc">Pendaftaran dibuka bergelombang. Pilih jenjang MI atau SMP, lalu daftar pada gelombang yang sedang dibuka.</p>
            <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin-top:18px">
                <a href="/login" class="btn btn-primary btn-lg"><i class="fa-solid fa-pen-to-square"></i> Daftar {{ config('sekolah.spmb') }} Online</a>
                @if ($profil->whatsapp)
                    <a href="{{ $profil->waLink('Assalamualaikum, saya ingin bertanya tentang biaya '.config('sekolah.spmb').' '.config('sekolah.nama').'.') }}" target="_blank" rel="noopener" class="btn btn-outline-light btn-lg"><i class="fa-brands fa-whatsapp"></i> Tanya Admin: {{ $profil->whatsapp }}</a>
                @endif
            </div>
        </div>
    </section>

    <section class="section" style="padding-top: 30px;">
        <div class="container">
            @if ($gelombang->isEmpty())
                <p class="section-desc text-center">Jadwal gelombang belum tersedia.</p>
            @else
                <div class="bg-grid">
                    @foreach ($jenjang as $kode => $judul)
                        <div class="bg-col {{ strtolower($kode) }}" id="{{ strtolower($kode) }}">
                            <h2>{{ mb_strtoupper(config('sekolah.unit.'.$kode, $judul)) }}</h2>
                            @foreach ($gelombang as $g)
                                @php $baris = $biaya[$g->id][$kode] ?? []; $st = $g->statusJadwal(); @endphp
                                <article class="bg-card {{ $g->is_aktif && $st === 'dibuka' ? 'dibuka' : '' }}" @if ($loop->parent->first) id="gelombang-{{ $g->id }}" @endif>
                                    <header class="bg-head">
                                        <div>
                                            <h3>{{ $g->nama }}</h3>
                                            <small>{{ $periode($g) }}</small>
                                        </div>
                                        <span class="bg-st {{ $st }}">{{ ['belum' => 'AKAN DIBUKA', 'dibuka' => 'DIBUKA', 'ditutup' => 'DITUTUP'][$st] }}</span>
                                    </header>
                                    @if ($baris)
                                        <div class="bg-rows">
                                            @foreach ($baris as $b)
                                                <div class="r"><span>{{ $b['nama'] }}</span><b class="{{ $b['gratis'] ? 'gratis' : '' }}">{{ $b['tampil'] }}</b></div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="bg-kosong">Rincian biaya belum tersedia.</div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="bg-note">
                <b>Catatan:</b> rincian biaya dapat berubah sesuai ketetapan yayasan. Untuk informasi terbaru, hubungi admin sekolah
                @if ($profil->whatsapp) lewat WhatsApp di <b>{{ $profil->whatsapp }}</b>@endif.
            </div>

            <div class="bg-syarat">
                @foreach ($berkas as $kode => $daftar)
                    <div class="box">
                        <h3><i class="fa-solid fa-folder-open" style="color:var(--primary)"></i> Berkas pendaftaran {{ config('sekolah.unit.'.$kode, $kode) }}</h3>
                        <ul>@foreach ($daftar as $nama)<li>{{ $nama }}</li>@endforeach</ul>
                        <p style="margin:10px 0 0;font-size:.84rem;color:var(--gray-500)">Diunggah saat mendaftar online, berupa PDF atau foto (JPG/PNG).</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    </main>

    @include('partials.publik-footer')

    <script>
        const navToggle = document.getElementById('navToggle');
        const navbarNav = document.getElementById('navbarNav');
        navToggle.addEventListener('click', () => {
            const open = navbarNav.classList.toggle('active');
            navToggle.setAttribute('aria-expanded', open);
            navToggle.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
        });
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
        });
    </script>
</body>
</html>
