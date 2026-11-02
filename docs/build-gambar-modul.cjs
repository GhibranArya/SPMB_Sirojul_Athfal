// Menggambar dua diagram Pertemuan 11 (hitam-putih, label singkat) dari docs/modul-data.cjs lalu mengubahnya ke PNG lewat Chrome:
//   struktur-modul.png  : diagram struktur program (hierarki modul -> submodul)
//   interaksi-modul.png : alur data antar modul, aktor, dan penyimpanan data
// Ukuran gambar dicatat di docs/gambar/ukuran-modul.json
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');
const { M, D } = require('./modul-data.cjs');

const OUT = path.join(__dirname, 'gambar');
fs.mkdirSync(OUT, { recursive: true });
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const FONT = 'font-family="Arial, Helvetica, sans-serif"';
const esc = s => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;');
const DIIMPLEMENTASIKAN = ['M2', 'M4'];

const txt = (x, y, s, o = {}) =>
  `<text x="${x}" y="${y}" ${FONT} font-size="${o.size || 14}" text-anchor="${o.anchor || 'middle'}"` +
  `${o.bold ? ' font-weight="700"' : ''}${o.italic ? ' font-style="italic"' : ''} fill="${o.fill || '#000'}">${esc(s)}</text>`;
const rect = (x, y, w, h, o = {}) =>
  `<rect x="${x}" y="${y}" width="${w}" height="${h}" fill="${o.fill || '#fff'}" stroke="#000" stroke-width="${o.sw || 1.4}"` +
  `${o.dash ? ' stroke-dasharray="6 4"' : ''}${o.rx ? ` rx="${o.rx}"` : ''}/>`;
const line = (pts, o = {}) =>
  `<polyline points="${pts.map(p => p.join(',')).join(' ')}" fill="none" stroke="#000" stroke-width="${o.sw || 1.4}"` +
  `${o.dash ? ' stroke-dasharray="5 4"' : ''}${o.end ? ' marker-end="url(#a)"' : ''}${o.start ? ' marker-start="url(#as)"' : ''}/>`;
const defs = `<defs>
<marker id="a" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="8" markerHeight="8" orient="auto"><path d="M0,0 L10,5 L0,10 z" fill="#000"/></marker>
<marker id="as" viewBox="0 0 10 10" refX="1" refY="5" markerWidth="8" markerHeight="8" orient="auto"><path d="M10,0 L0,5 L10,10 z" fill="#000"/></marker>
</defs>`;

function toPng(name, W, H, body) {
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">${defs}<rect width="100%" height="100%" fill="#fff"/>${body}</svg>`;
  fs.writeFileSync(path.join(OUT, name + '.svg'), svg);
  const html = path.join(OUT, name + '.html');
  fs.writeFileSync(html, `<!doctype html><html><body style="margin:0;background:#fff">${svg}</body></html>`);
  execFileSync(CHROME, ['--headless=new', '--disable-gpu', '--hide-scrollbars', '--force-device-scale-factor=2.5',
    `--window-size=${W},${H}`, `--screenshot=${path.join(OUT, name + '.png')}`, 'file:///' + html.replace(/\\/g, '/')], { stdio: 'ignore' });
  fs.unlinkSync(html);
  return { w: W, h: H };
}

// =====================================================================================================
// 1. Diagram struktur program
// =====================================================================================================
function struktur() {
  const CW = 196, GAP = 18, X0 = 22, PAD = 22;
  const W = X0 * 2 + M.length * CW + (M.length - 1) * GAP;
  const rootW = 380, rootH = 54, rootY = 18, rootX = (W - rootW) / 2;
  const modY = 118, modH = 62;
  const leafH = 30, leafGap = 9, leafY0 = modY + modH + 18;
  const maxLeaf = Math.max(...M.map(m => m.sub.length));
  const leavesEnd = leafY0 + maxLeaf * (leafH + leafGap);
  const baseY = leavesEnd + 18, baseH = 62;
  const H = baseY + baseH + 80;

  let s = '';
  s += rect(rootX, rootY, rootW, rootH, { fill: '#e6e6e6', sw: 2 });
  s += txt(W / 2, rootY + 23, 'SISTEM COMPANY PROFILE & SPMB', { size: 16, bold: true });
  s += txt(W / 2, rootY + 42, 'Sirojul Athfal (MI dan SMP)', { size: 14 });

  const busY = (rootY + rootH + modY) / 2;
  s += line([[W / 2, rootY + rootH], [W / 2, busY]]);
  const cx = i => X0 + i * (CW + GAP) + CW / 2;
  s += line([[cx(0), busY], [cx(M.length - 1), busY]]);

  M.forEach((m, i) => {
    const x = X0 + i * (CW + GAP);
    const impl = DIIMPLEMENTASIKAN.includes(m.id);
    s += line([[cx(i), busY], [cx(i), modY]]);
    s += rect(x, modY, CW, modH, { fill: '#e6e6e6', sw: impl ? 3.6 : 1.6 });
    s += txt(cx(i), modY + 21, m.id, { size: 14, bold: true });
    s += txt(cx(i), modY + 41, m.nama, { size: 15, bold: true });
    s += txt(cx(i), modY + 56, '', { size: 11 });

    // batang pohon di kiri, lalu cabang ke tiap submodul
    const trunkX = x + 14;
    const lastY = leafY0 + (m.sub.length - 1) * (leafH + leafGap) + leafH / 2;
    s += line([[trunkX, modY + modH], [trunkX, lastY]]);
    m.sub.forEach(([kode, label], j) => {
      const y = leafY0 + j * (leafH + leafGap);
      s += line([[trunkX, y + leafH / 2], [x + 26, y + leafH / 2]]);
      s += rect(x + 26, y, CW - 26, leafH, { sw: impl ? 2.6 : 1.2 });
      s += txt(x + 33, y + leafH / 2 + 5, kode, { size: 12, bold: true, anchor: 'start' });
      s += txt(x + 62, y + leafH / 2 + 5, label, { size: label.length > 17 ? 12 : 13.5, anchor: 'start' });
    });
  });

  // lapisan data bersama
  s += rect(X0, baseY, W - X0 * 2, baseH, { fill: '#f2f2f2', dash: true, sw: 1.6 });
  s += txt(W / 2, baseY + 24, 'LAPISAN DATA BERSAMA (Model Eloquent + MySQL)', { size: 14, bold: true });
  s += txt(W / 2, baseY + 46, 'User · Gelombang · KuotaJenjang · BiayaGelombang · Pendaftaran · Berkas · RiwayatStatus · Pengumuman · ProfilYayasan · Fasilitas · Galeri · Ekstrakurikuler', { size: 12.5 });

  // legenda
  const ly = baseY + baseH + 30;
  s += rect(X0, ly - 13, 34, 18, { sw: 3.6 }) + txt(X0 + 44, ly + 1, '= modul yang diimplementasikan dan diuji pada tugas ini (M2 dan M4)', { size: 13, anchor: 'start' });
  s += rect(X0 + 520, ly - 13, 34, 18, { fill: '#e6e6e6' }) + txt(X0 + 564, ly + 1, '= modul utama', { size: 13, anchor: 'start' });
  s += rect(X0 + 700, ly - 13, 34, 18) + txt(X0 + 744, ly + 1, '= submodul (fungsi)', { size: 13, anchor: 'start' });
  s += txt(X0, ly + 28, 'Garis putus-putus = lapisan yang dipakai bersama oleh semua modul', { size: 13, anchor: 'start', italic: true });

  return toPng('struktur-modul', W, H, s);
}

// =====================================================================================================
// 2. Diagram interaksi modul (alur data)
// =====================================================================================================
function interaksi() {
  const MW = 176, STEP = 238, X0 = 24;                 // lebar modul dan jarak antar modul (sela 62 px untuk nomor alur)
  const order = ['M1', 'M6', 'M2', 'M3', 'M4', 'M5'];
  const mx = Object.fromEntries(order.map((id, i) => [id, X0 + i * STEP]));
  const W = X0 * 2 + (order.length - 1) * STEP + MW;
  const actY = 56, actH = 46;
  const modY = 250, modH = 96, modMid = modY + modH / 2;
  const storeY = 500, storeH = 62;
  const H = storeY + storeH + 86;
  const mm = id => M.find(m => m.id === id);
  const cxm = id => mx[id] + MW / 2;
  let s = '';

  const flow = (n, x, y) => rect(x - 17, y - 11, 34, 22, { rx: 11, sw: 1.4 }) + txt(x, y + 4.5, n, { size: 12, bold: true });

  // modul
  for (const id of order) {
    const impl = DIIMPLEMENTASIKAN.includes(id);
    s += rect(mx[id], modY, MW, modH, { fill: '#e6e6e6', sw: impl ? 3.6 : 1.6 });
    s += txt(cxm(id), modY + 27, id, { size: 15, bold: true });
    const nama = mm(id).nama;
    const parts = nama.length > 14 ? [nama.split(' ').slice(0, 1).join(' '), nama.split(' ').slice(1).join(' ')] : [nama];
    parts.forEach((p, i) => { s += txt(cxm(id), modY + 52 + i * 19, p, { size: 15.5, bold: true }); });
  }

  // aktor (kotak bersudut bulat)
  const actor = (x, w, label) => rect(x, actY, w, actH, { rx: 22, sw: 1.6 }) + txt(x + w / 2, actY + actH / 2 + 5, label, { size: 15, bold: true });
  s += actor(mx.M1 + 14, MW - 28, 'Pengunjung');
  s += actor(mx.M6 + 14, MW - 28, 'Admin');
  s += actor(mx.M2 + 14, mx.M3 + MW - mx.M2 - 28, 'Wali / Admin');
  s += actor(mx.M4 + 14, mx.M5 + MW - mx.M4 - 28, 'Admin');

  // aktor -> modul (panah turun)
  const down = (x, n) => line([[x, actY + actH], [x, modY]], { end: true }) + flow(n, x, (actY + actH + modY) / 2);
  s += down(cxm('M1'), 'F1');
  s += down(cxm('M6'), 'F3');
  s += down(mx.M2 + 60, 'F5');
  s += down(mx.M3 + 96, 'F8');
  s += down(mx.M4 + 96, 'F12');
  s += down(mx.M5 + 96, 'F15');

  // penyimpanan data (pita bawah)
  const store = (x, w, id, label) =>
    rect(x, storeY, w, storeH, { sw: 1.6 }) + line([[x + 40, storeY], [x + 40, storeY + storeH]], { sw: 1.4 }) +
    txt(x + 20, storeY + storeH / 2 + 5, id, { size: 14, bold: true }) +
    label.map((l, i) => txt(x + 48 + (w - 48) / 2, storeY + 26 + i * 18, l, { size: 12.5 })).join('');
  const d5x = mx.M1, d5w = mx.M6 + MW - mx.M1;
  const d1x = mx.M2, d1w = MW;
  const d2x = mx.M3, d2w = mx.M4 + 100 - mx.M3;
  const d3x = mx.M4 + 116, d3w = mx.M5 + MW - (mx.M4 + 116);
  s += store(d5x, d5w, 'D5', ['profil_yayasan, fasilitas, galeri,', 'ekstrakurikuler']);
  s += store(d1x, d1w, 'D1', ['users']);
  s += store(d2x, d2w, 'D2', ['pendaftaran, berkas, riwayat_status']);
  s += store(d3x, d3w, 'D3', ['gelombang, kuota,', 'biaya, pengumuman']);

  const both = (x, n) => line([[x, modY + modH], [x, storeY]], { end: true, start: true }) + flow(n, x, (modY + modH + storeY) / 2);
  s += both(cxm('M1'), 'F2');
  s += both(cxm('M6'), 'F4');
  s += both(cxm('M2'), 'F6');
  s += both(mx.M3 + 96, 'F9');
  s += both(mx.M4 + 48, 'F13');
  s += both(mx.M4 + 140, 'F14');
  s += both(mx.M5 + 96, 'F16');

  // antar modul bersebelahan
  const gapMid = (a, b) => (mx[a] + MW + mx[b]) / 2;
  s += line([[mx.M2 + MW, modMid], [mx.M3, modMid]], { end: true }) + flow('F7', gapMid('M2', 'M3'), modMid);
  s += line([[mx.M3 + MW, modMid - 22], [mx.M4, modMid - 22]], { end: true }) + flow('F10', gapMid('M3', 'M4'), modMid - 22);
  s += line([[mx.M4, modMid + 22], [mx.M3 + MW, modMid + 22]], { end: true }) + flow('F11', gapMid('M3', 'M4'), modMid + 22);
  s += line([[mx.M5, modMid], [mx.M4 + MW, modMid]], { end: true }) + flow('F17', gapMid('M4', 'M5'), modMid);

  // M5 -> M3 melewati M4 lewat jalur bawah
  const laneY = 385;
  s += line([[mx.M5 + 28, modY + modH], [mx.M5 + 28, laneY], [mx.M3 + 150, laneY], [mx.M3 + 150, modY + modH]], { end: true });
  s += flow('F18', mx.M3 + 218, laneY);

  // alur pendukung (garis putus-putus)
  const ds = { end: true, dash: true };
  const lane19 = 224, lane21 = 204, lane20 = 470;
  // F19: peran admin dari M2 ke M6 (kiri) serta M4 dan M5 (kanan)
  s += line([[mx.M2 + 140, modY], [mx.M2 + 140, lane19], [mx.M6 + 150, lane19], [mx.M6 + 150, modY]], ds);
  s += line([[mx.M2 + 140, lane19], [mx.M5 + 150, lane19]], { dash: true });
  s += line([[mx.M4 + 40, lane19], [mx.M4 + 40, modY]], ds);
  s += line([[mx.M5 + 150, lane19], [mx.M5 + 150, modY]], ds);
  s += flow('F19', mx.M3 + 40, lane19);
  // F21: gelombang aktif dari M5 ke M1
  s += line([[mx.M5 + 30, modY], [mx.M5 + 30, lane21], [mx.M1 + 150, lane21], [mx.M1 + 150, modY]], ds);
  s += flow('F21', mx.M6 + MW + 31, lane21);
  // F20: reset sandi dari M4 ke D1
  s += line([[mx.M4 + 12, modY + modH], [mx.M4 + 12, lane20], [mx.M2 + 150, lane20], [mx.M2 + 150, storeY]], ds);
  s += flow('F20', mx.M2 + MW + 31, lane20);

  // legenda
  const ly = storeY + storeH + 40;
  s += line([[X0 + 930, ly - 4], [X0 + 970, ly - 4]], { dash: true, end: true }) + txt(X0 + 980, ly + 1, '= alur pendukung', { size: 13, anchor: 'start' });
  s += rect(X0, ly - 13, 34, 18, { sw: 3.6 }) + txt(X0 + 44, ly + 1, '= modul yang diimplementasikan dan diuji (M2 dan M4)', { size: 13, anchor: 'start' });
  s += rect(X0 + 440, ly - 13, 40, 18, { rx: 9 }) + txt(X0 + 490, ly + 1, '= aktor', { size: 13, anchor: 'start' });
  s += rect(X0 + 580, ly - 13, 34, 18) + txt(X0 + 624, ly + 1, '= penyimpanan data (tabel MySQL)', { size: 13, anchor: 'start' });
  s += flow('Fn', X0 + 17, ly + 36) + txt(X0 + 44, ly + 41, '= nomor alur data, rinciannya di Tabel Alur Data. Panah dua arah = baca dan tulis.', { size: 13, anchor: 'start' });

  return toPng('interaksi-modul', W, H + 36, s);
}

const sizes = { 'struktur-modul': struktur(), 'interaksi-modul': interaksi() };
fs.writeFileSync(path.join(OUT, 'ukuran-modul.json'), JSON.stringify(sizes, null, 1));
console.log(JSON.stringify(sizes));
