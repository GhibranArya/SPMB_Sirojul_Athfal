@extends('layouts.admin')

@section('title', 'Gelombang & Kuota')
@section('crumb', 'Gelombang & Kuota')

@section('content')
@php
    $tgl = fn ($d) => $d ? \Carbon\Carbon::parse($d)->locale('id')->translatedFormat('j M Y') : '-';
    $editId = old('_id');
    $bukaModal = $errors->any();
@endphp

<div class="page-title" style="display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap">
    <div>
        <h1>Gelombang &amp; Kuota</h1>
        <p>Atur periode pendaftaran dan kuota per tahun ajaran.</p>
    </div>
    <button class="btn btn-primary" data-open="gform" data-fill="1" data-mode="baru"><i class="fa-solid fa-plus" aria-hidden="true"></i> Tambah Gelombang</button>
</div>

<div class="tbl-wrap">
    <table class="tbl">
        <thead>
            <tr>
                <th>Gelombang</th><th>Periode</th><th>Pengumuman</th>
                <th>Kuota</th>
                <th class="num">Pendaftar</th><th>Status</th><th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($gelombang as $g)
                <tr>
                    <td class="nm"><b>{{ $g['nama'] }}</b><small>TA {{ $g['ta'] }}</small></td>
                    <td>{{ $tgl($g['buka']) }} – {{ $tgl($g['tutup']) }}</td>
                    <td>{{ $tgl($g['pengumuman']) }}</td>
                    <td>{{ implode(' / ', $g['kuota']) }}</td>
                    <td class="num">{{ $g['pendaftar'] }}</td>
                    <td>
                        @if ($g['dibuka'])<span class="st st-green">Dibuka</span>
                        @elseif ($g['aktif'] && $g['jadwal'])<span class="st st-gold">Dijadwalkan</span>
                        @elseif ($g['aktif'])<span class="st st-gray">Aktif · ditutup</span>
                        @else<span class="st st-gray">Tidak aktif</span>@endif
                    </td>
                    <td class="acts">
                        <button class="btn btn-sm btn-outline-light" data-open="gform" data-fill="1" data-mode="ubah" data-g='@json($g)'>
                            <i class="fa-solid fa-pen" aria-hidden="true"></i> Ubah
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="card" style="margin-top:20px">
    <h3>Aturan gelombang</h3>
    <p class="sub" style="margin:0">Formulir hanya bisa dikirim saat gelombang <b>aktif</b> dan masih dalam rentang tanggal buka–tutup. Hanya satu gelombang yang bisa aktif; mengaktifkan satu akan menonaktifkan yang lain. Kuota ditetapkan untuk setiap gelombang; jumlah <i>Diterima</i> melebihi kuota akan diberi peringatan di halaman Hasil Seleksi.</p>
</div>

<div class="modal {{ $bukaModal ? 'open' : '' }}" id="gform" role="dialog" aria-modal="true" aria-labelledby="g-title">
    <div class="modal-box">
        <form id="gf" method="POST" action="{{ $editId ? '/admin/gelombang/'.$editId : '/admin/gelombang' }}" data-once>
            @csrf
            <input type="hidden" name="_method" id="g-method" value="{{ $editId ? 'PUT' : 'POST' }}">
            <input type="hidden" name="_id" id="g-id" value="{{ $editId }}">
            <div class="modal-head">
                <h3 id="g-title">{{ $editId ? 'Ubah gelombang' : 'Tambah Gelombang' }}</h3>
                <button type="button" class="modal-x" data-close aria-label="Tutup"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            </div>
            <div class="modal-body">
                @if ($errors->any())
                    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                        <span>@foreach ($errors->all() as $e){{ $e }}<br>@endforeach</span>
                    </div>
                @endif
                <div class="form-grid">
                    <div class="field"><label for="g-nama">Nama gelombang</label><input class="form-control" id="g-nama" name="nama" value="{{ old('nama') }}" placeholder="Gelombang 2" required></div>
                    <div class="field"><label for="g-ta">Tahun ajaran</label><input class="form-control" id="g-ta" name="tahun_ajaran" value="{{ old('tahun_ajaran') }}" placeholder="2026/2027" required></div>
                    <div class="field"><label for="g-buka">Tanggal buka</label><input class="form-control" type="date" id="g-buka" name="tgl_buka" value="{{ old('tgl_buka') }}" required></div>
                    <div class="field"><label for="g-tutup">Tanggal tutup</label><input class="form-control" type="date" id="g-tutup" name="tgl_tutup" value="{{ old('tgl_tutup') }}" required></div>
                    <div class="field full"><label for="g-pengumuman">Tanggal pengumuman</label><input class="form-control" type="date" id="g-pengumuman" name="tgl_pengumuman" value="{{ old('tgl_pengumuman') }}" required></div>
                    <div class="field"><label for="g-um">Daftar ulang mulai</label><input class="form-control" type="date" id="g-um" name="daftar_ulang_mulai" value="{{ old('daftar_ulang_mulai') }}"></div>
                    <div class="field"><label for="g-us">Daftar ulang selesai</label><input class="form-control" type="date" id="g-us" name="daftar_ulang_selesai" value="{{ old('daftar_ulang_selesai') }}"></div>
                </div>
                <div class="form-section">Kuota</div>
                <div class="form-grid" style="grid-template-columns:1fr">
                    @foreach ($jenjang as $kode => $nama)
                        <div class="field"><label for="g-k-{{ $kode }}">Kuota murid baru {{ $nama }}</label><input class="form-control" type="number" min="0" id="g-k-{{ $kode }}" name="kuota[{{ $kode }}]" value="{{ old('kuota.'.$kode, 0) }}" required></div>
                    @endforeach
                </div>
                <div class="form-section">Rincian biaya</div>
                <p class="sub" style="margin:0 0 10px">Isi nama dan nominal tiap biaya (angka saja, tanpa titik). Centang <b>Gratis</b> bila tidak berbayar. Tampil di halaman biaya pendaftaran dan di formulir pendaftar.</p>
                @foreach ($jenjang as $kode => $nama)
                    <div class="biaya-ed" id="g-b-{{ $kode }}" data-kode="{{ $kode }}" data-rows='@json(old('biaya.'.$kode, []))'>
                        <input type="hidden" name="biaya_ada[{{ $kode }}]" value="1">
                        <div class="biaya-ed-head">Biaya {{ $nama }}</div>
                        <div class="biaya-rows"></div>
                        <button type="button" class="btn btn-sm btn-outline-light biaya-add"><i class="fa-solid fa-plus" aria-hidden="true"></i> Tambah biaya</button>
                    </div>
                @endforeach
                <label class="check"><input type="checkbox" name="is_aktif" id="g-aktif" value="1" {{ old('is_aktif') ? 'checked' : '' }}> <span>Jadikan gelombang aktif (menonaktifkan gelombang lain)</span></label>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-white" data-close>Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.fillModal = function (m, btn) {
        var baru = btn.dataset.mode === 'baru', f = document.getElementById('gf');
        var g = baru ? null : JSON.parse(btn.dataset.g);
        document.getElementById('g-title').textContent = baru ? 'Tambah Gelombang' : 'Ubah ' + g.nama + ' · ' + g.ta;
        f.action = baru ? '/admin/gelombang' : '/admin/gelombang/' + g.id;
        document.getElementById('g-method').value = baru ? 'POST' : 'PUT';
        document.getElementById('g-id').value = baru ? '' : g.id;
        var set = function (id, v) { document.getElementById(id).value = v == null ? '' : v; };
        set('g-nama', g && g.nama); set('g-ta', g && g.ta); set('g-buka', g && g.buka); set('g-tutup', g && g.tutup);
        set('g-pengumuman', g && g.pengumuman); set('g-um', g && g.ulang_mulai); set('g-us', g && g.ulang_selesai);
        @foreach ($jenjang as $kode => $nama)
        set('g-k-{{ $kode }}', g ? g.kuota['{{ $kode }}'] : 0);
        isiBiaya(document.getElementById('g-b-{{ $kode }}'), g ? g.biaya['{{ $kode }}'] : [{}]);
        @endforeach
        document.getElementById('g-aktif').checked = !!(g && g.aktif);
        m.querySelectorAll('.alert').forEach(function (a) { a.remove(); });
    };

    // Editor rincian biaya: satu baris = nama, nominal (angka), centang Gratis, tombol hapus.
    function buatBaris(ed, r) {
        var n = ed._n = (ed._n || 0) + 1, p = 'biaya[' + ed.dataset.kode + '][' + n + ']';
        var el = function (tag, props) { return Object.assign(document.createElement(tag), props || {}); };

        var baris = el('div', { className: 'biaya-row' });
        var nama = el('input', { className: 'form-control', name: p + '[nama]', maxLength: 100, placeholder: 'Nama biaya, mis. Baju Batik', value: r.nama || '' });
        nama.setAttribute('aria-label', 'Nama biaya');

        var rp = el('div', { className: 'biaya-rp' });
        var nominal = el('input', { className: 'form-control', type: 'number', name: p + '[nominal]', min: 1, step: 1, value: r.nominal == null ? '' : r.nominal });
        nominal.setAttribute('aria-label', 'Nominal biaya dalam rupiah');
        nominal.setAttribute('inputmode', 'numeric');
        rp.appendChild(el('span', { textContent: 'Rp' }));
        rp.appendChild(nominal);

        var lab = el('label', { className: 'check biaya-gratis' });
        var gratis = el('input', { type: 'checkbox', name: p + '[gratis]', value: '1', checked: !!r.gratis });
        lab.appendChild(gratis);
        lab.appendChild(el('span', { textContent: 'Gratis' }));
        var sinkron = function () { nominal.disabled = gratis.checked; if (gratis.checked) nominal.value = ''; };
        gratis.addEventListener('change', sinkron);
        sinkron();

        var hapus = el('button', { type: 'button', className: 'biaya-del' });
        hapus.setAttribute('aria-label', 'Hapus biaya ini');
        hapus.innerHTML = '<i class="fa-solid fa-trash" aria-hidden="true"></i>';

        [nama, rp, lab, hapus].forEach(function (x) { baris.appendChild(x); });
        ed.querySelector('.biaya-rows').appendChild(baris);
        return nama;
    }

    function isiBiaya(ed, baris) {
        ed.querySelector('.biaya-rows').textContent = '';
        ed._n = 0;
        (Array.isArray(baris) ? baris : Object.values(baris || {})).forEach(function (r) { buatBaris(ed, r); });
    }

    document.querySelectorAll('.biaya-ed').forEach(function (ed) { isiBiaya(ed, JSON.parse(ed.dataset.rows || '[]')); });
    document.getElementById('gf').addEventListener('click', function (e) {
        var tambah = e.target.closest('.biaya-add'), hapus = e.target.closest('.biaya-del');
        if (tambah) buatBaris(tambah.closest('.biaya-ed'), {}).focus();
        if (hapus) hapus.closest('.biaya-row').remove();
    });
</script>
@endpush
