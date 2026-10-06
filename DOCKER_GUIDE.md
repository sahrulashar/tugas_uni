# 🐳 Panduan Menjalankan Sistem ERP dengan Docker

Setup Docker ini telah dikonfigurasi lengkap menggunakan **PHP 8.3 Apache**, **MySQL 8.0**, dan **phpMyAdmin**, serta otomatis mengimpor seluruh skema tabel dan data awal (Master COA, Supplier, Karyawan, Transaksi, RBAC, dan Audit Trail).

---

## 🚀 1. Cara Menjalankan (Quick Start)

Pastikan Docker Desktop aktif di komputer Anda, lalu buka Terminal / PowerShell di folder project ini:

```bash
# Build dan jalankan seluruh container di background
docker compose up -d --build
```

Setelah proses build selesai, seluruh layanan akan otomatis aktif:
* 🌐 **Aplikasi ERP**: [http://localhost:8080](http://localhost:8080) atau [http://localhost:8080/46124026](http://localhost:8080/46124026)
* 🗄️ **phpMyAdmin (Database GUI)**: [http://localhost:8081](http://localhost:8081)
* 🐬 **Port MySQL (Akses Host/DBeaver/Navicat)**: `localhost:3307`

---

## 🔑 2. Kredensial Login

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

## 🛠️ 3. Perintah-Perintah Berguna (Docker Commands)

### Melihat Status Container:
```bash
docker compose ps
```

### Melihat Log Realtime Aplikasi:
```bash
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
Jika ingin mengulang database dari nol menggunakan skema `docker/db/init.sql`:
```bash
docker compose down -v
docker compose up -d
```

---

## 📁 4. Struktur Setup Docker

```text
├── Dockerfile                  # Image PHP 8.3 Apache + ekstensi CI4 (intl, mysqli, gd, zip, dll)
├── docker-compose.yml          # Konfigurasi orkestrasi service (app, db, phpmyadmin)
├── .dockerignore               # Optimasi build context
├── .env.docker                 # Template konfigurasi environment container
├── docker/
│   ├── apache/
│   │   └── 000-default.conf    # VirtualHost Apache mengarah ke /public
│   ├── db/
│   │   └── init.sql            # Skema lengkap dan data awal (auto-import saat pertama kali dijalankan)
│   └── entrypoint.sh           # Script startup container & penyesuaian permission writable
```
