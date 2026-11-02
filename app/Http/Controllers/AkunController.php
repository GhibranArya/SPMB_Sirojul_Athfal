<?php

namespace App\Http\Controllers;

use App\Models\ProfilYayasan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

/** Kata sandi akun: ganti sandi (pendaftar dan admin) dan petunjuk "lupa kata sandi" (tanpa email). */
class AkunController extends Controller
{
    public function formSandi(Request $request)
    {
        $user = $request->user();

        return view($user->isAdmin() ? 'akun.ganti-sandi-admin' : 'akun.ganti-sandi-pendaftar', [
            'authUser' => $user,
            'wajib' => (bool) $user->must_change_password,
        ]);
    }

    public function simpanSandi(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::min(8)->letters()->numbers()],
        ], [
            'current_password.required' => 'Isi kata sandi Anda saat ini.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'password.required' => 'Buat kata sandi baru.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak sama.',
            'password.different' => 'Kata sandi baru harus berbeda dari yang lama.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.letters' => 'Kata sandi harus memuat huruf.',
            'password.numbers' => 'Kata sandi harus memuat angka.',
        ]);

        $user->forceFill(['password' => $data['password'], 'must_change_password' => false])->save();
        $request->session()->regenerate();

        return redirect($user->isAdmin() ? '/admin/pendaftar' : '/pendaftar/dashboard')->with('status', 'Kata sandi berhasil diganti.');
    }

    /** Petunjuk bagi yang lupa kata sandi: hubungi panitia lewat WhatsApp untuk dibuatkan sandi sementara. */
    public function lupaSandi()
    {
        $wa = ProfilYayasan::first()?->whatsapp;
        $digits = preg_replace('/\D+/', '', (string) $wa);
        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        $pesan = 'Assalamualaikum, saya lupa kata sandi akun '.config('sekolah.spmb').' '.config('sekolah.nama').'. '
            .'Mohon dibantu reset kata sandi. Email atau nomor HP yang saya daftarkan: ';

        return view('auth.lupa-sandi', [
            'wa' => $wa,
            'waLink' => $digits ? 'https://wa.me/'.$digits.'?text='.rawurlencode($pesan) : null,
        ]);
    }
}
