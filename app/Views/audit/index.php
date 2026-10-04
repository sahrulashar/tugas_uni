<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Audit Trail Log — FinanceOS</title>

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
  window.__LOGS__           = <?= json_encode($logs ?? []) ?>;
  window.__STATS__          = <?= json_encode($stats ?? []) ?>;
  window.__FILTER_OPTIONS__ = <?= json_encode($filterOptions ?? []) ?>;
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
      <Icon name={isSuccess ? 'CheckCircle' : 'AlertCircle'} size={18}/>
      <span>{flash.success || flash.error}</span>
      <button onClick={onClose} className="ml-2 opacity-70 hover:opacity-100">
        <Icon name="X" size={15}/>
      </button>
    </div>
  );
}

/* Detail Diff Modal */
function DiffModal({ log, onClose }) {
  if (!log) return null;

  let parsed = null;
  if (log.detail_perubahan) {
    try {
      parsed = JSON.parse(log.detail_perubahan);
    } catch (e) {
      parsed = null;
    }
  }

  const before = parsed?.before || null;
  const after  = parsed?.after  || null;

  // Kumpulkan all keys jika ada before atau after
  const allKeys = useMemo(() => {
    const keys = new Set();
    if (before && typeof before === 'object') Object.keys(before).forEach(k => keys.add(k));
    if (after && typeof after === 'object') Object.keys(after).forEach(k => keys.add(k));
    return Array.from(keys);
  }, [before, after]);

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm fade-in">
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden">
        {/* Modal Header */}
        <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-blue-50 text-brand-600 flex items-center justify-center flex-shrink-0">
              <Icon name="History" size={20}/>
            </div>
            <div>
              <h3 className="text-base font-bold text-slate-800 flex items-center gap-2">
                Detail Jejak Audit #{log.id}
                <span className={`px-2 py-0.5 rounded text-xs font-semibold ${
                  log.aksi === 'TAMBAH' ? 'bg-green-100 text-green-700' :
                  log.aksi === 'EDIT' ? 'bg-amber-100 text-amber-700' :
                  log.aksi === 'SOFT_DELETE' || log.aksi === 'HAPUS' ? 'bg-red-100 text-red-700' :
                  'bg-blue-100 text-blue-700'
                }`}>
                  {log.aksi}
                </span>
              </h3>
              <p className="text-xs text-slate-400 mt-0.5">
                {log.waktu} &bull; {log.nama_user || 'System'}
              </p>
            </div>
          </div>
          <button onClick={onClose} className="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
            <Icon name="X" size={18}/>
          </button>
        </div>

        {/* Modal Body */}
        <div className="p-6 overflow-y-auto space-y-5">
          {/* Metadata Grid */}
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-xl text-xs">
            <div>
              <span className="text-slate-400 block font-medium">Tabel Terdampak</span>
              <span className="font-mono font-semibold text-slate-800">{log.tabel_terdampak}</span>
            </div>
            <div>
              <span className="text-slate-400 block font-medium">Record ID</span>
              <span className="font-mono font-semibold text-slate-800">#{log.record_id}</span>
            </div>
            <div>
              <span className="text-slate-400 block font-medium">Modul</span>
              <span className="font-semibold text-slate-800">{log.modul || '-'}</span>
            </div>
            <div>
              <span className="text-slate-400 block font-medium">Pengguna</span>
              <span className="font-semibold text-slate-800">{log.nama_user} (ID: {log.user_id})</span>
            </div>
          </div>

          {log.keterangan && (
            <div>
              <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Keterangan Aktivitas</span>
              <p className="text-sm font-medium text-slate-800 bg-blue-50/50 p-3 rounded-lg border border-blue-100">
                {log.keterangan}
              </p>
            </div>
          )}

          {log.url && (
            <div>
              <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Endpoint URL</span>
              <code className="text-xs text-slate-600 bg-slate-100 px-3 py-1.5 rounded-lg block font-mono break-all">
                {log.url}
              </code>
            </div>
          )}

          {/* Diff Section */}
          <div>
            <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-2">Perubahan Nilai Data</span>
            {allKeys.length > 0 ? (
              <div className="border border-slate-200 rounded-xl overflow-hidden">
                <table className="w-full text-xs">
                  <thead>
                    <tr className="bg-slate-100 text-slate-600 font-semibold border-b border-slate-200">
                      <th className="px-4 py-2.5 text-left">Kolom / Field</th>
                      <th className="px-4 py-2.5 text-left text-red-700 bg-red-50/50">Nilai Sebelum (Before)</th>
                      <th className="px-4 py-2.5 text-left text-green-700 bg-green-50/50">Nilai Sesudah (After)</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100 font-mono">
                    {allKeys.map(key => {
                      const vBefore = before ? before[key] : undefined;
                      const vAfter  = after  ? after[key]  : undefined;
                      const isChanged = JSON.stringify(vBefore) !== JSON.stringify(vAfter);

                      return (
                        <tr key={key} className={isChanged ? 'bg-amber-50/20' : ''}>
                          <td className="px-4 py-2 font-semibold text-slate-700">{key}</td>
                          <td className="px-4 py-2 text-red-600 bg-red-50/20 break-all">
                            {vBefore !== undefined ? (typeof vBefore === 'object' ? JSON.stringify(vBefore) : String(vBefore)) : <span className="text-slate-300 italic">null</span>}
                          </td>
                          <td className="px-4 py-2 text-green-600 bg-green-50/20 break-all">
                            {vAfter !== undefined ? (typeof vAfter === 'object' ? JSON.stringify(vAfter) : String(vAfter)) : <span className="text-slate-300 italic">null</span>}
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            ) : (
              <div className="bg-slate-50 p-4 rounded-xl text-center text-xs text-slate-400">
                {log.detail_perubahan ? (
                  <pre className="text-left font-mono whitespace-pre-wrap text-slate-600 bg-slate-100 p-3 rounded-lg overflow-x-auto">
                    {JSON.stringify(parsed, null, 2)}
                  </pre>
                ) : (
                  'Tidak ada snapshot detail data untuk aktivitas ini.'
                )}
              </div>
            )}
          </div>
        </div>

        {/* Modal Footer */}
        <div className="px-6 py-3 border-t border-slate-100 bg-slate-50/50 flex justify-end">
          <button onClick={onClose} className="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition-colors shadow-sm">
            Tutup
          </button>
        </div>
      </div>
    </div>
  );
}

function AuditPage() {
  const [collapsed, setCollapsed] = useState(false);
  const [search, setSearch] = useState('');
  const [filterAksi, setFilterAksi] = useState('Semua');
  const [filterTabel, setFilterTabel] = useState('Semua');
  const [filterModul, setFilterModul] = useState('Semua');
  const [selectedLog, setSelectedLog] = useState(null);
  const [showFlash, setShowFlash] = useState(true);

  const logs          = window.__LOGS__ || [];
  const stats         = window.__STATS__ || {};
  const filterOptions = window.__FILTER_OPTIONS__ || {};
  const flash         = window.__FLASH__ || {};
  const currentPath   = '/46124026/audit';

  const aksiBadge = (aksi) => {
    switch (aksi) {
      case 'TAMBAH':
        return { bg: 'bg-green-100 text-green-700 border-green-200', dot: 'bg-green-500', icon: 'PlusCircle' };
      case 'EDIT':
        return { bg: 'bg-amber-100 text-amber-700 border-amber-200', dot: 'bg-amber-500', icon: 'Pencil' };
      case 'SOFT_DELETE':
      case 'HAPUS':
        return { bg: 'bg-red-100 text-red-700 border-red-200', dot: 'bg-red-500', icon: 'Trash2' };
      case 'NONAKTIF':
        return { bg: 'bg-slate-100 text-slate-600 border-slate-200', dot: 'bg-slate-400', icon: 'PowerOff' };
      case 'AKTIF':
        return { bg: 'bg-emerald-100 text-emerald-700 border-emerald-200', dot: 'bg-emerald-500', icon: 'Power' };
      case 'LOGIN':
        return { bg: 'bg-blue-100 text-blue-700 border-blue-200', dot: 'bg-blue-500', icon: 'LogIn' };
      case 'LOGOUT':
        return { bg: 'bg-purple-100 text-purple-700 border-purple-200', dot: 'bg-purple-500', icon: 'LogOut' };
      default:
        return { bg: 'bg-slate-100 text-slate-700 border-slate-200', dot: 'bg-slate-500', icon: 'Activity' };
    }
  };

  const filteredLogs = useMemo(() => {
    return logs.filter(log => {
      const q = search.toLowerCase();
      const matchSearch = search === '' ||
        (log.nama_user && log.nama_user.toLowerCase().includes(q)) ||
        (log.keterangan && log.keterangan.toLowerCase().includes(q)) ||
        (log.tabel_terdampak && log.tabel_terdampak.toLowerCase().includes(q)) ||
        (log.aksi && log.aksi.toLowerCase().includes(q)) ||
        (log.url && log.url.toLowerCase().includes(q));

      const matchAksi  = filterAksi === 'Semua' || log.aksi === filterAksi;
      const matchTabel = filterTabel === 'Semua' || log.tabel_terdampak === filterTabel;
      const matchModul = filterModul === 'Semua' || log.modul === filterModul;

      return matchSearch && matchAksi && matchTabel && matchModul;
    });
  }, [logs, search, filterAksi, filterTabel, filterModul]);

  return (
    <div className="min-h-screen bg-slate-100">
      <Sidebar collapsed={collapsed} currentPath={currentPath}/>

      <div className={`sidebar-transition ${collapsed ? 'ml-16' : 'ml-64'}`}>
        <header className={`fixed top-0 right-0 z-20 flex items-center justify-between h-16 bg-white border-b border-slate-200 px-4 shadow-sm sidebar-transition ${collapsed ? 'left-16' : 'left-64'}`}>
          <div className="flex items-center gap-3">
            <button onClick={() => setCollapsed(c => !c)} className="p-2 rounded-lg hover:bg-slate-100 text-slate-500">
              <Icon name="PanelLeft" size={18}/>
            </button>
            <div className="hidden sm:flex items-center gap-1.5 text-sm">
              <a href="/46124026" className="text-slate-500 hover:text-brand-600 transition-colors">Dashboard</a>
              <Icon name="ChevronRight" size={13} className="text-slate-400"/>
              <span className="text-slate-500">Pengaturan RBAC</span>
              <Icon name="ChevronRight" size={13} className="text-slate-400"/>
              <span className="font-semibold text-slate-800">Audit Trail Log</span>
            </div>
          </div>
          <div className="flex items-center gap-2">
            <button onClick={() => window.print()} className="flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
              <Icon name="Printer" size={15}/>
              <span className="hidden sm:inline">Cetak</span>
            </button>
          </div>
        </header>

        <main className="pt-16 min-h-screen">
          <div className="p-6 space-y-6 fade-in">

            {/* Page Header */}
            <div>
              <h1 className="text-xl font-bold text-slate-800 flex items-center gap-2">
                <Icon name="Activity" size={20} className="text-brand-600"/>
                Audit Trail Log
              </h1>
              <p className="text-sm text-slate-500 mt-0.5">
                Pantau seluruh rekam jejak aktivitas, perubahan data, dan keamanan transaksi sistem secara transparan
              </p>
            </div>

            {/* Stat Cards */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <div className="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-start gap-4">
                <div className="w-11 h-11 rounded-xl bg-blue-50 text-brand-600 flex items-center justify-center flex-shrink-0">
                  <Icon name="Database" size={20}/>
                </div>
                <div>
                  <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Log</p>
                  <p className="text-2xl font-bold text-slate-800 mt-0.5">{stats.total || logs.length} <span className="text-xs font-normal text-slate-400">rekaman</span></p>
                </div>
              </div>

              <div className="bg-white p-4 rounded-xl border border-green-100 shadow-sm flex items-start gap-4">
                <div className="w-11 h-11 rounded-xl bg-green-50 text-green-600 flex items-center justify-center flex-shrink-0">
                  <Icon name="CalendarDays" size={20}/>
                </div>
                <div>
                  <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide">Aktivitas Hari Ini</p>
                  <p className="text-2xl font-bold text-green-700 mt-0.5">{stats.hari_ini || 0} <span className="text-xs font-normal text-slate-400">aksi</span></p>
                </div>
              </div>

              <div className="bg-white p-4 rounded-xl border border-amber-100 shadow-sm flex items-start gap-4">
                <div className="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                  <Icon name="FileEdit" size={20}/>
                </div>
                <div>
                  <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide">Modifikasi Data</p>
                  <p className="text-2xl font-bold text-amber-700 mt-0.5">{stats.modifikasi || 0} <span className="text-xs font-normal text-slate-400">perubahan</span></p>
                </div>
              </div>

              <div className="bg-white p-4 rounded-xl border border-purple-100 shadow-sm flex items-start gap-4">
                <div className="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                  <Icon name="Users" size={20}/>
                </div>
                <div>
                  <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide">User Terlibat</p>
                  <p className="text-2xl font-bold text-purple-700 mt-0.5">{stats.total_user || 1} <span className="text-xs font-normal text-slate-400">operator</span></p>
                </div>
              </div>
            </div>

            {/* Filter & Table Area */}
            <div className="bg-white rounded-xl border border-slate-200 shadow-sm">
              <div className="flex flex-col sm:flex-row sm:items-center gap-3 px-5 py-4 border-b border-slate-100">
                {/* Search */}
                <div className="flex items-center gap-2 bg-slate-100 rounded-lg px-3 py-2 flex-1 max-w-sm">
                  <Icon name="Search" size={14} className="text-slate-400 flex-shrink-0"/>
                  <input
                    type="text"
                    placeholder="Cari user, keterangan, tabel, url..."
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

                {/* Filter Aksi */}
                <select
                  value={filterAksi}
                  onChange={e => setFilterAksi(e.target.value)}
                  className="text-sm border border-slate-200 rounded-lg px-3 py-2 text-slate-700 bg-white outline-none focus:ring-2 focus:ring-brand-500 cursor-pointer"
                >
                  <option value="Semua">Semua Aksi</option>
                  <option value="TAMBAH">TAMBAH</option>
                  <option value="EDIT">EDIT</option>
                  <option value="HAPUS">HAPUS</option>
                  <option value="SOFT_DELETE">SOFT_DELETE</option>
                  <option value="NONAKTIF">NONAKTIF</option>
                  <option value="AKTIF">AKTIF</option>
                  <option value="LOGIN">LOGIN</option>
                  <option value="LOGOUT">LOGOUT</option>
                </select>

                {/* Filter Tabel */}
                <select
                  value={filterTabel}
                  onChange={e => setFilterTabel(e.target.value)}
                  className="text-sm border border-slate-200 rounded-lg px-3 py-2 text-slate-700 bg-white outline-none focus:ring-2 focus:ring-brand-500 cursor-pointer"
                >
                  <option value="Semua">Semua Tabel</option>
                  {(filterOptions.tabel || []).map(t => (
                    <option key={t} value={t}>{t}</option>
                  ))}
                </select>

                {/* Filter Modul */}
                <select
                  value={filterModul}
                  onChange={e => setFilterModul(e.target.value)}
                  className="text-sm border border-slate-200 rounded-lg px-3 py-2 text-slate-700 bg-white outline-none focus:ring-2 focus:ring-brand-500 cursor-pointer"
                >
                  <option value="Semua">Semua Modul</option>
                  {(filterOptions.modul || []).map(m => (
                    <option key={m} value={m}>{m}</option>
                  ))}
                </select>

                <div className="text-xs text-slate-400 ml-auto whitespace-nowrap">
                  <span className="font-semibold text-slate-700">{filteredLogs.length}</span> dari {logs.length} entri
                </div>
              </div>

              {/* Table */}
              <div className="overflow-x-auto">
                <table className="w-full text-sm">
                  <thead>
                    <tr className="bg-slate-50 border-b border-slate-200 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                      <th className="px-5 py-3 w-10">#</th>
                      <th className="px-5 py-3 w-40">Waktu</th>
                      <th className="px-5 py-3 w-44">Pengguna</th>
                      <th className="px-5 py-3 w-32">Aksi</th>
                      <th className="px-5 py-3 w-36">Modul & Tabel</th>
                      <th className="px-5 py-3">Keterangan / Endpoint</th>
                      <th className="px-5 py-3 text-center w-24">Snapshot</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {filteredLogs.length === 0 ? (
                      <tr>
                        <td colSpan="7" className="py-14 text-center text-slate-500">
                          <Icon name="SearchX" size={36} className="text-slate-300 mx-auto mb-2"/>
                          <p className="font-medium">Tidak ada rekaman jejak audit yang sesuai kriteria.</p>
                        </td>
                      </tr>
                    ) : (
                      filteredLogs.map((row, i) => {
                        const badge = aksiBadge(row.aksi);
                        return (
                          <tr key={row.id} className="hover:bg-slate-50 transition-colors">
                            <td className="px-5 py-3.5 text-slate-400 text-xs font-mono">{i + 1}</td>
                            <td className="px-5 py-3.5 whitespace-nowrap text-xs text-slate-600">
                              <span className="font-medium text-slate-800 block">
                                {row.waktu.split(' ')[0]}
                              </span>
                              <span className="font-mono text-slate-400">
                                {row.waktu.split(' ')[1] || ''}
                              </span>
                            </td>
                            <td className="px-5 py-3.5">
                              <div className="flex items-center gap-2">
                                <div className="w-7 h-7 rounded-full bg-brand-50 text-brand-700 font-bold text-xs flex items-center justify-center border border-brand-200 flex-shrink-0">
                                  {(row.nama_user || 'U').charAt(0).toUpperCase()}
                                </div>
                                <div className="min-w-0">
                                  <p className="font-semibold text-slate-800 text-xs truncate">{row.nama_user || 'System'}</p>
                                  <p className="text-[10px] text-slate-400 font-mono">ID: {row.user_id}</p>
                                </div>
                              </div>
                            </td>
                            <td className="px-5 py-3.5">
                              <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border ${badge.bg}`}>
                                <span className={`w-1.5 h-1.5 rounded-full ${badge.dot}`}></span>
                                {row.aksi}
                              </span>
                            </td>
                            <td className="px-5 py-3.5">
                              <div>
                                <span className="font-mono text-xs font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded border border-brand-100">
                                  {row.tabel_terdampak} #{row.record_id}
                                </span>
                                {row.modul && (
                                  <span className="block text-[11px] text-slate-400 mt-0.5 font-medium">
                                    Modul: {row.modul}
                                  </span>
                                )}
                              </div>
                            </td>
                            <td className="px-5 py-3.5 max-w-md">
                              <p className="text-xs text-slate-800 font-medium truncate" title={row.keterangan || '-'}>
                                {row.keterangan || '-'}
                              </p>
                              {row.url && (
                                <p className="text-[10px] text-slate-400 font-mono truncate mt-0.5" title={row.url}>
                                  {row.url}
                                </p>
                              )}
                            </td>
                            <td className="px-5 py-3.5 text-center">
                              {row.detail_perubahan ? (
                                <button
                                  onClick={() => setSelectedLog(row)}
                                  className="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-brand-700 bg-brand-50 border border-brand-200 hover:bg-brand-100 rounded-lg transition-colors"
                                  title="Lihat Perubahan Nilai Data"
                                >
                                  <Icon name="Eye" size={13}/>
                                  Detail
                                </button>
                              ) : (
                                <span className="text-slate-300 text-xs italic">-</span>
                              )}
                            </td>
                          </tr>
                        );
                      })
                    )}
                  </tbody>
                </table>
              </div>

              {filteredLogs.length > 0 && (
                <div className="px-5 py-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                  <span>Menampilkan {filteredLogs.length} rekaman audit</span>
                  <span>FinanceOS Enterprise Suite &bull; Audit Trail</span>
                </div>
              )}
            </div>

          </div>
        </main>
      </div>

      {selectedLog && (
        <DiffModal log={selectedLog} onClose={() => setSelectedLog(null)}/>
      )}

      {showFlash && <Toast flash={flash} onClose={() => setShowFlash(false)}/>}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<AuditPage/>);
</script>
</body>
</html>
