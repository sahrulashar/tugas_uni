<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Detail COA — FinanceOS</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand:   { 50:'#eff6ff', 100:'#dbeafe', 500:'#3b82f6', 600:'#2563eb', 700:'#1d4ed8', 800:'#1e40af' },
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
    .fade-in { animation: fadeIn 0.35s ease forwards; }
  </style>
</head>
<body class="h-full bg-slate-100 text-slate-800 antialiased">

<!-- PHP → JS Bridge -->
<script>
  window.__COA__ = <?= json_encode($coa ?? []) ?>;
</script>

<div id="root"></div>

<script type="text/babel">
const { useState, useEffect, useRef } = React;

/* ── Icon ─────────────────────────────── */
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

/* ── Nav data ─────────────────────────── */
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

/* ── NavItem ──────────────────────────── */
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
        <span className={`transition-transform duration-200 ${open ? 'rotate-90' : ''}`}>
          <Icon name="ChevronRight" size={14}/>
        </span>
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

/* ── Sidebar ──────────────────────────── */
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
            <button className="text-slate-500 hover:text-white transition-colors"><Icon name="LogOut" size={15}/></button>
          </div>
        )}
      </div>
    </aside>
  );
}

/* ── Topbar ───────────────────────────── */
function Topbar({ collapsed, onToggle, breadcrumbs }) {
  return (
    <header className={`fixed top-0 right-0 z-20 flex items-center justify-between h-16 bg-white border-b border-slate-200 px-4 shadow-sm sidebar-transition ${collapsed ? 'left-16' : 'left-64'}`}>
      <div className="flex items-center gap-3">
        <button onClick={onToggle} className="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-slate-800 transition-colors">
          <Icon name="PanelLeft" size={18}/>
        </button>
        <div className="hidden sm:flex items-center gap-1.5 text-sm">
          {breadcrumbs.map((b, i) => (
            <span key={i} className="flex items-center gap-1.5">
              {i > 0 && <Icon name="ChevronRight" size={13} className="text-slate-400"/>}
              {i === breadcrumbs.length - 1
                ? <span className="font-semibold text-slate-800">{b.label}</span>
                : <a href={b.href} className="text-slate-500 hover:text-brand-600 transition-colors">{b.label}</a>}
            </span>
          ))}
        </div>
      </div>
      <div className="flex items-center gap-1">
        <button className="relative p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-slate-800 transition-colors">
          <Icon name="Bell" size={18}/>
          <span className="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
        </button>
        <div className="w-px h-6 bg-slate-200 mx-1"></div>
        <button className="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
          <div className="w-7 h-7 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold">A</div>
          <Icon name="ChevronDown" size={13} className="text-slate-400 hidden sm:block"/>
        </button>
      </div>
    </header>
  );
}

/* ── Detail Row ───────────────────────── */
function DetailRow({ label, children, last = false }) {
  return (
    <div className={`flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-0 py-4 ${!last ? 'border-b border-slate-100' : ''}`}>
      <span className="sm:w-48 flex-shrink-0 text-sm font-medium text-slate-500">{label}</span>
      <div className="flex-1 text-sm text-slate-800">{children}</div>
    </div>
  );
}

/* ── Tipe config ──────────────────────── */
const TIPE_CONFIG = {
  'Aset':        { bg:'bg-blue-50',   text:'text-blue-700',   border:'border-blue-200',   iconBg:'bg-blue-100',   icon:'text-blue-600',   iconName:'Landmark',    desc:'Sumber daya ekonomi yang dimiliki perusahaan' },
  'Liabilitas':  { bg:'bg-red-50',    text:'text-red-700',    border:'border-red-200',    iconBg:'bg-red-100',    icon:'text-red-600',    iconName:'CreditCard',  desc:'Kewajiban atau hutang perusahaan kepada pihak lain' },
  'Ekuitas':     { bg:'bg-purple-50', text:'text-purple-700', border:'border-purple-200', iconBg:'bg-purple-100', icon:'text-purple-600', iconName:'PiggyBank',   desc:'Hak kepemilikan pemegang saham atas aset perusahaan' },
  'Pendapatan':  { bg:'bg-green-50',  text:'text-green-700',  border:'border-green-200',  iconBg:'bg-green-100',  icon:'text-green-600',  iconName:'TrendingUp',  desc:'Penghasilan dari kegiatan operasional utama perusahaan' },
  'Beban':       { bg:'bg-orange-50', text:'text-orange-700', border:'border-orange-200', iconBg:'bg-orange-100', icon:'text-orange-600', iconName:'TrendingDown',desc:'Pengeluaran yang terjadi dalam proses menghasilkan pendapatan' },
};

/* ── Main Page ────────────────────────── */
function LihatCoaPage() {
  const coa = window.__COA__ || {};
  const [collapsed, setCollapsed] = useState(false);
  const currentPath = '/kas_keluar/coa';

  const tipeConf = TIPE_CONFIG[coa.tipe_akun] || {
    bg:'bg-slate-50', text:'text-slate-700', border:'border-slate-200',
    iconBg:'bg-slate-100', icon:'text-slate-600', iconName:'BookOpen', desc:'Tipe akun lainnya',
  };

  const isAktif = coa.status === 'Aktif';

  return (
    <div className="min-h-screen bg-slate-100">
      <Sidebar collapsed={collapsed} currentPath={currentPath}/>

      <div className={`sidebar-transition ${collapsed ? 'ml-16' : 'ml-64'}`}>
        <Topbar
          collapsed={collapsed}
          onToggle={() => setCollapsed(c => !c)}
          breadcrumbs={[
            { label:'Home',             href:'/' },
            { label:'Kas Keluar',       href:'#' },
            { label:'Chart of Accounts',href:'/kas_keluar/coa' },
            { label:'Detail Akun' },
          ]}
        />

        <main className="pt-16 min-h-screen">
          <div className="p-6 max-w-4xl mx-auto space-y-5 fade-in">

            {/* ── Page Header ── */}
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-3">
                <a href="/kas_keluar/coa"
                  className="p-2 rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-brand-600 hover:border-brand-300 transition-colors shadow-sm">
                  <Icon name="ArrowLeft" size={16}/>
                </a>
                <div>
                  <h1 className="text-xl font-bold text-slate-800">Detail Akun COA</h1>
                  <p className="text-sm text-slate-500 mt-0.5">Informasi lengkap akun keuangan</p>
                </div>
              </div>
              <div className="flex items-center gap-2">
                <a href={`/kas_keluar/edit_coa/${coa.id_coa}`}
                  className="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg transition-colors shadow-sm">
                  <Icon name="Pencil" size={15}/>
                  Edit
                </a>
              </div>
            </div>

            {/* ── Hero Card ── */}
            <div className={`rounded-2xl border ${tipeConf.border} ${tipeConf.bg} p-6 flex flex-col sm:flex-row sm:items-center gap-5`}>
              <div className={`${tipeConf.iconBg} rounded-2xl p-5 flex-shrink-0 w-fit`}>
                <Icon name={tipeConf.iconName} size={32} className={tipeConf.icon}/>
              </div>
              <div className="flex-1">
                <div className="flex flex-wrap items-center gap-2 mb-2">
                  <code className={`font-mono text-lg font-bold ${tipeConf.text} bg-white/70 px-3 py-1 rounded-lg border ${tipeConf.border}`}>
                    {coa.kode_coa}
                  </code>
                  <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium
                    ${isAktif ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-slate-200 text-slate-600 border border-slate-300'}`}>
                    <span className={`w-1.5 h-1.5 rounded-full ${isAktif ? 'bg-green-500' : 'bg-slate-400'}`}></span>
                    {coa.status}
                  </span>
                </div>
                <h2 className={`text-2xl font-bold ${tipeConf.text}`}>{coa.nama_coa}</h2>
                <p className="text-sm text-slate-500 mt-1">{tipeConf.desc}</p>
              </div>

              {/* Quick info chips */}
              <div className="flex sm:flex-col gap-2 flex-wrap">
                <div className="bg-white/70 rounded-xl px-4 py-3 text-center border border-white/80 shadow-sm min-w-[100px]">
                  <p className="text-xs text-slate-500 font-medium">Tipe Akun</p>
                  <p className={`text-sm font-bold mt-0.5 ${tipeConf.text}`}>{coa.tipe_akun}</p>
                </div>
                <div className="bg-white/70 rounded-xl px-4 py-3 text-center border border-white/80 shadow-sm min-w-[100px]">
                  <p className="text-xs text-slate-500 font-medium">Saldo Normal</p>
                  <p className={`text-sm font-bold mt-0.5 flex items-center justify-center gap-1
                    ${coa.saldo_normal === 'Debit' ? 'text-blue-700' : 'text-pink-700'}`}>
                    <Icon name={coa.saldo_normal === 'Debit' ? 'ArrowUpRight' : 'ArrowDownRight'} size={13}/>
                    {coa.saldo_normal}
                  </p>
                </div>
              </div>
            </div>

            {/* ── Detail Section ── */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-5">

              {/* Left: Detail info */}
              <div className="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm">
                <div className="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                  <Icon name="Info" size={16} className="text-brand-600"/>
                  <h3 className="font-semibold text-slate-800 text-sm">Informasi Akun</h3>
                </div>
                <div className="px-6">
                  <DetailRow label="ID Akun">
                    <span className="font-mono text-slate-500 text-xs bg-slate-100 px-2 py-1 rounded">
                      #{coa.id_coa}
                    </span>
                  </DetailRow>
                  <DetailRow label="Kode COA">
                    <code className="font-mono font-semibold text-brand-600 bg-brand-50 px-3 py-1 rounded-lg text-sm border border-brand-100">
                      {coa.kode_coa}
                    </code>
                  </DetailRow>
                  <DetailRow label="Nama Akun">
                    <span className="font-semibold text-slate-800">{coa.nama_coa}</span>
                  </DetailRow>
                  <DetailRow label="Tipe Akun">
                    <span className={`inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold border ${tipeConf.border} ${tipeConf.text} ${tipeConf.bg}`}>
                      <Icon name={tipeConf.iconName} size={12} className="mr-1.5"/>
                      {coa.tipe_akun}
                    </span>
                  </DetailRow>
                  <DetailRow label="Saldo Normal">
                    <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold border
                      ${coa.saldo_normal === 'Debit'
                        ? 'bg-blue-50 text-blue-700 border-blue-200'
                        : 'bg-pink-50 text-pink-700 border-pink-200'}`}>
                      <Icon name={coa.saldo_normal === 'Debit' ? 'ArrowUpRight' : 'ArrowDownRight'} size={13}/>
                      {coa.saldo_normal}
                    </span>
                  </DetailRow>
                  <DetailRow label="Status" last>
                    <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border
                      ${isAktif ? 'bg-green-50 text-green-700 border-green-200' : 'bg-slate-100 text-slate-500 border-slate-200'}`}>
                      <span className={`w-1.5 h-1.5 rounded-full ${isAktif ? 'bg-green-500' : 'bg-slate-400'}`}></span>
                      {coa.status}
                    </span>
                  </DetailRow>
                </div>
              </div>

              {/* Right: Aturan akuntansi */}
              <div className="space-y-4">

                {/* Aturan debit/kredit */}
                <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                  <div className="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
                    <Icon name="BookMarked" size={16} className="text-brand-600"/>
                    <h3 className="font-semibold text-slate-800 text-sm">Aturan Akuntansi</h3>
                  </div>
                  <div className="p-5 space-y-3">
                    {/* Debit */}
                    <div className={`rounded-xl p-3 border ${coa.saldo_normal === 'Debit' ? 'bg-blue-50 border-blue-200' : 'bg-slate-50 border-slate-200'}`}>
                      <div className="flex items-center justify-between mb-1">
                        <span className={`text-xs font-semibold flex items-center gap-1
                          ${coa.saldo_normal === 'Debit' ? 'text-blue-700' : 'text-slate-500'}`}>
                          <Icon name="ArrowUpRight" size={12}/>
                          DEBIT
                        </span>
                        {coa.saldo_normal === 'Debit' && (
                          <span className="text-[10px] font-bold text-blue-700 bg-blue-100 px-1.5 py-0.5 rounded-full">NORMAL</span>
                        )}
                      </div>
                      <p className="text-xs text-slate-600">
                        {coa.tipe_akun === 'Aset' || coa.tipe_akun === 'Beban'
                          ? '↑ Menambah saldo akun'
                          : '↓ Mengurangi saldo akun'}
                      </p>
                    </div>
                    {/* Kredit */}
                    <div className={`rounded-xl p-3 border ${coa.saldo_normal === 'Kredit' ? 'bg-pink-50 border-pink-200' : 'bg-slate-50 border-slate-200'}`}>
                      <div className="flex items-center justify-between mb-1">
                        <span className={`text-xs font-semibold flex items-center gap-1
                          ${coa.saldo_normal === 'Kredit' ? 'text-pink-700' : 'text-slate-500'}`}>
                          <Icon name="ArrowDownRight" size={12}/>
                          KREDIT
                        </span>
                        {coa.saldo_normal === 'Kredit' && (
                          <span className="text-[10px] font-bold text-pink-700 bg-pink-100 px-1.5 py-0.5 rounded-full">NORMAL</span>
                        )}
                      </div>
                      <p className="text-xs text-slate-600">
                        {coa.tipe_akun === 'Liabilitas' || coa.tipe_akun === 'Ekuitas' || coa.tipe_akun === 'Pendapatan'
                          ? '↑ Menambah saldo akun'
                          : '↓ Mengurangi saldo akun'}
                      </p>
                    </div>
                  </div>
                </div>

                {/* Quick Actions */}
                <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                  <div className="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
                    <Icon name="Zap" size={16} className="text-brand-600"/>
                    <h3 className="font-semibold text-slate-800 text-sm">Aksi Cepat</h3>
                  </div>
                  <div className="p-3 space-y-2">
                    <a href={`/kas_keluar/edit_coa/${coa.id_coa}`}
                      className="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-amber-50 hover:text-amber-700 transition-colors group">
                      <span className="p-1 rounded-lg bg-amber-50 text-amber-600 group-hover:bg-amber-100">
                        <Icon name="Pencil" size={14}/>
                      </span>
                      Edit Akun Ini
                    </a>

                    {isAktif ? (
                      <a href={`/kas_keluar/hapus_coa/${coa.id_coa}`}
                        className="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-amber-50 hover:text-amber-700 transition-colors group"
                        onClick={(e) => { if(!confirm('Nonaktifkan akun ini?')) e.preventDefault(); }}>
                        <span className="p-1 rounded-lg bg-amber-50 text-amber-500 group-hover:bg-amber-100">
                          <Icon name="PowerOff" size={14}/>
                        </span>
                        Nonaktifkan Akun
                      </a>
                    ) : (
                      <a href={`/kas_keluar/aktifkan_coa/${coa.id_coa}`}
                        className="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-green-50 hover:text-green-700 transition-colors group">
                        <span className="p-1 rounded-lg bg-green-50 text-green-600 group-hover:bg-green-100">
                          <Icon name="Power" size={14}/>
                        </span>
                        Aktifkan Akun
                      </a>
                    )}

                    <a href={`/kas_keluar/hapus_permanen_coa/${coa.id_coa}`}
                      className="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-red-50 hover:text-red-700 transition-colors group"
                      onClick={(e) => { if(!confirm('Hapus akun COA ini secara permanen? Data tidak dapat dikembalikan!')) e.preventDefault(); }}>
                      <span className="p-1 rounded-lg bg-red-50 text-red-500 group-hover:bg-red-100">
                        <Icon name="Trash2" size={14}/>
                      </span>
                      Hapus Permanen
                    </a>

                    <a href="/kas_keluar/coa"
                      className="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100 transition-colors group">
                      <span className="p-1 rounded-lg bg-slate-100 text-slate-500 group-hover:bg-slate-200">
                        <Icon name="List" size={14}/>
                      </span>
                      Kembali ke Daftar
                    </a>
                  </div>
                </div>

              </div>
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

ReactDOM.createRoot(document.getElementById('root')).render(<LihatCoaPage/>);
</script>
</body>
</html>
