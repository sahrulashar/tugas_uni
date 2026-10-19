<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Neraca Saldo (Trial Balance) — FinanceOS</title>

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
      aside, header, .no-print { display: none !important; }
      .sidebar-transition { margin-left: 0 !important; }
      body { background: white !important; }
    }
  </style>
</head>
<body class="h-full bg-slate-100 text-slate-800 antialiased">

<script>
  window.__NERACA__ = <?= json_encode($neraca ?? []) ?>;
  window.__FLASH__  = {
    success: "<?= addslashes(session()->getFlashdata('success') ?? '') ?>",
    error:   "<?= addslashes(session()->getFlashdata('error')   ?? '') ?>"
  };
  window.__CSRF__   = {
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
  { label:'Dashboard', icon:'LayoutDashboard', href:'/' },
  { label:'Kas Keluar', icon:'ArrowUpFromLine', children:[
    { label:'Chart of Accounts', icon:'BookOpen', href:'/46124026/kas_keluar/coa_l1H' },
    { label:'Supplier',          icon:'Truck',    href:'/46124026/kas_keluar/supplier_l1H' },
    { label:'Karyawan',          icon:'Users',    href:'/46124026/kas_keluar/karyawan_l1H' },
  ]},
  { label:'Kas Masuk & Penjualan', icon:'ArrowDownToLine', children:[
    { label:'Kasir Penjualan (POS)', icon:'ShoppingCart', href:'/46124026/penjualan/kasir_l1H' },
    { label:'Master Barang & Stok',  icon:'Package',      href:'/46124026/penjualan/barang_l1H' },
  ]},
  { label:'Aktivitas Kas Keluar', icon:'ClipboardList', children:[
    { label:'Rencana Beli',      icon:'ShoppingCart', href:'/46124026/aktivitas/aktivitas1' },
    { label:'Bukti Kas Keluar',  icon:'Receipt',      href:'/46124026/aktivitas/aktivitas2' },
    { label:'Rekap BKK',         icon:'ClipboardCheck', href:'/46124026/aktivitas/aktivitas3' },
  ]},
  { label:'Akuntansi & Laporan', icon:'BarChart3', children:[
    { label:'Jurnal Umum (Automated)', icon:'BookMarked',   href:'/46124026/akuntansi/jurnal_l1H' },
    { label:'Neraca Saldo',            icon:'Scale',        href:'/46124026/akuntansi/neraca_saldo_l1H' },
    { label:'Praktikum Pertemuan 9',   icon:'CheckCircle2', href:'/46124026/akuntansi/praktikum_l1H' },
  ]},
  { label:'Pengaturan RBAC', icon:'Shield', children:[
    { label:'Manajemen User',  icon:'Users',    href:'/46124026/rbac/user' },
    { label:'Laman & Aksi',    icon:'FileText', href:'/46124026/rbac/laman' },
    { label:'Atur Hak Akses',  icon:'Lock',     href:'/46124026/rbac/akses' },
  ]},
  { label:'Audit Trail Log', icon:'History', href:'/46124026/audit' },
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
        <div className="flex-shrink-0 w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center text-white">
          <Icon name="Landmark" size={16}/>
        </div>
        {!collapsed && (
          <div className="fade-in">
            <p className="text-white font-bold text-sm leading-tight">FinanceOS</p>
            <p className="text-slate-500 text-xs">Pertemuan 9 — Integration</p>
          </div>
        )}
      </div>
      <nav className="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        <ul>{NAV.map(item => <NavItem key={item.label} item={item} currentPath={currentPath}/>)}</ul>
      </nav>
      <div className="p-3 border-t border-slate-800">
        <a href="/logout" className="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors">
          <Icon name="LogOut" size={16}/>
          {!collapsed && <span>Logout</span>}
        </a>
      </div>
    </aside>
  );
}

function NeracaSaldoApp() {
  const [collapsed, setCollapsed] = useState(false);
  const [neraca, setNeraca] = useState(window.__NERACA__ || {});
  const [search, setSearch] = useState('');

  const baris = neraca.baris || [];
  const isBalance = neraca.is_balance ?? true;
  const selisih = neraca.selisih ?? 0;

  const filtered = useMemo(() => {
    return baris.filter(b =>
      b.kode_coa.toLowerCase().includes(search.toLowerCase()) ||
      b.nama_coa.toLowerCase().includes(search.toLowerCase())
    );
  }, [baris, search]);

  return (
    <div className="flex h-screen overflow-hidden bg-slate-100">
      <Sidebar collapsed={collapsed} currentPath="/46124026/akuntansi/neraca_saldo_l1H"/>

      <div className={`flex-1 flex flex-col min-w-0 overflow-hidden sidebar-transition ${collapsed ? 'ml-16' : 'ml-64'}`}>
        {/* Topbar */}
        <header className="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between flex-shrink-0 z-20">
          <div className="flex items-center gap-3">
            <button onClick={() => setCollapsed(!collapsed)} className="p-2 text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-100 no-print">
              <Icon name="Menu" size={18}/>
            </button>
            <div className="flex items-center gap-2 text-sm text-slate-500">
              <a href="/" className="hover:text-brand-600">Home</a>
              <span>/</span>
              <span>Laporan Akuntansi</span>
              <span>/</span>
              <span className="text-slate-800 font-semibold">Neraca Saldo (Trial Balance)</span>
            </div>
          </div>

          <div className="flex items-center gap-3 no-print">
            <button
              onClick={() => window.print()}
              className="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors"
            >
              <Icon name="Printer" size={14}/> Cetak Laporan
            </button>
            <a
              href="/46124026/akuntansi/praktikum_l1H"
              className="px-3.5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-sm"
            >
              <Icon name="CheckCircle2" size={14}/> Uji Praktikum P9
            </a>
            <div className="w-8 h-8 rounded-full bg-brand-600 text-white font-bold flex items-center justify-center text-xs">
              {window._erpUser?.initial || 'A'}
            </div>
          </div>
        </header>

        {/* Content Area */}
        <main className="flex-1 overflow-y-auto p-6 space-y-6">
          {/* Status Header Alert */}
          <div className={`p-4 rounded-2xl border flex flex-col sm:flex-row items-center justify-between gap-4 fade-in
            ${isBalance ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-red-50 border-red-200 text-red-900'}`}>
            <div className="flex items-center gap-3.5">
              <div className={`w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 text-white
                ${isBalance ? 'bg-emerald-600' : 'bg-red-600'}`}>
                <Icon name={isBalance ? "CheckCircle2" : "AlertTriangle"} size={26}/>
              </div>
              <div>
                <h2 className="font-bold text-base">
                  {isBalance ? 'Neraca Saldo Sempurna & Seimbang (Balanced)' : 'Peringatan: Neraca Saldo Tidak Seimbang!'}
                </h2>
                <p className="text-xs opacity-90 mt-0.5">
                  {isBalance
                    ? 'Total Saldo Akhir Debit tepat sama dengan Total Saldo Akhir Kredit. Aliran data penjualan hingga pembukuan valid 100%.'
                    : `Terdapat selisih antara Debit dan Kredit sebesar Rp ${selisih.toLocaleString('id-ID')}. Periksa mutasi jurnal.`}
                </p>
              </div>
            </div>
            <div className="text-right">
              <span className={`px-4 py-1.5 rounded-full text-xs font-black tracking-wider uppercase
                ${isBalance ? 'bg-emerald-200/80 text-emerald-900' : 'bg-red-200/80 text-red-900'}`}>
                {isBalance ? 'DEBIT = KREDIT' : `SELISIH: Rp ${selisih.toLocaleString('id-ID')}`}
              </span>
            </div>
          </div>

          {/* Stat Summary Cards */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4 no-print">
            <div className="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
              <p className="text-xs text-slate-500 font-medium">Total Saldo Awal</p>
              <div className="mt-2 flex items-baseline justify-between">
                <div>
                  <span className="text-[10px] text-slate-400 font-bold block">DEBIT</span>
                  <p className="text-sm font-bold text-slate-800">Rp {(neraca.tot_awal_debit || 0).toLocaleString('id-ID')}</p>
                </div>
                <div className="text-right">
                  <span className="text-[10px] text-slate-400 font-bold block">KREDIT</span>
                  <p className="text-sm font-bold text-slate-800">Rp {(neraca.tot_awal_kredit || 0).toLocaleString('id-ID')}</p>
                </div>
              </div>
            </div>

            <div className="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
              <p className="text-xs text-slate-500 font-medium">Total Mutasi Jurnal</p>
              <div className="mt-2 flex items-baseline justify-between">
                <div>
                  <span className="text-[10px] text-indigo-500 font-bold block">MUTASI DEBIT</span>
                  <p className="text-sm font-bold text-indigo-700">Rp {(neraca.tot_mutasi_debit || 0).toLocaleString('id-ID')}</p>
                </div>
                <div className="text-right">
                  <span className="text-[10px] text-indigo-500 font-bold block">MUTASI KREDIT</span>
                  <p className="text-sm font-bold text-indigo-700">Rp {(neraca.tot_mutasi_kredit || 0).toLocaleString('id-ID')}</p>
                </div>
              </div>
            </div>

            <div className="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
              <p className="text-xs text-slate-500 font-medium">Total Saldo Akhir (Trial Balance)</p>
              <div className="mt-2 flex items-baseline justify-between">
                <div>
                  <span className="text-[10px] text-emerald-600 font-bold block">AKHIR DEBIT</span>
                  <p className="text-sm font-extrabold text-emerald-700">Rp {(neraca.tot_akhir_debit || 0).toLocaleString('id-ID')}</p>
                </div>
                <div className="text-right">
                  <span className="text-[10px] text-emerald-600 font-bold block">AKHIR KREDIT</span>
                  <p className="text-sm font-extrabold text-emerald-700">Rp {(neraca.tot_akhir_kredit || 0).toLocaleString('id-ID')}</p>
                </div>
              </div>
            </div>
          </div>

          {/* Table Container */}
          <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div className="p-4 border-b border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 no-print">
              <div className="relative w-full sm:w-80">
                <Icon name="Search" size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"/>
                <input
                  type="text"
                  value={search}
                  onChange={e => setSearch(e.target.value)}
                  placeholder="Cari akun..."
                  className="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500"
                />
              </div>
              <p className="text-xs text-slate-500">Agregasi dari tabel <b>coa</b> & mutasi <b>jurnal_umum_d</b></p>
            </div>

            <div className="overflow-x-auto">
              <table className="w-full text-xs text-left border-collapse">
                <thead>
                  <tr className="bg-slate-100/80 text-slate-700 font-bold border-b border-slate-200">
                    <th rowSpan="2" className="py-3 px-4 border-r border-slate-200 w-24">Kode Akun</th>
                    <th rowSpan="2" className="py-3 px-4 border-r border-slate-200">Nama Akun</th>
                    <th rowSpan="2" className="py-3 px-3 border-r border-slate-200 text-center w-20">Normal</th>
                    <th colSpan="2" className="py-2 px-4 border-r border-slate-200 text-center bg-slate-50">Saldo Awal</th>
                    <th colSpan="2" className="py-2 px-4 border-r border-slate-200 text-center bg-slate-100">Mutasi Jurnal</th>
                    <th colSpan="2" className="py-2 px-4 text-center bg-slate-50">Saldo Akhir</th>
                  </tr>
                  <tr className="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 text-right">
                    <th className="py-1.5 px-3 border-r border-slate-200">Debit</th>
                    <th className="py-1.5 px-3 border-r border-slate-200">Kredit</th>
                    <th className="py-1.5 px-3 border-r border-slate-200">Debit</th>
                    <th className="py-1.5 px-3 border-r border-slate-200">Kredit</th>
                    <th className="py-1.5 px-3 border-r border-slate-200">Debit</th>
                    <th className="py-1.5 px-3">Kredit</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 font-mono">
                  {filtered.length === 0 ? (
                    <tr>
                      <td colSpan="9" className="py-8 text-center text-slate-400 font-sans">Data akun tidak ditemukan</td>
                    </tr>
                  ) : (
                    filtered.map((b, idx) => (
                      <tr key={idx} className="hover:bg-slate-50/70">
                        <td className="py-2 px-4 font-bold text-brand-700 border-r border-slate-100">{b.kode_coa}</td>
                        <td className="py-2 px-4 font-sans font-medium text-slate-800 border-r border-slate-100">{b.nama_coa}</td>
                        <td className="py-2 px-3 text-center border-r border-slate-100 font-sans">
                          <span className={`px-1.5 py-0.5 rounded text-[10px] font-bold ${b.saldo_normal === 'Debit' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700'}`}>
                            {b.saldo_normal}
                          </span>
                        </td>
                        <td className="py-2 px-3 text-right text-slate-600 border-r border-slate-100">
                          {b.awal_debit > 0 ? Number(b.awal_debit).toLocaleString('id-ID') : '-'}
                        </td>
                        <td className="py-2 px-3 text-right text-slate-600 border-r border-slate-100">
                          {b.awal_kredit > 0 ? Number(b.awal_kredit).toLocaleString('id-ID') : '-'}
                        </td>
                        <td className="py-2 px-3 text-right text-indigo-700 border-r border-slate-100 font-semibold">
                          {b.mutasi_debit > 0 ? Number(b.mutasi_debit).toLocaleString('id-ID') : '-'}
                        </td>
                        <td className="py-2 px-3 text-right text-indigo-700 border-r border-slate-100 font-semibold">
                          {b.mutasi_kredit > 0 ? Number(b.mutasi_kredit).toLocaleString('id-ID') : '-'}
                        </td>
                        <td className="py-2 px-3 text-right font-bold text-emerald-800 border-r border-slate-100 bg-emerald-50/30">
                          {b.akhir_debit > 0 ? Number(b.akhir_debit).toLocaleString('id-ID') : '-'}
                        </td>
                        <td className="py-2 px-3 text-right font-bold text-emerald-800 bg-emerald-50/30">
                          {b.akhir_kredit > 0 ? Number(b.akhir_kredit).toLocaleString('id-ID') : '-'}
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
                <tfoot className="bg-slate-100 font-mono font-bold text-slate-900 border-t-2 border-slate-300">
                  <tr className="text-right">
                    <td colSpan="3" className="py-3 px-4 text-center font-sans font-extrabold uppercase tracking-wide border-r border-slate-300">
                      Total Neraca Saldo
                    </td>
                    <td className="py-3 px-3 border-r border-slate-300">
                      Rp {(neraca.tot_awal_debit || 0).toLocaleString('id-ID')}
                    </td>
                    <td className="py-3 px-3 border-r border-slate-300">
                      Rp {(neraca.tot_awal_kredit || 0).toLocaleString('id-ID')}
                    </td>
                    <td className="py-3 px-3 border-r border-slate-300 text-indigo-900">
                      Rp {(neraca.tot_mutasi_debit || 0).toLocaleString('id-ID')}
                    </td>
                    <td className="py-3 px-3 border-r border-slate-300 text-indigo-900">
                      Rp {(neraca.tot_mutasi_kredit || 0).toLocaleString('id-ID')}
                    </td>
                    <td className="py-3 px-3 border-r border-slate-300 text-emerald-900 bg-emerald-100/50">
                      Rp {(neraca.tot_akhir_debit || 0).toLocaleString('id-ID')}
                    </td>
                    <td className="py-3 px-3 text-emerald-900 bg-emerald-100/50">
                      Rp {(neraca.tot_akhir_kredit || 0).toLocaleString('id-ID')}
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </main>
      </div>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<NeracaSaldoApp />);
</script>
</body>
</html>
