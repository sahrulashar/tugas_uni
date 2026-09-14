<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rekap BKK — FinanceOS</title>

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
    @keyframes slideDown { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }
    .slide-down { animation: slideDown 0.25s ease forwards; }
  </style>
</head>
<body class="h-full bg-slate-100 text-slate-800 antialiased">

<script>
  window.__REKAP__ = <?= json_encode($rekap ?? []) ?>;
  window.__FLASH__ = {
    success: "<?= addslashes(session()->getFlashdata('success') ?? '') ?>",
    error:   "<?= addslashes(session()->getFlashdata('error')   ?? '') ?>"
  };
  window.__CSRF__ = {
    name:  "<?= csrf_token() ?>",
    value: "<?= csrf_hash() ?>"
  };
</script>

<div id="root"></div>

<script type="text/babel">
const { useState, useEffect, useRef } = React;

/* ── Icon ── */
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

/* ── Sidebar navItems ── */
const navItems = [
  { label: 'Dashboard', icon: 'LayoutDashboard', href: '/' },
  {
    label: 'Kas Keluar', icon: 'ArrowUpFromLine',
    children: [
      { label: 'Chart of Accounts', icon: 'BookOpen', href: '/kas_keluar/coa' },
      { label: 'Supplier',          icon: 'Truck',    href: '/kas_keluar/supplier' },
      { label: 'Karyawan',          icon: 'Users',    href: '/kas_keluar/karyawan' },
    ],
  },
  {
    label: 'Kas Masuk', icon: 'ArrowDownToLine',
    children: [
      { label: 'Penerimaan', icon: 'Receipt',  href: '#' },
      { label: 'Piutang',    icon: 'FilePlus', href: '#' },
    ],
  },
  {
    label: 'Aktivitas', icon: 'ClipboardList',
    children: [
      { label: 'Rencana Beli',     icon: 'ShoppingCart',   href: '/aktivitas/aktivitas1' },
      { label: 'Bukti Kas Keluar', icon: 'Receipt',         href: '/aktivitas/aktivitas2' },
      { label: 'Rekap BKK',        icon: 'ClipboardCheck',  href: '/aktivitas/aktivitas3' },
    ],
  },
  {
    label: 'Laporan', icon: 'BarChart3',
    children: [
      { label: 'Neraca',    icon: 'Scale',      href: '#' },
      { label: 'Laba Rugi', icon: 'TrendingUp', href: '#' },
      { label: 'Arus Kas',  icon: 'Activity',   href: '#' },
    ],
  },
  { label: 'Pengaturan', icon: 'Settings', href: '#' },
];

function NavItem({ item, currentPath }) {
  const hasChildren  = item.children && item.children.length > 0;
  const isParentActive = hasChildren && item.children.some(c => c.href === currentPath);
  const [open, setOpen] = useState(isParentActive);
  return (
    <li>
      {hasChildren ? (
        <>
          <button onClick={() => setOpen(o => !o)}
            className={`w-full flex items-center justify-between gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors
              ${isParentActive ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5'}`}>
            <span className="flex items-center gap-3"><Icon name={item.icon} size={16} />{item.label}</span>
            <span className={`transition-transform duration-200 ${open ? 'rotate-90' : ''}`}><Icon name="ChevronRight" size={14} /></span>
          </button>
          {open && (
            <ul className="mt-1 ml-4 pl-3 border-l border-slate-700 space-y-0.5 fade-in">
              {item.children.map(child => (
                <li key={child.label}>
                  <a href={child.href}
                    className={`flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors
                      ${currentPath === child.href ? 'text-white bg-brand-600 font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5'}`}>
                    <Icon name={child.icon} size={14} />{child.label}
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
          <Icon name={item.icon} size={16} />{item.label}
        </a>
      )}
    </li>
  );
}

function Sidebar({ collapsed, currentPath }) {
  return (
    <aside className={`fixed inset-y-0 left-0 z-30 flex flex-col bg-sidebar sidebar-transition ${collapsed ? 'w-16' : 'w-64'}`}>
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
      <nav className="flex-1 overflow-y-auto px-3 py-4">
        {!collapsed && <p className="text-xs font-semibold uppercase tracking-widest text-slate-600 px-1 mb-2">Menu Utama</p>}
        <ul className="space-y-0.5">
          {navItems.map(item =>
            collapsed ? (
              <li key={item.label} title={item.label}>
                <a href={item.href || '#'} className="flex items-center justify-center w-full py-3 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors">
                  <Icon name={item.icon} size={18} />
                </a>
              </li>
            ) : (
              <NavItem key={item.label} item={item} currentPath={currentPath} />
            )
          )}
        </ul>
      </nav>
      <div className={`border-t border-slate-800 p-3 ${collapsed ? 'flex justify-center' : ''}`}>
        {collapsed ? (
          <div className="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold">A</div>
        ) : (
          <div className="flex items-center gap-3 px-1">
            <div className="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">A</div>
            <div className="flex-1 min-w-0">
              <p className="text-white text-sm font-medium truncate">Admin User</p>
              <p className="text-slate-500 text-xs truncate">admin@company.com</p>
            </div>
            <button className="text-slate-500 hover:text-white transition-colors" title="Logout"><Icon name="LogOut" size={15} /></button>
          </div>
        )}
      </div>
    </aside>
  );
}

function Topbar({ collapsed, onToggle, breadcrumbs }) {
  return (
    <header className={`fixed top-0 right-0 z-20 flex items-center justify-between h-16 bg-white border-b border-slate-200 px-4 shadow-sm sidebar-transition ${collapsed ? 'left-16' : 'left-64'}`}>
      <div className="flex items-center gap-3">
        <button onClick={onToggle} className="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-slate-800 transition-colors">
          <Icon name="PanelLeft" size={18} />
        </button>
        <div className="hidden sm:flex items-center gap-1.5 text-sm">
          {breadcrumbs.map((b, i) => (
            <span key={i} className="flex items-center gap-1.5">
              {i > 0 && <Icon name="ChevronRight" size={13} className="text-slate-400" />}
              {i === breadcrumbs.length - 1
                ? <span className="font-semibold text-slate-800">{b.label}</span>
                : <a href={b.href} className="text-slate-500 hover:text-brand-600 transition-colors">{b.label}</a>}
            </span>
          ))}
        </div>
      </div>
      <div className="flex items-center gap-1">
        <div className="hidden md:flex items-center gap-2 bg-slate-100 rounded-lg px-3 py-2 mr-2">
          <Icon name="Search" size={14} className="text-slate-400" />
          <input type="text" placeholder="Cari..." className="bg-transparent text-sm text-slate-700 placeholder-slate-400 outline-none w-40" />
        </div>
        <button className="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-slate-800 transition-colors">
          <Icon name="Bell" size={18} />
        </button>
        <button className="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-slate-800 transition-colors">
          <Icon name="HelpCircle" size={18} />
        </button>
        <div className="w-px h-6 bg-slate-200 mx-1"></div>
        <button className="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
          <div className="w-7 h-7 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold">A</div>
          <Icon name="ChevronDown" size={13} className="text-slate-400 hidden sm:block" />
        </button>
      </div>
    </header>
  );
}

/* ── Toast ── */
function Toast({ msg, type, onClose }) {
  useEffect(() => { const t = setTimeout(onClose, 4000); return () => clearTimeout(t); }, []);
  const cfg = type === 'success'
    ? { bg: 'bg-green-50 border-green-200', icon: 'CheckCircle', ic: 'text-green-500', tx: 'text-green-800' }
    : { bg: 'bg-red-50 border-red-200',     icon: 'XCircle',     ic: 'text-red-500',   tx: 'text-red-800' };
  return (
    <div className={`fixed top-4 right-4 z-50 flex items-center gap-3 px-4 py-3 rounded-xl border shadow-lg slide-down ${cfg.bg}`}>
      <Icon name={cfg.icon} size={18} className={cfg.ic} />
      <span className={`text-sm font-medium ${cfg.tx}`}>{msg}</span>
      <button onClick={onClose} className="ml-2 text-slate-400 hover:text-slate-600"><Icon name="X" size={14} /></button>
    </div>
  );
}

/* ── Delete Modal ── */
function DeleteModal({ item, onConfirm, onCancel }) {
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 fade-in">
        <div className="flex items-center gap-4 mb-4">
          <div className="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
            <Icon name="Trash2" size={22} className="text-red-500" />
          </div>
          <div>
            <h3 className="font-semibold text-slate-800">Hapus Rekap BKK</h3>
            <p className="text-sm text-slate-500 mt-0.5">Tindakan ini tidak dapat dibatalkan.</p>
          </div>
        </div>
        <p className="text-sm text-slate-600 mb-6">
          Apakah Anda yakin ingin menghapus rekap <span className="font-semibold text-slate-800">{item.no_rec}</span>?
        </p>
        <div className="flex gap-3 justify-end">
          <button onClick={onCancel} className="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">Batal</button>
          <a href={`/aktivitas/aktivitas3/hapus/${item.id}`}
            className="px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors">
            Ya, Hapus
          </a>
        </div>
      </div>
    </div>
  );
}

/* ── Format tanggal ── */
function fmtDate(str) {
  if (!str) return '-';
  const d = new Date(str);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

/* ── Main Page ── */
function RekapPage() {
  const allData   = window.__REKAP__ || [];
  const flash     = window.__FLASH__ || {};
  const [search,  setSearch]  = useState('');
  const [toast,   setToast]   = useState(flash.success ? { msg: flash.success, type: 'success' }
                                        : flash.error   ? { msg: flash.error,   type: 'error' } : null);
  const [delItem, setDelItem] = useState(null);

  const filtered = allData.filter(r =>
    (r.no_rec  || '').toLowerCase().includes(search.toLowerCase()) ||
    (r.no_bkk  || '').toLowerCase().includes(search.toLowerCase()) ||
    (r.ket     || '').toLowerCase().includes(search.toLowerCase())
  );

  return (
    <div className="space-y-6 fade-in">
      {toast && <Toast msg={toast.msg} type={toast.type} onClose={() => setToast(null)} />}
      {delItem && <DeleteModal item={delItem} onCancel={() => setDelItem(null)} />}

      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h1 className="text-xl font-bold text-slate-800">Rekap BKK</h1>
          <p className="text-sm text-slate-500 mt-0.5">Daftar Rekapitulasi Bukti Kas Keluar</p>
        </div>
        <a href="/aktivitas/aktivitas3/tambah"
          className="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 transition-colors shadow-sm">
          <Icon name="Plus" size={15} />Tambah Rekap
        </a>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {[
          { label: 'Total Rekap',   value: allData.length,  icon: 'ClipboardCheck', color: 'blue' },
          { label: 'Bulan Ini',     value: allData.filter(r => { const d = new Date(r.tgl); const n = new Date(); return d.getMonth() === n.getMonth() && d.getFullYear() === n.getFullYear(); }).length, icon: 'CalendarDays', color: 'green' },
          { label: 'BKK Tercakup', value: new Set(allData.map(r => r.id_bkk)).size, icon: 'FileText', color: 'amber' },
        ].map(s => {
          const colors = { blue:'bg-blue-50 text-blue-600 border-blue-100', green:'bg-green-50 text-green-600 border-green-100', amber:'bg-amber-50 text-amber-600 border-amber-100' };
          const [bg, ic, bd] = (colors[s.color] || colors.blue).split(' ');
          return (
            <div key={s.label} className={`bg-white rounded-xl border ${bd} p-5 flex items-center gap-4 shadow-sm`}>
              <div className={`${bg} ${ic} rounded-xl p-3`}><Icon name={s.icon} size={20} /></div>
              <div>
                <p className="text-xs text-slate-500 font-medium uppercase tracking-wide">{s.label}</p>
                <p className="text-2xl font-bold text-slate-800 mt-0.5">{s.value}</p>
              </div>
            </div>
          );
        })}
      </div>

      {/* Table */}
      <div className="bg-white rounded-xl border border-slate-200 shadow-sm">
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-6 py-4 border-b border-slate-100">
          <div>
            <h2 className="font-semibold text-slate-800">Daftar Rekap</h2>
            <p className="text-xs text-slate-500 mt-0.5">{filtered.length} dari {allData.length} data</p>
          </div>
          <div className="flex items-center gap-2 bg-slate-100 rounded-lg px-3 py-2">
            <Icon name="Search" size={14} className="text-slate-400" />
            <input
              type="text" placeholder="Cari no rekap, BKK..."
              value={search} onChange={e => setSearch(e.target.value)}
              className="bg-transparent text-sm text-slate-700 placeholder-slate-400 outline-none w-48"
            />
            {search && (
              <button onClick={() => setSearch('')} className="text-slate-400 hover:text-slate-600"><Icon name="X" size={13} /></button>
            )}
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="bg-slate-50 text-left">
                {['No', 'No. Rekap', 'Tanggal', 'No. BKK', 'Keterangan', 'Aksi'].map(h => (
                  <th key={h} className="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">{h}</th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {filtered.length === 0 ? (
                <tr>
                  <td colSpan={6} className="px-6 py-12 text-center">
                    <div className="flex flex-col items-center gap-2 text-slate-400">
                      <Icon name="ClipboardX" size={32} />
                      <p className="text-sm font-medium">Belum ada data rekap</p>
                      {search && <p className="text-xs">Coba ubah kata kunci pencarian</p>}
                    </div>
                  </td>
                </tr>
              ) : filtered.map((r, i) => (
                <tr key={r.id} className="hover:bg-slate-50 transition-colors">
                  <td className="px-6 py-3.5 text-slate-500 text-xs">{i + 1}</td>
                  <td className="px-6 py-3.5">
                    <span className="font-mono text-xs font-semibold text-brand-600 bg-brand-50 px-2 py-0.5 rounded">{r.no_rec}</span>
                  </td>
                  <td className="px-6 py-3.5 text-slate-600">{fmtDate(r.tgl)}</td>
                  <td className="px-6 py-3.5">
                    <code className="bg-slate-100 text-slate-700 text-xs px-2 py-0.5 rounded">{r.no_bkk || '-'}</code>
                  </td>
                  <td className="px-6 py-3.5 text-slate-500 max-w-xs truncate">{r.ket || <span className="text-slate-300">—</span>}</td>
                  <td className="px-6 py-3.5">
                    <div className="flex items-center gap-1">
                      <a href={`/aktivitas/aktivitas3/lihat/${r.id}`}
                        className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        <Icon name="Eye" size={12} />Lihat
                      </a>
                      <a href={`/aktivitas/aktivitas3/edit/${r.id}`}
                        className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-amber-700 bg-amber-50 rounded-lg hover:bg-amber-100 transition-colors">
                        <Icon name="Pencil" size={12} />Edit
                      </a>
                      <button onClick={() => setDelItem(r)}
                        className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                        <Icon name="Trash2" size={12} />Hapus
                      </button>
                    </div>
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

/* ── App Shell ── */
function App() {
  const [collapsed, setCollapsed] = useState(false);
  const currentPath = window.location.pathname;
  const breadcrumbs = [
    { label: 'Home', href: '/' },
    { label: 'Aktivitas', href: '#' },
    { label: 'Rekap BKK', href: '/aktivitas/aktivitas3' },
  ];
  return (
    <div className="min-h-screen bg-slate-100">
      <Sidebar collapsed={collapsed} currentPath={currentPath} />
      <div className={`sidebar-transition ${collapsed ? 'ml-16' : 'ml-64'}`}>
        <Topbar collapsed={collapsed} onToggle={() => setCollapsed(c => !c)} breadcrumbs={breadcrumbs} />
        <main className="pt-16 min-h-screen">
          <div className="p-6"><RekapPage /></div>
        </main>
      </div>
      {!collapsed && (
        <div className="fixed inset-0 bg-black/30 z-20 lg:hidden backdrop-blur-sm" onClick={() => setCollapsed(true)} />
      )}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
</script>
</body>
</html>
