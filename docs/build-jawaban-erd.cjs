// Membuat docs/jawaban-erd-pertemuan-9.html dari satu sumber data (tabel + diagram dijamin konsisten).
const fs = require('fs');
const path = require('path');

// [nama, tipe, kunci(PK|FK|UK|''), keterangan]
const { T, R, ppdb, profil } = require('./erd-data.cjs');

const esc = s => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;');
const mmType = t => t.replace(/\(.*\)/, '').replace(/ unsigned/, '');
const mmEnt = n => `    ${n} {\n` + T[n][1].map(([a, t, k]) =>
  `        ${mmType(t)} ${a}${k ? ' ' + k : ''}`).join('\n') + '\n    }';
const mmRel = ([a, b, m]) => `    ${a} ${m} ${b} : ""`;

const diagPPDB = 'erDiagram\n' + R.map(mmRel).join('\n') + '\n' + ppdb.map(mmEnt).join('\n');
const diagProfil = 'erDiagram\n' + profil.map(mmEnt).join('\n');

const keyLabel = k => k === 'PK' ? 'Primary Key (PK)' : k === 'FK' ? 'Foreign Key (FK)' : k === 'UK' ? 'UNIQUE' : '-';
const tableDetail = n => `
<h4>Tabel <code>${n}</code> <small>— ${esc(T[n][0])}</small></h4>
<table><thead><tr><th>Nama atribut</th><th>Identifikasi</th><th>Tipe data</th><th>Keterangan</th></tr></thead><tbody>
${T[n][1].map(([a, t, k, d]) => `<tr><td><code>${a}</code></td><td>${keyLabel(k)}</td><td>${esc(t)}</td><td>${esc(d)}</td></tr>`).join('\n')}
</tbody></table>`;

const entRow = n => {
  const a = T[n][1];
  const pk = a.filter(x => x[2] === 'PK').map(x => x[0]).join(', ') || '-';
  const fk = a.filter(x => x[2] === 'FK').map(x => x[0]).join(', ') || '-';
  const rest = a.filter(x => !x[2] || x[2] === 'UK').map(x => `${x[0]} (${x[1]}${x[2] === 'UK' ? ', UNIQUE' : ''})`).join(', ');
  return `<tr><td><code>${n}</code></td><td>${pk}</td><td>${fk}</td><td>${esc(rest)}</td></tr>`;
};

const html = `<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Jawaban ERD dan Rancangan Database - Pertemuan 9</title>
<style>
 body{font:14px/1.55 system-ui,Segoe UI,Arial,sans-serif;max-width:1100px;margin:24px auto;padding:0 16px;color:#1b1b1b}
 h1{font-size:22px;margin-bottom:4px} h2{margin-top:36px;border-bottom:2px solid #0a7d4b;padding-bottom:4px;color:#0a7d4b}
 h3{margin-top:26px} h4{margin:22px 0 6px} h4 small{font-weight:400;color:#555}
 table{border-collapse:collapse;width:100%;margin:6px 0 12px;font-size:13px}
 th,td{border:1px solid #bbb;padding:4px 8px;vertical-align:top;text-align:left} th{background:#e8f5ee}
 code{background:#f2f2f2;padding:0 3px;border-radius:3px}
 .meta td{border:none;padding:1px 8px 1px 0} .diagram{overflow:auto;border:1px solid #ddd;padding:8px;margin:8px 0}
 @page{size:A4;margin:12mm} .diagram svg{max-width:100%;height:auto}
 @media print{body{max-width:none;margin:0;font-size:12px} h2{break-before:page} h2:first-of-type{break-before:auto} h4,table,.diagram{break-inside:avoid} .diagram{overflow:visible;text-align:center} .diagram svg{max-width:100%!important;max-height:235mm;width:auto;height:auto}}
</style></head><body>
<h1>Tugas Analisa dan Perancangan SI — Pertemuan ke-9</h1>
<table class="meta"><tr><td><b>Kelompok</b></td><td>10</td></tr>
<tr><td><b>Anggota</b></td><td>Ghibran Aryasena Avianto (231011750061), Salsabillah (231011750247), Gilang Ramadhan (231011750251)</td></tr>
<tr><td><b>Judul aplikasi</b></td><td>Perancangan Sistem Company Profile dan Pendaftaran Siswa Baru Berbasis Web Pada Yayasan Sirajul Athfal</td></tr>
<tr><td><b>Tujuan akhir</b></td><td>Website yang memuat profil resmi yayasan dan memungkinkan orang tua mendaftarkan calon siswa secara online (formulir, unggah berkas, cek status, pengumuman hasil), sementara panitia memverifikasi dan mengumumkan hasil dari satu dashboard.</td></tr>
<tr><td><b>Basis data</b></td><td>MySQL, 14 tabel (9 tabel modul PPDB + 5 tabel modul Company Profile), mengacu pada PRD Revisi 1.</td></tr></table>

<h2>Soal 1 — ERD lengkap (entitas, atribut, relasi, kardinalitas)</h2>
<p><i>Catatan: kolom bantu bawaan Laravel (<code>created_at</code>, <code>updated_at</code>) tidak digambar agar ERD fokus pada data bisnis. Waktu yang bermakna bisnis tetap ada, yaitu <code>tanggal_submit</code>, <code>published_at</code>, dan <code>riwayat_status.created_at</code>.</i></p>
<h3>1.1 ERD Modul PPDB</h3>
<p>Notasi crow's foot: <code>||</code> tepat satu, <code>o{</code> nol atau banyak, <code>|o</code> nol atau satu.</p>
<div class="diagram"><pre class="mermaid">
${diagPPDB}
</pre></div>
<h3>1.2 ERD Modul Company Profile</h3>
<p>Lima tabel ini berdiri sendiri (tidak ada foreign key) karena hanya menyimpan konten yang dikelola panitia.</p>
<div class="diagram"><pre class="mermaid">
${diagProfil}
</pre></div>

<h3>1.3 Daftar entitas dan atribut</h3>
<table><thead><tr><th>Entitas</th><th>Primary key</th><th>Foreign key</th><th>Atribut lain (tipe data)</th></tr></thead><tbody>
${[...ppdb, ...profil].map(entRow).join('\n')}
</tbody></table>

<h3>1.4 Relasi dan kardinalitas</h3>
<table><thead><tr><th>Entitas sumber</th><th>Entitas tujuan</th><th>Kardinalitas</th><th>Keterangan</th></tr></thead><tbody>
${R.map(r => `<tr><td><code>${r[0]}</code></td><td><code>${r[1]}</code></td><td>${r[3]}</td><td>${esc(r[4])}</td></tr>`).join('\n')}
</tbody></table>
<p>Hasil seleksi tiap pendaftar disimpan pada kolom <code>pendaftaran.status</code> (Diterima/Tidak Diterima); tabel <code>pengumuman</code> hanya mengatur kapan hasil boleh dilihat (<code>is_published</code>).</p>

<h2>Soal 2 — Detail atribut: primary key, foreign key, tipe data</h2>
<h3>2.1 Modul PPDB</h3>
${ppdb.map(tableDetail).join('\n')}
<h3>2.2 Modul Company Profile</h3>
${profil.map(tableDetail).join('\n')}

<h2>Soal 3 — Gambar rancangan database</h2>
<p>Rancangan database memuat <b>14 tabel</b>. Gambar di bawah menunjukkan seluruh tabel PPDB beserta tipe data kolom, penanda <code>PK</code> (primary key), <code>FK</code> (foreign key), <code>UK</code> (unique), dan garis relasi antar tabel; modul Company Profile digambar terpisah karena tidak berelasi dengan tabel lain.</p>
<div class="diagram"><pre class="mermaid">
${diagPPDB}
</pre></div>
<div class="diagram"><pre class="mermaid">
${diagProfil}
</pre></div>
<h3>Aturan integritas data pada rancangan</h3>
<ul>
<li><code>pendaftaran</code>: UNIQUE (<code>nik</code>, <code>tahun_ajaran_id</code>) — satu NIK hanya satu pendaftaran per tahun ajaran (BR-02).</li>
<li><code>kuota_jenjang</code>: UNIQUE (<code>tahun_ajaran_id</code>, <code>jenjang_id</code>) — satu kuota per jenjang per tahun ajaran.</li>
<li><code>pengumuman</code>: UNIQUE (<code>tahun_ajaran_id</code>, <code>jenjang_id</code>) — satu pengumuman per jenjang per tahun ajaran.</li>
<li><code>berkas</code>: UNIQUE (<code>pendaftaran_id</code>, <code>jenis_berkas_id</code>) — satu file aktif untuk tiap jenis berkas; mengganti berkas menimpa file lama.</li>
<li><code>riwayat_status.user_id</code> boleh kosong untuk perubahan otomatis oleh sistem; setiap perubahan status mencatat status lama, status baru, pelaku, waktu, dan catatan (BR-09).</li>
</ul>
<h3>Alur singkat</h3>
<p>Calon pendaftar membuat akun di <code>users</code>, mengirim formulir yang tersimpan di <code>pendaftaran</code> (memilih <code>tahun_ajaran</code> aktif dan <code>jenjang</code>), lalu mengunggah dokumen ke <code>berkas</code> sesuai <code>jenis_berkas</code>. Panitia memverifikasi dan mengubah status; setiap perubahan dicatat di <code>riwayat_status</code>. Setelah hasil ditetapkan dan <code>pengumuman</code> dipublikasikan, pendaftar melihat hasil dan jadwal daftar ulang.</p>

<script type="module">
import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
mermaid.initialize({startOnLoad:true,theme:'neutral',er:{useMaxWidth:true}});
</script>
</body></html>`;

const out = path.join(__dirname, 'jawaban-erd-pertemuan-9.html');
fs.writeFileSync(out, html);
fs.copyFileSync(out, path.join(__dirname, '..', 'public', 'docs', 'jawaban-erd-pertemuan-9.html'));
console.log('ok', out);
