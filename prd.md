# PRD — Website Company Profile & SPMB Online Yayasan Sirojul Athfal (MI dan SMP)

| | |
|---|---|
| **Versi** | 1.1 (disesuaikan dengan aplikasi yang berjalan) |
| **Tanggal** | 30 Oktober 2026 (versi 1.0: 15 Oktober 2026) |
| **Stack** | Laravel 10, PHP ^8.1, MySQL (`ppdb_amanah`), Blade, barryvdh/laravel-dompdf |
| **Sumber isi situs** | Dokumen KSP SMP Islam Sirojul Athfal 2026/2027 dari yayasan (hanya SMP), brosur gelombang MIS/SMPI, dan PDF "Struktur Organisasi Sekolah MI dan SMP" |
| **Dokumen terkait** | `PRD - Sistem Company Profile & PPDB Yayasan Sirajul Athfal (revisi).docx` (sumber nomor FR/BR/NFR), `design_system.md` |
| **Status** | Halaman publik, auth, portal pendaftar untuk dua jenjang (MI dan SMP), panel admin, pengelolaan konten situs, biaya per gelombang, serta ganti dan reset kata sandi tanpa email berjalan. Cek Status publik, reset sandi lewat email, dan notifikasi belum dibuat. Konten profil MI belum ada sumbernya. |

Dokumen ini menggantikan draf 0.1 (4 Okt 2026) yang masih menyebut Yayasan Amanah, Cimahi, dan jenjang TK/MI/MTs. Versi 1.1 membalik keputusan versi 1.0 ("satu jenjang SMP") sesuai revisi yayasan 26 Okt 2026: jenjang yang dilayani **MI dan SMP**. Nomor FR, BR, dan NFR mengikuti PRD .docx. Kolom **Status** menunjukkan kondisi aplikasi saat ini.

Istilah penerimaan murid baru di tampilan adalah **SPMB** (Sistem Penerimaan Murid Baru), diatur di `config/sekolah.php` (menggantikan "PPDB"). Dokumen ini masih memakai "PPDB" hanya untuk nama proyek, nama berkas lama, dan nama DB.

---

## 1. Ringkasan

Website resmi **Yayasan Pendidikan Islam Sirojul Athfal**, Kabupaten Bogor, yang menaungi **MIS Sirojul Athfal** dan **SMP Islam Sirojul Athfal** (alamat dan NPSN 20200558 dari dokumen KSP SMP: Kampung Lio Masjid, Desa Kedung Waringin, Kecamatan Bojong Gede). Isinya company profile dan **Sistem Penerimaan Murid Baru (SPMB) online** untuk dua jenjang, **MI dan SMP**. Orang tua/wali membuat akun, memilih jenjang, mengisi formulir, mengunggah berkas, lalu memantau status dan hasil seleksi. Panitia memverifikasi pendaftar, menetapkan hasil, dan mempublikasikan pengumuman dari panel admin.

Proyek ini merupakan kerja praktik (KP) untuk yayasan. Teks profil, visi-misi, fasilitas, dan statistik diambil dari dokumen KSP SMP tanpa mengubah kata-katanya (statistik diberi label "SMP"). Isi yang tidak ada di dokumen tidak ditampilkan dan tidak dikarang; **konten profil MI belum ada sumbernya** dan menunggu data dari yayasan.

## 2. Latar Belakang & Masalah

Masalah di bawah ini masih dugaan dan perlu dikonfirmasi ke panitia PPDB (jumlah pendaftar per tahun, lama rekap manual).

- Pendaftaran masih manual (datang langsung, berkas kertas), sehingga lambat, rawan salah catat, dan sulit direkap.
- Orang tua sulit mengetahui status pendaftaran tanpa menghubungi panitia.
- Profil, kegiatan, dan fasilitas sekolah belum punya kanal resmi yang mudah ditemukan.

## 3. Tujuan & Indikator

Angka target adalah usulan dan belum disepakati dengan yayasan.

| Tujuan | Indikator | Target (usulan) |
|---|---|---|
| Pendaftaran sepenuhnya online | Formulir dan berkas selesai tanpa datang ke sekolah | ≥ 90% pendaftar |
| Verifikasi lebih cepat dan rapi | Panitia memverifikasi langsung dari panel | ≤ 5 menit per pendaftar, tanpa rekap manual |
| Status transparan | Pendaftar melihat status dan riwayat kapan saja | 100% pendaftar menerima kode |
| Pengumuman terpusat | Hasil terbit pada tanggal pengumuman | Sesuai jadwal gelombang |
| Profil resmi online | Profil, fasilitas, dan kegiatan tampil dari dokumen KSP | Sesuai dokumen sumber |
| Mudah dipakai | Lulus uji black box, rapi di ponsel | 0 cacat kritis |

**Di luar lingkup v1:** pembayaran online (biaya diurus di sekolah), tes seleksi online, sistem akademik setelah diterima, aplikasi mobile native, WhatsApp API berbayar, daftar tunggu/cadangan, multi-bahasa.

## 4. Pengguna

| Peran | Login | Kebutuhan utama |
|---|---|---|
| **Pengunjung** | Tidak | Melihat profil, fasilitas, unit pendidikan, kegiatan, alur dan jadwal PPDB; mendaftar akun |
| **Pendaftar** | Ya (`role = pendaftar`) | Isi formulir, unggah berkas, pantau status, lihat pengumuman, unduh bukti pendaftaran |
| **Panitia/Admin** | Ya (`role = admin`) | Verifikasi pendaftar, tetapkan dan publikasikan hasil, kelola gelombang dan kuota, ekspor rekap |

Pendaftar mewarisi akses Pengunjung. Login memakai email atau nomor HP (nomor dinormalisasi ke format `08…`).

## 5. Kebutuhan Fungsional dan Statusnya

### 5.1 Halaman publik (rute `/`, `/profil`, `/fasilitas`, `/unit-pendidikan`, `/galeri`, `/struktur-organisasi`, `/biaya-pendaftaran`)

| ID | Kebutuhan | Status |
|---|---|---|
| FR-01 | Sambutan dan sejarah | **Sebagian.** Profil menampilkan tonggak 1991–2026 dari dokumen KSP SMP. Sambutan tidak ditampilkan karena dokumen tidak memuatnya. Sejarah MI belum ada |
| FR-02 | Program/unit pendidikan | **Sebagian.** Data murid dan pendidik, struktur kurikulum, ekstrakurikuler, dan alur SPMB masih untuk SMP (dokumen KSP). Konten MI belum ada sumbernya. Struktur organisasi MI dan SMP tampil di `/struktur-organisasi` dari `config/struktur.php` dan PDF yayasan |
| FR-03 | Galeri fasilitas dan sarana | **Selesai.** Fasilitas dari tabel `fasilitas`; foto kegiatan SMP dan MI dari tabel `galeri` (kolom `unit` = MI/SMP/kosong untuk semua), dikelola dari admin. Halaman `/galeri` punya filter MI/SMP dan penampil foto. Judul dan keterangan foto MI hanya deskripsi isi foto dan perlu dicek yayasan |
| FR-04 | Visi, misi, alamat, kontak | **Selesai.** Visi, misi, alamat, NPSN, telepon, WhatsApp, email, dan tautan media sosial (Instagram, TikTok, Facebook, YouTube) dari tabel `profil_yayasan`, diisi panitia lewat admin. WhatsApp panitia terisi (0851-5825-6411) dan tampil di footer serta tombol melayang; ikon media sosial hanya muncul bila URL-nya diisi. URL media sosial sekolah belum diberikan |
| — | Rincian biaya pendaftaran per gelombang dan jenjang (`/biaya-pendaftaran`) | **Selesai.** Data tabel `biaya_gelombang`, nominal kosong tampil "Gratis". Isi awal mengikuti brosur dan **belum dikonfirmasi yayasan** |

Rincian yang sudah ada:
- Beranda: hero foto kegiatan layar penuh dengan jadwal gelombang aktif, carousel ekstrakurikuler (5 eskul awal dari tabel `ekstrakurikuler`), program unggulan, galeri.
- Profil: visi dan misi (12 butir), prinsip pengembangan kurikulum, kepala sekolah dan pembimbing eskul, legalitas.
- Header, footer, ajakan daftar, dan galeri berupa partial bersama `resources/views/partials/publik-*.blade.php`.
- Berita dan agenda **tidak** ditampilkan karena dokumen KSP tidak memuatnya. Tabel `berita` dan `agenda` sudah dihapus (30 Okt 2026).

### 5.2 Akun pendaftar (`/login`, `/register`, `/logout`)

| ID | Kebutuhan | Status |
|---|---|---|
| FR-20 | Registrasi: nama, email, no. HP, kata sandi (min. 8, huruf + angka), persetujuan data | **Selesai** |
| FR-21 | Login/logout; admin diarahkan ke `/admin/pendaftar`, pendaftar ke `/pendaftar/dashboard` | **Selesai** (throttle 10/menit) |
| FR-22 | Reset kata sandi lewat email | **Sebagian.** Tanpa email: halaman `/lupa-sandi` (petunjuk + tombol WhatsApp panitia dari Profil & Kontak), tombol "Reset kata sandi wali" di admin yang membuat sandi sementara acak (tampil sekali, dikirim lewat WhatsApp), wajib ganti sandi saat masuk (`users.must_change_password`), dan halaman `/ganti-sandi` untuk pendaftar dan admin. Reset lewat tautan email belum ada (butuh SMTP; tabel `password_reset_tokens` sudah ada). Sandi sementara belum punya masa kedaluwarsa |
| FR-23 | Dashboard: status, langkah berikutnya, riwayat | **Sebagian.** Satu akun hanya bisa memiliki satu pendaftaran. Belum bisa mendaftarkan beberapa anak |

### 5.3 Formulir dan berkas (`/pendaftar/*`)

| ID | Kebutuhan | Status |
|---|---|---|
| FR-05 | Formulir hanya bisa dibuat/dikirim saat gelombang aktif dan dalam rentang tanggal | **Selesai** (`Gelombang::sedangDibuka`) |
| FR-06 | Memilih jenjang dan tahun ajaran | **Selesai.** Pendaftar memilih MI atau SMP di formulir (terkunci setelah dikirim); tahun ajaran mengikuti gelombang aktif |
| FR-07 | Data calon siswa dan orang tua dengan validasi. Draft boleh dikosongkan, kirim final mewajibkan kolom inti | **Selesai** |
| FR-08 | Halaman konfirmasi sebelum kirim | **Belum.** Formulir langsung terkirim. Draft bisa disimpan lebih dulu |
| FR-09 | Kode pendaftaran unik | **Selesai.** Format `SA-yy-XXXXX`, acak, tanpa karakter 0/O/1/I |
| FR-34 | Tolak NIK ganda dalam satu tahun ajaran | **Selesai** |
| FR-35 | Persetujuan data pribadi sebelum kirim | **Selesai** |
| FR-10 | Daftar berkas wajib dan statusnya, berbeda per jenjang | **Selesai.** Wajib untuk semua jenjang: Kartu Keluarga, Akta Kelahiran, KTP Ayah, KTP Ibu. Khusus SMP ditambah Ijazah Terakhir / SKL (kolom `jenis_berkas.jenjang`). Pas foto dan surat keterangan sehat dinonaktifkan (datanya tetap) |
| FR-11 | Unggah PDF/JPG/PNG, maks. 2 MB | **Selesai** (throttle 30/menit) |
| FR-12 | Ganti berkas yang ditolak | **Selesai** |
| FR-13 | Status otomatis "Menunggu Verifikasi" saat berkas wajib lengkap | **Selesai** |
| FR-14 | Cek Status tanpa login (kode + tanggal lahir) | **Belum.** Status hanya terlihat setelah login |
| FR-15 | Tahapan seleksi dan riwayat status | **Selesai** di dashboard (stepper dan timeline) |
| FR-16 | Catatan perbaikan dari panitia | **Selesai** |
| — | Bukti pendaftaran PDF (`/pendaftar/bukti-pendaftaran`) | **Selesai** (tambahan di luar PRD .docx) |

### 5.4 Panel admin (`/admin/*`)

| ID | Kebutuhan | Status |
|---|---|---|
| FR-24 | Verifikasi: setujui atau minta perbaikan, catatan wajib, tandai berkas yang ditolak, tercatat di riwayat | **Selesai.** Modal Periksa menampilkan data calon siswa (jenjang, NIK, jenis kelamin, tempat dan tanggal lahir, asal sekolah, alamat), data orang tua, serta status tiap berkas (Menunggu diperiksa, Valid, Ditolak, Belum ada) |
| FR-36 | Email otomatis saat Perlu Perbaikan | **Belum.** Belum ada konfigurasi email |
| FR-25 | Tetapkan Diterima/Tidak Diterima hanya untuk yang Terverifikasi | **Selesai** |
| FR-37 | Peringatan bila Diterima melebihi kuota | **Selesai** |
| FR-26 | Publikasi pengumuman beserta jadwal daftar ulang | **Sebagian.** Tombol publikasi ada (judul dibuat otomatis, harus tanpa pendaftar yang belum ditetapkan). Jadwal daftar ulang diambil dari data gelombang. Belum ada isi pengumuman bebas |
| FR-27 | Rekap dengan filter | **Sebagian.** Pencarian, filter status, dan pilihan gelombang (termasuk yang tidak aktif) di Data Pendaftar dan Hasil Seleksi. Belum ada filter tahun ajaran dan jenjang |
| FR-28 | Ekspor Excel/PDF | **Sebagian.** CSV (terbuka di Excel). Belum PDF |
| FR-29 | Kelola data master | **Sebagian.** Gelombang (tahun ajaran, tanggal, kuota per jenjang, rincian biaya per jenjang, status aktif) lewat panel. Jenis berkas dan jenjang hanya lewat seeder/DB |
| FR-30 | Kelola profil dan fasilitas dari panel | **Selesai.** Menu "Konten Situs": Profil & Kontak, Fasilitas, Galeri, Ekstrakurikuler (tambah, ubah, hapus, unggah foto) |

### 5.5 Pengumuman hasil

| ID | Kebutuhan | Status |
|---|---|---|
| FR-17 | Daftar hasil memakai kode dan inisial | **Ditiadakan (keputusan 1 Nov 2026).** Hasil hanya tampil di akun masing-masing pendaftar; tidak ada daftar hasil bersama agar yang belum diterima tidak melihat hasil orang lain |
| FR-18 | Detail dan jadwal daftar ulang untuk yang diterima | **Selesai** |
| FR-19 | Kabar hasil lewat email dan tautan WhatsApp | **Belum** |

### 5.6 Informasi dan kegiatan

| ID | Kebutuhan | Status |
|---|---|---|
| FR-31, FR-32 | Berita dan agenda | **Tidak dikerjakan.** Dokumen KSP tidak memuatnya. Tabel `berita` dan `agenda` dikosongkan 15 Okt 2026 dan dihapus 30 Okt 2026 |
| FR-33 | Galeri kegiatan | **Selesai.** Tampil di beranda dan dikelola dari admin. Foto landscape ≥ 1280 px otomatis dapat dipakai sebagai latar hero |

## 6. Alur Status Pendaftaran

Status ada di kolom `pendaftaran.status`. Setiap perubahan dicatat di `riwayat_status` (status lama, status baru, pelaku, waktu, keterangan).

| Status | Label | Arti |
|---|---|---|
| `draft` | Draft | Formulir belum dikirim, atau sudah dikirim tetapi berkas wajib belum lengkap |
| `menunggu` | Menunggu Verifikasi | Berkas wajib lengkap. Data dan berkas terkunci |
| `perbaikan` | Perlu Perbaikan | Panitia menulis catatan. Data dan berkas terbuka lagi |
| `terverifikasi` | Terverifikasi | Data dan berkas sesuai, siap ditetapkan hasilnya |
| `diterima` | Diterima | Hasil akhir, terlihat pendaftar setelah pengumuman dipublikasikan |
| `ditolak` | Tidak Diterima | Hasil akhir, terlihat setelah pengumuman dipublikasikan |

| Dari | Ke | Pemicu | Oleh |
|---|---|---|---|
| draft | menunggu | Semua berkas wajib terunggah | Sistem |
| menunggu | perbaikan | Panitia meminta perbaikan (catatan wajib) | Panitia |
| perbaikan | menunggu | Pendaftar mengirim perbaikan (semua berkas ditolak sudah diganti) | Pendaftar |
| menunggu | terverifikasi | Panitia menyetujui | Panitia |
| terverifikasi | diterima / ditolak | Panitia menetapkan hasil | Panitia |
| diterima / ditolak | terverifikasi | Panitia membatalkan hasil (hanya sebelum publikasi) | Panitia |
| draft (gelombang ditutup) | draft di gelombang aktif | Pendaftar menekan "Lanjutkan di Gelombang X"; data, berkas, dan kode terbawa, perpindahan dicatat di riwayat | Pendaftar |

Pendaftaran Draft yang gelombangnya sudah ditutup tidak otomatis dinyatakan Tidak Diterima: tetap Draft, tidak ikut pengumuman, dan dapat dilanjutkan di gelombang berikutnya. Pendaftar Tidak Diterima (hasil terbit) dapat mendaftar lagi di gelombang aktif lain dengan akun yang sama; NIK dari pendaftaran yang ditolak dikecualikan dari cek ganda. Pendaftaran Perlu Perbaikan terkunci setelah pengumuman gelombangnya terbit.

Sebelum pengumuman dipublikasikan, pendaftar yang sudah ditetapkan melihat status **Terverifikasi** dan riwayat "diterima/ditolak" disembunyikan.

## 7. Model Data

Skema aktual (migrasi di `database/migrations`). Berbeda dari draf 0.1: tidak ada tabel `data_siswa`, `data_orang_tua`, maupun `hasil_seleksi`. Data siswa dan orang tua ada di `pendaftaran`, hasil seleksi di kolom `status`, dan `tahun_ajaran` menjadi kolom pada `gelombang`.

| Tabel | Isi pokok |
|---|---|
| `users` | name, email, phone (unik), password, role (`admin`/`pendaftar`), must_change_password |
| `gelombang` | nama, tahun_ajaran, tgl_buka, tgl_tutup, tgl_pengumuman, daftar_ulang_mulai/selesai, is_aktif |
| `jenjang` | kode, nama, deskripsi (dua baris: `MI` dan `SMP`) |
| `kuota_jenjang` | gelombang_id, jenjang_id, kuota (unik per pasangan) |
| `biaya_gelombang` | gelombang_id, jenjang_id, nama butir biaya, nominal (kosong = Gratis), urutan |
| `pendaftaran` | user_id, gelombang_id, jenjang_id, kode, status, catatan, data siswa (nama_siswa, nik, jenis_kelamin, tempat_lahir, tgl_lahir, asal_sekolah, alamat_siswa), data orang tua (nama_ayah, nama_ibu, pekerjaan_ortu, hp_ortu, alamat_ortu), setuju_data, submitted_at |
| `jenis_berkas` | kode, jenjang (kosong = semua jenjang), nama, wajib, icon, urutan, is_aktif |
| `berkas` | pendaftaran_id, jenis_berkas_id, path (storage privat), nama_asli, mime, ukuran, status (`menunggu`/`valid`/`ditolak`), catatan |
| `riwayat_status` | pendaftaran_id, user_id (pelaku), status_lama, status_baru, keterangan |
| `pengumuman` | gelombang_id (unik), judul, isi, published_at |
| `profil_yayasan`, `fasilitas`, `galeri` | Konten halaman publik (visi, misi, alamat, NPSN, kontak, tautan media sosial; daftar fasilitas dengan ikon; foto dengan posisi, urutan, `untuk_hero`, `unit` MI/SMP) |
| `ekstrakurikuler` | nama, ikon, waktu, pembimbing, foto, posisi_foto, tujuan/target/profil (satu butir per baris), urutan |

Relasi: User 1—N Pendaftaran; Gelombang 1—N Pendaftaran; Gelombang M—N Jenjang lewat KuotaJenjang; Gelombang 1—N BiayaGelombang (per Jenjang); Pendaftaran 1—N Berkas dan 1—N RiwayatStatus; JenisBerkas 1—N Berkas; Gelombang 1—1 Pengumuman.

## 8. Aturan Bisnis

| ID | Aturan | Status |
|---|---|---|
| BR-01 | Formulir hanya dibuat/dikirim saat gelombang aktif dan dalam rentang buka–tutup. Setelah lewat tanggal tutup, pendaftaran Draft terkunci (formulir dan berkas) tetapi dapat dilanjutkan di gelombang aktif berikutnya | Diterapkan |
| BR-02 | Satu NIK satu pendaftaran per tahun ajaran | Diterapkan. Satu akun masih dibatasi satu pendaftaran |
| BR-03 | Kode unik, acak, tanpa data pribadi | Diterapkan (`SA-yy-XXXXX`) |
| BR-04 | Setelah Menunggu Verifikasi, data dan berkas terkunci kecuali dikembalikan ke Perbaikan | Diterapkan |
| BR-05 | Penolakan/permintaan perbaikan wajib bercatatan | Diterapkan |
| BR-06 | Diterima per jenjang dibatasi kuota | Sebagian. Sistem memberi peringatan, tidak memblokir |
| BR-07 | Hasil tidak terlihat sebelum pengumuman dipublikasikan | Diterapkan |
| BR-08 | Pengumuman tidak menampilkan nama lengkap | Diterapkan (hasil hanya tampil di akun pendaftar sendiri) |
| BR-09 | Setiap perubahan status tercatat | Diterapkan |

Tambahan: seleksi akademik/ujian dilakukan di luar website, website hanya mencatat dan mengumumkan hasil. Pembayaran tidak dicatat di sistem.

## 9. Kebutuhan Non-Fungsional

| ID | Aspek | Status |
|---|---|---|
| NFR-01 | Alur daftar singkat, bahasa Indonesia sederhana | Terpenuhi (akun → formulir → berkas) |
| NFR-02 | Rapi di ponsel mulai 360 px | Terpenuhi. Dicek di 375 px, tidak ada overflow |
| NFR-03 | Sandi di-hash (bcrypt), CSRF, validasi server | Terpenuhi |
| NFR-04 | Hak akses per peran | Terpenuhi (middleware `role:`) |
| NFR-05 | Berkas di storage privat, nama acak, hanya pemilik dan admin | Terpenuhi (`storage/app/berkas/<id>`, rute `/berkas/{berkas}`) |
| NFR-06 | Halaman publik < 3 detik di 4G | Belum diukur. Foto sumber 1280 px dari WhatsApp |
| NFR-07 | Bisa di-hosting di PHP 8.1+ dan MySQL | Terpenuhi |
| NFR-08 | Konten profil diubah dari dashboard | Terpenuhi untuk profil, fasilitas, galeri, dan ekstrakurikuler. Teks panjang (sejarah, kurikulum, statistik) tetap di kode |
| NFR-09 | Cadangan DB dan folder berkas mingguan | Belum diatur |
| NFR-10 | Privasi data anak | Persetujuan data ada. Kebijakan privasi belum ditulis |
| NFR-11 | Pembatasan percobaan | Sebagian. Login dan register 10/menit, unggah 30/menit. Cek Status belum ada |
| NFR-12 | Aksesibilitas | Sebagian. Label form, `lang="id"`, alt gambar ada. Audit kontras belum dilakukan |

## 10. Rencana dan Sisa Pekerjaan

Fase 1–3 dan pengelolaan konten dari PRD .docx (fondasi, berkas dan verifikasi, seleksi dan pelaporan) sudah terbangun kecuali butir bertanda Belum/Sebagian di atas. Urutan yang disarankan:

1. (Sebagian, 16 Okt 2026) Lupa dan ganti kata sandi (FR-22): tahap tanpa email selesai; reset lewat tautan email menyusul setelah SMTP tersedia.
2. Notifikasi (FR-19, FR-36). Saran: tautan `wa.me` yang diklik panitia ditambah email, dengan pesan netral tanpa status diterima/tidak.
3. Cek Status publik dengan kode + tanggal lahir dan pembatasan percobaan (FR-14, NFR-11).
4. (Selesai 15 Okt 2026) Panel kelola konten: profil dan kontak, fasilitas, galeri, ekstrakurikuler.
5. Halaman konfirmasi sebelum kirim (FR-08), beberapa anak per akun (FR-23), ekspor PDF dan filter gelombang (FR-27, FR-28).
6. (Selesai 26 Okt 2026) Revisi MI + SMP: dua jenjang, sebutan SPMB, biaya per gelombang, berkas per jenjang, struktur organisasi, WhatsApp dan media sosial.
7. Konten MI (profil, visi-misi, fasilitas, statistik) setelah yayasan menyerahkan sumbernya; pilih foto MI dan video MI untuk galeri.
8. Persiapan produksi: naik versi Laravel (Laravel 10 sudah tidak menerima pembaruan keamanan), ganti sandi akun seeder, HTTPS, nonaktifkan `APP_DEBUG`, ganti nama DB dari `ppdb_amanah` dan email seed `*@amanah.sch.id`, atur backup.

## 11. Risiko dan Asumsi

- Laravel 10 sudah tidak menerima pembaruan keamanan. Naik versi sebelum produksi.
- Paket Sanctum terpasang tetapi tidak dipakai (rute `/api/user` bawaan).
- Kuota di seed hanya angka sementara (kuota MI disalin dari SMP), jadwal gelombang (Gelombang 1–3 TA 2027/2028, tanggal brosur digeser setahun) dan rincian biaya belum dikonfirmasi yayasan. Isi lewat admin > Gelombang.
- Foto uji (tangkapan layar dialog Windows) masih ada di galeri; hapus lewat admin > Galeri sebelum penyerahan.
- Akun di DB sudah direset 30 Okt 2026 menjadi 3 akun: 1 admin, 1 kepala sekolah (peran admin, belum ada peran terpisah), dan 1 peserta. Peserta itu punya 1 pendaftaran contoh (SMP, Menunggu Verifikasi) dengan 5 berkas berisi foto dari galeri, dibuat oleh `PendaftarContohSeeder`; hapus sebelum penyerahan. Kata sandi awalnya ada di `database/seeders/UserSeeder.php`; ganti sebelum produksi. Gelombang dan pengumuman contoh juga sudah dihapus; tersisa Gelombang 1–3 TA 2027/2028.
- MariaDB XAMPP mudah crash saat tulis besar. Cadangkan lewat phpMyAdmin sebelum migrasi besar.
- Foto di hero perlu minimal 1600 px lebar dan landscape agar tajam. Admin menolak foto yang bukan landscape atau di bawah 1280 px sebagai latar hero (tetap bisa masuk galeri).
- Foto unggahan disimpan di `public/images/foto/` dengan nama acak dan hanya dihapus dari disk bila tidak dipakai lagi oleh galeri maupun ekstrakurikuler.
- Asumsi: panitia PPDB sekaligus admin konten; seleksi di luar sistem; pembayaran diurus di sekolah.

## 12. Pertanyaan Terbuka

1. Kuota MI dan SMP serta jadwal gelombang resmi (seed saat ini: TA 2027/2028).
2. Konfirmasi berkas wajib final (saat ini KK, akta, KTP ayah dan ibu, ditambah ijazah/SKL untuk SMP) dan rincian biaya per gelombang dari brosur.
3. Kontak resmi (telepon, email, jam layanan) dan URL Instagram/TikTok/Facebook/YouTube sekolah; WhatsApp panitia sudah ada.
4. Dokumen sumber konten MI (profil, visi-misi, fasilitas, statistik) dan kepastian teks judul/keterangan foto MI.
5. Apakah perlu daftar tunggu/cadangan bila kuota penuh.
6. Apakah satu akun boleh mendaftarkan lebih dari satu anak.
7. Siapa pemegang akun panitia, berapa orang, dan apakah perlu sub-peran.
8. Apakah nama lengkap boleh tampil di pengumuman, atau tetap kode dan inisial.
9. Domain dan hosting yayasan, serta siapa yang mengelolanya.
