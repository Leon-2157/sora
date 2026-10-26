# Sora — Inventory & Sales Management System

A high-performance Inventory & Sales Management System built with **Laravel 11**, **MySQL 8 / MariaDB**, **Blade**, **Vanilla CSS**, and **Chart.js**.

Designed with the **Organic Modern Commerce** aesthetic (light theme only, curated palette of Sage, Cream, Mustard, Brown, and Navy) ensuring strict WCAG 2.2 AA accessibility, data integrity, and atomic inventory transactions.

---

## 1. Project Overview

**Sora** is an end-to-end web-based inventory and point-of-sale system tailored for retail and small-to-medium businesses. It bridges procurement workflows and retail sales with real-time stock synchronisation, strict role-based access control (Admin & Staff), and analytical insights into business revenue and inventory health.

- **Stack**: Laravel 11.x, MariaDB 11.x / MySQL 8.x, Blade Templates, Vanilla CSS, Vanilla JavaScript, Chart.js (CDN).
- **Architecture**: Service-oriented transaction pattern (`PurchaseService`, `SaleService`), atomic database transactions with row-level locking (`lockForUpdate()`), and Role-Based Access Control (`RoleMiddleware`).

---

## 2. Requirements

Before running the project, ensure your environment meets the following prerequisites:

- **PHP**: 8.2 or higher (PHP 8.5 tested & supported)
- **Composer**: 2.x
- **Database**: MySQL 8.0+ or MariaDB 10.11+ (dengan phpMyAdmin)
- **PHP Extensions**: `pdo_mysql` / `pdo_mariadb`, `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `tokenizer`, `xml`, `sqlite3` (for testing)

---

## 3. Installation

Clone repository dan install dependensi PHP melalui Composer:

```bash
git clone https://github.com/Leon-2157/sora.git
cd sora
composer install
```

---

## 4. Environment & Database Setup

1. **Import Database ke phpMyAdmin**:
   - Buka **phpMyAdmin** di browser Anda (`http://localhost/phpmyadmin`).
   - Buat database baru bernama `sora_db` (atau langsung klik tab **Import**).
   - Pilih dan import file SQL yang tersedia di project:
     ```
     database/sora_db.sql
     ```
   *(File ini secara otomatis membuat struktur 15 tabel dan mengisikan akun pengguna default).*

2. **Salin File Environment**:
   ```bash
   cp .env.example .env
   ```

3. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

4. **Sesuaikan Kredensial Database di `.env`**:
   Buka file `.env` dan pastikan konfigurasi database sesuai dengan pengaturan MySQL/MariaDB lokal Anda:
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

## 5. Migration & Seeding

Setelah database diimport dan file `.env` disesuaikan, jalankan migrasi Laravel untuk memastikan seluruh tabel terdaftar:

```bash
php artisan migrate
```

> **Alternatif (Fresh Install & Demo Data)**:  
> Jika ingin me-reset database dan men-generate ulang data katalog demo (produk, kategori, supplier) langsung via artisan CLI:
> ```bash
> php artisan migrate:fresh --seed
> ```

Jalankan server aplikasi lokal:
```bash
php artisan serve
```
Buka aplikasi di web browser: [http://localhost:8000](http://localhost:8000).

---

## 6. Demo Accounts

Setelah import SQL atau seeder dijalankan, gunakan akun berikut untuk masuk:

| Role | Email | Password | Permissions & Scope |
|---|---|---|---|
| **Administrator** | `admin@sora.test` | `password` | Full system access: manage categories, suppliers, catalog, user accounts, approve/cancel transactions, and view executive analytics. |
| **Cashier / Staff** | `staff@sora.test` | `password` | Operational access: Point of Sale (POS) terminal, view product catalogue & low stock alerts, process sales and record purchase drafts. Restricted from administrative modules (HTTP 403). |

---

## 7. Features

- **Point of Sale (POS) Terminal**:
  - Interactive 2-column cashier interface with instant product search (by Name/SKU) and category filtering.
  - Reactive client-side cart with real-time stock limits, quantity adjustments, and subtotal calculation.
  - Single-click checkout that atomically verifies stock, decrements inventory, and creates formatted sales receipts (`SAL-YYYYMMDD-XXXX`).
  - Printable sales receipt with printable view (`window.print()`).
- **Inventory & Transaction Lifecycle**:
  - **Purchases (`PUR-YYYYMMDD-XXXX`)**: Multi-item procurement orders. Pending orders preserve inventory; completed orders atomically increment stock and update unit costs.
  - **Sales (`SAL-YYYYMMDD-XXXX`)**: Real-time stock validation preventing overselling. Stock decrements atomically with `lockForUpdate()`.
  - Transaction immutability: Completed transactions cannot be edited or cancelled (audit trail integrity).
- **Low Stock Watchlist**:
  - Dedicated monitoring view displaying items with current stock $\le$ minimum stock threshold.
  - Calculates real-time **Deficit Gap** (`minimum_stock - stock`) with 1-click reorder shortcut to procurement.
- **Executive Analytics Dashboard**:
  - Real-time stat cards (Today's Sales, Monthly Sales, Daily Transactions, Active Products).
  - Responsive Chart.js analytics: 7-day sales bar chart and 6-month revenue trend line.
  - Top 5 selling products by quantity and revenue.
- **Master Data Management (Admin only)**:
  - Categories CRUD with deletion guard (cannot delete if products are attached).
  - Suppliers CRUD with deletion guard (cannot delete if referenced in purchases).
  - Products CRUD with automatic SKU uniqueness check and deletion protection.
  - User accounts management with self-deletion and last-admin safeguards.
- **Design System ("Organic Modern Commerce")**:
  - Pure Vanilla CSS (`public/css/app.css`) using curated tokens (Sage `#81A186`, Cream `#FCFFD0`, Mustard `#C4AC46`, Brown `#574749`, Navy `#0C0F22`).
  - WCAG 2.2 AA compliant contrast ratios.
  - Permitted glassmorphism strictly limited to 4 floating surfaces (topbar, mobile sticky total, toast, login card).
  - Client-side double-submit prevention on all forms.

---

## 8. Known Limitations

1. **Light Theme Only**: The UI is intentionally crafted strictly for light mode; dark mode is out of scope per product specification.
2. **Single Currency**: Currency formatting is exclusively configured for Indonesian Rupiah (`Rp 1.250.000`) using tabular figures.
3. **Standalone Single-Store Scope**: Built for single-store / single-warehouse inventory; multi-warehouse distribution or transfer orders are not supported in this version.
4. **Local Network / Online POS**: The POS terminal relies on direct connectivity to the local/intranet Laravel backend; offline-first IndexedDB synchronization is not implemented.
5. **Direct / Cash Payments**: Payment processing assumes instant direct/cash transactions; automated third-party payment gateway integration (e.g., Midtrans, Xendit) is out of scope.
6. **No External Bundler Required**: Does not require `npm run build` or Vite compiler; uses pure Vanilla CSS/JS directly served from `public/`.

---

## Running Automated Tests

Run the complete automated test suite (14 tests covering Auth, Role RBAC guards, and Atomic Inventory Transactions):

```bash
php artisan test
```

---

## LICENSE

[MIT](LICENSE)
