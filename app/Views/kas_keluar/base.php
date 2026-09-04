<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FinanceOS — Enterprise</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
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
            'sidebar-hover': '#1e293b',
            'sidebar-active': '#1d4ed8',
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

  <!-- React + ReactDOM -->
  <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
  <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>

  <!-- Babel (JSX transformer) -->
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

  <style>
    * { box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; }

    /* Custom scrollbar */
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: #0f172a; }
    ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #475569; }

    /* Sidebar transition */
    .sidebar-transition { transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); }

    /* Active nav glow */
    .nav-active { box-shadow: inset 3px 0 0 #3b82f6; }

    /* Fade-in animation */
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .fade-in { animation: fadeIn 0.3s ease forwards; }
  </style>
</head>
<body class="h-full bg-slate-100 text-slate-800 antialiased">

<div id="root"></div>

<script type="text/babel">
const { useState, useEffect, useRef } = React;

/* ─────────────────────────────────────────
   ICON HELPER (Lucide)
───────────────────────────────────────── */
function Icon({ name, size = 18, className = '' }) {
  const ref = useRef(null);
  useEffect(() => {
    if (ref.current && window.lucide) {
      ref.current.innerHTML = '';
      const svg = lucide.createElement(lucide[name] || lucide.HelpCircle);
      svg.setAttribute('width', size);
      svg.setAttribute('height', size);
      ref.current.appendChild(svg);
    }
  }, [name, size]);
  return <span ref={ref} className={`inline-flex items-center justify-center ${className}`} />;
}

/* ─────────────────────────────────────────
   NAVIGATION DATA
───────────────────────────────────────── */
const navItems = [
  {
    label: 'Dashboard',
    icon: 'LayoutDashboard',
    href: '/',
  },
  {
    label: 'Kas Keluar',
    icon: 'ArrowUpFromLine',
    children: [
      { label: 'Chart of Accounts', icon: 'BookOpen',    href: '/kas_keluar/coa' },
      { label: 'Supplier',          icon: 'Truck',        href: '/kas_keluar/supplier' },
      { label: 'Karyawan',          icon: 'Users',        href: '/kas_keluar/karyawan' },
    ],
  },
  {
    label: 'Kas Masuk',
    icon: 'ArrowDownToLine',
    children: [
      { label: 'Penerimaan',        icon: 'Receipt',      href: '#' },
      { label: 'Piutang',           icon: 'FilePlus',     href: '#' },
    ],
  },
  {
    label: 'Laporan',
    icon: 'BarChart3',
    children: [
      { label: 'Neraca',            icon: 'Scale',        href: '#' },
      { label: 'Laba Rugi',         icon: 'TrendingUp',   href: '#' },
      { label: 'Arus Kas',          icon: 'Activity',     href: '#' },
    ],
  },
  {
    label: 'Pengaturan',
    icon: 'Settings',
    href: '#',
  },
];

/* ─────────────────────────────────────────
   SIDEBAR NAV ITEM
───────────────────────────────────────── */
function NavItem({ item, currentPath }) {
  const hasChildren = item.children && item.children.length > 0;
  const isParentActive = hasChildren && item.children.some(c => c.href === currentPath);
  const [open, setOpen] = useState(isParentActive);

  return (
    <li>
      {hasChildren ? (
        <>
          <button
            onClick={() => setOpen(o => !o)}
            className={`w-full flex items-center justify-between gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors
              ${isParentActive
                ? 'text-white bg-white/10'
                : 'text-slate-400 hover:text-white hover:bg-white/5'}`}
          >
            <span className="flex items-center gap-3">
              <Icon name={item.icon} size={16} />
              {item.label}
            </span>
            <span className={`transition-transform duration-200 ${open ? 'rotate-90' : ''}`}>
              <Icon name="ChevronRight" size={14} />
            </span>
          </button>

          {open && (
            <ul className="mt-1 ml-4 pl-3 border-l border-slate-700 space-y-0.5 fade-in">
              {item.children.map(child => (
                <li key={child.href}>
                  <a
                    href={child.href}
                    className={`flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors
                      ${currentPath === child.href
                        ? 'text-white bg-brand-600 font-medium'
                        : 'text-slate-400 hover:text-white hover:bg-white/5'}`}
                  >
                    <Icon name={child.icon} size={14} />
                    {child.label}
                  </a>
                </li>
              ))}
            </ul>
          )}
        </>
      ) : (
        <a
          href={item.href}
          className={`flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors
            ${currentPath === item.href
              ? 'text-white bg-brand-600'
              : 'text-slate-400 hover:text-white hover:bg-white/5'}`}
        >
          <Icon name={item.icon} size={16} />
          {item.label}
        </a>
      )}
    </li>
  );
}

/* ─────────────────────────────────────────
   SIDEBAR
───────────────────────────────────────── */
function Sidebar({ collapsed, currentPath }) {
  return (
    <aside
      className={`fixed inset-y-0 left-0 z-30 flex flex-col bg-sidebar sidebar-transition
        ${collapsed ? 'w-16' : 'w-64'}`}
    >
      {/* Logo */}
      <div className={`flex items-center gap-3 px-4 py-5 border-b border-slate-800 ${collapsed ? 'justify-center' : ''}`}>
        <div className="flex-shrink-0 w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center">
          <Icon name="Landmark" size={16} className="text-white" />
        </div>
        {!collapsed && (
          <div className="fade-in">
            <p className="text-white font-bold text-sm leading-tight">FinanceOS</p>
            <p className="text-slate-500 text-xs">Enterprise Suite</p>
          </div>
        )}
      </div>

      {/* Navigation */}
      <nav className="flex-1 overflow-y-auto px-3 py-4">
        {!collapsed && (
          <p className="text-xs font-semibold uppercase tracking-widest text-slate-600 px-1 mb-2">
            Menu Utama
          </p>
        )}
        <ul className="space-y-0.5">
          {navItems.map(item => (
            collapsed ? (
              <li key={item.label} title={item.label}>
                <a
                  href={item.href || '#'}
                  className="flex items-center justify-center w-full py-3 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors"
                >
                  <Icon name={item.icon} size={18} />
                </a>
              </li>
            ) : (
              <NavItem key={item.label} item={item} currentPath={currentPath} />
            )
          ))}
        </ul>
      </nav>

      {/* User Footer */}
      <div className={`border-t border-slate-800 p-3 ${collapsed ? 'flex justify-center' : ''}`}>
        {collapsed ? (
          <div className="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold">
            A
          </div>
        ) : (
          <div className="flex items-center gap-3 px-1">
            <div className="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
              A
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-white text-sm font-medium truncate">Admin User</p>
              <p className="text-slate-500 text-xs truncate">admin@company.com</p>
            </div>
            <button className="text-slate-500 hover:text-white transition-colors" title="Logout">
              <Icon name="LogOut" size={15} />
            </button>
          </div>
        )}
      </div>
    </aside>
  );
}

/* ─────────────────────────────────────────
   TOPBAR
───────────────────────────────────────── */
function Topbar({ collapsed, onToggle, pageTitle, breadcrumbs }) {
  const [notifOpen, setNotifOpen] = useState(false);

  return (
    <header
      className={`fixed top-0 right-0 z-20 flex items-center justify-between
        h-16 bg-white border-b border-slate-200 px-4 shadow-sm sidebar-transition
        ${collapsed ? 'left-16' : 'left-64'}`}
    >
      {/* Left: Toggle + Breadcrumb */}
      <div className="flex items-center gap-3">
        <button
          onClick={onToggle}
          className="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-slate-800 transition-colors"
        >
          <Icon name="PanelLeft" size={18} />
        </button>
        <div className="hidden sm:flex items-center gap-1.5 text-sm">
          {breadcrumbs.map((b, i) => (
            <span key={i} className="flex items-center gap-1.5">
              {i > 0 && <Icon name="ChevronRight" size={13} className="text-slate-400" />}
              {i === breadcrumbs.length - 1
                ? <span className="font-semibold text-slate-800">{b.label}</span>
                : <a href={b.href} className="text-slate-500 hover:text-brand-600 transition-colors">{b.label}</a>
              }
            </span>
          ))}
        </div>
      </div>

      {/* Right: Actions */}
      <div className="flex items-center gap-1">
        {/* Search */}
        <div className="hidden md:flex items-center gap-2 bg-slate-100 rounded-lg px-3 py-2 mr-2">
          <Icon name="Search" size={14} className="text-slate-400" />
          <input
            type="text"
            placeholder="Cari..."
            className="bg-transparent text-sm text-slate-700 placeholder-slate-400 outline-none w-40"
          />
          <kbd className="text-xs text-slate-400 bg-slate-200 rounded px-1.5 py-0.5">⌘K</kbd>
        </div>

        {/* Notification */}
        <div className="relative">
          <button
            onClick={() => setNotifOpen(o => !o)}
            className="relative p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-slate-800 transition-colors"
          >
            <Icon name="Bell" size={18} />
            <span className="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
          </button>
          {notifOpen && (
            <div className="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-slate-200 fade-in z-50">
              <div className="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                <p className="font-semibold text-sm">Notifikasi</p>
                <span className="text-xs text-brand-600 font-medium cursor-pointer hover:underline">Tandai semua dibaca</span>
              </div>
              {[
                { icon: 'AlertCircle', color: 'text-red-500', title: 'Approval diperlukan', sub: 'Kas keluar IDR 5.000.000 menunggu', time: '2 menit lalu' },
                { icon: 'CheckCircle', color: 'text-green-500', title: 'Transaksi berhasil', sub: 'Pembayaran supplier #INV-0421', time: '1 jam lalu' },
                { icon: 'Info',        color: 'text-blue-500',  title: 'Laporan bulanan siap', sub: 'Agustus 2026 telah diproses', time: '3 jam lalu' },
              ].map((n, i) => (
                <div key={i} className="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer transition-colors">
                  <Icon name={n.icon} size={16} className={n.color + ' mt-0.5 flex-shrink-0'} />
                  <div>
                    <p className="text-sm font-medium text-slate-800">{n.title}</p>
                    <p className="text-xs text-slate-500">{n.sub}</p>
                    <p className="text-xs text-slate-400 mt-0.5">{n.time}</p>
                  </div>
                </div>
              ))}
              <div className="px-4 py-2.5 border-t border-slate-100 text-center">
                <a href="#" className="text-xs text-brand-600 hover:underline font-medium">Lihat semua notifikasi</a>
              </div>
            </div>
          )}
        </div>

        {/* Help */}
        <button className="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-slate-800 transition-colors">
          <Icon name="HelpCircle" size={18} />
        </button>

        {/* Divider */}
        <div className="w-px h-6 bg-slate-200 mx-1"></div>

        {/* Avatar */}
        <button className="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
          <div className="w-7 h-7 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold">
            A
          </div>
          <Icon name="ChevronDown" size={13} className="text-slate-400 hidden sm:block" />
        </button>
      </div>
    </header>
  );
}

/* ─────────────────────────────────────────
   STATS CARD (Sample)
───────────────────────────────────────── */
function StatCard({ icon, label, value, delta, color }) {
  const colors = {
    blue:   { bg: 'bg-blue-50',   icon: 'text-blue-600',   border: 'border-blue-100' },
    green:  { bg: 'bg-green-50',  icon: 'text-green-600',  border: 'border-green-100' },
    amber:  { bg: 'bg-amber-50',  icon: 'text-amber-600',  border: 'border-amber-100' },
    red:    { bg: 'bg-red-50',    icon: 'text-red-600',    border: 'border-red-100' },
  };
  const c = colors[color] || colors.blue;

  return (
    <div className={`bg-white rounded-xl border ${c.border} p-5 flex items-start gap-4 shadow-sm hover:shadow-md transition-shadow`}>
      <div className={`${c.bg} ${c.icon} rounded-xl p-3 flex-shrink-0`}>
        <Icon name={icon} size={20} />
      </div>
      <div className="flex-1 min-w-0">
        <p className="text-xs text-slate-500 font-medium uppercase tracking-wide">{label}</p>
        <p className="text-2xl font-bold text-slate-800 mt-1">{value}</p>
        <p className={`text-xs mt-1 font-medium ${delta.startsWith('+') ? 'text-green-600' : 'text-red-500'}`}>
          {delta} <span className="text-slate-400 font-normal">vs bulan lalu</span>
        </p>
      </div>
    </div>
  );
}

/* ─────────────────────────────────────────
   MAIN CONTENT (Sample Dashboard)
───────────────────────────────────────── */
function MainContent() {
  const stats = [
    { icon: 'ArrowUpFromLine', label: 'Total Kas Keluar', value: 'Rp 128,4 Jt', delta: '+12.4%', color: 'red' },
    { icon: 'ArrowDownToLine', label: 'Total Kas Masuk',  value: 'Rp 215,7 Jt', delta: '+8.1%',  color: 'green' },
    { icon: 'Wallet',          label: 'Saldo Bersih',     value: 'Rp 87,3 Jt',  delta: '+3.2%',  color: 'blue' },
    { icon: 'FileText',        label: 'Transaksi Bulan Ini', value: '248',      delta: '+21',     color: 'amber' },
  ];

  return (
    <div className="space-y-6 fade-in">
      {/* Page Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-xl font-bold text-slate-800">Dashboard</h1>
          <p className="text-sm text-slate-500 mt-0.5">Ringkasan keuangan bulan September 2026</p>
        </div>
        <div className="flex items-center gap-2">
          <button className="flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
            <Icon name="CalendarDays" size={15} />
            Sep 2026
          </button>
          <button className="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 transition-colors shadow-sm">
            <Icon name="Download" size={15} />
            Export
          </button>
        </div>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        {stats.map(s => <StatCard key={s.label} {...s} />)}
      </div>

      {/* Table Preview */}
      <div className="bg-white rounded-xl border border-slate-200 shadow-sm">
        <div className="flex items-center justify-between px-6 py-4 border-b border-slate-100">
          <div>
            <h2 className="font-semibold text-slate-800">Transaksi Terakhir</h2>
            <p className="text-xs text-slate-500 mt-0.5">10 transaksi terbaru</p>
          </div>
          <a href="#" className="flex items-center gap-1.5 text-sm text-brand-600 hover:underline font-medium">
            Lihat semua <Icon name="ArrowRight" size={14} />
          </a>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="bg-slate-50 text-left">
                {['No. Transaksi', 'Tanggal', 'Keterangan', 'Akun COA', 'Jumlah', 'Status'].map(h => (
                  <th key={h} className="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">{h}</th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {[
                { no: 'TRX-0091', tgl: '05 Sep 2026', ket: 'Pembelian ATK',          akun: '5-1001', jml: 'Rp 450.000',    status: 'Selesai' },
                { no: 'TRX-0090', tgl: '04 Sep 2026', ket: 'Gaji Karyawan Sep',      akun: '5-2001', jml: 'Rp 45.000.000', status: 'Selesai' },
                { no: 'TRX-0089', tgl: '03 Sep 2026', ket: 'Pembayaran Supplier A',  akun: '5-1003', jml: 'Rp 12.500.000', status: 'Pending' },
                { no: 'TRX-0088', tgl: '02 Sep 2026', ket: 'Biaya Listrik & Air',    akun: '5-1005', jml: 'Rp 2.300.000',  status: 'Selesai' },
                { no: 'TRX-0087', tgl: '01 Sep 2026', ket: 'Maintenance Gedung',     akun: '5-3002', jml: 'Rp 8.000.000',  status: 'Ditolak' },
              ].map(t => (
                <tr key={t.no} className="hover:bg-slate-50 transition-colors">
                  <td className="px-6 py-3.5 font-mono text-xs font-medium text-brand-600">{t.no}</td>
                  <td className="px-6 py-3.5 text-slate-600">{t.tgl}</td>
                  <td className="px-6 py-3.5 text-slate-800 font-medium">{t.ket}</td>
                  <td className="px-6 py-3.5">
                    <code className="bg-slate-100 text-slate-700 text-xs px-2 py-0.5 rounded">{t.akun}</code>
                  </td>
                  <td className="px-6 py-3.5 font-semibold text-slate-800">{t.jml}</td>
                  <td className="px-6 py-3.5">
                    <span className={`inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium
                      ${t.status === 'Selesai' ? 'bg-green-100 text-green-700'
                      : t.status === 'Pending' ? 'bg-amber-100 text-amber-700'
                      : 'bg-red-100 text-red-600'}`}>
                      <span className={`w-1.5 h-1.5 rounded-full
                        ${t.status === 'Selesai' ? 'bg-green-500'
                        : t.status === 'Pending' ? 'bg-amber-500'
                        : 'bg-red-500'}`}></span>
                      {t.status}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

/* ─────────────────────────────────────────
   ROOT APP
───────────────────────────────────────── */
function App() {
  const [collapsed, setCollapsed] = useState(false);
  const currentPath = window.location.pathname;

  const breadcrumbs = [
    { label: 'Home', href: '/' },
    { label: 'Dashboard', href: '/' },
  ];

  return (
    <div className="min-h-screen bg-slate-100">
      <Sidebar collapsed={collapsed} currentPath={currentPath} />

      <div className={`sidebar-transition ${collapsed ? 'ml-16' : 'ml-64'}`}>
        <Topbar
          collapsed={collapsed}
          onToggle={() => setCollapsed(c => !c)}
          pageTitle="Dashboard"
          breadcrumbs={breadcrumbs}
        />

        {/* Main Content */}
        <main className="pt-16 min-h-screen">
          <div className="p-6">
            {/* ===== SLOT: Konten halaman di-render di sini ===== */}
            <MainContent />
            {/* ===== END SLOT ===== */}
          </div>
        </main>
      </div>

      {/* Mobile overlay */}
      {!collapsed && (
        <div
          className="fixed inset-0 bg-black/30 z-20 lg:hidden backdrop-blur-sm"
          onClick={() => setCollapsed(true)}
        />
      )}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
</script>
</body>
</html>
