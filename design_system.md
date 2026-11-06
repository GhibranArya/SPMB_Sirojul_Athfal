# Design System — SMP Islam Sirojul Athfal

> **Pembaruan 3 Nov 2026:** tampilan diganti ke gaya **Tegas** (`public/css/tegas.css`, dimuat paling akhir di semua halaman). Latar putih/mint, hijau blok penuh dengan pola gelombang, aksen kuning, bingkai gelap 2px dengan bayangan keras, judul Bricolage Grotesque, hero beranda terbelah (teks kiri, foto kanan). Bagian di bawah yang menyebut krem, serif Fraunces, bintang 8 sudut, dan bingkai tipis sudah tidak berlaku; token terbaru ada di `:root` pada `tegas.css`.

Dokumen ini merangkum tampilan yang **sudah ada** di aplikasi (per 30 Oktober 2026), dibaca dari `public/css/*.css`, layout Blade, dan view. Pakai sebagai acuan untuk halaman atau komponen baru agar tetap konsisten.

**Karakter visual:** hangat, editorial, dan tepercaya dengan nuansa islami. Latar krem kertas, hijau zamrud sebagai warna utama, emas sebagai aksen kecil, judul berhuruf serif, dan pola bintang delapan sudut yang sangat tipis. Bentuknya persegi halus dengan bingkai tipis, hampir tanpa bayangan.

Draf awal dokumen ini (Okt 2026) menggambarkan gaya putih dengan sudut membulat besar dan bayangan. Gaya itu sudah diganti oleh `theme.css`. Nilai lama yang masih tersisa di `style.css` hanya dipakai bila tidak ditimpa `theme.css`.

---

## 1. Prinsip

1. **Hangat dan lega:** ruang kosong luas, latar krem, bingkai 1px, bukan bayangan tebal.
2. **Hijau untuk aksi, emas untuk penekanan kecil:** emas dipakai untuk garis, penanda aktif, eyebrow, dan satu CTA per tampilan. Jangan menjadikan semua tombol emas.
3. **Judul serif, isi sans:** Fraunces untuk judul dan angka besar, Plus Jakarta Sans untuk selebihnya.
4. **Mudah dibaca orang tua:** isi 16px, label form selalu terlihat, kontras tinggi.
5. **Mobile-first:** sebagian besar pengguna membuka dari ponsel.
6. **Satu sumber token:** warna, radius, dan bayangan memakai variabel `:root`. Hindari menulis hex baru di view.
7. **Teks situs mengikuti dokumen KSP.** Jangan mengubah kata-kata atau menambah klaim yang tidak ada di dokumen sumber.

---

## 2. Berkas Gaya dan Urutan Muat

| Berkas | Isi | Dimuat di |
|---|---|---|
| `public/css/style.css` | Token dasar, tombol, navbar, grid, kartu, footer, tabel (basis lama) | Semua halaman |
| `public/css/theme.css` | **Override tema**: token krem/serif, tombol persegi, navbar, kartu, footer, carousel eskul, visi-misi | Semua halaman |
| `public/css/hero.css` | Hero beranda layar penuh (`.hx-*`, `.hc-*`) | Beranda |
| `public/css/galeri.css` | Galeri kolom bata tanpa crop, filter MI/SMP, penampil foto (`<dialog>`); skrip pendamping `public/js/galeri.js` | Beranda dan `/galeri` |
| `public/css/auth.css` | Login/register (`.auth-*`), `.alert`, `.field-error`, `.check` | Auth dan panel |
| `public/css/panel.css` | Portal pendaftar dan panel admin (termasuk halaman Konten Situs) | Panel |

Urutan: `style.css` → `theme.css` → (`hero.css` dan/atau `galeri.css` | `auth.css` → `panel.css`). Font Awesome 6.4 dari cdnjs; Google Fonts memuat Fraunces dan Plus Jakarta Sans.

Layout Blade: `layouts/auth.blade.php`, `layouts/pendaftar.blade.php`, `layouts/admin.blade.php`, dengan `partials/panel-head.blade.php` (head bersama), `partials/flash.blade.php` (pesan sukses/peringatan/error), dan `partials/publik-{nav,footer,cta}.blade.php`. Halaman publik masih memiliki `<head>` sendiri di tiap view (lihat bagian 8).

---

## 3. Design Tokens

### 3.1 Warna

| Token | Nilai | Pemakaian |
|---|---|---|
| `--primary` | `#0D6E3E` | Tombol utama, tautan aktif, ikon, kata kunci judul |
| `--primary-dark` | `#0A5A32` | Hover tombol, hero halaman, kartu status, header tabel publik |
| `--primary-light` | `#15A35B` | Ikon centang/sukses |
| `--primary-pale` | `#EBF5F0` | Latar ikon, badge, pilihan terpilih |
| `--accent` | `#D29D29` | Aksen emas: penanda, ring langkah aktif, ikon daftar |
| `--accent-hover` | `#B88923` | Eyebrow, label seksi form, hover aksen |
| `--paper` | `#FBF8F1` | **Latar halaman** (pengganti putih) |
| `--paper-deep` | `#F3EEE2` | Seksi selang-seling, kotak fitur, latar hasil "menunggu" |
| `--ink` | `#1F2A24` | Heading, teks tegas |
| `--line` | `#E4DDCB` | Bingkai dan pemisah |
| `--white` | `#FFFFFF` | Kartu, input, header panel |
| `--gray-700` | `#4A5568` | Teks isi sekunder |
| `--gray-500` | `#718096` | Teks pendukung, placeholder |
| `--gray-300` / `--gray-100` | `#E2E8F0` / `#F7FAFC` | Sisa token lama |

Teks isi `body` memakai `#44504A` dan deskripsi seksi `#5C6862`. Footer dan sidebar admin memakai `#14201A`. Hero memakai `--hx-deep: 5, 38, 23` (hijau tua, untuk `rgba`) dan `--hx-gold: #F0C45A`. Rasio kontras teks minimal 4.5:1.

### 3.2 Status

Badge `.st` (pil, titik di depan). Warna **jangan menjadi satu-satunya penanda**: selalu sertakan teks.

| Kelas | Teks | Latar | Dipakai untuk |
|---|---|---|---|
| `.st-gray` | `#4A5568` | `#EDF0F3` | Draft, belum diunggah, tidak aktif |
| `.st-gold` | `#8A6A14` | `#FBF3DE` | Menunggu Verifikasi, dijadwalkan, sedang diperiksa |
| `.st-orange` | `#B24A12` | `#FEEBC8` | Perlu Perbaikan |
| `.st-blue` | `#2B6CB0` | `#E3EEFB` | Terverifikasi |
| `.st-green` | `#0A5A32` | `#DDF1E6` | Diterima, berkas valid, gelombang dibuka |
| `.st-red` | `#C53030` | `#FDE2E2` | Tidak Diterima, berkas ditolak |

Pemetaan status pendaftaran berada di `Pendaftaran::STATUS` (label dan kelas), jadi jangan menulis ulang di view. Tidak ada status "Cadangan" karena daftar tunggu tidak ada di v1.

### 3.3 Tipografi

- **Judul:** Fraunces (500/600, miring 500 untuk kata kunci), token `--font-display`. Diterapkan ke `.hero-title`, `.section-title`, `.page-hero h1`, judul kartu, `.brand-name`, dan semua `h1–h3` di `.panel`.
- **Isi:** Plus Jakarta Sans 400–800 (`--font-main`), line-height isi 1.6.
- **Ikon:** Font Awesome 6.4 (`fa-solid`, `fa-regular`, `fa-brands`).

| Peran | Ukuran | Catatan |
|---|---|---|
| Judul hero beranda (`.hx-title`) | responsif besar | Kata kunci `.hl` berwarna emas miring |
| Judul seksi (`.section-title`) | `clamp(1.8rem, 3vw, 2.4rem)` | Kata kunci dalam `<span>` berwarna `--primary`, miring |
| Judul halaman panel (`.page-title h1`) | 1.9rem (1.6rem di ponsel) | |
| Judul kartu (`.card h3`) | 1.15rem | |
| Isi | 1rem | `#44504A` |
| Deskripsi pendukung | 0.86–0.95rem | `--gray-500` |
| Eyebrow (`.section-badge-sm`) | 0.72rem, `letter-spacing: 0.18em`, bobot 600 | Berwarna `--accent-hover`, diapit garis 28px, bukan ikon |
| Label seksi form (`.form-section`) | 0.74rem, huruf kapital, `--accent-hover` | Diikuti garis |
| Angka besar (`.stat b`, `.countdown b`) | 1.6–2rem Fraunces | |

### 3.4 Spasi dan Tata Letak

- `.container`: lebar maks 1200px, padding samping 20px.
- `.section`: padding vertikal **96px** (`theme.css`); kurangi di ponsel.
- Grid: `.grid-2`, `.grid-3`, `.grid-4` dengan jarak 20px; kartu besar 24–32px.
- Portal pendaftar (`.pd-wrap`): lebar maks **1040px**. Grid dua kolom `.pd-grid` (`1fr 320px`).
- Panel admin: `.ad-shell` dengan sidebar 250px dan konten `.ad-content` berpadding 28px.
- Auth: kartu di tengah (`.auth-main`), pita hijau melengkung di atas.

### 3.5 Radius, Bayangan, Pola

| Token | Nilai | Pemakaian |
|---|---|---|
| `--radius-sm` | 4px | Tombol, input (`.form-control` memakai `--radius-sm`) |
| `--radius-md` | 6px | Kartu, tabel publik |
| `--radius-lg` | 10px | Hero status, modal, blok besar |
| pil | 999px | Badge `.st`, tautan pil di bilah auth |
| lingkaran | 50% | Avatar, titik langkah |

Bayangan hampir dihapus: `--shadow-sm: none`, `--shadow-md: 0 1px 0 rgba(31,42,36,.06)`, `--shadow-lg: 0 18px 40px -22px rgba(31,42,36,.35)` (hanya untuk elemen melayang). Pemisah visual berasal dari bingkai `1px solid var(--line)`.

**Pola bintang delapan sudut** (satu warna, sangat tipis): `--pattern-green` untuk latar terang, `--pattern-light` di atas hijau/gelap (hero, kartu status, sidebar admin, pita auth). Jangan menambah pola lain.

### 3.6 Gerak dan Breakpoint

- `--transition: 0.3s ease` untuk hover warna dan border. Kartu tidak diangkat saat hover (`transform: none`), cukup border menjadi hijau.
- Semua animasi berulang menghormati `prefers-reduced-motion` (hero carousel berhenti, carousel eskul tanpa animasi). Pertahankan ini untuk fitur baru.
- Breakpoint yang dipakai: 1100, 1024, 992, 900 (panel), 768, 640, 480 px. Yang utama: ≤ 992px hero dan grid menumpuk, ≤ 900px sidebar admin menjadi drawer, ≤ 768px grid satu kolom dan navbar hamburger, ≤ 640px form satu kolom.

---

## 4. Komponen Publik

### 4.1 Tombol (`.btn`)

Persegi halus (radius 4px), bobot 600, tanpa bayangan.

| Kelas | Tampilan | Kapan dipakai |
|---|---|---|
| `.btn-primary` | Hijau, teks putih | Aksi utama |
| `.btn-primary-light` | Hijau terang | Aksi sekunder positif |
| `.btn-accent` | Emas, teks **putih** tebal dengan `text-shadow` tipis | CTA penekanan (Daftar). Maksimal satu per tampilan |
| `.btn-white`, `.btn-outline-light` | Transparan, border dan teks hijau tua; hover terisi hijau | Aksi sekunder/tersier |
| `.btn-ghost-light` | Garis putih di atas latar gelap | Tombol di hero |
| `.btn-lg` | Padding lebih besar | CTA hero |
| `.btn-danger`, `.btn-sm` | Merah `#C53030`; padding kecil | Tombol tolak/hapus; aksi di tabel (hanya di panel) |

### 4.2 Navbar dan Footer

- Navbar `fixed`, latar krem transparan dengan blur, logo gambar `images/logo/logo.png`, nama `.brand-name` (serif). Tautan aktif berwarna hijau dengan penanda belah ketupat emas kecil di bawahnya. Tombol "Daftar PPDB Online" ada di kanan; di ponsel masuk ke menu (`.nav-daftar`).
- Footer gelap `#14201A`, kolom brand, tautan cepat, dan kontak (alamat dari `profil_yayasan`).
- Keduanya berupa partial: ubah di `partials/publik-nav.blade.php` dan `publik-footer.blade.php`.

### 4.3 Header Seksi

`.section-badge-sm` (eyebrow) → `.section-title` dengan `<span>` → `.section-desc` (maks 600px, tengah). Gunakan `.section-header` (tengah) atau `.section-header-flex` (judul kiri, aksi kanan). Seksi selang-seling memakai `--paper` dan `--paper-deep`.

### 4.4 Hero

- **Beranda** (`hero.css`): foto kegiatan layar penuh bergantian di latar (`.hc-slide`), selubung hijau tua, teks putih, panel jadwal `.hx-jadwal`, kontrol dan thumbnail di kanan bawah, kartu statistik menumpuk di tepi bawah. Foto dibatasi `min(100%, 1400px)` dan memudar ke hijau di tepi kiri.
- **Foto hero:** minimal 1600px lebar dan landscape. Foto potret atau di bawah 1280px diberi `galeri.untuk_hero = false` (hanya muncul di galeri dan kartu eskul).
- **Halaman dalam:** `.page-hero` hijau gelap dengan pola cahaya.

### 4.5 Kartu dan Daftar

- `.unit-card`, `.galeri-item`: latar putih, border `--line`, tanpa bayangan; hover mengubah border menjadi `--primary`.
- `.keunggulan-card`: daftar bernomor (counter CSS), tanpa ikon.
- `.feature-box`: latar `--paper-deep`, radius sedang.
- Kartu eskul (`.eks-slide`) berada di carousel `.eks-track` dengan scroll-snap, panah `.eks-arrow`, dan titik `.eks-dots`. Data dari tabel `ekstrakurikuler` (dikelola dari admin). Kartu tanpa foto memakai ikon (`.no-foto`).
- Visi dan misi: banner foto `.vm-visi` dan 12 kartu bernomor `.vm-misi-list`/`.vm-no`.

### 4.6 Tabel Publik (`.matrix-table`)

Border `--line`, radius 6px, tanpa bayangan; header `--primary-dark` teks putih; kolom pertama berlatar `--paper-deep`. Di ≤ 768px diberi `min-width` dan scroll horizontal.

### 4.7 Langkah PPDB (`.ppdb-step`)

Kartu berborder dengan nomor `.step-num` (kotak 4px, `--paper-deep`, serif), judul, dan deskripsi kecil.

---

## 5. Komponen Panel (Pendaftar dan Admin)

Semua ada di `panel.css`. Body panel memakai kelas `panel`.

### 5.1 Form

| Elemen | Spesifikasi |
|---|---|
| `.form-control` | Tinggi min 46px, padding `10px 14px`, border `--line`, radius 4px, font 1rem, latar putih |
| Fokus | Border `--primary` dan ring `0 0 0 3px rgba(13,110,62,.15)` |
| `.form-section` | Judul kecil huruf kapital emas dengan garis |
| `.form-grid` | Dua kolom (`.full` untuk satu baris penuh); satu kolom di ≤ 640px |
| `.field-error` | Pesan merah 0.82rem di bawah input |
| `.choice-cards` | Kartu pilihan berbasis radio; terpilih berlatar `--primary-pale` |
| `.check` | Kotak centang dengan teks persetujuan |
| `.form-actions` | Baris tombol dengan garis di atas ("Simpan Draft" outline, "Kirim" utama) |

Label selalu di atas input dan terhubung (`for`/`id`). Tombol lihat sandi memakai `.toggle-pass` dengan `aria-label`.

### 5.2 Badge, Alert, Toast

- Badge status: `.st` + kelas warna (bagian 3.2). Label kecil: `.pill`.
- Alert: `.alert` dengan `.alert-success` / `.alert-error`; peringatan memakai gaya inline di `partials/flash.blade.php` (latar `#FBF3DE`, border `--accent`). Ikon di kiri, `role="status"` atau `role="alert"`.
- `.note-box`: kotak catatan perbaikan dengan border kiri oranye.
- Toast `.toast-wrap`/`.toast` (kanan atas, gelap) tersedia di `panel.css` dan `js/panel.js`.

### 5.3 Portal Pendaftar

- Header `.pd-header` (sticky): brand, `.user-chip` (avatar, nama, Keluar), lalu menu tab `.pd-nav` (Ringkasan, Form Data, Upload Berkas, Pengumuman) dengan garis emas pada tab aktif.
- `.hero-status`: kartu hijau tua berpola dengan status, kode pendaftaran `.kode`, dan eyebrow emas.
- `.stepper` (`.step.done` / `.now`): langkah Akun → Data → Berkas → Verifikasi → Pengumuman.
- `.todo` (daftar tugas, `.ok` / `.warn`), `.timeline` (riwayat status), `.info-list`, `.kv` (pasangan label–nilai).
- Unggah berkas: `.up-list` / `.up-item` (ikon, nama, badge, tombol; `.ok` dan `.bad` mengubah border) dengan `.guide` untuk petunjuk. Format PDF/JPG/PNG, maks 2 MB.
- Pengumuman: `.result` dengan varian `.wait`, `.win`, `.lose`, serta `.countdown` untuk hitung mundur.
- `.empty`: keadaan kosong dengan ikon lingkaran, judul, dan satu tombol.

### 5.3a Konten Situs (admin)

Grup menu "Konten Situs" di sidebar: Profil & Kontak, Fasilitas, Galeri, Ekstrakurikuler. Polanya sama di semua halaman: `.page-title` dengan tombol tambah → `.tbl` → modal `.modal-box` untuk tambah/ubah.
- `.thumb` (84×56, foto dengan `object-position` dari kolom posisi), `.thumb-ico` (ikon bila tanpa foto), `.thumb-lg` (pratinjau foto di modal), `.cell-ico` (kotak ikon di tabel).
- Modal diisi otomatis oleh `window.isiForm` di `js/panel.js`: tombol membawa `data-mode="baru|ubah"` dan `data-row` (JSON baris); form membawa `data-store`, `data-update` (`:id` diganti id), `data-title-baru`, dan `data-title-ubah`. Atribut `data-show-edit` / `data-hide-edit` menampilkan atau menyembunyikan elemen sesuai mode.
- Pilihan ikon dibatasi daftar `KontenController::IKON` (Font Awesome); ikon baru perlu ditambahkan di sana.
- Hapus memakai form `DELETE` dengan `confirm()`; tombol memakai `.btn-danger`.

### 5.4 Panel Admin

- `.ad-shell`: sidebar `.ad-side` (gelap `#14201A`, berpola, grup menu `.grp`, item aktif dengan garis emas di kiri) dan `.ad-main`. Bilah atas `.ad-top` memuat tombol menu (ponsel), breadcrumb, dan badge gelombang aktif. Di ≤ 900px sidebar menjadi drawer.
- `.stats` / `.stat`: kartu angka (serif 2rem) dengan garis atas berwarna status.
- `.toolbar`: pencarian `.search` + filter status + tombol ekspor.
- `.tbl-wrap` / `.tbl`: header `#F6F2E7` huruf kapital kecil, baris hover krem, `.nm` untuk nama + info kecil, `<code>` untuk kode, `.acts` untuk aksi di kanan. `min-width: 720px` dengan scroll horizontal.
- `.quota` / `.bar`: batang kuota per jenjang; `.bar i.over` merah bila melebihi kuota.
- `.modal` / `.modal-box` / `.modal-head` / `.modal-body` / `.modal-foot`: detail pendaftar dan form gelombang. `.doc-row` untuk daftar berkas.
- Editor rincian biaya di form gelombang: `.biaya-ed` (satu per jenjang) berisi `.biaya-rows` dan tombol `.biaya-add`; tiap `.biaya-row` = nama, `.biaya-rp` (nominal berawalan "Rp"), `.check.biaya-gratis` (menonaktifkan nominal), dan `.biaya-del`. Baris dibuat oleh skrip di `admin/gelombang.blade.php`; di ≤ 640px nama memenuhi satu baris sendiri.

### 5.5 Auth

Halaman krem berpola, pita hijau melengkung dengan garis emas di atas, bilah atas `.auth-top` (brand dan tautan pil), kartu putih `.auth-card` menimpa pita. Tombol utama penuh lebar (min 48px). `.auth-switch` mengarah ke halaman lawan (masuk/daftar).

---

## 6. Pola Halaman

| Halaman | Pola |
|---|---|
| Beranda | Hero layar penuh → profil singkat → carousel eskul → program unggulan → galeri → ajakan daftar |
| Profil, Fasilitas, Unit Pendidikan | `.page-hero` → seksi selang-seling krem/paper → tabel atau kartu → ajakan daftar |
| Login / Register | Pita hijau + kartu form di tengah; di ponsel hanya kartu |
| Dashboard Pendaftar | `.hero-status` → dua kolom (`.pd-grid`): stepper, tugas, berkas di kiri; jadwal dan info di kanan |
| Form Data | Bagian dalam kartu (`.form-section`), tombol simpan draft dan kirim di bawah |
| Upload Berkas | `.up-list` satu kartu per jenis berkas dengan badge status |
| Pengumuman | `.result` besar, jadwal daftar ulang di bawahnya, daftar hasil (kode + inisial) bila sudah terbit |
| Admin Pendaftar | `.stats` → `.toolbar` → `.tbl` → modal detail dengan tombol setujui/perbaikan |
| Admin Gelombang | Tabel gelombang + modal tambah/ubah (tanggal dan kuota) |
| Admin Hasil Seleksi | `.quota` → tabel dengan pilihan hasil per baris → tombol simpan dan publikasi |

---

## 7. Aksesibilitas

- Kontras teks minimal 4.5:1; status selalu bersama teks.
- Semua input punya `<label>`; kesalahan memakai `.field-error`; flash memakai `role="status"`/`role="alert"`.
- Fokus keyboard terlihat (ring hijau pada input, outline pada pilihan kartu dan track carousel).
- Target sentuh minimal 44px untuk tombol dan input utama.
- Gambar bermakna memakai `alt` (foto galeri memakai kolom `keterangan_gambar`); ikon dekoratif sebaiknya `aria-hidden="true"`.
- `<html lang="id">` di semua layout.
- Hormati `prefers-reduced-motion` untuk setiap animasi baru.

---

## 8. Konvensi Kode dan Utang Desain

- Gaya baru masuk ke berkas CSS yang sesuai (publik → `theme.css`, panel → `panel.css`), bukan `<style>` inline. Pakai `var(--token)`.
- Penamaan kelas: huruf kecil dengan tanda hubung; varian ditambahkan sebagai kelas kedua (`.btn .btn-primary`). Awalan per area: `hx-`/`hc-` (hero), `eks-` (eskul), `vm-` (visi-misi), `auth-`, `pd-` (pendaftar), `ad-` (admin).
- Aset gambar di `public/images/` (`logo/` untuk logo, `foto/` untuk semua foto situs, satu folder saja). Panggil dengan `asset()`. `foto kegitan/` (subfolder `foto kegiatan MI` dan `foto kegiatan SMP`) hanya folder staging foto asli; isinya belum otomatis tampil di situs.
- Aturan foto: konten foto harus kegiatan MI atau SMP Sirojul Athfal (kolom `galeri.unit` menandai jenjangnya). Foto yang tidak jelas kegiatannya dilewati; foto upacara dengan anak kecil dilewati untuk galeri SMP.

**Utang yang diketahui** (belum dikerjakan):
1. Tiap view publik masih menyalin `<head>` sendiri. Buat `layouts/publik.blade.php` agar urutan CSS dan font konsisten.
2. `fasilitas` dan `unit-pendidikan` masih memakai `style="…"` inline dan blok `<style>` yang sebaiknya masuk `theme.css`.
3. `partials/flash.blade.php` memakai gaya inline untuk peringatan; jadikan `.alert-warning`.
4. `style.css` masih memuat kelas lama yang tidak dipakai (`.berita-card`, `.btn-filter`, token abu-abu). Bersihkan setelah dipastikan tidak ada pemakai.
5. Ukuran font sebagian teks kecil (0.7–0.8rem) berada di bawah 14px; tinjau untuk teks yang dibaca orang tua.

---

## 9. Panduan untuk Desain atau Prompt Baru

Gunakan ringkasan ini bila membuat layar baru (misalnya di Stitch) agar hasilnya cocok:

> Desain UI untuk portal PPDB SMP Islam. Latar krem hangat #FBF8F1, teks #44504A, judul #1F2A24 dengan font serif Fraunces; isi Plus Jakarta Sans. Warna utama hijau #0D6E3E (gelap #0A5A32), aksen emas #D29D29 hanya untuk garis kecil dan penanda aktif. Sudut hampir persegi (4px tombol dan input, 6px kartu), bingkai 1px #E4DDCB, tanpa bayangan. Pola bintang delapan sudut sangat tipis di atas area hijau. Gaya editorial, lega, mobile-first. Ikon Font Awesome. Bahasa Indonesia.

Saat mengubah hasil desain ke Blade: ganti warna dan radius dengan token, pakai kelas komponen proyek, dan masukkan ke layout yang sesuai. Tailwind tidak dipakai.
