# Sora — Inventory & Sales Management System

A high-performance Inventory & Sales Management System built with **Laravel 11**, **MySQL 8 / MariaDB**, **Blade**, **Vanilla CSS**, and **Chart.js**.

Designed with the **Organic Modern Commerce** aesthetic (light theme only, curated palette of Sage, Cream, Mustard, Brown, and Navy) ensuring strict WCAG 2.2 AA accessibility, data integrity, and atomic inventory transactions.

---

## System Requirements

- **PHP**: 8.2 or higher (PHP 8.5 tested)
- **Composer**: 2.x
- **Database**: MySQL 8.0+ or MariaDB 10.11+
- **Extensions**: `pdo_mysql`, `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `tokenizer`, `xml`

---

## Quick Start & Installation

### 1. Database Setup
Create database `sora_db` in MySQL / phpMyAdmin:
```sql
CREATE DATABASE `sora_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Ensure user credentials are configured in `sora/.env`:
```env
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sora_db
DB_USERNAME=root
DB_PASSWORD=
```

### 2. Install Dependencies & Setup App
```bash
cd sora
composer install
php artisan key:generate
php artisan migrate --seed
```

### 3. Start Development Server
```bash
php artisan serve
```
Open [http://localhost:8000](http://localhost:8000) in your web browser.

---

## Default User Accounts

| Role | Email | Password | Permissions |
|---|---|---|---|
| **Administrator** | `admin@sora.test` | `password` | Full access (CRUD categories, suppliers, products, users, reports, approvals) |
| **Cashier / Staff** | `staff@sora.test` | `password` | POS Quick Sale, view catalog & low stock, record sales & purchases |

---

## Architecture & Core Features

1. **Point of Sale (POS) Terminal**:
   - Interactive client-side product catalog with real-time text and category search.
   - Dynamic cart panel with live quantity controls and stock protection.
   - Single-click checkout that generates immutable sales receipts (`SAL-YYYYMMDD-XXXX`).

2. **Inventory Stock Lifecycle**:
   - **Purchases (`PUR-YYYYMMDD-XXXX`)**: Pending purchases preserve stock; completed purchases atomically increase inventory and update purchase costs.
   - **Sales (`SAL-YYYYMMDD-XXXX`)**: Real-time stock validation prevents overselling. Stock decrements atomically with `lockForUpdate()`.

3. **Role-Based Access Control**:
   - Built-in `RoleMiddleware` protecting administrative routes (`/categories`, `/suppliers`, `/users`, `/products/create`).
   - Staff is blocked with 403 Forbidden on restricted endpoints.

4. **Analytics Dashboard**:
   - Stat cards with real database values (Sales today, Sales this month, Daily transactions, Active products).
   - Responsive Chart.js bar and line charts for 7-day revenue and 6-month trends.
   - Top 5 selling products and Low Stock Watchlist.

5. **Design System (Organic Modern Commerce)**:
   - Vanilla CSS custom properties with strict contrast ratios.
   - Permitted glassmorphism on 4 floating surfaces only (topbar, mobile sticky total, toast, login card).
   - Double-submit prevention and tabular numbers on all financial amounts.

---

## Running Automated Tests

Run the complete feature and unit test suite:
```bash
cd sora
php artisan test
```

---

## Documentation

Full architectural specifications and user journey maps:
- [Architecture & ERD](file:///home/leon/Kuliah/Web/docs/ARCHITECTURE.md)
- [Design System & UI Tokens](file:///home/leon/Kuliah/Web/docs/DESIGN.md)
- [Product Requirements (PRD)](file:///home/leon/Kuliah/Web/docs/PRD.md)
- [Task Breakdown](file:///home/leon/Kuliah/Web/docs/TASKS.md)
