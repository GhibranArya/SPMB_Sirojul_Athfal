<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KontenController;
use App\Http\Controllers\PendaftarController;
use App\Http\Controllers\PublikController;
use Illuminate\Support\Facades\Route;

// Publik
Route::get('/', [PublikController::class, 'beranda']);
Route::get('/profil', [PublikController::class, 'profil']);
Route::get('/fasilitas', [PublikController::class, 'fasilitas']);
Route::view('/unit-pendidikan', 'publik.unit-pendidikan');
Route::get('/galeri', [PublikController::class, 'galeri']);
Route::get('/struktur-organisasi', [PublikController::class, 'strukturOrganisasi']);
Route::get('/biaya-pendaftaran', [PublikController::class, 'biayaPendaftaran']);

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Kata sandi
Route::get('/lupa-sandi', [AkunController::class, 'lupaSandi'])->middleware('guest');
Route::middleware('auth')->group(function () {
    Route::get('/ganti-sandi', [AkunController::class, 'formSandi']);
    Route::post('/ganti-sandi', [AkunController::class, 'simpanSandi'])->middleware('throttle:10,1');
});

// Berkas (privat): pemilik, admin, atau kepala sekolah
Route::get('/berkas/{berkas}', [PendaftarController::class, 'berkas'])->middleware('role:pendaftar,admin,kepsek')->name('berkas');

// Pendaftar
Route::prefix('pendaftar')->middleware('role:pendaftar')->group(function () {
    Route::get('/dashboard', [PendaftarController::class, 'dashboard']);
    Route::get('/form-data', [PendaftarController::class, 'formData']);
    Route::post('/form-data', [PendaftarController::class, 'simpanForm']);
    Route::get('/upload-berkas', [PendaftarController::class, 'uploadBerkas']);
    Route::post('/upload-berkas/{jenis}', [PendaftarController::class, 'simpanBerkas'])->middleware('throttle:30,1');
    Route::post('/kirim-perbaikan', [PendaftarController::class, 'kirimPerbaikan']);
    Route::post('/lanjutkan-gelombang', [PendaftarController::class, 'lanjutkanGelombang']);
    Route::get('/bukti-pendaftaran', [PendaftarController::class, 'buktiPendaftaran']);
    Route::get('/bukti-diterima', [PendaftarController::class, 'buktiDiterima']);
    Route::get('/pengumuman', [PendaftarController::class, 'pengumuman']);
});

// Panel: master admin (penuh) dan kepala sekolah (lihat pendaftar, ekspor, kelola konten)
Route::prefix('admin')->middleware('role:admin,kepsek')->group(function () {
    Route::get('/pendaftar', [AdminController::class, 'pendaftar']);
    Route::get('/pendaftar/export', [AdminController::class, 'exportCsv']);

    // Hanya master admin: verifikasi, reset sandi, gelombang, seleksi
    Route::middleware('role:admin')->group(function () {
        Route::post('/pendaftar/{pendaftaran}/verifikasi', [AdminController::class, 'verifikasi']);
        Route::post('/pendaftar/{pendaftaran}/reset-sandi', [AdminController::class, 'resetSandi']);

        Route::get('/gelombang', [AdminController::class, 'gelombang']);
        Route::post('/gelombang', [AdminController::class, 'simpanGelombangBaru']);
        Route::put('/gelombang/{gelombang}', [AdminController::class, 'ubahGelombang']);

        Route::get('/hasil-seleksi', [AdminController::class, 'hasilSeleksi']);
        Route::post('/hasil-seleksi', [AdminController::class, 'simpanHasil']);
        Route::post('/hasil-seleksi/publikasi', [AdminController::class, 'publikasi']);
    });

    // Konten situs publik
    Route::prefix('konten')->group(function () {
        Route::get('/profil', [KontenController::class, 'profil']);
        Route::put('/profil', [KontenController::class, 'simpanProfil']);

        Route::get('/fasilitas', [KontenController::class, 'fasilitas']);
        Route::post('/fasilitas', [KontenController::class, 'simpanFasilitas']);
        Route::put('/fasilitas/{fasilitas}', [KontenController::class, 'ubahFasilitas']);
        Route::delete('/fasilitas/{fasilitas}', [KontenController::class, 'hapusFasilitas']);

        Route::get('/galeri', [KontenController::class, 'galeri']);
        Route::post('/galeri', [KontenController::class, 'simpanGaleri']);
        Route::put('/galeri/{galeri}', [KontenController::class, 'ubahGaleri']);
        Route::delete('/galeri/{galeri}', [KontenController::class, 'hapusGaleri']);

        Route::get('/ekstrakurikuler', [KontenController::class, 'ekstrakurikuler']);
        Route::post('/ekstrakurikuler', [KontenController::class, 'simpanEkstrakurikuler']);
        Route::put('/ekstrakurikuler/{ekstrakurikuler}', [KontenController::class, 'ubahEkstrakurikuler']);
        Route::delete('/ekstrakurikuler/{ekstrakurikuler}', [KontenController::class, 'hapusEkstrakurikuler']);
    });
});
