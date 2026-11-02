{{-- Kata sandi sementara hasil reset oleh panitia. Hanya tampil sekali (flash), tidak disimpan di tempat lain. --}}
@if ($s = session('sandi_sementara'))
    @php
        $pesan = 'Assalamualaikum, berikut kata sandi sementara akun '.config('sekolah.spmb').' '.config('sekolah.nama').' Anda: '.$s['sandi']
            .' . Silakan masuk lalu segera buat kata sandi baru. Jangan bagikan kata sandi ini kepada siapa pun.';
    @endphp
    <div class="card" style="margin-bottom:20px;border-color:var(--accent)">
        <div class="card-head" style="margin-bottom:10px">
            <div>
                <h3><i class="fa-solid fa-key" style="color:var(--accent)"></i> Kata sandi sementara untuk {{ $s['nama'] }}</h3>
                <p class="sub" style="margin:0">Masuk dengan {{ $s['email'] }}@if ($s['phone']) atau {{ $s['phone'] }}@endif. Kata sandi ini <b>hanya tampil sekali</b>; sampaikan kepada wali, yang akan diminta menggantinya saat masuk.</p>
            </div>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
            <code id="sandi-tmp" style="font-size:1.4rem;letter-spacing:.12em;padding:8px 16px;background:var(--paper-deep);border:1px dashed var(--accent);border-radius:6px">{{ $s['sandi'] }}</code>
            <button type="button" class="btn btn-sm btn-outline-light" id="salin-sandi"><i class="fa-regular fa-copy"></i> Salin</button>
            @if ($s['wa'])
                <a class="btn btn-sm btn-primary" target="_blank" rel="noopener" href="https://wa.me/{{ $s['wa'] }}?text={{ rawurlencode($pesan) }}"><i class="fa-brands fa-whatsapp"></i> Kirim lewat WhatsApp</a>
            @endif
        </div>
    </div>
    @push('scripts')
    <script>
        document.getElementById('salin-sandi').addEventListener('click', function () {
            var t = document.getElementById('sandi-tmp').textContent;
            (navigator.clipboard ? navigator.clipboard.writeText(t) : Promise.reject()).then(function () { window.toast('Kata sandi sementara disalin.'); },
                function () { window.getSelection().selectAllChildren(document.getElementById('sandi-tmp')); window.toast('Blok kata sandi lalu salin manual (Ctrl+C).'); });
        });
    </script>
    @endpush
@endif
