@extends('layouts.admin')

@section('title', 'Galeri')
@section('crumb', 'Galeri')

@section('content')
@php $editId = old('_id'); @endphp

<div class="page-title" style="display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap">
    <div>
        <h1>Galeri</h1>
        <p>Foto kegiatan di beranda. Foto yang bertanda <b>Hero</b> juga dipakai sebagai latar bergantian di bagian atas beranda.</p>
    </div>
    <button class="btn btn-primary" data-open="gform" data-mode="baru"><i class="fa-solid fa-plus"></i> Tambah Foto</button>
</div>

<div class="tbl-wrap">
    <table class="tbl">
        <thead><tr><th style="width:70px">Urutan</th><th style="width:96px">Foto</th><th>Judul</th><th>Unit</th><th>Hero</th><th></th></tr></thead>
        <tbody>
            @forelse ($list as $g)
                <tr>
                    <td>{{ $g['urutan'] }}</td>
                    <td><img class="thumb" src="{{ asset($g['gambar']) }}" alt="{{ $g['keterangan_gambar'] ?: $g['judul'] }}" loading="lazy" style="object-position: {{ $g['fx'] }}% {{ $g['fy'] }}%"></td>
                    <td class="nm"><b>{{ $g['judul'] }}</b><small>{{ $g['keterangan_gambar'] }}</small></td>
                    <td>{{ $g['unit'] ?: 'Semua' }}</td>
                    <td>
                        @if ($g['untuk_hero'])<span class="st st-green">Hero</span>
                        @elseif (! $g['layak_hero'])<span class="st st-gray" title="Bukan landscape atau lebar kurang dari 1280 px">Tidak layak hero</span>
                        @else<span class="st st-gray">Galeri saja</span>@endif
                    </td>
                    <td class="acts">
                        <button class="btn btn-sm btn-outline-light" data-open="gform" data-mode="ubah" data-row='@json($g)'><i class="fa-solid fa-pen"></i> Ubah</button>
                        <form action="/admin/konten/galeri/{{ $g['id'] }}" method="POST" style="display:inline" onsubmit="return confirm('Hapus foto ini dari galeri?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i> Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">Belum ada foto. Klik "Tambah Foto".</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="card" style="margin-top:20px">
    <h3>Panduan foto</h3>
    <p class="sub" style="margin:0">Format JPG, PNG, atau WebP, maksimal 5 MB. Untuk latar hero gunakan foto <b>landscape</b> dengan lebar minimal 1280 px (disarankan 1600 px); foto yang lebih kecil atau berdiri (potret) tetap bisa tampil di galeri tetapi otomatis tidak dipakai sebagai latar hero agar tidak pecah. Pakai hanya foto kegiatan SMP Islam Sirojul Athfal.</p>
</div>

<div class="modal {{ $errors->any() ? 'open' : '' }}" id="gform" role="dialog" aria-modal="true" aria-labelledby="g-title">
    <div class="modal-box narrow">
        <form method="POST" enctype="multipart/form-data" data-once
              data-store="/admin/konten/galeri" data-update="/admin/konten/galeri/:id"
              data-title-baru="Tambah Foto" data-title-ubah="Ubah Foto"
              action="{{ $editId ? '/admin/konten/galeri/'.$editId : '/admin/konten/galeri' }}">
            @csrf
            <input type="hidden" name="_method" value="{{ $editId ? 'PUT' : 'POST' }}">
            <input type="hidden" name="_id" value="{{ $editId }}">
            <div class="modal-head">
                <h3 id="g-title" data-title>{{ $editId ? 'Ubah Foto' : 'Tambah Foto' }}</h3>
                <button type="button" class="modal-x" data-close aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                @if ($errors->any())
                    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><span>@foreach ($errors->all() as $e){{ $e }}<br>@endforeach</span></div>
                @endif

                <img data-preview="gambar" data-base="{{ url('/') }}/" class="thumb-lg" alt="Foto saat ini" style="display:none">
                <div class="field">
                    <label for="g-foto">Foto <span class="req" data-hide-edit>*</span></label>
                    <input class="form-control" type="file" id="g-foto" name="foto" accept="image/jpeg,image/png,image/webp">
                    <div class="hint"><span data-show-edit style="display:none">Kosongkan bila tidak ingin mengganti foto. </span>JPG, PNG, atau WebP, maksimal 5 MB.</div>
                </div>
                <div class="field">
                    <label for="g-judul">Judul <span class="req">*</span></label>
                    <input class="form-control" id="g-judul" name="judul" value="{{ old('judul') }}" maxlength="120" required>
                </div>
                <div class="field">
                    <label for="g-unit">Unit</label>
                    <select class="form-control" id="g-unit" name="unit">
                        <option value="">Semua unit (MI dan SMP)</option>
                        <option value="MI" {{ old('unit') === 'MI' ? 'selected' : '' }}>MI</option>
                        <option value="SMP" {{ old('unit') === 'SMP' ? 'selected' : '' }}>SMP</option>
                    </select>
                    <div class="hint">Dipakai untuk filter MI / SMP pada galeri di beranda.</div>
                </div>
                <div class="field">
                    <label for="g-ket">Keterangan foto</label>
                    <input class="form-control" id="g-ket" name="keterangan_gambar" value="{{ old('keterangan_gambar') }}" maxlength="255" placeholder="Siswa mengerjakan soal di dalam ruang kelas">
                    <div class="hint">Satu kalimat yang menjelaskan isi foto, dibacakan untuk pengguna pembaca layar.</div>
                </div>
                <div class="form-grid">
                    <div class="field"><label for="g-fx">Fokus foto: kiri–kanan (%)</label><input class="form-control" type="number" min="0" max="100" id="g-fx" name="fx" value="{{ old('fx', 50) }}"></div>
                    <div class="field"><label for="g-fy">Fokus foto: atas–bawah (%)</label><input class="form-control" type="number" min="0" max="100" id="g-fy" name="fy" value="{{ old('fy', 50) }}"></div>
                    <div class="field full"><div class="hint" style="margin-top:-8px">Titik foto yang dijaga tetap terlihat saat dipotong. 50 dan 50 berarti tengah.</div></div>
                    <div class="field">
                        <label for="g-urutan">Urutan</label>
                        <input class="form-control" type="number" min="0" max="250" id="g-urutan" name="urutan" value="{{ old('urutan') }}" placeholder="otomatis">
                    </div>
                </div>
                <label class="check"><input type="checkbox" name="untuk_hero" value="1" {{ old('untuk_hero') ? 'checked' : '' }}> <span>Pakai juga sebagai latar hero beranda (hanya bila foto landscape dan lebar minimal 1280 px)</span></label>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-white" data-close>Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
