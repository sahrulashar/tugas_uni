<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Praktikum Pertemuan 10–11: Advanced SQL & Executive Charting</title>

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
            mono: ['Fira Code', 'Courier New', 'monospace'],
          },
        },
      },
    };
  </script>

  <!-- Google Fonts: Inter & Fira Code -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

  <!-- React 18 + Babel -->
  <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
  <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

  <!-- ApexCharts CDN -->
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
  </style>
</head>
<body class="h-full bg-slate-100 text-slate-800 antialiased">

<script>
  window.__LAB_DATA__ = {
    year:      <?= (int) ($year ?? 2026) ?>,
    trends:    <?= json_encode($trends ?? []) ?>,
    pivotCoa:  <?= json_encode($pivotCoa ?? []) ?>,
    pareto:    <?= json_encode($pareto ?? []) ?>,
    suppliers: <?= json_encode($suppliers ?? []) ?>
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

function ApexChartWrapper({ options, series, type = 'line', height = 300 }) {
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

function formatRupiah(val) {
  if (val === null || val === undefined || isNaN(val)) return 'Rp 0';
  return 'Rp ' + Number(val).toLocaleString('id-ID');
}

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
          isParentActive ? 'text-white bg-slate-800/80' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'
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
                    active ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'
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
      <div className={`flex items-center gap-3 px-4 py-5 border-b border-slate-800 ${collapsed ? 'justify-center' : ''}`}>
        <div className="flex-shrink-0 w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center text-white shadow-md">
          <Icon name="Landmark" size={16} />
        </div>
        {!collapsed && (
          <div className="fade-in">
            <p className="text-white font-bold text-sm leading-tight">FinanceOS</p>
            <p className="text-purple-400 text-xs font-semibold">Lab Praktikum P10</p>
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
//  MAIN PRAKTIKUM COMPONENT
// ─────────────────────────────────────────────────────────────────
function PraktikumApp() {
  const [collapsed, setCollapsed] = useState(false);
  const [activeTab, setActiveTab] = useState('q1');
  const [chartType, setChartType] = useState('area');
  const [loadingAction, setLoadingAction] = useState(false);
  const [statusMsg, setStatusMsg] = useState(null);
  const [data, setData] = useState(window.__LAB_DATA__);

  const refreshData = async () => {
    try {
      const res = await fetch('/46124026/dashboard/api_metrics_l1H?year=2026');
      const json = await res.json();
      if (json.status === 'success') {
        setData({
          year: json.year,
          trends: json.trends,
          pivotCoa: json.pivotCoa,
          pareto: json.pareto,
          suppliers: json.suppliers,
        });
      }
    } catch (e) {
      console.error('Refresh error:', e);
    }
  };

  const handleInjectDemo = async () => {
    setLoadingAction(true);
    setStatusMsg(null);
    try {
      const formData = new FormData();
      formData.append(window.__CSRF__.name, window.__CSRF__.value);
      const res = await fetch('/46124026/dashboard/inject_demo_l1H', {
        method: 'POST',
        body: formData,
      });
      const json = await res.json();
      setStatusMsg({ type: 'success', text: json.message });
      await refreshData();
    } catch (e) {
      setStatusMsg({ type: 'error', text: 'Gagal menyuntikkan data demo.' });
    } finally {
      setLoadingAction(false);
    }
  };

  const handleResetDemo = async () => {
    setLoadingAction(true);
    setStatusMsg(null);
    try {
      const formData = new FormData();
      formData.append(window.__CSRF__.name, window.__CSRF__.value);
      const res = await fetch('/46124026/dashboard/reset_demo_l1H', {
        method: 'POST',
        body: formData,
      });
      const json = await res.json();
      setStatusMsg({ type: 'info', text: json.message });
      await refreshData();
    } catch (e) {
      setStatusMsg({ type: 'error', text: 'Gagal mereset data demo.' });
    } finally {
      setLoadingAction(false);
    }
  };

  const trends = data.trends || {};
  const pivotCoa = data.pivotCoa || {};
  const pareto = data.pareto || {};
  const suppliers = data.suppliers || {};

  // Sandbox Chart Options for Tab 1
  const sandboxChartOptions = {
    chart: { type: chartType, height: 290 },
    colors: ['#10b981', '#f43f5e', '#3b82f6', '#8b5cf6'],
    stroke: { curve: 'smooth', width: 2.5 },
    xaxis: { categories: trends.categories || [] },
    yaxis: {
      labels: { formatter: (val) => 'Rp ' + (val / 1000000).toFixed(1) + 'Jt' },
    },
    tooltip: { y: { formatter: (val) => formatRupiah(val) } },
    legend: { position: 'top', horizontalAlign: 'right' },
  };

  const sandboxChartSeries = [
    { name: 'Arus Masuk', data: trends.seriesInflow || [] },
    { name: 'Arus Keluar', data: trends.seriesOutflow || [] },
    { name: 'Arus Bersih', data: trends.seriesNet || [] },
    { name: 'Saldo Berjalan', data: trends.seriesCumulative || [] },
  ];

  return (
    <div className="flex h-screen overflow-hidden">
      <Sidebar collapsed={collapsed} currentPath="/46124026/dashboard/praktikum_l1H" />

      <div className={`flex-1 flex flex-col min-w-0 overflow-hidden sidebar-transition ${collapsed ? 'ml-16' : 'ml-64'}`}>
        {/* Topbar */}
        <header className="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between flex-shrink-0 z-20 shadow-sm">
          <div className="flex items-center gap-3">
            <button
              onClick={() => setCollapsed(!collapsed)}
              className="p-2 text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-100"
            >
              <Icon name="Menu" size={18} />
            </button>
            <div className="flex items-center gap-2 text-sm text-slate-500">
              <a href="/46124026/dashboard" className="hover:text-brand-600">Executive Dashboard</a>
              <span>/</span>
              <span className="text-slate-800 font-bold">Laboratorium Praktikum P10–11</span>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <a
              href="/46124026/dashboard"
              className="px-3.5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-sm"
            >
              <Icon name="LayoutDashboard" size={14} /> Ke Dashboard
            </a>
            <div className="w-8 h-8 rounded-full bg-purple-600 text-white font-bold flex items-center justify-center text-xs">
              {window._erpUser?.initial || 'A'}
            </div>
          </div>
        </header>

        {/* Main Lab Runner */}
        <main className="flex-1 overflow-y-auto p-6 space-y-6">
          {/* Header Lab Banner with 1-Click Simulation Buttons */}
          <div className="bg-gradient-to-r from-purple-900 via-indigo-900 to-slate-900 rounded-2xl p-6 text-white shadow-lg flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div className="space-y-1.5">
              <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-500/20 text-purple-300 text-xs font-bold border border-purple-400/30">
                <Icon name="Cpu" size={14} /> Modul Praktikum: Advanced SQL Aggregation & Visual Sandbox
              </div>
              <h1 className="text-2xl font-black tracking-tight">Interactive SQL Query & Chart Engine Lab</h1>
              <p className="text-slate-300 text-xs max-w-2xl leading-relaxed">
                Bedah langsung cara kerja <strong>MySQL 8.0 Window Functions</strong> (<code className="text-purple-300 font-mono">LAG()</code>, <code className="text-purple-300 font-mono">SUM() OVER()</code>, <code className="text-purple-300 font-mono">DENSE_RANK()</code>) dan <strong>Conditional Aggregation Pivot</strong> (<code className="text-purple-300 font-mono">SUM CASE WHEN</code>) dengan data nyata secara real-time.
              </p>
            </div>

            {/* Simulation Controls */}
            <div className="flex flex-col sm:flex-row items-center gap-2.5">
              <button
                onClick={handleInjectDemo}
                disabled={loadingAction}
                className="w-full sm:w-auto px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-xl text-xs flex items-center justify-center gap-2 shadow-md transition-all active:scale-95 disabled:opacity-50"
              >
                <Icon name="Zap" size={14} />
                <span>Suntik Dataset Multi-Bulan (Demo)</span>
              </button>

              <button
                onClick={handleResetDemo}
                disabled={loadingAction}
                className="w-full sm:w-auto px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-xl text-xs flex items-center justify-center gap-2 border border-white/20 transition-all active:scale-95 disabled:opacity-50"
              >
                <Icon name="RotateCcw" size={14} />
                <span>Reset Data Demo</span>
              </button>
            </div>
          </div>

          {/* Flash Feedback */}
          {statusMsg && (
            <div
              className={`p-4 rounded-xl text-xs font-medium flex items-center gap-2 fade-in ${
                statusMsg.type === 'success'
                  ? 'bg-emerald-50 text-emerald-800 border border-emerald-200'
                  : statusMsg.type === 'info'
                  ? 'bg-blue-50 text-blue-800 border border-blue-200'
                  : 'bg-rose-50 text-rose-800 border border-rose-200'
              }`}
            >
              <Icon
                name={statusMsg.type === 'success' ? 'CheckCircle2' : 'Info'}
                size={16}
                className={statusMsg.type === 'success' ? 'text-emerald-600' : 'text-blue-600'}
              />
              <span>{statusMsg.text}</span>
            </div>
          )}

          {/* Navigation Tabs for 4 Advanced SQL Queries */}
          <div className="flex items-center gap-2 border-b border-slate-200 pb-2">
            {[
              { id: 'q1', label: 'Query 1: Window Function Arus Kas & LAG() MoM', icon: 'TrendingUp' },
              { id: 'q2', label: 'Query 2: Dynamic Pivot Kuartalan per COA', icon: 'Grid' },
              { id: 'q3', label: 'Query 3: Pareto Ranking Produk (DENSE_RANK)', icon: 'Award' },
              { id: 'q4', label: 'Query 4: Konsentrasi Belanja Supplier', icon: 'PieChart' },
            ].map(tab => (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id)}
                className={`px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 transition-all ${
                  activeTab === tab.id
                    ? 'bg-brand-600 text-white shadow-sm'
                    : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200/80'
                }`}
              >
                <Icon name={tab.icon} size={14} />
                {tab.label}
              </button>
            ))}
          </div>

          {/* TAB 1: ARUS KAS BULANAN & WINDOW FUNCTION */}
          {activeTab === 'q1' && (
            <div className="space-y-6 fade-in">
              {/* SQL Syntax Card */}
              <div className="bg-slate-900 rounded-2xl p-5 text-slate-100 shadow-md">
                <div className="flex items-center justify-between pb-3 border-b border-slate-800 mb-3">
                  <div className="flex items-center gap-2">
                    <span className="w-3 h-3 rounded-full bg-rose-500 inline-block" />
                    <span className="w-3 h-3 rounded-full bg-amber-500 inline-block" />
                    <span className="w-3 h-3 rounded-full bg-emerald-500 inline-block" />
                    <span className="text-xs font-mono text-slate-400 ml-2">MySQL 8.0 CTE + Window Functions</span>
                  </div>
                  <span className="text-[11px] font-mono bg-purple-900/60 text-purple-300 px-2 py-0.5 rounded border border-purple-700/50">
                    Execution: &lt; 5 ms
                  </span>
                </div>
                <pre className="font-mono text-xs overflow-x-auto text-emerald-300 leading-relaxed">
                  {trends.sql || 'SQL query definition loaded.'}
                </pre>
              </div>

              {/* Chart Switcher Sandbox */}
              <div className="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                <div className="flex items-center justify-between">
                  <div>
                    <h3 className="text-sm font-bold text-slate-800">Visual Sandbox: Live ApexCharts Switcher</h3>
                    <p className="text-xs text-slate-400 mt-0.5">Ubah tipe representasi grafik visual secara instan</p>
                  </div>
                  <div className="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl">
                    {['area', 'bar', 'line'].map(t => (
                      <button
                        key={t}
                        onClick={() => setChartType(t)}
                        className={`px-3 py-1 rounded-lg text-xs font-semibold capitalize transition-all ${
                          chartType === t ? 'bg-white text-brand-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'
                        }`}
                      >
                        {t}
                      </button>
                    ))}
                  </div>
                </div>

                <ApexChartWrapper options={sandboxChartOptions} series={sandboxChartSeries} type={chartType} height={280} />
              </div>

              {/* Data Table with Window Function Output Highlights */}
              <div className="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                  <h3 className="font-bold text-slate-800 text-sm">Hasil Eksekusi Window Function (`LAG` & `Running Balance`)</h3>
                  <span className="text-xs font-mono text-slate-400">{trends.raw?.length || 0} Baris Periode</span>
                </div>
                <div className="overflow-x-auto">
                  <table className="w-full text-left text-xs text-slate-600">
                    <thead className="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-100">
                      <tr>
                        <th className="px-6 py-3">Periode</th>
                        <th className="px-6 py-3 text-right">Kas Masuk (In)</th>
                        <th className="px-6 py-3 text-right">Kas Keluar (Out)</th>
                        <th className="px-6 py-3 text-right">Net Flow</th>
                        <th className="px-6 py-3 text-right bg-purple-50 text-purple-900 font-bold">
                          Cumulative Balance (SUM OVER)
                        </th>
                        <th className="px-6 py-3 text-right bg-blue-50 text-blue-900 font-bold">
                          Prev Month Net (LAG)
                        </th>
                        <th className="px-6 py-3 text-right bg-emerald-50 text-emerald-900 font-bold">
                          MoM Growth (%)
                        </th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                      {trends.raw?.map((row, idx) => (
                        <tr key={idx} className="hover:bg-slate-50 transition-colors font-mono">
                          <td className="px-6 py-3.5 font-bold text-slate-800 font-sans">{row.periode}</td>
                          <td className="px-6 py-3.5 text-right text-emerald-600">{formatRupiah(row.total_masuk)}</td>
                          <td className="px-6 py-3.5 text-right text-rose-600">{formatRupiah(row.total_keluar)}</td>
                          <td className="px-6 py-3.5 text-right font-bold text-slate-800">{formatRupiah(row.net_cashflow)}</td>
                          <td className="px-6 py-3.5 text-right bg-purple-50/60 font-bold text-purple-700">
                            {formatRupiah(row.running_cash_balance)}
                          </td>
                          <td className="px-6 py-3.5 text-right bg-blue-50/60 text-blue-700">
                            {formatRupiah(row.prev_month_cashflow)}
                          </td>
                          <td className="px-6 py-3.5 text-right bg-emerald-50/60 font-bold text-emerald-700">
                            {row.mom_growth_pct}%
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          )}

          {/* TAB 2: PIVOT TABLE COA */}
          {activeTab === 'q2' && (
            <div className="space-y-6 fade-in">
              <div className="bg-slate-900 rounded-2xl p-5 text-slate-100 shadow-md">
                <div className="flex items-center justify-between pb-3 border-b border-slate-800 mb-3">
                  <span className="text-xs font-mono text-slate-400">MySQL 8.0 Conditional Aggregation (Pivot)</span>
                  <span className="text-[11px] font-mono bg-amber-900/60 text-amber-300 px-2 py-0.5 rounded border border-amber-700/50">
                    SUM(CASE WHEN MONTH(b.tgl) ... )
                  </span>
                </div>
                <pre className="font-mono text-xs overflow-x-auto text-amber-300 leading-relaxed">
                  {pivotCoa.sql || 'SQL query loaded.'}
                </pre>
              </div>

              <div className="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                  <h3 className="font-bold text-slate-800 text-sm">Hasil Matrix Pivot Kuartalan Pengeluaran Kas per COA</h3>
                  <span className="text-xs font-bold text-brand-600">
                    Total: {formatRupiah(pivotCoa.total_all || 0)}
                  </span>
                </div>
                <div className="overflow-x-auto">
                  <table className="w-full text-left text-xs text-slate-600">
                    <thead className="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-100">
                      <tr>
                        <th className="px-6 py-3">Kode COA</th>
                        <th className="px-6 py-3">Nama Akun</th>
                        <th className="px-6 py-3">Tipe</th>
                        <th className="px-6 py-3 text-right">Q1 (Jan-Mar)</th>
                        <th className="px-6 py-3 text-right">Q2 (Apr-Jun)</th>
                        <th className="px-6 py-3 text-right">Q3 (Jul-Sep)</th>
                        <th className="px-6 py-3 text-right">Q4 (Okt-Des)</th>
                        <th className="px-6 py-3 text-right font-bold text-slate-800">Total Tahunan</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100 font-mono">
                      {pivotCoa.raw?.map((row, idx) => (
                        <tr key={idx} className="hover:bg-slate-50 transition-colors">
                          <td className="px-6 py-3.5 font-bold text-brand-600 font-sans">{row.kode_coa}</td>
                          <td className="px-6 py-3.5 font-sans font-medium text-slate-800">{row.nama_coa}</td>
                          <td className="px-6 py-3.5 font-sans">
                            <span className="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-600 font-semibold uppercase">
                              {row.tipe || 'Umum'}
                            </span>
                          </td>
                          <td className="px-6 py-3.5 text-right">{formatRupiah(row.q1_expense)}</td>
                          <td className="px-6 py-3.5 text-right">{formatRupiah(row.q2_expense)}</td>
                          <td className="px-6 py-3.5 text-right">{formatRupiah(row.q3_expense)}</td>
                          <td className="px-6 py-3.5 text-right">{formatRupiah(row.q4_expense)}</td>
                          <td className="px-6 py-3.5 text-right font-bold text-slate-900 bg-slate-50/60">
                            {formatRupiah(row.total_tahunan)}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          )}

          {/* TAB 3: PARETO PRODUK DENSE_RANK */}
          {activeTab === 'q3' && (
            <div className="space-y-6 fade-in">
              <div className="bg-slate-900 rounded-2xl p-5 text-slate-100 shadow-md">
                <div className="flex items-center justify-between pb-3 border-b border-slate-800 mb-3">
                  <span className="text-xs font-mono text-slate-400">MySQL 8.0 DENSE_RANK() & Cumulative Pareto Share</span>
                  <span className="text-[11px] font-mono bg-blue-900/60 text-blue-300 px-2 py-0.5 rounded border border-blue-700/50">
                    DENSE_RANK() OVER (ORDER BY total_laba_kotor DESC)
                  </span>
                </div>
                <pre className="font-mono text-xs overflow-x-auto text-blue-300 leading-relaxed">
                  {pareto.sql || 'SQL query loaded.'}
                </pre>
              </div>

              <div className="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                  <h3 className="font-bold text-slate-800 text-sm">Klasifikasi Pareto Kontribusi Margin Laba Kotor</h3>
                  <span className="text-xs text-slate-500 font-semibold">Prinsip Pareto 80/20</span>
                </div>
                <div className="overflow-x-auto">
                  <table className="w-full text-left text-xs text-slate-600">
                    <thead className="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-100">
                      <tr>
                        <th className="px-6 py-3 text-center">Rank</th>
                        <th className="px-6 py-3">Kode & Nama Barang</th>
                        <th className="px-6 py-3">Kategori</th>
                        <th className="px-6 py-3 text-right">Unit Terjual</th>
                        <th className="px-6 py-3 text-right">Total Omset</th>
                        <th className="px-6 py-3 text-right font-bold text-slate-800">Laba Kotor</th>
                        <th className="px-6 py-3 text-right bg-emerald-50 text-emerald-900 font-bold">
                          Kumulatif Share (%)
                        </th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                      {pareto.raw?.map((row, idx) => (
                        <tr key={idx} className="hover:bg-slate-50 transition-colors">
                          <td className="px-6 py-3.5 text-center font-bold text-brand-600">
                            #{row.profit_rank}
                          </td>
                          <td className="px-6 py-3.5 font-medium text-slate-800">
                            <span className="font-mono text-[11px] text-slate-400 mr-2">{row.kode_barang}</span>
                            {row.nama_barang}
                          </td>
                          <td className="px-6 py-3.5 text-slate-500">{row.kategori}</td>
                          <td className="px-6 py-3.5 text-right font-mono">{Number(row.total_unit_terjual).toFixed(0)}</td>
                          <td className="px-6 py-3.5 text-right font-mono">{formatRupiah(row.total_omset)}</td>
                          <td className="px-6 py-3.5 text-right font-mono font-bold text-slate-900">
                            {formatRupiah(row.total_laba_kotor)}
                          </td>
                          <td className="px-6 py-3.5 text-right font-mono font-bold text-emerald-700 bg-emerald-50/60">
                            {row.cumulative_profit_pct}%
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          )}

          {/* TAB 4: VENDOR CONCENTRATION */}
          {activeTab === 'q4' && (
            <div className="space-y-6 fade-in">
              <div className="bg-slate-900 rounded-2xl p-5 text-slate-100 shadow-md">
                <div className="flex items-center justify-between pb-3 border-b border-slate-800 mb-3">
                  <span className="text-xs font-mono text-slate-400">MySQL 8.0 Supplier Expense Share Ratio</span>
                  <span className="text-[11px] font-mono bg-orange-900/60 text-orange-300 px-2 py-0.5 rounded border border-orange-700/50">
                    SUM(rbd.nilai) OVER ()
                  </span>
                </div>
                <pre className="font-mono text-xs overflow-x-auto text-orange-300 leading-relaxed">
                  {suppliers.sql || 'SQL query loaded.'}
                </pre>
              </div>

              <div className="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                  <h3 className="font-bold text-slate-800 text-sm">Peringkat dan Pangsa Alokasi Kas ke Supplier</h3>
                </div>
                <div className="overflow-x-auto">
                  <table className="w-full text-left text-xs text-slate-600">
                    <thead className="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-100">
                      <tr>
                        <th className="px-6 py-3 text-center">Rank</th>
                        <th className="px-6 py-3">Kode & Nama Supplier</th>
                        <th className="px-6 py-3 text-right">Frekuensi Transaksi</th>
                        <th className="px-6 py-3 text-right font-bold text-slate-800">Total Pengeluaran</th>
                        <th className="px-6 py-3 text-right bg-orange-50 text-orange-900 font-bold">Pangsa Kas Keluar (%)</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                      {suppliers.raw?.map((row, idx) => (
                        <tr key={idx} className="hover:bg-slate-50 transition-colors">
                          <td className="px-6 py-3.5 text-center font-bold text-amber-600">#{row.rank_supplier}</td>
                          <td className="px-6 py-3.5 font-medium text-slate-800">
                            <span className="font-mono text-[11px] text-slate-400 mr-2">{row.kode_supplier}</span>
                            {row.nama_supplier}
                          </td>
                          <td className="px-6 py-3.5 text-right font-mono">{row.total_transaksi}x</td>
                          <td className="px-6 py-3.5 text-right font-mono font-bold text-slate-900">
                            {formatRupiah(row.total_pengeluaran)}
                          </td>
                          <td className="px-6 py-3.5 text-right font-mono font-bold text-orange-700 bg-orange-50/60">
                            {row.share_pengeluaran_pct}%
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          )}
        </main>
      </div>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<PraktikumApp />);
</script>
</body>
</html>
