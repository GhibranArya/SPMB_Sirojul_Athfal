<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#FFFFFF">
    <title>Struktur Organisasi | {{ config('sekolah.nama') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/favicon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .org { --wew: #0D6E3E; --koor: #D6264F; --fung: #1A87C9; max-width: 1040px; margin: 0 auto; }
        .org-legend { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px 28px; padding: 12px 18px; margin-bottom: 28px; background: #fff; border: 1px solid var(--gray-300); border-radius: var(--radius-md); font-size: .85rem; font-weight: 600; color: var(--gray-700); }
        .org-legend span { display: inline-flex; align-items: center; gap: 8px; }
        .org-legend i { display: inline-block; width: 34px; height: 0; border-top: 3px solid var(--wew); }
        .org-legend i.k { border-top: 3px dashed var(--koor); }
        .org-legend i.f { border-top: 3px dotted var(--fung); }

        .org-box { background: #fff; border: 1px solid var(--gray-300); border-top: 4px solid var(--gray-500); border-radius: var(--radius-md); padding: 10px 14px; text-align: center; }
        .org-box small { display: block; font-size: .72rem; letter-spacing: .06em; text-transform: uppercase; font-weight: 700; color: var(--gray-500); }
        .org-box b { display: block; font-size: .98rem; color: var(--gray-900); margin-top: 2px; }
        .org-box.pimpin { border-top-color: var(--wew); background: #F0FAF4; } .org-box.pimpin small { color: var(--wew); }
        .org-box.komite { border-top-color: var(--koor); background: #FFF3F5; } .org-box.komite small { color: var(--koor); }
        .org-box.penunjang { border-top-color: #5B5BD6; background: #F7F5FF; } .org-box.penunjang small { color: #5B5BD6; }
        .org-box.peserta { background: #F2F5F9; border-top-color: var(--gray-300); } .org-box.peserta b { color: var(--wew); }

        .org-line { width: 0; height: 26px; margin: 0 auto; border-left: 3px solid var(--wew); }
        .org-line.f { border-left: 3px dotted var(--fung); height: 22px; }
        .org-top { max-width: 320px; margin: 0 auto; }
        .org-penunjang { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; max-width: 640px; margin: 0 auto; }
        .org-units { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; }
        .org-unit { padding: 18px; border: 1.5px dashed var(--gray-300); border-radius: 14px; background: rgba(255,255,255,.55); }
        .org-unit h3 { margin: 0 0 16px; text-align: center; font-size: .98rem; }
        .org-unit h3 span { display: inline-block; padding: 7px 16px; border-radius: 999px; background: #CCF5E6; color: #0B6A55; letter-spacing: .02em; }
        .org-row { display: grid; grid-template-columns: 1.4fr 1fr; gap: 10px; align-items: stretch; }
        .org-row.dua { grid-template-columns: 1fr 1fr; }
        .org-row .link-k { position: relative; }
        .org-gap { height: 12px; }
        .org-dots { width: 0; height: 14px; margin: 0 auto; border-left: 3px dotted var(--fung); }

        .org-tbl-wrap { overflow-x: auto; border: 1px solid var(--gray-300); border-radius: var(--radius-md); background: #fff; }
        .org-tbl { width: 100%; border-collapse: collapse; min-width: 640px; font-size: .9rem; }
        .org-tbl th { text-align: left; padding: 12px 16px; background: #F2F5F9; color: var(--wew); font-size: .78rem; letter-spacing: .05em; text-transform: uppercase; }
        .org-tbl td { padding: 12px 16px; border-top: 1px solid var(--gray-300); vertical-align: top; color: var(--gray-700); line-height: 1.5; }
        .org-tbl td:first-child { font-weight: 700; color: var(--gray-900); white-space: nowrap; }
        .org-tbl td b { color: var(--gray-900); }

        .garis-list { display: grid; gap: 12px; }
        .garis { padding: 14px 18px; border-left: 5px solid; border-radius: 8px; }
        .garis h4 { margin: 0 0 4px; font-size: .95rem; }
        .garis p { margin: 0; font-size: .88rem; line-height: 1.6; color: var(--gray-700); }
        .garis.wewenang { border-color: var(--wew); background: #EFFAF3; } .garis.wewenang h4 { color: var(--wew); }
        .garis.koordinasi { border-color: var(--koor); background: #FFF1F3; } .garis.koordinasi h4 { color: var(--koor); }
        .garis.fungsional { border-color: var(--fung); background: #EEF7FD; } .garis.fungsional h4 { color: var(--fung); }

        @media (max-width: 820px) {
            .org-units { grid-template-columns: 1fr; }
            .org-penunjang { grid-template-columns: 1fr; gap: 12px; }
        }
        @media (max-width: 460px) { .org-row { grid-template-columns: 1fr; } .org-unit { padding: 12px; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tegas.css') }}">
</head>
<body>

    @include('partials.publik-nav')

    <main id="main" tabindex="-1">

    <section class="section text-center hero-halaman" style="background-color: var(--bg-cream); padding-top: 140px; padding-bottom: 40px;">
        <div class="container">
            <div class="section-badge-sm" style="background: var(--white); border: 1px solid var(--primary-pale); padding: 8px 16px; border-radius: 50px;">
                <i class="fa-solid fa-sitemap" aria-hidden="true"></i> TAHUN AJARAN {{ $s['tahun_ajaran'] }}
            </div>
            <h1 class="section-title" style="font-size: 2.6rem; max-width: 800px; margin: 0 auto 16px;">
                Struktur <span>Organisasi</span> Sekolah
            </h1>
            <p class="section-desc">Madrasah Ibtidaiyah (MI) dan Sekolah Menengah Pertama (SMP) di bawah {{ config('sekolah.nama_yayasan') }}.</p>
            <a href="{{ asset('docs/struktur-organisasi-mi-smp.pdf') }}" class="btn btn-outline-light" target="_blank" rel="noopener" style="margin-top: 14px;"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Unduh PDF</a>
        </div>
    </section>

    <section class="section" style="padding-top: 30px;">
        <div class="container">
            <div class="org">
                <div class="org-legend" aria-label="Keterangan garis">
                    <span><i></i> Garis Wewenang / Instruksi</span>
                    <span><i class="k"></i> Garis Koordinasi</span>
                    <span><i class="f"></i> Garis Fungsional / Layanan</span>
                </div>

                <div class="org-top org-box pimpin"><small>{{ $s['ketua']['jabatan'] }}</small><b>{{ $s['ketua']['nama'] }}</b></div>
                <div class="org-line"></div>
                <div class="org-penunjang">
                    @foreach ($s['penunjang'] as $p)
                        <div class="org-box penunjang"><small>{{ $p['jabatan'] }}</small><b>{{ $p['nama'] }}</b></div>
                    @endforeach
                </div>
                <div class="org-line f"></div>

                <div class="org-units">
                    @foreach ($s['unit'] as $u)
                        <div class="org-unit">
                            <h3><span>{{ mb_strtoupper($u['judul']) }}</span></h3>
                            <div class="org-row">
                                <div class="org-box pimpin"><small>{{ $u['pimpinan']['jabatan'] }}</small><b>{{ $u['pimpinan']['nama'] }}</b></div>
                                <div class="org-box komite" style="border-style: dashed; border-top-style: solid;"><small>{{ $u['komite']['jabatan'] }}</small><b>{{ $u['komite']['nama'] }}</b></div>
                            </div>
                            <div class="org-line" style="height:18px"></div>
                            <div class="org-row dua">
                                <div class="org-box"><small>{{ $u['bendahara']['jabatan'] }}</small><b>{{ $u['bendahara']['nama'] }}</b></div>
                                <div class="org-box"><small>{{ $u['kurikulum']['jabatan'] }}</small><b>{{ $u['kurikulum']['nama'] }}</b></div>
                            </div>
                            <div class="org-gap"></div>
                            <div class="org-box"><small>{{ $u['kesiswaan']['jabatan'] }}</small><b>{{ $u['kesiswaan']['nama'] }}</b></div>
                            <div class="org-line" style="height:18px"></div>
                            <div class="org-box pimpin" style="background:#fff"><small>Pelaksana Akademik</small><b>{{ $u['pelaksana'] }}</b></div>
                            <div class="org-dots"></div>
                            <div class="org-box peserta"><small>Peserta Didik</small><b>{{ $u['peserta'] }}</b></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="section" style="background-color: var(--bg-cream);">
        <div class="container" style="max-width: 1040px;">
            <h2 class="section-title" style="font-size: 1.7rem;">Deskripsi Tugas &amp; <span>Pembagian Wewenang</span></h2>
            <div class="org-tbl-wrap" style="margin-top: 20px;">
                <table class="org-tbl">
                    <thead><tr><th>Jabatan</th><th>Nama personel</th><th>Tugas utama &amp; wewenang singkat</th></tr></thead>
                    <tbody>
                        @foreach ($s['tugas'] as [$jabatan, $nama, $tugas])
                            <tr>
                                <td>{{ $jabatan }}</td>
                                <td>
                                    @foreach ($nama as $unit => $n)
                                        @if (is_string($unit))<b>{{ $unit }}:</b> @endif{{ $n }}@if (! $loop->last)<br>@endif
                                    @endforeach
                                </td>
                                <td>{{ $tugas }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <h2 class="section-title" style="font-size: 1.7rem; margin-top: 44px;">Penjelasan <span>Garis Hubungan Kerja</span></h2>
            <div class="garis-list" style="margin-top: 20px;">
                @foreach ($s['garis'] as $g)
                    <div class="garis {{ $g['kelas'] }}"><h4>{{ $g['judul'] }}</h4><p>{{ $g['isi'] }}</p></div>
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
