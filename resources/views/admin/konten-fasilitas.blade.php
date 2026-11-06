@extends('layouts.admin')

@section('title', 'Fasilitas')
@section('crumb', 'Fasilitas')

@section('content')
@php $editId = old('_id'); @endphp

<div class="page-title" style="display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap">
    <div>
        <h1>Fasilitas</h1>
        <p>Daftar sarana dan prasarana di halaman Fasilitas. Urutan kecil tampil lebih dulu.</p>
    </div>
    <button class="btn btn-primary" data-open="fform" data-mode="baru"><i class="fa-solid fa-plus" aria-hidden="true"></i> Tambah Fasilitas</button>
</div>

<div class="tbl-wrap">
    <table class="tbl">
        <thead><tr><th style="width:70px">Urutan</th><th style="width:60px">Ikon</th><th>Nama fasilitas</th><th></th></tr></thead>
        <tbody>
            @forelse ($list as $f)
                @php $row = ['id' => $f->id, 'nama' => $f->nama, 'ikon' => $f->ikon, 'urutan' => $f->urutan]; @endphp
                <tr>
                    <td>{{ $f->urutan }}</td>
                    <td><span class="cell-ico"><i class="fa-solid {{ $f->ikon ?: 'fa-building' }}" aria-hidden="true"></i></span></td>
                    <td class="nm"><b>{{ $f->nama }}</b></td>
                    <td class="acts">
                        <button class="btn btn-sm btn-outline-light" data-open="fform" data-mode="ubah" data-row='@json($row)'><i class="fa-solid fa-pen" aria-hidden="true"></i> Ubah</button>
                        <form action="/admin/konten/fasilitas/{{ $f->id }}" method="POST" style="display:inline" onsubmit="return confirm('Hapus fasilitas ini dari situs?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash" aria-hidden="true"></i> Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">Belum ada fasilitas. Klik "Tambah Fasilitas".</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal {{ $errors->any() ? 'open' : '' }}" id="fform" role="dialog" aria-modal="true" aria-labelledby="f-title">
    <div class="modal-box narrow">
        <form method="POST" data-once
              data-store="/admin/konten/fasilitas" data-update="/admin/konten/fasilitas/:id"
              data-title-baru="Tambah Fasilitas" data-title-ubah="Ubah Fasilitas"
              action="{{ $editId ? '/admin/konten/fasilitas/'.$editId : '/admin/konten/fasilitas' }}">
            @csrf
            <input type="hidden" name="_method" value="{{ $editId ? 'PUT' : 'POST' }}">
            <input type="hidden" name="_id" value="{{ $editId }}">
            <div class="modal-head">
                <h3 id="f-title" data-title>{{ $editId ? 'Ubah Fasilitas' : 'Tambah Fasilitas' }}</h3>
                <button type="button" class="modal-x" data-close aria-label="Tutup"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            </div>
            <div class="modal-body">
                @if ($errors->any())
                    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span>@foreach ($errors->all() as $e){{ $e }}<br>@endforeach</span></div>
                @endif
                <div class="field">
                    <label for="f-nama">Nama fasilitas <span class="req">*</span></label>
                    <input class="form-control" id="f-nama" name="nama" value="{{ old('nama') }}" maxlength="100" required>
                </div>
                <div class="form-grid">
                    <div class="field">
                        <label for="f-ikon">Ikon <span class="req">*</span></label>
                        <select class="form-control" id="f-ikon" name="ikon" required>
                            @foreach ($ikon as $kelas => $label)
                                <option value="{{ $kelas }}" {{ old('ikon', 'fa-building') === $kelas ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="f-urutan">Urutan</label>
                        <input class="form-control" type="number" min="0" max="250" id="f-urutan" name="urutan" value="{{ old('urutan') }}" placeholder="otomatis">
                        <div class="hint">Kosongkan untuk menaruh di paling akhir.</div>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-white" data-close>Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
