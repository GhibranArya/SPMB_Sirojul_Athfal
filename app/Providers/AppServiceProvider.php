<?php

namespace App\Providers;

use App\Models\Ekstrakurikuler;
use App\Models\ProfilYayasan;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Identitas sekolah (visi, misi, alamat, NPSN, kontak) dan daftar ekstrakurikuler dipakai di banyak halaman publik dan footer.
        View::composer(['publik.*', 'partials.publik-*'], function ($view) {
            // Dibaca sekali per request (komposer berjalan untuk tiap view dan partial).
            $atribut = request()->attributes;
            if (! $atribut->has('konten_publik')) {
                $atribut->set('konten_publik', [
                    'profil' => ProfilYayasan::first() ?? new ProfilYayasan,
                    'eskul' => Ekstrakurikuler::untukView(),
                ]);
            }
            $view->with($atribut->get('konten_publik'));
        });
    }
}
