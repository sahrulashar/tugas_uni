<?php

namespace App\Controllers;

use App\Services\DashboardAnalyticsService;
use App\Libraries\AuditLogger;

/**
 * ExecutiveDashboard Controller
 *
 * Mengelola antarmuka Executive Dashboard, visualisasi grafik analitik,
 * dan Laboratorium Praktikum Pertemuan 10-11.
 *
 * Mengikuti konvensi proyek:
 *   - Route Prefix: /46124026/dashboard/*
 *   - Suffix Method: _l1H
 *   - RBAC: Terproteksi kode laman 'dsh' & 'pkt10'
 */
class ExecutiveDashboard extends BaseController
{
    protected DashboardAnalyticsService $analyticsService;

    public function __construct()
    {
        $this->analyticsService = new DashboardAnalyticsService();
    }

    // ─────────────────────────────────────────────────────────────────
    //  1. EXECUTIVE DASHBOARD VIEW
    // ─────────────────────────────────────────────────────────────────

    /**
     * Tampilkan Executive Dashboard Interaktif (C-Level Financial Health)
     */
    public function index_l1H()
    {
        $this->cekAkses('dsh', 'daftar');

        $selectedYear = (int) ($this->request->getGet('year') ?? date('Y'));

        $kpis      = $this->analyticsService->getExecutiveKpis_l1H($selectedYear);
        $trends    = $this->analyticsService->getMonthlyCashFlowTrends_l1H($selectedYear);
        $pivotCoa  = $this->analyticsService->getPivotExpensePerCoa_l1H($selectedYear);
        $pareto    = $this->analyticsService->getParetoProducts_l1H(5);
        $suppliers = $this->analyticsService->getSupplierConcentration_l1H(5);
        $alerts    = $this->analyticsService->getHighValueOutflows_l1H(500000.00, 5);

        return view('executive/dashboard', [
            'year'      => $selectedYear,
            'kpis'      => $kpis,
            'trends'    => $trends,
            'pivotCoa'  => $pivotCoa,
            'pareto'    => $pareto,
            'suppliers' => $suppliers,
            'alerts'    => $alerts,
        ]);
    }

    /**
     * API JSON Endpoint untuk Filter Interaktif tanpa Reload Halaman
     */
    public function api_metrics_l1H()
    {
        $this->cekAkses('dsh', 'lihat');

        $year  = (int) ($this->request->getGet('year') ?? date('Y'));
        $month = $this->request->getGet('month') ? (int) $this->request->getGet('month') : null;

        $kpis      = $this->analyticsService->getExecutiveKpis_l1H($year, $month);
        $trends    = $this->analyticsService->getMonthlyCashFlowTrends_l1H($year);
        $pivotCoa  = $this->analyticsService->getPivotExpensePerCoa_l1H($year);
        $pareto    = $this->analyticsService->getParetoProducts_l1H(5);
        $suppliers = $this->analyticsService->getSupplierConcentration_l1H(5);
        $alerts    = $this->analyticsService->getHighValueOutflows_l1H(500000.00, 5);

        return $this->response->setJSON([
            'status'    => 'success',
            'year'      => $year,
            'month'     => $month,
            'kpis'      => $kpis,
            'trends'    => $trends,
            'pivotCoa'  => $pivotCoa,
            'pareto'    => $pareto,
            'suppliers' => $suppliers,
            'alerts'    => $alerts,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    //  2. PRAKTIKUM PERTEMUAN 10-11 INTERAKTIF
    // ─────────────────────────────────────────────────────────────────

    /**
     * Halaman Laboratorium Praktikum Interaktif (SQL Inspector & Sandbox)
     */
    public function praktikum_l1H()
    {
        $this->cekAkses('pkt10', 'daftar');

        $year      = (int) ($this->request->getGet('year') ?? date('Y'));
        $trends    = $this->analyticsService->getMonthlyCashFlowTrends_l1H($year);
        $pivotCoa  = $this->analyticsService->getPivotExpensePerCoa_l1H($year);
        $pareto    = $this->analyticsService->getParetoProducts_l1H(10);
        $suppliers = $this->analyticsService->getSupplierConcentration_l1H(5);

        return view('executive/praktikum', [
            'year'      => $year,
            'trends'    => $trends,
            'pivotCoa'  => $pivotCoa,
            'pareto'    => $pareto,
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Endpoint Suntik Demo Dataset (Multi-Month Transaction Simulation)
     */
    public function inject_demo_l1H()
    {
        $this->cekAkses('pkt10', 'tambah');

        $res = $this->analyticsService->injectDemoDataset_l1H();

        if ($res['status']) {
            AuditLogger::catat(
                'TAMBAH',
                'penjualan',
                0,
                ['after' => ['simulasi' => $res['message']]],
                'Analitik',
                'Suntik Dataset Demo Multi-Bulan (Pertemuan 10-11)'
            );
        }

        return $this->response->setJSON($res);
    }

    /**
     * Endpoint Bersihkan Demo Dataset
     */
    public function reset_demo_l1H()
    {
        $this->cekAkses('pkt10', 'tambah');

        $res = $this->analyticsService->resetDemoDataset_l1H();

        if ($res['status']) {
            AuditLogger::catat(
                'HAPUS',
                'penjualan',
                0,
                ['before' => ['simulasi' => $res['message']]],
                'Analitik',
                'Reset/Pembersihan Dataset Demo Multi-Bulan (Pertemuan 10-11)'
            );
        }

        return $this->response->setJSON($res);
    }
}
