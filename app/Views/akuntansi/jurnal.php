<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Buku Jurnal Umum — FinanceOS</title>

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
  window.__JURNAL__ = <?= json_encode($jurnalList ?? []) ?>;
  window.__TOTAL_DEBIT__  = <?= (float) ($totalDebit ?? 0) ?>;
  window.__TOTAL_KREDIT__ = <?= (float) ($totalKredit ?? 0) ?>;
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
      { label:'Executive Dashboard',      icon:'PieChart',     href:'/46124026/dashboard' },
      { label:'Praktikum Pertemuan 10',   icon:'Sparkles',     href:'/46124026/dashboard/praktikum_l1H' },
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

function JurnalApp() {
  const [collapsed, setCollapsed] = useState(false);
  const [jurnalList, setJurnalList] = useState(window.__JURNAL__ || []);
  const [search, setSearch] = useState('');

  const totDebit  = window.__TOTAL_DEBIT__ || 0;
  const totKredit = window.__TOTAL_KREDIT__ || 0;
  const isBalance = Math.abs(totDebit - totKredit) < 0.01;

  const filtered = useMemo(() => {
    return jurnalList.filter(j =>
      j.no_jurnal.toLowerCase().includes(search.toLowerCase()) ||
      j.no_referensi.toLowerCase().includes(search.toLowerCase()) ||
      (j.keterangan && j.keterangan.toLowerCase().includes(search.toLowerCase()))
    );
  }, [jurnalList, search]);

  return (
    <div className="flex h-screen overflow-hidden bg-slate-100">
      <Sidebar collapsed={collapsed} currentPath="/46124026/akuntansi/jurnal_l1H"/>

      <div className={`flex-1 flex flex-col min-w-0 overflow-hidden sidebar-transition ${collapsed ? 'ml-16' : 'ml-64'}`}>
        {/* Topbar */}
        <header className="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between flex-shrink-0 z-20">
          <div className="flex items-center gap-3">
            <button onClick={() => setCollapsed(!collapsed)} className="p-2 text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-100">
              <Icon name="Menu" size={18}/>
            </button>
            <div className="flex items-center gap-2 text-sm text-slate-500">
              <a href="/" className="hover:text-brand-600">Home</a>
              <span>/</span>
              <span>Akuntansi</span>
              <span>/</span>
              <span className="text-slate-800 font-semibold">Jurnal Umum (Automated)</span>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <a
              href="/46124026/akuntansi/neraca_saldo_l1H"
              className="px-3.5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-sm"
            >
              <Icon name="Scale" size={14}/> Rekap Neraca Saldo
            </a>
            <div className="w-8 h-8 rounded-full bg-brand-600 text-white font-bold flex items-center justify-center text-xs">
              {window._erpUser?.initial || 'A'}
            </div>
          </div>
        </header>

        {/* Content Area */}
        <main className="flex-1 overflow-y-auto p-6 space-y-6">
          {/* Flash Messages */}
          {window.__FLASH__?.success && (
            <div className="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3 fade-in">
              <Icon name="CheckCircle2" size={20} className="text-emerald-600 flex-shrink-0"/>
              <div>{window.__FLASH__.success}</div>
            </div>
          )}

          {/* Stat Cards */}
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div className="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <Icon name="BookMarked" size={20}/>
              </div>
              <div>
                <p className="text-xs text-slate-500 font-medium">Total Voucher Jurnal</p>
                <h3 className="text-lg font-bold text-slate-800">{jurnalList.length} Entri</h3>
              </div>
            </div>

            <div className="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <Icon name="ArrowDownLeft" size={20}/>
              </div>
              <div>
                <p className="text-xs text-slate-500 font-medium">Total Mutasi Debit</p>
                <h3 className="text-lg font-bold text-slate-800">Rp {totDebit.toLocaleString('id-ID')}</h3>
              </div>
            </div>

            <div className="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <Icon name="ArrowUpRight" size={20}/>
              </div>
              <div>
                <p className="text-xs text-slate-500 font-medium">Total Mutasi Kredit</p>
                <h3 className="text-lg font-bold text-slate-800">Rp {totKredit.toLocaleString('id-ID')}</h3>
              </div>
            </div>

            <div className="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center gap-3">
              <div className={`w-10 h-10 rounded-xl flex items-center justify-center ${isBalance ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600'}`}>
                <Icon name={isBalance ? "ShieldCheck" : "AlertTriangle"} size={20}/>
              </div>
              <div>
                <p className="text-xs text-slate-500 font-medium">Integritas Double-Entry</p>
                <h3 className={`text-sm font-bold ${isBalance ? 'text-emerald-700' : 'text-red-700'}`}>
                  {isBalance ? '100% Balanced' : 'Tidak Seimbang!'}
                </h3>
              </div>
            </div>
          </div>

          {/* Filter Bar */}
          <div className="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
            <div className="relative w-full sm:w-80">
              <Icon name="Search" size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"/>
              <input
                type="text"
                value={search}
                onChange={e => setSearch(e.target.value)}
                placeholder="Cari no. jurnal, no. referensi, keterangan..."
                className="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500"
              />
            </div>
            <div className="text-xs text-slate-500 flex items-center gap-2">
              <span className="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
              Semua ayat jurnal digenerate otomatis oleh <b>JurnalService</b>
            </div>
          </div>

          {/* Daftar Voucher Jurnal */}
          <div className="space-y-4">
            {filtered.length === 0 ? (
              <div className="bg-white rounded-2xl p-12 text-center text-slate-400 border border-slate-200">
                <Icon name="BookOpen" size={48} className="mx-auto text-slate-300 mb-2"/>
                <p className="font-semibold text-sm">Belum ada jurnal transaksi tercatat</p>
                <p className="text-xs text-slate-400 mt-1">Lakukan transaksi kasir untuk menguji penjurnalan otomatis</p>
                <a href="/46124026/penjualan/kasir_l1H" className="inline-flex items-center gap-1.5 px-4 py-2 mt-4 bg-brand-600 text-white rounded-xl text-xs font-semibold hover:bg-brand-700">
                  <Icon name="ShoppingCart" size={14}/> Buka Kasir Penjualan
                </a>
              </div>
            ) : (
              filtered.map(j => {
                const details = j.details || [];
                const totDeb = details.reduce((sum, d) => sum + parseFloat(d.debit || 0), 0);
                const totKred = details.reduce((sum, d) => sum + parseFloat(d.kredit || 0), 0);
                const voucherBalance = Math.abs(totDeb - totKred) < 0.01;

                return (
                  <div key={j.id} className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    {/* Header Voucher */}
                    <div className="px-5 py-3.5 bg-slate-50/80 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                      <div className="flex items-center gap-3">
                        <span className="font-mono font-bold text-brand-700 text-xs bg-brand-50 px-2.5 py-1 rounded-md border border-brand-200">
                          {j.no_jurnal}
                        </span>
                        <span className="text-xs text-slate-500 font-medium">
                          Ref: <b className="text-slate-800">{j.no_referensi}</b>
                        </span>
                        <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700">
                          {j.sumber}
                        </span>
                      </div>
                      <div className="flex items-center gap-4 text-xs text-slate-500">
                        <span>Tanggal: <b className="text-slate-700">{j.tgl_jurnal}</b></span>
                        <span className={`inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold
                          ${voucherBalance ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'}`}>
                          <Icon name={voucherBalance ? "Check" : "X"} size={12}/>
                          {voucherBalance ? 'Balanced' : 'Unbalanced'}
                        </span>
                      </div>
                    </div>

                    {/* Keterangan */}
                    {j.keterangan && (
                      <div className="px-5 py-2 bg-slate-50/40 border-b border-slate-100 text-xs text-slate-600 italic">
                        {j.keterangan}
                      </div>
                    )}

                    {/* Tabel Rincian Ayat Jurnal */}
                    <div className="overflow-x-auto">
                      <table className="w-full text-xs text-left">
                        <thead className="text-slate-400 font-semibold border-b border-slate-100">
                          <tr>
                            <th className="py-2.5 px-5 w-28">Kode Akun</th>
                            <th className="py-2.5 px-4">Nama Akun & Posisi</th>
                            <th className="py-2.5 px-4">Keterangan</th>
                            <th className="py-2.5 px-5 text-right w-36">Debit</th>
                            <th className="py-2.5 px-5 text-right w-36">Kredit</th>
                          </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                          {details.map((d, idx) => {
                            const isKredit = parseFloat(d.kredit) > 0;
                            return (
                              <tr key={idx} className="hover:bg-slate-50/60">
                                <td className="py-2 px-5 font-mono text-slate-500">{d.kode_coa}</td>
                                <td className={`py-2 px-4 font-medium text-slate-800 ${isKredit ? 'pl-8 text-slate-600' : ''}`}>
                                  {isKredit && <span className="text-slate-400 mr-1.5">↳</span>}
                                  {d.nama_coa}
                                </td>
                                <td className="py-2 px-4 text-slate-500">{d.keterangan || '-'}</td>
                                <td className="py-2 px-5 text-right font-mono font-semibold text-slate-800">
                                  {parseFloat(d.debit) > 0 ? `Rp ${Number(d.debit).toLocaleString('id-ID')}` : '-'}
                                </td>
                                <td className="py-2 px-5 text-right font-mono font-semibold text-slate-800">
                                  {parseFloat(d.kredit) > 0 ? `Rp ${Number(d.kredit).toLocaleString('id-ID')}` : '-'}
                                </td>
                              </tr>
                            );
                          })}
                        </tbody>
                        <tfoot className="bg-slate-50/60 font-bold border-t border-slate-200 text-slate-700">
                          <tr>
                            <td colSpan="3" className="py-2.5 px-5 text-right">Total Ayat Jurnal:</td>
                            <td className="py-2.5 px-5 text-right font-mono text-slate-900">
                              Rp {totDeb.toLocaleString('id-ID')}
                            </td>
                            <td className="py-2.5 px-5 text-right font-mono text-slate-900">
                              Rp {totKred.toLocaleString('id-ID')}
                            </td>
                          </tr>
                        </tfoot>
                      </table>
                    </div>
                  </div>
                );
              })
            )}
          </div>
        </main>
      </div>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<JurnalApp />);
</script>
</body>
</html>
