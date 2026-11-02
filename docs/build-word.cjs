// Membuat docs/Jawaban ERD Pertemuan 9 - Kelompok 10.docx (A4) dari erd-data.cjs + gambar di docs/gambar.
// Jalankan: NODE_PATH=<folder node_modules berisi docx> node docs/build-word.cjs
const fs = require('fs');
const path = require('path');
const {
  Document, Packer, Paragraph, TextRun, ImageRun, Table, TableRow, TableCell, WidthType, ShadingType,
  AlignmentType, BorderStyle, TableLayoutType, HeadingLevel, PageOrientation, Footer, PageNumber, LevelFormat, VerticalAlign,
} = require('docx');
const { T, R, ppdb, profil } = require('./erd-data.cjs');

const GAMBAR = path.join(__dirname, 'gambar');
const sizes = JSON.parse(fs.readFileSync(path.join(GAMBAR, 'ukuran.json'), 'utf8'));
const FONT = 'Calibri', MONO = 'Consolas';

// ---------- ukuran halaman ----------
const A4 = { width: 11906, height: 16838 };
const PORTRAIT = { page: { size: A4, margin: { top: 1134, bottom: 1134, left: 1134, right: 1134, footer: 500 } } };
const LANDSCAPE = { page: { size: { ...A4, orientation: PageOrientation.LANDSCAPE }, margin: { top: 720, bottom: 900, left: 720, right: 720, footer: 400 } } };
const CONTENT_W = A4.width - 2268;                 // 9638 DXA
const PX_PORTRAIT_W = Math.floor(CONTENT_W / 15);  // 1 px = 15 DXA (96 dpi)
const PX_LAND_W = Math.floor((A4.height - 1440) / 15), PX_LAND_H = 585;

// ---------- elemen dasar ----------
const run = (text, o = {}) => new TextRun({ text, font: FONT, size: 22, ...o });
const p = (children, o = {}) => new Paragraph({ spacing: { after: 120, line: 276 }, ...o, children: Array.isArray(children) ? children : [run(children)] });
const h1 = text => new Paragraph({ heading: HeadingLevel.HEADING_1, keepNext: true, children: [new TextRun({ text, font: FONT })] });
const h2 = text => new Paragraph({ heading: HeadingLevel.HEADING_2, keepNext: true, children: [new TextRun({ text, font: FONT })] });
const h3 = (code, desc) => new Paragraph({ heading: HeadingLevel.HEADING_3, keepNext: true, children: [
  new TextRun({ text: 'Tabel ', font: FONT }), new TextRun({ text: code, font: MONO }), new TextRun({ text: desc ? ` — ${desc}` : '', font: FONT, bold: false, italics: true })] });
const caption = text => new Paragraph({ alignment: AlignmentType.CENTER, spacing: { before: 80, after: 120 }, children: [run(text, { italics: true, size: 20, color: '444444' })] });
const bullet = children => new Paragraph({ numbering: { reference: 'bul', level: 0 }, spacing: { after: 60, line: 276 }, children: Array.isArray(children) ? children : [run(children)] });

function image(name, maxW, maxH) {
  const { w, h } = sizes[name];
  const s = Math.min(maxW / w, maxH / h, 1);
  return new Paragraph({ alignment: AlignmentType.CENTER, spacing: { before: 60, after: 0 }, keepNext: true, children: [new ImageRun({
    type: 'png', data: fs.readFileSync(path.join(GAMBAR, name + '.png')),
    transformation: { width: Math.round(w * s), height: Math.round(h * s) },
    altText: { title: name, description: `Diagram ${name}`, name } })] });
}

// ---------- tabel ----------
const line = { style: BorderStyle.SINGLE, size: 4, color: 'A6A6A6' };
const borders = { top: line, bottom: line, left: line, right: line };
function cell(content, width, o = {}) {
  const parts = Array.isArray(content) ? content : [content];
  return new TableCell({
    width: { size: width, type: WidthType.DXA }, borders, verticalAlign: VerticalAlign.CENTER,
    margins: { top: 50, bottom: 50, left: 100, right: 100 },
    shading: o.fill ? { type: ShadingType.CLEAR, fill: o.fill, color: 'auto' } : undefined,
    children: parts.map(t => new Paragraph({ keepNext: !!o.keepNext, spacing: { after: 0 }, alignment: o.align,
      children: [typeof t === 'string' ? new TextRun({ text: t, font: o.mono ? MONO : FONT, size: o.size || 19, bold: !!o.bold }) : t] })),
  });
}
function table(widths, header, bodyRows, o = {}) {
  const total = widths.reduce((a, b) => a + b, 0);
  const head = new TableRow({ tableHeader: true, cantSplit: true, children: header.map((t, i) => cell(t, widths[i], { fill: 'D9E6F2', bold: true, keepNext: true, size: o.size })) });
  const body = bodyRows.map((r, ri) => new TableRow({ cantSplit: true, children: r.map((c, i) => {
    const spec = typeof c === 'object' && c !== null && !Array.isArray(c) && c.text !== undefined ? c : { text: c };
    return cell(spec.text, widths[i], { mono: spec.mono, bold: spec.bold, fill: spec.fill, size: o.size, keepNext: o.keepTogether && ri < bodyRows.length - 1 });
  }) }));
  return new Table({ width: { size: total, type: WidthType.DXA }, columnWidths: widths, layout: TableLayoutType.FIXED, rows: [head, ...body] });
}
const spacer = () => new Paragraph({ spacing: { after: 160 }, children: [] });

// ---------- isi ----------
const keyLabel = k => (k === 'PK' ? 'Primary Key (PK)' : k === 'FK' ? 'Foreign Key (FK)' : k === 'UK' ? 'UNIQUE' : '-');
const detailTable = n => [
  h3(n, T[n][0]),
  table([2300, 1750, 2600, 2988], ['Nama atribut', 'Identifikasi', 'Tipe data', 'Keterangan'],
    T[n][1].map(([a, t, k, d]) => [{ text: a, mono: true, bold: k === 'PK', fill: k === 'PK' ? 'FFF6D6' : undefined }, keyLabel(k), t, d]), { keepTogether: true }),
  spacer(),
];
const entityRow = n => {
  const a = T[n][1];
  return [{ text: n, mono: true },
    a.filter(x => x[2] === 'PK').map(x => x[0]).join(', ') || '-',
    a.filter(x => x[2] === 'FK').map(x => x[0]).join(', ') || '-',
    a.filter(x => !x[2] || x[2] === 'UK').map(x => `${x[0]} (${x[1]}${x[2] === 'UK' ? ', UNIQUE' : ''})`).join(', ')];
};

const meta = (k, v) => new TableRow({ children: [cell(k, 2000, { bold: true, fill: 'EEF3F8', size: 21 }), cell(v, 7638, { size: 21 })] });
const metaTable = new Table({ width: { size: CONTENT_W, type: WidthType.DXA }, columnWidths: [2000, 7638], layout: TableLayoutType.FIXED, rows: [
  meta('Kelompok', '10'),
  new TableRow({ children: [cell('Anggota', 2000, { bold: true, fill: 'EEF3F8', size: 21 }),
    cell(['Ghibran Aryasena Avianto — 231011750061', 'Salsabillah — 231011750247', 'Gilang Ramadhan — 231011750251'], 7638, { size: 21 })] }),
  meta('Judul aplikasi', 'Perancangan Sistem Company Profile dan Pendaftaran Siswa Baru Berbasis Web Pada Yayasan Sirajul Athfal'),
  meta('Tujuan akhir', 'Website yang memuat profil resmi yayasan dan memungkinkan orang tua mendaftarkan calon siswa secara online (formulir, unggah berkas, cek status, pengumuman hasil), sementara panitia memverifikasi dan mengumumkan hasil dari satu dashboard.'),
  meta('Basis data', 'MySQL, 14 tabel: 9 tabel modul PPDB dan 5 tabel modul Company Profile (mengacu pada PRD Revisi 1).'),
] });

const notasi = table([1500, 8138], ['Simbol', 'Arti'], [
  [{ text: 'PK', bold: true }, 'Primary Key: kolom pengenal unik setiap baris (diberi latar kuning).'],
  [{ text: 'FK', bold: true }, 'Foreign Key: kolom yang merujuk ke primary key tabel lain.'],
  [{ text: 'UK', bold: true }, 'UNIQUE: nilai kolom tidak boleh sama antar baris.'],
  [{ text: '1', bold: true }, 'Sisi "satu" pada relasi (ditandai dua garis tegak).'],
  [{ text: 'N', bold: true }, 'Sisi "nol atau banyak" (ditandai lingkaran dan kaki gagak).'],
  [{ text: '0..1', bold: true }, 'Sisi "nol atau satu" (relasi opsional; kolom FK boleh kosong).'],
], { keepTogether: true });

const relTable = table([1750, 1850, 1300, 4738], ['Entitas sumber', 'Entitas tujuan', 'Kardinalitas', 'Keterangan'],
  R.map(r => [{ text: r[0], mono: true }, { text: r[1], mono: true }, r[3], r[4]]));

const section = (props, children) => ({
  properties: props,
  footers: { default: new Footer({ children: [new Paragraph({ alignment: AlignmentType.CENTER, children: [
    new TextRun({ text: 'Kelompok 10 — ERD dan Rancangan Database PPDB Yayasan Sirajul Athfal  |  Halaman ', font: FONT, size: 18, color: '666666' }),
    new TextRun({ children: [PageNumber.CURRENT], font: FONT, size: 18, color: '666666' })] })] }) },
  children,
});

const sections = [
  // 1. identitas + notasi (potret)
  section(PORTRAIT, [
    new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 60 }, children: [run('TUGAS ANALISA DAN PERANCANGAN SI', { bold: true, size: 32, color: '1F3A5F' })] }),
    new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 240 }, children: [run('Pertemuan ke-9 — ERD dan Rancangan Database', { size: 26, color: '444444' })] }),
    metaTable,
    spacer(),
    h1('Soal 1 — ERD lengkap (entitas, atribut, relasi, kardinalitas)'),
    p('ERD berikut menggambarkan seluruh entitas, atribut, relasi, dan kardinalitas sistem. Rancangan terdiri dari dua modul: modul PPDB (9 entitas yang saling berelasi) dan modul Company Profile (5 entitas yang berdiri sendiri karena hanya menyimpan konten yang dikelola panitia).'),
    p([run('Cara membaca diagram', { bold: true })], { keepNext: true }),
    notasi,
    spacer(),
    p([run('Catatan: ', { bold: true }), run('kolom bantu bawaan Laravel (created_at, updated_at) tidak digambar agar ERD fokus pada data bisnis. Waktu yang bermakna bagi proses bisnis tetap ada, yaitu tanggal_submit, published_at, dan riwayat_status.created_at.')]),
    p([run('Hasil seleksi tiap pendaftar disimpan pada kolom pendaftaran.status (diterima / tidak_diterima). Tabel pengumuman hanya mengatur kapan hasil boleh dilihat (is_published).')]),
  ]),
  // 2. ERD PPDB (lanskap)
  section(LANDSCAPE, [
    h2('1.1 ERD Modul PPDB'),
    image('erd-ppdb-k', PX_LAND_W, PX_LAND_H),
    caption('Gambar 1. ERD Modul PPDB (9 entitas) dengan atribut, relasi, dan kardinalitas'),
  ]),
  // 3. ERD profil, daftar entitas, relasi, Soal 2 (potret)
  section(PORTRAIT, [
    h2('1.2 ERD Modul Company Profile'),
    p('Lima entitas ini tidak memiliki foreign key karena kontennya berdiri sendiri.'),
    image('erd-profil-k', PX_PORTRAIT_W, 430),
    caption('Gambar 2. ERD Modul Company Profile (5 entitas)'),
    h2('1.3 Daftar entitas dan atribut'),
    table([2050, 800, 2450, 4338], ['Entitas', 'PK', 'FK', 'Atribut lain (tipe data)'], [...ppdb, ...profil].map(entityRow), { size: 18 }),
    spacer(),
    h2('1.4 Relasi dan kardinalitas'),
    relTable,
    new Paragraph({ pageBreakBefore: true, heading: HeadingLevel.HEADING_1, keepNext: true, children: [new TextRun({ text: 'Soal 2 — Detail atribut: primary key, foreign key, dan tipe data', font: FONT })] }),
    p('Setiap tabel di bawah menjelaskan identifikasi kunci dan tipe data tiap atribut. Baris berlatar kuning adalah primary key.'),
    h2('2.1 Modul PPDB'),
    ...ppdb.flatMap(detailTable),
    h2('2.2 Modul Company Profile'),
    ...profil.flatMap(detailTable),
  ]),
  // 4. Soal 3: gambar fisik PPDB (lanskap)
  section(LANDSCAPE, [
    h1('Soal 3 — Gambar rancangan database'),
    p('Rancangan database memuat 14 tabel. Gambar berikut menampilkan seluruh tabel modul PPDB beserta tipe data, penanda PK, FK, dan UK, serta garis relasi antar tabel.'),
    image('erd-ppdb-f', PX_LAND_W, PX_LAND_H - 40),
    caption('Gambar 3. Rancangan database modul PPDB (9 tabel)'),
  ]),
  // 5. Soal 3 lanjutan (potret)
  section(PORTRAIT, [
    h2('Rancangan tabel modul Company Profile'),
    image('erd-profil-f', PX_PORTRAIT_W, 400),
    caption('Gambar 4. Rancangan database modul Company Profile (5 tabel)'),
    h2('Aturan integritas data pada rancangan'),
    bullet([run('pendaftaran: ', { bold: true }), run('UNIQUE (nik, tahun_ajaran_id) — satu NIK hanya boleh satu pendaftaran per tahun ajaran (BR-02).')]),
    bullet([run('kuota_jenjang: ', { bold: true }), run('UNIQUE (tahun_ajaran_id, jenjang_id) — satu kuota untuk tiap jenjang per tahun ajaran.')]),
    bullet([run('pengumuman: ', { bold: true }), run('UNIQUE (tahun_ajaran_id, jenjang_id) — satu pengumuman untuk tiap jenjang per tahun ajaran.')]),
    bullet([run('berkas: ', { bold: true }), run('UNIQUE (pendaftaran_id, jenis_berkas_id) — satu file aktif untuk tiap jenis berkas; mengganti berkas menimpa file lama.')]),
    bullet([run('riwayat_status: ', { bold: true }), run('user_id boleh kosong untuk perubahan otomatis oleh sistem; setiap perubahan mencatat status lama, status baru, pelaku, waktu, dan catatan (BR-09).')]),
    h2('Alur singkat sistem'),
    p('Calon pendaftar membuat akun di users, lalu mengirim formulir yang tersimpan di pendaftaran (memilih tahun_ajaran aktif dan jenjang). Dokumen diunggah ke berkas sesuai jenis_berkas. Panitia memverifikasi dan mengubah status; setiap perubahan dicatat di riwayat_status. Setelah hasil ditetapkan dan pengumuman dipublikasikan, pendaftar dapat melihat hasil dan jadwal daftar ulang.'),
  ]),
];

const doc = new Document({
  creator: 'Kelompok 10', title: 'Jawaban ERD dan Rancangan Database - Pertemuan 9',
  styles: {
    default: { document: { run: { font: FONT, size: 22 } } },
    paragraphStyles: [
      { id: 'Heading1', name: 'Heading 1', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 30, bold: true, font: FONT, color: '1F3A5F' }, paragraph: { spacing: { before: 280, after: 140 }, outlineLevel: 0,
          border: { bottom: { style: BorderStyle.SINGLE, size: 8, color: '2F5D8C', space: 2 } } } },
      { id: 'Heading2', name: 'Heading 2', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 26, bold: true, font: FONT, color: '2F5D8C' }, paragraph: { spacing: { before: 220, after: 100 }, outlineLevel: 1 } },
      { id: 'Heading3', name: 'Heading 3', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 22, bold: true, font: FONT, color: '1B1B1B' }, paragraph: { spacing: { before: 160, after: 80 }, outlineLevel: 2 } },
    ],
  },
  numbering: { config: [{ reference: 'bul', levels: [{ level: 0, format: LevelFormat.BULLET, text: '•', alignment: AlignmentType.LEFT,
    style: { paragraph: { indent: { left: 540, hanging: 270 } } } }] }] },
  sections,
});

const out = path.join(__dirname, 'Jawaban ERD Pertemuan 9 - Kelompok 10.docx');
Packer.toBuffer(doc).then(buf => { fs.writeFileSync(out, buf); console.log('ok', out, buf.length); });
