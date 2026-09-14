<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Detail Rencana Beli — FinanceOS</title>

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
    @media print {
      aside, header { display: none !important; }
      .sidebar-transition { margin-left: 0 !important; }
      main { padding-top: 0 !important; }
    }
  </style>
</head>
<body class="h-full bg-slate-100 text-slate-800 antialiased">

<script>
  window.__RBELI__  = <?= json_encode($rbeli  ?? []) ?>;
  window.__DETAIL__ = <?= json_encode($detail ?? []) ?>;
  window.__TOTAL__  = <?= json_encode($total  ?? 0) ?>;
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
  { label:'Aktivitas',  icon:'ClipboardList', children:[
    { label:'Rencana Beli',     icon:'ShoppingCart', href:'/aktivitas/aktivitas1' },
    { label:'Bukti Kas Keluar', icon:'Receipt',      href:'/aktivitas/aktivitas2' },
    { label:'Rekap BKK',        icon:'ClipboardCheck', href:'/aktivitas/aktivitas3' },
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

  if (!hasChildren) return (
    <li>
      <a href={item.href}
        className={`flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors
          ${currentPath === item.href ? 'text-white bg-brand-600' : 'text-slate-400 hover:text-white hover:bg-white/5'}`}>
        <Icon name={item.icon} size={16}/>{item.label}
      </a>
    </li>
  );

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
            <li key={c.label}>
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
              <a href={item.href || '#'} className="flex items-center justify-center w-full py-3 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors">
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

function LihatPage() {
  const rbeli  = window.__RBELI__  || {};
  const detail = window.__DETAIL__ || [];
  const total  = window.__TOTAL__  || 0;
  const [collapsed, setCollapsed] = useState(false);

  const tglFormatted = rbeli.tgl
    ? new Date(rbeli.tgl).toLocaleDateString('id-ID', { weekday:'long', day:'2-digit', month:'long', year:'numeric' })
    : '-';

  return (
    <div className="min-h-screen bg-slate-100">
      <Sidebar collapsed={collapsed} currentPath="/aktivitas/aktivitas1"/>

      <div className={`sidebar-transition ${collapsed ? 'ml-16' : 'ml-64'}`}>
        <header className={`fixed top-0 right-0 z-20 flex items-center justify-between h-16 bg-white border-b border-slate-200 px-4 shadow-sm sidebar-transition ${collapsed ? 'left-16' : 'left-64'}`}>
          <div className="flex items-center gap-3">
            <button onClick={() => setCollapsed(c => !c)} className="p-2 rounded-lg hover:bg-slate-100 text-slate-500">
              <Icon name="PanelLeft" size={18}/>
            </button>
            <div className="hidden sm:flex items-center gap-1.5 text-sm">
              <a href="/" className="text-slate-500 hover:text-brand-600">Home</a>
              <Icon name="ChevronRight" size={13} className="text-slate-400"/>
              <a href="/aktivitas/aktivitas1" className="text-slate-500 hover:text-brand-600">Rencana Beli</a>
              <Icon name="ChevronRight" size={13} className="text-slate-400"/>
              <span className="font-semibold text-slate-800">Detail</span>
            </div>
          </div>
          <div className="flex items-center gap-2">
            <button onClick={() => window.print()}
              className="flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
              <Icon name="Printer" size={15}/>
              <span className="hidden sm:inline">Cetak</span>
            </button>
            <a href={`/aktivitas/aktivitas1/edit/${rbeli.id}`}
              className="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg shadow-sm transition-colors">
              <Icon name="Pencil" size={15}/> Edit
            </a>
          </div>
        </header>

        <main className="pt-16 min-h-screen">
          <div className="p-6 max-w-4xl mx-auto space-y-6 fade-in">

            {/* Back + Title */}
            <div className="flex items-center gap-3">
              <a href="/aktivitas/aktivitas1" className="p-2 bg-white rounded-lg border border-slate-200 hover:bg-slate-50 shadow-sm transition-colors">
                <Icon name="ArrowLeft" size={16}/>
              </a>
              <div>
                <h1 className="text-xl font-bold text-slate-800">Detail Rencana Beli</h1>
                <p className="text-sm text-slate-500">Informasi lengkap dokumen beserta daftar faktur</p>
              </div>
            </div>

            {/* Header Card */}
            <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
              <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <Icon name="FileText" size={16} className="text-brand-600"/>
                  <h2 className="font-semibold text-slate-800 text-sm">Informasi Header</h2>
                </div>
                <code className="font-mono text-sm font-bold text-brand-600 bg-brand-50 px-3 py-1 rounded-lg">
                  {rbeli.no_rbeli}
                </code>
              </div>
              <div className="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                  <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Tanggal</p>
                  <p className="text-sm font-medium text-slate-800 flex items-center gap-2">
                    <Icon name="Calendar" size={14} className="text-slate-400"/>
                    {tglFormatted}
                  </p>
                </div>
                <div>
                  <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Supplier</p>
                  <p className="text-sm font-medium text-slate-800 flex items-center gap-2">
                    <Icon name="Truck" size={14} className="text-slate-400"/>
                    {rbeli.nama_supplier || '-'}
                  </p>
                </div>
                <div className="sm:col-span-2">
                  <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Keterangan</p>
                  <p className="text-sm text-slate-700 bg-slate-50 px-4 py-3 rounded-xl border border-slate-100">
                    {rbeli.kete || <span className="text-slate-400 italic">Tidak ada keterangan</span>}
                  </p>
                </div>
              </div>
            </div>

            {/* Detail Faktur */}
            <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
              <div className="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                <Icon name="List" size={16} className="text-brand-600"/>
                <h2 className="font-semibold text-slate-800 text-sm">Detail Faktur</h2>
                <span className="ml-1 bg-brand-100 text-brand-700 text-xs font-semibold px-2 py-0.5 rounded-full">{detail.length} faktur</span>
              </div>

              <div className="overflow-x-auto">
                <table className="w-full text-sm">
                  <thead>
                    <tr className="bg-slate-50 border-b border-slate-200">
                      <th className="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide w-10">#</th>
                      <th className="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">No. Faktur</th>
                      <th className="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Nilai</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {detail.length === 0 ? (
                      <tr>
                        <td colSpan="3" className="py-10 text-center text-slate-400 text-sm">
                          <Icon name="FileX" size={28} className="text-slate-300 mx-auto mb-2"/>
                          Belum ada detail faktur.
                        </td>
                      </tr>
                    ) : detail.map((row, i) => (
                      <tr key={row.id} className="hover:bg-slate-50 transition-colors">
                        <td className="px-5 py-3.5 text-slate-400 text-xs">{i + 1}</td>
                        <td className="px-5 py-3.5">
                          <code className="font-mono text-xs font-semibold text-slate-700 bg-slate-100 px-2.5 py-1 rounded">
                            {row.no_faktur}
                          </code>
                        </td>
                        <td className="px-5 py-3.5 text-right font-semibold text-slate-800">
                          Rp {Number(row.nilai).toLocaleString('id-ID')}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                  <tfoot>
                    <tr className="bg-brand-50 border-t-2 border-brand-100">
                      <td colSpan="2" className="px-5 py-4 text-right text-sm font-semibold text-brand-700">
                        Total Nilai
                      </td>
                      <td className="px-5 py-4 text-right text-lg font-bold text-brand-700">
                        Rp {Number(total).toLocaleString('id-ID')}
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>

            {/* Action Footer */}
            <div className="flex items-center justify-between">
              <a href="/aktivitas/aktivitas1"
                className="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl shadow-sm transition-colors">
                <Icon name="ArrowLeft" size={15}/> Kembali ke Daftar
              </a>
              <div className="flex items-center gap-2">
                <a href={`/aktivitas/aktivitas1/edit/${rbeli.id}`}
                  className="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-xl transition-colors">
                  <Icon name="Pencil" size={15}/> Edit
                </a>
              </div>
            </div>

          </div>
        </main>
      </div>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<LihatPage/>);
</script>
</body>
</html>
