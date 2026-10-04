<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Libraries\RbacChecker;

/**
 * Audit — Controller untuk melihat dan menganalisis Jejak Audit (Audit Trail Log)
 *
 * Route:
 *   GET /46124026/audit
 *   GET /46124026/audit/detail/(:num)
 *
 * @package App\Controllers
 */
class Audit extends BaseController
{
    protected AuditLogModel $auditModel;

    public function __construct()
    {
        $this->auditModel = new AuditLogModel();
    }

    /**
     * Tampilkan halaman utama audit trail log
     */
    public function index()
    {
        // Proteksi akses: Minimal memiliki hak akses ke manajemen user/akses, atau admin
        if (!RbacChecker::boleh('aks', 'daftar') && !RbacChecker::boleh('usr', 'daftar')) {
            return redirect()->to(base_url('46124026'))
                ->with('error', '⛔ Anda tidak memiliki izin untuk melihat Audit Trail Log.');
        }

        $filters = [
            'aksi'            => trim((string) $this->request->getGet('aksi')),
            'modul'           => trim((string) $this->request->getGet('modul')),
            'tabel_terdampak' => trim((string) $this->request->getGet('tabel')),
            'tanggal_mulai'   => trim((string) $this->request->getGet('mulai')),
            'tanggal_akhir'   => trim((string) $this->request->getGet('akhir')),
        ];

        // Hapus filter kosong
        $activeFilters = array_filter($filters, fn($v) => $v !== '');

        $logs          = $this->auditModel->search_l1H($activeFilters, 500);
        $stats         = $this->auditModel->getStats_l1H();
        $filterOptions = $this->auditModel->getFilterOptions_l1H();

        return view('audit/index', [
            'logs'          => $logs,
            'stats'         => $stats,
            'filterOptions' => $filterOptions,
            'activeFilters' => $activeFilters,
        ]);
    }

    /**
     * Endpoint API untuk mengambil satu record log (format JSON)
     */
    public function detail(int $id)
    {
        if (!RbacChecker::boleh('aks', 'daftar') && !RbacChecker::boleh('usr', 'daftar')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Akses ditolak']);
        }

        $log = $this->auditModel->find($id);
        if (!$log) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Log tidak ditemukan']);
        }

        // Parse detail_perubahan jika ada
        if (!empty($log['detail_perubahan'])) {
            $log['parsed_detail'] = json_decode($log['detail_perubahan'], true);
        } else {
            $log['parsed_detail'] = null;
        }

        return $this->response->setJSON($log);
    }
}
