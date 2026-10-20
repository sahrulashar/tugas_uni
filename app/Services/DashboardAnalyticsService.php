<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

/**
 * DashboardAnalyticsService
 *
 * Layanan kalkulasi analitik tingkat tinggi untuk Executive Dashboard
 * menggunakan fitur native MySQL 8.0:
 *   - Window Functions: LAG(), SUM() OVER (), DENSE_RANK()
 *   - Conditional Aggregation: Dynamic Pivot Matrix (SUM CASE WHEN)
 *   - Multi-period Financial Consolidation & Pareto Analysis
 *
 * Sesuai konvensi proyek:
 *   - Penamaan method menggunakan suffix `_l1H`
 *   - Mengikuti skema riil: tbbkk_d.nilai, supplier.id_supplier
 */
class DashboardAnalyticsService
{
    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect('default');
    }

    // ─────────────────────────────────────────────────────────────────
    //  1. EXECUTIVE KPIS SUMMARY
    // ─────────────────────────────────────────────────────────────────

    /**
     * Mengambil 4 KPI Utama Eksekutif:
     *   - Total Omset Bersih (Net Revenue) + MoM Growth %
     *   - Total Kas Keluar Operasional (BKK) + MoM Outflow %
     *   - Arus Kas Bersih (Net Operating Cashflow)
     *   - Gross Profit Margin (%)
     */
    public function getExecutiveKpis_l1H(?int $year = null, ?int $month = null): array
    {
        $targetYear = $year ?? (int) date('Y');

        // Total Penjualan & HPP tahun ini
        $builderPenjualan = $this->db->table('penjualan')
            ->select('
                COALESCE(SUM(total_netto), 0) AS total_revenue,
                COALESCE(SUM(total_hpp), 0) AS total_hpp,
                COUNT(id) AS total_transaksi
            ')
            ->where('is_deleted', 0)
            ->where('YEAR(tgl_jual)', $targetYear);

        if ($month !== null) {
            $builderPenjualan->where('MONTH(tgl_jual)', $month);
        }
        $salesRow = $builderPenjualan->get()->getRowArray() ?? [
            'total_revenue'   => 0,
            'total_hpp'       => 0,
            'total_transaksi' => 0,
        ];

        // Total Kas Keluar BKK tahun ini
        $builderBkk = $this->db->table('tbbkk b')
            ->select('COALESCE(SUM(d.nilai), 0) AS total_bkk, COUNT(DISTINCT b.id) AS total_bkk_count')
            ->join('tbbkk_d d', 'd.id_bkk = b.id')
            ->where('b.is_deleted', 0)
            ->where('YEAR(b.tgl)', $targetYear);

        if ($month !== null) {
            $builderBkk->where('MONTH(b.tgl)', $month);
        }
        $bkkRow = $builderBkk->get()->getRowArray() ?? [
            'total_bkk'       => 0,
            'total_bkk_count' => 0,
        ];

        $totalRevenue = (float) $salesRow['total_revenue'];
        $totalHpp     = (float) $salesRow['total_hpp'];
        $totalBkk     = (float) $bkkRow['total_bkk'];

        $netCashflow  = $totalRevenue - $totalBkk;
        $grossProfit  = $totalRevenue - $totalHpp;
        $grossMargin  = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 2) : 0.0;

        // Ambil data MoM dari tren bulanan
        $monthlyTrends = $this->getMonthlyCashFlowTrends_l1H($targetYear);
        $latestTrend   = !empty($monthlyTrends['raw']) ? end($monthlyTrends['raw']) : null;

        $revenueMomGrowth = $latestTrend ? (float) ($latestTrend['revenue_mom_pct'] ?? 0.0) : 0.0;
        $outflowMomGrowth = $latestTrend ? (float) ($latestTrend['outflow_mom_pct'] ?? 0.0) : 0.0;

        return [
            'year'                => $targetYear,
            'month'               => $month,
            'total_revenue'       => $totalRevenue,
            'total_hpp'           => $totalHpp,
            'gross_profit'        => $grossProfit,
            'gross_margin_pct'    => $grossMargin,
            'total_bkk'           => $totalBkk,
            'net_cashflow'        => $netCashflow,
            'total_sales_count'   => (int) $salesRow['total_transaksi'],
            'total_bkk_count'     => (int) $bkkRow['total_bkk_count'],
            'revenue_mom_growth'  => $revenueMomGrowth,
            'outflow_mom_growth'  => $outflowMomGrowth,
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    //  2. WINDOW FUNCTION: TREN ARUS KAS & RUNNING BALANCE
    // ─────────────────────────────────────────────────────────────────

    /**
     * Query 1: Analisis Arus Kas Bulanan dengan Window Functions
     * - SUM(net_cashflow) OVER () -> Running Balance
     * - LAG() OVER () -> Perbandingan bulan sebelumnya (MoM Growth %)
     */
    public function getMonthlyCashFlowTrends_l1H(?int $year = null): array
    {
        $yearFilter = $year ? "WHERE YEAR(mutasi.tgl) = " . (int)$year : "";

        $sql = "
            WITH BulananMutasi AS (
                SELECT 
                    DATE_FORMAT(mutasi.tgl, '%Y-%m') AS periode,
                    SUM(CASE WHEN arus = 'IN' THEN nominal ELSE 0 END) AS total_masuk,
                    SUM(CASE WHEN arus = 'OUT' THEN nominal ELSE 0 END) AS total_keluar,
                    SUM(CASE WHEN arus = 'IN' THEN nominal ELSE -nominal END) AS net_cashflow
                FROM (
                    -- Kas Masuk: Penjualan
                    SELECT tgl_jual AS tgl, total_netto AS nominal, 'IN' AS arus 
                    FROM penjualan 
                    WHERE is_deleted = 0
                    
                    UNION ALL
                    
                    -- Kas Keluar: BKK
                    SELECT b.tgl AS tgl, COALESCE(d.nilai, 0) AS nominal, 'OUT' AS arus 
                    FROM tbbkk b 
                    JOIN tbbkk_d d ON d.id_bkk = b.id 
                    WHERE b.is_deleted = 0
                ) mutasi
                {$yearFilter}
                GROUP BY DATE_FORMAT(mutasi.tgl, '%Y-%m')
            )
            SELECT 
                periode,
                total_masuk,
                total_keluar,
                net_cashflow,
                -- Window Function 1: Saldo Kas Kumulatif Berjalan (Running Balance)
                SUM(net_cashflow) OVER (
                    ORDER BY periode ASC 
                    ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                ) AS running_cash_balance,
                -- Window Function 2: Net Cashflow Bulan Sebelumnya
                LAG(net_cashflow, 1, 0) OVER (ORDER BY periode ASC) AS prev_month_cashflow,
                -- Window Function 3: Total Masuk Bulan Sebelumnya
                LAG(total_masuk, 1, 0) OVER (ORDER BY periode ASC) AS prev_month_masuk,
                -- Window Function 4: Total Keluar Bulan Sebelumnya
                LAG(total_keluar, 1, 0) OVER (ORDER BY periode ASC) AS prev_month_keluar,
                -- Hitung MoM Growth untuk Net Cashflow (%)
                ROUND(
                    (net_cashflow - LAG(net_cashflow, 1, net_cashflow) OVER (ORDER BY periode ASC)) 
                    / NULLIF(ABS(LAG(net_cashflow, 1, net_cashflow) OVER (ORDER BY periode ASC)), 0) * 100, 2
                ) AS mom_growth_pct,
                -- Hitung MoM Growth untuk Revenue / Masuk (%)
                ROUND(
                    (total_masuk - LAG(total_masuk, 1, total_masuk) OVER (ORDER BY periode ASC)) 
                    / NULLIF(ABS(LAG(total_masuk, 1, total_masuk) OVER (ORDER BY periode ASC)), 0) * 100, 2
                ) AS revenue_mom_pct,
                -- Hitung MoM Growth untuk Outflow / Keluar (%)
                ROUND(
                    (total_keluar - LAG(total_keluar, 1, total_keluar) OVER (ORDER BY periode ASC)) 
                    / NULLIF(ABS(LAG(total_keluar, 1, total_keluar) OVER (ORDER BY periode ASC)), 0) * 100, 2
                ) AS outflow_mom_pct
            FROM BulananMutasi
            ORDER BY periode ASC
        ";

        $rows = $this->db->query($sql)->getResultArray();

        // Format data untuk siap dikonsumsi ApexCharts
        $categories       = [];
        $seriesInflow     = [];
        $seriesOutflow    = [];
        $seriesNet        = [];
        $seriesCumulative = [];

        foreach ($rows as $r) {
            $categories[]       = $r['periode'];
            $seriesInflow[]     = (float) $r['total_masuk'];
            $seriesOutflow[]    = (float) $r['total_keluar'];
            $seriesNet[]        = (float) $r['net_cashflow'];
            $seriesCumulative[] = (float) $r['running_cash_balance'];
        }

        return [
            'raw'              => $rows,
            'categories'       => $categories,
            'seriesInflow'     => $seriesInflow,
            'seriesOutflow'    => $seriesOutflow,
            'seriesNet'        => $seriesNet,
            'seriesCumulative' => $seriesCumulative,
            'sql'              => trim($sql),
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    //  3. CONDITIONAL AGGREGATION: DYNAMIC PIVOT PENGELUARAN COA
    // ─────────────────────────────────────────────────────────────────

    /**
     * Query 2: Dynamic Pivot Table Pengeluaran Kas per COA (Kuartalan Q1–Q4)
     * Menggunakan Conditional Aggregation SUM(CASE WHEN ...)
     */
    public function getPivotExpensePerCoa_l1H(?int $year = null): array
    {
        $targetYear = $year ?? (int) date('Y');

        $sql = "
            SELECT 
                c.id AS id_coa,
                c.kode_coa,
                c.nama_coa,
                COALESCE(c.tipe, 'Operasional') AS tipe,
                COALESCE(SUM(CASE WHEN MONTH(b.tgl) IN (1,2,3) THEN d.nilai ELSE 0 END), 0) AS q1_expense,
                COALESCE(SUM(CASE WHEN MONTH(b.tgl) IN (4,5,6) THEN d.nilai ELSE 0 END), 0) AS q2_expense,
                COALESCE(SUM(CASE WHEN MONTH(b.tgl) IN (7,8,9) THEN d.nilai ELSE 0 END), 0) AS q3_expense,
                COALESCE(SUM(CASE WHEN MONTH(b.tgl) IN (10,11,12) THEN d.nilai ELSE 0 END), 0) AS q4_expense,
                COALESCE(SUM(d.nilai), 0) AS total_tahunan
            FROM tbbkk_d d
            JOIN tbbkk b ON b.id = d.id_bkk AND b.is_deleted = 0
            JOIN coa c ON c.id = d.id_coa
            WHERE YEAR(b.tgl) = ?
            GROUP BY c.id, c.kode_coa, c.nama_coa, c.tipe
            ORDER BY total_tahunan DESC
        ";

        $rows = $this->db->query($sql, [$targetYear])->getResultArray();

        // Data untuk Donut Chart: Komposisi Biaya per Akun / Tipe
        $donutLabels = [];
        $donutSeries = [];
        $totalAll    = array_sum(array_column($rows, 'total_tahunan'));

        foreach ($rows as $r) {
            $donutLabels[] = $r['kode_coa'] . ' - ' . $r['nama_coa'];
            $donutSeries[] = (float) $r['total_tahunan'];
        }

        return [
            'raw'         => $rows,
            'year'        => $targetYear,
            'total_all'   => (float) $totalAll,
            'donutLabels' => $donutLabels,
            'donutSeries' => $donutSeries,
            'sql'         => trim($sql),
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    //  4. WINDOW FUNCTION: PARETO RANKING PRODUK (DENSE_RANK)
    // ─────────────────────────────────────────────────────────────────

    /**
     * Query 3: Analisis Pareto Profitabilitas Produk (DENSE_RANK & Cumulative Share)
     */
    public function getParetoProducts_l1H(int $limit = 10): array
    {
        $sql = "
            WITH ProductPerformance AS (
                SELECT 
                    pd.kode_barang,
                    pd.nama_barang,
                    COALESCE(b.kategori, 'Umum') AS kategori,
                    SUM(pd.qty) AS total_unit_terjual,
                    SUM(pd.subtotal) AS total_omset,
                    SUM(pd.subtotal - (pd.qty * pd.hpp_satuan)) AS total_laba_kotor
                FROM penjualan_d pd
                JOIN penjualan p ON p.id = pd.id_penjualan AND p.is_deleted = 0
                LEFT JOIN barang b ON b.kode_barang = pd.kode_barang
                GROUP BY pd.kode_barang, pd.nama_barang, b.kategori
            )
            SELECT 
                kode_barang,
                nama_barang,
                kategori,
                total_unit_terjual,
                total_omset,
                total_laba_kotor,
                -- Window Function 1: Peringkat Profitabilitas Produk
                DENSE_RANK() OVER (ORDER BY total_laba_kotor DESC) AS profit_rank,
                -- Window Function 2: Kontribusi Kumulatif terhadap Total Laba (%)
                ROUND(
                    SUM(total_laba_kotor) OVER (
                        ORDER BY total_laba_kotor DESC 
                        ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                    ) / NULLIF(SUM(total_laba_kotor) OVER (), 0) * 100, 2
                ) AS cumulative_profit_pct
            FROM ProductPerformance
            ORDER BY profit_rank ASC
            LIMIT {$limit}
        ";

        $rows = $this->db->query($sql)->getResultArray();

        $labels = [];
        $profit = [];
        $omset  = [];

        foreach ($rows as $r) {
            $labels[] = $r['nama_barang'];
            $profit[] = (float) $r['total_laba_kotor'];
            $omset[]  = (float) $r['total_omset'];
        }

        return [
            'raw'    => $rows,
            'labels' => $labels,
            'profit' => $profit,
            'omset'  => $omset,
            'sql'    => trim($sql),
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    //  5. WINDOW FUNCTION: KONSENTRASI BELANJA SUPPLIER (DENSE_RANK)
    // ─────────────────────────────────────────────────────────────────

    /**
     * Query 4: Analisis Konsentrasi Belanja Vendor/Supplier
     */
    public function getSupplierConcentration_l1H(int $limit = 5): array
    {
        $sql = "
            WITH SupplierExpense AS (
                SELECT 
                    s.id_supplier,
                    s.kode_supplier,
                    s.nama_supplier,
                    COUNT(DISTINCT rb.id) AS total_transaksi,
                    COALESCE(SUM(rbd.nilai), 0) AS total_pengeluaran
                FROM supplier s
                JOIN tbrbeli rb ON rb.id_supp = s.id_supplier AND rb.is_deleted = 0
                JOIN tbrbeli_d rbd ON rbd.id_rbeli = rb.id
                GROUP BY s.id_supplier, s.kode_supplier, s.nama_supplier
            )
            SELECT 
                kode_supplier,
                nama_supplier,
                total_transaksi,
                total_pengeluaran,
                -- Window Function 1: Peringkat Belanja Supplier
                DENSE_RANK() OVER (ORDER BY total_pengeluaran DESC) AS rank_supplier,
                -- Window Function 2: Pangsa Belanja terhadap Total Belanja Perusahaan (%)
                ROUND(
                    total_pengeluaran / NULLIF(SUM(total_pengeluaran) OVER (), 0) * 100, 2
                ) AS share_pengeluaran_pct
            FROM SupplierExpense
            ORDER BY rank_supplier ASC
            LIMIT {$limit}
        ";

        $rows = $this->db->query($sql)->getResultArray();

        $labels = [];
        $series = [];

        foreach ($rows as $r) {
            $labels[] = $r['nama_supplier'];
            $series[] = (float) $r['total_pengeluaran'];
        }

        return [
            'raw'    => $rows,
            'labels' => $labels,
            'series' => $series,
            'sql'    => trim($sql),
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    //  6. ACTIONABLE AUDIT: PENGELUARAN BERNILAI TINGGI (THRESHOLD ALERTS)
    // ─────────────────────────────────────────────────────────────────

    /**
     * Mendeteksi pengeluaran kas bernilai tinggi (melebihi ambang batas)
     * untuk evaluasi dan audit direksi.
     */
    public function getHighValueOutflows_l1H(float $threshold = 500000.00, int $limit = 10): array
    {
        $sql = "
            SELECT 
                b.id,
                b.no_bkk,
                b.tgl,
                b.kete,
                d.nilai,
                c.kode_coa,
                c.nama_coa
            FROM tbbkk b
            JOIN tbbkk_d d ON d.id_bkk = b.id
            JOIN coa c ON c.id = d.id_coa
            WHERE b.is_deleted = 0 
              AND d.nilai >= ?
            ORDER BY d.nilai DESC
            LIMIT ?
        ";

        return $this->db->query($sql, [$threshold, $limit])->getResultArray();
    }

    // ─────────────────────────────────────────────────────────────────
    //  7. DEMO DATASET INJECTOR (MULTI-MONTH EXECUTIVE SIMULATOR)
    // ─────────────────────────────────────────────────────────────────

    /**
     * Menyuntikkan transaksi simulasi multi-bulan (Juli–Oktober 2026)
     * untuk keperluan demonstrasi praktikum dan pengujian visualisasi grafik.
     * Menggunakan flag prefix `[DEMO-P10]` agar mudah diidentifikasi & di-rollback.
     */
    public function injectDemoDataset_l1H(): array
    {
        $this->db->transStart();

        // 1. Bersihkan demo data lama jika ada
        $this->resetDemoDataset_l1H(false);

        // 2. Daftar bulan simulasi tahun 2026
        $simulasiBulan = [
            ['tgl' => '2026-07-15', 'omset' => 4500000, 'hpp' => 2700000, 'bkk' => 1800000, 'coa' => 24],
            ['tgl' => '2026-07-28', 'omset' => 5200000, 'hpp' => 3120000, 'bkk' => 2100000, 'coa' => 13],
            ['tgl' => '2026-08-12', 'omset' => 6800000, 'hpp' => 4080000, 'bkk' => 2900000, 'coa' => 24],
            ['tgl' => '2026-08-25', 'omset' => 7400000, 'hpp' => 4440000, 'bkk' => 3200000, 'coa' => 13],
            ['tgl' => '2026-09-10', 'omset' => 8900000, 'hpp' => 5340000, 'bkk' => 4100000, 'coa' => 24],
            ['tgl' => '2026-09-22', 'omset' => 9600000, 'hpp' => 5760000, 'bkk' => 4300000, 'coa' => 13],
            ['tgl' => '2026-10-05', 'omset' => 11200000, 'hpp' => 6720000, 'bkk' => 5100000, 'coa' => 24],
            ['tgl' => '2026-10-18', 'omset' => 12500000, 'hpp' => 7500000, 'bkk' => 5600000, 'coa' => 13],
        ];

        $insertedSales = 0;
        $insertedBkk   = 0;

        foreach ($simulasiBulan as $idx => $s) {
            $noFaktur = sprintf('[DEMO-P10]-INV-%03d', $idx + 1);
            $noBkk    = sprintf('[DEMO-P10]-BKK-%03d', $idx + 1);

            // Insert Penjualan
            $this->db->table('penjualan')->insert([
                'no_faktur'    => $noFaktur,
                'tgl_jual'     => $s['tgl'],
                'customer'     => 'Customer Eksekutif ' . ($idx + 1),
                'total_bruto'  => $s['omset'],
                'diskon'       => 0,
                'total_netto'  => $s['omset'],
                'total_hpp'    => $s['hpp'],
                'metode_bayar' => 'Transfer',
                'id_user'      => 1,
                'is_deleted'   => 0,
                'created_at'   => $s['tgl'] . ' 10:00:00',
            ]);
            $idPenjualan = $this->db->insertID();

            // Insert Detail Penjualan
            $this->db->table('penjualan_d')->insert([
                'id_penjualan' => $idPenjualan,
                'kode_barang'  => ($idx % 2 === 0) ? 'BRG-001' : 'BRG-003',
                'nama_barang'  => ($idx % 2 === 0) ? 'Kopi Arabika Specialty 250g' : 'Sirup Vanila Gourmet 750ml',
                'qty'          => 10,
                'harga_satuan' => $s['omset'] / 10,
                'hpp_satuan'   => $s['hpp'] / 10,
                'subtotal'     => $s['omset'],
            ]);
            $insertedSales++;

            // Insert Header BKK
            $this->db->table('tbbkk')->insert([
                'no_bkk'     => $noBkk,
                'tgl'        => $s['tgl'],
                'kete'       => 'Operasional Bulanan Demo P10',
                'is_deleted' => 0,
            ]);
            $idBkk = $this->db->insertID();

            // Insert Detail BKK
            $this->db->table('tbbkk_d')->insert([
                'id_bkk'     => $idBkk,
                'id_rbeli_d' => null,
                'nilai'      => $s['bkk'],
                'id_coa'     => $s['coa'],
                'id_coa_kb'  => 1,
            ]);
            $insertedBkk++;
        }

        $this->db->transComplete();

        return [
            'status'         => $this->db->transStatus(),
            'inserted_sales' => $insertedSales,
            'inserted_bkk'   => $insertedBkk,
            'message'        => "Berhasil menyuntikkan {$insertedSales} transaksi penjualan & {$insertedBkk} transaksi BKK multi-bulan.",
        ];
    }

    /**
     * Menghapus seluruh dataset demo [DEMO-P10]
     */
    public function resetDemoDataset_l1H(bool $useTrans = true): array
    {
        if ($useTrans) {
            $this->db->transStart();
        }

        // Ambil ID penjualan demo
        $salesDemos = $this->db->table('penjualan')
            ->select('id')
            ->like('no_faktur', '[DEMO-P10]')
            ->get()->getResultArray();

        if (!empty($salesDemos)) {
            $salesIds = array_column($salesDemos, 'id');
            $this->db->table('penjualan_d')->whereIn('id_penjualan', $salesIds)->delete();
            $this->db->table('penjualan')->whereIn('id', $salesIds)->delete();
        }

        // Ambil ID BKK demo
        $bkkDemos = $this->db->table('tbbkk')
            ->select('id')
            ->like('no_bkk', '[DEMO-P10]')
            ->get()->getResultArray();

        if (!empty($bkkDemos)) {
            $bkkIds = array_column($bkkDemos, 'id');
            $this->db->table('tbbkk_d')->whereIn('id_bkk', $bkkIds)->delete();
            $this->db->table('tbbkk')->whereIn('id', $bkkIds)->delete();
        }

        if ($useTrans) {
            $this->db->transComplete();
        }

        return [
            'status'  => true,
            'message' => 'Dataset simulasi demo berhasil dibersihkan.',
        ];
    }
}
