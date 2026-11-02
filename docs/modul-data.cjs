// Sumber tunggal rancangan modular (Pertemuan 11): dipakai oleh gambar diagram dan dokumen Word agar selalu konsisten.
// Setiap fungsi di sini dipetakan ke route dan class yang benar-benar ada di aplikasi (routes/web.php, app/).

/** Modul utama. kelas = class/berkas pelaksana; sub = [kode, label singkat, route/metode]. */
const M = [
  { id: 'M1', nama: 'Publik', judul: 'Modul Publik (Company Profile)', kelas: 'PublikController',
    fungsi: 'Menampilkan situs profil sekolah kepada pengunjung tanpa login: beranda, profil, fasilitas, galeri, struktur organisasi, dan informasi biaya pendaftaran.',
    sub: [
      ['1.1', 'Beranda', 'GET /  · PublikController@beranda'],
      ['1.2', 'Profil', 'GET /profil  · PublikController@profil'],
      ['1.3', 'Fasilitas', 'GET /fasilitas  · PublikController@fasilitas'],
      ['1.4', 'Galeri', 'GET /galeri  · PublikController@galeri'],
      ['1.5', 'Struktur Organisasi', 'GET /struktur-organisasi  · PublikController@strukturOrganisasi'],
      ['1.6', 'Biaya Pendaftaran', 'GET /biaya-pendaftaran  · PublikController@biayaPendaftaran'],
    ] },
  { id: 'M2', nama: 'Autentikasi & Akun', judul: 'Modul Autentikasi & Akun', kelas: 'AuthController, AkunController, EnsureRole',
    fungsi: 'Mengenali pengguna (wali atau admin), membuat akun wali, mengelola kata sandi, dan membatasi halaman sesuai peran.',
    sub: [
      ['2.1', 'Login', 'POST /login  · AuthController@login'],
      ['2.2', 'Registrasi', 'POST /register  · AuthController@register'],
      ['2.3', 'Logout', 'POST /logout  · AuthController@logout'],
      ['2.4', 'Ganti Sandi', 'POST /ganti-sandi  · AkunController@simpanSandi'],
      ['2.5', 'Lupa Sandi', 'GET /lupa-sandi  · AkunController@lupaSandi'],
      ['2.6', 'Cek Peran', 'middleware role:...  · EnsureRole@handle'],
    ] },
  { id: 'M3', nama: 'Pendaftaran', judul: 'Modul Pendaftaran (Portal Wali)', kelas: 'PendaftarController',
    fungsi: 'Melayani wali calon siswa: mengisi formulir, mengunggah berkas, memperbaiki data, melanjutkan ke gelombang lain, mencetak bukti, dan melihat pengumuman.',
    sub: [
      ['3.1', 'Dashboard Status', 'GET /pendaftar/dashboard  · PendaftarController@dashboard'],
      ['3.2', 'Isi Formulir', 'POST /pendaftar/form-data  · PendaftarController@simpanForm'],
      ['3.3', 'Unggah Berkas', 'POST /pendaftar/upload-berkas/{jenis}  · PendaftarController@simpanBerkas'],
      ['3.4', 'Kirim Perbaikan', 'POST /pendaftar/kirim-perbaikan  · PendaftarController@kirimPerbaikan'],
      ['3.5', 'Lanjut / Daftar Lagi', 'POST /pendaftar/lanjutkan-gelombang  · PendaftarController@lanjutkanGelombang'],
      ['3.6', 'Cetak Bukti', 'GET /pendaftar/bukti-pendaftaran, /bukti-diterima  · PendaftarController@buktiPendaftaran'],
      ['3.7', 'Lihat Pengumuman', 'GET /pendaftar/pengumuman  · PendaftarController@pengumuman'],
    ] },
  { id: 'M4', nama: 'Verifikasi & Seleksi', judul: 'Modul Verifikasi & Seleksi (Panel Admin)', kelas: 'AdminController (bagian pendaftar dan hasil seleksi), Pendaftaran::ubahStatus',
    fungsi: 'Dipakai panitia: memeriksa pendaftar dan berkasnya, meminta perbaikan, menetapkan Diterima atau Tidak Diterima terhadap kuota, lalu mempublikasikan pengumuman.',
    sub: [
      ['4.1', 'Daftar Pendaftar', 'GET /admin/pendaftar  · AdminController@pendaftar'],
      ['4.2', 'Verifikasi Berkas', 'POST /admin/pendaftar/{id}/verifikasi  · AdminController@verifikasi'],
      ['4.3', 'Reset Sandi Wali', 'POST /admin/pendaftar/{id}/reset-sandi  · AdminController@resetSandi'],
      ['4.4', 'Ekspor CSV', 'GET /admin/pendaftar/export  · AdminController@exportCsv'],
      ['4.5', 'Tetapkan Hasil', 'POST /admin/hasil-seleksi  · AdminController@simpanHasil'],
      ['4.6', 'Publikasi Hasil', 'POST /admin/hasil-seleksi/publikasi  · AdminController@publikasi'],
    ] },
  { id: 'M5', nama: 'Gelombang & Biaya', judul: 'Modul Gelombang & Biaya', kelas: 'AdminController (bagian gelombang), Gelombang, KuotaJenjang, BiayaGelombang',
    fungsi: 'Mengatur jadwal tiap gelombang pendaftaran, kuota per jenjang, dan rincian biaya; menjadi sumber aturan buka-tutup pendaftaran bagi modul lain.',
    sub: [
      ['5.1', 'Daftar Gelombang', 'GET /admin/gelombang  · AdminController@gelombang'],
      ['5.2', 'Kelola Gelombang', 'POST, PUT /admin/gelombang  · AdminController@simpanGelombangBaru, ubahGelombang'],
      ['5.3', 'Atur Kuota', 'bagian dari simpan gelombang  · KuotaJenjang'],
      ['5.4', 'Atur Biaya', 'bagian dari simpan gelombang  · BiayaGelombang'],
      ['5.5', 'Cek Jadwal Buka', 'Gelombang::sedangDibuka(), Gelombang::aktif()'],
    ] },
  { id: 'M6', nama: 'Konten Situs', judul: 'Modul Konten Situs (Panel Admin)', kelas: 'KontenController',
    fungsi: 'Memungkinkan admin mengubah isi halaman publik tanpa mengubah kode: profil dan kontak, fasilitas, galeri foto, dan ekstrakurikuler.',
    sub: [
      ['6.1', 'Profil & Kontak', 'PUT /admin/konten/profil  · KontenController@simpanProfil'],
      ['6.2', 'Fasilitas', 'POST, PUT, DELETE /admin/konten/fasilitas  · KontenController'],
      ['6.3', 'Galeri', 'POST, PUT, DELETE /admin/konten/galeri  · KontenController'],
      ['6.4', 'Ekstrakurikuler', 'POST, PUT, DELETE /admin/konten/ekstrakurikuler  · KontenController'],
    ] },
];

/** Penyimpanan data (tabel MySQL). */
const D = [
  { id: 'D1', nama: 'users', isi: 'akun wali dan admin: nama, email, nomor HP, kata sandi (hash), peran, penanda wajib ganti sandi' },
  { id: 'D2', nama: 'pendaftaran, berkas, riwayat_status', isi: 'data siswa dan orang tua, status, berkas terunggah, jejak perubahan status' },
  { id: 'D3', nama: 'gelombang, kuota_jenjang, biaya_gelombang, pengumuman', isi: 'jadwal, kuota, biaya, dan penanda hasil sudah diumumkan' },
  { id: 'D5', nama: 'profil_yayasan, fasilitas, galeri, ekstrakurikuler', isi: 'konten halaman publik yang dikelola admin' },
];

/** Alur data. Nomor sama dengan penanda pada gambar interaksi-modul. [no, dari, ke, data, pemicu] */
const F = [
  ['F1', 'Pengunjung', 'M1', 'Permintaan halaman publik', 'GET /, /profil, /galeri, ...'],
  ['F2', 'D5', 'M1', 'Isi profil, fasilitas, galeri, dan ekstrakurikuler', 'PublikController membaca model ProfilYayasan, Fasilitas, Galeri, Ekstrakurikuler'],
  ['F3', 'Admin', 'M6', 'Isian konten dan foto yang diunggah', 'PUT/POST /admin/konten/...'],
  ['F4', 'M6', 'D5', 'Konten yang sudah divalidasi; foto disimpan di public/images/foto', 'KontenController@simpan*'],
  ['F5', 'Wali / Admin', 'M2', 'Email atau nomor HP dan kata sandi; data registrasi', 'POST /login, POST /register'],
  ['F6', 'M2', 'D1', 'Cek hash sandi saat login; simpan akun baru (peran selalu pendaftar); simpan sandi baru', 'Auth::attempt, User::create, forceFill'],
  ['F7', 'M2', 'M3', 'Sesi login dan peran pendaftar; wali boleh ke /pendaftar/*, selain itu dialihkan', 'EnsureRole@handle (role:pendaftar,admin)'],
  ['F8', 'Wali', 'M3', 'Data siswa dan orang tua, pilihan jenjang, berkas PDF/JPG/PNG maks. 2 MB', 'POST /pendaftar/form-data, /upload-berkas/{jenis}'],
  ['F9', 'M3', 'D2', 'Simpan formulir, kode pendaftaran, berkas, dan status Draft → Menunggu Verifikasi', 'Pendaftaran::create/update, Berkas, ubahStatus'],
  ['F10', 'M3', 'M4', 'Pendaftar berstatus Menunggu Verifikasi siap diperiksa (lewat D2)', 'AdminController@pendaftar membaca D2'],
  ['F11', 'M4', 'M3', 'Status baru, catatan perbaikan, dan hasil seleksi yang sudah diumumkan', 'Pendaftaran::untukView, Gelombang::hasilTerbit'],
  ['F12', 'Admin', 'M4', 'Keputusan verifikasi (setujui atau perbaikan + catatan), hasil Diterima atau Tidak Diterima, perintah publikasi', 'POST /admin/pendaftar/{id}/verifikasi, /hasil-seleksi, /hasil-seleksi/publikasi'],
  ['F13', 'M4', 'D2', 'Perubahan status dan catatan riwayat (siapa, kapan, dari mana ke mana)', 'Pendaftaran::ubahStatus dalam DB::transaction'],
  ['F14', 'M4', 'D3', 'Pengumuman: tanggal terbit (published_at)', 'Pengumuman::updateOrCreate'],
  ['F15', 'Admin', 'M5', 'Nama, tahun ajaran, tanggal buka/tutup/pengumuman, kuota, dan biaya', 'POST, PUT /admin/gelombang'],
  ['F16', 'M5', 'D3', 'Simpan dan baca gelombang, kuota, dan biaya', 'AdminController@simpanGelombang (DB::transaction)'],
  ['F17', 'M5', 'M4', 'Kuota per jenjang untuk membandingkan jumlah Diterima', 'KuotaJenjang pada AdminController@simpanHasil'],
  ['F18', 'M5', 'M3', 'Gelombang aktif, status dibuka/tutup, dan rincian biaya', 'Gelombang::aktif, sedangDibuka, biayaPerJenjang'],
  ['F19', 'M2', 'M4, M5, M6', 'Sesi dan peran admin; pengguna bukan admin dialihkan ke /pendaftar/dashboard', 'EnsureRole@handle (role:admin) pada semua route /admin/*'],
  ['F20', 'M4', 'D1', 'Sandi sementara (ter-hash) dan penanda wajib ganti sandi saat panitia mereset sandi wali', 'AdminController@resetSandi'],
  ['F21', 'M5', 'M1', 'Gelombang aktif, jadwal, dan rincian biaya untuk beranda dan halaman biaya', 'PublikController memakai Gelombang::aktif dan biayaPerJenjang'],
];

module.exports = { M, D, F };
