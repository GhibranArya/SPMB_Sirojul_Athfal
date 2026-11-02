<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Penanda foto yang cukup tajam (landscape, lebar >= 1280 px) untuk dipakai sebagai latar hero layar penuh. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galeri', function (Blueprint $t) {
            $t->boolean('untuk_hero')->default(true)->after('urutan');
        });
    }

    public function down(): void
    {
        Schema::table('galeri', fn (Blueprint $t) => $t->dropColumn('untuk_hero'));
    }
};
