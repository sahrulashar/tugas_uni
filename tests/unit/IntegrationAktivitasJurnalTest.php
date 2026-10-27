<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\JurnalService;
use App\Services\NeracaSaldoService;
use App\Models\TbBkkModel;
use App\Models\TbBkkDModel;
use App\Models\TbRbeliModel;
use App\Models\TbRbeliDModel;

/**
 * IntegrationAktivitasJurnalTest
 *
 * Menguji integrasi siklus Aktivitas Kas Keluar (BKK) ke Jurnal Umum & Neraca Saldo:
 *   1. JurnalService: Double-entry BKK balance debit & kredit
 *   2. Validasi input: Nilai nol/negatif & detail kosong ditolak
 *   3. End-to-End Simpan BKK: Header + Detail -> Jurnal Umum otomatis (sumber = 'BKK')
 *   4. End-to-End Edit BKK: Update nominal -> Jurnal lama digantikan jurnal baru
 *   5. End-to-End Soft Delete: Hapus BKK -> Jurnal umum dibatalkan (mencegah jurnal hantu)
 *   6. End-to-End Restore: Pemulihan BKK -> Jurnal umum tercatat kembali secara berimbang
 *   7. Neraca Saldo: Memastikan pembukuan tetap 100% balance
 */
final class IntegrationAktivitasJurnalTest extends CIUnitTestCase
{
    private JurnalService $jurnalService;
    private NeracaSaldoService $neracaService;
    private TbBkkModel $bkkModel;
    private TbBkkDModel $bkkDModel;
    private TbRbeliModel $rbeliModel;
    private TbRbeliDModel $rbeliDModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jurnalService = new JurnalService();
        $this->neracaService = new NeracaSaldoService();
        $this->bkkModel      = new TbBkkModel();
        $this->bkkDModel     = new TbBkkDModel();
        $this->rbeliModel    = new TbRbeliModel();
        $this->rbeliDModel   = new TbRbeliDModel();
    }

    // ───────────────────────────────────────────
    //  1. PURE LOGIC JURNALSERVICE (BKK)
    // ───────────────────────────────────────────

    public function testBkkJurnalDoubleEntryBalance(): void
    {
        $header = [
            'no_bkk' => 'BKK-TEST-UNIT-01',
            'tgl'    => date('Y-m-d'),
            'kete'   => 'Pengeluaran kas perlengkapan kantor',
        ];

        // id_coa 13 (Kewajiban), id_coa_kb 4 (Kas Kecil)
        $detail = [
            [
                'id_coa'    => 13,
                'id_coa_kb' => 4,
                'nilai'     => 150000.0,
            ],
            [
                'id_coa'    => 24,
                'id_coa_kb' => 4,
                'nilai'     => 75000.0,
            ],
        ];

        $res = $this->jurnalService->buatEntriJurnalBkk($header, $detail);

        $this->assertTrue($this->jurnalService->cekBalance($res['entries']));
        $this->assertSame(225000.0, $res['total_debit']);
        $this->assertSame(225000.0, $res['total_kredit']);
        $this->assertCount(4, $res['entries']); // 2 baris * 2 ayat (D & K)

        // Cek baris pertama (Debet & Kredit)
        $this->assertSame(150000.0, $res['entries'][0]['debit']);
        $this->assertSame(0.0, $res['entries'][0]['kredit']);
        $this->assertSame(0.0, $res['entries'][1]['debit']);
        $this->assertSame(150000.0, $res['entries'][1]['kredit']);
    }

    public function testBkkDetailKosongDitolak(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->jurnalService->buatEntriJurnalBkk(['no_bkk' => 'BKK-0'], []);
    }

    public function testBkkNilaiNolAtauNegatifDitolak(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->jurnalService->buatEntriJurnalBkk(
            ['no_bkk' => 'BKK-0'],
            [['id_coa' => 13, 'id_coa_kb' => 4, 'nilai' => -5000.0]]
        );
    }

    // ───────────────────────────────────────────
    //  2. END-TO-END DATABASE INTEGRATION (ACID)
    // ───────────────────────────────────────────

    public function testSiklusLengkapBkkSimpanEditHapusDanRestore(): void
    {
        $db = \Config\Database::connect('default');

        // Pastikan akun master tersedia
        $coaDebit = $db->table('coa')->where('is_off', 0)->where('saldo_normal', 'Debit')->get()->getRowArray();
        $coaKas   = $db->table('coa')->where('is_off', 0)->where('tipe', 'kasbank')->get()->getRowArray();

        $this->assertNotNull($coaDebit, 'Master COA Debit harus tersedia');
        $this->assertNotNull($coaKas, 'Master COA Kas/Bank harus tersedia');

        $noBkk = 'BKK-TEST-' . date('His') . '-' . rand(100, 999);
        $tgl   = date('Y-m-d');
        $kete  = 'BKK Test Siklus Akuntansi CI4';

        // ── STEP A: Simpan BKK Baru ─────────────────
        $db->transStart();

        $idBkk = $this->bkkModel->insert([
            'no_bkk' => $noBkk,
            'tgl'    => $tgl,
            'kete'   => $kete,
        ]);

        $baris = [
            [
                'id_coa'     => (int) $coaDebit['id'],
                'id_coa_kb'  => (int) $coaKas['id'],
                'nilai'      => 125000.0,
                'id_rbeli_d' => null,
            ],
        ];

        foreach ($baris as $b) {
            $this->bkkDModel->insert($b + ['id_bkk' => $idBkk]);
        }

        $idJurnal = $this->jurnalService->generateDanSimpanJurnalBkk([
            'no_bkk' => $noBkk,
            'tgl'    => $tgl,
            'kete'   => $kete,
        ], $baris, $db);

        $db->transComplete();

        $this->assertTrue($db->transStatus());
        $this->assertGreaterThan(0, $idBkk);
        $this->assertGreaterThan(0, $idJurnal);

        // Verifikasi Jurnal Terbentuk
        $jurnal = $db->table('jurnal_umum')->where('no_referensi', $noBkk)->get()->getRowArray();
        $this->assertNotNull($jurnal, "Jurnal umum harus terbentuk untuk {$noBkk}");
        $this->assertSame('BKK', $jurnal['sumber']);
        $this->assertSame(125000.0, (float) $jurnal['total_debit']);
        $this->assertSame(125000.0, (float) $jurnal['total_kredit']);

        $detailJurnal = $db->table('jurnal_umum_d')->where('id_jurnal', $jurnal['id'])->get()->getResultArray();
        $this->assertCount(2, $detailJurnal);

        // ── STEP B: Update / Edit BKK ───────────────
        $db->transStart();

        $keteBaru   = 'BKK Test Siklus Akuntansi CI4 (Updated)';
        $nilaiBaru  = 175000.0;
        $barisBaru  = [
            [
                'id_coa'     => (int) $coaDebit['id'],
                'id_coa_kb'  => (int) $coaKas['id'],
                'nilai'      => $nilaiBaru,
                'id_rbeli_d' => null,
            ],
        ];

        $this->bkkModel->update($idBkk, ['kete' => $keteBaru]);
        $this->bkkDModel->hapusByIdBkk_l1H($idBkk);
        foreach ($barisBaru as $b) {
            $this->bkkDModel->insert($b + ['id_bkk' => $idBkk]);
        }

        $this->jurnalService->batalkanJurnalByReferensi('BKK', $noBkk, $db);
        $this->jurnalService->generateDanSimpanJurnalBkk([
            'no_bkk' => $noBkk,
            'tgl'    => $tgl,
            'kete'   => $keteBaru,
        ], $barisBaru, $db);

        $db->transComplete();
        $this->assertTrue($db->transStatus());

        // Verifikasi Jurnal Terupdate
        $jurnalUpdated = $db->table('jurnal_umum')->where('no_referensi', $noBkk)->get()->getResultArray();
        $this->assertCount(1, $jurnalUpdated, 'Hanya boleh ada 1 jurnal aktif setelah edit (jurnal lama terhapus)');
        $this->assertSame(175000.0, (float) $jurnalUpdated[0]['total_debit']);
        $this->assertSame(175000.0, (float) $jurnalUpdated[0]['total_kredit']);

        // ── STEP C: Soft Delete BKK ─────────────────
        $db->transStart();
        $this->bkkModel->softDelete_l1H($idBkk);
        $this->jurnalService->batalkanJurnalByReferensi('BKK', $noBkk, $db);
        $db->transComplete();

        // Verifikasi Jurnal Dibatalkan / Dihapus
        $jurnalHapus = $db->table('jurnal_umum')->where('no_referensi', $noBkk)->get()->getRowArray();
        $this->assertNull($jurnalHapus, 'Jurnal harus terhapus/dibatalkan saat BKK di-soft-delete');

        // ── STEP D: Restore BKK ─────────────────────
        $db->transStart();
        $this->bkkModel->update($idBkk, ['is_deleted' => 0]);
        $detailRestore = $this->bkkDModel->getByIdBkk_l1H($idBkk);
        $this->jurnalService->generateDanSimpanJurnalBkk([
            'no_bkk' => $noBkk,
            'tgl'    => $tgl,
            'kete'   => $keteBaru,
        ], $detailRestore, $db);
        $db->transComplete();

        // Verifikasi Jurnal Muncul Kembali
        $jurnalRestore = $db->table('jurnal_umum')->where('no_referensi', $noBkk)->get()->getRowArray();
        $this->assertNotNull($jurnalRestore, 'Jurnal harus kembali aktif setelah restore');
        $this->assertSame(175000.0, (float) $jurnalRestore['total_debit']);

        // ── STEP E: Validasi Neraca Saldo ───────────
        $neraca = $this->neracaService->ambilNeracaSaldoDariDb($db);
        $this->assertTrue($neraca['is_balance'], 'Neraca Saldo harus tetap seimbang 100%');
        $this->assertLessThan(0.01, $neraca['selisih']);

        // Cleanup test data
        $this->jurnalService->batalkanJurnalByReferensi('BKK', $noBkk, $db);
        $this->bkkDModel->hapusByIdBkk_l1H($idBkk);
        $db->table('tbbkk')->where('id', $idBkk)->delete();
    }

    // ───────────────────────────────────────────
    //  3. PURE LOGIC JURNALSERVICE (RENCANA BELI - AKTIVITAS 1)
    // ───────────────────────────────────────────

    public function testRbeliJurnalDoubleEntryBalance(): void
    {
        $header = [
            'no_rbeli' => 'RB-TEST-UNIT-01',
            'tgl'      => date('Y-m-d'),
            'id_supp'  => 1,
            'kete'     => 'Rencana pembelian bahan baku kopi',
        ];

        $detail = [
            [
                'no_faktur' => 'INV-TEST-001',
                'nilai'     => 500000.0,
            ],
            [
                'no_faktur' => 'INV-TEST-002',
                'nilai'     => 250000.0,
            ],
        ];

        $res = $this->jurnalService->buatEntriJurnalRbeli($header, $detail);

        $this->assertTrue($this->jurnalService->cekBalance($res['entries']));
        $this->assertSame(750000.0, $res['total_debit']);
        $this->assertSame(750000.0, $res['total_kredit']);
        $this->assertCount(4, $res['entries']); // 2 faktur * 2 ayat (D: Pembelian, K: Utang Usaha)

        // Cek baris pertama (Debet Pembelian 5111 & Kredit Utang Usaha 2111)
        $this->assertSame(JurnalService::AKUN_PEMBELIAN, $res['entries'][0]['kode_coa']);
        $this->assertSame(500000.0, $res['entries'][0]['debit']);
        $this->assertSame(0.0, $res['entries'][0]['kredit']);

        $this->assertSame(JurnalService::AKUN_UTANG_USAHA, $res['entries'][1]['kode_coa']);
        $this->assertSame(0.0, $res['entries'][1]['debit']);
        $this->assertSame(500000.0, $res['entries'][1]['kredit']);

        // Cek baris kedua
        $this->assertSame(JurnalService::AKUN_PEMBELIAN, $res['entries'][2]['kode_coa']);
        $this->assertSame(250000.0, $res['entries'][2]['debit']);
        $this->assertSame(0.0, $res['entries'][2]['kredit']);

        $this->assertSame(JurnalService::AKUN_UTANG_USAHA, $res['entries'][3]['kode_coa']);
        $this->assertSame(0.0, $res['entries'][3]['debit']);
        $this->assertSame(250000.0, $res['entries'][3]['kredit']);
    }

    public function testRbeliDetailKosongDitolak(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->jurnalService->buatEntriJurnalRbeli(['no_rbeli' => 'RB-0'], []);
    }

    public function testRbeliNilaiNolAtauNegatifDitolak(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->jurnalService->buatEntriJurnalRbeli(
            ['no_rbeli' => 'RB-0'],
            [['no_faktur' => 'INV-NEG', 'nilai' => -10000.0]]
        );
    }

    // ───────────────────────────────────────────
    //  4. DATABASE INTEGRATION RENCANA BELI (ACID)
    // ───────────────────────────────────────────

    public function testSiklusLengkapRbeliSimpanEditHapusDanRestore(): void
    {
        $db = \Config\Database::connect('default');

        $noRbeli = 'RB-TEST-' . date('His') . '-' . rand(100, 999);
        $tgl     = date('Y-m-d');
        $kete    = 'Test Siklus Rencana Beli CI4';

        // ── STEP A: Simpan Rencana Beli Baru ───────
        $db->transStart();

        $idRbeli = $this->rbeliModel->insert([
            'no_rbeli' => $noRbeli,
            'tgl'      => $tgl,
            'id_supp'  => 1,
            'kete'     => $kete,
        ]);

        $detail = [
            [
                'id_rbeli'  => $idRbeli,
                'no_faktur' => 'FK-TEST-01',
                'nilai'     => 300000.0,
            ],
        ];

        foreach ($detail as $d) {
            $this->rbeliDModel->insert($d);
        }

        $idJurnal = $this->jurnalService->generateDanSimpanJurnalRbeli([
            'no_rbeli' => $noRbeli,
            'tgl'      => $tgl,
            'id_supp'  => 1,
            'kete'     => $kete,
        ], $detail, $db);

        $db->transComplete();

        $this->assertTrue($db->transStatus());
        $this->assertGreaterThan(0, $idRbeli);
        $this->assertGreaterThan(0, $idJurnal);

        // Verifikasi Jurnal Terbentuk
        $jurnal = $db->table('jurnal_umum')->where('no_referensi', $noRbeli)->get()->getRowArray();
        $this->assertNotNull($jurnal, "Jurnal umum harus terbentuk untuk {$noRbeli}");
        $this->assertSame('RBELI', $jurnal['sumber']);
        $this->assertSame(300000.0, (float) $jurnal['total_debit']);
        $this->assertSame(300000.0, (float) $jurnal['total_kredit']);

        // Verifikasi ayat jurnal detail: Pembelian (D) dan Utang Usaha (K)
        $detailJurnal = $db->table('jurnal_umum_d')->where('id_jurnal', $jurnal['id'])->get()->getResultArray();
        $this->assertCount(2, $detailJurnal);
        $this->assertSame(JurnalService::AKUN_PEMBELIAN, $detailJurnal[0]['kode_coa']);
        $this->assertSame(300000.0, (float) $detailJurnal[0]['debit']);
        $this->assertSame(JurnalService::AKUN_UTANG_USAHA, $detailJurnal[1]['kode_coa']);
        $this->assertSame(300000.0, (float) $detailJurnal[1]['kredit']);

        // ── STEP B: Update / Edit Rencana Beli ─────
        $db->transStart();

        $keteBaru   = 'Test Siklus Rencana Beli CI4 (Updated)';
        $nilaiBaru  = 450000.0;
        $detailBaru = [
            [
                'id_rbeli'  => $idRbeli,
                'no_faktur' => 'FK-TEST-01',
                'nilai'     => $nilaiBaru,
            ],
        ];

        $this->rbeliModel->update($idRbeli, ['kete' => $keteBaru]);
        $this->rbeliDModel->hapusByIdRbeli_l1H($idRbeli);
        foreach ($detailBaru as $d) {
            $this->rbeliDModel->insert($d);
        }

        $this->jurnalService->batalkanJurnalByReferensi('RBELI', $noRbeli, $db);
        $this->jurnalService->generateDanSimpanJurnalRbeli([
            'no_rbeli' => $noRbeli,
            'tgl'      => $tgl,
            'id_supp'  => 1,
            'kete'     => $keteBaru,
        ], $detailBaru, $db);

        $db->transComplete();
        $this->assertTrue($db->transStatus());

        // Verifikasi Jurnal Terupdate
        $jurnalUpdated = $db->table('jurnal_umum')->where('no_referensi', $noRbeli)->get()->getResultArray();
        $this->assertCount(1, $jurnalUpdated, 'Hanya boleh ada 1 jurnal aktif setelah edit (jurnal lama terhapus)');
        $this->assertSame(450000.0, (float) $jurnalUpdated[0]['total_debit']);
        $this->assertSame(450000.0, (float) $jurnalUpdated[0]['total_kredit']);

        // ── STEP C: Soft Delete Rencana Beli ───────
        $db->transStart();
        $this->rbeliModel->softDelete_l1H($idRbeli);
        $this->jurnalService->batalkanJurnalByReferensi('RBELI', $noRbeli, $db);
        $db->transComplete();

        // Verifikasi Jurnal Dibatalkan / Dihapus
        $jurnalHapus = $db->table('jurnal_umum')->where('no_referensi', $noRbeli)->get()->getRowArray();
        $this->assertNull($jurnalHapus, 'Jurnal harus terhapus/dibatalkan saat Rencana Beli di-soft-delete');

        // ── STEP D: Restore Rencana Beli ───────────
        $db->transStart();
        $this->rbeliModel->update($idRbeli, ['is_deleted' => 0]);
        $detailRestore = $this->rbeliDModel->getByIdRbeli_l1H($idRbeli);
        $this->jurnalService->generateDanSimpanJurnalRbeli([
            'no_rbeli' => $noRbeli,
            'tgl'      => $tgl,
            'id_supp'  => 1,
            'kete'     => $keteBaru,
        ], $detailRestore, $db);
        $db->transComplete();

        // Verifikasi Jurnal Muncul Kembali
        $jurnalRestore = $db->table('jurnal_umum')->where('no_referensi', $noRbeli)->get()->getRowArray();
        $this->assertNotNull($jurnalRestore, 'Jurnal harus kembali aktif setelah restore');
        $this->assertSame(450000.0, (float) $jurnalRestore['total_debit']);

        // ── STEP E: Validasi Neraca Saldo ─────────
        $neraca = $this->neracaService->ambilNeracaSaldoDariDb($db);
        $this->assertTrue($neraca['is_balance'], 'Neraca Saldo harus tetap seimbang 100%');
        $this->assertLessThan(0.01, $neraca['selisih']);

        // Cleanup test data
        $this->jurnalService->batalkanJurnalByReferensi('RBELI', $noRbeli, $db);
        $this->rbeliDModel->hapusByIdRbeli_l1H($idRbeli);
        $db->table('tbrbeli')->where('id', $idRbeli)->delete();
    }

    // ───────────────────────────────────────────
    //  5. SIKLUS TERINTEGRASI: RENCANA BELI → BUKTI KAS KELUAR
    // ───────────────────────────────────────────

    public function testSiklusLengkapHubunganRbeliDanBkk(): void
    {
        $db = \Config\Database::connect('default');

        $coaKas = $db->table('coa')->where('is_off', 0)->where('tipe', 'kasbank')->get()->getRowArray();
        $coaUtang = $db->table('coa')->where('kode_coa', JurnalService::AKUN_UTANG_USAHA)->get()->getRowArray();

        $this->assertNotNull($coaKas, 'Master COA Kas/Bank harus tersedia');
        $this->assertNotNull($coaUtang, 'Master COA Utang Usaha harus tersedia');

        $noRbeli = 'RB-FLOW-' . date('His') . '-' . rand(100, 999);
        $noBkk   = 'BKK-FLOW-' . date('His') . '-' . rand(100, 999);
        $nominal = 250000.0;

        // 1. Aktivitas 1: Simpan Rencana Beli -> [D] Pembelian, [K] Utang Usaha
        $db->transStart();
        $idRbeli = $this->rbeliModel->insert([
            'no_rbeli' => $noRbeli,
            'tgl'      => date('Y-m-d'),
            'id_supp'  => 1,
            'kete'     => 'Flow Test Rencana Beli',
        ]);
        $idRbeliD = $this->rbeliDModel->insert([
            'id_rbeli'  => $idRbeli,
            'no_faktur' => 'FK-FLOW-99',
            'nilai'     => $nominal,
        ]);
        $this->jurnalService->generateDanSimpanJurnalRbeli([
            'no_rbeli' => $noRbeli,
            'tgl'      => date('Y-m-d'),
            'id_supp'  => 1,
            'kete'     => 'Flow Test Rencana Beli',
        ], [['no_faktur' => 'FK-FLOW-99', 'nilai' => $nominal]], $db);
        $db->transComplete();

        // 2. Aktivitas 2: Bukti Kas Keluar pelunasan -> [D] Utang Usaha, [K] Kas/Bank
        $db->transStart();
        $idBkk = $this->bkkModel->insert([
            'no_bkk' => $noBkk,
            'tgl'    => date('Y-m-d'),
            'kete'   => 'Pelunasan faktur FK-FLOW-99 via BKK',
        ]);
        $barisBkk = [
            [
                'id_bkk'     => $idBkk,
                'id_rbeli_d' => $idRbeliD,
                'id_coa'     => (int) $coaUtang['id'], // Utang Usaha di-Debet
                'id_coa_kb'  => (int) $coaKas['id'],   // Kas/Bank di-Kredit
                'nilai'      => $nominal,
            ],
        ];
        $this->bkkDModel->insert($barisBkk[0]);
        $this->jurnalService->generateDanSimpanJurnalBkk([
            'no_bkk' => $noBkk,
            'tgl'    => date('Y-m-d'),
            'kete'   => 'Pelunasan faktur FK-FLOW-99 via BKK',
        ], $barisBkk, $db);
        $db->transComplete();

        // Validasi: Kedua jurnal tersimpan
        $jurnalRb = $db->table('jurnal_umum')->where('no_referensi', $noRbeli)->get()->getRowArray();
        $jurnalBkk = $db->table('jurnal_umum')->where('no_referensi', $noBkk)->get()->getRowArray();

        $this->assertNotNull($jurnalRb, "Jurnal Rencana Beli {$noRbeli} harus ada");
        $this->assertNotNull($jurnalBkk, "Jurnal BKK {$noBkk} harus ada");
        $this->assertSame('RBELI', $jurnalRb['sumber']);
        $this->assertSame('BKK', $jurnalBkk['sumber']);

        // Neraca Saldo tetap balance
        $neraca = $this->neracaService->ambilNeracaSaldoDariDb($db);
        $this->assertTrue($neraca['is_balance'], 'Neraca Saldo harus tetap balance setelah alur RBeli + BKK');
        $this->assertLessThan(0.01, $neraca['selisih']);

        // Cleanup
        $this->jurnalService->batalkanJurnalByReferensi('RBELI', $noRbeli, $db);
        $this->jurnalService->batalkanJurnalByReferensi('BKK', $noBkk, $db);
        $this->bkkDModel->hapusByIdBkk_l1H($idBkk);
        $db->table('tbbkk')->where('id', $idBkk)->delete();
        $this->rbeliDModel->hapusByIdRbeli_l1H($idRbeli);
        $db->table('tbrbeli')->where('id', $idRbeli)->delete();
    }
}
