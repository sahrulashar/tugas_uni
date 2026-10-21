<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Laboratorium Praktikum Pertemuan 9 — FinanceOS</title>

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
  window.__BARANG__ = <?= json_encode($barangList ?? []) ?>;
  window.__NERACA__ = <?= json_encode($neraca ?? []) ?>;
  window.__JURNAL__ = <?= json_encode($jurnalList ?? []) ?>;
  window.__FLASH__  = {
    success: "<?= addslashes(session()->getFlashdata('success') ?? '') ?>",
    warning: "<?= addslashes(session()->getFlashdata('warning') ?? '') ?>",
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
  { label: 'Executive Dashboard', icon: 'LayoutDashboard', href: '/46124026/dashboard' },
  {
    label: 'Kas Keluar',
    icon: 'ArrowUpFromLine',
    children: [
      { label: 'Chart of Accounts', icon: 'BookOpen', href: '/46124026/kas_keluar/coa_l1H' },
      { label: 'Supplier',          icon: 'Truck',    href: '/46124026/kas_keluar/supplier_l1H' },
      { label: 'Karyawan',          icon: 'Users',    href: '/46124026/kas_keluar/karyawan_l1H' },
    ],
  },
  {
    label: 'Kas Masuk & Penjualan',
    icon: 'ArrowDownToLine',
    children: [
      { label: 'Kasir Penjualan (POS)', icon: 'ShoppingCart', href: '/46124026/penjualan/kasir_l1H' },
      { label: 'Master Barang & Stok',  icon: 'Package',      href: '/46124026/penjualan/barang_l1H' },
    ],
  },
  {
    label: 'Aktivitas Kas Keluar',
    icon: 'ClipboardList',
    children: [
      { label: 'Rencana Beli',      icon: 'ShoppingCart', href: '/46124026/aktivitas/aktivitas1' },
      { label: 'Bukti Kas Keluar',  icon: 'Receipt',      href: '/46124026/aktivitas/aktivitas2' },
      { label: 'Rekap BKK',         icon: 'ClipboardCheck', href: '/46124026/aktivitas/aktivitas3' },
    ],
  },
  {
    label: 'Akuntansi & Analitik',
    icon: 'BarChart3',
    children: [
      { label: 'Executive Dashboard',      icon: 'PieChart',     href: '/46124026/dashboard' },
      { label: 'Praktikum Pertemuan 10',   icon: 'Sparkles',     href: '/46124026/dashboard/praktikum_l1H' },
      { label: 'Jurnal Umum (Automated)',  icon: 'BookMarked',   href: '/46124026/akuntansi/jurnal_l1H' },
      { label: 'Neraca Saldo',             icon: 'Scale',        href: '/46124026/akuntansi/neraca_saldo_l1H' },
      { label: 'Praktikum Pertemuan 9',    icon: 'CheckCircle2', href: '/46124026/akuntansi/praktikum_l1H' },
    ],
  },
  {
    label: 'Pengaturan RBAC',
    icon: 'Shield',
    children: [
      { label: 'Manajemen User',  icon: 'Users',    href: '/46124026/rbac/user' },
      { label: 'Laman & Aksi',    icon: 'FileText', href: '/46124026/rbac/laman' },
      { label: 'Atur Hak Akses',  icon: 'Lock',     href: '/46124026/rbac/akses' },
    ],
  },
  { label: 'Audit Trail Log', icon: 'History', href: '/46124026/audit' },
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

function PraktikumApp() {
  const [collapsed, setCollapsed] = useState(false);
  const [loadingAction, setLoadingAction] = useState(null);

  const neraca = window.__NERACA__ || {};
  const isBalance = neraca.is_balance ?? true;
  const barangList = window.__BARANG__ || [];

  return (
    <div className="flex h-screen overflow-hidden bg-slate-100">
      <Sidebar collapsed={collapsed} currentPath="/46124026/akuntansi/praktikum_l1H"/>

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
              <span>Praktikum</span>
              <span>/</span>
              <span className="text-slate-800 font-semibold">Pertemuan 9 (Integration & Journaling)</span>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <span className="text-xs font-semibold px-3 py-1 rounded-full bg-blue-100 text-blue-800">
              NIM: 46124026
            </span>
            <div className="w-8 h-8 rounded-full bg-brand-600 text-white font-bold flex items-center justify-center text-xs">
              {window._erpUser?.initial || 'A'}
            </div>
          </div>
        </header>

        {/* Content Area */}
        <main className="flex-1 overflow-y-auto p-6 space-y-6">
          {/* Flash Messages */}
          {window.__FLASH__?.success && (
            <div className="p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl flex items-start gap-3 fade-in shadow-sm">
              <div className="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                <Icon name="CheckCircle2" size={18}/>
              </div>
              <div className="text-xs leading-relaxed" dangerouslySetInnerHTML={{ __html: window.__FLASH__.success }} />
            </div>
          )}
          {window.__FLASH__?.warning && (
            <div className="p-4 bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl flex items-start gap-3 fade-in shadow-sm">
              <div className="w-8 h-8 rounded-lg bg-amber-600 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                <Icon name="AlertTriangle" size={18}/>
              </div>
              <div className="text-xs leading-relaxed" dangerouslySetInnerHTML={{ __html: window.__FLASH__.warning }} />
            </div>
          )}
          {window.__FLASH__?.error && (
            <div className="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center gap-3 fade-in shadow-sm">
              <Icon name="AlertCircle" size={20} className="text-rose-600 flex-shrink-0"/>
              <div className="text-xs">{window.__FLASH__.error}</div>
            </div>
          )}

          {/* Hero Praktikum */}
          <div className="bg-gradient-to-br from-slate-900 via-indigo-950 to-blue-900 rounded-3xl p-6 text-white shadow-xl">
            <div className="max-w-3xl space-y-2">
              <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-xs font-semibold">
                <Icon name="Cpu" size={14}/> Praktikum Mandiri — Pertemuan 9
              </div>
              <h1 className="text-2xl font-black tracking-tight">Cross-Module Integration & Automated Journaling</h1>
              <p className="text-xs text-slate-300 leading-relaxed">
                Menguji aliran data end-to-end secara atomik (ACID):
                <b> Transaksi Kasir Penjualan → Pengurangan Stok Gudang → Penjurnal Otomatis Akuntansi → Terbentuknya Neraca Saldo Seimbang</b>.
              </p>
            </div>

            {/* Pipeline Visual Diagram */}
            <div className="grid grid-cols-1 sm:grid-cols-4 gap-3 mt-6 pt-6 border-t border-white/10 text-xs">
              <div className="p-3 bg-white/5 rounded-xl border border-white/10">
                <div className="text-blue-400 font-bold mb-1 flex items-center gap-1.5">
                  <Icon name="ShoppingCart" size={14}/> 1. Kasir POS
                </div>
                <p className="text-slate-300 text-[11px]">User input transaksi item & metode bayar.</p>
              </div>
              <div className="p-3 bg-white/5 rounded-xl border border-white/10">
                <div className="text-indigo-400 font-bold mb-1 flex items-center gap-1.5">
                  <Icon name="PackageCheck" size={14}/> 2. Stok Gudang
                </div>
                <p className="text-slate-300 text-[11px]">Cek stok cukup & kurangi kuantitas item.</p>
              </div>
              <div className="p-3 bg-white/5 rounded-xl border border-white/10">
                <div className="text-purple-400 font-bold mb-1 flex items-center gap-1.5">
                  <Icon name="BookMarked" size={14}/> 3. Jurnal Otomatis
                </div>
                <p className="text-slate-300 text-[11px]">Generate ayat Kas, Penjualan, HPP & Persediaan.</p>
              </div>
              <div className="p-3 bg-white/5 rounded-xl border border-white/10">
                <div className="text-emerald-400 font-bold mb-1 flex items-center gap-1.5">
                  <Icon name="Scale" size={14}/> 4. Neraca Saldo
                </div>
                <p className="text-slate-300 text-[11px]">Debit = Kredit terefleksi secara real-time.</p>
              </div>
            </div>
          </div>

          {/* Action Cards: Uji Kasus 1 & Uji Kasus 2 */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {/* Card 1: Sukses */}
            <div className="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between space-y-4">
              <div>
                <div className="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center mb-3">
                  <Icon name="PlayCircle" size={26}/>
                </div>
                <h3 className="text-lg font-bold text-slate-800">Uji 1: Simulasi Transaksi Sukses End-to-End</h3>
                <p className="text-xs text-slate-500 mt-1 leading-relaxed">
                  Menjalankan simulasi transaksi kasir valid (2 pcs barang). Sistem akan memotong stok gudang,
                  mencatat header & detail penjualan, membuat ayat jurnal otomatis (Debit & Kredit), dan memastikan
                  Neraca Saldo tetap dalam kondisi <b>Balanced</b>.
                </p>
              </div>

              <div className="pt-4 border-t border-slate-100 flex items-center justify-between">
                <span className="text-[11px] font-semibold text-slate-400">Target: Transaksi Atomik Sukses</span>
                <a
                  href="/46124026/akuntansi/uji_sukses_l1H"
                  onClick={() => setLoadingAction('sukses')}
                  className="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2 active:scale-95"
                >
                  <Icon name="Zap" size={14}/> Jalankan Uji Sukses
                </a>
              </div>
            </div>

            {/* Card 2: Rollback */}
            <div className="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between space-y-4">
              <div>
                <div className="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mb-3">
                  <Icon name="RotateCcw" size={26}/>
                </div>
                <h3 className="text-lg font-bold text-slate-800">Uji 2: Validasi Rollback (Under-Stock Prevention)</h3>
                <p className="text-xs text-slate-500 mt-1 leading-relaxed">
                  Menjalankan order kasir dengan kuantitas melebihi stok yang tersedia. <b>StokService</b> akan
                  melempar Exception sebelum transaksi commit, memicu <b>DB Rollback</b> sehingga stok tidak berkurang,
                  faktur tidak masuk, dan tidak ada jurnal sampah yang tersimpan.
                </p>
              </div>

              <div className="pt-4 border-t border-slate-100 flex items-center justify-between">
                <span className="text-[11px] font-semibold text-slate-400">Target: ACID Atomicity Rollback</span>
                <a
                  href="/46124026/akuntansi/uji_rollback_l1H"
                  onClick={() => setLoadingAction('rollback')}
                  className="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2 active:scale-95"
                >
                  <Icon name="ShieldAlert" size={14}/> Jalankan Uji Rollback
                </a>
              </div>
            </div>
          </div>

          {/* Quick Monitor Status: Stok Terkini & Neraca Saldo */}
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {/* Monitor Stok (6 cols) */}
            <div className="lg:col-span-6 bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-3">
              <div className="flex items-center justify-between">
                <h4 className="font-bold text-sm text-slate-800 flex items-center gap-2">
                  <Icon name="Package" size={16} className="text-brand-600"/> Stok Gudang Saat Ini
                </h4>
                <a href="/46124026/penjualan/barang_l1H" className="text-xs text-brand-600 hover:underline">Kelola Barang</a>
              </div>
              <div className="overflow-x-auto">
                <table className="w-full text-xs text-left">
                  <thead className="bg-slate-50 text-slate-500">
                    <tr>
                      <th className="py-2 px-3">Kode</th>
                      <th className="py-2 px-3">Nama Barang</th>
                      <th className="py-2 px-3 text-right">HPP</th>
                      <th className="py-2 px-3 text-center">Stok</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {barangList.map(b => (
                      <tr key={b.id}>
                        <td className="py-2 px-3 font-mono font-bold text-slate-700">{b.kode_barang}</td>
                        <td className="py-2 px-3 text-slate-800 font-medium">{b.nama_barang}</td>
                        <td className="py-2 px-3 text-right text-slate-600">Rp {Number(b.harga_beli).toLocaleString('id-ID')}</td>
                        <td className="py-2 px-3 text-center">
                          <span className="font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                            {b.stok} {b.satuan}
                          </span>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>

            {/* Monitor Neraca Saldo (6 cols) */}
            <div className="lg:col-span-6 bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-3">
              <div className="flex items-center justify-between">
                <h4 className="font-bold text-sm text-slate-800 flex items-center gap-2">
                  <Icon name="Scale" size={16} className="text-brand-600"/> Status Neraca Saldo Terkini
                </h4>
                <a href="/46124026/akuntansi/neraca_saldo_l1H" className="text-xs text-brand-600 hover:underline">Lihat Rincian</a>
              </div>

              <div className="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3 text-xs">
                <div className="flex justify-between items-center pb-2 border-b border-slate-200">
                  <span className="text-slate-600">Status Keseimbangan:</span>
                  <span className={`px-2.5 py-1 rounded-full font-bold text-xs ${isBalance ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'}`}>
                    {isBalance ? '🟢 100% Balanced' : '🔴 Unbalanced'}
                  </span>
                </div>
                <div className="flex justify-between items-center">
                  <span className="text-slate-600">Total Saldo Akhir Debit:</span>
                  <span className="font-mono font-bold text-slate-900">
                    Rp {Number(neraca.tot_akhir_debit || 0).toLocaleString('id-ID')}
                  </span>
                </div>
                <div className="flex justify-between items-center">
                  <span className="text-slate-600">Total Saldo Akhir Kredit:</span>
                  <span className="font-mono font-bold text-slate-900">
                    Rp {Number(neraca.tot_akhir_kredit || 0).toLocaleString('id-ID')}
                  </span>
                </div>
                <div className="flex justify-between items-center pt-2 border-t border-slate-200">
                  <span className="font-semibold text-slate-700">Selisih (Discrepancy):</span>
                  <span className="font-mono font-bold text-emerald-700">
                    Rp {Number(neraca.selisih || 0).toLocaleString('id-ID')}
                  </span>
                </div>
              </div>

              <div className="flex gap-2">
                <a href="/46124026/penjualan/kasir_l1H" className="flex-1 py-2 text-center bg-brand-50 hover:bg-brand-100 text-brand-700 font-semibold rounded-xl text-xs transition-colors">
                  Buka Kasir POS
                </a>
                <a href="/46124026/akuntansi/jurnal_l1H" className="flex-1 py-2 text-center bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold rounded-xl text-xs transition-colors">
                  Buka Jurnal Umum
                </a>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<PraktikumApp />);
</script>
</body>
</html>
