<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Master Barang & Stok — FinanceOS</title>

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
  window.__BARANG__ = <?= json_encode($barang ?? []) ?>;
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

function BarangApp() {
  const [collapsed, setCollapsed] = useState(false);
  const [barangList, setBarangList] = useState(window.__BARANG__ || []);
  const [search, setSearch] = useState('');
  const [modalTambah, setModalTambah] = useState(false);
  const [modalEdit, setModalEdit] = useState(null);
  const [modalHapus, setModalHapus] = useState(null);

  const stats = useMemo(() => {
    const totalItem = barangList.length;
    const totalNilai = barangList.reduce((acc, b) => acc + (parseFloat(b.stok || 0) * parseFloat(b.harga_beli || 0)), 0);
    const stokKritis = barangList.filter(b => parseFloat(b.stok || 0) <= 10).length;
    return { totalItem, totalNilai, stokKritis };
  }, [barangList]);

  const filtered = useMemo(() => {
    return barangList.filter(b =>
      b.nama_barang.toLowerCase().includes(search.toLowerCase()) ||
      b.kode_barang.toLowerCase().includes(search.toLowerCase()) ||
      (b.kategori && b.kategori.toLowerCase().includes(search.toLowerCase()))
    );
  }, [barangList, search]);

  return (
    <div className="flex h-screen overflow-hidden bg-slate-100">
      <Sidebar collapsed={collapsed} currentPath="/46124026/penjualan/barang_l1H"/>

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
              <span>Kas Masuk</span>
              <span>/</span>
              <span className="text-slate-800 font-semibold">Master Barang & Stok</span>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <button
              onClick={() => setModalTambah(true)}
              className="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-semibold flex items-center gap-2 transition-colors shadow-sm"
            >
              <Icon name="Plus" size={14}/> Tambah Barang
            </button>
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
          {window.__FLASH__?.error && (
            <div className="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center gap-3 fade-in">
              <Icon name="AlertCircle" size={20} className="text-rose-600 flex-shrink-0"/>
              <div>{window.__FLASH__.error}</div>
            </div>
          )}

          {/* Stat Cards */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div className="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center gap-4">
              <div className="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <Icon name="Package" size={24}/>
              </div>
              <div>
                <p className="text-xs text-slate-500 font-medium">Total Jenis Barang</p>
                <h3 className="text-xl font-bold text-slate-800">{stats.totalItem} Produk</h3>
              </div>
            </div>

            <div className="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center gap-4">
              <div className="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <Icon name="Coins" size={24}/>
              </div>
              <div>
                <p className="text-xs text-slate-500 font-medium">Nilai Persediaan Gudang (HPP)</p>
                <h3 className="text-xl font-bold text-slate-800">Rp {stats.totalNilai.toLocaleString('id-ID')}</h3>
              </div>
            </div>

            <div className="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center gap-4">
              <div className="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <Icon name="AlertTriangle" size={24}/>
              </div>
              <div>
                <p className="text-xs text-slate-500 font-medium">Stok Kritis (≤ 10)</p>
                <h3 className="text-xl font-bold text-amber-600">{stats.stokKritis} Produk</h3>
              </div>
            </div>
          </div>

          {/* Table Container */}
          <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div className="p-4 border-b border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
              <div className="relative w-full sm:w-72">
                <Icon name="Search" size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"/>
                <input
                  type="text"
                  value={search}
                  onChange={e => setSearch(e.target.value)}
                  placeholder="Cari kode, nama, atau kategori..."
                  className="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500"
                />
              </div>
              <p className="text-xs text-slate-500">Menampilkan <b>{filtered.length}</b> barang aktif</p>
            </div>

            <div className="overflow-x-auto">
              <table className="w-full text-xs text-left">
                <thead className="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                  <tr>
                    <th className="py-3 px-4">Kode</th>
                    <th className="py-3 px-4">Nama Barang</th>
                    <th className="py-3 px-4">Kategori</th>
                    <th className="py-3 px-4 text-right">Harga Beli (HPP)</th>
                    <th className="py-3 px-4 text-right">Harga Jual</th>
                    <th className="py-3 px-4 text-center">Stok</th>
                    <th className="py-3 px-4 text-right">Nilai Stok</th>
                    <th className="py-3 px-4 text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {filtered.length === 0 ? (
                    <tr>
                      <td colSpan="8" className="py-8 text-center text-slate-400">Data barang tidak ditemukan</td>
                    </tr>
                  ) : (
                    filtered.map(b => {
                      const stok = parseFloat(b.stok) || 0;
                      const hpp = parseFloat(b.harga_beli) || 0;
                      const nilai = stok * hpp;
                      return (
                        <tr key={b.id} className="hover:bg-slate-50/80">
                          <td className="py-3 px-4 font-mono font-bold text-brand-700">{b.kode_barang}</td>
                          <td className="py-3 px-4 font-semibold text-slate-800">{b.nama_barang}</td>
                          <td className="py-3 px-4">
                            <span className="px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-medium">{b.kategori}</span>
                          </td>
                          <td className="py-3 px-4 text-right text-slate-600">Rp {hpp.toLocaleString('id-ID')}</td>
                          <td className="py-3 px-4 text-right font-bold text-slate-900">
                            Rp {Number(b.harga_jual).toLocaleString('id-ID')}
                          </td>
                          <td className="py-3 px-4 text-center">
                            <span className={`px-2.5 py-0.5 rounded-full font-bold text-[11px]
                              ${stok <= 0 ? 'bg-red-100 text-red-700' : (stok <= 10 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700')}`}>
                              {stok} {b.satuan}
                            </span>
                          </td>
                          <td className="py-3 px-4 text-right font-semibold text-slate-700">
                            Rp {nilai.toLocaleString('id-ID')}
                          </td>
                          <td className="py-3 px-4 text-center">
                            <div className="flex items-center justify-center gap-1.5">
                              <button
                                onClick={() => setModalEdit(b)}
                                className="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                title="Edit Barang"
                              >
                                <Icon name="Edit2" size={14}/>
                              </button>
                              <button
                                onClick={() => setModalHapus(b)}
                                className="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                title="Hapus Barang"
                              >
                                <Icon name="Trash2" size={14}/>
                              </button>
                            </div>
                          </td>
                        </tr>
                      );
                    })
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </main>
      </div>

      {/* Modal Tambah Barang */}
      {modalTambah && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
          <div className="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl fade-in">
            <div className="flex items-center justify-between border-b pb-3">
              <h3 className="font-bold text-slate-800 text-base">Tambah Barang Baru</h3>
              <button onClick={() => setModalTambah(false)} className="text-slate-400 hover:text-slate-600">
                <Icon name="X" size={18}/>
              </button>
            </div>
            <form action="/46124026/penjualan/simpan_barang_l1H" method="POST" className="space-y-3">
              <input type="hidden" name={window.__CSRF__.name} value={window.__CSRF__.value}/>
              <div>
                <label className="text-xs font-semibold text-slate-600 block mb-1">Kode Barang</label>
                <input required type="text" name="kode_barang" placeholder="misal: BRG-005"
                  className="w-full text-xs px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none"/>
              </div>
              <div>
                <label className="text-xs font-semibold text-slate-600 block mb-1">Nama Barang</label>
                <input required type="text" name="nama_barang" placeholder="Nama produk lengkap"
                  className="w-full text-xs px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none"/>
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="text-xs font-semibold text-slate-600 block mb-1">Kategori</label>
                  <input type="text" name="kategori" defaultValue="Bahan Baku"
                    className="w-full text-xs px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none"/>
                </div>
                <div>
                  <label className="text-xs font-semibold text-slate-600 block mb-1">Satuan</label>
                  <input type="text" name="satuan" defaultValue="pcs"
                    className="w-full text-xs px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none"/>
                </div>
              </div>
              <div className="grid grid-cols-3 gap-3">
                <div>
                  <label className="text-xs font-semibold text-slate-600 block mb-1">Harga Beli</label>
                  <input required type="number" min="0" name="harga_beli" defaultValue="0"
                    className="w-full text-xs px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none"/>
                </div>
                <div>
                  <label className="text-xs font-semibold text-slate-600 block mb-1">Harga Jual</label>
                  <input required type="number" min="0" name="harga_jual" defaultValue="0"
                    className="w-full text-xs px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none"/>
                </div>
                <div>
                  <label className="text-xs font-semibold text-slate-600 block mb-1">Stok Awal</label>
                  <input required type="number" min="0" name="stok" defaultValue="0"
                    className="w-full text-xs px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none"/>
                </div>
              </div>
              <div className="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onClick={() => setModalTambah(false)} className="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                <button type="submit" className="px-4 py-2 text-xs font-semibold bg-brand-600 hover:bg-brand-700 text-white rounded-lg shadow-sm">Simpan Barang</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal Edit Barang */}
      {modalEdit && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
          <div className="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl fade-in">
            <div className="flex items-center justify-between border-b pb-3">
              <h3 className="font-bold text-slate-800 text-base">Edit Barang: {modalEdit.kode_barang}</h3>
              <button onClick={() => setModalEdit(null)} className="text-slate-400 hover:text-slate-600">
                <Icon name="X" size={18}/>
              </button>
            </div>
            <form action="/46124026/penjualan/update_barang_l1H" method="POST" className="space-y-3">
              <input type="hidden" name={window.__CSRF__.name} value={window.__CSRF__.value}/>
              <input type="hidden" name="id" value={modalEdit.id}/>
              <div>
                <label className="text-xs font-semibold text-slate-600 block mb-1">Nama Barang</label>
                <input required type="text" name="nama_barang" defaultValue={modalEdit.nama_barang}
                  className="w-full text-xs px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none"/>
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="text-xs font-semibold text-slate-600 block mb-1">Kategori</label>
                  <input type="text" name="kategori" defaultValue={modalEdit.kategori}
                    className="w-full text-xs px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none"/>
                </div>
                <div>
                  <label className="text-xs font-semibold text-slate-600 block mb-1">Satuan</label>
                  <input type="text" name="satuan" defaultValue={modalEdit.satuan}
                    className="w-full text-xs px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none"/>
                </div>
              </div>
              <div className="grid grid-cols-3 gap-3">
                <div>
                  <label className="text-xs font-semibold text-slate-600 block mb-1">Harga Beli</label>
                  <input required type="number" min="0" name="harga_beli" defaultValue={modalEdit.harga_beli}
                    className="w-full text-xs px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none"/>
                </div>
                <div>
                  <label className="text-xs font-semibold text-slate-600 block mb-1">Harga Jual</label>
                  <input required type="number" min="0" name="harga_jual" defaultValue={modalEdit.harga_jual}
                    className="w-full text-xs px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none"/>
                </div>
                <div>
                  <label className="text-xs font-semibold text-slate-600 block mb-1">Stok</label>
                  <input required type="number" min="0" name="stok" defaultValue={modalEdit.stok}
                    className="w-full text-xs px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none"/>
                </div>
              </div>
              <div className="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onClick={() => setModalEdit(null)} className="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                <button type="submit" className="px-4 py-2 text-xs font-semibold bg-brand-600 hover:bg-brand-700 text-white rounded-lg shadow-sm">Perbarui Data</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal Konfirmasi Hapus */}
      {modalHapus && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
          <div className="bg-white rounded-2xl max-w-sm w-full p-6 space-y-4 shadow-xl fade-in text-center">
            <div className="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto">
              <Icon name="AlertTriangle" size={24}/>
            </div>
            <div>
              <h3 className="font-bold text-slate-800 text-base">Hapus Barang?</h3>
              <p className="text-xs text-slate-500 mt-1">
                Apakah Anda yakin ingin menghapus barang <b>{modalHapus.nama_barang}</b> ({modalHapus.kode_barang})?
              </p>
            </div>
            <div className="flex justify-center gap-2 pt-2">
              <button onClick={() => setModalHapus(null)} className="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
              <a href={`/46124026/penjualan/hapus_barang_l1H/${modalHapus.id}`} className="px-4 py-2 text-xs font-semibold bg-red-600 hover:bg-red-700 text-white rounded-lg shadow-sm">
                Ya, Hapus
              </a>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<BarangApp />);
</script>
</body>
</html>
