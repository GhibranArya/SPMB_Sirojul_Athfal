@extends('layouts.admin')

@section('title', 'Ekstrakurikuler')
@section('crumb', 'Ekstrakurikuler')

@section('content')
@php $editId = old('_id'); @endphp

<div class="page-title" style="display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap">
    <div>
        <h1>Ekstrakurikuler</h1>
        <p>Kegiatan yang tampil di carousel beranda, halaman Unit Pendidikan, dan footer. Urutan kecil tampil lebih dulu.</p>
    </div>
    <button class="btn btn-primary" data-open="eform" data-mode="baru"><i class="fa-solid fa-plus"></i> Tambah Ekstrakurikuler</button>
</div>

<div class="tbl-wrap">
    <table class="tbl">
        <thead><tr><th style="width:70px">Urutan</th><th style="width:96px">Foto</th><th>Kegiatan</th><th>Waktu</th><th>Pembimbing</th><th></th></tr></thead>
        <tbody>
            @forelse ($list as $e)
                <tr>
                    <td>{{ $e['urutan'] }}</td>
                    <td>
                        @if ($e['foto_pilih'])
                            <img class="thumb" src="{{ asset('images/foto/'.$e['foto_pilih']) }}" alt="" loading="lazy" style="object-position: {{ $e['fx'] }}% {{ $e['fy'] }}%">
                        @else
                            <span class="thumb thumb-ico"><i class="fa-solid {{ $e['ikon'] }}" aria-hidden="true"></i></span>
                        @endif
                    </td>
                    <td class="nm"><b>{{ $e['nama'] }}</b><small>{{ $ikon[$e['ikon']] ?? $e['ikon'] }}</small></td>
                    <td>{{ $e['waktu'] ?: '-' }}</td>
                    <td>{{ $e['pembimbing'] ?: '-' }}</td>
                    <td class="acts">
                        <button class="btn btn-sm btn-outline-light" data-open="eform" data-mode="ubah" data-row='@json($e)'><i class="fa-solid fa-pen"></i> Ubah</button>
                        <form action="/admin/konten/ekstrakurikuler/{{ $e['id'] }}" method="POST" style="display:inline" onsubmit="return confirm('Hapus ekstrakurikuler ini dari situs?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i> Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">Belum ada ekstrakurikuler. Bagian ini disembunyikan dari situs sampai ada satu.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal {{ $errors->any() ? 'open' : '' }}" id="eform" role="dialog" aria-modal="true" aria-labelledby="e-title">
    <div class="modal-box">
        <form method="POST" enctype="multipart/form-data" data-once
              data-store="/admin/konten/ekstrakurikuler" data-update="/admin/konten/ekstrakurikuler/:id"
              data-title-baru="Tambah Ekstrakurikuler" data-title-ubah="Ubah Ekstrakurikuler"
              action="{{ $editId ? '/admin/konten/ekstrakurikuler/'.$editId : '/admin/konten/ekstrakurikuler' }}">
            @csrf
            <input type="hidden" name="_method" value="{{ $editId ? 'PUT' : 'POST' }}">
            <input type="hidden" name="_id" value="{{ $editId }}">
            <div class="modal-head">
                <h3 id="e-title" data-title>{{ $editId ? 'Ubah Ekstrakurikuler' : 'Tambah Ekstrakurikuler' }}</h3>
                <button type="button" class="modal-x" data-close aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                @if ($errors->any())
                    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><span>@foreach ($errors->all() as $e){{ $e }}<br>@endforeach</span></div>
                @endif

                <div class="form-section">Data kegiatan</div>
                <div class="form-grid">
                    <div class="field">
                        <label for="e-nama">Nama kegiatan <span class="req">*</span></label>
                        <input class="form-control" id="e-nama" name="nama" value="{{ old('nama') }}" maxlength="80" required>
                    </div>
                    <div class="field">
                        <label for="e-ikon">Ikon <span class="req">*</span></label>
                        <select class="form-control" id="e-ikon" name="ikon" required>
                            @foreach ($ikon as $kelas => $label)
                                <option value="{{ $kelas }}" {{ old('ikon', 'fa-star') === $kelas ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="hint">Dipakai bila kegiatan belum punya foto.</div>
                    </div>
                    <div class="field">
                        <label for="e-waktu">Waktu kegiatan</label>
                        <input class="form-control" id="e-waktu" name="waktu" value="{{ old('waktu') }}" maxlength="60" placeholder="Hari Jumat">
                    </div>
                    <div class="field">
                        <label for="e-pembimbing">Pembimbing</label>
                        <input class="form-control" id="e-pembimbing" name="pembimbing" value="{{ old('pembimbing') }}" maxlength="120">
                    </div>
                    <div class="field">
                        <label for="e-urutan">Urutan</label>
                        <input class="form-control" type="number" min="0" max="250" id="e-urutan" name="urutan" value="{{ old('urutan') }}" placeholder="otomatis">
                    </div>
                </div>

                <div class="form-section">Isi kartu</div>
                <div class="field">
                    <label for="e-tujuan">Tujuan</label>
                    <textarea class="form-control" id="e-tujuan" name="tujuan" rows="4">{{ old('tujuan') }}</textarea>
                    <div class="hint">Satu butir per baris. Satu butir tampil sebagai paragraf, lebih dari satu sebagai daftar.</div>
                </div>
                <div class="field">
                    <label for="e-target">Target pencapaian</label>
                    <textarea class="form-control" id="e-target" name="target" rows="4">{{ old('target') }}</textarea>
                    <div class="hint">Satu butir per baris.</div>
                </div>
                <div class="field">
                    <label for="e-profil">Profil lulusan</label>
                    <textarea class="form-control" id="e-profil" name="profil" rows="2">{{ old('profil') }}</textarea>
                    <div class="hint">Satu label per baris, mis. Kemandirian, Kolaborasi.</div>
                </div>

                <div class="form-section">Foto</div>
                <img data-preview="foto_pilih" data-base="{{ url('/images/foto') }}/" class="thumb-lg" alt="Foto saat ini" style="display:none">
                <div class="form-grid">
                    <div class="field">
                        <label for="e-foto-pilih">Pilih dari foto yang sudah ada</label>
                        <select class="form-control" id="e-foto-pilih" name="foto_pilih">
                            <option value="">Tanpa foto (pakai ikon)</option>
                            @foreach ($fotoTersedia as $f)
                                <option value="{{ $f }}" {{ old('foto_pilih') === $f ? 'selected' : '' }}>{{ $f }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="e-foto">Atau unggah foto baru</label>
                        <input class="form-control" type="file" id="e-foto" name="foto" accept="image/jpeg,image/png,image/webp">
                        <div class="hint">Foto unggahan menggantikan pilihan di samping. JPG, PNG, atau WebP, maksimal 5 MB.</div>
                    </div>
                    <div class="field"><label for="e-fx">Fokus foto: kiri–kanan (%)</label><input class="form-control" type="number" min="0" max="100" id="e-fx" name="fx" value="{{ old('fx', 50) }}"></div>
                    <div class="field"><label for="e-fy">Fokus foto: atas–bawah (%)</label><input class="form-control" type="number" min="0" max="100" id="e-fy" name="fy" value="{{ old('fy', 50) }}"></div>
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
