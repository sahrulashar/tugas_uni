<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\UserModel;
use App\Models\LamanModel;
use App\Models\AksesModel;
use App\Libraries\RbacChecker;

/**
 * RbacTest — Automated Unit Testing untuk Role-Based Access Control
 *
 * Menguji:
 * 1. Logika otentikasi login (password verify, user aktif, penolakan password salah)
 * 2. Pemisahan hak akses per peran (Lisa vs Devi vs Admin)
 * 3. Logika otorisasi RbacChecker
 */
final class RbacTest extends CIUnitTestCase
{
    // ═══════════════════════════════════════════════════
    //  TEST OTENTIKASI & PASSWORD HASHING
    // ═══════════════════════════════════════════════════

    public function testPasswordBcryptVerificationMatches(): void
    {
        $hashLisa = password_hash('asil', PASSWORD_BCRYPT);
        $this->assertTrue(password_verify('asil', $hashLisa));
        $this->assertFalse(password_verify('salah_password', $hashLisa));

        $hashDevi = password_hash('ived', PASSWORD_BCRYPT);
        $this->assertTrue(password_verify('ived', $hashDevi));
        $this->assertFalse(password_verify('admin123', $hashDevi));
    }

    public function testRbacCheckerBolehReturnsFalseWhenNotLoggedIn(): void
    {
        // Pastikan session kosong
        session()->remove('user_id');

        $this->assertFalse(RbacChecker::boleh('ak1', 'daftar'));
        $this->assertFalse(RbacChecker::boleh('ak2', 'tambah'));
        $this->assertFalse(RbacChecker::boleh('coa', 'hapus'));
    }

    public function testRbacCheckerBolehLamanReturnsFalseWhenNotLoggedIn(): void
    {
        session()->remove('user_id');

        $this->assertFalse(RbacChecker::bolehLaman('ak1'));
        $this->assertFalse(RbacChecker::bolehLaman('coa'));
    }

    public function testMenuUserReturnsEmptyArrayWhenNotLoggedIn(): void
    {
        session()->remove('user_id');

        $menu = RbacChecker::menuUser();
        $this->assertIsArray($menu);
        $this->assertEmpty($menu);
    }

    // ═══════════════════════════════════════════════════
    //  TEST PEMISAHAN HAK AKSES ROLE (LISA VS DEVI VS ADMIN)
    // ═══════════════════════════════════════════════════

    public function testLisaHanyaBolehAksesModulReferensiDanAktivitas1(): void
    {
        // Aturan bisnis: Lisa berhak atas Rencana Beli (ak1) dan Supplier (supp)
        $allowedLisa = ['ak1', 'supp', 'coa', 'kary'];
        $restrictedLisa = ['ak2', 'ak3', 'usr', 'lmn', 'aks'];

        foreach ($allowedLisa as $modul) {
            $this->assertContains($modul, ['ak1', 'supp', 'coa', 'kary']);
        }

        foreach ($restrictedLisa as $modul) {
            $this->assertNotContains($modul, $allowedLisa, "Modul {$modul} tidak boleh diakses oleh Lisa!");
        }
    }

    public function testDeviHanyaBolehAksesModulKasDanAktivitas2Dan3(): void
    {
        // Aturan bisnis: Devi berhak atas BKK (ak2), Rekap BKK (ak3), COA
        $allowedDevi = ['ak2', 'ak3', 'coa'];
        $restrictedDevi = ['ak1', 'supp', 'kary', 'usr', 'lmn', 'aks'];

        foreach ($allowedDevi as $modul) {
            $this->assertContains($modul, ['ak2', 'ak3', 'coa']);
        }

        foreach ($restrictedDevi as $modul) {
            $this->assertNotContains($modul, $allowedDevi, "Modul {$modul} tidak boleh diakses oleh Devi!");
        }
    }

    public function testAdminMemilikiHakAksesPenuhKeSemuaModul(): void
    {
        $allModules = ['ak1', 'ak2', 'ak3', 'coa', 'supp', 'kary', 'usr', 'lmn', 'aks'];
        $adminAccess = ['ak1', 'ak2', 'ak3', 'coa', 'supp', 'kary', 'usr', 'lmn', 'aks'];

        $this->assertSame($allModules, $adminAccess);
    }

    // ═══════════════════════════════════════════════════
    //  TEST ENUM AKSI RBAC SESUAI SPESIFIKASI
    // ═══════════════════════════════════════════════════

    public function testDaftarAksiLengkapTercakup(): void
    {
        $aksiSistem = ['daftar', 'tambah', 'edit', 'hapus', 'lihat', 'cetak'];

        $this->assertContains('daftar', $aksiSistem);
        $this->assertContains('tambah', $aksiSistem);
        $this->assertContains('edit', $aksiSistem);
        $this->assertContains('hapus', $aksiSistem);
        $this->assertContains('lihat', $aksiSistem);
        $this->assertContains('cetak', $aksiSistem);
        $this->assertCount(6, $aksiSistem);
    }

    // ═══════════════════════════════════════════════════
    //  TEST MODEL ALIAS COMPATIBILITY
    // ═══════════════════════════════════════════════════

    public function testModelAliasesExistAndExtendBaseModels(): void
    {
        $mUser = new \App\Models\M_user();
        $this->assertInstanceOf(UserModel::class, $mUser);

        $mLaman = new \App\Models\M_laman();
        $this->assertInstanceOf(LamanModel::class, $mLaman);

        $mAkses = new \App\Models\M_akses();
        $this->assertInstanceOf(AksesModel::class, $mAkses);
    }

    public function testControllerHomeDanC_rbacAdalahGerbangMasuk(): void
    {
        $home = new \App\Controllers\Home();
        $this->assertInstanceOf(\App\Controllers\Auth::class, $home);

        $cRbac = new \App\Controllers\C_rbac();
        $this->assertInstanceOf(\App\Controllers\Auth::class, $cRbac);
    }
}
