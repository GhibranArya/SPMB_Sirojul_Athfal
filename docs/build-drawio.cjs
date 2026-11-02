// Membuat docs/ERD_dan_Class_Diagram_PPDB_Sirajul_Athfal.drawio (ERD + Class Diagram) dari erd-data.cjs.
// Tata letak: tahun_ajaran di atas, jenjang di bawah, tabel yang bergantung pada keduanya di baris tengah
// (kuota_jenjang, pengumuman, pendaftaran) sehingga garis relasi tidak saling bersilangan.
const fs = require('fs');
const path = require('path');
const { T, R, ppdb, profil } = require('./erd-data.cjs');

const xml = s => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
const BLUE = { fill: '#dae8fc', stroke: '#6c8ebf' };
const GREEN = { fill: '#d5e8d4', stroke: '#82b366' };

let seq = 0;
const nid = () => 'n' + (++seq);

// ---------- builder umum ----------
class Page {
  constructor(name, w, h) { this.name = name; this.w = w; this.h = h; this.cells = []; this.box = {}; }
  add(s) { this.cells.push(s); }
  text(value, x, y, w, h, style) {
    this.add(`<mxCell id="${nid()}" value="${xml(value)}" style="${style}" vertex="1" parent="1"><mxGeometry x="${x}" y="${y}" width="${w}" height="${h}" as="geometry"/></mxCell>`);
  }
  frame(label, x, y, w, h, color) {
    this.text(label, x, y, w, h, `rounded=1;arcSize=2;whiteSpace=wrap;html=1;fillColor=none;dashed=1;strokeColor=${color.stroke};verticalAlign=top;align=left;spacingLeft=10;fontStyle=1;fontSize=13;fontColor=${color.stroke};`);
  }
  // kotak bertumpuk (swimlane) dengan baris anak; rows = [{html, sep?}]
  table(key, title, x, y, w, rowH, startSize, rows, color, sepH) {
    const id = nid();
    const h = startSize + rows.reduce((a, r) => a + (r.sep ? sepH : rowH), 0);
    this.box[key] = { id, x, y, w, h };
    let out = `<mxCell id="${id}" value="${xml(title)}" style="swimlane;fontStyle=1;childLayout=stackLayout;horizontal=1;startSize=${startSize};horizontalStack=0;resizeParent=1;resizeParentMax=0;resizeLast=0;collapsible=0;marginBottom=0;html=1;fillColor=${color.fill};strokeColor=${color.stroke};fontSize=12;" vertex="1" parent="1"><mxGeometry x="${x}" y="${y}" width="${w}" height="${h}" as="geometry"/></mxCell>`;
    let cy = startSize;
    for (const r of rows) {
      if (r.sep) {
        out += `<mxCell id="${nid()}" value="" style="line;strokeWidth=1;fillColor=none;align=left;verticalAlign=middle;spacingTop=-1;spacingLeft=3;spacingRight=3;rotatable=0;labelPosition=right;points=[];portConstraint=eastwest;" vertex="1" parent="${id}"><mxGeometry x="0" y="${cy}" width="${w}" height="${sepH}" as="geometry"/></mxCell>`;
        cy += sepH;
      } else {
        out += `<mxCell id="${nid()}" value="${xml(r.html)}" style="text;strokeColor=none;fillColor=none;align=left;verticalAlign=middle;spacingLeft=6;spacingRight=4;overflow=hidden;rotatable=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;html=1;fontSize=11;" vertex="1" parent="${id}"><mxGeometry x="0" y="${cy}" width="${w}" height="${rowH}" as="geometry"/></mxCell>`;
        cy += rowH;
      }
    }
    this.add(out);
    return this.box[key];
  }
  // posisi titik sambung pada sisi kotak; sisi: L R T B; frac = posisi sepanjang sisi
  anchor(side, frac = 0.5) {
    return { L: [0, frac], R: [1, frac], T: [frac, 0], B: [frac, 1] }[side];
  }
  edge(srcKey, tgtKey, exitSide, entrySide, arrows, labels = {}, exitFrac = 0.5, entryFrac = 0.5) {
    const s = this.box[srcKey], t = this.box[tgtKey];
    const [ex, ey] = this.anchor(exitSide, exitFrac);
    const [nx, ny] = this.anchor(entrySide, entryFrac);
    const id = nid();
    let out = `<mxCell id="${id}" value="" style="edgeStyle=orthogonalEdgeStyle;rounded=0;html=1;strokeColor=#333333;exitX=${ex};exitY=${ey};exitDx=0;exitDy=0;entryX=${nx};entryY=${ny};entryDx=0;entryDy=0;${arrows}" edge="1" parent="1" source="${s.id}" target="${t.id}"><mxGeometry relative="1" as="geometry"/></mxCell>`;
    const lab = (v, pos) => v ? `<mxCell id="${nid()}" value="${xml(v)}" style="edgeLabel;html=1;align=center;verticalAlign=middle;resizable=0;points=[];fontSize=11;labelBackgroundColor=#ffffff;" vertex="1" connectable="0" parent="${id}"><mxGeometry x="${pos}" relative="1" as="geometry"><mxPoint as="offset"/></mxGeometry></mxCell>` : '';
    out += lab(labels.src, -0.82) + lab(labels.tgt, 0.82);
    this.add(out);
  }
  toXml() {
    return `<diagram name="${xml(this.name)}" id="${xml(this.name.replace(/\W+/g, ''))}"><mxGraphModel dx="1400" dy="900" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="${this.w}" pageHeight="${this.h}" math="0" shadow="0"><root><mxCell id="0"/><mxCell id="1" parent="0"/>${this.cells.join('')}</root></mxGraphModel></diagram>`;
  }
}

// ---------- tata letak bersama ----------
const COLA = 60, COLB = 400, COLC = 760, COLD = 1150, W = 270, WC = 300;
const GAP = 55;
// posisi tiap tabel: [x, y]; y diisi sesuai tinggi tabel pada tiap diagram
function layout(h) {
  const top = 130;
  const midY = top + h.tahun_ajaran + GAP;
  const L = {
    tahun_ajaran: [COLB, top, W],
    kuota_jenjang: [COLA, midY, W],
    pengumuman: [COLB, midY, W],
    pendaftaran: [COLC, midY, WC],
    users: [COLD, midY, W],
  };
  L.jenjang = [COLB, midY + h.pengumuman + GAP, W];
  L.riwayat_status = [COLD, midY + h.users + GAP, W];
  L.berkas = [COLC, midY + h.pendaftaran + GAP, WC];
  L.jenis_berkas = [COLD, L.berkas[1], W];
  return L;
}

// relasi: [sumber(1), tujuan(N), sisiKeluar, sisiMasuk]
const LINKS = [
  ['users', 'pendaftaran', 'L', 'R'],
  ['tahun_ajaran', 'pendaftaran', 'R', 'T'],
  ['jenjang', 'pendaftaran', 'R', 'L'],
  ['tahun_ajaran', 'kuota_jenjang', 'L', 'T'],
  ['jenjang', 'kuota_jenjang', 'L', 'B'],
  ['tahun_ajaran', 'pengumuman', 'B', 'T'],
  ['jenjang', 'pengumuman', 'T', 'B'],
  ['pendaftaran', 'berkas', 'B', 'T'],
  ['jenis_berkas', 'berkas', 'L', 'R'],
  ['pendaftaran', 'riwayat_status', 'R', 'L'],
  ['users', 'riwayat_status', 'B', 'T'],
];
const OPTIONAL = new Set(['users>riwayat_status']);   // user_id boleh kosong

// pecah fraksi agar garis horizontal lurus bila sisi masuk/keluar adalah L/R
function fracs(p, src, tgt, exitSide, entrySide) {
  const s = p.box[src], t = p.box[tgt];
  const clamp = v => Math.min(0.95, Math.max(0.05, v));
  let exitFrac = 0.5, entryFrac = 0.5;
  if (exitSide === 'R' || exitSide === 'L') {
    if (entrySide === 'L' || entrySide === 'R') {
      // satu garis lurus: samakan tinggi di sisi target (atau sisi sumber bila sumber lebih tinggi)
      const cy = s.y + s.h * 0.5;
      entryFrac = clamp((cy - t.y) / t.h);
      if (entryFrac !== (cy - t.y) / t.h) { entryFrac = 0.5; exitFrac = clamp((t.y + t.h / 2 - s.y) / s.h); }
    }
  }
  return [exitFrac, entryFrac];
}

// ---------- ERD ----------
function erdType(t) {
  const base = t.replace(' unsigned', '');
  if (/^enum\(/.test(base) && base.length > 26) return 'enum(...)';
  return base.replace(/^smallint$/, 'smallint').replace(/^enum\(([^)]*)\)$/, (m, v) => `enum(${v})`);
}
function erdRow(a) {
  const [name, type, key] = a;
  const t = erdType(type);
  const uk = key === 'UK' ? ' UNIQUE' : '';
  if (key === 'PK') return { html: `<b><u>PK</u></b>&nbsp; <u>${name} : ${t}</u>` };
  if (key === 'FK') return { html: `<i>FK</i>&nbsp; ${name} : ${t}` };
  return { html: `&nbsp; &nbsp; &nbsp; ${name} : ${t}${uk}` };
}
function buildErd() {
  const p = new Page('Entity Relationship Diagram (ERD)', 2140, 1260);
  const hOf = n => 30 + 22 * T[n][1].length;
  const heights = Object.fromEntries(Object.keys(T).map(n => [n, hOf(n)]));
  const L = layout(heights);

  p.text('Entity Relationship Diagram (ERD) - Sistem Company Profile & PPDB Yayasan Sirajul Athfal', 60, 20, 1400, 30, 'text;html=1;fontStyle=1;fontSize=16;align=left;verticalAlign=middle;');
  const frameH = Math.max(...Object.entries(L).map(([n, v]) => v[1] + heights[n])) + 45 - 85;
  p.frame('Modul PPDB (Pendaftaran Siswa Baru)', 40, 85, 1410, frameH, BLUE);
  p.frame('Modul Company Profile', 1480, 85, 600, frameH, GREEN);

  for (const n of ppdb) p.table(n, n, L[n][0], L[n][1], L[n][2], 22, 30, T[n][1].map(erdRow), BLUE, 0);

  // modul company profile (tanpa relasi)
  const prof = { profil_yayasan: [1500, 130], fasilitas: [1500, 130 + heights.profil_yayasan + 50],
                 berita: [1790, 130], agenda: [1790, 130 + heights.berita + 50], galeri: [1790, 130 + heights.berita + 50 + heights.agenda + 50] };
  for (const n of profil) p.table(n, n, prof[n][0], prof[n][1], 270, 22, 30, T[n][1].map(erdRow), GREEN, 0);

  for (const [s, t, es, ns] of LINKS) {
    const [ef, nf] = fracs(p, s, t, es, ns);
    const start = OPTIONAL.has(`${s}>${t}`) ? 'ERzeroToOne' : 'ERmandOne';
    p.edge(s, t, es, ns, `startArrow=${start};endArrow=ERzeroToMany;startFill=0;endFill=0;startSize=10;endSize=10;`, {}, ef, nf);
  }

  const legend = [
    '<b>Keterangan</b>',
    'PK = Primary Key, FK = Foreign Key',
    'Relasi: ┼┼ satu (wajib), ○┼ nol/satu, ○&lt; nol atau banyak',
    '<b>pendaftaran.status</b>: draft, menunggu_verifikasi, perlu_perbaikan, terverifikasi, diterima, tidak_diterima',
    '<b>UNIQUE gabungan</b>: pendaftaran(nik, tahun_ajaran_id); kuota_jenjang(tahun_ajaran_id, jenjang_id); pengumuman(tahun_ajaran_id, jenjang_id); berkas(pendaftaran_id, jenis_berkas_id)',
    '<b>riwayat_status.user_id</b> boleh kosong (perubahan oleh sistem)',
    'Kolom created_at/updated_at bawaan Laravel tidak digambar',
  ].join('<br>');
  p.text(legend, 1500, 130 + heights.berita + 50 + heights.agenda + 50 + heights.galeri + 40, 560, 190,
    'text;html=1;whiteSpace=wrap;align=left;verticalAlign=top;fontSize=11;strokeColor=#999999;fillColor=#ffffff;spacing=8;');
  return p;
}

// ---------- Class Diagram ----------
const CLS = {
  users: ['User', ['register()', 'login()', 'logout()', 'resetPassword()', 'isAdmin() : bool']],
  tahun_ajaran: ['TahunAjaran', ['aktifkan()', 'isDibuka() : bool', 'simpan()', 'ubah()', 'hapus()']],
  jenjang: ['Jenjang', ['simpan()', 'ubah()', 'hapus()']],
  kuota_jenjang: ['KuotaJenjang', ['atur(kuota)', 'cekKuota() : bool']],
  pendaftaran: ['Pendaftaran', ['generateKode() : string', 'cekDuplikatNik() : bool', 'simpan()', 'kirim()', 'ubahStatus(status, catatan)', 'cariByKode(kode, tanggalLahir)', 'rekap(filter)', 'export(format)']],
  jenis_berkas: ['JenisBerkas', ['simpan()', 'ubah()', 'hapus()']],
  berkas: ['Berkas', ['unggah(file)', 'ganti(file)', 'validasi(status, catatan)']],
  riwayat_status: ['RiwayatStatus', ['catat(statusLama, statusBaru, catatan)', 'tampilByPendaftaran()']],
  pengumuman: ['Pengumuman', ['simpan()', 'publikasikan()', 'kirimNotifikasi()']],
  profil_yayasan: ['ProfilYayasan', ['tampil()', 'ubah()']],
  fasilitas: ['Fasilitas', ['tampil()', 'simpan()', 'ubah()', 'hapus()']],
  berita: ['Berita', ['tampil()', 'simpan()', 'ubah()', 'hapus()', 'publikasikan()']],
  agenda: ['Agenda', ['tampil()', 'simpan()', 'ubah()', 'hapus()']],
  galeri: ['Galeri', ['tampil()', 'simpan()', 'ubah()', 'hapus()']],
};
const clsType = t => {
  const b = t.replace(' unsigned', '');
  if (/int$/.test(b)) return 'int';
  if (b === 'boolean') return 'bool';
  if (b === 'date') return 'date';
  if (b === 'datetime' || b === 'timestamp') return 'datetime';
  return 'string';
};
function clsRows(n) {
  const attrs = T[n][1].filter(a => a[2] !== 'FK' && !['created_at', 'updated_at'].includes(a[0]) || (n === 'riwayat_status' && a[0] === 'created_at'))
    .map(a => ({ html: `- ${a[0]} : ${a[0] === 'created_at' ? 'datetime' : clsType(a[1])}`.replace('created_at : datetime', 'created_at : datetime') }));
  return [...attrs, { sep: true }, ...CLS[n][1].map(m => ({ html: `+ ${m}` }))];
}
function buildClass() {
  const p = new Page('Class Diagram', 2140, 1500);
  const rowsOf = Object.fromEntries(Object.keys(T).map(n => [n, clsRows(n)]));
  const hOf = n => 30 + rowsOf[n].reduce((a, r) => a + (r.sep ? 8 : 20), 0);
  const heights = Object.fromEntries(Object.keys(T).map(n => [n, hOf(n)]));
  const L = layout(heights);

  p.text('Class Diagram - Sistem Company Profile & PPDB Yayasan Sirajul Athfal', 60, 20, 1400, 30, 'text;html=1;fontStyle=1;fontSize=16;align=left;verticalAlign=middle;');
  const frameH = Math.max(...Object.entries(L).map(([n, v]) => v[1] + heights[n])) + 45 - 85;
  p.frame('Modul PPDB (Pendaftaran Siswa Baru)', 40, 85, 1410, frameH, BLUE);
  p.frame('Modul Company Profile', 1480, 85, 600, frameH, GREEN);

  for (const n of ppdb) p.table(n, CLS[n][0], L[n][0], L[n][1], L[n][2], 20, 30, rowsOf[n], BLUE, 8);

  const prof = { profil_yayasan: [1500, 130], fasilitas: [1500, 130 + heights.profil_yayasan + 50],
                 berita: [1790, 130], agenda: [1790, 130 + heights.berita + 50], galeri: [1790, 130 + heights.berita + 50 + heights.agenda + 50] };
  for (const n of profil) p.table(n, CLS[n][0], prof[n][0], prof[n][1], 270, 20, 30, rowsOf[n], GREEN, 8);

  for (const [s, t, es, ns] of LINKS) {
    const [ef, nf] = fracs(p, s, t, es, ns);
    const srcMult = OPTIONAL.has(`${s}>${t}`) ? '0..1' : '1';
    p.edge(s, t, es, ns, 'startArrow=none;endArrow=none;', { src: srcMult, tgt: '0..*' }, ef, nf);
  }

  const legend = [
    '<b>Keterangan</b>',
    '- atribut private, + method public',
    'Multiplisitas: 1 ── 0..* (satu ke banyak); 0..1 = boleh kosong',
    'Atribut foreign key (user_id, tahun_ajaran_id, dst.) tidak ditulis di kelas; hubungannya digambarkan oleh asosiasi',
    '<b>Pendaftaran.status</b>: draft, menunggu_verifikasi, perlu_perbaikan, terverifikasi, diterima, tidak_diterima',
  ].join('<br>');
  p.text(legend, 1500, 130 + heights.berita + 50 + heights.agenda + 50 + heights.galeri + 40, 560, 130,
    'text;html=1;whiteSpace=wrap;align=left;verticalAlign=top;fontSize=11;strokeColor=#999999;fillColor=#ffffff;spacing=8;');
  return p;
}

const out = `<mxfile host="app.diagrams.net" agent="Claude" version="24.7.0">${buildErd().toXml()}${buildClass().toXml()}</mxfile>\n`;
const file = path.join(__dirname, 'ERD_dan_Class_Diagram_PPDB_Sirajul_Athfal.drawio');
fs.writeFileSync(file, out);
console.log('ok', file, out.length);
