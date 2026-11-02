<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unit Pendidikan | {{ config('sekolah.nama') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/favicon.png') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;1,9..144,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        .matrix-table { width: 100%; border-collapse: collapse; margin-top: 30px; background: white; border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-sm); }
        .matrix-table th { background: var(--primary-dark); color: white; padding: 15px; text-align: left; font-weight: 600; border-right: 1px solid rgba(255,255,255,0.1); }
        .matrix-table td { padding: 15px; border-bottom: 1px solid var(--gray-300); border-right: 1px solid var(--gray-300); font-size: 0.9rem; color: var(--gray-700); }
        .matrix-table tr:last-child td { border-bottom: none; }
        .matrix-table td:first-child { font-weight: 600; color: var(--gray-900); background: var(--bg-cream); width: 25%; }
        .table-caption { margin-top: 14px; font-size: 0.85rem; color: var(--gray-500); }

        .ppdb-step { position: relative; padding: 20px; background: white; border: 1px solid var(--gray-300); border-radius: 12px; text-align: center; }
        .ppdb-step .step-num { width: 40px; height: 40px; background: var(--primary-pale); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem; margin: 0 auto 15px; }
        .ppdb-step h4 { font-size: 1.1rem; margin-bottom: 10px; }
        .ppdb-step p { font-size: 0.85rem; color: var(--gray-500); line-height: 1.5; }

        .eks-card { display: flex; flex-direction: column; background: var(--white); border: 1px solid var(--gray-300); border-radius: var(--radius-md); overflow: hidden; }
        .eks-head { display: flex; align-items: center; gap: 14px; padding: 20px 22px; background: var(--bg-cream); border-bottom: 1px solid var(--gray-300); }
        .eks-head .e-icon { flex-shrink: 0; width: 46px; height: 46px; border-radius: 50%; background: var(--primary-pale); color: var(--primary); display: grid; place-items: center; font-size: 1.1rem; }
        .eks-head h4 { font-size: 1.15rem; margin: 0; color: var(--gray-900); }
        .eks-head small { font-size: 0.78rem; color: var(--gray-500); }
        .eks-body { padding: 20px 22px; flex: 1; }
        .eks-body h5 { font-size: 0.72rem; letter-spacing: 0.12em; text-transform: uppercase; color: var(--gray-500); margin: 0 0 8px; }
        .eks-body h5:not(:first-child) { margin-top: 18px; }
        .eks-body p { font-size: 0.88rem; color: var(--gray-700); line-height: 1.6; margin: 0; }
        .eks-body ul { list-style: none; padding: 0; margin: 0; }
        .eks-body li { display: flex; gap: 8px; font-size: 0.88rem; color: var(--gray-700); line-height: 1.55; margin-bottom: 8px; }
        .eks-body li i { color: var(--primary); margin-top: 4px; font-size: 0.8rem; }
        .eks-tags { display: flex; flex-wrap: wrap; gap: 6px; }
        .eks-tags span { background: var(--primary-pale); color: var(--primary); padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .eks-foot { padding: 14px 22px; border-top: 1px solid var(--gray-300); font-size: 0.85rem; color: var(--gray-700); }
        .eks-foot i { color: var(--primary); margin-right: 6px; }

        .habit-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-top: 10px; }
        .habit-card { background: var(--white); border: 1px solid var(--gray-300); border-radius: var(--radius-md); padding: 22px 18px; text-align: center; }
        .habit-ico { width: 52px; height: 52px; margin: 0 auto 14px; border-radius: 50%; background: var(--primary-pale); color: var(--primary); display: grid; place-items: center; font-size: 1.2rem; }
        .habit-card h4 { font-size: 1rem; margin: 0 0 8px; color: var(--gray-900); }
        .habit-card p { font-size: 0.84rem; color: var(--gray-500); line-height: 1.55; margin: 0; }

        @media (max-width: 1100px) {
            .habit-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 992px) {
            .matrix-table { display: block; overflow-x: auto; }
        }
        @media (max-width: 640px) {
            .habit-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .page-hero h1 { font-size: 2rem !important; }
            .page-hero .lead-text { margin-bottom: 25px !important; }
            .matrix-table th,
            .matrix-table td { padding: 12px; }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body>

    @include('partials.publik-nav')

    <main id="main" tabindex="-1">

    <!-- HERO UNIT PENDIDIKAN -->
    <section class="page-hero pt-140 pb-60 text-center" style="background-color: var(--bg-cream);">
        <div class="container">
            <div class="section-badge-sm mx-auto mb-3" style="width: max-content; max-width: 100%; background: var(--primary-pale); color: var(--primary); padding: 8px 16px; border-radius: 50px; font-weight: 600; font-size: 0.85rem;">
                <i class="fa-solid fa-graduation-cap"></i> SMP ISLAM SIROJUL ATHFAL KABUPATEN BOGOR
            </div>
            <h1 class="display-title mb-4" style="font-size: 2.5rem; font-weight: 800; color: var(--primary-dark); max-width: 800px; margin: 0 auto;">
                Unit Pendidikan <span style="color: var(--primary);">SMP Islam Sirojul Athfal</span>
            </h1>
            <p class="lead-text" style="max-width: 700px; margin: 0 auto 40px; color: var(--gray-500);">
                Mata pelajaran yang diajarkan di SMP Islam Sirojul Athfal terdiri dari sepuluh mata pelajaran utama dan satu mata pelajaran muatan lokal.
            </p>
        </div>
    </section>

    <!-- KEBIASAAN MURID & PENDIDIK. Daftar kebiasaan masih data contoh (dummy); ganti sesuai kegiatan sebenarnya. -->
    <section class="section" style="padding: 60px 0;">
        <div class="container">
            <div class="section-header">
                <div class="section-badge-sm">BUDAYA SEKOLAH SMP ISLAM SIROJUL ATHFAL</div>
                <h2 class="section-title">Kebiasaan <span>Murid</span></h2>
                <p class="section-desc">Murid dibiasakan melaksanakan kegiatan rutin yang menumbuhkan ibadah, kebersihan, kedisiplinan, dan kepedulian di lingkungan sekolah.</p>
            </div>

            <div class="habit-grid">
                @foreach ([
                    ['fa-broom', 'Jumat Bersih', 'Seluruh murid bergotong royong membersihkan kelas, halaman, dan lingkungan sekolah setiap hari Jumat pagi.'],
                    ['fa-book-quran', 'Tadarus Bersama', 'Membaca Al-Qur\'an bersama sebelum pembelajaran dimulai untuk membiasakan cinta Al-Qur\'an.'],
                    ['fa-person-praying', 'Shalat Dhuha dan Zuhur Berjamaah', 'Melaksanakan shalat Dhuha dan shalat Zuhur berjamaah di sekolah secara tertib.'],
                    ['fa-handshake', 'Salam, Sapa, dan Salim', 'Menyambut guru dengan salam dan salim di gerbang sekolah untuk membangun sopan santun.'],
                    ['fa-hand-holding-heart', 'Infak Jumat', 'Menyisihkan sebagian uang saku untuk infak yang disalurkan bagi kegiatan sosial sekolah.'],
                ] as [$ikon, $judul, $isi])
                    <div class="habit-card">
                        <span class="habit-ico"><i class="fa-solid {{ $ikon }}"></i></span>
                        <h4>{{ $judul }}</h4>
                        <p>{{ $isi }}</p>
                    </div>
                @endforeach
            </div>

            <div style="margin-top: 50px;">
                <h3 style="font-size: 1.4rem; margin-bottom: 14px;">Pendidik</h3>
                <p class="paragraph-text" style="max-width: 900px;">Pendidik dan tenaga kependidikan di SMP Islam Sirojul Athfal memiliki komitmen dan dedikasi yang tinggi dalam melaksanakan tugas pendidikan. Dari 11 guru yang ada, sebanyak 8 guru telah memiliki kualifikasi akademik D-IV atau S1 (Sarjana), sedangkan 3 guru lainnya masih dalam proses pemenuhan kualifikasi akademik tersebut.</p>
                <p class="paragraph-text" style="max-width: 900px;">Selain peningkatan kualifikasi akademik, sekolah juga terus mendorong peningkatan profesionalisme guru. Saat ini, sebanyak 2 guru telah memiliki sertifikat pendidik, sementara 1 guru sedang mengikuti Pendidikan Profesi Guru (PPG) sebagai bagian dari upaya pemenuhan kompetensi dan profesionalisme pendidik.</p>
            </div>
        </div>
    </section>

    <!-- EKSTRAKURIKULER -->
    @if (count($eskul))
    <section class="section" id="ekstrakurikuler" style="padding: 60px 0; background-color: var(--bg-cream);">
        <div class="container">
            <div class="section-header">
                <div class="section-badge-sm">EKSTRAKURIKULER</div>
                <h2 class="section-title">Data Kegiatan <span>Ekstrakurikuler</span></h2>
                <p class="section-desc">Kegiatan ekstrakurikuler yang dilaksanakan di luar jam belajar intrakurikuler di bawah bimbingan dan pengawasan satuan pendidikan. Kegiatan ini bertujuan untuk meningkatkan kemampuan kognitif, afektif, dan psikomotorik serta mengembangkan potensi, bakat dan minat dalam upaya pembinaan peribadi menuju manusia seutuhnya.</p>
            </div>

            <div class="grid-3">
                @foreach ($eskul as $e)
                    <div class="eks-card">
                        <div class="eks-head">
                            <div class="e-icon"><i class="fa-solid {{ $e['ikon'] }}"></i></div>
                            <div>
                                <h4>{{ $e['nama'] }}</h4>
                                <small>{{ $e['waktu'] }}</small>
                            </div>
                        </div>
                        <div class="eks-body">
                            <h5>Tujuan</h5>
                            @if (count($e['tujuan']) === 1)
                                <p>{{ $e['tujuan'][0] }}</p>
                            @else
                                <ul>@foreach ($e['tujuan'] as $t)<li><i class="fa-solid fa-circle-check"></i> {{ $t }}</li>@endforeach</ul>
                            @endif
                            <h5>Target Pencapaian</h5>
                            @if (count($e['target']) === 1)
                                <p>{{ $e['target'][0] }}</p>
                            @else
                                <ul>@foreach ($e['target'] as $t)<li><i class="fa-solid fa-circle-check"></i> {{ $t }}</li>@endforeach</ul>
                            @endif
                            <h5>Profil Lulusan</h5>
                            <div class="eks-tags">@foreach ($e['profil'] as $p)<span>{{ $p }}</span>@endforeach</div>
                        </div>
                        <div class="eks-foot"><i class="fa-solid fa-user"></i> Pembimbing: <strong>{{ $e['pembimbing'] }}</strong></div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- ALUR PPDB -->
    <section class="section" style="padding: 80px 0; background-color: var(--bg-cream);">
        <div class="container section-header-flex mb-5">
            <div class="left text-left">
                <div class="text-primary font-bold text-sm mb-2 uppercase tracking-wide">Panduan Registrasi</div>
                <h2 class="section-title" style="color: var(--primary-dark); font-size: 2rem; margin: 0;">Alur Pendaftaran Murid Baru ({{ config('sekolah.spmb') }})</h2>
            </div>
        </div>

        <div class="container">
            <div class="grid-4 gap-4 mb-5">
                <div class="ppdb-step">
                    <div class="step-num">1</div>
                    <h4>Pendaftaran Online</h4>
                    <p>Membuat akun, memilih jenjang (MI atau SMP), mengisi formulir, dan mengunggah berkas persyaratan (PDF atau foto).</p>
                </div>
                <div class="ppdb-step">
                    <div class="step-num">2</div>
                    <h4>Verifikasi Berkas</h4>
                    <p>Panitia memeriksa data dan berkas. Bila diperlukan perbaikan, pendaftar mengirim ulang.</p>
                </div>
                <div class="ppdb-step">
                    <div class="step-num">3</div>
                    <h4>Bukti Pendaftaran</h4>
                    <p>Setelah formulir terkirim, bukti pendaftaran (PDF) dapat diunduh dari akun pendaftar.</p>
                </div>
                <div class="ppdb-step">
                    <div class="step-num">4</div>
                    <h4>Pengumuman</h4>
                    <p>Hasil seleksi dapat dilihat pada akun pendaftar setelah dipublikasikan oleh panitia.</p>
                </div>
            </div>

            <div class="text-center">
                <a href="/login" class="btn btn-accent btn-lg"><i class="fa-solid fa-pen-to-square"></i> Formulir {{ config('sekolah.spmb') }} Online</a>
                <a href="/biaya-pendaftaran" class="btn btn-outline-light btn-lg" style="margin-left:8px"><i class="fa-solid fa-receipt"></i> Gelombang &amp; Biaya</a>
            </div>
        </div>
    </section>

    </main>

    @include('partials.publik-footer')

    <script>
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
