<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Karyawan — FinanceOS</title>

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
  window.__KARYAWAN__ = <?= json_encode($karyawan ?? []) ?>;
  window.__STATS__    = <?= json_encode($stats ?? []) ?>;
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
  { label:'Dashboard',  icon:'LayoutDashboard', href:'/' },
  { label:'Kas Keluar', icon:'ArrowUpFromLine', children:[
    { label:'Chart of Accounts', icon:'BookOpen', href:'/kas_keluar/coa' },
    { label:'Supplier',          icon:'Truck',    href:'/kas_keluar/supplier' },
    { label:'Karyawan',          icon:'Users',    href:'/kas_keluar/karyawan' },
  ]},
  { label:'Kas Masuk',  icon:'ArrowDownToLine', children:[
    { label:'Penerimaan', icon:'Receipt',  href:'#' },
    { label:'Piutang',    icon:'FilePlus', href:'#' },
  ]},
  { label:'Laporan',    icon:'BarChart3', children:[
    { label:'Neraca',    icon:'Scale',      href:'#' },
    { label:'Laba Rugi', icon:'TrendingUp', href:'#' },
    { label:'Arus Kas',  icon:'Activity',   href:'#' },
  ]},
  { label:'Pengaturan', icon:'Settings', href:'#' },
];

function NavItem({ item, currentPath }) {
  const hasChildren = item.children?.length > 0;
  const isParentActive = hasChildren && item.children.some(c => c.href === currentPath);
  const [open, setOpen] = useState(isParentActive);

  if (!hasChildren) {
    return (
      <li>
        <a href={item.href}
          className={`flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors
            ${currentPath === item.href ? 'text-white bg-brand-600' : 'text-slate-400 hover:text-white hover:bg-white/5'}`}>
          <Icon name={item.icon} size={16}/>{item.label}
        </a>
      </li>
    );
  }

  return (
    <li>
      <button onClick={() => setOpen(o => !o)}
        className={`w-full flex items-center justify-between gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors
          ${isParentActive ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5'}`}>
        <span className="flex items-center gap-3"><Icon name={item.icon} size={16}/>{item.label}</span>
        <span className={`transition-transform duration-200 ${open ? 'rotate-90' : ''}`}><Icon name="ChevronRight" size={14}/></span>
      </button>
      {open && (
        <ul className="mt-1 ml-4 pl-3 border-l border-slate-700 space-y-0.5 fade-in">
          {item.children.map(c => (
            <li key={c.href}>
              <a href={c.href}
                className={`flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors
                  ${currentPath === c.href ? 'text-white bg-brand-600 font-medium' : 'text-slate-400 hover:text-white hover:bg-white/5'}`}>
                <Icon name={c.icon} size={14}/>{c.label}
              </a>
            </li>
          ))}
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
          <div className="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold">A</div>
        ) : (
          <div className="flex items-center gap-3 px-1">
            <div className="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">A</div>
            <div className="flex-1 min-w-0">
              <p className="text-white text-sm font-medium truncate">Admin User</p>
              <p className="text-slate-500 text-xs truncate">admin@company.com</p>
            </div>
            <button className="text-slate-500 hover:text-white transition-colors" title="Logout"><Icon name="LogOut" size={15}/></button>
          </div>
        )}
      </div>
    </aside>
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
    <div className={`fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-3.5 rounded-xl shadow-xl text-sm font-medium slide-down
      ${isSuccess ? 'bg-green-600 text-white' : 'bg-red-600 text-white'}`}>
      <Icon name={isSuccess ? 'CheckCircle' : 'XCircle'} size={18}/>
      <span>{flash.success || flash.error}</span>
      <button onClick={onClose} className="ml-2 opacity-70 hover:opacity-100">
        <Icon name="X" size={15}/>
      </button>
    </div>
  );
}

function ConfirmModal({ item, onCancel }) {
  if (!item) return null;
  const isAktif = item.status === 'Aktif';

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md fade-in">
        <div className="p-6">
          <div className={`w-12 h-12 rounded-full flex items-center justify-center mb-4 ${isAktif ? 'bg-amber-100' : 'bg-green-100'}`}>
            <Icon name={isAktif ? 'AlertTriangle' : 'CheckCircle'} size={22} className={isAktif ? 'text-amber-600' : 'text-green-600'}/>
          </div>
          <h3 className="text-lg font-semibold text-slate-800">
            {isAktif ? 'Nonaktifkan Karyawan?' : 'Aktifkan Karyawan?'}
          </h3>
          <p className="text-sm text-slate-500 mt-2">
            {isAktif
              ? <>Karyawan <strong>{item.nama_karyawan} ({item.nip})</strong> akan dinonaktifkan dari sistem. Data masih tersimpan dan dapat diaktifkan kembali kapan saja.</>
              : <>Karyawan <strong>{item.nama_karyawan} ({item.nip})</strong> akan diaktifkan kembali ke sistem.</>
            }
          </p>
        </div>
        <div className="flex gap-3 px-6 pb-6">
          <button onClick={onCancel} className="flex-1 px-4 py-2.5 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
            Batal
          </button>
          <a
            href={isAktif ? `/kas_keluar/hapus_karyawan/${item.id_karyawan}` : `/kas_keluar/aktifkan_karyawan/${item.id_karyawan}`}
            className={`flex-1 px-4 py-2.5 text-sm font-medium text-white text-center rounded-lg transition-colors ${isAktif ? 'bg-amber-600 hover:bg-amber-700' : 'bg-green-600 hover:bg-green-700'}`}
          >
            {isAktif ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'}
          </a>
        </div>
      </div>
    </div>
  );
}

function DeleteModal({ item, onCancel }) {
  if (!item) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md fade-in">
        <div className="p-6">
          <div className="w-12 h-12 rounded-full flex items-center justify-center mb-4 bg-red-100">
            <Icon name="Trash2" size={22} className="text-red-600"/>
          </div>
          <h3 className="text-lg font-semibold text-slate-800">
            Hapus Karyawan Permanen?
          </h3>
          <p className="text-sm text-slate-500 mt-2">
            Data karyawan <strong>{item.nama_karyawan} ({item.nip})</strong> akan dihapus permanen dari basis data. Tindakan ini tidak dapat dibatalkan.
          </p>
        </div>
        <div className="flex gap-3 px-6 pb-6">
          <button onClick={onCancel} className="flex-1 px-4 py-2.5 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
            Batal
          </button>
          <a
            href={`/kas_keluar/hapus_permanen_karyawan/${item.id_karyawan}`}
            className="flex-1 px-4 py-2.5 text-sm font-medium text-white text-center rounded-lg transition-colors bg-red-600 hover:bg-red-700 shadow-sm"
          >
            Ya, Hapus Permanen
          </a>
        </div>
      </div>
    </div>
  );
}

function KaryawanPage() {
  const rawKaryawan = window.__KARYAWAN__ || [];
  const rawStats    = window.__STATS__ || [];
  const flash       = window.__FLASH__ || {};

  const [collapsed, setCollapsed]       = useState(false);
  const [search, setSearch]             = useState('');
  const [filterJabatan, setFilterJabatan] = useState('Semua');
  const [filterStatus, setFilterStatus]   = useState('Semua');
  const [confirmItem, setConfirmItem]   = useState(null);
  const [deleteItem, setDeleteItem]     = useState(null);
  const [showFlash, setShowFlash]       = useState(true);

  const currentPath = '/kas_keluar/karyawan';

  const listJabatan = useMemo(() => {
    const list = [...new Set(rawKaryawan.map(r => r.jabatan || 'Lainnya'))];
    return ['Semua', ...list];
  }, [rawKaryawan]);

  const filtered = useMemo(() => {
    return rawKaryawan.filter(row => {
      const q = search.toLowerCase();
      const matchSearch = search === '' ||
        (row.nip && row.nip.toLowerCase().includes(q)) ||
        (row.nama_karyawan && row.nama_karyawan.toLowerCase().includes(q));
      const matchJabatan = filterJabatan === 'Semua' || (row.jabatan || 'Lainnya') === filterJabatan;
      const matchStatus  = filterStatus === 'Semua'  || row.status === filterStatus;
      return matchSearch && matchJabatan && matchStatus;
    });
  }, [rawKaryawan, search, filterJabatan, filterStatus]);

  return (
    <div className="min-h-screen bg-slate-100">
      <Sidebar collapsed={collapsed} currentPath={currentPath} />

      <div className={`sidebar-transition ${collapsed ? 'ml-16' : 'ml-64'}`}>
        <header className={`fixed top-0 right-0 z-20 flex items-center justify-between h-16 bg-white border-b border-slate-200 px-4 shadow-sm sidebar-transition ${collapsed ? 'left-16' : 'left-64'}`}>
          <div className="flex items-center gap-3">
            <button onClick={() => setCollapsed(c => !c)} className="p-2 rounded-lg hover:bg-slate-100 text-slate-500">
              <Icon name="PanelLeft" size={18}/>
            </button>
            <div className="hidden sm:flex items-center gap-1.5 text-sm">
              <a href="/" className="text-slate-500 hover:text-brand-600">Home</a>
              <Icon name="ChevronRight" size={13} className="text-slate-400"/>
              <span className="font-semibold text-slate-800">Karyawan</span>
            </div>
          </div>
          <div className="flex items-center gap-2">
            <button onClick={() => window.print()} className="flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
              <Icon name="Printer" size={15}/>
              <span className="hidden sm:inline">Cetak</span>
            </button>
            <button className="flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
              <Icon name="Download" size={15}/>
              <span className="hidden sm:inline">Export</span>
            </button>
            <a href="/kas_keluar/tambah_karyawan" className="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg shadow-sm transition-colors">
              <Icon name="Plus" size={15}/>
              Tambah Karyawan
            </a>
          </div>
        </header>

        <main className="pt-16 min-h-screen">
          <div className="p-6 space-y-6 fade-in">
            {/* Header */}
            <div>
              <h1 className="text-xl font-bold text-slate-800 flex items-center gap-2">
                <Icon name="Users" size={20} className="text-brand-600"/> Data Karyawan
              </h1>
              <p className="text-sm text-slate-500 mt-0.5">Kelola identitas, jabatan, dan status kepegawaian</p>
            </div>

            {/* Stats Cards */}
            {rawStats.length > 0 && (
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                {rawStats.map(s => (
                  <div key={s.jabatan} className="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-start gap-4 hover:shadow-md transition-shadow">
                    <div className="w-11 h-11 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center flex-shrink-0">
                      <Icon name="Briefcase" size={19}/>
                    </div>
                    <div>
                      <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide">{s.jabatan || 'Lainnya'}</p>
                      <p className="text-xl font-bold text-slate-800 mt-0.5">{s.total} <span className="text-xs font-normal text-slate-400">orang</span></p>
                      <p className="text-xs text-green-600 font-medium mt-0.5">{s.aktif} aktif</p>
                    </div>
                  </div>
                ))}
              </div>
            )}

            {/* Table Area */}
            <div className="bg-white rounded-xl border border-slate-200 shadow-sm">
              {/* Toolbar */}
              <div className="flex flex-col sm:flex-row sm:items-center gap-3 px-5 py-4 border-b border-slate-100">
                <div className="flex items-center gap-2 bg-slate-100 rounded-lg px-3 py-2 flex-1 max-w-sm">
                  <Icon name="Search" size={14} className="text-slate-400 flex-shrink-0"/>
                  <input
                    type="text"
                    placeholder="Cari NIP atau nama karyawan..."
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

                <select
                  value={filterStatus}
                  onChange={e => setFilterStatus(e.target.value)}
                  className="text-sm border border-slate-200 rounded-lg px-3 py-2 text-slate-700 bg-white outline-none focus:ring-2 focus:ring-brand-500 cursor-pointer"
                >
                  <option value="Semua">Semua Status</option>
                  <option value="Aktif">Aktif</option>
                  <option value="Nonaktif">Nonaktif</option>
                </select>

                <div className="text-xs text-slate-400 ml-auto whitespace-nowrap">
                  <span className="font-semibold text-slate-700">{filtered.length}</span> dari {rawKaryawan.length} pegawai
                </div>
              </div>

              {/* Jabatan Tabs */}
              <div className="flex items-center gap-1 px-5 py-2 border-b border-slate-100 overflow-x-auto">
                {listJabatan.map(jab => (
                  <button
                    key={jab}
                    onClick={() => setFilterJabatan(jab)}
                    className={`px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-colors
                      ${filterJabatan === jab ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100'}`}
                  >
                    {jab}
                  </button>
                ))}
              </div>

              {/* Table */}
              <div className="overflow-x-auto">
                <table className="w-full text-sm">
                  <thead>
                    <tr className="bg-slate-50 border-b border-slate-200">
                      <th className="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide w-10">#</th>
                      <th className="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">NIP</th>
                      <th className="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Nama Karyawan</th>
                      <th className="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Jabatan</th>
                      <th className="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                      <th className="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Aksi</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {filtered.length === 0 ? (
                      <tr>
                        <td colSpan="6" className="py-14 text-center text-slate-500">
                          <Icon name="SearchX" size={36} className="text-slate-300 mx-auto mb-2"/>
                          <p className="font-medium">Tidak ada data karyawan ditemukan.</p>
                        </td>
                      </tr>
                    ) : filtered.map((row, i) => (
                      <tr key={row.id_karyawan} className={`hover:bg-slate-50 transition-colors ${row.status !== 'Aktif' ? 'opacity-60' : ''}`}>
                        <td className="px-5 py-3.5 text-slate-400 text-xs">{i + 1}</td>
                        <td className="px-5 py-3.5">
                          <code className="font-mono text-xs font-semibold text-brand-600 bg-brand-50 px-2.5 py-1 rounded">
                            {row.nip}
                          </code>
                        </td>
                        <td className="px-5 py-3.5 font-medium text-slate-800">{row.nama_karyawan}</td>
                        <td className="px-5 py-3.5 text-slate-600">
                          <span className="px-2.5 py-1 bg-slate-100 rounded-md text-xs font-medium text-slate-700">
                            {row.jabatan || '-'}
                          </span>
                        </td>
                        <td className="px-5 py-3.5">
                          <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ${row.status === 'Aktif' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'}`}>
                            <span className={`w-1.5 h-1.5 rounded-full ${row.status === 'Aktif' ? 'bg-green-500' : 'bg-slate-400'}`}></span>
                            {row.status}
                          </span>
                        </td>
                        <td className="px-5 py-3.5">
                          <div className="flex items-center justify-center gap-1">
                            <a href={`/kas_keluar/lihat_karyawan/${row.id_karyawan}`} title="Detail" className="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                              <Icon name="Eye" size={15}/>
                            </a>
                            <a href={`/kas_keluar/edit_karyawan/${row.id_karyawan}`} title="Edit" className="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                              <Icon name="Pencil" size={15}/>
                            </a>
                            <button
                              onClick={() => setConfirmItem(row)}
                              title={row.status === 'Aktif' ? 'Nonaktifkan' : 'Aktifkan'}
                              className={`p-1.5 rounded-lg transition-colors ${row.status === 'Aktif' ? 'text-slate-400 hover:text-amber-600 hover:bg-amber-50' : 'text-slate-400 hover:text-green-600 hover:bg-green-50'}`}
                            >
                              <Icon name={row.status === 'Aktif' ? 'PowerOff' : 'Power'} size={15}/>
                            </button>
                            <button
                              onClick={() => setDeleteItem(row)}
                              title="Hapus Permanen"
                              className="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                            >
                              <Icon name="Trash2" size={15}/>
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>

              {filtered.length > 0 && (
                <div className="px-5 py-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                  <span>Menampilkan {filtered.length} karyawan</span>
                  <span>FinanceOS © 2026</span>
                </div>
              )}
            </div>
          </div>
        </main>
      </div>

      <ConfirmModal item={confirmItem} onCancel={() => setConfirmItem(null)} />
      <DeleteModal item={deleteItem} onCancel={() => setDeleteItem(null)} />
      {showFlash && <Toast flash={flash} onClose={() => setShowFlash(false)} />}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<KaryawanPage />);
</script>
</body>
</html>
