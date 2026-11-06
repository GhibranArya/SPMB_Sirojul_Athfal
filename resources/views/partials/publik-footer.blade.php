{{-- Footer halaman publik. Visi dan alamat bersumber dari dokumen KSP; kontak dan media sosial dari menu admin Profil & Kontak. --}}
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="brand-name">
                    <img src="{{ asset('images/logo/logo.png') }}" alt="Logo {{ config('sekolah.nama') }}" class="logo-icon" width="44" height="44">
                    <h4>{{ config('sekolah.nama') }}<br><small style="font-weight:400; font-size:0.8rem; color:#A0AEC0;">MI &amp; SMP · {{ config('sekolah.wilayah') }}</small></h4>
                </div>
                <p>“{{ $profil->visi }}”</p>
                @if ($profil->sosial())
                <div class="footer-social">
                    @foreach ($profil->sosial() as [$label, $ikon, $url])
                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $label }} {{ config('sekolah.nama') }}"><i class="{{ $ikon }}"></i></a>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="footer-col">
                <h5>Link Cepat</h5>
                <ul>
                    <li><a href="/">Beranda</a></li>
                    <li><a href="/profil">Profil Sekolah</a></li>
                    <li><a href="/unit-pendidikan">Unit Pendidikan</a></li>
                    <li><a href="/struktur-organisasi">Struktur Organisasi</a></li>
                    <li><a href="/biaya-pendaftaran">Gelombang &amp; Biaya</a></li>
                    <li><a href="/fasilitas">Fasilitas</a></li>
                    <li><a href="/galeri">Galeri Kegiatan</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h5>Ekstrakurikuler</h5>
                <ul>
                    @foreach ($eskul as $e)
                        <li><a href="/unit-pendidikan#ekstrakurikuler">{{ $e['nama'] }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="footer-col">
                <h5>Informasi Kontak</h5>
                <ul class="footer-contact">
                    <li>
                        <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>
                        <span>{{ $profil->alamat }}</span>
                    </li>
                    <li>
                        <i class="fa-solid fa-id-card" aria-hidden="true"></i>
                        <span>NPSN {{ $profil->npsn }}</span>
                    </li>
                    @if ($profil->telepon)
                    <li><i class="fa-solid fa-phone" aria-hidden="true"></i><span>{{ $profil->telepon }}</span></li>
                    @endif
                    @if ($profil->whatsapp)
                    <li><i class="fa-brands fa-whatsapp" aria-hidden="true"></i><span><a href="{{ $profil->waLink('Assalamualaikum, saya ingin bertanya tentang '.config('sekolah.spmb').' '.config('sekolah.nama').'.') }}" target="_blank" rel="noopener">{{ $profil->whatsapp }}</a> <small>(WhatsApp admin)</small></span></li>
                    @endif
                    @if ($profil->email)
                    <li><i class="fa-solid fa-envelope" aria-hidden="true"></i><span>{{ $profil->email }}</span></li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 {{ config('sekolah.nama_yayasan') }}. All rights reserved.</p>
            <div class="footer-bottom-links">
                <a href="#">Kebijakan Privasi</a>
                <a href="#">Syarat & Ketentuan</a>
                <a href="/login" style="color: var(--accent);">Portal Wali Murid</a>
            </div>
        </div>
    </div>
</footer>

@if ($profil->whatsapp)
    <a href="{{ $profil->waLink('Assalamualaikum, saya ingin bertanya tentang '.config('sekolah.spmb').' '.config('sekolah.nama').'.') }}" class="wa-float" target="_blank" rel="noopener" aria-label="Chat admin sekolah lewat WhatsApp">
        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i><span>Chat Admin</span>
    </a>
@endif
