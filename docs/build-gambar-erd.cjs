// Menggambar ERD sebagai SVG yang rapi (garis siku, crow's foot, label kardinalitas) lalu mengubahnya ke PNG lewat Chrome.
//   konseptual (k): nama atribut + penanda PK/FK/UK, untuk Soal 1
//   fisik      (f): nama atribut + tipe data, untuk Soal 3
// Hasil: docs/gambar/{erd-ppdb-k,erd-ppdb-f,erd-profil-k,erd-profil-f}.png + ukuran di docs/gambar/ukuran.json
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');
const { T, R, ppdb, profil } = require('./erd-data.cjs');

const OUT = path.join(__dirname, 'gambar');
fs.mkdirSync(OUT, { recursive: true });
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';

const FONT = 14, ROW = 24, HEAD = 34, CH = 7.6;            // CH = perkiraan lebar 1 karakter (px)
const COL = { ppdb: '#2f5d8c', profil: '#2e7d4f' };
const esc = s => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;');
const typeShow = t => {
  const b = t.replace(' unsigned', '');
  return /^enum\(/.test(b) && b.length > 24 ? 'enum(...)' : b;
};
const rows = n => T[n][1].map(([name, type, key]) => ({ name, type: typeShow(type), key }));

function tableW(n, mode) {
  let w = n.length * (CH + 1.2) + 28;
  for (const r of rows(n)) {
    const need = mode === 'f' ? 44 + r.name.length * CH + 26 + r.type.length * CH + 14
                              : 44 + r.name.length * CH + 20 + (r.key === 'UK' ? 22 : 0);
    w = Math.max(w, need);
  }
  return Math.ceil(w / 10) * 10;
}
const tableH = n => HEAD + ROW * T[n][1].length;

// ---------- gambar satu tabel ----------
function drawTable(n, b, mode, color) {
  let s = `<g font-family="Arial, Helvetica, sans-serif">`;
  s += `<rect x="${b.x}" y="${b.y}" width="${b.w}" height="${b.h}" rx="5" fill="#ffffff" stroke="${color}" stroke-width="1.6"/>`;
  s += `<path d="M${b.x},${b.y + HEAD} V${b.y + 5} a5,5 0 0 1 5,-5 H${b.x + b.w - 5} a5,5 0 0 1 5,5 V${b.y + HEAD} Z" fill="${color}"/>`;
  s += `<text x="${b.x + b.w / 2}" y="${b.y + HEAD / 2 + 5.5}" text-anchor="middle" font-size="${FONT + 1}" font-weight="700" fill="#ffffff">${n}</text>`;
  rows(n).forEach((r, i) => {
    const y = b.y + HEAD + i * ROW;
    if (r.key === 'PK') s += `<rect x="${b.x + 1}" y="${y}" width="${b.w - 2}" height="${ROW}" fill="#fff6d6"/>`;
    if (i > 0) s += `<line x1="${b.x}" y1="${y}" x2="${b.x + b.w}" y2="${y}" stroke="#e3e3e3"/>`;
    const ty = y + ROW / 2 + 4.8;
    if (r.key) {
      const kc = r.key === 'PK' ? '#9a6b00' : r.key === 'FK' ? '#2f5d8c' : '#7a3b9a';
      s += `<text x="${b.x + 9}" y="${ty}" font-size="${FONT - 3}" font-weight="700" fill="${kc}">${r.key}</text>`;
    }
    const uk = mode === 'k' && r.key === 'UK' ? '' : '';
    s += `<text x="${b.x + 40}" y="${ty}" font-size="${FONT}" ${r.key === 'PK' ? 'font-weight="700"' : ''} fill="#1b1b1b">${r.name}${uk}</text>`;
    if (mode === 'f') s += `<text x="${b.x + b.w - 10}" y="${ty}" text-anchor="end" font-size="${FONT - 1.5}" fill="#666666">${esc(r.type)}</text>`;
  });
  return s + '</g>';
}

// ---------- garis relasi ----------
const pt = (b, side, f = 0.5) => ({ L: [b.x, b.y + b.h * f], R: [b.x + b.w, b.y + b.h * f], T: [b.x + b.w * f, b.y], B: [b.x + b.w * f, b.y + b.h] }[side]);
function route(a, b, es, ns, ef, nf) {
  const p = pt(a, es, ef), q = pt(b, ns, nf);
  const horiz = side => side === 'L' || side === 'R';
  if (horiz(es) && horiz(ns)) {
    if (Math.abs(p[1] - q[1]) < 0.5) return [p, q];
    const mx = (p[0] + q[0]) / 2;
    return [p, [mx, p[1]], [mx, q[1]], q];
  }
  if (!horiz(es) && !horiz(ns)) return Math.abs(p[0] - q[0]) < 0.5 ? [p, q] : [p, [p[0], (p[1] + q[1]) / 2], [q[0], (p[1] + q[1]) / 2], q];
  return horiz(es) ? [p, [q[0], p[1]], q] : [p, [p[0], q[1]], q];
}
function unit(from, to) { const dx = to[0] - from[0], dy = to[1] - from[1], l = Math.hypot(dx, dy) || 1; return [dx / l, dy / l]; }
function ends(pts, srcOpt) {
  const A = pts[0], A2 = pts[1], B = pts[pts.length - 1], B2 = pts[pts.length - 2];
  let s = '';
  const sd = unit(A, A2), td = unit(B, B2);
  const nrm = d => [-d[1], d[0]];
  const at = (P, d, k, n, m) => [P[0] + d[0] * k + n[0] * m, P[1] + d[1] * k + n[1] * m];
  const line = (p1, p2) => `<line x1="${p1[0].toFixed(1)}" y1="${p1[1].toFixed(1)}" x2="${p2[0].toFixed(1)}" y2="${p2[1].toFixed(1)}" stroke="#333" stroke-width="1.6"/>`;
  // sisi "satu"
  const ns = nrm(sd);
  s += line(at(A, sd, 9, ns, -7), at(A, sd, 9, ns, 7));
  if (srcOpt) s += `<circle cx="${at(A, sd, 19, ns, 0)[0].toFixed(1)}" cy="${at(A, sd, 19, ns, 0)[1].toFixed(1)}" r="5" fill="#fff" stroke="#333" stroke-width="1.6"/>`;
  else s += line(at(A, sd, 15, ns, -7), at(A, sd, 15, ns, 7));
  // sisi "nol atau banyak"
  const nt = nrm(td), tip = at(B, td, 17, nt, 0);
  s += line(tip, at(B, td, 0, nt, -9)) + line(tip, B) + line(tip, at(B, td, 0, nt, 9));
  const c = at(B, td, 25, nt, 0);
  s += `<circle cx="${c[0].toFixed(1)}" cy="${c[1].toFixed(1)}" r="5" fill="#fff" stroke="#333" stroke-width="1.6"/>`;
  // label
  const lab = (P, d, txt) => { const off = 9 + txt.length * 4.2; const p = at(P, d, 34, [-d[1], d[0]], off); return `<text x="${p[0].toFixed(1)}" y="${(p[1] + 5).toFixed(1)}" text-anchor="middle" font-size="13" font-weight="700" font-family="Arial, Helvetica, sans-serif" fill="#b00020">${txt}</text>`; };
  s += lab(A, sd, srcOpt ? '0..1' : '1') + lab(B, td, 'N');
  return s;
}

// ---------- diagram PPDB ----------
// tahun_ajaran di atas dan jenjang di bawah; kuota_jenjang, pengumuman, pendaftaran di tengah -> tanpa garis bersilangan
function ppdbDiagram(mode) {
  const w = {}, h = {};
  for (const n of ppdb) { w[n] = tableW(n, mode); h[n] = tableH(n); }
  const colW = {
    A: w.kuota_jenjang,
    B: Math.max(w.tahun_ajaran, w.pengumuman, w.jenjang),
    C: Math.max(w.pendaftaran, w.berkas),
    D: Math.max(w.users, w.riwayat_status, w.jenis_berkas),
  };
  const GX = [110, 130, 110];
  const x = { A: 30 };
  x.B = x.A + colW.A + GX[0]; x.C = x.B + colW.B + GX[1]; x.D = x.C + colW.C + GX[2];
  const VG = 84, top = 30;
  const B = {};
  const put = (n, col, y) => (B[n] = { x: x[col], y, w: colW[col], h: h[n] });
  put('tahun_ajaran', 'B', top);
  put('pendaftaran', 'C', top);
  put('users', 'D', top);
  put('pengumuman', 'B', B.tahun_ajaran.y + h.tahun_ajaran + VG);
  put('jenjang', 'B', B.pengumuman.y + h.pengumuman + VG);
  put('riwayat_status', 'D', B.users.y + h.users + VG);
  put('berkas', 'C', B.pendaftaran.y + h.pendaftaran + VG);
  put('jenis_berkas', 'D', B.berkas.y);
  put('kuota_jenjang', 'A', Math.round((B.tahun_ajaran.y + B.jenjang.y + h.jenjang) / 2 - h.kuota_jenjang / 2));

  const frac = (box, y) => Math.min(0.97, Math.max(0.03, (y - box.y) / box.h));
  const cy = n => B[n].y + B[n].h / 2;
  const E = [];
  const edge = (s, t, es, ns, ef, nf, opt) => E.push({ pts: route(B[s], B[t], es, ns, ef, nf), opt });
  edge('users', 'pendaftaran', 'L', 'R', 0.5, frac(B.pendaftaran, cy('users')));
  edge('tahun_ajaran', 'pendaftaran', 'R', 'L', 0.5, frac(B.pendaftaran, cy('tahun_ajaran')));
  edge('jenjang', 'pendaftaran', 'R', 'L', 0.12, 0.965);
  edge('tahun_ajaran', 'kuota_jenjang', 'L', 'T', 0.5, 0.5);
  edge('jenjang', 'kuota_jenjang', 'L', 'B', 0.5, 0.5);
  edge('tahun_ajaran', 'pengumuman', 'B', 'T', 0.5, 0.5);
  edge('jenjang', 'pengumuman', 'T', 'B', 0.5, 0.5);
  edge('pendaftaran', 'berkas', 'B', 'T', 0.5, 0.5);
  edge('jenis_berkas', 'berkas', 'L', 'R', 0.5, frac(B.berkas, cy('jenis_berkas')));
  edge('pendaftaran', 'riwayat_status', 'R', 'L', frac(B.pendaftaran, cy('riwayat_status')), 0.5);
  edge('users', 'riwayat_status', 'B', 'T', 0.5, 0.5, true);

  const W = x.D + colW.D + 30;
  const H = Math.max(...Object.values(B).map(b => b.y + b.h)) + 30;
  let svg = '';
  for (const e of E) svg += `<polyline points="${e.pts.map(p => p.map(v => v.toFixed(1)).join(',')).join(' ')}" fill="none" stroke="#333" stroke-width="1.6"/>`;
  for (const e of E) svg += ends(e.pts, e.opt);
  for (const n of ppdb) svg += drawTable(n, B[n], mode, COL.ppdb);
  return { W, H, svg };
}

// ---------- diagram company profile (tanpa relasi) ----------
function profilDiagram(mode) {
  const W1 = Math.max(...profil.map(n => tableW(n, mode)));
  const GAP = 50, VG = 50;
  const grid = [['profil_yayasan', 'berita', 'agenda'], ['fasilitas', 'galeri']];
  let y = 30, svg = '', maxX = 0, maxY = 0;
  for (const rowNames of grid) {
    const rh = Math.max(...rowNames.map(tableH));
    rowNames.forEach((n, i) => {
      const b = { x: 30 + i * (W1 + GAP), y, w: W1, h: tableH(n) };
      svg += drawTable(n, b, mode, COL.profil);
      maxX = Math.max(maxX, b.x + b.w); maxY = Math.max(maxY, b.y + b.h);
    });
    y += rh + VG;
  }
  return { W: maxX + 30, H: maxY + 30, svg };
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

const sizes = {};
for (const [name, d] of Object.entries({
  'erd-ppdb-k': ppdbDiagram('k'), 'erd-ppdb-f': ppdbDiagram('f'),
  'erd-profil-k': profilDiagram('k'), 'erd-profil-f': profilDiagram('f'),
})) { toPng(name, d); sizes[name] = { w: d.W, h: d.H }; }
fs.writeFileSync(path.join(OUT, 'ukuran.json'), JSON.stringify(sizes, null, 1));
console.log(sizes);
