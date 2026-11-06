<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Diterima {{ $p['kode'] }}</title>
    <style>
        @page { margin: 28px 40px 34px; }
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { font-size: 10px; color: #2d3a33; line-height: 1.5; }
        h1, h2, h3 { margin: 0; color: #1f2a24; }
        .head { border-bottom: 3px solid #0D6E3E; padding-bottom: 10px; margin-bottom: 18px; }
        .head table { width: 100%; }
        .brand { font-size: 15px; font-weight: bold; color: #0D6E3E; }
        .brand small { display: block; font-size: 8px; letter-spacing: 1.5px; color: #718096; font-weight: normal; }
        .addr { text-align: right; font-size: 8.5px; color: #718096; }
        .title { text-align: center; margin: 2px 0 10px; }
        .title h1 { font-size: 16px; letter-spacing: 1px; }
        .title p { margin: 2px 0 0; color: #718096; font-size: 9.5px; }
        .hasil { border: 2px solid #0D6E3E; background: #EAF6EF; text-align: center; padding: 12px 10px; margin-bottom: 10px; }
        .hasil small { font-size: 8.5px; letter-spacing: 2px; color: #4A5568; }
        .hasil b { display: block; font-size: 24px; letter-spacing: 3px; color: #0A5A32; margin: 3px 0; }
        .hasil span { font-size: 11px; color: #1f2a24; font-weight: bold; }
        .kode-box { border: 1.5px dashed #D29D29; background: #FBF8F1; text-align: center; padding: 6px; margin-bottom: 8px; }
        .kode-box small { font-size: 8.5px; letter-spacing: 2px; color: #718096; }
        .kode-box b { display: block; font-size: 18px; letter-spacing: 3px; color: #0A5A32; margin-top: 2px; }
        .sec { font-size: 9px; letter-spacing: 1.5px; text-transform: uppercase; color: #B88923; font-weight: bold; border-bottom: 1px solid #E4DDCB; padding-bottom: 3px; margin: 12px 0 5px; }
        table.kv { width: 100%; border-collapse: collapse; }
        table.kv td { padding: 2px 0; vertical-align: top; }
        table.kv td.k { width: 30%; color: #718096; }
        table.kv td.v { font-weight: bold; color: #1f2a24; }
        ol { margin: 4px 0 0 16px; padding: 0; }
        ol li { margin-bottom: 4px; }
        .note { background: #FBF3DE; border-left: 3px solid #D29D29; padding: 6px 9px; margin-top: 12px; font-size: 9px; }
        .sign { margin-top: 14px; width: 100%; }
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
                <td class="addr">{{ $alamat }}</td>
            </tr>
        </table>
    </div>

    <div class="title">
        <h1>BUKTI DITERIMA {{ config('sekolah.spmb') }}</h1>
        <p>{{ $g['nama'] }} · Tahun Ajaran {{ $g['tahun_ajaran'] }}</p>
    </div>

    <div class="hasil">
        <small>HASIL SELEKSI</small>
        <b>DITERIMA</b>
        <span>{{ $sw['nama'] }}</span>
    </div>

    <div class="kode-box">
        <small>KODE PENDAFTARAN</small>
        <b>{{ $p['kode'] }}</b>
    </div>

    <div class="sec">Data calon siswa</div>
    <table class="kv">
        <tr><td class="k">Nama lengkap</td><td class="v">{{ $sw['nama'] }}</td></tr>
        <tr><td class="k">Jenis kelamin</td><td class="v">{{ $sw['jk'] === 'L' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
        <tr><td class="k">Tempat, tanggal lahir</td><td class="v">{{ $sw['tempat_lahir'] }}, {{ $tglLahir }}</td></tr>
        <tr><td class="k">Asal sekolah</td><td class="v">{{ $sw['asal_sekolah'] ?: '-' }}</td></tr>
        <tr><td class="k">Orang tua / wali</td><td class="v">{{ $ot['ibu'] ?: $ot['ayah'] }}</td></tr>
        <tr><td class="k">Gelombang</td><td class="v">{{ $p['gelombang'] }}</td></tr>
        <tr><td class="k">Tanggal pengumuman</td><td class="v">{{ $g['pengumuman'] }}</td></tr>
    </table>

    <div class="sec">Langkah daftar ulang</div>
    <table class="kv">
        <tr><td class="k">Jadwal daftar ulang</td><td class="v">{{ $g['daftar_ulang'] }}</td></tr>
    </table>
    <ol>
        <li>Datang ke {{ config('sekolah.unit.'.$p['jenjang'], config('sekolah.nama')) }}, {{ $alamat }}.</li>
        <li>Bawa cetakan bukti ini dan berkas asli untuk dicocokkan:
            @foreach ($jenisBerkas as $j){{ $j['nama'] }}{{ $loop->last ? '.' : ', ' }}@endforeach
        </li>
        <li>Selesaikan administrasi daftar ulang langsung di sekolah.</li>
    </ol>

    <div class="note">
        <b>Penting:</b> dokumen ini dibuat otomatis oleh sistem setelah panitia mempublikasikan pengumuman hasil seleksi.
        Keabsahan hasil dapat dicocokkan dengan panitia menggunakan kode pendaftaran di atas.
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
