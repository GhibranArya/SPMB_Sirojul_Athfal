{{-- Banner pendaftaran. Kalimat bersumber dari bagian "Rekrutmen Murid" > "Program Khusus" dokumen KSP. --}}
<section class="cta-section">
    <div class="container">
        <div class="cta-banner">
            <div class="cta-content-left">
                <div class="cta-badge">
                    <i class="fa-solid fa-door-open"></i> Rekrutmen Murid
                </div>
                <h2>Program Khusus</h2>
                <p>Menawarkan beasiswa untuk Anak yatim dan berprestasi dan memiliki prestasi sebagai daya tarik tambahan. Beasiswa ini mencakup bebas biaya sekolah, biaya buku, dan fasilitas pendukung lainnya.</p>
            </div>
            <div class="cta-content-right">
                <a href="/login" class="btn btn-accent btn-lg w-100"><i class="fa-solid fa-pen-to-square"></i> Formulir {{ config('sekolah.spmb') }} Online</a>
                <a href="/biaya-pendaftaran" class="btn btn-ghost-light btn-lg w-100" style="margin-top:10px"><i class="fa-solid fa-receipt"></i> Gelombang &amp; Biaya</a>
                @if ($profil->whatsapp)
                    <a href="{{ $profil->waLink('Assalamualaikum, saya ingin bertanya tentang '.config('sekolah.spmb').' '.config('sekolah.nama').'.') }}" target="_blank" rel="noopener" class="btn btn-ghost-light btn-lg w-100" style="margin-top:10px"><i class="fa-brands fa-whatsapp"></i> Tanya Admin: {{ $profil->whatsapp }}</a>
                @endif
            </div>
        </div>
    </div>
</section>
