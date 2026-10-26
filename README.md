# Sora — Inventory & Sales Management System

Sistem Manajemen Inventaris & Penjualan (Point of Sale) berkinerja tinggi yang dibangun menggunakan **Laravel 11**, **MySQL 8 / MariaDB**, **Blade**, **Vanilla CSS**, dan **Chart.js**.

Dirancang dengan estetika **Organic Modern Commerce** (khusus tema terang/light theme dengan palet terkurasi: Sage, Cream, Mustard, Brown, dan Navy) yang menjamin standar aksesibilitas WCAG 2.2 AA, integritas data, serta transaksi inventaris yang atomik.

---

## 1. Gambaran Proyek

**Sora** adalah sistem inventaris dan kasir berbasis web menyeluruh (*end-to-end*) yang dirancang khusus untuk operasional ritel serta usaha kecil dan menengah (UMKM). Sistem ini menghubungkan alur kerja pengadaan barang (*procurement*) dengan penjualan kasir secara sinkron (*real-time*), dilengkapi kontrol akses berbasis peran (Admin & Kasir/Staff), serta analitik bisnis.

- **Teknologi**: Laravel 11.x, MariaDB 11.x / MySQL 8.x, Blade Templates, Vanilla CSS, Vanilla JavaScript, Chart.js (CDN).
- **Arsitektur**: Pola transaksi berbasis layanan (*Service-oriented* via `PurchaseService` dan `SaleService`), transaksi database atomik dengan penguncian baris (`lockForUpdate()`), serta kontrol akses berbasis peran (`RoleMiddleware`).

---

## 2. Persyaratan Sistem

Sebelum menjalankan aplikasi, pastikan komputer Anda telah memenuhi persyaratan berikut:

- **PHP**: Versi 8.2 atau lebih baru (telah diuji pada PHP 8.5)
- **Composer**: Versi 2.x
- **Database**: MySQL 8.0+ atau MariaDB 10.11+
- **Ekstensi PHP**: `pdo_mysql` / `pdo_mariadb`, `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `tokenizer`, `xml`, `sqlite3` (untuk pengujian otomatis)

---

## 3. Panduan Instalasi

Clone repository dan pasang dependensi paket PHP menggunakan Composer:

```bash
git clone https://github.com/Leon-2157/sora.git
cd sora
composer install
```

---

## 4. Konfigurasi Lingkungan (.env)

1. **Import Database**:
   - Buka **phpMyAdmin** di browser Anda atau gunakan terminal.
   - Buat database baru bernama `sora_db` atau langsung import file SQL berikut:
     ```text
     database/sora_db.sql
     ```
   *(File ini secara otomatis membuat struktur 15 tabel lengkap dengan data demo awal).*

2. **Salin File Environment**:
   ```bash
   cp .env.example .env
   ```

3. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

4. **Sesuaikan Pengaturan Database di `.env`**:
   Buka file `.env` dan pastikan kredensial database sesuai dengan server MariaDB/MySQL Anda:
   ```env
   APP_NAME=Sora
   APP_ENV=local
   APP_TIMEZONE=Asia/Jakarta
   APP_URL=http://localhost:8000

   DB_CONNECTION=mariadb
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sora_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

---

## 5. Migrasi & Data Awal

Setelah mengimpor database dan menyesuaikan file `.env`, jalankan migrasi Laravel untuk memastikan seluruh tabel terdaftar:

```bash
php artisan migrate
```

> **Catatan (Reset Bersih & Data Demo)**:  
> Jika Anda ingin me-reset total database dan mengisi ulang data katalog demo (kategori, pemasok, dan produk) secara otomatis via CLI:
> ```bash
> php artisan migrate:fresh --seed
> ```

Jalankan server aplikasi lokal:
```bash
php artisan serve
```
Akses aplikasi melalui browser di: [http://localhost:8000](http://localhost:8000).

---

## 6. Akun Demo

Database telah dilengkapi dengan dua akun pengguna berdasarkan peran masing-masing:

| Peran (Role) | Email | Password | Hak Akses & Cakupan |
|---|---|---|---|
| **Administrator** | `admin@sora.test` | `password` | Akses penuh sistem: mengelola kategori, supplier, produk, akun pengguna, persetujuan/pembatalan transaksi, dan analitik eksekutif. |
| **Cashier / Staff** | `staff@sora.test` | `password` | Akses operasional: terminal POS (Point of Sale), melihat katalog & peringatan stok menipis, memproses penjualan kasir, dan mencatat draf pembelian. Dibatasi dari menu administratif (HTTP 403). |

---

## 7. Fitur Unggulan

- **Point of Sale (POS) Terminal**:
  - Antarmuka kasir 2-kolom responsif dengan pencarian instan (berdasarkan Nama/SKU) serta filter kategori.
  - Keranjang belanja reaktif dengan proteksi batas stok *real-time*, pengatur kuantitas (`+`/`-`), dan hitungan subtotal otomatis.
  - Checkout satu tombol yang memverifikasi stok, memotong inventaris secara atomik, dan membuat struk penjualan (`SAL-YYYYMMDD-XXXX`).
  - Fitur cetak struk penjualan langsung ke printer kasir/kertas (`window.print()`).
- **Siklus Inventaris & Transaksi Atomik**:
  - **Pembelian / Pengadaan (`PUR-YYYYMMDD-XXXX`)**: Pesanan dengan status *pending* tidak mengubah stok; status *completed* otomatis menambah stok fisik dan memperbarui harga beli produk.
  - **Penjualan Kasir (`SAL-YYYYMMDD-XXXX`)**: Validasi ketersediaan stok ketat untuk mencegah *overselling*. Pemotongan stok dilakukan atomik via `lockForUpdate()`.
  - Imutabilitas Transaksi: Transaksi yang telah *completed* tidak dapat diedit atau dihapus sembarangan demi integritas audit keuangan.
- **Daftar Pantau Stok Menipis (Low Stock Watchlist)**:
  - Tampilan khusus memantau produk dengan stok saat ini $\le$ batas minimum keselamatan.
  - Menampilkan perhitungan langsung celah defisit stok (**Deficit Gap**) dengan tombol pintas pengadaan pesanan (*reorder*).
- **Dashboard Analitik Eksekutif**:
  - Kartu metrik *real-time* (Penjualan Hari Ini, Penjualan Bulan Ini, Transaksi Harian, Total Produk Aktif).
  - Grafik interaktif Chart.js: diagram batang penjualan 7 hari terakhir dan tren garis pendapatan 6 bulan terakhir.
  - Tabel 5 produk terlaris berdasarkan kuantitas dan total omzet.
- **Manajemen Data Master (Khusus Admin)**:
  - CRUD Kategori dengan pengaman hapus (tidak bisa dihapus jika masih digunakan oleh produk).
  - CRUD Supplier dengan pengaman hapus (tidak bisa dihapus jika terkait data pengadaan).
  - CRUD Produk dengan pengecekan keunikan SKU otomatis dan proteksi penghapusan riwayat transaksi.
  - Manajemen Pengguna dengan proteksi pencegahan hapus akun sendiri dan proteksi admin terakhir.
- **Sistem Desain ("Organic Modern Commerce")**:
  - Menggunakan Vanilla CSS murni (`public/css/app.css`) dengan token terstandarisasi.
  - Rasio kontras teks memenuhi standar aksesibilitas WCAG 2.2 AA.
  - Efek *glassmorphism* proporsional (dibatasi hanya pada 4 elemen: topbar, bar total mobile, toast, kartu login).
  - Proteksi *double-submit* pada semua tombol formulir untuk mencegah duplikasi data transaksi.

---

## 8. Batasan Sistem

1. **Khusus Tema Terang (Light Theme Only)**: Desain antarmuka secara sengaja dibuat eksklusif untuk mode terang; mode gelap tidak disediakan sesuai spesifikasi desain awal.
2. **Format Mata Uang Tunggal**: Pemformatan harga dikhususkan untuk Rupiah Indonesia (`Rp 1.250.000`) dengan tipografi angka tabular (*tabular figures*).
3. **Cakupan Toko Tunggal (*Single-Store*)**: Dirancang untuk manajemen satu toko/gudang utama; transfer antar cabang gudang (*multi-warehouse*) belum tercakup pada versi ini.
4. **Koneksi Jaringan Lokal / Online POS**: Terminal POS memerlukan konektivitas ke server backend Laravel lokal/intranet (bukan aplikasi PWA *offline-first*).
5. **Pembayaran Tunai / Langsung**: Sistem mencatat pembayaran langsung (kas/tunai); integrasi otomatis ke *payment gateway* pihak ketiga (seperti Midtrans/Xendit) belum terpasang.
6. **Bebas Kompilasi Node.js**: Tidak memerlukan `npm run build` atau kompilator Vite di server; seluruh asset CSS dan JavaScript Vanilla langsung dilayani dari direktori `public/`.

---

## Pengujian Otomatis

Jalankan seluruh rangkaian pengujian otomatis (14 skenario pengujian yang mencakup Autentikasi, Guard Otorisasi Role RBAC, serta Transaksi Atomik Inventaris):

```bash
php artisan test
```

---

## Lisensi

[MIT](LICENSE)
