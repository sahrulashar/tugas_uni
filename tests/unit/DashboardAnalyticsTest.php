<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\DashboardAnalyticsService;

/**
 * DashboardAnalyticsTest
 *
 * Menguji kalkulasi analitik tingkat tinggi Pertemuan 10-11:
 *   1. Executive KPIs (Revenue, Outflow, Net Cashflow, Gross Margin)
 *   2. Window Functions: Arus Kas Bulanan, LAG() MoM Growth, Running Balance
 *   3. Conditional Aggregation: Dynamic Pivot Matrix COA (Q1-Q4)
 *   4. Window Function: Pareto Ranking Profitabilitas Produk (DENSE_RANK & Cumulative %)
 *   5. Window Function: Konsentrasi Belanja Supplier
 *   6. Multi-Month Demo Simulator: Injection & Clean Reset
 */
final class DashboardAnalyticsTest extends CIUnitTestCase
{
    private DashboardAnalyticsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DashboardAnalyticsService();
    }

    public function testExecutiveKpisCalculation(): void
    {
        $kpis = $this->service->getExecutiveKpis_l1H(2026);

        $this->assertIsArray($kpis);
        $this->assertArrayHasKey('total_revenue', $kpis);
        $this->assertArrayHasKey('total_bkk', $kpis);
        $this->assertArrayHasKey('net_cashflow', $kpis);
        $this->assertArrayHasKey('gross_profit', $kpis);
        $this->assertArrayHasKey('gross_margin_pct', $kpis);

        // Validasi formula bisnis
        $expectedNet = $kpis['total_revenue'] - $kpis['total_bkk'];
        $this->assertEquals($expectedNet, $kpis['net_cashflow']);

        $expectedGrossProfit = $kpis['total_revenue'] - $kpis['total_hpp'];
        $this->assertEquals($expectedGrossProfit, $kpis['gross_profit']);
    }

    public function testWindowFunctionMonthlyCashFlowTrends(): void
    {
        $trends = $this->service->getMonthlyCashFlowTrends_l1H(2026);

        $this->assertIsArray($trends);
        $this->assertArrayHasKey('categories', $trends);
        $this->assertArrayHasKey('seriesInflow', $trends);
        $this->assertArrayHasKey('seriesOutflow', $trends);
        $this->assertArrayHasKey('seriesNet', $trends);
        $this->assertArrayHasKey('seriesCumulative', $trends);
        $this->assertArrayHasKey('sql', $trends);

        // Validasi sintaks SQL memuat Window Functions
        $this->assertStringContainsString('SUM(net_cashflow) OVER', $trends['sql']);
        $this->assertStringContainsString('LAG(net_cashflow, 1, 0) OVER', $trends['sql']);
    }

    public function testDynamicPivotExpensePerCoa(): void
    {
        $pivot = $this->service->getPivotExpensePerCoa_l1H(2026);

        $this->assertIsArray($pivot);
        $this->assertArrayHasKey('raw', $pivot);
        $this->assertArrayHasKey('total_all', $pivot);
        $this->assertArrayHasKey('donutLabels', $pivot);
        $this->assertArrayHasKey('donutSeries', $pivot);
        $this->assertArrayHasKey('sql', $pivot);

        // Validasi sintaks SQL memuat conditional pivot
        $this->assertStringContainsString('SUM(CASE WHEN MONTH(b.tgl)', $pivot['sql']);

        // Jika ada baris, pastikan kolom kuartal terbentuk
        if (!empty($pivot['raw'])) {
            $first = $pivot['raw'][0];
            $this->assertArrayHasKey('q1_expense', $first);
            $this->assertArrayHasKey('q2_expense', $first);
            $this->assertArrayHasKey('q3_expense', $first);
            $this->assertArrayHasKey('q4_expense', $first);
            $this->assertArrayHasKey('total_tahunan', $first);
        }
    }

    public function testParetoProductProfitabilityRanking(): void
    {
        $pareto = $this->service->getParetoProducts_l1H(10);

        $this->assertIsArray($pareto);
        $this->assertArrayHasKey('raw', $pareto);
        $this->assertArrayHasKey('labels', $pareto);
        $this->assertArrayHasKey('profit', $pareto);
        $this->assertArrayHasKey('omset', $pareto);
        $this->assertArrayHasKey('sql', $pareto);

        // Validasi DENSE_RANK ada di query
        $this->assertStringContainsString('DENSE_RANK() OVER', $pareto['sql']);

        if (!empty($pareto['raw'])) {
            $prevRank = 0;
            foreach ($pareto['raw'] as $p) {
                $this->assertGreaterThanOrEqual($prevRank, (int) $p['profit_rank']);
                $prevRank = (int) $p['profit_rank'];
                $this->assertLessThanOrEqual(100.0, (float) $p['cumulative_profit_pct']);
            }
        }
    }

    public function testSupplierConcentrationWindowFunction(): void
    {
        $suppliers = $this->service->getSupplierConcentration_l1H(5);

        $this->assertIsArray($suppliers);
        $this->assertArrayHasKey('raw', $suppliers);
        $this->assertArrayHasKey('labels', $suppliers);
        $this->assertArrayHasKey('series', $suppliers);
        $this->assertArrayHasKey('sql', $suppliers);

        $this->assertStringContainsString('DENSE_RANK() OVER', $suppliers['sql']);
        $this->assertStringContainsString('SUM(total_pengeluaran) OVER ()', $suppliers['sql']);
    }

    public function testDemoDatasetInjectionAndCleanReset(): void
    {
        // 1. Suntikkan demo dataset
        $injectResult = $this->service->injectDemoDataset_l1H();
        $this->assertTrue($injectResult['status']);
        $this->assertGreaterThan(0, $injectResult['inserted_sales']);
        $this->assertGreaterThan(0, $injectResult['inserted_bkk']);

        // 2. Ambil tren setelah injeksi: harus memuat 4 bulan (Juli-Oktober 2026)
        $trendsWithDemo = $this->service->getMonthlyCashFlowTrends_l1H(2026);
        $this->assertGreaterThanOrEqual(4, count($trendsWithDemo['categories']));

        // 3. Reset demo dataset
        $resetResult = $this->service->resetDemoDataset_l1H();
        $this->assertTrue($resetResult['status']);

        // 4. Pastikan data demo telah bersih kembali
        $trendsAfterReset = $this->service->getMonthlyCashFlowTrends_l1H(2026);
        $this->assertLessThan(count($trendsWithDemo['categories']), count($trendsAfterReset['categories']));
    }
}
