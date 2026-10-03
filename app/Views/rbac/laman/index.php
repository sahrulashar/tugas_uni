<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Laman & Aksi — FinanceOS</title>

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
const { useState, useEffect, useRef, useMemo } = React;

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
  { label:'Aktivitas',  icon:'ClipboardList', children:[
    { label:'Rencana Beli',     icon:'ShoppingCart', href:'/46124026/aktivitas/aktivitas1' },
    { label:'Bukti Kas Keluar', icon:'Receipt',      href:'/46124026/aktivitas/aktivitas2' },
    { label:'Rekap BKK',        icon:'ClipboardCheck', href:'/46124026/aktivitas/aktivitas3' },
  ]},
  { label:'Pengaturan RBAC', icon:'Shield', children:[
    { label:'Manajemen User',  icon:'Users',    href:'/46124026/rbac/user' },
    { label:'Laman & Aksi',    icon:'FileText', href:'/46124026/rbac/laman' },
    { label:'Atur Hak Akses',  icon:'Lock',     href:'/46124026/rbac/akses' },
  ]},
];

function NavItem({ item, currentPath }) {
  const hasChildren = item.children?.length > 0;
  const isParentActive = hasChildren && item.children.some(c => c.href === currentPath);
  const [open, setOpen] = useState(isParentActive || item.label === 'Pengaturan RBAC');

  if (!hasChildren) {
    const active = currentPath === item.href;
    return (
      <li>
        <a href={item.href}
          className={`flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
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
        className={`w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
          ${isParentActive ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5'}`}>
        <span className="flex items-center gap-3">
          <Icon name={item.icon} size={16}/>
          {item.label}
        </span>
        <Icon name={open ? 'ChevronDown' : 'ChevronRight'} size={14}/>
      </button>
      {open && (
        <ul className="mt-1 ml-4 pl-3 border-l border-slate-700/60 space-y-1">
          {item.children.map(child => {
            const active = currentPath === child.href;
            return (
              <li key={child.label}>
                <a href={child.href}
                  className={`flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors
                    ${active ? 'bg-brand-600 text-white font-semibold' : 'text-slate-400 hover:text-white hover:bg-white/5'}`}>
                  <Icon name={child.icon} size={13}/>
                  {child.label}
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
          <Icon name="ShieldCheck" size={18} className="text-white"/>
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
          <span className="text-slate-500">Pengaturan RBAC</span>
          <Icon name="ChevronRight" size={13} className="text-slate-400"/>
          <span className="font-semibold text-slate-800">Laman & Aksi</span>
        </div>
      </div>
    </header>
  );
}

function Toast({ flash, onClose }) {
  useEffect(() => {
    if (flash.success || flash.error) {
      const t = setTimeout(onClose, 4000);
      return () => clearTimeout(t);
    }
  }, [flash]);

  if (!flash.success && !flash.error) return null;
  const isSuccess = !!flash.success;

  return (
    <div className={`fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-3.5 rounded-xl shadow-xl text-sm font-medium
      ${isSuccess ? 'bg-green-600 text-white' : 'bg-red-600 text-white'}`}>
      <Icon name={isSuccess ? 'CheckCircle' : 'AlertCircle'} size={18}/>
      <span>{flash.success || flash.error}</span>
      <button onClick={onClose} className="ml-2 opacity-70 hover:opacity-100">
        <Icon name="X" size={15}/>
      </button>
    </div>
  );
}

function LamanPage() {
  const [collapsed, setCollapsed] = useState(false);
  const [search, setSearch] = useState('');
  const [filterAksi, setFilterAksi] = useState('Semua');
  const [showFlash, setShowFlash] = useState(true);

  const lamanList = window.__LAMAN__ || [];
  const flash = window.__FLASH__ || {};
  const currentPath = window.location.pathname;

  const total = lamanList.length;
  const uniqueModul = useMemo(() => new Set(lamanList.map(l => l.kode)).size, [lamanList]);

  const filtered = useMemo(() => {
    return lamanList.filter(l => {
      const matchSearch = l.nama.toLowerCase().includes(search.toLowerCase()) ||
                          l.kode.toLowerCase().includes(search.toLowerCase());
      const matchAksi = filterAksi === 'Semua' ? true : l.aksi === filterAksi;
      return matchSearch && matchAksi;
    });
  }, [lamanList, search, filterAksi]);

  const getAksiColor = (aksi) => {
    switch (aksi) {
      case 'daftar': return 'bg-blue-50 text-blue-700 border-blue-200';
      case 'tambah': return 'bg-green-50 text-green-700 border-green-200';
      case 'edit':   return 'bg-amber-50 text-amber-700 border-amber-200';
      case 'hapus':  return 'bg-red-50 text-red-700 border-red-200';
      case 'lihat':  return 'bg-purple-50 text-purple-700 border-purple-200';
      case 'cetak':  return 'bg-emerald-50 text-emerald-700 border-emerald-200';
      default:       return 'bg-slate-100 text-slate-700 border-slate-200';
    }
  };

  return (
    <div className="min-h-screen bg-slate-100">
      <Sidebar collapsed={collapsed} currentPath={currentPath}/>

      <div className={`sidebar-transition ${collapsed ? 'ml-16' : 'ml-64'}`}>
        <Topbar collapsed={collapsed} onToggle={() => setCollapsed(c => !c)}/>

        <main className="pt-16 min-h-screen">
          <div className="p-6 space-y-6 fade-in">

            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div>
                <h1 className="text-xl font-bold text-slate-800 flex items-center gap-2">
                  <Icon name="FileText" size={20} className="text-brand-600"/>
                  Daftar Laman & Aksi Sistem
                </h1>
                <p className="text-sm text-slate-500 mt-0.5">
                  Daftar seluruh titik modul dan jenis aksi yang dikendalikan oleh sistem RBAC
                </p>
              </div>
              <div className="flex items-center gap-2">
                <a href="/46124026/rbac/laman/tambah"
                  className="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg transition-colors shadow-sm">
                  <Icon name="Plus" size={15}/>
                  Tambah Laman / Aksi
                </a>
              </div>
            </div>

            {/* Stats Cards */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center justify-between">
                <div>
                  <p className="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Titik Laman & Aksi</p>
                  <p className="text-2xl font-bold text-slate-800 mt-1">{total}</p>
                </div>
                <div className="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                  <Icon name="FileCode" size={20}/>
                </div>
              </div>
              <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center justify-between">
                <div>
                  <p className="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Modul Unik</p>
                  <p className="text-2xl font-bold text-indigo-600 mt-1">{uniqueModul}</p>
                </div>
                <div className="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                  <Icon name="Layers" size={20}/>
                </div>
              </div>
            </div>

            {/* Table Card */}
            <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 border-b border-slate-100">
                <div className="flex items-center gap-2 bg-slate-100 rounded-lg px-3 py-2 flex-1 max-w-sm">
                  <Icon name="Search" size={14} className="text-slate-400"/>
                  <input
                    type="text"
                    placeholder="Cari kode modul atau nama halaman..."
                    value={search}
                    onChange={e => setSearch(e.target.value)}
                    className="bg-transparent text-sm text-slate-700 placeholder-slate-400 outline-none w-full"
                  />
                  {search && (
                    <button onClick={() => setSearch('')} className="text-slate-400 hover:text-slate-600">
                      <Icon name="X" size={13}/>
                    </button>
                  )}
                </div>

                <div className="flex items-center gap-3">
                  <select
                    value={filterAksi}
                    onChange={e => setFilterAksi(e.target.value)}
                    className="text-sm border border-slate-200 rounded-lg px-3 py-2 text-slate-700 bg-white outline-none focus:ring-2 focus:ring-brand-500 cursor-pointer">
                    <option value="Semua">Semua Jenis Aksi</option>
                    <option value="daftar">daftar</option>
                    <option value="tambah">tambah</option>
                    <option value="edit">edit</option>
                    <option value="hapus">hapus</option>
                    <option value="lihat">lihat</option>
                    <option value="cetak">cetak</option>
                  </select>
                  <span className="text-xs text-slate-400">
                    Menampilkan <strong>{filtered.length}</strong> laman
                  </span>
                </div>
              </div>

              <div className="overflow-x-auto">
                <table className="w-full text-sm">
                  <thead>
                    <tr className="bg-slate-50/80 border-b border-slate-100 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                      <th className="px-5 py-3 w-12">#</th>
                      <th className="px-5 py-3">Kode Modul</th>
                      <th className="px-5 py-3">Nama Halaman</th>
                      <th className="px-5 py-3">Aksi Terproteksi</th>
                      <th className="px-5 py-3">User Diberi Izin</th>
                      <th className="px-5 py-3 text-right">Tindakan</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {filtered.length === 0 ? (
                      <tr>
                        <td colSpan="6" className="px-5 py-8 text-center text-slate-400">
                          Tidak ditemukan laman yang sesuai filter.
                        </td>
                      </tr>
                    ) : (
                      filtered.map((l, i) => (
                        <tr key={l.id} className="hover:bg-slate-50/60 transition-colors">
                          <td className="px-5 py-3.5 text-slate-400 text-xs font-mono">{i + 1}</td>
                          <td className="px-5 py-3.5">
                            <span className="font-mono text-xs font-semibold px-2 py-1 rounded bg-slate-100 text-brand-700 border border-slate-200">
                              {l.kode}
                            </span>
                          </td>
                          <td className="px-5 py-3.5 font-medium text-slate-800">
                            {l.nama}
                          </td>
                          <td className="px-5 py-3.5">
                            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider border ${getAksiColor(l.aksi)}`}>
                              {l.aksi}
                            </span>
                          </td>
                          <td className="px-5 py-3.5 text-slate-600">
                            <span className="inline-flex items-center gap-1 font-semibold text-xs px-2 py-1 rounded bg-slate-50 text-slate-700 border border-slate-200">
                              <Icon name="User" size={12} className="text-slate-400"/>
                              {l.jumlah_user ?? 0} user
                            </span>
                          </td>
                          <td className="px-5 py-3.5 text-right">
                            <div className="inline-flex items-center gap-2">
                              <a href={`/46124026/rbac/laman/edit/${l.id}`}
                                className="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-brand-700 bg-brand-50 border border-brand-200 hover:bg-brand-100 transition-colors"
                                title="Edit Laman">
                                <Icon name="Pencil" size={13}/>
                                Edit
                              </a>
                              <a href={`/46124026/rbac/laman/hapus/${l.id}`}
                                onClick={e => { if(!confirm(`Yakin ingin menghapus laman [${l.kode}] ${l.nama} (${l.aksi})?`)) e.preventDefault(); }}
                                className="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-red-700 bg-red-50 border border-red-200 hover:bg-red-100 transition-colors"
                                title="Hapus Laman">
                                <Icon name="Trash2" size={13}/>
                                Hapus
                              </a>
                            </div>
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </main>
      </div>

      {showFlash && <Toast flash={flash} onClose={() => setShowFlash(false)}/>}

      {!collapsed && (
        <div className="fixed inset-0 bg-black/30 z-20 lg:hidden backdrop-blur-sm"
          onClick={() => setCollapsed(true)}/>
      )}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<LamanPage/>);
</script>
</body>
</html>
