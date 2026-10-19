# 🐳 Panduan Menjalankan Sistem ERP dengan Docker, Nginx & Cloudflare Tunnel

Setup Docker ini telah dikonfigurasi lengkap menggunakan:
- 🛡️ **Nginx Reverse Proxy Gateway**: Titik masuk utama HTTP/HTTPS, proteksi header, buffer, dan pemulih IP asli Cloudflare.
- ☁️ **Cloudflare Tunnel (`cloudflared`)**: Ekspos aplikasi ke internet secara aman tanpa port forwarding / IP publik statis.
- 🐘 **Aplikasi PHP 8.3 (Apache + CodeIgniter 4)**: Backend aplikasi ERP yang stabil dan optimal.
- 🐬 **Database MySQL 8.0**: Auto-import skema tabel dan data awal (Master COA, Supplier, Karyawan, Transaksi, RBAC, dan Audit Trail).
- 🗄️ **phpMyAdmin**: Web GUI database yang dapat diakses lokal maupun lewat Nginx.

---

## 🚀 1. Cara Menjalankan (Quick Start)

Pastikan Docker Desktop aktif di komputer Anda, lalu buka Terminal / PowerShell di folder project ini:

```bash
# Build dan jalankan seluruh container di background
docker compose up -d --build
```

Setelah proses build selesai, seluruh layanan akan otomatis aktif:
* 🌐 **Aplikasi ERP (via Nginx Gateway)**: [http://localhost:8080](http://localhost:8080) atau [http://localhost:8080/46124026](http://localhost:8080/46124026)
* 🗄️ **phpMyAdmin (Direct Port)**: [http://localhost:8081](http://localhost:8081)
* 🗄️ **phpMyAdmin (via Nginx Gateway)**: [http://localhost:8080/phpmyadmin/](http://localhost:8080/phpmyadmin/)
* 🐬 **Port MySQL (Akses Host/DBeaver/Navicat)**: `localhost:3307`
* ☁️ **Cloudflare Tunnel**: Otomatis aktif jika `CLOUDFLARE_TUNNEL_TOKEN` diisi di file `.env`.

---

## ☁️ 2. Konfigurasi Cloudflare Tunnel (Online / Public Access)

Untuk mengakses aplikasi dari mana saja melalui domain publik Anda sendiri tanpa port forwarding:

1. Buka [Cloudflare Zero Trust Dashboard](https://one.dash.cloudflare.com/)
2. Masuk ke **Networks** > **Tunnels** > Klik **Create a tunnel**.
3. Pilih **Cloudflared**, beri nama tunnel (misal: `erp-ci4`).
4. Pada instruksi installer, salin nilai **Token** (karakter panjang setelah `--token`).
5. Buka file `.env` di proyek ini, masukkan token tersebut:
   ```env
   CLOUDFLARE_TUNNEL_TOKEN=eyJhIjoiY2...xxxxxx
   APP_URL=https://erp.domainanda.com/
   ```
6. Pada tab **Public Hostname** di Cloudflare Dashboard:
   - **Subdomain / Domain**: Masukkan domain yang Anda inginkan (misal `erp.domainanda.com`).
   - **Service Type**: `HTTP`
   - **URL**: `nginx:80`
7. Simpan dan jalankan ulang container tunnel:
   ```bash
   docker compose restart cloudflared
   ```
8. Aplikasi ERP kini dapat diakses secara global di `https://erp.domainanda.com` dengan SSL otomatis dari Cloudflare!

> 📖 **Panduan Lengkap & Bergambar**: Lihat panduan mendalam di [CLOUDFLARE_TUNNEL_GUIDE.md](file:///c:/Users/sahru/OneDrive/Documents/Sahruni_Ashar/sistem_erp_ci4/CLOUDFLARE_TUNNEL_GUIDE.md).

---

## 🔑 3. Kredensial Login

### A. Akun Pengguna Aplikasi ERP:
| Role / Jabatan | Username | Password | Deskripsi Akses |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin` | `admin123` | Akses penuh seluruh modul & RBAC |
| **Staf Finance** | `lisa` | `asil` | Akses Kas Keluar & Transaksi |
| **Supervisor** | `devi` | `ived` | Akses Transaksi & Verifikasi |

### B. Kredensial Database MySQL:
* **Host (dalam container)**: `db`
* **Host (dari luar container / host PC)**: `localhost:3307`
* **Database Name**: `kas_keluar`
* **User**: `root`
* **Password**: `root`

---

## 🛠️ 4. Perintah-Perintah Berguna (Docker Commands)

### Melihat Status Container:
```bash
docker compose ps
```

### Melihat Log Realtime Layanan Tertentu:
```bash
# Log Nginx reverse proxy
docker compose logs -f nginx

# Log Cloudflare Tunnel
docker compose logs -f cloudflared

# Log Aplikasi CodeIgniter
docker compose logs -f app
```

### Masuk ke Shell Container PHP / CodeIgniter:
```bash
docker compose exec app bash
```

### Menjalankan Unit Testing di dalam Docker:
```bash
docker compose exec app php vendor/bin/phpunit
```

### Menghentikan Container:
```bash
docker compose down
```

### Reset Ulang Database (Menghapus Volume Data & Import Ulang SQL Awal):
```bash
docker compose down -v
docker compose up -d
```

---

## 📁 5. Struktur Setup Docker

```text
├── Dockerfile                  # Image PHP 8.3 Apache + ekstensi CI4 (intl, mysqli, gd, zip, dll)
├── docker-compose.yml          # Orkestrasi 5 service (nginx, cloudflared, app, db, phpmyadmin)
├── .dockerignore               # Optimasi build context
├── .env.docker                 # Template konfigurasi environment container
├── docker/
│   ├── nginx/
│   │   ├── nginx.conf          # Konfigurasi inti Nginx, gzip, buffer, log format
│   │   └── conf.d/
│   │       ├── cloudflare.conf # Real-IP IP ranges Cloudflare & Docker subnet
│   │       └── default.conf    # VirtualHost proxy ke app:80 dan phpmyadmin:80
│   ├── cloudflared/
│   │   └── config.example.yml  # Template file konfigurasi Cloudflare Tunnel lokal (CLI mode)
│   ├── apache/
│   │   └── 000-default.conf    # VirtualHost Apache backend
│   ├── db/
│   │   └── init.sql            # Skema lengkap dan data awal (auto-import)
│   └── entrypoint.sh           # Script startup container & penyesuaian permission writable
```
