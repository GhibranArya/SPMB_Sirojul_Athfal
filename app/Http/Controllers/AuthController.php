<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Isi email atau nomor HP Anda.',
            'password.required' => 'Isi kata sandi Anda.',
        ]);

        $field = filter_var($data['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $identity = $field === 'phone' ? $this->normalizePhone($data['username']) : strtolower($data['username']);

        if (! Auth::attempt([$field => $identity, 'password' => $data['password']], $request->boolean('remember'))) {
            return back()
                ->withErrors(['username' => 'Email/nomor HP atau kata sandi salah.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();

        return redirect()->intended($this->homeFor($request->user()->role));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->merge([
            'phone' => $this->normalizePhone((string) $request->input('phone')),
            'email' => strtolower((string) $request->input('email')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'regex:/^08\d{8,12}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'agree' => ['accepted'],
        ], [
            'name.required' => 'Isi nama lengkap Anda.',
            'email.required' => 'Isi alamat email Anda.',
            'email.email' => 'Format email belum benar.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk.',
            'phone.required' => 'Isi nomor HP/WhatsApp Anda.',
            'phone.regex' => 'Nomor HP harus diawali 08 dan terdiri dari 10–14 angka.',
            'phone.unique' => 'Nomor HP ini sudah terdaftar. Silakan masuk.',
            'password.required' => 'Buat kata sandi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.letters' => 'Kata sandi harus memuat huruf.',
            'password.numbers' => 'Kata sandi harus memuat angka.',
            'agree.accepted' => 'Centang persetujuan penggunaan data untuk melanjutkan.',
        ]);

        // Role tidak ikut fillable: akun dari form selalu 'pendaftar' (default kolom).
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'], // di-hash otomatis oleh cast 'hashed'
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/pendaftar/dashboard')->with('status', 'Akun berhasil dibuat. Silakan lengkapi formulir pendaftaran.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'Anda sudah keluar.');
    }

    private function homeFor(string $role): string
    {
        return $role === 'admin' ? '/admin/pendaftar' : '/pendaftar/dashboard';
    }

    /** 0812-3456-789, +62 812..., 62812... → 0812... */
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($digits, '62')) {
            $digits = '0'.substr($digits, 2);
        }

        return $digits;
    }
}
