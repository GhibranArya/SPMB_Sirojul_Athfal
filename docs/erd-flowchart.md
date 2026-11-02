# ERD & Flowchart — Sistem PPDB Yayasan Amanah

Database: `ppdb_amanah` (MariaDB/XAMPP). Diagram ditulis dengan [Mermaid](https://mermaid.js.org/), yang tampil langsung di GitHub dan VS Code (ekstensi Markdown Preview Mermaid). Versi yang bisa dibuka di browser: <http://localhost:8000/docs/erd-flowchart.html>.

## 1. ERD

Sumber: migrasi di `database/migrations/` (`2026_10_05_000001_create_ppdb_tables.php`, `2026_10_05_000002_create_konten_tables.php`, dan tabel `users`). Terdapat 10 foreign key; semuanya tergambar di bawah.

```mermaid
erDiagram
    users ||--o{ pendaftaran : "mendaftarkan"
    users |o--o{ riwayat_status : "pelaku perubahan"
    gelombang ||--o{ pendaftaran : "menerima"
    jenjang ||--o{ pendaftaran : "dituju"
    gelombang ||--o{ kuota_jenjang : "punya kuota"
    jenjang ||--o{ kuota_jenjang : "dibatasi"
    gelombang ||--o| pengumuman : "diumumkan lewat"
    pendaftaran ||--o{ berkas : "melampirkan"
    jenis_berkas ||--o{ berkas : "jenis"
    pendaftaran ||--o{ riwayat_status : "dicatat"

    users {
        bigint id PK
        string name
        string email UK
        string phone UK
        string password
        string role "admin | pendaftar"
    }
    gelombang {
        bigint id PK
        string nama
        string tahun_ajaran "2026/2027"
        date tgl_buka
        date tgl_tutup
        date tgl_pengumuman
        date daftar_ulang_mulai
        date daftar_ulang_selesai
        boolean is_aktif "hanya satu aktif"
    }
    jenjang {
        bigint id PK
        string kode UK "TK | MI | MTs"
        string nama
        text deskripsi
    }
    kuota_jenjang {
        bigint id PK
        bigint gelombang_id FK
        bigint jenjang_id FK
        smallint kuota "unik per gelombang+jenjang"
    }
    pendaftaran {
        bigint id PK
        bigint user_id FK
        bigint gelombang_id FK
        bigint jenjang_id FK
        string kode UK "terbit saat formulir dikirim"
        string status "draft|menunggu|perbaikan|terverifikasi|diterima|ditolak"
        text catatan "catatan perbaikan"
        string nama_siswa
        string nik
        char jenis_kelamin
        date tgl_lahir
        string nama_ayah
        string nama_ibu
        string hp_ortu
        boolean setuju_data
        timestamp submitted_at "null = baru draft"
    }
    jenis_berkas {
        bigint id PK
        string kode UK "akta|kk|foto|rapor|sehat"
        string nama
        boolean wajib
        boolean is_aktif
    }
    berkas {
        bigint id PK
        bigint pendaftaran_id FK
        bigint jenis_berkas_id FK
        string path "storage privat"
        string nama_asli
        string status "menunggu|valid|ditolak"
        string catatan
    }
    riwayat_status {
        bigint id PK
        bigint pendaftaran_id FK
        bigint user_id FK "nullable"
        string status_lama
        string status_baru
        string keterangan
    }
    pengumuman {
        bigint id PK
        bigint gelombang_id FK,UK
        string judul
        timestamp published_at "null = belum terbit"
    }
```

Tabel konten company profile (belum berelasi dengan tabel PPDB; dikelola panitia pada Fase 3–4):

```mermaid
erDiagram
    profil_yayasan {
        bigint id PK
        string nama_sambutan
        text sambutan
        text sejarah
        text visi
        text misi
        string alamat
        string whatsapp
        string email
    }
    fasilitas {
        bigint id PK
        string nama
        text deskripsi
        string gambar
        tinyint urutan
    }
    berita {
        bigint id PK
        string judul
        string slug UK
        string kategori
        longtext isi
        timestamp published_at
    }
    agenda {
        bigint id PK
        string judul
        date tgl_mulai
        date tgl_selesai
        string lokasi
    }
    galeri {
        bigint id PK
        string judul
        string kategori
        string gambar
    }
```

Catatan desain:

- `pendaftaran.kode` bernilai `NULL` selama formulir masih draft; kode acak terbit saat formulir dikirim (`submitted_at` terisi).
- `kuota_jenjang` memisahkan kuota dari `jenjang` karena kuota berbeda tiap gelombang.
- Hasil seleksi per pendaftar disimpan di `pendaftaran.status`. Tabel `pengumuman` hanya menentukan **kapan** hasil boleh dilihat (`published_at`).
- `riwayat_status` adalah jejak audit: status lama/baru, pelaku, waktu, dan keterangan.
- Lima tabel konten (`profil_yayasan`, `fasilitas`, `berita`, `agenda`, `galeri`) belum dipakai halaman publik; disiapkan untuk Fase 3–4 PRD.
- Tabel bawaan Laravel (`migrations`, `failed_jobs`, `password_reset_tokens`, `personal_access_tokens`) tidak digambar.

## 2. Flowchart alur pendaftaran (end-to-end)

```mermaid
flowchart TD
    subgraph P["Pendaftar (orang tua/wali)"]
        A1([Buka website]) --> A2[Register akun]
        A2 --> A3[Login]
        A3 --> A4[Isi Form Data]
        A4 --> A5{Kirim atau simpan draft?}
        A5 -- Simpan draft --> A4
        A5 -- Kirim --> S1
        A9[Unggah berkas wajib] 
        A12[Ganti berkas yang ditolak<br/>lalu Kirim Perbaikan]
        A14[Lihat pengumuman hasil]
        A15[Unduh bukti pendaftaran PDF]
        A16([Daftar ulang di sekolah])
    end

    subgraph SYS["Sistem"]
        S1{Gelombang dibuka<br/>dan NIK belum terdaftar?}
        S1 -- Tidak --> S2[Tampilkan pesan error] --> A4
        S1 -- Ya --> S3[Buat kode pendaftaran<br/>status = Draft]
        S3 --> A15
        S3 --> A9
        A9 --> S4{Semua berkas<br/>wajib lengkap?}
        S4 -- Belum --> A9
        S4 -- Ya --> S5[Status = Menunggu Verifikasi<br/>form dan berkas terkunci]
        A12 --> S5
    end

    subgraph ADM["Panitia (admin)"]
        M1{Periksa data dan berkas}
        S5 --> M1
        M1 -- Perlu perbaikan<br/>wajib catatan --> M2[Status = Perlu Perbaikan<br/>tandai berkas ditolak]
        M2 --> A12
        M1 -- Sesuai --> M3[Status = Terverifikasi]
        M3 --> M4[Tetapkan hasil<br/>Diterima / Tidak Diterima]
        M4 --> M5{Jumlah Diterima<br/>melebihi kuota?}
        M5 -- Ya --> M6[Peringatan kuota] --> M4
        M5 -- Tidak --> M7[Publikasikan pengumuman]
        M7 --> A14
    end

    A14 --> A16
```

## 3. Diagram status pendaftaran

Setiap perpindahan dicatat di `riwayat_status`. Hasil `Diterima`/`Tidak Diterima` baru terlihat pendaftar setelah pengumuman dipublikasikan.

```mermaid
stateDiagram-v2
    [*] --> Draft : Formulir dikirim
    Draft --> MenungguVerifikasi : Semua berkas wajib lengkap
    MenungguVerifikasi --> PerluPerbaikan : Panitia minta perbaikan
    PerluPerbaikan --> MenungguVerifikasi : Pendaftar kirim perbaikan
    MenungguVerifikasi --> Terverifikasi : Panitia setujui
    Terverifikasi --> Diterima : Hasil ditetapkan (dalam kuota)
    Terverifikasi --> TidakDiterima : Hasil ditetapkan
    Diterima --> [*]
    TidakDiterima --> [*]

    state "Draft" as Draft
    state "Menunggu Verifikasi" as MenungguVerifikasi
    state "Perlu Perbaikan" as PerluPerbaikan
    state "Tidak Diterima" as TidakDiterima
```

## 4. Flowchart verifikasi oleh panitia

```mermaid
flowchart TD
    V1([Admin buka Data Pendaftar]) --> V2[Klik Periksa pada pendaftar]
    V2 --> V3{Status = Menunggu Verifikasi?}
    V3 -- Tidak --> V4[Tombol dinonaktifkan<br/>hanya bisa melihat]
    V3 -- Ya --> V5[Buka dan cek tiap berkas]
    V5 --> V6{Data dan berkas sesuai?}
    V6 -- Ya --> V7[Setujui dan Verifikasi]
    V7 --> V8[Semua berkas = valid<br/>status = Terverifikasi]
    V6 -- Tidak --> V9[Centang berkas yang perlu diulang<br/>tulis catatan wajib]
    V9 --> V10[Berkas dicentang = ditolak<br/>status = Perlu Perbaikan]
    V8 --> V11[(riwayat_status dicatat)]
    V10 --> V11
```

## 5. Flowchart hak akses (login dan peran)

```mermaid
flowchart TD
    L1([Akses halaman]) --> L2{Sudah login?}
    L2 -- Tidak --> L3[Redirect ke /login]
    L3 --> L4{Kredensial benar?<br/>maks 10 percobaan/menit}
    L4 -- Tidak --> L3
    L4 -- Ya --> L5{Peran}
    L2 -- Ya --> L5
    L5 -- pendaftar --> L6[/pendaftar/*<br/>hanya data milik sendiri/]
    L5 -- admin --> L7[/admin/*<br/>kelola semua pendaftar/]
    L6 -. buka /admin .-> L5
    L7 -. buka /pendaftar .-> L5
    L6 --> L8[Berkas: hanya pemilik atau admin<br/>lewat /berkas/id]
    L7 --> L8
```
