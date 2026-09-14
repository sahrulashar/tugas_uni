<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tambah Rekap BKK — FinanceOS</title>

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
  window.__BKK__ = <?= json_encode($bkk ?? []) ?>;
  window.__FLASH__ = {
    success: "<?= addslashes(session()->getFlashdata('success') ?? '') ?>",
    error:   "<?= addslashes(session()->getFlashdata('error')   ?? '') ?>"
  };
  window.__CSRF__ = {
    name:  "<?= csrf_token() ?>",
    value: "<?= csrf_hash() ?>"
  };
  window.__OLD__ = <?= json_encode(old() ?: new stdClass()) ?>;
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
    { label: 'Chart of Accounts', icon: 'BookOpen', href: '/kas_keluar/coa' },
    { label: 'Supplier',          icon: 'Truck',    href: '/kas_keluar/supplier' },
    { label: 'Karyawan',          icon: 'Users',    href: '/kas_keluar/karyawan' },
  ]},
  { label: 'Kas Masuk', icon: 'ArrowDownToLine', children: [
    { label: 'Penerimaan', icon: 'Receipt',  href: '#' },
    { label: 'Piutang',    icon: 'FilePlus', href: '#' },
  ]},
  { label: 'Aktivitas', icon: 'ClipboardList', children: [
    { label: 'Rencana Beli',     icon: 'ShoppingCart',  href: '/aktivitas/aktivitas1' },
    { label: 'Bukti Kas Keluar', icon: 'Receipt',        href: '/aktivitas/aktivitas2' },
    { label: 'Rekap BKK',        icon: 'ClipboardCheck', href: '/aktivitas/aktivitas3' },
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
            <span className={`transition-transform duration-200 ${open ? 'rotate-90':''}`}><Icon name="ChevronRight" size={14}/></span>
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
        <button className="p-2 rounded-lg hover:bg-slate-100 text-slate-500 transition-colors"><Icon name="HelpCircle" size={18}/></button>
        <div className="w-px h-6 bg-slate-200 mx-1"></div>
        <button className="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
          <div className="w-7 h-7 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold">A</div>
        </button>
      </div>
    </header>
  );
}

/* ── Form Page ── */
function TambahPage() {
  const bkkList = window.__BKK__  || [];
  const old     = window.__OLD__  || {};
  const flash   = window.__FLASH__|| {};
  const csrf    = window.__CSRF__ || {};

  const [noRec, setNoRec] = useState(old.no_rec || '');
  const [tgl,   setTgl]   = useState(old.tgl    || new Date().toISOString().split('T')[0]);
  const [idBkk, setIdBkk] = useState(old.id_bkk || '');
  const [ket,   setKet]   = useState(old.ket    || '');
  const [errors, setErrors] = useState({});

  const selectedBkk = bkkList.find(b => String(b.id) === String(idBkk));

  function validate() {
    const e = {};
    if (!noRec.trim()) e.noRec = 'No. Rekap wajib diisi.';
    if (!tgl)          e.tgl   = 'Tanggal wajib diisi.';
    if (!idBkk)        e.idBkk = 'Pilih BKK terlebih dahulu.';
    setErrors(e);
    return Object.keys(e).length === 0;
  }

  function handleSubmit(e) {
    if (!validate()) { e.preventDefault(); }
  }

  const inputCls = (err) =>
    `w-full px-3 py-2.5 text-sm rounded-lg border transition-colors outline-none focus:ring-2
     ${err ? 'border-red-300 focus:ring-red-100 bg-red-50' : 'border-slate-300 focus:ring-brand-100 focus:border-brand-400 bg-white'}`;

  return (
    <div className="max-w-2xl mx-auto space-y-6 fade-in">
      {/* Header */}
      <div className="flex items-center gap-3">
        <a href="/aktivitas/aktivitas3" className="p-2 rounded-lg hover:bg-slate-200 text-slate-500 transition-colors">
          <Icon name="ArrowLeft" size={18}/>
        </a>
        <div>
          <h1 className="text-xl font-bold text-slate-800">Tambah Rekap BKK</h1>
          <p className="text-sm text-slate-500 mt-0.5">Buat rekapitulasi Bukti Kas Keluar baru</p>
        </div>
      </div>

      {/* Flash error */}
      {flash.error && (
        <div className="flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
          <Icon name="AlertCircle" size={16} className="text-red-500 flex-shrink-0"/>
          {flash.error}
        </div>
      )}

      {/* Form Card */}
      <form method="POST" action="/aktivitas/aktivitas3/simpan" onSubmit={handleSubmit}
        className="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
        <input type="hidden" name={csrf.name} value={csrf.value}/>

        {/* No. Rekap */}
        <div>
          <label className="block text-sm font-medium text-slate-700 mb-1.5">
            No. Rekap <span className="text-red-500">*</span>
          </label>
          <input type="text" name="no_rec" value={noRec} onChange={e => setNoRec(e.target.value)}
            placeholder="Contoh: REC-2026-001"
            className={inputCls(errors.noRec)}/>
          {errors.noRec && <p className="text-xs text-red-500 mt-1">{errors.noRec}</p>}
        </div>

        {/* Tanggal */}
        <div>
          <label className="block text-sm font-medium text-slate-700 mb-1.5">
            Tanggal <span className="text-red-500">*</span>
          </label>
          <input type="date" name="tgl" value={tgl} onChange={e => setTgl(e.target.value)}
            className={inputCls(errors.tgl)}/>
          {errors.tgl && <p className="text-xs text-red-500 mt-1">{errors.tgl}</p>}
        </div>

        {/* Pilih BKK */}
        <div>
          <label className="block text-sm font-medium text-slate-700 mb-1.5">
            Bukti Kas Keluar (BKK) <span className="text-red-500">*</span>
          </label>
          <select name="id_bkk" value={idBkk} onChange={e => setIdBkk(e.target.value)}
            className={inputCls(errors.idBkk)}>
            <option value="">-- Pilih BKK --</option>
            {bkkList.map(b => (
              <option key={b.id} value={b.id}>
                {b.no_bkk} — {b.tgl}
              </option>
            ))}
          </select>
          {errors.idBkk && <p className="text-xs text-red-500 mt-1">{errors.idBkk}</p>}
          {selectedBkk && (
            <div className="mt-2 px-3 py-2 bg-blue-50 border border-blue-100 rounded-lg text-xs text-blue-700">
              <span className="font-semibold">BKK Terpilih:</span> {selectedBkk.no_bkk}
              {selectedBkk.kete ? ` — ${selectedBkk.kete}` : ''}
            </div>
          )}
        </div>

        {/* Keterangan */}
        <div>
          <label className="block text-sm font-medium text-slate-700 mb-1.5">Keterangan</label>
          <textarea name="ket" value={ket} onChange={e => setKet(e.target.value)}
            rows={3} placeholder="Keterangan opsional..."
            className="w-full px-3 py-2.5 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-100 focus:border-brand-400 bg-white outline-none transition-colors resize-none"/>
        </div>

        {/* Actions */}
        <div className="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
          <a href="/aktivitas/aktivitas3"
            className="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">
            Batal
          </a>
          <button type="submit"
            className="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 transition-colors shadow-sm">
            <Icon name="Save" size={15}/>Simpan Rekap
          </button>
        </div>
      </form>
    </div>
  );
}

function App() {
  const [collapsed, setCollapsed] = useState(false);
  const currentPath = window.location.pathname;
  const breadcrumbs = [
    { label: 'Home', href: '/' },
    { label: 'Aktivitas', href: '#' },
    { label: 'Rekap BKK', href: '/aktivitas/aktivitas3' },
    { label: 'Tambah', href: '#' },
  ];
  return (
    <div className="min-h-screen bg-slate-100">
      <Sidebar collapsed={collapsed} currentPath={currentPath}/>
      <div className={`sidebar-transition ${collapsed ? 'ml-16':'ml-64'}`}>
        <Topbar collapsed={collapsed} onToggle={() => setCollapsed(c => !c)} breadcrumbs={breadcrumbs}/>
        <main className="pt-16 min-h-screen">
          <div className="p-6"><TambahPage/></div>
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
