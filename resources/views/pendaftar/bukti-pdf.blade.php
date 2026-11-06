<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Pendaftaran {{ $p['kode'] }}</title>
    <style>
        @page { margin: 28px 40px 34px; }
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { font-size: 9.6px; color: #2d3a33; line-height: 1.4; }
        h1, h2, h3 { margin: 0; color: #1f2a24; }
        .head { border-bottom: 3px solid #0D6E3E; padding-bottom: 10px; margin-bottom: 16px; }
        .head table { width: 100%; }
        .brand { font-size: 15px; font-weight: bold; color: #0D6E3E; }
        .brand small { display: block; font-size: 8px; letter-spacing: 1.5px; color: #718096; font-weight: normal; }
        .addr { text-align: right; font-size: 8.5px; color: #718096; }
        .title { text-align: center; margin: 2px 0 8px; }
        .title h1 { font-size: 16px; letter-spacing: 1px; }
        .title p { margin: 2px 0 0; color: #718096; font-size: 9.5px; }
        .kode-box { border: 1.5px dashed #D29D29; background: #FBF8F1; text-align: center; padding: 7px; margin-bottom: 8px; }
        .kode-box small { font-size: 8.5px; letter-spacing: 2px; color: #718096; }
        .kode-box b { display: block; font-size: 22px; letter-spacing: 3px; color: #0A5A32; margin-top: 2px; }
        .sec { font-size: 9px; letter-spacing: 1.5px; text-transform: uppercase; color: #B88923; font-weight: bold; border-bottom: 1px solid #E4DDCB; padding-bottom: 3px; margin: 10px 0 4px; }
        table.kv { width: 100%; border-collapse: collapse; }
        table.kv td { padding: 2px 0; vertical-align: top; }
        table.kv td.k { width: 30%; color: #718096; }
        table.kv td.v { font-weight: bold; color: #1f2a24; }
        table.grid { width: 100%; border-collapse: collapse; }
        table.grid th { background: #0D6E3E; color: #fff; text-align: left; padding: 5px 7px; font-size: 9px; }
        table.grid td { border-bottom: 1px solid #E4DDCB; padding: 3px 7px; }
        .tag { font-weight: bold; }
        .ok { color: #0A5A32; } .wait { color: #8A6A14; } .bad { color: #B24A12; } .none { color: #718096; }
        .note { background: #FBF3DE; border-left: 3px solid #D29D29; padding: 6px 9px; margin-top: 10px; font-size: 9px; }
        .sign { margin-top: 10px; width: 100%; }
        .sign td { width: 50%; vertical-align: top; font-size: 9.5px; }
        .foot { position: fixed; bottom: 0; left: 0; right: 0; border-top: 1px solid #E4DDCB; padding-top: 5px; font-size: 8px; color: #718096; text-align: center; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tegas.css') }}">
</head>
<body>
    <div class="head">
        <table>
            <tr>
                <td style="width: 52px;"><img src="{{ public_path('images/logo/logo-cetak.jpg') }}" alt="Logo" width="46" height="46"></td>
                <td>
                    <div class="brand">{{ config('sekolah.unit.'.$p['jenjang'], config('sekolah.nama')) }}<small>{{ config('sekolah.nama_yayasan') }} · KABUPATEN BOGOR</small></div>
                </td>
                <td class="addr">Jl. KH. Muh Sanusi Kp. Lio Masjid RT 04/03<br>Desa Kedung Waringin Kecamatan Bojong Gede<br>Kabupaten Bogor Provinsi Jawa Barat</td>
            </tr>
        </table>
    </div>

    <div class="title">
        <h1>BUKTI PENDAFTARAN {{ config('sekolah.spmb') }}</h1>
        <p>{{ $g['nama'] }} · Tahun Ajaran {{ $g['tahun_ajaran'] }}</p>
    </div>

    <div class="kode-box">
        <small>KODE PENDAFTARAN</small>
        <b>{{ $p['kode'] }}</b>
    </div>

    <div class="sec">Informasi pendaftaran</div>
    <table class="kv">
        <tr><td class="k">Gelombang</td><td class="v">{{ $p['gelombang'] }}</td></tr>
        <tr><td class="k">Tanggal formulir dikirim</td><td class="v">{{ $p['tgl_kirim'] }}</td></tr>
        <tr><td class="k">Status saat dicetak</td><td class="v">{{ $status[$p['status']][0] }}</td></tr>
        <tr><td class="k">Pengumuman hasil</td><td class="v">{{ $g['pengumuman'] }}</td></tr>
        <tr><td class="k">Daftar ulang</td><td class="v">{{ $g['daftar_ulang'] }}</td></tr>
    </table>

    <div class="sec">Data calon siswa</div>
    <table class="kv">
        <tr><td class="k">Nama lengkap</td><td class="v">{{ $sw['nama'] }}</td></tr>
        <tr><td class="k">NIK</td><td class="v">{{ $sw['nik'] }}</td></tr>
        <tr><td class="k">Jenis kelamin</td><td class="v">{{ $sw['jk'] === 'L' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
        <tr><td class="k">Tempat, tanggal lahir</td><td class="v">{{ $sw['tempat_lahir'] }}, {{ $tglLahir }}</td></tr>
        <tr><td class="k">Asal sekolah</td><td class="v">{{ $sw['asal_sekolah'] ?: '-' }}</td></tr>
        <tr><td class="k">Alamat</td><td class="v">{{ $sw['alamat'] }}</td></tr>
    </table>

    <div class="sec">Data orang tua / wali</div>
    <table class="kv">
        <tr><td class="k">Nama ayah</td><td class="v">{{ $ot['ayah'] }}</td></tr>
        <tr><td class="k">Nama ibu</td><td class="v">{{ $ot['ibu'] }}</td></tr>
        <tr><td class="k">Pekerjaan</td><td class="v">{{ $ot['pekerjaan'] ?: '-' }}</td></tr>
        <tr><td class="k">No. HP / WhatsApp</td><td class="v">{{ $ot['hp'] }}</td></tr>
        <tr><td class="k">Alamat</td><td class="v">{{ $ot['alamat'] ?: '-' }}</td></tr>
    </table>

    <div class="sec">Berkas persyaratan</div>
    <table class="grid">
        <tr><th>Berkas</th><th>Kewajiban</th><th>Nama file</th><th>Status</th></tr>
        @foreach ($jenisBerkas as $j)
            @php
                $b = $p['berkas'][$j['key']] ?? ['status' => 'kosong', 'file' => null];
                $map = ['valid' => ['Valid', 'ok'], 'menunggu' => ['Menunggu diperiksa', 'wait'], 'ditolak' => ['Perlu diunggah ulang', 'bad'], 'kosong' => ['Belum diunggah', 'none']];
            @endphp
            <tr>
                <td>{{ $j['nama'] }}</td>
                <td>{{ $j['wajib'] ? 'Wajib' : 'Opsional' }}</td>
                <td>{{ $b['file'] ?? '-' }}</td>
                <td class="tag {{ $map[$b['status']][1] }}">{{ $map[$b['status']][0] }}</td>
            </tr>
        @endforeach
    </table>

    <div class="note">
        <b>Penting:</b> dokumen ini adalah bukti bahwa formulir pendaftaran telah diterima sistem dan <b>bukan</b> bukti kelulusan.
        Simpan kode pendaftaran untuk memeriksa status dan hasil seleksi di website. Status pada dokumen ini sesuai waktu pencetakan.
    </div>

    <table class="sign">
        <tr>
            <td></td>
            <td style="text-align:center">
                Bogor, {{ $dicetak }}<br>
                Panitia {{ config('sekolah.spmb') }} {{ config('sekolah.unit.'.$p['jenjang'], config('sekolah.nama')) }}<br><br><br>
                <span style="color:#718096;font-size:8.5px">(dokumen dibuat otomatis oleh sistem)</span>
            </td>
        </tr>
    </table>

    <div class="foot">Dicetak dari portal {{ config('sekolah.spmb') }} {{ config('sekolah.unit.'.$p['jenjang'], config('sekolah.nama')) }} · {{ $p['kode'] }} · {{ $dicetak }}</div>
</body>
</html>
