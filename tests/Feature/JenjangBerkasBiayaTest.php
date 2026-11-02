<?php

namespace Tests\Feature;

use App\Models\BiayaGelombang;
use App\Models\Gelombang;
use App\Models\JenisBerkas;
use App\Models\Jenjang;
use App\Models\Pendaftaran;
use App\Models\ProfilYayasan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Revisi MI + SMP: pilihan jenjang, berkas per jenjang, biaya per gelombang, WhatsApp/sosmed, halaman struktur organisasi. */
class JenjangBerkasBiayaTest extends TestCase
{
    use DatabaseTransactions;

    private Gelombang $gel;

    protected function setUp(): void
    {
        parent::setUp();

        Gelombang::query()->update(['is_aktif' => false]);
        $this->gel = Gelombang::create([
            'nama' => 'Gelombang Uji Jenjang', 'tahun_ajaran' => '2090/2091', 'tgl_buka' => today()->subDay(), 'tgl_tutup' => today()->addDays(10),
            'tgl_pengumuman' => today()->addDays(20), 'is_aktif' => true,
        ]);
    }

    private function wali(string $email = 'wali.jenjang@uji.test'): User
    {
        $u = User::create(['name' => 'Wali Uji', 'email' => $email, 'phone' => '0818'.random_int(10000000, 99999999), 'password' => 'rahasia123']);
        $u->forceFill(['role' => 'pendaftar'])->save();

        return $u;
    }

    private function form(array $ganti = []): array
    {
        return $ganti + ['aksi' => 'kirim', 'jenjang' => 'MI', 'nama' => 'Anak Uji', 'nik' => '3277010101190001', 'jk' => 'P', 'tempat_lahir' => 'Bogor',
            'tgl_lahir' => '2019-01-01', 'alamat' => 'Bogor', 'ayah' => 'Ayah', 'ibu' => 'Ibu', 'hp' => '081200001111', 'setuju' => 1];
    }

    public function test_jenjang_wajib_dipilih(): void
    {
        $u = $this->wali();
        $this->actingAs($u)->post('/pendaftar/form-data', $this->form(['jenjang' => '']))->assertSessionHasErrors('jenjang');
        $this->actingAs($u)->post('/pendaftar/form-data', $this->form(['jenjang' => 'TK']))->assertSessionHasErrors('jenjang');
    }

    public function test_pendaftar_mi_meminta_empat_berkas_dan_smp_menambah_ijazah(): void
    {
        $mi = $this->wali('mi@uji.test');
        $this->actingAs($mi)->post('/pendaftar/form-data', $this->form())->assertRedirect('/pendaftar/upload-berkas');
        $this->assertSame('MI', Pendaftaran::where('user_id', $mi->id)->first()->jenjang->kode);

        $halaman = $this->actingAs($mi)->get('/pendaftar/upload-berkas')->assertOk();
        foreach (['Kartu Keluarga', 'Akta Kelahiran', 'KTP Ayah', 'KTP Ibu'] as $nama) {
            $halaman->assertSee($nama);
        }
        $halaman->assertDontSee('Ijazah Terakhir');

        $smp = $this->wali('smp@uji.test');
        $this->actingAs($smp)->post('/pendaftar/form-data', $this->form(['jenjang' => 'SMP', 'nik' => '3277010101100002']))->assertRedirect('/pendaftar/upload-berkas');
        $this->actingAs($smp)->get('/pendaftar/upload-berkas')->assertOk()->assertSee('Ijazah Terakhir / SKL')->assertSee('KTP Ibu');

        $admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($admin)->get('/admin/pendaftar?gelombang='.$this->gel->id)->assertOk()->assertSee('0/4')->assertSee('0/5');
        $this->actingAs($admin)->get('/admin/konten/profil')->assertOk()->assertSee('Media sosial');
        $this->actingAs($admin)->get('/admin/hasil-seleksi?gelombang='.$this->gel->id)->assertOk()->assertSee('MI')->assertSee('SMP');

        $this->assertSame(4, Pendaftaran::where('user_id', $mi->id)->first()->berkasWajibKurang());
        $this->assertSame(5, Pendaftaran::where('user_id', $smp->id)->first()->berkasWajibKurang());
    }

    public function test_jenjang_terkunci_setelah_formulir_dikirim(): void
    {
        $u = $this->wali();
        $this->actingAs($u)->post('/pendaftar/form-data', $this->form())->assertSessionHasNoErrors();
        $this->actingAs($u)->post('/pendaftar/form-data', $this->form(['jenjang' => 'SMP']))->assertSessionHasNoErrors();

        $this->assertSame('MI', Pendaftaran::where('user_id', $u->id)->first()->jenjang->kode);
    }

    public function test_berkas_pdf_diterima_dan_pendaftaran_mi_lengkap_dengan_empat_berkas(): void
    {
        Storage::fake();
        $u = $this->wali();
        $this->actingAs($u)->post('/pendaftar/form-data', $this->form());

        foreach (['kk', 'akta', 'ktp_ayah', 'ktp_ibu'] as $kode) {
            $jenis = JenisBerkas::where('kode', $kode)->firstOrFail();
            $this->actingAs($u)->post('/pendaftar/upload-berkas/'.$jenis->id, ['berkas' => UploadedFile::fake()->create($kode.'.pdf', 100, 'application/pdf')])
                ->assertSessionHasNoErrors();
        }

        $this->assertSame('menunggu', Pendaftaran::where('user_id', $u->id)->first()->status);
    }

    public function test_form_menampilkan_catatan_whatsapp_dan_rincian_biaya(): void
    {
        $mi = Jenjang::where('kode', 'MI')->first();
        $this->gel->biaya()->create(['jenjang_id' => $mi->id, 'nama' => 'Baju Batik Uji', 'nominal' => 115000, 'urutan' => 1]);
        $this->gel->biaya()->create(['jenjang_id' => $mi->id, 'nama' => 'LKS Uji', 'nominal' => null, 'urutan' => 2]);

        $this->actingAs($this->wali())->get('/pendaftar/form-data')->assertOk()
            ->assertSee('harus nomor', false)->assertSee('WhatsApp', false)
            ->assertSee('Baju Batik Uji')->assertSee('Rp. 115.000,-')->assertSee('Gratis');
    }

    public function test_baris_biaya_dari_editor(): void
    {
        $baris = BiayaGelombang::dariBaris([
            ['nama' => ' Pendaftaran ', 'nominal' => '150000'],
            ['nama' => '', 'nominal' => ''],                       // baris kosong dilewati
            ['nama' => 'SPP bulan Juli', 'gratis' => '1'],
            ['nama' => 'Baju Batik', 'nominal' => '115000', 'gratis' => '1'], // Gratis menang atas nominal
            ['nama' => 'LKS', 'nominal' => ''],
        ]);
        $this->assertSame([
            ['nama' => 'Pendaftaran', 'nominal' => 150000],
            ['nama' => 'SPP bulan Juli', 'nominal' => null],
            ['nama' => 'Baju Batik', 'nominal' => null],
            ['nama' => 'LKS', 'nominal' => null],
        ], $baris);
        $this->assertSame('Rp. 1.250.000,-', BiayaGelombang::format(1250000));
        $this->assertSame('Gratis', BiayaGelombang::format(null));
    }

    public function test_admin_menyimpan_biaya_gelombang(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $mi = Jenjang::where('kode', 'MI')->first();

        $this->actingAs($admin)->put('/admin/gelombang/'.$this->gel->id, $this->isianGelombang([
            'biaya_ada' => ['MI' => 1, 'SMP' => 1],
            'biaya' => ['MI' => [
                1 => ['nama' => 'Formulir', 'nominal' => '100000'],
                2 => ['nama' => 'SPP Juli', 'gratis' => '1'],
                3 => ['nama' => '', 'nominal' => ''],   // baris kosong dari tombol "Tambah biaya" diabaikan
            ]],
        ]))->assertSessionHasNoErrors();

        $this->assertSame(2, $this->gel->biaya()->where('jenjang_id', $mi->id)->count());
        $this->assertSame(100000, $this->gel->biaya()->where('nama', 'Formulir')->first()->nominal);
        $this->assertNull($this->gel->biaya()->where('nama', 'SPP Juli')->first()->nominal);
        $this->actingAs($admin)->get('/admin/gelombang')->assertOk()->assertSee('Formulir')->assertSee('biaya-ed', false);
    }

    public function test_menghapus_semua_baris_biaya_mengosongkan_rincian(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $mi = Jenjang::where('kode', 'MI')->first();
        $this->gel->biaya()->create(['jenjang_id' => $mi->id, 'nama' => 'Lama', 'nominal' => 5000, 'urutan' => 1]);

        // Tanpa penanda biaya_ada, rincian tidak disentuh.
        $this->actingAs($admin)->put('/admin/gelombang/'.$this->gel->id, $this->isianGelombang())->assertSessionHasNoErrors();
        $this->assertSame(1, $this->gel->biaya()->count());

        // Editor dikirim tanpa satu baris pun: rincian MI dikosongkan.
        $this->actingAs($admin)->put('/admin/gelombang/'.$this->gel->id, $this->isianGelombang(['biaya_ada' => ['MI' => 1]]))->assertSessionHasNoErrors();
        $this->assertSame(0, $this->gel->biaya()->count());
    }

    public function test_baris_biaya_tidak_lengkap_ditolak(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin)->put('/admin/gelombang/'.$this->gel->id, $this->isianGelombang([
            'biaya_ada' => ['MI' => 1], 'biaya' => ['MI' => [1 => ['nama' => 'Baju Batik', 'nominal' => '']]],
        ]))->assertSessionHasErrors('biaya.MI.1.nominal');

        $this->actingAs($admin)->put('/admin/gelombang/'.$this->gel->id, $this->isianGelombang([
            'biaya_ada' => ['MI' => 1], 'biaya' => ['MI' => [1 => ['nama' => '', 'nominal' => '75000']]],
        ]))->assertSessionHasErrors('biaya.MI.1.nama');

        $this->actingAs($admin)->put('/admin/gelombang/'.$this->gel->id, $this->isianGelombang([
            'biaya_ada' => ['MI' => 1], 'biaya' => ['MI' => [1 => ['nama' => 'Seragam', 'nominal' => '115.000']]],
        ]))->assertSessionHasErrors('biaya.MI.1.nominal');

        $this->assertSame(0, $this->gel->biaya()->count());
    }

    /** Isian form Gelombang yang valid untuk gelombang uji, dengan bagian yang bisa ditimpa. */
    private function isianGelombang(array $tambahan = []): array
    {
        return $tambahan + [
            'nama' => $this->gel->nama, 'tahun_ajaran' => '2090/2091',
            'tgl_buka' => $this->gel->tgl_buka->toDateString(), 'tgl_tutup' => $this->gel->tgl_tutup->toDateString(),
            'tgl_pengumuman' => $this->gel->tgl_pengumuman->toDateString(),
            'kuota' => ['MI' => 10, 'SMP' => 10], 'is_aktif' => 1,
        ];
    }

    public function test_halaman_publik_baru_dan_kontak_whatsapp(): void
    {
        $profil = ProfilYayasan::first();
        $profil->update(['whatsapp' => '0851-5825-6411', 'instagram' => null, 'tiktok' => null, 'facebook' => null, 'youtube' => null]);
        $this->assertSame('https://wa.me/6285158256411', $profil->waLink());

        $this->get('/struktur-organisasi')->assertOk()->assertSee('H. Ahmad Baidowi')->assertSee('Hj. Titin Rostini, S.Ag')
            ->assertSee('Wildan Abdul Aziz, S.M')->assertSee('0851-5825-6411');
        $this->get('/biaya-pendaftaran')->assertOk()->assertSee('Gelombang')->assertSee('0851-5825-6411')->assertSee('KTP Ibu');
        $this->get('/')->assertOk()->assertSee('wa.me/6285158256411', false)->assertDontSee('instagram.com', false);

        $profil->update(['instagram' => 'https://www.instagram.com/sirojulathfal_uji']);
        $this->get('/')->assertSee('https://www.instagram.com/sirojulathfal_uji', false);
    }

    public function test_admin_menyimpan_tautan_media_sosial(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $p = ProfilYayasan::first();
        $isi = ['visi' => $p->visi, 'misi' => $p->misi, 'alamat' => $p->alamat];

        $this->actingAs($admin)->put('/admin/konten/profil', $isi + ['tiktok' => 'bukan-url'])->assertSessionHasErrors('tiktok');
        $this->actingAs($admin)->put('/admin/konten/profil', $isi + ['tiktok' => 'https://www.tiktok.com/@uji'])->assertSessionHasNoErrors();
        $this->assertSame('https://www.tiktok.com/@uji', ProfilYayasan::first()->tiktok);
    }
}
