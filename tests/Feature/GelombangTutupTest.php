<?php

namespace Tests\Feature;

use App\Models\Gelombang;
use App\Models\JenisBerkas;
use App\Models\Jenjang;
use App\Models\Pendaftaran;
use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Gelombang tutup otomatis menurut tanggal; pendaftaran Draft dapat dilanjutkan di gelombang aktif berikutnya. */
class GelombangTutupTest extends TestCase
{
    use DatabaseTransactions;

    private Gelombang $lama;

    private Gelombang $baru;

    private array $berkasUji = [];

    protected function setUp(): void
    {
        parent::setUp();

        Gelombang::query()->update(['is_aktif' => false]);
        $this->lama = Gelombang::create([
            'nama' => 'Gelombang Tutup Uji', 'tahun_ajaran' => '2031/2032', 'tgl_buka' => today()->subDays(30), 'tgl_tutup' => today()->subDay(),
            'tgl_pengumuman' => today()->addDays(5), 'is_aktif' => true, // aktif tetapi sudah lewat tanggal tutup
        ]);
        $this->baru = Gelombang::create([
            'nama' => 'Gelombang Berikut Uji', 'tahun_ajaran' => '2031/2032', 'tgl_buka' => today()->subDay(), 'tgl_tutup' => today()->addDays(20),
            'tgl_pengumuman' => today()->addDays(30), 'is_aktif' => false,
        ]);
    }

    protected function tearDown(): void
    {
        foreach ($this->berkasUji as $path) {
            Storage::delete($path);
        }
        parent::tearDown();
    }

    private function bukaGelombangBaru(): void
    {
        $this->lama->update(['is_aktif' => false]);
        $this->baru->update(['is_aktif' => true]);
    }

    private function peserta(string $status, string $email, string $nik = '3277014544440001', ?Gelombang $g = null): User
    {
        $u = User::create(['name' => 'Wali Uji', 'email' => $email, 'phone' => '0816'.random_int(10000000, 99999999), 'password' => 'rahasia123']);
        $u->forceFill(['role' => 'pendaftar'])->save();

        Pendaftaran::create([
            'user_id' => $u->id, 'gelombang_id' => ($g ?? $this->lama)->id, 'jenjang_id' => Jenjang::where('kode', 'SMP')->value('id'),
            'kode' => Pendaftaran::buatKode(), 'status' => $status, 'nama_siswa' => 'Anak Uji', 'nik' => $nik, 'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Bogor', 'tgl_lahir' => '2013-05-05', 'alamat_siswa' => 'Bogor', 'nama_ayah' => 'Ayah', 'nama_ibu' => 'Ibu',
            'hp_ortu' => '081200001111', 'setuju_data' => true, 'submitted_at' => now()->subDays(10),
        ]);

        return $u;
    }

    public function test_draft_di_gelombang_yang_sudah_ditutup_terkunci(): void
    {
        $u = $this->peserta('draft', 'a@tutup.test');
        $a = $this->actingAs($u);

        $a->get('/pendaftar/dashboard')->assertOk()->assertSee('Gelombang Tutup Uji sudah ditutup')->assertSee('saat gelombang berikutnya dibuka');
        $a->get('/pendaftar/upload-berkas')->assertOk()->assertSee('sudah ditutup')->assertDontSee('Berkas terkunci karena');

        $a->post('/pendaftar/form-data', ['aksi' => 'draft', 'nama' => 'Ubah']) ->assertSessionHas('error');
        $this->assertSame('Anak Uji', $u->pendaftaran()->first()->nama_siswa, 'formulir tidak berubah');

        $berkas = $this->peserta('draft', 'b@tutup.test', '3277014544440002');
        $this->actingAs($berkas)->post('/pendaftar/upload-berkas/'.JenisBerkas::where('wajib', true)->value('id'), ['berkas' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')])
            ->assertSessionHas('error');
        $this->assertSame(0, $berkas->pendaftaran()->first()->berkas()->count());
    }

    public function test_draft_bisa_dilanjutkan_di_gelombang_yang_dibuka(): void
    {
        $u = $this->peserta('draft', 'c@tutup.test');
        $p = $u->pendaftaran()->first();
        $kode = $p->kode;

        $this->actingAs($u)->post('/pendaftar/lanjutkan-gelombang')->assertSessionHas('error'); // belum ada gelombang dibuka

        $this->bukaGelombangBaru();
        $this->actingAs($u)->get('/pendaftar/dashboard')->assertSee('Lanjutkan di Gelombang Berikut Uji');
        $this->actingAs($u)->post('/pendaftar/lanjutkan-gelombang')->assertRedirect('/pendaftar/dashboard')->assertSessionHas('status');

        $p->refresh();
        $this->assertSame($this->baru->id, $p->gelombang_id);
        $this->assertSame($kode, $p->kode, 'kode dan data ikut terbawa');
        $this->assertSame('draft', $p->status);
        $this->assertStringContainsString('Dilanjutkan dari Gelombang Tutup Uji ke Gelombang Berikut Uji', $p->riwayat()->latest('id')->value('keterangan'));

        // sudah di gelombang yang dibuka: terbuka lagi untuk diubah, tawaran lanjut hilang
        $this->actingAs($u)->get('/pendaftar/dashboard')->assertDontSee('sudah ditutup')->assertDontSee('Lanjutkan di Gelombang');
        $this->actingAs($u)->post('/pendaftar/form-data', ['aksi' => 'draft', 'nama' => 'Anak Uji Baru'])->assertSessionHasNoErrors()->assertSessionMissing('error');
        $this->assertSame('Anak Uji Baru', $p->fresh()->nama_siswa);
    }

    public function test_yang_menunggu_dan_perbaikan_tidak_terkunci_oleh_tanggal(): void
    {
        $menunggu = $this->peserta('menunggu', 'd@tutup.test', '3277014544440003');
        $this->actingAs($menunggu)->get('/pendaftar/dashboard')->assertDontSee('sudah ditutup');
        $this->actingAs($menunggu)->post('/pendaftar/lanjutkan-gelombang')->assertSessionHas('error');

        $perbaikan = $this->peserta('perbaikan', 'e@tutup.test', '3277014544440004');
        $this->actingAs($perbaikan)->post('/pendaftar/form-data', ['aksi' => 'draft', 'nama' => 'Diperbaiki'])->assertSessionMissing('error');
        $this->assertSame('Diperbaiki', $perbaikan->pendaftaran()->first()->nama_siswa);
    }

    public function test_setelah_pengumuman_terbit_perbaikan_terkunci_dan_pesan_jelas(): void
    {
        Pengumuman::create(['gelombang_id' => $this->lama->id, 'judul' => 'Hasil uji', 'published_at' => now()]);

        $perbaikan = $this->peserta('perbaikan', 'f@tutup.test', '3277014544440005');
        $this->actingAs($perbaikan)->post('/pendaftar/form-data', ['aksi' => 'draft', 'nama' => 'Terlambat'])->assertSessionHas('error');
        $this->assertSame('Anak Uji', $perbaikan->pendaftaran()->first()->nama_siswa);
        $this->actingAs($perbaikan)->post('/pendaftar/kirim-perbaikan')->assertSessionHas('error');

        $draft = $this->peserta('draft', 'g@tutup.test', '3277014544440006');
        $this->actingAs($draft)->get('/pendaftar/pengumuman')->assertOk()
            ->assertSee('Pendaftaran Anda belum mendapat hasil')->assertSee('belum lengkap saat gelombang ditutup')
            ->assertDontSee('Hasil belum diumumkan')->assertDontSee('id="cd-d"', false);

        $menunggu = $this->peserta('menunggu', 'h@tutup.test', '3277014544440007');
        $this->actingAs($menunggu)->get('/pendaftar/pengumuman')->assertSee('belum selesai diproses panitia');

        $this->bukaGelombangBaru();
        $this->actingAs($draft)->get('/pendaftar/pengumuman')->assertSee('Lanjutkan di Gelombang Berikut Uji');
    }

    public function test_nik_ganda_di_gelombang_tujuan_menghalangi_pindah(): void
    {
        $u = $this->peserta('draft', 'i@tutup.test', '3277014544440008');
        $this->peserta('menunggu', 'j@tutup.test', '3277014544440008', $this->baru); // pendaftar lain, NIK sama, tahun ajaran sama
        $this->bukaGelombangBaru();

        $this->actingAs($u)->post('/pendaftar/lanjutkan-gelombang')->assertSessionHas('error');
        $this->assertSame($this->lama->id, $u->pendaftaran()->first()->gelombang_id);
    }

    public function test_modal_publikasi_memperingatkan_pendaftar_yang_belum_selesai(): void
    {
        $this->peserta('draft', 'k@tutup.test', '3277014544440009');
        $this->peserta('menunggu', 'l@tutup.test', '3277014544440010');
        $admin = User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin)->get('/admin/hasil-seleksi')->assertOk()
            ->assertSee('tidak ikut')->assertSee('1 Menunggu Verifikasi')->assertSee('1 Draft (berkas belum lengkap)');
    }
}
