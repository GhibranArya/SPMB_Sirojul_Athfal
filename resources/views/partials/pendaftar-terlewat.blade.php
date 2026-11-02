{{-- Pendaftaran Draft pada gelombang yang sudah ditutup: terkunci, tetapi dapat dilanjutkan di gelombang aktif. --}}
@if ($terlewat ?? false)
    <div class="card" style="margin-bottom:20px;border-color:var(--accent)">
        <div class="card-head" style="margin:0">
            <div>
                <h3>{{ $gelombang['nama'] }} sudah ditutup</h3>
                <p class="sub" style="margin:0">
                    Batas pendaftaran {{ $gelombang['tutup'] }} sudah lewat, jadi formulir dan berkas tidak dapat diubah. Pendaftaran Anda tersimpan.
                    @if ($lanjutKe)
                        {{ $lanjutKe['nama'] }} TA {{ $lanjutKe['tahun_ajaran'] }} sedang dibuka sampai {{ $lanjutKe['tutup'] }}; Anda dapat meneruskan pendaftaran ini di sana tanpa mengisi ulang.
                    @else
                        Anda dapat melanjutkannya saat gelombang berikutnya dibuka.
                    @endif
                </p>
            </div>
            @if ($lanjutKe)
                <form action="/pendaftar/lanjutkan-gelombang" method="POST" data-once>
                    @csrf
                    <button type="submit" class="btn btn-accent">Lanjutkan di {{ $lanjutKe['nama'] }} <i class="fa-solid fa-arrow-right"></i></button>
                </form>
            @endif
        </div>
    </div>
@elseif ($terkunciPengumuman ?? false)
    <div class="alert alert-error"><i class="fa-solid fa-lock"></i><span>Pengumuman {{ $gelombang['nama'] }} sudah terbit, jadi formulir dan berkas tidak dapat diubah lagi. Hubungi panitia bila ada kendala.</span></div>
@endif
