@extends('layouts.admin')

@section('title', 'Pendaftar')
@section('crumb', 'Pendaftar')

@section('content')
<div class="page-title" style="display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap">
    <div>
        <h1>Data Pendaftar</h1>
        <p>Periksa data dan berkas, lalu setujui atau minta perbaikan.</p>
    </div>
    @include('partials.admin-pilih-gelombang')
</div>

@include('partials.sandi-sementara')

<div class="stats">
    <div class="stat"><b>{{ $stat['total'] }}</b><span>Total pendaftar</span></div>
    <div class="stat s-gold"><b>{{ $stat['menunggu'] }}</b><span>Menunggu verifikasi</span></div>
    <div class="stat s-orange"><b>{{ $stat['perbaikan'] }}</b><span>Perlu perbaikan</span></div>
    <div class="stat s-blue"><b>{{ $stat['terverifikasi'] }}</b><span>Terverifikasi</span></div>
    <div class="stat s-green"><b>{{ $stat['diterima'] }}</b><span>Diterima</span></div>
</div>

<div class="toolbar">
    <div class="search">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <input class="form-control" id="q" type="search" placeholder="Cari nama, kode, atau wali…">
    </div>
    <select class="form-control" id="fs" aria-label="Filter status">
        <option value="">Semua status</option>
        @foreach ($status as $k => $v)<option value="{{ $k }}">{{ $v[0] }}</option>@endforeach
    </select>
    <a href="/admin/pendaftar/export?gelombang={{ $dipilih }}" class="btn btn-outline-light spacer"><i class="fa-solid fa-file-csv" aria-hidden="true"></i> Ekspor CSV</a>
</div>

<div class="tbl-wrap">
    <table class="tbl" id="tbl">
        <thead>
            <tr><th>Calon siswa</th><th>Kode</th><th>Berkas wajib</th><th>Tgl daftar</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
            @forelse ($list as $r)
                <tr data-nama="{{ strtolower($r['nama'].' '.$r['kode'].' '.$r['wali']) }}" data-status="{{ $r['status'] }}">
                    <td class="nm"><b>{{ $r['nama'] }}</b><small>Wali: {{ $r['wali'] }}</small></td>
                    <td><code>{{ $r['kode'] }}</code></td>
                    <td>{{ $r['berkas'] }}/{{ $r['wajib'] }}</td>
                    <td>{{ $r['tgl'] }}</td>
                    <td><span class="st {{ $status[$r['status']][1] }}">{{ $status[$r['status']][0] }}</span></td>
                    <td class="acts">
                        <button class="btn btn-sm btn-outline-light" data-open="detail" data-fill="1"
                            data-id="{{ $r['id'] }}" data-nama="{{ $r['nama'] }}" data-kode="{{ $r['kode'] }}"
                            data-status="{{ $r['status'] }}" data-catatan="{{ $r['catatan'] }}"
                            data-siswa='@json($r['siswa'])' data-ortu='@json($r['ortu'])' data-docs='@json($r['docs'])'>
                            <i class="fa-regular fa-eye" aria-hidden="true"></i> Periksa
                        </button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:var(--gray-500);padding:28px">Belum ada pendaftar yang mengirim formulir pada gelombang aktif.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<p id="empty" class="empty" style="display:none"><span style="color:var(--gray-500)">Tidak ada pendaftar yang cocok dengan filter.</span></p>

{{-- Modal detail & verifikasi --}}
@if ($authUser->isKepsek())
{{-- Kepala sekolah hanya membaca: tombol aksi disembunyikan (route-nya pun ditolak server). --}}
<style>
    #detail .modal-foot .btn:not(#b-tutup), #detail .aksi-admin, #detail #m-lock { display: none !important; }
</style>
@endif
<div class="modal" id="detail" role="dialog" aria-modal="true" aria-labelledby="m-title">
    <div class="modal-box">
        <form id="vf" method="POST" data-once>
            @csrf
            <input type="hidden" name="aksi" id="aksi" value="">
            <div class="modal-head">
                <div>
                    <h3 id="m-title">—</h3>
                    <div style="font-size:.85rem;color:var(--gray-500)"><code id="m-kode"></code> · <span id="m-status"></span></div>
                </div>
                <button type="button" class="modal-x" data-close aria-label="Tutup"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-section">Calon siswa</div>
                <dl class="kv" id="m-siswa" style="margin-bottom:22px"></dl>

                <div class="form-section">Orang tua / wali</div>
                <dl class="kv" id="m-ortu" style="margin-bottom:22px"></dl>

                <div class="form-section">Berkas</div>
                <div id="m-docs"></div>

                <div id="m-note" style="display:none;margin-top:20px">
                    <p style="font-size:.84rem;color:var(--gray-500);margin-bottom:10px">Centang berkas yang perlu diunggah ulang, lalu tulis catatannya.</p>
                    <div class="field" style="margin:0">
                        <label for="catatan">Catatan perbaikan <span class="req">*</span></label>
                        <textarea class="form-control" id="catatan" name="catatan" placeholder="Jelaskan bagian yang perlu diperbaiki pendaftar…"></textarea>
                        <div class="field-error" id="catatan-err" style="display:none"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> Catatan wajib diisi.</div>
                    </div>
                </div>
                <div class="aksi-admin">
                <div class="form-section" style="margin-top:24px">Akun wali</div>
                <p style="font-size:.86rem;color:var(--gray-500);margin-bottom:10px">Wali lupa kata sandi? Buat kata sandi sementara, lalu sampaikan lewat WhatsApp. Kata sandi lama langsung tidak berlaku.</p>
                <button type="submit" form="rs" class="btn btn-sm btn-outline-light" onclick="return confirm('Reset kata sandi akun wali ini? Kata sandi lama tidak akan berlaku lagi.')"><i class="fa-solid fa-key" aria-hidden="true"></i> Reset kata sandi wali</button>
                </div>
                <p id="m-lock" style="display:none;margin-top:16px;font-size:.88rem;color:var(--gray-500)"><i class="fa-solid fa-lock" aria-hidden="true"></i> Pendaftar ini tidak sedang menunggu verifikasi, jadi tidak dapat diperiksa lagi.</p>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-white" data-close id="b-tutup">Tutup</button>
                <button type="button" class="btn btn-white" id="b-batal" style="display:none">Batal</button>
                <button type="button" class="btn btn-outline-light" id="b-fix"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Minta Perbaikan</button>
                <button type="button" class="btn btn-primary" id="b-ok"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Setujui &amp; Verifikasi</button>
            </div>
        </form>
        <form id="rs" method="POST" style="display:none">@csrf</form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var STATUS = @json($status);
    var form = document.getElementById('vf');

    // Filter
    var q = document.getElementById('q'), fs = document.getElementById('fs');
    function apply() {
        var n = 0;
        document.querySelectorAll('#tbl tbody tr[data-nama]').forEach(function (tr) {
            var ok = (!q.value || tr.dataset.nama.indexOf(q.value.toLowerCase()) > -1)
                && (!fs.value || tr.dataset.status === fs.value);
            tr.style.display = ok ? '' : 'none';
            if (ok) n++;
        });
        document.getElementById('empty').style.display = n ? 'none' : 'block';
    }
    [q, fs].forEach(function (el) { el.addEventListener('input', apply); });

    function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }

    // [label, isi] → baris <dt>/<dd>; isi kosong tampil "—"
    function pasangan(rows) {
        return rows.map(function (r) { return '<dt>' + esc(r[0]) + '</dt><dd>' + (r[1] ? esc(r[1]) : '—') + '</dd>'; }).join('');
    }

    // status berkas → [label, kelas badge, ikon, warna ikon]
    var BERKAS = {
        kosong: ['Belum ada', 'st-gray', 'fa-file-circle-xmark', '#C53030'],
        menunggu: ['Menunggu diperiksa', 'st-gold', 'fa-file', 'var(--gray-500)'],
        valid: ['Valid', 'st-green', 'fa-file-circle-check', 'var(--primary)'],
        ditolak: ['Ditolak', 'st-red', 'fa-file-circle-xmark', '#C53030']
    };

    window.fillModal = function (m, btn) {
        var d = btn.dataset, docs = JSON.parse(d.docs || '[]');
        form.action = '/admin/pendaftar/' + d.id + '/verifikasi';
        document.getElementById('rs').action = '/admin/pendaftar/' + d.id + '/reset-sandi';
        document.getElementById('m-title').textContent = d.nama;
        document.getElementById('m-kode').textContent = d.kode;
        var st = document.getElementById('m-status');
        st.className = 'st ' + STATUS[d.status][1]; st.textContent = STATUS[d.status][0];
        document.getElementById('m-siswa').innerHTML = pasangan(JSON.parse(d.siswa || '[]'));
        document.getElementById('m-ortu').innerHTML = pasangan(JSON.parse(d.ortu || '[]'));

        var html = '';
        docs.forEach(function (doc) {
            var has = doc.id !== null, b = BERKAS[doc.status] || BERKAS.kosong;
            html += '<div class="doc-row"><span class="l"><i class="fa-solid ' + b[2] + '" style="color:' + b[3] + '"></i>'
                + esc(doc.nama) + (doc.wajib ? '' : ' <small style="color:var(--gray-500)">(opsional)</small>')
                + (doc.status === 'ditolak' && doc.catatan ? ' <small style="color:#C53030">· ' + esc(doc.catatan) + '</small>' : '')
                + '</span><span style="display:flex;gap:8px;align-items:center"><span class="st ' + b[1] + '">' + b[0] + '</span>'
                + (has ? '<label class="fix-pick" style="display:none;font-size:.8rem;gap:6px;align-items:center;cursor:pointer"><input type="checkbox" name="berkas_ditolak[]" value="' + doc.id + '"> Unggah ulang</label>'
                    + '<a class="btn btn-sm btn-white" target="_blank" rel="noopener" href="/berkas/' + doc.id + '"><i class="fa-regular fa-eye" aria-hidden="true"></i> Lihat</a>'
                    : '') + '</span></div>';
        });
        document.getElementById('m-docs').innerHTML = html;

        var bisa = d.status === 'menunggu';
        document.getElementById('catatan').value = '';
        modePerbaikan(false);
        document.getElementById('m-lock').style.display = bisa ? 'none' : 'block';
        document.getElementById('b-ok').disabled = ! bisa;
        document.getElementById('b-fix').disabled = ! bisa;
    };

    document.getElementById('b-ok').addEventListener('click', function () {
        document.getElementById('aksi').value = 'setujui';
        form.submit();
    });
    // Mode "Minta Perbaikan": klik pertama membuka kolom catatan dan mengubah tombol jadi "Kirim Perbaikan";
    // Setujui disembunyikan agar tidak tertukar, "Batal" kembali ke tampilan semula.
    var tombolFix = document.getElementById('b-fix');
    function modePerbaikan(on) {
        document.getElementById('m-note').style.display = on ? 'block' : 'none';
        document.querySelectorAll('.fix-pick').forEach(function (l) { l.style.display = on ? 'inline-flex' : 'none'; });
        document.getElementById('b-ok').style.display = on ? 'none' : '';
        document.getElementById('b-tutup').style.display = on ? 'none' : '';
        document.getElementById('b-batal').style.display = on ? '' : 'none';
        tombolFix.classList.toggle('btn-primary', on);
        tombolFix.classList.toggle('btn-outline-light', !on);
        tombolFix.innerHTML = on ? '<i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Kirim Perbaikan' : '<i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Minta Perbaikan';
        document.getElementById('catatan-err').style.display = 'none';
    }
    document.getElementById('b-batal').addEventListener('click', function () { modePerbaikan(false); });
    tombolFix.addEventListener('click', function () {
        var box = document.getElementById('m-note'), ta = document.getElementById('catatan');
        if (box.style.display === 'none') {
            modePerbaikan(true);
            ta.focus();
            return;
        }
        if (!ta.value.trim()) { document.getElementById('catatan-err').style.display = 'flex'; ta.focus(); return; }
        document.getElementById('aksi').value = 'perbaikan';
        form.submit();
    });
})();
</script>
@endpush
