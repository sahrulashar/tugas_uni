<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Executive Dashboard — FinanceOS Enterprise</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              50:  '#eff6ff',
              100: '#dbeafe',
              500: '#3b82f6',
              600: '#2563eb',
              700: '#1d4ed8',
              800: '#1e40af',
              900: '#1e3a8a',
            },
            sidebar: '#0f172a',
          },
          fontFamily: {
            sans: ['Inter', 'system-ui', 'sans-serif'],
          },
        },
      },
    };
  </script>

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

  <!-- React 18 CDN + Babel -->
  <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
  <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

  <!-- Lucide Icons CDN -->
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

  <!-- ApexCharts CDN (Pertemuan 10-11) -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

  <style>
    * { box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; }
    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: #0f172a; }
    ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
    .sidebar-transition { transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    .fade-in { animation: fadeIn 0.3s ease-out forwards; }
    .apexcharts-tooltip { border-radius: 8px !important; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1) !important; }
  </style>
</head>
<body class="h-full bg-slate-100 text-slate-800 antialiased">

<!-- Inject Backend Data -->
<script>
  window.__INITIAL_DATA__ = {
    year:      <?= (int) ($year ?? 2026) ?>,
    kpis:      <?= json_encode($kpis ?? []) ?>,
    trends:    <?= json_encode($trends ?? []) ?>,
    pivotCoa:  <?= json_encode($pivotCoa ?? []) ?>,
    pareto:    <?= json_encode($pareto ?? []) ?>,
    suppliers: <?= json_encode($suppliers ?? []) ?>,
    alerts:    <?= json_encode($alerts ?? []) ?>
  };
  window.__FLASH__ = {
    success: "<?= addslashes(session()->getFlashdata('success') ?? '') ?>",
    error:   "<?= addslashes(session()->getFlashdata('error')   ?? '') ?>"
  };
  window.__CSRF__ = {
    name:  "<?= csrf_token() ?>",
    value: "<?= csrf_hash() ?>"
  };
  <?php include APPPATH . "Views/_session_inject.php"; ?>
</script>

<div id="root"></div>

<script type="text/babel">
const { useState, useEffect, useRef, useMemo } = React;

// ─────────────────────────────────────────────────────────────────
//  ICON COMPONENT
// ─────────────────────────────────────────────────────────────────
function Icon({ name, size = 18, className = '' }) {
  const ref = useRef(null);
  useEffect(() => {
    if (ref.current && window.lucide) {
      ref.current.innerHTML = '';
      const iconDef = lucide[name] || lucide.HelpCircle;
      const svg = lucide.createElement(iconDef);
      svg.setAttribute('width', size);
      svg.setAttribute('height', size);
      ref.current.appendChild(svg);
    }
  }, [name, size]);
  return <span ref={ref} className={`inline-flex items-center justify-center ${className}`} />;
}

// ─────────────────────────────────────────────────────────────────
//  REUSABLE APEXCHART COMPONENT
// ─────────────────────────────────────────────────────────────────
function ApexChartWrapper({ options, series, type = 'line', height = 320 }) {
  const chartRef = useRef(null);
  const chartInstance = useRef(null);

  useEffect(() => {
    if (!chartRef.current || !window.ApexCharts) return;

    if (chartInstance.current) {
      chartInstance.current.destroy();
    }

    const config = {
      ...options,
      chart: {
        ...(options.chart || {}),
        type: type,
        height: height,
        toolbar: { show: true },
        fontFamily: 'Inter, system-ui, sans-serif',
      },
      series: series,
    };

    chartInstance.current = new window.ApexCharts(chartRef.current, config);
    chartInstance.current.render();

    return () => {
      if (chartInstance.current) {
        chartInstance.current.destroy();
        chartInstance.current = null;
      }
    };
  }, [options, series, type, height]);

  return <div ref={chartRef} className="w-full" />;
}

// ─────────────────────────────────────────────────────────────────
//  FORMAT HELPER
// ─────────────────────────────────────────────────────────────────
function formatRupiah(val) {
  if (val === null || val === undefined || isNaN(val)) return 'Rp 0';
  return 'Rp ' + Number(val).toLocaleString('id-ID');
}

// ─────────────────────────────────────────────────────────────────
//  NAVIGATION DATA
// ─────────────────────────────────────────────────────────────────
const NAV = [
  { label: 'Executive Dashboard', icon: 'LayoutDashboard', href: '/46124026/dashboard' },
  {
    label: 'Kas Keluar',
    icon: 'ArrowUpFromLine',
    children: [
      { label: 'Chart of Accounts', icon: 'BookOpen', href: '/46124026/kas_keluar/coa_l1H' },
      { label: 'Supplier',          icon: 'Truck',    href: '/46124026/kas_keluar/supplier_l1H' },
      { label: 'Karyawan',          icon: 'Users',    href: '/46124026/kas_keluar/karyawan_l1H' },
    ],
  },
  {
    label: 'Kas Masuk & Penjualan',
    icon: 'ArrowDownToLine',
    children: [
      { label: 'Kasir Penjualan (POS)', icon: 'ShoppingCart', href: '/46124026/penjualan/kasir_l1H' },
      { label: 'Master Barang & Stok',  icon: 'Package',      href: '/46124026/penjualan/barang_l1H' },
    ],
  },
  {
    label: 'Aktivitas Kas Keluar',
    icon: 'ClipboardList',
    children: [
      { label: 'Rencana Beli',      icon: 'ShoppingCart', href: '/46124026/aktivitas/aktivitas1' },
      { label: 'Bukti Kas Keluar',  icon: 'Receipt',      href: '/46124026/aktivitas/aktivitas2' },
      { label: 'Rekap BKK',         icon: 'ClipboardCheck', href: '/46124026/aktivitas/aktivitas3' },
    ],
  },
  {
    label: 'Akuntansi & Analitik',
    icon: 'BarChart3',
    children: [
      { label: 'Executive Dashboard',      icon: 'PieChart',     href: '/46124026/dashboard' },
      { label: 'Praktikum Pertemuan 10',   icon: 'Sparkles',     href: '/46124026/dashboard/praktikum_l1H' },
      { label: 'Jurnal Umum (Automated)',  icon: 'BookMarked',   href: '/46124026/akuntansi/jurnal_l1H' },
      { label: 'Neraca Saldo',             icon: 'Scale',        href: '/46124026/akuntansi/neraca_saldo_l1H' },
      { label: 'Praktikum Pertemuan 9',    icon: 'CheckCircle2', href: '/46124026/akuntansi/praktikum_l1H' },
    ],
  },
  {
    label: 'Pengaturan RBAC',
    icon: 'Shield',
    children: [
      { label: 'Manajemen User',  icon: 'Users',    href: '/46124026/rbac/user' },
      { label: 'Laman & Aksi',    icon: 'FileText', href: '/46124026/rbac/laman' },
      { label: 'Atur Hak Akses',  icon: 'Lock',     href: '/46124026/rbac/akses' },
    ],
  },
  { label: 'Audit Trail Log', icon: 'History', href: '/46124026/audit' },
];

function NavItem({ item, currentPath }) {
  const hasChildren = item.children?.length > 0;
  const isParentActive = hasChildren && item.children.some(c => c.href === currentPath);
  const [open, setOpen] = useState(isParentActive);

  if (!hasChildren) {
    const active = currentPath === item.href;
    return (
      <li>
        <a
          href={item.href}
          className={`flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ${
            active
              ? 'bg-brand-600 text-white shadow-sm'
              : 'text-slate-400 hover:text-white hover:bg-slate-800/60'
          }`}
        >
          <Icon name={item.icon} size={18} />
          <span>{item.label}</span>
        </a>
      </li>
    );
  }

  return (
    <li>
      <button
        onClick={() => setOpen(!open)}
        className={`w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ${
          isParentActive
            ? 'text-white bg-slate-800/80'
            : 'text-slate-400 hover:text-white hover:bg-slate-800/50'
        }`}
      >
        <div className="flex items-center gap-3">
          <Icon name={item.icon} size={18} />
          <span>{item.label}</span>
        </div>
        <Icon name={open ? 'ChevronDown' : 'ChevronRight'} size={14} className="text-slate-400" />
      </button>

      {open && (
        <ul className="mt-1 ml-4 pl-3 border-l border-slate-800 space-y-1 fade-in">
          {item.children.map(child => {
            const active = currentPath === child.href;
            return (
              <li key={child.label}>
                <a
                  href={child.href}
                  className={`flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition-colors ${
                    active
                      ? 'bg-brand-600 text-white shadow-sm'
                      : 'text-slate-400 hover:text-white hover:bg-slate-800/60'
                  }`}
                >
                  <Icon name={child.icon} size={15} />
                  <span>{child.label}</span>
                </a>
              </li>
            );
          })}
        </ul>
      )}
    </li>
  );
}

function Sidebar({ collapsed, currentPath }) {
  return (
    <aside
      className={`fixed inset-y-0 left-0 z-30 flex flex-col bg-sidebar sidebar-transition ${
        collapsed ? 'w-16' : 'w-64'
      }`}
    >
      <div
        className={`flex items-center gap-3 px-4 py-5 border-b border-slate-800 ${
          collapsed ? 'justify-center' : ''
        }`}
      >
        <div className="flex-shrink-0 w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center text-white shadow-md">
          <Icon name="Landmark" size={16} />
        </div>
        {!collapsed && (
          <div className="fade-in">
            <p className="text-white font-bold text-sm leading-tight">FinanceOS</p>
            <p className="text-slate-400 text-xs">Executive Enterprise</p>
          </div>
        )}
      </div>

      <nav className="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        <ul>
          {NAV.map(item => (
            <NavItem key={item.label} item={item} currentPath={currentPath} />
          ))}
        </ul>
      </nav>

      <div className="p-3 border-t border-slate-800">
        <a
          href="/logout"
          className="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors"
        >
          <Icon name="LogOut" size={16} />
          {!collapsed && <span>Logout</span>}
        </a>
      </div>
    </aside>
  );
}

// ─────────────────────────────────────────────────────────────────
//  EXECUTIVE KPI STAT CARD
// ─────────────────────────────────────────────────────────────────
function StatCard({ label, value, subtext, icon, color = 'blue', trend, badge }) {
  const colorMap = {
    blue:   { bg: 'bg-blue-50 text-blue-600 border-blue-100', iconBg: 'bg-blue-500 text-white' },
    emerald:{ bg: 'bg-emerald-50 text-emerald-600 border-emerald-100', iconBg: 'bg-emerald-500 text-white' },
    rose:   { bg: 'bg-rose-50 text-rose-600 border-rose-100', iconBg: 'bg-rose-500 text-white' },
    amber:  { bg: 'bg-amber-50 text-amber-600 border-amber-100', iconBg: 'bg-amber-500 text-white' },
    purple: { bg: 'bg-purple-50 text-purple-600 border-purple-100', iconBg: 'bg-purple-500 text-white' },
  };
  const theme = colorMap[color] || colorMap.blue;

  return (
    <div className="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
      <div className="flex items-start justify-between">
        <div>
          <p className="text-xs font-semibold uppercase tracking-wider text-slate-400">{label}</p>
          <h3 className="text-2xl font-bold text-slate-800 mt-1">{value}</h3>
        </div>
        <div className={`w-11 h-11 rounded-xl flex items-center justify-center shadow-sm ${theme.iconBg}`}>
          <Icon name={icon} size={20} />
        </div>
      </div>
      <div className="mt-3 flex items-center justify-between text-xs pt-3 border-t border-slate-100">
        <span className="text-slate-500">{subtext}</span>
        {trend !== undefined && (
          <span
            className={`font-semibold px-2 py-0.5 rounded-full text-xs flex items-center gap-1 ${
              trend >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'
            }`}
          >
            <Icon name={trend >= 0 ? 'TrendingUp' : 'TrendingDown'} size={12} />
            {trend >= 0 ? `+${trend}%` : `${trend}%`} MoM
          </span>
        )}
        {badge && (
          <span className="bg-slate-100 text-slate-600 font-semibold px-2 py-0.5 rounded-full text-xs">
            {badge}
          </span>
        )}
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────────────
//  MAIN EXECUTIVE DASHBOARD APP
// ─────────────────────────────────────────────────────────────────
function ExecutiveDashboardApp() {
  const [collapsed, setCollapsed] = useState(false);
  const [data, setData] = useState(window.__INITIAL_DATA__);
  const [selectedYear, setSelectedYear] = useState(window.__INITIAL_DATA__.year || 2026);
  const [selectedMonth, setSelectedMonth] = useState('');
  const [loading, setLoading] = useState(false);

  // Filter change handler
  const handleFilterChange = async (year, month) => {
    setLoading(true);
    try {
      const q = new URLSearchParams({ year });
      if (month) q.append('month', month);
      const res = await fetch(`/46124026/dashboard/api_metrics_l1H?${q.toString()}`);
      const json = await res.json();
      if (json.status === 'success') {
        setData(json);
      }
    } catch (err) {
      console.error('Failed to load metrics:', err);
    } finally {
      setLoading(false);
    }
  };

  const kpis = data.kpis || {};
  const trends = data.trends || {};
  const pivotCoa = data.pivotCoa || {};
  const pareto = data.pareto || {};
  const suppliers = data.suppliers || {};
  const alerts = data.alerts || [];

  // 1. Config Chart 1: Cash Flow Trends & Running Balance
  const cashFlowOptions = {
    chart: { type: 'area', height: 320, toolbar: { show: true } },
    colors: ['#10b981', '#f43f5e', '#3b82f6', '#8b5cf6'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: [2.5, 2.5, 2, 2.5], dashArray: [0, 0, 0, 4] },
    xaxis: {
      categories: trends.categories || [],
      labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: 500 } },
    },
    yaxis: {
      labels: {
        style: { colors: '#64748b', fontSize: '11px' },
        formatter: (val) => 'Rp ' + (val / 1000000).toFixed(1) + 'Jt',
      },
    },
    tooltip: {
      y: { formatter: (val) => formatRupiah(val) },
    },
    legend: { position: 'top', horizontalAlign: 'right', fontSize: '12px' },
  };

  const cashFlowSeries = [
    { name: 'Kas Masuk (Penjualan)', data: trends.seriesInflow || [] },
    { name: 'Kas Keluar (BKK)',      data: trends.seriesOutflow || [] },
    { name: 'Net Operating Flow',    data: trends.seriesNet || [] },
    { name: 'Running Cash Balance',  data: trends.seriesCumulative || [] },
  ];

  // 2. Config Chart 2: Komposisi Pengeluaran COA (Donut)
  const donutOptions = {
    chart: { type: 'donut', height: 320 },
    labels: pivotCoa.donutLabels || [],
    colors: ['#3b82f6', '#f59e0b', '#10b981', '#ec4899', '#8b5cf6', '#06b6d4'],
    legend: { position: 'bottom', fontSize: '11px' },
    tooltip: { y: { formatter: (val) => formatRupiah(val) } },
    plotOptions: {
      pie: {
        donut: {
          size: '65%',
          labels: {
            show: true,
            total: {
              show: true,
              label: 'Total Biaya',
              formatter: () => formatRupiah(pivotCoa.total_all || 0),
            },
          },
        },
      },
    },
  };

  const donutSeries = pivotCoa.donutSeries || [];

  // 3. Config Chart 3: Pareto Top Products (Bar)
  const paretoOptions = {
    chart: { type: 'bar', height: 320, toolbar: { show: false } },
    colors: ['#2563eb', '#10b981'],
    plotOptions: { bar: { horizontal: false, columnWidth: '50%', borderRadius: 4 } },
    xaxis: {
      categories: pareto.labels || [],
      labels: { style: { colors: '#64748b', fontSize: '10px' }, rotate: -20 },
    },
    yaxis: {
      labels: {
        style: { colors: '#64748b', fontSize: '11px' },
        formatter: (val) => 'Rp ' + (val / 1000).toFixed(0) + 'Rb',
      },
    },
    tooltip: { y: { formatter: (val) => formatRupiah(val) } },
    legend: { position: 'top', horizontalAlign: 'right', fontSize: '11px' },
  };

  const paretoSeries = [
    { name: 'Omset Penjualan', data: pareto.omset || [] },
    { name: 'Laba Kotor',     data: pareto.profit || [] },
  ];

  // 4. Config Chart 4: Supplier Concentration (Horizontal Bar)
  const supplierOptions = {
    chart: { type: 'bar', height: 320, toolbar: { show: false } },
    colors: ['#f59e0b'],
    plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 4 } },
    xaxis: {
      labels: {
        formatter: (val) => 'Rp ' + (val / 1000000).toFixed(1) + 'Jt',
        style: { colors: '#64748b', fontSize: '11px' },
      },
    },
    yaxis: { labels: { style: { colors: '#334155', fontSize: '11px', fontWeight: 600 } } },
    tooltip: { y: { formatter: (val) => formatRupiah(val) } },
  };

  const supplierSeries = [
    { name: 'Total Belanja BKK', data: suppliers.series || [] },
  ];

  return (
    <div className="flex h-screen overflow-hidden">
      <Sidebar collapsed={collapsed} currentPath="/46124026/dashboard" />

      <div className={`flex-1 flex flex-col min-w-0 overflow-hidden sidebar-transition ${collapsed ? 'ml-16' : 'ml-64'}`}>
        {/* Topbar Header */}
        <header className="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between flex-shrink-0 z-20 shadow-sm">
          <div className="flex items-center gap-3">
            <button
              onClick={() => setCollapsed(!collapsed)}
              className="p-2 text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-100 transition-colors"
            >
              <Icon name="Menu" size={18} />
            </button>
            <div className="flex items-center gap-2 text-sm text-slate-500">
              <a href="/" className="hover:text-brand-600">Home</a>
              <span>/</span>
              <span className="text-slate-800 font-bold">Executive Dashboard</span>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <a
              href="/46124026/dashboard/praktikum_l1H"
              className="px-3.5 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all shadow-sm hover:shadow"
            >
              <Icon name="Sparkles" size={14} /> Lab Praktikum P10
            </a>
            <button
              onClick={() => window.print()}
              className="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium flex items-center gap-1.5 transition-colors"
            >
              <Icon name="Printer" size={14} /> Cetak
            </button>
            <div className="w-8 h-8 rounded-full bg-brand-600 text-white font-bold flex items-center justify-center text-xs shadow">
              {window._erpUser?.initial || 'A'}
            </div>
          </div>
        </header>

        {/* Dashboard Content Container */}
        <main className="flex-1 overflow-y-auto p-6 space-y-6">
          {/* Dashboard Header Bar & Filters */}
          <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div>
              <div className="flex items-center gap-2">
                <span className="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                  Pertemuan 10–11
                </span>
                <span className="text-xs text-slate-400">• MySQL 8.0 Window Functions + ApexCharts</span>
              </div>
              <h1 className="text-xl font-extrabold text-slate-900 mt-1">C-Level Executive Financial Dashboard</h1>
              <p className="text-xs text-slate-500 mt-0.5">
                Konsolidasi Arus Kas, Margin Penjualan, Pareto Produk, dan Pengeluaran Kas Terintegrasi.
              </p>
            </div>

            {/* Filter Controls */}
            <div className="flex items-center gap-3">
              <div className="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                <Icon name="Calendar" size={15} className="text-slate-400" />
                <select
                  value={selectedYear}
                  onChange={(e) => {
                    const y = Number(e.target.value);
                    setSelectedYear(y);
                    handleFilterChange(y, selectedMonth);
                  }}
                  className="bg-transparent text-xs font-semibold text-slate-700 focus:outline-none cursor-pointer"
                >
                  <option value={2026}>Tahun 2026</option>
                  <option value={2025}>Tahun 2025</option>
                </select>
              </div>

              <div className="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                <Icon name="Filter" size={15} className="text-slate-400" />
                <select
                  value={selectedMonth}
                  onChange={(e) => {
                    const m = e.target.value;
                    setSelectedMonth(m);
                    handleFilterChange(selectedYear, m);
                  }}
                  className="bg-transparent text-xs font-semibold text-slate-700 focus:outline-none cursor-pointer"
                >
                  <option value="">Semua Bulan (Tahunan)</option>
                  <option value="7">Juli</option>
                  <option value="8">Agustus</option>
                  <option value="9">September</option>
                  <option value="10">Oktober</option>
                </select>
              </div>

              {loading && (
                <div className="w-5 h-5 border-2 border-brand-600 border-t-transparent rounded-full animate-spin" />
              )}
            </div>
          </div>

          {/* Row 1: Executive KPI Cards */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <StatCard
              label="Net Sales Revenue"
              value={formatRupiah(kpis.total_revenue)}
              subtext={`${kpis.total_sales_count || 0} Faktur Penjualan`}
              icon="TrendingUp"
              color="emerald"
              trend={kpis.revenue_mom_growth}
            />
            <StatCard
              label="Total Kas Keluar (BKK)"
              value={formatRupiah(kpis.total_bkk)}
              subtext={`${kpis.total_bkk_count || 0} Dokumen Kas Keluar`}
              icon="ArrowUpFromLine"
              color="rose"
              trend={kpis.outflow_mom_growth}
            />
            <StatCard
              label="Net Operating Cashflow"
              value={formatRupiah(kpis.net_cashflow)}
              subtext="Kas Masuk minus Kas Keluar"
              icon="Wallet"
              color={kpis.net_cashflow >= 0 ? 'blue' : 'rose'}
              badge={kpis.net_cashflow >= 0 ? 'Surplus Kas' : 'Defisit Kas'}
            />
            <StatCard
              label="Gross Profit Margin"
              value={`${kpis.gross_margin_pct || 0}%`}
              subtext={`Laba Kotor: ${formatRupiah(kpis.gross_profit)}`}
              icon="PieChart"
              color="purple"
              badge="Efisiensi HPP"
            />
          </div>

          {/* Row 2: Charts Grid (Arus Kas Area & Komposisi Beban Donut) */}
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* Chart 1: Arus Kas Multi-Period (Span 2 Cols) */}
            <div className="lg:col-span-2 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
              <div className="flex items-center justify-between mb-4">
                <div>
                  <div className="flex items-center gap-2">
                    <h3 className="font-bold text-slate-800 text-sm">Tren Arus Kas & Saldo Kumulatif (Running Balance)</h3>
                    <span className="text-[10px] font-bold px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200">
                      MySQL 8 Window Function LAG & SUM() OVER()
                    </span>
                  </div>
                  <p className="text-xs text-slate-400 mt-0.5">
                    Membandingkan Arus Masuk, Arus Keluar, dan Pertumbuhan Arus Kas Kumulatif.
                  </p>
                </div>
              </div>

              {trends.categories?.length > 0 ? (
                <ApexChartWrapper options={cashFlowOptions} series={cashFlowSeries} type="area" height={310} />
              ) : (
                <div className="h-64 flex flex-col items-center justify-center text-slate-400 text-xs">
                  <Icon name="Inbox" size={32} className="mb-2 text-slate-300" />
                  Belum ada transaksi kas pada filter periode ini.
                </div>
              )}
            </div>

            {/* Chart 2: Komposisi Beban COA (Donut) */}
            <div className="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col">
              <div className="mb-3">
                <div className="flex items-center gap-2">
                  <h3 className="font-bold text-slate-800 text-sm">Distribusi Kas Keluar per COA</h3>
                  <span className="text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200">
                    Pivot Matrix
                  </span>
                </div>
                <p className="text-xs text-slate-400 mt-0.5">Proporsi pengeluaran per pos akun biaya</p>
              </div>

              <div className="flex-1 flex items-center justify-center">
                {donutSeries.length > 0 ? (
                  <ApexChartWrapper options={donutOptions} series={donutSeries} type="donut" height={300} />
                ) : (
                  <div className="text-center text-slate-400 text-xs py-10">
                    <Icon name="PieChart" size={32} className="mx-auto mb-2 text-slate-300" />
                    Belum ada pengeluaran BKK tercatat.
                  </div>
                )}
              </div>
            </div>
          </div>

          {/* Row 3: Charts Grid (Pareto Produk & Vendor Concentration) */}
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Chart 3: Pareto Profitabilitas Produk */}
            <div className="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
              <div className="flex items-center justify-between mb-4">
                <div>
                  <div className="flex items-center gap-2">
                    <h3 className="font-bold text-slate-800 text-sm">Pareto Ranking Profitabilitas Produk Terlaris</h3>
                    <span className="text-[10px] font-bold px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200">
                      DENSE_RANK()
                    </span>
                  </div>
                  <p className="text-xs text-slate-400 mt-0.5">Perbandingan Omset Penjualan terhadap Laba Kotor</p>
                </div>
              </div>

              {pareto.labels?.length > 0 ? (
                <ApexChartWrapper options={paretoOptions} series={paretoSeries} type="bar" height={290} />
              ) : (
                <div className="h-60 flex flex-col items-center justify-center text-slate-400 text-xs">
                  Belum ada data barang terjual.
                </div>
              )}
            </div>

            {/* Chart 4: Konsentrasi Belanja Vendor */}
            <div className="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
              <div className="flex items-center justify-between mb-4">
                <div>
                  <div className="flex items-center gap-2">
                    <h3 className="font-bold text-slate-800 text-sm">Konsentrasi Belanja Vendor/Supplier Terbesar</h3>
                    <span className="text-[10px] font-bold px-2 py-0.5 rounded-md bg-orange-50 text-orange-700 border border-orange-200">
                      Share Ratio %
                    </span>
                  </div>
                  <p className="text-xs text-slate-400 mt-0.5">Pangsa alokasi kas keluar kepada supplier utama</p>
                </div>
              </div>

              {suppliers.labels?.length > 0 ? (
                <ApexChartWrapper options={supplierOptions} series={supplierSeries} type="bar" height={290} />
              ) : (
                <div className="h-60 flex flex-col items-center justify-center text-slate-400 text-xs">
                  Belum ada transaksi pembelian ke supplier.
                </div>
              )}
            </div>
          </div>

          {/* Row 4: Actionable High-Value Outflow Alerts */}
          <div className="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
              <div>
                <div className="flex items-center gap-2">
                  <h3 className="font-bold text-slate-800 text-sm">Pengawasan Audit: Pengeluaran Kas Bernilai Tinggi</h3>
                  <span className="text-[10px] font-bold px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200">
                    Threshold Alert &ge; Rp 500.000
                  </span>
                </div>
                <p className="text-xs text-slate-400 mt-0.5">
                  Daftar transaksi BKK bernilai signifikan untuk audit dan review jajaran direksi.
                </p>
              </div>
              <span className="text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600">
                {alerts.length} Transaksi Terdeteksi
              </span>
            </div>

            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs text-slate-600">
                <thead className="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-100">
                  <tr>
                    <th className="px-6 py-3">No. BKK</th>
                    <th className="px-6 py-3">Tanggal</th>
                    <th className="px-6 py-3">Pos Akun COA</th>
                    <th className="px-6 py-3">Keterangan</th>
                    <th className="px-6 py-3 text-right">Nominal</th>
                    <th className="px-6 py-3 text-center">Status Audit</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {alerts.length > 0 ? (
                    alerts.map((item, idx) => (
                      <tr key={idx} className="hover:bg-slate-50/80 transition-colors">
                        <td className="px-6 py-3.5 font-bold text-brand-600 font-mono">{item.no_bkk}</td>
                        <td className="px-6 py-3.5">{item.tgl}</td>
                        <td className="px-6 py-3.5 font-medium text-slate-800">
                          <span className="bg-slate-100 px-1.5 py-0.5 rounded font-mono text-[11px] mr-1.5">
                            {item.kode_coa}
                          </span>
                          {item.nama_coa}
                        </td>
                        <td className="px-6 py-3.5 text-slate-500">{item.kete || 'Pengeluaran Kas'}</td>
                        <td className="px-6 py-3.5 text-right font-bold text-rose-600 font-mono">
                          {formatRupiah(item.nilai)}
                        </td>
                        <td className="px-6 py-3.5 text-center">
                          <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <Icon name="CheckCheck" size={11} /> Diverifikasi
                          </span>
                        </td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td colSpan="6" className="px-6 py-8 text-center text-slate-400">
                        Tidak ada pengeluaran kas yang melebihi batas toleransi.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </main>
      </div>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<ExecutiveDashboardApp />);
</script>
</body>
</html>
