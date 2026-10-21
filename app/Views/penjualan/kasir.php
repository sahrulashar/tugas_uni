<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Kasir Penjualan (POS) — FinanceOS</title>

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
  window.__RECENT__ = <?= json_encode($recent ?? []) ?>;
  window.__FLASH__  = {
    success: "<?= addslashes(session()->getFlashdata('success') ?? '') ?>",
    error:   "<?= addslashes(session()->getFlashdata('error')   ?? '') ?>",
    warning: "<?= addslashes(session()->getFlashdata('warning') ?? '') ?>"
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

function KasirApp() {
  const [collapsed, setCollapsed] = useState(false);
  const [barangList, setBarangList] = useState(window.__BARANG__ || []);
  const [cart, setCart] = useState([]);
  const [search, setSearch] = useState('');
  const [selectedCat, setSelectedCat] = useState('ALL');
  const [customer, setCustomer] = useState('Pelanggan Umum');
  const [metodeBayar, setMetodeBayar] = useState('Tunai');
  const [diskon, setDiskon] = useState(0);

  const categories = useMemo(() => {
    const cats = new Set(barangList.map(b => b.kategori || 'Umum'));
    return ['ALL', ...Array.from(cats)];
  }, [barangList]);

  const filteredBarang = useMemo(() => {
    return barangList.filter(b => {
      const matchCat = selectedCat === 'ALL' || b.kategori === selectedCat;
      const matchSearch = b.nama_barang.toLowerCase().includes(search.toLowerCase()) ||
                          b.kode_barang.toLowerCase().includes(search.toLowerCase());
      return matchCat && matchSearch;
    });
  }, [barangList, selectedCat, search]);

  const addToCart = (barang) => {
    const stok = parseFloat(barang.stok) || 0;
    if (stok <= 0) return;

    setCart(prev => {
      const existing = prev.find(item => item.kode_barang === barang.kode_barang);
      if (existing) {
        if (existing.qty + 1 > stok) {
          alert(`Stok tidak mencukupi! Tersedia hanya ${stok} ${barang.satuan}`);
          return prev;
        }
        return prev.map(item =>
          item.kode_barang === barang.kode_barang
            ? { ...item, qty: item.qty + 1, subtotal: (item.qty + 1) * parseFloat(item.harga_satuan) }
            : item
        );
      } else {
        return [...prev, {
          kode_barang:  barang.kode_barang,
          nama_barang:  barang.nama_barang,
          satuan:       barang.satuan,
          harga_satuan: parseFloat(barang.harga_jual),
          hpp_satuan:   parseFloat(barang.harga_beli),
          stok:         stok,
          qty:          1,
          subtotal:     parseFloat(barang.harga_jual),
        }];
      }
    });
  };

  const updateQty = (kode_barang, newQty) => {
    const qty = parseFloat(newQty);
    if (isNaN(qty) || qty <= 0) {
      removeFromCart(kode_barang);
      return;
    }
    setCart(prev => prev.map(item => {
      if (item.kode_barang === kode_barang) {
        if (qty > item.stok) {
          alert(`Maksimal stok tersedia: ${item.stok} ${item.satuan}`);
          return { ...item, qty: item.stok, subtotal: item.stok * item.harga_satuan };
        }
        return { ...item, qty, subtotal: qty * item.harga_satuan };
      }
      return item;
    }));
  };

  const removeFromCart = (kode_barang) => {
    setCart(prev => prev.filter(i => i.kode_barang !== kode_barang));
  };

  const clearCart = () => setCart([]);

  const subtotal = useMemo(() => cart.reduce((sum, item) => sum + item.subtotal, 0), [cart]);
  const totalNetto = useMemo(() => Math.max(0, subtotal - parseFloat(diskon || 0)), [subtotal, diskon]);
  const totalHpp = useMemo(() => cart.reduce((sum, item) => sum + (item.qty * item.hpp_satuan), 0), [cart]);

  return (
    <div className="flex h-screen overflow-hidden bg-slate-100">
      <Sidebar collapsed={collapsed} currentPath="/46124026/penjualan/kasir_l1H"/>

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
              <span>Penjualan</span>
              <span>/</span>
              <span className="text-slate-800 font-semibold">Kasir POS</span>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
              <span className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
              Automated Journaling: Ready
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
            <div className="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3 fade-in"
                 dangerouslySetInnerHTML={{ __html: window.__FLASH__.success }} />
          )}
          {window.__FLASH__?.error && (
            <div className="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center gap-3 fade-in">
              <Icon name="AlertCircle" size={20} className="text-rose-600 flex-shrink-0"/>
              <div>{window.__FLASH__.error}</div>
            </div>
          )}
          {window.__FLASH__?.warning && (
            <div className="p-4 bg-amber-50 border border-amber-200 text-amber-900 rounded-xl flex items-center gap-3 fade-in"
                 dangerouslySetInnerHTML={{ __html: window.__FLASH__.warning }} />
          )}

          {/* Banner Integrasi */}
          <div className="bg-gradient-to-r from-blue-900 to-indigo-800 rounded-2xl p-6 text-white shadow-lg flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
              <div className="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-white/10 text-xs font-medium mb-2">
                <Icon name="Sparkles" size={14}/> Pertemuan 9: Cross-Module Integration
              </div>
              <h1 className="text-2xl font-bold">Kasir Penjualan & Automated Journaling</h1>
              <p className="text-blue-200 text-sm mt-1">
                Aliran otomatis: Transaksi Kasir → Validasi & Pengurangan Stok → Penjurnalan Akuntansi Ganda (ACID).
              </p>
            </div>
            <div className="flex items-center gap-3">
              <a href="/46124026/akuntansi/jurnal_l1H" className="px-4 py-2 bg-white/10 hover:bg-white/20 rounded-xl text-sm font-medium transition-colors flex items-center gap-2">
                <Icon name="BookMarked" size={16}/> Lihat Buku Jurnal
              </a>
              <a href="/46124026/akuntansi/neraca_saldo_l1H" className="px-4 py-2 bg-brand-500 hover:bg-brand-600 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2 shadow-md">
                <Icon name="Scale" size={16}/> Cek Neraca Saldo
              </a>
            </div>
          </div>

          {/* Grid Layout: Katalog Barang & Keranjang Transaksi */}
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {/* Kolom Kiri: Katalog Produk (7 cols) */}
            <div className="lg:col-span-7 space-y-4">
              <div className="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 flex flex-col sm:flex-row gap-3 items-center justify-between">
                <div className="relative w-full sm:w-72">
                  <Icon name="Search" size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"/>
                  <input
                    type="text"
                    value={search}
                    onChange={e => setSearch(e.target.value)}
                    placeholder="Cari kode atau nama barang..."
                    className="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500"
                  />
                </div>
                <div className="flex gap-1 overflow-x-auto w-full sm:w-auto pb-1">
                  {categories.map(cat => (
                    <button
                      key={cat}
                      onClick={() => setSelectedCat(cat)}
                      className={`px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors
                        ${selectedCat === cat ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'}`}
                    >
                      {cat}
                    </button>
                  ))}
                </div>
              </div>

              {/* Grid Cards Produk */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {filteredBarang.length === 0 ? (
                  <div className="col-span-2 bg-white rounded-2xl p-8 text-center text-slate-400 border border-slate-200">
                    <Icon name="PackageOpen" size={40} className="mx-auto text-slate-300 mb-2"/>
                    <p className="font-medium">Tidak ada barang ditemukan</p>
                  </div>
                ) : (
                  filteredBarang.map(item => {
                    const stok = parseFloat(item.stok) || 0;
                    const habis = stok <= 0;
                    return (
                      <div key={item.id} className="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
                        <div>
                          <div className="flex items-start justify-between gap-2">
                            <span className="text-xs font-mono font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded">
                              {item.kode_barang}
                            </span>
                            <span className={`text-xs font-semibold px-2 py-0.5 rounded-full
                              ${habis ? 'bg-red-100 text-red-700' : (stok < 10 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700')}`}>
                              {habis ? 'Stok Habis' : `Stok: ${stok} ${item.satuan}`}
                            </span>
                          </div>
                          <h3 className="font-semibold text-slate-800 text-sm mt-2 line-clamp-1">{item.nama_barang}</h3>
                          <p className="text-xs text-slate-500">{item.kategori || 'Bahan Baku'}</p>
                        </div>

                        <div className="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                          <div>
                            <p className="text-xs text-slate-400">Harga Jual</p>
                            <p className="font-bold text-slate-900 text-base">
                              Rp {Number(item.harga_jual).toLocaleString('id-ID')}
                            </p>
                          </div>
                          <button
                            type="button"
                            disabled={habis}
                            onClick={() => addToCart(item)}
                            className={`px-3 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors
                              ${habis ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-brand-600 text-white hover:bg-brand-700 active:scale-95'}`}
                          >
                            <Icon name="Plus" size={14}/> Tambah
                          </button>
                        </div>
                      </div>
                    );
                  })
                )}
              </div>
            </div>

            {/* Kolom Kanan: Keranjang Kasir & Checkout (5 cols) */}
            <div className="lg:col-span-5">
              <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4 sticky top-6">
                <div className="flex items-center justify-between border-b border-slate-100 pb-3">
                  <div className="flex items-center gap-2">
                    <Icon name="ShoppingBag" size={18} className="text-brand-600"/>
                    <h2 className="font-bold text-slate-800">Keranjang Kasir</h2>
                  </div>
                  {cart.length > 0 && (
                    <button onClick={clearCart} className="text-xs text-red-500 hover:text-red-700 font-medium">
                      Kosongkan
                    </button>
                  )}
                </div>

                {/* Form Data Pelanggan */}
                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <label className="text-xs font-semibold text-slate-600 block mb-1">Pelanggan</label>
                    <input
                      type="text"
                      value={customer}
                      onChange={e => setCustomer(e.target.value)}
                      className="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none"
                    />
                  </div>
                  <div>
                    <label className="text-xs font-semibold text-slate-600 block mb-1">Metode Bayar</label>
                    <select
                      value={metodeBayar}
                      onChange={e => setMetodeBayar(e.target.value)}
                      className="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none"
                    >
                      <option value="Tunai">Kas Tangan (Tunai)</option>
                      <option value="Kas BCA">Kas BCA (Transfer)</option>
                      <option value="Kas Kecil">Kas Kecil</option>
                    </select>
                  </div>
                </div>

                {/* List Item Keranjang */}
                <div className="space-y-2 max-h-60 overflow-y-auto pr-1">
                  {cart.length === 0 ? (
                    <div className="text-center py-8 text-slate-400">
                      <Icon name="ShoppingCart" size={32} className="mx-auto mb-2 text-slate-300"/>
                      <p className="text-xs font-medium">Keranjang masih kosong</p>
                      <p className="text-[11px] text-slate-400">Pilih barang di sebelah kiri untuk mulai transaksi</p>
                    </div>
                  ) : (
                    cart.map(item => (
                      <div key={item.kode_barang} className="p-3 bg-slate-50 rounded-xl border border-slate-200/60 flex items-center justify-between gap-3">
                        <div className="min-w-0 flex-1">
                          <p className="font-semibold text-xs text-slate-800 truncate">{item.nama_barang}</p>
                          <p className="text-[11px] text-slate-500">
                            @ Rp {item.harga_satuan.toLocaleString('id-ID')}
                          </p>
                        </div>
                        {/* Stepper Qty */}
                        <div className="flex items-center gap-1.5">
                          <button
                            onClick={() => updateQty(item.kode_barang, item.qty - 1)}
                            className="w-6 h-6 rounded bg-white border border-slate-300 flex items-center justify-center text-slate-600 hover:bg-slate-100 text-xs font-bold"
                          >-</button>
                          <span className="w-8 text-center text-xs font-semibold">{item.qty}</span>
                          <button
                            onClick={() => updateQty(item.kode_barang, item.qty + 1)}
                            className="w-6 h-6 rounded bg-white border border-slate-300 flex items-center justify-center text-slate-600 hover:bg-slate-100 text-xs font-bold"
                          >+</button>
                        </div>
                        {/* Subtotal */}
                        <div className="text-right min-w-[70px]">
                          <p className="text-xs font-bold text-slate-800">
                            Rp {item.subtotal.toLocaleString('id-ID')}
                          </p>
                          <button
                            onClick={() => removeFromCart(item.kode_barang)}
                            className="text-[10px] text-red-500 hover:underline"
                          >Hapus</button>
                        </div>
                      </div>
                    ))
                  )}
                </div>

                {/* Ringkasan Biaya */}
                <div className="pt-3 border-t border-slate-200 space-y-2 text-xs">
                  <div className="flex justify-between text-slate-600">
                    <span>Subtotal ({cart.length} item)</span>
                    <span className="font-semibold">Rp {subtotal.toLocaleString('id-ID')}</span>
                  </div>
                  <div className="flex items-center justify-between text-slate-600">
                    <span>Diskon (Rp)</span>
                    <input
                      type="number"
                      min="0"
                      value={diskon}
                      onChange={e => setDiskon(e.target.value)}
                      className="w-28 text-right px-2 py-1 border border-slate-200 rounded focus:ring-1 focus:ring-brand-500 focus:outline-none"
                    />
                  </div>
                  <div className="flex justify-between items-baseline pt-2 border-t border-slate-200">
                    <span className="font-bold text-slate-800 text-sm">Total Bayar</span>
                    <span className="font-extrabold text-brand-600 text-lg">
                      Rp {totalNetto.toLocaleString('id-ID')}
                    </span>
                  </div>
                </div>

                {/* Form Post Submit */}
                <form action="/46124026/penjualan/proses_kasir_l1H" method="POST">
                  <input type="hidden" name={window.__CSRF__.name} value={window.__CSRF__.value}/>
                  <input type="hidden" name="customer" value={customer}/>
                  <input type="hidden" name="metode_bayar" value={metodeBayar}/>
                  <input type="hidden" name="diskon" value={diskon}/>
                  <input type="hidden" name="items_json" value={JSON.stringify(cart)}/>

                  <button
                    type="submit"
                    disabled={cart.length === 0}
                    className={`w-full py-3.5 rounded-xl text-sm font-bold flex items-center justify-center gap-2 transition-all shadow-md
                      ${cart.length === 0 ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-brand-600 hover:bg-brand-700 text-white active:scale-98'}`}
                  >
                    <Icon name="Zap" size={16}/> Proses Transaksi & Penjurnalan
                  </button>
                </form>

                <p className="text-[11px] text-slate-400 text-center leading-relaxed">
                  🛡️ Transaksi bersifat <b>Atomik (ACID)</b>: Menjamin stok terpotong dan ayat jurnal [Kas/Pendapatan/HPP/Persediaan] tercatat secara simultan.
                </p>
              </div>
            </div>
          </div>

          {/* Tabel Riwayat Transaksi Kasir Terbaru */}
          <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
            <div className="flex items-center justify-between">
              <div>
                <h3 className="font-bold text-slate-800 text-base">Riwayat Penjualan Terkini</h3>
                <p className="text-xs text-slate-500">10 transaksi kasir terakhir beserta status integrasi jurnalnya</p>
              </div>
              <a href="/46124026/akuntansi/jurnal_l1H" className="text-xs font-semibold text-brand-600 hover:underline flex items-center gap-1">
                Buka Semua di Jurnal Umum <Icon name="ArrowRight" size={12}/>
              </a>
            </div>

            <div className="overflow-x-auto">
              <table className="w-full text-xs text-left">
                <thead className="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                  <tr>
                    <th className="py-2.5 px-3">No. Faktur</th>
                    <th className="py-2.5 px-3">Tanggal</th>
                    <th className="py-2.5 px-3">Pelanggan</th>
                    <th className="py-2.5 px-3">Metode</th>
                    <th className="py-2.5 px-3 text-right">Total Netto</th>
                    <th className="py-2.5 px-3 text-right">HPP</th>
                    <th className="py-2.5 px-3 text-center">Status Jurnal</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {(!window.__RECENT__ || window.__RECENT__.length === 0) ? (
                    <tr>
                      <td colSpan="7" className="py-6 text-center text-slate-400">Belum ada transaksi penjualan</td>
                    </tr>
                  ) : (
                    window.__RECENT__.map(r => (
                      <tr key={r.id} className="hover:bg-slate-50/80">
                        <td className="py-2.5 px-3 font-mono font-bold text-brand-700">{r.no_faktur}</td>
                        <td className="py-2.5 px-3 text-slate-600">{r.tgl_jual}</td>
                        <td className="py-2.5 px-3 text-slate-800 font-medium">{r.customer}</td>
                        <td className="py-2.5 px-3">
                          <span className="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-medium">{r.metode_bayar}</span>
                        </td>
                        <td className="py-2.5 px-3 text-right font-bold text-slate-900">
                          Rp {Number(r.total_netto).toLocaleString('id-ID')}
                        </td>
                        <td className="py-2.5 px-3 text-right text-slate-500">
                          Rp {Number(r.total_hpp).toLocaleString('id-ID')}
                        </td>
                        <td className="py-2.5 px-3 text-center">
                          <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                            <Icon name="CheckCircle" size={12}/> Terjurnal Otomatis
                          </span>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </main>
      </div>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<KasirApp />);
</script>
</body>
</html>
