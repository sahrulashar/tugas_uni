# 🌐 Panduan Setup Cloudflare Tunnel & Nginx Reverse Proxy (Sistem ERP CI4)

Panduan ini menjelaskan cara menghubungkan aplikasi Sistem ERP CodeIgniter 4 ke internet secara publik menggunakan **Cloudflare Tunnel (`cloudflared`)** dan **Nginx Reverse Proxy**.

---

## 🏛️ Arsitektur Alur Lalu Lintas (Traffic Flow)

```text
Pengunjung Internet (Browser)
            │
            ▼ [HTTPS Port 443 dengan SSL Otomatis Cloudflare]
    Cloudflare Edge Network
            │
            ▼ [Koneksi Tunnel Keluar / Outbound QUIC/HTTP2 - Tanpa Port Forwarding]
Container: cloudflared (cloudflare/cloudflared:latest)
            │
            ▼ [Jaringan Internal Docker: erp_network]
Container: nginx (nginx:alpine - Port 80)
            │   ├── Memulihkan IP Pengunjung Asli (CF-Connecting-IP)
            │   ├── Mengatur Security Headers & Buffer
            │   └── Menangani Upload Limit & Gzip
            │
            ├──────────────────────────┐
            ▼                          ▼
Container: app (CI4 PHP 8.3)   Container: phpmyadmin (GUI DB)
       (Port 80)                  (Port 80)
            │
            ▼
Container: db (MySQL 8.0)
```

### Keunggulan Setup Ini:
1. **Tidak Perlu Port Forwarding di Router**: Aman dari port scanner internet karena tidak ada port router/firewall yang dibuka.
2. **IP Publik Tetap Tersembunyi**: Alamat IP asli server Anda tidak pernah terekspos ke publik.
3. **Gratis SSL/TLS Otomatis**: Sertifikat HTTPS dikelola langsung oleh Cloudflare.
4. **IP Asli Pengunjung Tersimpan**: Header `CF-Connecting-IP` dipetakan oleh Nginx dan diteruskan ke CodeIgniter 4 sehingga log audit trail ERP mencatat IP pengunjung yang sebenarnya.

---

## 📋 Langkah-Langkah Konfigurasi (Cloudflare Zero Trust)

### Langkah 1: Buat Tunnel di Cloudflare Dashboard
1. Login ke akun Cloudflare Anda, lalu buka [Cloudflare Zero Trust](https://one.dash.cloudflare.com/).
2. Di menu navigasi sebelah kiri, pilih **Networks** > **Tunnels**.
3. Klik tombol **Add a tunnel** (atau **Create a tunnel**).
4. Pilih opsi **Cloudflared** > Klik **Next**.
5. Beri nama tunnel Anda, contoh: `sistem-erp-ci4` > Klik **Save tunnel**.

---

### Langkah 2: Ambil Token Tunnel
1. Pada halaman **Install and run a connector**, pilih tab **Docker**.
2. Anda akan melihat perintah docker run seperti:
   ```bash
   docker run cloudflare/cloudflared:latest tunnel --no-autoupdate run --token eyJhIjoiY2...xxxxxx
   ```
3. Salin kode token panjang setelah kata `--token` (mulai dari `eyJh...`).

---

### Langkah 3: Masukkan Token ke File `.env` Proyek
Buka file `.env` di folder proyek ini (atau buat dari `.env.docker`), lalu isi variabel `CLOUDFLARE_TUNNEL_TOKEN` dan domain `APP_URL`:

```env
# Port lokal Nginx
APP_PORT=8080

# Domain publik yang akan diarahkan ke ERP (gunakan https)
APP_URL=https://erp.domainanda.com/

# Masukkan token Cloudflare yang disalin pada Langkah 2
CLOUDFLARE_TUNNEL_TOKEN=eyJhIjoiY2...xxxxxx
```

---

### Langkah 4: Tambahkan Public Hostname di Cloudflare
Kembali ke Cloudflare Zero Trust Dashboard pada halaman tunnel Anda:
1. Buka tab **Public Hostname** > Klik **Add a public hostname**.
2. Masukkan rincian berikut:
   * **Subdomain**: `erp` (atau kosongkan jika domain utama)
   * **Domain**: Pilih domain Anda (misal `domainanda.com`)
   * **Path**: Biarkan kosong
   * **Type**: `HTTP`
   * **URL**: `nginx:80`  *(PENTING: Gunakan nama service docker `nginx:80`, BUKAN localhost!)*
3. Pada **Additional application settings** > **HTTP Settings** (opsional):
   * *No TLS Verify*: Biarkan default (karena komunikasi di internal Docker menggunakan HTTP)
4. Klik **Save hostname**.

*(Opsional: Jika ingin mengekspos phpMyAdmin via subdomain terpisah, tambahkan hostname kedua dengan Type `HTTP` dan URL `phpmyadmin:80` atau `nginx:80`)*

---

### Langkah 5: Jalankan Container Docker
Buka Terminal / PowerShell di folder proyek ini:

```bash
# Jalankan seluruh service (Nginx, Cloudflared, App, DB, phpMyAdmin)
docker compose up -d --build
```

Jika container sudah berjalan sebelumnya, cukup restart service cloudflared dan app:
```bash
docker compose up -d
docker compose restart cloudflared app
```

---

## 🔍 Cara Verifikasi & Monitoring

### 1. Cek Status Koneksi Tunnel:
```bash
docker compose logs -f cloudflared
```
* **Status Sukses**: Anda akan melihat pesan:
  `Registered tunnel connection` dan `Connection ... has been established`.
* **Jika Token Belum Diisi**: Container akan mencetak panduan setup dan berada dalam status idle menunggu token diisi.

### 2. Cek Log Akses Nginx:
```bash
docker compose logs -f nginx
```
Saat Anda mengakses `https://erp.domainanda.com`, Nginx akan mencatat request dengan IP asli pengunjung dan kode `CF-Ray`.

### 3. Tes Akses Browser:
* Buka browser dan kunjungi: `https://erp.domainanda.com`
* Halaman login ERP Sistem Kas Keluar akan terbuka dengan gembok hijau HTTPS aktif.

---

## 🛡️ Keamanan & Tips Tambahan

1. **Akses phpMyAdmin**:
   * Secara default, phpMyAdmin tetap dapat diakses di komputer lokal via `http://localhost:8081` atau `http://localhost:8080/phpmyadmin/`.
   * Jika tidak ingin phpMyAdmin bisa diakses dari publik via Cloudflare Tunnel, Anda tidak perlu menambahkan route hostname phpMyAdmin di dashboard Cloudflare.
2. **Upload Limit File Besar**:
   * Konfigurasi Nginx (`docker/nginx/nginx.conf` dan `default.conf`) telah diset ke `client_max_body_size 64M;` agar file bukti transaksi atau backup database dapat diunggah tanpa error `413 Request Entity Too Large`.
3. **Redirect Loop Prevention**:
   * File `app/Config/App.php` telah dikonfigurasi untuk mempercayai subnet Docker (`172.16.0.0/12`) sebagai trusted reverse proxy, sehingga CodeIgniter 4 mengenali protokol HTTPS secara akurat dari header `X-Forwarded-Proto`.
