<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Detail Rekap BKK — FinanceOS</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand:   { 50:'#eff6ff', 100:'#dbeafe', 500:'#3b82f6', 600:'#2563eb', 700:'#1d4ed8', 800:'#1e40af', 900:'#1e3a8a' },
            sidebar: '#0f172a',
          },
          fontFamily: { sans: ['Inter','system-ui','sans-serif'] },
        },
      },
    };
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

  <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
  <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

  <style>
    * { box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; }
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: #0f172a; }
    ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
    .sidebar-transition { transition: all 0.25s cubic-bezier(0.4,0,0.2,1); }
    @keyframes fadeIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
    .fade-in { animation: fadeIn 0.3s ease forwards; }
  </style>
</head>
<body class="h-full bg-slate-100 text-slate-800 antialiased">

<script>
  window.__REKAP__ = <?= json_encode($rekap ?? []) ?>;
</script>

<div id="root"></div>

<script type="text/babel">
const { useState, useEffect, useRef } = React;

function Icon({ name, size = 18, className = '' }) {
  const ref = useRef(null);
  useEffect(() => {
    if (ref.current && window.lucide) {
      ref.current.innerHTML = '';
      const svg = lucide.createElement(lucide[name] || lucide.HelpCircle);
      svg.setAttribute('width', size); svg.setAttribute('height', size);
      ref.current.appendChild(svg);
    }
  }, [name, size]);
  return <span ref={ref} className={`inline-flex items-center justify-center ${className}`} />;
}

const navItems = [
  { label: 'Dashboard', icon: 'LayoutDashboard', href: '/' },
  { label: 'Kas Keluar', icon: 'ArrowUpFromLine', children: [
    { label: 'Chart of Accounts', icon: 'BookOpen', href: '/46124026/kas_keluar/coa' },
    { label: 'Supplier',          icon: 'Truck',    href: '/46124026/kas_keluar/supplier' },
    { label: 'Karyawan',          icon: 'Users',    href: '/46124026/kas_keluar/karyawan' },
  ]},
  { label: 'Kas Masuk', icon: 'ArrowDownToLine', children: [
    { label: 'Penerimaan', icon: 'Receipt',  href: '#' },
    { label: 'Piutang',    icon: 'FilePlus', href: '#' },
  ]},
  { label: 'Aktivitas', icon: 'ClipboardList', children: [
    { label: 'Rencana Beli',     icon: 'ShoppingCart',  href: '/46124026/aktivitas/aktivitas1' },
    { label: 'Bukti Kas Keluar', icon: 'Receipt',        href: '/46124026/aktivitas/aktivitas2' },
    { label: 'Rekap BKK',        icon: 'ClipboardCheck', href: '/46124026/aktivitas/aktivitas3' },
  ]},
  { label: 'Laporan', icon: 'BarChart3', children: [
    { label: 'Neraca',    icon: 'Scale',      href: '#' },
    { label: 'Laba Rugi', icon: 'TrendingUp', href: '#' },
    { label: 'Arus Kas',  icon: 'Activity',   href: '#' },
  ]},
  { label: 'Pengaturan', icon: 'Settings', href: '#' },
];

function NavItem({ item, currentPath }) {
  const hasChildren    = item.children && item.children.length > 0;
  const isParentActive = hasChildren && item.children.some(c => c.href === currentPath);
  const [open, setOpen] = useState(isParentActive);
  return (
    <li>
      {hasChildren ? (
        <>
          <button onClick={() => setOpen(o => !o)}
            className={`w-full flex items-center justify-between gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors
              ${isParentActive ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5'}`}>
            <span className="flex items-center gap-3"><Icon name={item.icon} size={16}/>{item.label}</span>
            <span className={`transition-transform duration-200 ${open?'rotate-90':''}`}><Icon name="ChevronRight" size={14}/></span>
          </button>
          {open && (
            <ul className="mt-1 ml-4 pl-3 border-l border-slate-700 space-y-0.5 fade-in">
              {item.children.map(child => (
                <li key={child.label}>
                  <a href={child.href}
                    className={`flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors
                      ${currentPath === child.href ? 'text-white bg-brand-600 font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5'}`}>
                    <Icon name={child.icon} size={14}/>{child.label}
                  </a>
                </li>
              ))}
            </ul>
          )}
        </>
      ) : (
        <a href={item.href}
          className={`flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors
            ${currentPath === item.href ? 'text-white bg-brand-600' : 'text-slate-400 hover:text-white hover:bg-white/5'}`}>
          <Icon name={item.icon} size={16}/>{item.label}
        </a>
      )}
    </li>
  );
}

function Sidebar({ collapsed, currentPath }) {
  return (
    <aside className={`fixed inset-y-0 left-0 z-30 flex flex-col bg-sidebar sidebar-transition ${collapsed ? 'w-16':'w-64'}`}>
      <div className={`flex items-center gap-3 px-4 py-5 border-b border-slate-800 ${collapsed ? 'justify-center':''}`}>
        <div className="flex-shrink-0 w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center">
          <Icon name="Landmark" size={16} className="text-white"/>
        </div>
        {!collapsed && <div className="fade-in"><p className="text-white font-bold text-sm">FinanceOS</p><p className="text-slate-500 text-xs">Enterprise Suite</p></div>}
      </div>
      <nav className="flex-1 overflow-y-auto px-3 py-4">
        {!collapsed && <p className="text-xs font-semibold uppercase tracking-widest text-slate-600 px-1 mb-2">Menu Utama</p>}
        <ul className="space-y-0.5">
          {navItems.map(item =>
            collapsed ? (
              <li key={item.label} title={item.label}>
                <a href={item.href||'#'} className="flex items-center justify-center w-full py-3 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors">
                  <Icon name={item.icon} size={18}/>
                </a>
              </li>
            ) : <NavItem key={item.label} item={item} currentPath={currentPath}/>
          )}
        </ul>
      </nav>
      <div className={`border-t border-slate-800 p-3 ${collapsed ? 'flex justify-center':''}`}>
        {collapsed
          ? <div className="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold">A</div>
          : <div className="flex items-center gap-3 px-1">
              <div className="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">A</div>
              <div className="flex-1 min-w-0"><p className="text-white text-sm font-medium truncate">Admin User</p><p className="text-slate-500 text-xs">admin@company.com</p></div>
              <button className="text-slate-500 hover:text-white"><Icon name="LogOut" size={15}/></button>
            </div>
        }
      </div>
    </aside>
  );
}

function Topbar({ collapsed, onToggle, breadcrumbs }) {
  return (
    <header className={`fixed top-0 right-0 z-20 flex items-center justify-between h-16 bg-white border-b border-slate-200 px-4 shadow-sm sidebar-transition ${collapsed ? 'left-16':'left-64'}`}>
      <div className="flex items-center gap-3">
        <button onClick={onToggle} className="p-2 rounded-lg hover:bg-slate-100 text-slate-500 transition-colors"><Icon name="PanelLeft" size={18}/></button>
        <div className="hidden sm:flex items-center gap-1.5 text-sm">
          {breadcrumbs.map((b, i) => (
            <span key={i} className="flex items-center gap-1.5">
              {i > 0 && <Icon name="ChevronRight" size={13} className="text-slate-400"/>}
              {i === breadcrumbs.length - 1
                ? <span className="font-semibold text-slate-800">{b.label}</span>
                : <a href={b.href} className="text-slate-500 hover:text-brand-600">{b.label}</a>}
            </span>
          ))}
        </div>
      </div>
      <div className="flex items-center gap-1">
        <button className="p-2 rounded-lg hover:bg-slate-100 text-slate-500 transition-colors"><Icon name="Bell" size={18}/></button>
        <div className="w-px h-6 bg-slate-200 mx-1"></div>
        <button className="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
          <div className="w-7 h-7 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold">A</div>
        </button>
      </div>
    </header>
  );
}

function fmtDate(str) {
  if (!str) return '-';
  const d = new Date(str);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
}

function InfoRow({ label, value, mono = false }) {
  return (
    <div className="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-4 py-3 border-b border-slate-100 last:border-0">
      <dt className="text-sm font-medium text-slate-500 sm:w-44 flex-shrink-0">{label}</dt>
      <dd className={`text-sm text-slate-800 ${mono ? 'font-mono' : ''}`}>{value || <span className="text-slate-400 italic">—</span>}</dd>
    </div>
  );
}

function LihatPage() {
  const rekap = window.__REKAP__ || {};

  return (
    <div className="max-w-2xl mx-auto space-y-6 fade-in">
      {/* Header */}
      <div className="flex items-center gap-3">
        <a href="/46124026/aktivitas/aktivitas3" className="p-2 rounded-lg hover:bg-slate-200 text-slate-500 transition-colors">
          <Icon name="ArrowLeft" size={18}/>
        </a>
        <div>
          <h1 className="text-xl font-bold text-slate-800">Detail Rekap BKK</h1>
          <p className="text-sm text-slate-500 mt-0.5">Informasi lengkap rekapitulasi</p>
        </div>
      </div>

      {/* Badge status */}
      <div className="flex items-center gap-2">
        <span className="inline-flex items-center gap-1.5 px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
          <span className="w-1.5 h-1.5 rounded-full bg-green-500"></span>Rekap Aktif
        </span>
      </div>

      {/* Detail Card */}
      <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        {/* Card Header */}
        <div className="bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-5">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
              <Icon name="ClipboardCheck" size={20} className="text-white"/>
            </div>
            <div>
              <p className="text-white/70 text-xs font-medium uppercase tracking-wider">No. Rekap</p>
              <p className="text-white font-bold text-lg font-mono">{rekap.no_rec || '-'}</p>
            </div>
          </div>
        </div>

        {/* Detail Body */}
        <div className="px-6 py-4">
          <dl>
            <InfoRow label="Tanggal Rekap"  value={fmtDate(rekap.tgl)} />
            <InfoRow label="No. BKK Terkait" value={rekap.no_bkk || '-'} mono />
            <InfoRow label="Tanggal BKK"    value={fmtDate(rekap.tgl_bkk)} />
            <InfoRow label="Keterangan BKK" value={rekap.kete_bkk} />
            <InfoRow label="Keterangan Rekap" value={rekap.ket} />
          </dl>
        </div>
      </div>

      {/* Actions */}
      <div className="flex items-center gap-3">
        <a href={`/aktivitas/aktivitas3/edit/${rekap.id}`}
          className="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 transition-colors shadow-sm">
          <Icon name="Pencil" size={15}/>Edit Rekap
        </a>
        <a href="/46124026/aktivitas/aktivitas3"
          className="px-5 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
          Kembali ke Daftar
        </a>
      </div>
    </div>
  );
}

function App() {
  const [collapsed, setCollapsed] = useState(false);
  const currentPath = window.location.pathname;
  const rekap = window.__REKAP__ || {};
  const breadcrumbs = [
    { label: 'Home', href: '/' },
    { label: 'Aktivitas', href: '#' },
    { label: 'Rekap BKK', href: '/46124026/aktivitas/aktivitas3' },
    { label: rekap.no_rec || 'Detail', href: '#' },
  ];
  return (
    <div className="min-h-screen bg-slate-100">
      <Sidebar collapsed={collapsed} currentPath={currentPath}/>
      <div className={`sidebar-transition ${collapsed ? 'ml-16':'ml-64'}`}>
        <Topbar collapsed={collapsed} onToggle={() => setCollapsed(c => !c)} breadcrumbs={breadcrumbs}/>
        <main className="pt-16 min-h-screen">
          <div className="p-6"><LihatPage/></div>
        </main>
      </div>
      {!collapsed && <div className="fixed inset-0 bg-black/30 z-20 lg:hidden backdrop-blur-sm" onClick={() => setCollapsed(true)}/>}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App/>);
</script>
</body>
</html>
