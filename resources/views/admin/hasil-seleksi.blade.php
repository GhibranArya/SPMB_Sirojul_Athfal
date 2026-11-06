@extends('layouts.admin')

@section('title', 'Hasil Seleksi')
@section('crumb', 'Hasil Seleksi')

@section('content')
<div class="page-title" style="display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap">
    <div>
        <h1>Hasil Seleksi</h1>
        <p>Tetapkan hasil untuk pendaftar yang sudah terverifikasi, lalu publikasikan pada {{ $gelombang['pengumuman'] }}.</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        @include('partials.admin-pilih-gelombang')
        @if ($terbit)
            <span class="st st-green"><i class="fa-solid fa-bullhorn" aria-hidden="true"></i> Sudah dipublikasikan</span>
        @else
            <button class="btn btn-accent" data-open="publish"><i class="fa-solid fa-bullhorn" aria-hidden="true"></i> Publikasikan Pengumuman</button>
        @endif
    </div>
</div>

@if ($menunggu > 0)
    <div class="alert" style="background:#FBF3DE;border-color:#D29D29;color:#6B5210"><i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        <span>{{ $menunggu }} pendaftar masih <b>Menunggu Verifikasi</b> dan belum muncul di sini. <a href="/admin/pendaftar?gelombang={{ $dipilih }}" style="font-weight:700;text-decoration:underline">Periksa sekarang</a></span></div>
@endif

<div class="quota" id="quota">
    @foreach ($kuota as $j => $k)
        <div class="q" data-j="{{ $j }}" data-kuota="{{ $k['kuota'] }}">
            <header><b>{{ $jenjang[$j] }}</b><span><span class="used">{{ $k['diterima'] }}</span> / {{ $k['kuota'] }} kursi</span></header>
            <div class="bar"><i style="width:{{ min(100, $k['kuota'] ? round($k['diterima'] / $k['kuota'] * 100) : 0) }}%"></i></div>
            <small class="msg">Sisa {{ max(0, $k['kuota'] - $k['diterima']) }} kursi</small>
        </div>
    @endforeach
</div>

<form action="/admin/hasil-seleksi" method="POST" data-once>
    @csrf
    <input type="hidden" name="gelombang_id" value="{{ $dipilih }}">
    <div class="tbl-wrap">
        <table class="tbl" id="hasil">
            <thead>
                <tr><th>Calon siswa</th><th>Kode</th><th>Status</th><th>Tetapkan hasil</th></tr>
            </thead>
            <tbody>
                @forelse ($list as $r)
                    <tr data-jenjang="{{ $r['jenjang'] }}">
                        <td class="nm"><b>{{ $r['nama'] }}</b><small>Wali: {{ $r['wali'] }}</small></td>
                        <td><code>{{ $r['kode'] }}</code></td>
                        <td><span class="st {{ $status[$r['status']][1] }}" data-badge>{{ $status[$r['status']][0] }}</span></td>
                        <td>
                            <select class="form-control" name="hasil[{{ $r['id'] }}]" style="min-height:38px;padding:6px 10px;width:auto;min-width:170px" data-hasil aria-label="Hasil untuk {{ $r['nama'] }}" @disabled($terbit)>
                                <option value="terverifikasi" {{ $r['status'] === 'terverifikasi' ? 'selected' : '' }}>— Belum ditetapkan —</option>
                                <option value="diterima" {{ $r['status'] === 'diterima' ? 'selected' : '' }}>Diterima</option>
                                <option value="ditolak" {{ $r['status'] === 'ditolak' ? 'selected' : '' }}>Tidak Diterima</option>
                            </select>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;color:var(--gray-500);padding:28px">Belum ada pendaftar yang terverifikasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if (! $terbit && count($list))
        <div class="form-actions" style="border:0">
            <span style="font-size:.85rem;color:var(--gray-500)">Hasil baru terlihat oleh pendaftar setelah pengumuman dipublikasikan.</span>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Simpan Hasil</button>
        </div>
    @endif
</form>

<div class="modal" id="publish" role="dialog" aria-modal="true" aria-labelledby="p-title">
    <div class="modal-box narrow">
        <form action="/admin/hasil-seleksi/publikasi" method="POST" data-once>
            @csrf
            <input type="hidden" name="gelombang_id" value="{{ $dipilih }}">
            <div class="modal-head">
                <h3 id="p-title">Publikasikan pengumuman?</h3>
                <button type="button" class="modal-x" data-close aria-label="Tutup"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            </div>
            <div class="modal-body">
                <p style="margin-bottom:12px">Setelah dipublikasikan, pendaftar dapat melihat hasilnya di akun masing-masing. Hasil tidak dapat diubah lagi.</p>
                <p style="font-size:.9rem;color:var(--gray-500)">Pastikan semua hasil sudah <b>disimpan</b> dan jumlah <i>Diterima</i> tidak melebihi kuota.</p>
                @if (array_sum($belumSelesai) > 0)
                    <div class="alert" style="background:#FBF3DE;border-color:#D29D29;color:#6B5210;margin:12px 0 0"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                        <span>Masih ada pendaftar yang belum selesai dan <b>tidak ikut</b> pengumuman ini:
                            {{ collect($belumSelesai)->filter()->map(fn ($n, $label) => $n.' '.$label)->implode(', ') }}.
                            Pendaftar Draft dapat melanjutkan di gelombang berikutnya.</span></div>
                @endif
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-white" data-close>Batal</button>
                <button type="submit" class="btn btn-accent">Ya, Publikasikan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var STATUS = @json($status);
    function recount() {
        document.querySelectorAll('#quota .q').forEach(function (q) {
            var j = q.dataset.j, kuota = +q.dataset.kuota, n = 0;
            document.querySelectorAll('#hasil tbody tr[data-jenjang="' + j + '"] select').forEach(function (s) { if (s.value === 'diterima') n++; });
            q.querySelector('.used').textContent = n;
            var bar = q.querySelector('.bar i');
            bar.style.width = Math.min(100, kuota ? Math.round(n / kuota * 100) : 0) + '%';
            bar.className = n > kuota ? 'over' : '';
            q.querySelector('.msg').textContent = n > kuota ? 'Melebihi kuota ' + (n - kuota) + ' kursi' : 'Sisa ' + (kuota - n) + ' kursi';
            q.querySelector('.msg').style.color = n > kuota ? '#C53030' : '';
        });
    }
    document.querySelectorAll('[data-hasil]').forEach(function (sel) {
        sel.addEventListener('change', function () {
            var b = sel.closest('tr').querySelector('[data-badge]');
            b.className = 'st ' + STATUS[sel.value][1];
            b.textContent = STATUS[sel.value][0];
            recount();
        });
    });
    recount();
})();
</script>
@endpush
