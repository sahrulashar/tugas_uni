<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Laman — FinanceOS</title>

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
  window.__LAMAN__ = <?= json_encode($laman ?? []) ?>;
  window.__FLASH__ = {
    error:   "<?= addslashes(session()->getFlashdata('error')   ?? '') ?>",
    success: "<?= addslashes(session()->getFlashdata('success') ?? '') ?>"
  };
  window.__CSRF__ = {
    name:  "<?= csrf_token() ?>",
    value: "<?= csrf_hash() ?>"
  };
  <?php include APPPATH . "Views/_session_inject.php"; ?>
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
      svg.setAttribute('width', size);
      svg.setAttribute('height', size);
      ref.current.appendChild(svg);
    }
  }, [name, size]);
  return <span ref={ref} className={`inline-flex items-center justify-center ${className}`} />;
}

const NAV = [
  { label:'Dashboard',  icon:'LayoutDashboard', href:'/46124026' },
  { label:'Kas Keluar', icon:'ArrowUpFromLine', children:[
    { label:'Chart of Accounts', icon:'BookOpen', href:'/46124026/kas_keluar/coa_l1H' },
    { label:'Supplier',          icon:'Truck',    href:'/46124026/kas_keluar/supplier_l1H' },
    { label:'Karyawan',          icon:'Users',    href:'/46124026/kas_keluar/karyawan_l1H' },
  ]},
  { label:'Kas Masuk',  icon:'ArrowDownToLine', children:[
    { label:'Penerimaan', icon:'Receipt',  href:'#' },
    { label:'Piutang',    icon:'FilePlus', href:'#' },
  ]},
  { label:'Aktivitas',  icon:'ClipboardList', children:[
    { label:'Rencana Beli',     icon:'ShoppingCart', href:'/46124026/aktivitas/aktivitas1' },
    { label:'Bukti Kas Keluar', icon:'Receipt',      href:'/46124026/aktivitas/aktivitas2' },
    { label:'Rekap BKK',        icon:'ClipboardCheck', href:'/46124026/aktivitas/aktivitas3' },
  ]},
  { label:'Laporan',    icon:'BarChart3', children:[
    { label:'Neraca',    icon:'Scale',      href:'#' },
    { label:'Laba Rugi', icon:'TrendingUp', href:'#' },
    { label:'Arus Kas',  icon:'Activity',   href:'#' },
  ]},
  { label:'Pengaturan RBAC', icon:'Shield', children:[
    { label:'Manajemen User',  icon:'Users',    href:'/46124026/rbac/user' },
    { label:'Laman & Aksi',    icon:'FileText', href:'/46124026/rbac/laman' },
    { label:'Atur Hak Akses',  icon:'Lock',     href:'/46124026/rbac/akses' },
    { label:'Audit Trail Log', icon:'Activity', href:'/46124026/audit' },
  ]},
];

function NavItem({ item, currentPath }) {
  const hasChildren = item.children?.length > 0;
  const isParentActive = hasChildren && item.children.some(c => c.href === currentPath || (c.href !== '/46124026' && currentPath.startsWith(c.href)));
  const [open, setOpen] = useState(isParentActive || item.label === 'Pengaturan RBAC');

  if (!hasChildren) {
    const active = currentPath === item.href;
    return (
      <li>
        <a href={item.href}
          className={`flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors
            ${active ? 'bg-brand-600 text-white' : 'text-slate-400 hover:text-white hover:bg-white/5'}`}>
          <Icon name={item.icon} size={16}/>
          <span>{item.label}</span>
        </a>
      </li>
    );
  }

  return (
    <li>
      <button onClick={() => setOpen(!open)}
        className={`w-full flex items-center justify-between gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors
          ${isParentActive ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5'}`}>
        <span className="flex items-center gap-3">
          <Icon name={item.icon} size={16}/>
          {item.label}
        </span>
        <span className={`transition-transform duration-200 ${open ? 'rotate-90' : ''}`}>
          <Icon name="ChevronRight" size={14}/>
        </span>
      </button>
      {open && (
        <ul className="mt-1 ml-4 pl-3 border-l border-slate-700 space-y-0.5 fade-in">
          {item.children.map(child => {
            const active = currentPath === child.href || (child.href !== '/46124026' && currentPath.startsWith(child.href + '/'));
            return (
              <li key={child.label}>
                <a href={child.href}
                  className={`flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors
                    ${active ? 'bg-brand-600 text-white font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5'}`}>
                  <Icon name={child.icon} size={14}/>
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
    <aside className={`fixed inset-y-0 left-0 z-30 flex flex-col bg-sidebar sidebar-transition ${collapsed ? 'w-16' : 'w-64'}`}>
      <div className={`flex items-center gap-3 px-4 py-5 border-b border-slate-800 ${collapsed ? 'justify-center' : ''}`}>
        <div className="flex-shrink-0 w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center">
          <Icon name="Landmark" size={16} className="text-white"/>
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
          {NAV.map(item => collapsed ? (
            <li key={item.label} title={item.label}>
              <a href={item.href || '#'}
                className="flex items-center justify-center w-full py-3 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors">
                <Icon name={item.icon} size={18}/>
              </a>
            </li>
          ) : (
            <NavItem key={item.label} item={item} currentPath={currentPath}/>
          ))}
        </ul>
      </nav>

      <div className={`border-t border-slate-800 p-3 ${collapsed ? 'flex justify-center' : ''}`}>
        {collapsed ? (
          <div className="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold">
            {window._erpUser ? window._erpUser.initial : "U"}
          </div>
        ) : (
          <div className="flex items-center gap-3 px-1">
            <div className="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
              {window._erpUser ? window._erpUser.initial : "U"}
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-white text-sm font-medium truncate">{window._erpUser ? window._erpUser.nama : "User"}</p>
              <p className="text-slate-500 text-xs truncate">{window._erpUser ? window._erpUser.kode : ""}</p>
            </div>
            <a href="/logout" className="ml-auto px-2.5 py-1 rounded bg-red-800 text-red-100 hover:bg-red-700 hover:text-white text-xs font-semibold no-underline inline-block" title="Logout">
              ⏻ Keluar
            </a>
          </div>
        )}
      </div>
    </aside>
  );
}

function Topbar({ collapsed, onToggle }) {
  return (
    <header className={`fixed top-0 right-0 z-20 flex items-center justify-between h-16 bg-white border-b border-slate-200 px-4 shadow-sm sidebar-transition ${collapsed ? 'left-16' : 'left-64'}`}>
      <div className="flex items-center gap-3">
        <button onClick={onToggle} className="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-slate-800 transition-colors">
          <Icon name="PanelLeft" size={18}/>
        </button>
        <div className="hidden sm:flex items-center gap-1.5 text-sm">
          <a href="/46124026" className="text-slate-500 hover:text-brand-600 transition-colors">Dashboard</a>
          <Icon name="ChevronRight" size={13} className="text-slate-400"/>
          <a href="/46124026/rbac/laman" className="text-slate-500 hover:text-brand-600 transition-colors">Laman & Aksi</a>
          <Icon name="ChevronRight" size={13} className="text-slate-400"/>
          <span className="font-semibold text-slate-800">Edit Laman</span>
        </div>
      </div>
    </header>
  );
}

function EditLamanPage() {
  const [collapsed, setCollapsed] = useState(false);
  const l = window.__LAMAN__ || {};
  const flash = window.__FLASH__ || {};
  const csrf = window.__CSRF__ || {};

  return (
    <div className="min-h-screen bg-slate-100">
      <Sidebar collapsed={collapsed} currentPath="/46124026/rbac/laman"/>

      <div className={`sidebar-transition ${collapsed ? 'ml-16' : 'ml-64'}`}>
        <Topbar collapsed={collapsed} onToggle={() => setCollapsed(c => !c)}/>

        <main className="pt-16 min-h-screen">
          <div className="p-6 max-w-2xl mx-auto space-y-6 fade-in">

            <div>
              <a href="/46124026/rbac/laman" className="inline-flex items-center gap-1 text-xs text-brand-600 hover:underline mb-2 font-medium">
                <Icon name="ArrowLeft" size={13}/> Kembali ke Daftar Laman
              </a>
              <h1 className="text-xl font-bold text-slate-800 flex items-center gap-2">
                <Icon name="Pencil" size={20} className="text-brand-600"/>
                Edit Laman: [{l.kode || ''}] {l.nama || ''}
              </h1>
              <p className="text-sm text-slate-500 mt-0.5">
                Perbarui definisi kode modul, nama laman, atau jenis aksi
              </p>
            </div>

            {flash.error && (
              <div className="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-2">
                <Icon name="AlertCircle" size={18}/>
                <span>{flash.error}</span>
              </div>
            )}

            <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
              <form action="/46124026/rbac/laman/update" method="POST" className="space-y-5">
                <input type="hidden" name={csrf.name} value={csrf.value}/>
                <input type="hidden" name="id" value={l.id}/>

                <div>
                  <label className="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Kode Modul <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    name="kode"
                    defaultValue={l.kode}
                    required
                    className="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-lg text-slate-800 outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all font-mono"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nama Halaman <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    name="nama"
                    defaultValue={l.nama}
                    required
                    className="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-lg text-slate-800 outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Jenis Aksi <span className="text-red-500">*</span>
                  </label>
                  <select
                    name="aksi"
                    defaultValue={l.aksi}
                    required
                    className="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-lg text-slate-800 outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all bg-white cursor-pointer font-medium">
                    <option value="daftar">daftar — Akses melihat daftar/tabel index</option>
                    <option value="tambah">tambah — Akses input dan simpan data baru</option>
                    <option value="edit">edit — Akses formulir ubah dan update data</option>
                    <option value="hapus">hapus — Akses menghapus data</option>
                    <option value="lihat">lihat — Akses membuka detail/rinci data</option>
                    <option value="cetak">cetak — Akses mencetak laporan atau dokumen PDF</option>
                  </select>
                </div>

                <div className="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                  <a href="/46124026/rbac/laman"
                    className="px-4 py-2.5 rounded-lg border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                    Batal
                  </a>
                  <button type="submit"
                    className="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm transition-colors">
                    <Icon name="Save" size={15}/>
                    Simpan Perubahan
                  </button>
                </div>
              </form>
            </div>

          </div>
        </main>
      </div>

      {!collapsed && (
        <div className="fixed inset-0 bg-black/30 z-20 lg:hidden backdrop-blur-sm"
          onClick={() => setCollapsed(true)}/>
      )}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<EditLamanPage/>);
</script>
</body>
</html>
