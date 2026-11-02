// Membuat docs/Jawaban Perancangan Modular Pertemuan 11 - Kelompok 10.docx (A4).
// Sumber: modul-data.cjs, gambar di docs/gambar, dan potongan kode ASLI yang dibaca langsung dari app/ (tidak diketik ulang).
// Jalankan: NODE_PATH=<folder node_modules berisi docx> node docs/build-word-modul.cjs
const fs = require('fs');
const path = require('path');
const {
  Document, Packer, Paragraph, TextRun, ImageRun, Table, TableRow, TableCell, WidthType, ShadingType,
  AlignmentType, BorderStyle, TableLayoutType, HeadingLevel, PageOrientation, Footer, PageNumber, LevelFormat, VerticalAlign,
} = require('docx');
const { M, D, F } = require('./modul-data.cjs');

const ROOT = path.join(__dirname, '..');
const GAMBAR = path.join(__dirname, 'gambar');
const sizes = JSON.parse(fs.readFileSync(path.join(GAMBAR, 'ukuran-modul.json'), 'utf8'));
const FONT = 'Calibri', MONO = 'Consolas';

const A4 = { width: 11906, height: 16838 };
const PORTRAIT = { page: { size: A4, margin: { top: 1134, bottom: 1134, left: 1134, right: 1134, footer: 500 } } };
const LANDSCAPE = { page: { size: { ...A4, orientation: PageOrientation.LANDSCAPE }, margin: { top: 720, bottom: 900, left: 720, right: 720, footer: 400 } } };
const CONTENT_W = A4.width - 2268;
const PX_LAND_W = Math.floor((A4.height - 1440) / 15), PX_LAND_H = 560;

// ---------- elemen dasar ----------
const run = (text, o = {}) => new TextRun({ text, font: FONT, size: 22, ...o });
const p = (children, o = {}) => new Paragraph({ spacing: { after: 120, line: 276 }, ...o, children: Array.isArray(children) ? children : [run(children)] });
const h1 = text => new Paragraph({ heading: HeadingLevel.HEADING_1, keepNext: true, children: [new TextRun({ text, font: FONT })] });
const h2 = text => new Paragraph({ heading: HeadingLevel.HEADING_2, keepNext: true, children: [new TextRun({ text, font: FONT })] });
const h3 = text => new Paragraph({ heading: HeadingLevel.HEADING_3, keepNext: true, children: [new TextRun({ text, font: FONT })] });
const caption = text => new Paragraph({ alignment: AlignmentType.CENTER, spacing: { before: 80, after: 120 }, children: [run(text, { italics: true, size: 20, color: '444444' })] });
const bullet = children => new Paragraph({ numbering: { reference: 'bul', level: 0 }, spacing: { after: 60, line: 276 }, children: Array.isArray(children) ? children : [run(children)] });
const spacer = () => new Paragraph({ spacing: { after: 160 }, children: [] });

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
  const body = bodyRows.map(r => new TableRow({ cantSplit: true, children: r.map((c, i) => {
    const spec = typeof c === 'object' && c !== null && !Array.isArray(c) && c.text !== undefined ? c : { text: c };
    return cell(spec.text, widths[i], { mono: spec.mono, bold: spec.bold, fill: spec.fill, size: o.size });
  }) }));
  return new Table({ width: { size: total, type: WidthType.DXA }, columnWidths: widths, layout: TableLayoutType.FIXED, rows: [head, ...body] });
}

// ---------- potongan kode asli ----------
const src = rel => fs.readFileSync(path.join(ROOT, rel), 'utf8').replace(/\r\n/g, '\n').split('\n');

/** Ambil method (beserta docblock di atasnya) dari berkas rel, dikenali lewat potongan tanda tangannya. */
function method(rel, signature) {
  const L = src(rel);
  const i = L.findIndex(l => l.includes(signature));
  if (i < 0) throw new Error(`tidak ditemukan: ${signature} di ${rel}`);
  let a = i;
  while (a > 0 && /^\s*(\/\*\*|\*|\*\/)/.test(L[a - 1])) a--;
  let b = i;
  while (b < L.length && L[b] !== '    }') b++;
  return { rel, from: a + 1, to: b + 1, text: L.slice(a, b + 1).join('\n') };
}
const whole = rel => ({ rel, from: 1, to: src(rel).length, text: src(rel).join('\n').trimEnd() });

function code(snip, judul) {
  const lines = snip.text.split('\n');
  const label = `${snip.rel.replace(/\\/g, '/')}  (baris ${snip.from}–${snip.to})`;
  const mk = (t, o = {}) => new Paragraph({
    spacing: { after: 0, line: 240 }, keepLines: true, shading: { type: ShadingType.CLEAR, fill: o.fill || 'F4F4F4', color: 'auto' },
    border: { left: { style: BorderStyle.SINGLE, size: 12, color: o.bar || '8A8A8A', space: 4 } },
    children: [new TextRun({ text: t === '' ? ' ' : t, font: MONO, size: 15, bold: !!o.bold, color: o.color })],
  });
  return [
    new Paragraph({ keepNext: true, spacing: { before: 120, after: 40 }, children: [run(judul, { bold: true, size: 21 }), run('   ' + label, { size: 17, color: '555555' })] }),
    ...lines.map(l => mk(l)),
    new Paragraph({ spacing: { after: 100 }, children: [] }),
  ];
}

// ---------- isi ----------
const meta = (k, v) => new TableRow({ children: [cell(k, 2000, { bold: true, fill: 'EEF3F8', size: 21 }), cell(v, 7638, { size: 21 })] });
const metaTable = new Table({ width: { size: CONTENT_W, type: WidthType.DXA }, columnWidths: [2000, 7638], layout: TableLayoutType.FIXED, rows: [
  meta('Kelompok', '10'),
  new TableRow({ children: [cell('Anggota', 2000, { bold: true, fill: 'EEF3F8', size: 21 }),
    cell(['Ghibran Aryasena Avianto — 231011750061', 'Salsabillah — 231011750247', 'Gilang Ramadhan — 231011750251'], 7638, { size: 21 })] }),
  meta('Sistem', 'Sistem Company Profile dan Pendaftaran Siswa Baru (SPMB) berbasis web — Sirojul Athfal, jenjang MI dan SMP (hasil Kerja Praktik)'),
  meta('Teknologi', 'PHP 8.1, Laravel 10 (pola MVC), MySQL, Blade; pengujian dengan PHPUnit'),
] });

const tabelModul = table([700, 1900, 4538, 2500], ['Kode', 'Modul', 'Fungsi', 'Class pelaksana'],
  M.map(m => [{ text: m.id, bold: true }, { text: m.judul, bold: true }, m.fungsi, { text: m.kelas, mono: true }]), { size: 16 });

const tabelSub = id => {
  const m = M.find(x => x.id === id);
  return table([700, 2300, 6638], ['Kode', 'Submodul', 'Antarmuka (route dan method)'], m.sub.map(([k, l, r]) => [k, l, { text: r, mono: true }]), { size: 17 });
};

const tabelStore = table([700, 3300, 5638], ['Kode', 'Tabel', 'Isi'], D.map(d => [{ text: d.id, bold: true }, { text: d.nama, mono: true }, d.isi]), { size: 18 });

const tabelAlur = table([620, 1250, 1250, 3500, 3018], ['No', 'Dari', 'Ke', 'Data yang mengalir', 'Pemicu / pelaksana'],
  F.map(f => [{ text: f[0], bold: true }, f[1], f[2], f[3], { text: f[4], mono: true }]), { size: 17 });

const modulIO = table([700, 2650, 3100, 3188], ['Kode', 'Masukan (input)', 'Proses', 'Keluaran (output)'], [
  ['M1', 'Permintaan halaman; data konten (D5) dan gelombang (D3)', 'Mengambil data lalu merender halaman Blade; galeri difilter menurut unit', 'Halaman HTML publik'],
  ['M2', 'Email atau nomor HP + sandi; data registrasi', 'Normalisasi nomor HP, validasi, Auth::attempt, hash sandi, cek peran per permintaan', 'Sesi login, pengalihan sesuai peran, atau pesan kesalahan'],
  ['M3', 'Formulir siswa, berkas, perintah kirim; aturan gelombang dari M5', 'Validasi, aturan buka/tutup, cek NIK ganda, simpan, ubah status Draft → Menunggu', 'Pendaftaran tersimpan, kode pendaftaran, bukti PDF, tampilan status/hasil'],
  ['M4', 'Keputusan admin; pendaftar Menunggu/Terverifikasi (D2); kuota (M5)', 'Verifikasi, ubah status + catat riwayat dalam transaksi, hitung kuota, terbitkan pengumuman', 'Status baru, riwayat status, peringatan kuota, pengumuman terbit, CSV'],
  ['M5', 'Isian gelombang, kuota, biaya', 'Validasi tanggal, simpan dalam transaksi, hanya satu gelombang aktif', 'Gelombang aktif, status dibuka/tutup, kuota, rincian biaya'],
  ['M6', 'Isian konten dan foto dari admin', 'Validasi, simpan foto, cek kelayakan foto hero', 'Konten halaman publik terbaru'],
], { size: 17 });

// Potongan kode asli
const kAuthLogin = method('app/Http/Controllers/AuthController.php', 'public function login(');
const kAuthRegister = method('app/Http/Controllers/AuthController.php', 'public function register(');
const kAuthPhone = method('app/Http/Controllers/AuthController.php', 'private function normalizePhone(');
const kRole = whole('app/Http/Middleware/EnsureRole.php');
const kVerifikasi = method('app/Http/Controllers/AdminController.php', 'public function verifikasi(');
const kHasil = method('app/Http/Controllers/AdminController.php', 'public function simpanHasil(');
const kPublikasi = method('app/Http/Controllers/AdminController.php', 'public function publikasi(');
const kUbah = method('app/Models/Pendaftaran.php', 'public function ubahStatus(');
const kTerbit = method('app/Models/Gelombang.php', 'public function hasilTerbit(');

const tesAuth = [
  ['test_login_dengan_email_dan_dengan_nomor_hp_berformat_bebas', 'Login lewat email (huruf besar-kecil bebas) dan lewat nomor HP berawalan +62 dengan tanda hubung.'],
  ['test_sandi_salah_ditolak_tanpa_membocorkan_mana_yang_salah', 'Sandi salah atau akun tidak ada menghasilkan pesan yang sama; pengguna tetap tamu.'],
  ['test_registrasi_selalu_membuat_peran_pendaftar_walau_dikirim_role_admin', 'Field role yang diselipkan di formulir diabaikan; nomor HP dinormalkan; sandi tersimpan sebagai hash.'],
  ['test_registrasi_menolak_sandi_lemah_dan_email_ganda', 'Sandi tanpa angka dan email yang sudah terpakai ditolak.'],
  ['test_peran_membatasi_halaman_yang_boleh_dibuka', 'Tamu dialihkan ke login; wali tidak bisa membuka /admin; admin tidak masuk area wali.'],
  ['test_logout_mengakhiri_sesi', 'Setelah logout pengguna kembali menjadi tamu.'],
];
const tesSeleksi = [
  ['test_perbaikan_wajib_bercatatan_lalu_status_dan_riwayat_tercatat', 'Permintaan perbaikan tanpa catatan ditolak; dengan catatan status berubah dan pelaku (admin) tercatat di riwayat.'],
  ['test_hanya_status_menunggu_yang_dapat_diverifikasi', 'Pendaftar yang sudah Diterima tidak bisa diverifikasi ulang.'],
  ['test_hasil_ditetapkan_dan_peringatan_muncul_bila_melebihi_kuota', 'Dua Diterima pada kuota 1 tersimpan dan memunculkan peringatan "SMP (2/1)".'],
  ['test_publikasi_ditolak_selama_masih_ada_yang_belum_ditetapkan', 'Publikasi gagal selama masih ada pendaftar Terverifikasi tanpa keputusan.'],
  ['test_hasil_tersembunyi_dari_pendaftar_sampai_dipublikasikan_lalu_terkunci', 'Sebelum terbit hasil tidak terlihat oleh pendaftar; sesudah terbit terlihat dan tidak bisa diubah lagi.'],
  ['test_wali_tidak_dapat_menetapkan_hasil', 'Wali yang memanggil route admin dialihkan dan status tidak berubah.'],
];

const sections = [
  // ----- halaman judul + Soal 1 (potret)
  { properties: PORTRAIT, children: [
    new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 60 }, children: [run('TUGAS ANALISA DAN PERANCANGAN SI', { bold: true, size: 32, color: '1F3A5F' })] }),
    new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 240 }, children: [run('Pertemuan ke-11 — Perancangan Proses dan Struktur Modular', { size: 26, color: '444444' })] }),
    metaTable,
    spacer(),
    h1('Soal 1 — Diagram struktur program'),
    p('Sistem dipecah secara hierarkis menjadi enam modul utama, dan tiap modul dipecah lagi menjadi submodul (fungsi). Data dipakai bersama lewat satu lapisan Model Eloquent yang memuat aturan bisnisnya, bukan dengan saling membaca tabel. Pemecahan mengikuti kebutuhan pengguna (pengunjung, wali calon siswa, admin) dan satu tujuan utama per modul, sehingga tiap modul dapat dipahami, diuji, dan diubah tanpa harus membaca modul lain. Diagram pada halaman berikut memakai kode M1–M6 untuk modul dan 1.1–6.4 untuk submodul; kode yang sama dipakai di seluruh dokumen.'),
    p([run('Dasar pemecahan modul', { bold: true })], { keepNext: true }),
    bullet([run('Satu tujuan per modul (kohesi tinggi): ', { bold: true }), run('M2 hanya mengurus identitas dan akses; M5 hanya mengurus jadwal, kuota, dan biaya gelombang; M6 hanya mengurus isi halaman publik.')]),
    bullet([run('Dipisah menurut pengguna: ', { bold: true }), run('M1 untuk pengunjung, M3 untuk wali, M4–M6 untuk admin. Dengan begitu aturan hak akses cukup dipasang di pintu masuk tiap kelompok route.')]),
    bullet([run('Dipisah menurut tahap proses pendaftaran: ', { bold: true }), run('mengisi dan mengunggah (M3) → memeriksa dan memutuskan (M4), dengan aturan jadwal yang dipasok M5.')]),
    spacer(),
    p([run('Daftar modul utama', { bold: true })], { keepNext: true }),
    tabelModul,
  ] },
  { properties: LANDSCAPE, children: [
    h2('Gambar diagram struktur program'),
    image('struktur-modul', PX_LAND_W, PX_LAND_H),
    caption('Gambar 1. Diagram struktur program Sistem Company Profile & SPMB Sirojul Athfal (6 modul, 34 submodul). Diagram ini hanya menunjukkan hierarki; hubungan antar modul ada pada Gambar 2.'),
  ] },
  { properties: PORTRAIT, children: [
    h2('Rincian submodul dan antarmukanya'),
    p('Setiap submodul di diagram punya titik masuk yang nyata di aplikasi (route dan method). Tabel berikut menghubungkan diagram dengan kode.'),
    ...M.flatMap(m => [h3(`${m.id} — ${m.judul}`), tabelSub(m.id), spacer()]),
  ] },

  // ----- Soal 2 (lanskap gambar, lalu potret tabel)
  { properties: LANDSCAPE, children: [
    h1('Soal 2 — Interaksi modul'),
    p('Gambar berikut menunjukkan bagaimana modul saling berhubungan: siapa yang memicu modul (aktor), data apa yang mengalir antar modul, dan tabel mana yang dibaca atau ditulis. Nomor F1–F21 dijelaskan pada Tabel Alur Data di halaman berikutnya.'),
    image('interaksi-modul', PX_LAND_W, PX_LAND_H - 40),
    caption('Gambar 2. Diagram interaksi modul dan alur data (garis tebal = alur utama, garis putus-putus = alur pendukung)'),
  ] },
  { properties: PORTRAIT, children: [
    h2('2.1 Fungsi, masukan, proses, dan keluaran tiap modul'),
    modulIO,
    spacer(),
    h2('2.2 Penyimpanan data'),
    tabelStore,
    spacer(),
    h2('2.3 Tabel alur data'),
    p('Setiap baris adalah satu alur data pada Gambar 2 (F1–F18 alur utama, F19–F21 alur pendukung). Kolom terakhir menunjuk pelaksananya di kode aplikasi.'),
    tabelAlur,
    spacer(),
    h2('2.4 Skenario alur antar modul dari awal sampai akhir'),
    p([run('Skenario A — wali mendaftar dan mengetahui hasil', { bold: true })], { keepNext: true }),
    bullet('F5, F6: wali membuat akun di M2; akun disimpan di D1 dengan peran pendaftar dan sandi ter-hash.'),
    bullet('F7, F18: EnsureRole (M2) mengizinkan wali masuk ke M3 (admin yang mencoba masuk dialihkan ke area admin lewat filter yang sama); M3 menanyakan ke M5 apakah ada gelombang yang sedang dibuka dan berapa biayanya.'),
    bullet('F8, F9: wali mengirim formulir dan mengunggah berkas; M3 menyimpannya ke D2. Begitu semua berkas wajib lengkap, status otomatis menjadi Menunggu Verifikasi dan riwayat dicatat.'),
    bullet('F19, F10, F12, F13: setelah EnsureRole memastikan perannya admin, admin melihat daftar pendaftar di M4, memverifikasi atau meminta perbaikan; setiap perubahan status masuk ke D2 bersama riwayatnya.'),
    bullet('F12, F17, F13: admin menetapkan Diterima atau Tidak Diterima; M4 membandingkan jumlah Diterima dengan kuota dari M5 dan memberi peringatan bila melebihi.'),
    bullet('F12, F14, F11: admin mempublikasikan pengumuman (D3); sejak saat itu M3 menampilkan hasil kepada wali dan mengunci perubahan.'),
    spacer(),
    p([run('Skenario B — admin menyiapkan gelombang baru', { bold: true })], { keepNext: true }),
    bullet('F15, F16: admin mengisi tanggal, kuota MI dan SMP, serta biaya di M5; semuanya disimpan dalam satu transaksi dan hanya satu gelombang yang boleh aktif.'),
    bullet('F21, F18: halaman publik (M1) dan portal wali (M3) langsung memakai gelombang aktif itu tanpa perubahan kode.'),
    spacer(),
    p([run('Skenario C — wali lupa kata sandi', { bold: true })], { keepNext: true }),
    bullet('M2 menampilkan petunjuk menghubungi panitia lewat WhatsApp (2.5). Admin memakai M4 (4.3) untuk membuat sandi sementara; sandi ditulis ke D1 (F20) dan tampil sekali. Saat wali login, EnsureRole (M2) memaksa ganti sandi lebih dulu.'),
    spacer(),
    h2('2.5 Evaluasi kohesi dan keterkaitan (coupling)'),
    p('Hasil penilaian terhadap struktur yang sebenarnya ada di kode saat ini, termasuk kelemahannya:'),
    table([2200, 3800, 3638], ['Aspek', 'Temuan', 'Saran'], [
      [{ text: 'Kohesi M2', bold: true }, 'Fungsional: semua method bertujuan satu, yaitu mengenali pengguna dan mengatur aksesnya.', 'Dipertahankan.'],
      [{ text: 'Kohesi M4 dan M5', bold: true }, 'Secara logika dua modul, tetapi secara fisik berada dalam satu class AdminController (±380 baris) bersama helper gelombang.', 'Pisahkan menjadi SeleksiController dan GelombangController.'],
      [{ text: 'Coupling M3 ↔ M5', bold: true }, 'Coupling data: M3 hanya bergantung pada hasil Gelombang::aktif() dan sedangDibuka(), bukan pada isi tabel gelombang.', 'Dipertahankan; aturan buka-tutup tetap satu tempat (BR-01).'],
      [{ text: 'Coupling M3 ↔ M4', bold: true }, 'Tidak saling memanggil; keduanya hanya bertemu di D2 lewat Pendaftaran::ubahStatus() sebagai satu-satunya pintu perubahan status.', 'Dipertahankan.'],
      [{ text: 'Aturan bisnis di M3', bold: true }, 'PendaftarController (±415 baris) memuat banyak aturan gelombang (terlewat, bolehLanjut, bolehDaftarLagi) sebagai method private.', 'Pindahkan aturan itu ke model Pendaftaran atau class layanan agar mudah diuji tanpa HTTP.'],
      [{ text: 'Coupling M2 → modul lain', bold: true }, 'Coupling kendali ringan: EnsureRole menerima daftar peran sebagai parameter dan mengalihkan pengguna.', 'Dipertahankan.'],
    ], { size: 17 }),
  ] },

  // ----- Soal 3
  { properties: PORTRAIT, children: [
    h1('Soal 3 — Implementasi kode dua modul'),
    p('Bahasa yang dipakai adalah PHP 8.1 dengan framework Laravel 10, yaitu bahasa dan framework yang sama dengan sistem hasil Kerja Praktik. Dua modul yang dipilih adalah M2 (Autentikasi & Akun) dan M4 (Verifikasi & Seleksi). Keduanya dipilih karena M2 adalah pintu masuk yang menentukan keamanan seluruh sistem, sedangkan M4 memuat aturan bisnis inti: status pendaftaran, kuota, dan pengumuman.'),
    p([run('Seluruh potongan kode di bawah dibaca langsung dari berkas aplikasi yang berjalan (nomor baris tertera), bukan contoh terpisah. ', { bold: true }), run('Untuk membuktikan kedua modul bekerja, ditulis 12 uji otomatis baru (ModulAutentikasiTest dan ModulSeleksiTest).')]),
    table([2300, 7338], ['Modul', 'Berkas'], [
      [{ text: 'M2 Autentikasi & Akun', bold: true }, { text: 'app/Http/Controllers/AuthController.php, app/Http/Controllers/AkunController.php, app/Http/Middleware/EnsureRole.php, app/Models/User.php, resources/views/auth/*', mono: true }],
      [{ text: 'M4 Verifikasi & Seleksi', bold: true }, { text: 'app/Http/Controllers/AdminController.php, app/Models/Pendaftaran.php (ubahStatus), app/Models/Gelombang.php (hasilTerbit), app/Models/RiwayatStatus.php, resources/views/admin/pendaftar.blade.php, hasil-seleksi.blade.php', mono: true }],
      [{ text: 'Pengujian', bold: true }, { text: 'tests/Feature/ModulAutentikasiTest.php, tests/Feature/ModulSeleksiTest.php', mono: true }],
    ], { size: 17 }),
    spacer(),

    h2('3.1 Modul M2 — Autentikasi & Akun'),
    p([run('Antarmuka modul: ', { bold: true }), run('POST /login, POST /register, POST /logout, POST /ganti-sandi, dan middleware role:... yang dipasang pada kelompok route /pendaftar dan /admin. Masukan: kredensial. Keluaran: sesi dan pengalihan sesuai peran.')]),
    p([run('Keputusan rancangan yang tampak di kode:', { bold: true })], { keepNext: true }),
    bullet('Login menerima email atau nomor HP; nomor dinormalkan (+62…, 62…, 08…) agar satu orang tidak punya banyak bentuk.'),
    bullet('Pesan gagal login sengaja sama untuk akun tidak ada dan sandi salah.'),
    bullet('Peran tidak masuk daftar fillable: akun dari formulir registrasi selalu pendaftar, sehingga orang tidak bisa mendaftarkan diri sebagai admin.'),
    bullet('Sandi di-hash otomatis oleh cast hashed; sesi diregenerasi setelah login dan registrasi.'),
    ...code(kAuthLogin, 'Login'),
    ...code(kAuthRegister, 'Registrasi'),
    ...code(kAuthPhone, 'Normalisasi nomor HP'),
    ...code(kRole, 'Filter peran (middleware)'),

    h2('3.2 Modul M4 — Verifikasi & Seleksi'),
    p([run('Antarmuka modul: ', { bold: true }), run('POST /admin/pendaftar/{id}/verifikasi, POST /admin/hasil-seleksi, POST /admin/hasil-seleksi/publikasi. Masukan: keputusan admin. Keluaran: status baru, riwayat, peringatan kuota, dan pengumuman terbit.')]),
    p([run('Keputusan rancangan yang tampak di kode:', { bold: true })], { keepNext: true }),
    bullet('Semua perubahan status melewati satu method, Pendaftaran::ubahStatus(), sehingga riwayat selalu tercatat (BR-09).'),
    bullet('Verifikasi dan penetapan hasil berjalan dalam DB::transaction agar status dan berkas tidak setengah berubah.'),
    bullet('Permintaan perbaikan wajib bercatatan (BR-05); publikasi ditolak selama masih ada pendaftar Terverifikasi tanpa keputusan.'),
    bullet('Melebihi kuota tidak diblokir, tetapi diberi peringatan (FR-37) karena keputusan akhir ada pada panitia.'),
    bullet('Hasil disembunyikan dari pendaftar sampai pengumuman terbit (BR-07) lewat Gelombang::hasilTerbit() dan dikunci setelahnya.'),
    ...code(kVerifikasi, 'Verifikasi berkas'),
    ...code(kHasil, 'Penetapan hasil dan peringatan kuota'),
    ...code(kPublikasi, 'Publikasi pengumuman'),
    ...code(kUbah, 'Perubahan status + riwayat'),
    ...code(kTerbit, 'Penanda hasil sudah terbit'),

    h2('3.3 Pengujian'),
    p('Pengujian berupa feature test PHPUnit yang memanggil route sungguhan terhadap basis data (dibungkus transaksi sehingga data tidak tersisa).'),
    h3('ModulAutentikasiTest (M2) — 6 uji'),
    table([4300, 5338], ['Nama uji', 'Yang diperiksa'], tesAuth.map(([a, b]) => [{ text: a, mono: true }, b]), { size: 16 }),
    spacer(),
    h3('ModulSeleksiTest (M4) — 6 uji'),
    table([4300, 5338], ['Nama uji', 'Yang diperiksa'], tesSeleksi.map(([a, b]) => [{ text: a, mono: true }, b]), { size: 16 }),
    spacer(),
    p([run('Hasil: ', { bold: true }), run('12 uji baru lulus. Seluruh rangkaian uji aplikasi (termasuk modul lain) menghasilkan OK (55 tests, 378 assertions) pada 27 Oktober 2026.')]),
    p([run('Cara menjalankan: ', { bold: true }), run('php vendor/bin/phpunit --filter "ModulAutentikasiTest|ModulSeleksiTest"', { font: MONO, size: 19 })]),
    spacer(),
    h2('Kesimpulan'),
    p('Struktur modular sistem terdiri dari enam modul dengan satu tujuan masing-masing; interaksi antar modul terjadi lewat sedikit titik temu yang jelas (middleware peran, Model Gelombang, dan Pendaftaran::ubahStatus), bukan lewat pemanggilan langsung antar controller. Dua modul (M2 dan M4) diimplementasikan penuh pada kode yang berjalan dan terbukti lewat 12 uji otomatis. Kelemahan yang masih ada, yaitu controller yang terlalu besar, dicatat pada bagian 2.5 beserta saran pemisahannya.'),
  ] },
];

const footer = new Footer({ children: [new Paragraph({ alignment: AlignmentType.CENTER, children: [
  new TextRun({ text: 'Kelompok 10 — Perancangan Proses dan Struktur Modular  |  Halaman ', font: FONT, size: 18, color: '666666' }),
  new TextRun({ children: [PageNumber.CURRENT], font: FONT, size: 18, color: '666666' })] })] });

const doc = new Document({
  creator: 'Kelompok 10', title: 'Jawaban Perancangan Proses dan Struktur Modular - Pertemuan 11',
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
  sections: sections.map(s => ({ ...s, footers: { default: footer } })),
});

const out = path.join(__dirname, 'Jawaban Perancangan Modular Pertemuan 11 - Kelompok 10_rev2.docx');
Packer.toBuffer(doc).then(buf => { fs.writeFileSync(out, buf); console.log('ok', out, buf.length); });
