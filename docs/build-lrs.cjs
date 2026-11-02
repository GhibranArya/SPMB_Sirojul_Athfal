// Menggambar Logical Record Structure (LRS) sistem PPDB + company profile SMP Islam Sirojul Athfal.
// Sumber: database/migrations/, tetapi HANYA tabel dan atribut yang benar-benar dipakai kode aplikasi
// (controller, model, view, seeder). Kolom bawaan Laravel yang tidak dipakai fitur (email_verified_at,
// remember_token), tabel berita/agenda, dan kolom konten yang tidak pernah dibaca sengaja tidak digambar.
// Notasi: kotak = tabel/record, PK digarisbawahi, FK diberi tanda #, panah = dari tabel anak (pemegang FK) ke tabel induk.
// Hasil: docs/gambar/lrs.svg dan docs/gambar/lrs.png
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

const OUT = path.join(__dirname, 'gambar');
fs.mkdirSync(OUT, { recursive: true });
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';

// 'nama:PK' | 'nama:FK' | 'nama'  (created_at/updated_at tidak digambar)
const T = {
  users: ['id:PK', 'name', 'email', 'phone', 'password', 'role'],
  gelombang: ['id:PK', 'nama', 'tahun_ajaran', 'tgl_buka', 'tgl_tutup', 'tgl_pengumuman', 'daftar_ulang_mulai', 'daftar_ulang_selesai', 'is_aktif'],
  jenjang: ['id:PK', 'kode', 'nama'],
  kuota_jenjang: ['id:PK', 'gelombang_id:FK', 'jenjang_id:FK', 'kuota'],
  jenis_berkas: ['id:PK', 'kode', 'nama', 'wajib', 'icon', 'urutan', 'is_aktif'],
  pendaftaran: ['id:PK', 'user_id:FK', 'gelombang_id:FK', 'jenjang_id:FK', 'kode', 'status', 'catatan',
    'nama_siswa', 'nik', 'jenis_kelamin', 'tempat_lahir', 'tgl_lahir', 'asal_sekolah', 'alamat_siswa',
    'nama_ayah', 'nama_ibu', 'pekerjaan_ortu', 'hp_ortu', 'alamat_ortu', 'setuju_data', 'submitted_at'],
  berkas: ['id:PK', 'pendaftaran_id:FK', 'jenis_berkas_id:FK', 'path', 'nama_asli', 'mime', 'ukuran', 'status', 'catatan'],
  riwayat_status: ['id:PK', 'pendaftaran_id:FK', 'user_id:FK', 'status_lama', 'status_baru', 'keterangan'],
  pengumuman: ['id:PK', 'gelombang_id:FK', 'judul', 'published_at'],
  // konten halaman publik: tidak punya relasi
  profil_yayasan: ['id:PK', 'visi', 'misi', 'alamat', 'npsn'],
  fasilitas: ['id:PK', 'nama', 'ikon', 'urutan'],
  galeri: ['id:PK', 'judul', 'gambar', 'keterangan_gambar', 'posisi_gambar', 'urutan', 'untuk_hero'],
};
const PPDB = ['users', 'gelombang', 'jenjang', 'kuota_jenjang', 'jenis_berkas', 'pendaftaran', 'berkas', 'riwayat_status', 'pengumuman'];
const KONTEN = ['profil_yayasan', 'fasilitas', 'galeri'];

const rows = n => T[n].map(a => { const [name, key = ''] = a.split(':'); return { name, key }; });

const FONT = 14, ROW = 24, HEAD = 34, CH = 7.6;
const COL = { ppdb: '#2f5d8c', konten: '#2e7d4f' };
const warna = n => (KONTEN.includes(n) ? COL.konten : COL.ppdb);

const tableW = n => Math.ceil(Math.max(n.length * (CH + 1.2) + 28, ...rows(n).map(r => 40 + (r.name.length + (r.key === 'FK' ? 2 : 0)) * CH + 16)) / 10) * 10;
const tableH = n => HEAD + ROW * T[n].length;
const rowY = (b, n, attr) => b.y + HEAD + ROW * rows(n).findIndex(r => r.name === attr) + ROW / 2;

function drawTable(n, b) {
  const color = warna(n);
  let s = `<g font-family="Arial, Helvetica, sans-serif">`;
  s += `<rect x="${b.x}" y="${b.y}" width="${b.w}" height="${b.h}" fill="#ffffff" stroke="${color}" stroke-width="1.8"/>`;
  s += `<rect x="${b.x}" y="${b.y}" width="${b.w}" height="${HEAD}" fill="${color}"/>`;
  s += `<text x="${b.x + b.w / 2}" y="${b.y + HEAD / 2 + 5.5}" text-anchor="middle" font-size="${FONT + 1}" font-weight="700" fill="#ffffff">${n}</text>`;
  rows(n).forEach((r, i) => {
    const y = b.y + HEAD + i * ROW;
    if (i > 0) s += `<line x1="${b.x}" y1="${y}" x2="${b.x + b.w}" y2="${y}" stroke="#e6e6e6"/>`;
    const ty = y + ROW / 2 + 4.8;
    const label = (r.key === 'FK' ? '# ' : '') + r.name;
    const style = r.key === 'PK' ? 'font-weight="700" text-decoration="underline" fill="#1b1b1b"'
      : r.key === 'FK' ? 'font-style="italic" fill="#2f5d8c"' : 'fill="#1b1b1b"';
    s += `<text x="${b.x + 12}" y="${ty}" font-size="${FONT}" ${style}>${label}</text>`;
  });
  return s + '</g>';
}

// garis siku dari anak ke induk; ujung induk diberi kepala panah
function edge(pts) {
  const f = p => p.map(v => v.toFixed(1)).join(',');
  const B = pts[pts.length - 1], B2 = pts[pts.length - 2];
  const dx = B[0] - B2[0], dy = B[1] - B2[1], l = Math.hypot(dx, dy) || 1, ux = dx / l, uy = dy / l;
  const a = [B[0] - ux * 12 - uy * 6, B[1] - uy * 12 + ux * 6], c = [B[0] - ux * 12 + uy * 6, B[1] - uy * 12 - ux * 6];
  return `<polyline points="${pts.map(f).join(' ')}" fill="none" stroke="#333" stroke-width="1.6"/>` +
    `<polygon points="${f(B)} ${f(a)} ${f(c)}" fill="#333"/>`;
}

function diagram() {
  const names = [...PPDB, ...KONTEN];
  const w = {}, h = {};
  for (const n of names) { w[n] = tableW(n); h[n] = tableH(n); }
  const colW = {
    A: Math.max(w.kuota_jenjang, ...KONTEN.map(n => w[n])),
    B: Math.max(w.gelombang, w.pengumuman, w.jenjang),
    C: Math.max(w.pendaftaran, w.berkas),
    D: Math.max(w.users, w.riwayat_status, w.jenis_berkas),
  };
  const GAP = [120, 150, 110], VG = 70, top = 30;
  const x = { A: 30 };
  x.B = x.A + colW.A + GAP[0]; x.C = x.B + colW.B + GAP[1]; x.D = x.C + colW.C + GAP[2];
  const B = {};
  const put = (n, col, y) => (B[n] = { x: x[col], y, w: colW[col], h: h[n] });

  put('gelombang', 'B', top);
  put('pendaftaran', 'C', top);
  put('pengumuman', 'B', B.gelombang.y + h.gelombang + VG);
  put('jenjang', 'B', B.pengumuman.y + h.pengumuman + VG);
  put('berkas', 'C', B.pendaftaran.y + h.pendaftaran + VG);
  // users sejajar: baris id users = baris user_id pendaftaran -> garis lurus
  put('users', 'D', rowY(B.pendaftaran, 'pendaftaran', 'user_id') - (HEAD + ROW / 2));
  // riwayat_status: baris pendaftaran_id sejajar titik tempel di sisi kanan pendaftaran (di bawah garis users)
  put('riwayat_status', 'D', Math.max(B.users.y + h.users + VG, B.pendaftaran.y + 330));
  const riwayatAttach = rowY(B.riwayat_status, 'riwayat_status', 'pendaftaran_id');
  // jenis_berkas sejajar: baris id = baris jenis_berkas_id berkas
  put('jenis_berkas', 'D', rowY(B.berkas, 'berkas', 'jenis_berkas_id') - (HEAD + ROW / 2));

  // kolom kiri: profil_yayasan di atas, kuota_jenjang di tengah, fasilitas dan galeri di bawah (tanpa garis)
  put('profil_yayasan', 'A', top);
  const kuotaMid = Math.round((B.gelombang.y + B.jenjang.y + h.jenjang) / 2 - h.kuota_jenjang / 2);
  put('kuota_jenjang', 'A', Math.max(kuotaMid, B.profil_yayasan.y + h.profil_yayasan + VG));
  put('fasilitas', 'A', B.kuota_jenjang.y + h.kuota_jenjang + VG);
  put('galeri', 'A', B.fasilitas.y + h.fasilitas + 50);

  const gelId = rowY(B.gelombang, 'gelombang', 'id'), jenId = rowY(B.jenjang, 'jenjang', 'id');
  const P = B.pendaftaran, K = B.kuota_jenjang;
  const cx = b => b.x + b.w / 2;
  const x2 = B.gelombang.x + colW.B + GAP[1] * 0.35, x3 = B.gelombang.x + colW.B + GAP[1] * 0.7; // x2 lebih jauh dari pendaftaran
  const xk = K.x + K.w + GAP[0] / 2;
  const E = [
    // pendaftaran -> users (lurus)
    [[P.x + P.w, rowY(P, 'pendaftaran', 'user_id')], [B.users.x, rowY(B.users, 'users', 'id')]],
    // pendaftaran -> gelombang
    [[P.x, rowY(P, 'pendaftaran', 'gelombang_id')], [x2, rowY(P, 'pendaftaran', 'gelombang_id')], [x2, gelId], [B.gelombang.x + colW.B, gelId]],
    // pendaftaran -> jenjang
    [[P.x, rowY(P, 'pendaftaran', 'jenjang_id')], [x3, rowY(P, 'pendaftaran', 'jenjang_id')], [x3, jenId], [B.jenjang.x + colW.B, jenId]],
    // kuota_jenjang -> gelombang / jenjang
    [[K.x + K.w, rowY(K, 'kuota_jenjang', 'gelombang_id')], [xk, rowY(K, 'kuota_jenjang', 'gelombang_id')], [xk, gelId], [B.gelombang.x, gelId]],
    [[K.x + K.w, rowY(K, 'kuota_jenjang', 'jenjang_id')], [xk, rowY(K, 'kuota_jenjang', 'jenjang_id')], [xk, jenId], [B.jenjang.x, jenId]],
    // pengumuman -> gelombang (lurus)
    [[cx(B.pengumuman), B.pengumuman.y], [cx(B.gelombang), B.gelombang.y + h.gelombang]],
    // berkas -> pendaftaran (lurus)
    [[cx(B.berkas), B.berkas.y], [cx(P), P.y + P.h]],
    // berkas -> jenis_berkas (lurus)
    [[B.berkas.x + colW.C, rowY(B.berkas, 'berkas', 'jenis_berkas_id')], [B.jenis_berkas.x, rowY(B.jenis_berkas, 'jenis_berkas', 'id')]],
    // riwayat_status -> pendaftaran (lurus ke sisi kanan pendaftaran)
    [[B.riwayat_status.x, riwayatAttach], [P.x + P.w, riwayatAttach]],
    // riwayat_status -> users (lurus)
    [[cx(B.riwayat_status), B.riwayat_status.y], [cx(B.users), B.users.y + h.users]],
  ];

  const W = x.D + colW.D + 30;
  const Hbox = Math.max(...Object.values(B).map(b => b.y + b.h)) + 20;

  let svg = E.map(edge).join('');
  for (const n of names) svg += drawTable(n, B[n]);

  // keterangan notasi
  const ly = Hbox + 14;
  svg += `<g font-family="Arial, Helvetica, sans-serif" font-size="13" fill="#444">` +
    `<rect x="30" y="${ly - 11}" width="14" height="14" fill="${COL.ppdb}"/><text x="50" y="${ly + 1}">Modul PPDB</text>` +
    `<rect x="150" y="${ly - 11}" width="14" height="14" fill="${COL.konten}"/><text x="170" y="${ly + 1}">Konten halaman publik (tanpa relasi)</text>` +
    `<text x="430" y="${ly + 1}"><tspan font-weight="700" text-decoration="underline">PK</tspan> = garis bawah   ·   <tspan font-style="italic" fill="#2f5d8c"># FK</tspan> = kunci tamu   ·   panah menuju tabel induk</text>` +
    `</g>`;
  return { W, H: ly + 22, svg };
}

function toPng(name, d) {
  const svgText = `<svg xmlns="http://www.w3.org/2000/svg" width="${d.W}" height="${d.H}" viewBox="0 0 ${d.W} ${d.H}"><rect width="100%" height="100%" fill="#ffffff"/>${d.svg}</svg>`;
  fs.writeFileSync(path.join(OUT, name + '.svg'), svgText);
  const html = path.join(OUT, name + '.html');
  fs.writeFileSync(html, `<!doctype html><html><body style="margin:0;background:#fff">${svgText}</body></html>`);
  execFileSync(CHROME, ['--headless=new', '--disable-gpu', '--hide-scrollbars', '--force-device-scale-factor=2.5',
    `--window-size=${d.W},${d.H}`, `--screenshot=${path.join(OUT, name + '.png')}`, 'file:///' + html.replace(/\\/g, '/')], { stdio: 'ignore' });
  fs.unlinkSync(html);
}

const d = diagram();
toPng('lrs', d);
console.log('lrs', d.W + 'x' + d.H);
