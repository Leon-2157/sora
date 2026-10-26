# Product Requirements Document (PRD)

## Sora — Inventory & Sales Management System

**Version:** 1.0  
**Date:** 2026-10-27  
**Stack:** Laravel 11 · MySQL · Blade · Vanilla CSS/JS  
**Database:** `sora_db`

---

## 1. Project Overview

Sora is an integrated inventory and sales management web application for a small distribution business. The system manages products, suppliers, purchase (stock-in) transactions, and sales (stock-out) transactions with automatic stock tracking and role-based access control.

### 1.1 Problem Statement

Manual record-keeping causes:

- Stock counts out of sync with actual transactions
- Out-of-stock products still being sold
- Sales history difficult to trace
- Owner and staff require different access levels
- Owner needs sales summaries and reports

### 1.2 System Objectives

- Record master data and transactions consistently
- Automatically update stock when transactions are confirmed
- Enforce role-based access for Admin and Staff
- Provide dashboards with real-time business metrics

### 1.3 Target Users

| Role | Description |
|------|-------------|
| **Admin** | Business owner. Full access to all data, master data management, user management, all transactions, dashboard, reports, and cancellation rights. |
| **Staff** | Cashier/warehouse. Limited access: view products, create & complete purchases and sales, view own transactions. Cannot manage roles, delete master data, or cancel transactions. |

---

## 2. Functional Requirements

### 2.1 Authentication

| ID | Requirement |
|----|-------------|
| AUTH-01 | Login with email and password |
| AUTH-02 | Logout with session destruction |
| AUTH-03 | Password hashing (bcrypt) |
| AUTH-04 | Protected routes — guests redirected to login |
| AUTH-05 | Role-based middleware for admin-only routes |

### 2.2 User Management (Admin Only)

| ID | Requirement |
|----|-------------|
| USER-01 | List all users with pagination |
| USER-02 | Create new user (name, email, password, role) |
| USER-03 | Edit user details |
| USER-04 | Delete user (cannot delete self) |
| USER-05 | Minimum 1 admin seed account |

### 2.3 Category Management (Admin Only)

| ID | Requirement |
|----|-------------|
| CAT-01 | List categories with pagination and search |
| CAT-02 | Create category (unique name, required) |
| CAT-03 | Edit category |
| CAT-04 | Soft delete category |
| CAT-05 | Show soft-deleted status in UI |

### 2.4 Product Management

| ID | Requirement | Admin | Staff |
|----|-------------|-------|-------|
| PROD-01 | List products with search, filter by category, pagination | ✅ | ✅ (view only) |
| PROD-02 | Create product (SKU unique, category, name, purchase_price, selling_price, stock, minimum_stock) | ✅ | ❌ |
| PROD-03 | Edit product | ✅ | ❌ |
| PROD-04 | Soft delete product | ✅ | ❌ |
| PROD-05 | View product detail | ✅ | ✅ |
| PROD-06 | Low-stock indicator (stock ≤ minimum_stock) | ✅ | ✅ |
| PROD-07 | Show soft-deleted status in UI | ✅ | ❌ |

### 2.5 Supplier Management (Admin Only)

| ID | Requirement |
|----|-------------|
| SUP-01 | List suppliers with search and pagination |
| SUP-02 | Create supplier (name, phone, email) |
| SUP-03 | Edit supplier |
| SUP-04 | Soft delete supplier |
| SUP-05 | Show soft-deleted status in UI |

### 2.6 Purchase Transactions (Stock-In)

| ID | Requirement | Admin | Staff |
|----|-------------|-------|-------|
| PUR-01 | List all purchases with search, date filter, status filter, pagination | ✅ | ✅ (own only) |
| PUR-02 | Create purchase: select supplier, add multiple items (product, quantity, unit_cost) | ✅ | ✅ |
| PUR-03 | Auto-generate purchase_number (`PUR-YYYYMMDD-XXXX`) | ✅ | ✅ |
| PUR-04 | Server-side calculation of subtotal and total | ✅ | ✅ |
| PUR-05 | Initial status: `pending` | ✅ | ✅ |
| PUR-06 | Confirm (complete) purchase → stock increases | ✅ | ✅ |
| PUR-07 | Cancel pending purchase (no stock change) | ✅ | ❌ |
| PUR-08 | View purchase detail with all items | ✅ | ✅ |
| PUR-09 | Completed/cancelled status is final | ✅ | ✅ |

### 2.7 Sales Transactions (Stock-Out)

| ID | Requirement | Admin | Staff |
|----|-------------|-------|-------|
| SAL-01 | List all sales with search, date filter, status filter, pagination | ✅ | ✅ (own only) |
| SAL-02 | Create sale: select products, quantity, unit_price auto-filled from selling_price | ✅ | ✅ |
| SAL-03 | Auto-generate sale_number (`SAL-YYYYMMDD-XXXX`) | ✅ | ✅ |
| SAL-04 | Server-side calculation of subtotal and total | ✅ | ✅ |
| SAL-05 | Stock validation: reject if insufficient stock | ✅ | ✅ |
| SAL-06 | Initial status: `pending` | ✅ | ✅ |
| SAL-07 | Confirm (complete) sale → stock decreases | ✅ | ✅ |
| SAL-08 | Cancel pending sale (no stock change) | ✅ | ❌ |
| SAL-09 | View sale detail with all items | ✅ | ✅ |
| SAL-10 | Completed/cancelled status is final | ✅ | ✅ |

### 2.8 Dashboard — Admin

| ID | Widget |
|----|--------|
| DASH-A01 | Total sales revenue today |
| DASH-A02 | Total sales revenue this month |
| DASH-A03 | Total sales transactions today |
| DASH-A04 | Total transactions (purchases + sales) |
| DASH-A05 | Products with low stock (stock ≤ minimum_stock) |
| DASH-A06 | Sales chart — last 7 days |
| DASH-A07 | Sales chart — monthly |
| DASH-A08 | Top 5 best-selling products |
| DASH-A09 | Recent transactions list |

### 2.9 Dashboard — Staff

| ID | Widget |
|----|--------|
| DASH-S01 | Quick sale form (POS-style) |
| DASH-S02 | Staff's own sales total today |
| DASH-S03 | Products with low stock (warning) |
| DASH-S04 | Staff's recent sales transactions |

### 2.10 Search, Filter & Pagination

| ID | Requirement |
|----|-------------|
| SRC-01 | Search products by SKU or name |
| SRC-02 | Filter products by category |
| SRC-03 | Filter transactions by date range |
| SRC-04 | Filter transactions by status |
| SRC-05 | Pagination on all list pages (using Laravel `paginate()`) |
| SRC-06 | Preserve query filters across pagination |

---

## 3. Business Rules

| ID | Rule |
|----|------|
| BR-01 | Product SKU must be unique (enforced at database and application level) |
| BR-02 | Prices and quantities must be positive numbers; transaction quantity > 0 |
| BR-03 | Sales cannot exceed available stock |
| BR-04 | Stock updates only when transaction status changes to `completed` |
| BR-05 | Transaction totals are calculated server-side, never trusted from browser input |
| BR-06 | Database transactions (DB::transaction) wrap header + items + stock changes atomically |
| BR-07 | Completed transactions are final — cannot be edited or cancelled |
| BR-08 | Cancelled transactions are final |
| BR-09 | Only pending transactions can be cancelled |
| BR-10 | Only admin can cancel transactions |
| BR-11 | Products used in transactions use soft delete, not hard delete |
| BR-12 | Cancellation of purchase: only allowed when status is `pending`, no stock reversal needed |
| BR-13 | Cancellation of sale: only allowed when status is `pending`, no stock reversal needed |
| BR-14 | Prevent concurrent overselling with stock validation inside DB transaction |

---

## 4. Non-Functional Requirements

| ID | Requirement |
|----|-------------|
| NFR-01 | Server-side validation with clear error messages |
| NFR-02 | CSRF protection on all forms |
| NFR-03 | Output escaping (Blade `{{ }}`) |
| NFR-04 | Password hashing (bcrypt) |
| NFR-05 | Credentials in `.env`, commit only `.env.example` |
| NFR-06 | No stack traces or sensitive info in production |
| NFR-07 | Responsive layout |
| NFR-08 | Reusable Blade layouts with navigation and flash messages |
| NFR-09 | Eloquent relationships; avoid unnecessary duplicate queries |
| NFR-10 | Migration-first approach: no manual database setup |
| NFR-11 | Named routes and resource routes |
| NFR-12 | Middleware/policy/gate for authorization |
| NFR-13 | Application runnable from README on a clean environment |

---

## 5. Database Schema

### 5.1 Entity-Relationship Summary

```
users (1) ──── (N) purchases
users (1) ──── (N) sales
categories (1) ──── (N) products
suppliers (1) ──── (N) purchases
purchases (1) ──── (N) purchase_items
sales (1) ──── (N) sale_items
products (1) ──── (N) purchase_items
products (1) ──── (N) sale_items
```

### 5.2 Tables

| Table | Columns | Notes |
|-------|---------|-------|
| `users` | id, name, email (unique), password, role (ENUM: admin/staff), timestamps, soft_deletes | Seed: 1 admin, 1 staff |
| `categories` | id, name (unique), timestamps, soft_deletes | |
| `products` | id, category_id (FK), sku (unique), name, purchase_price, selling_price, stock, minimum_stock, timestamps, soft_deletes | |
| `suppliers` | id, name, phone, email, timestamps, soft_deletes | |
| `purchases` | id, supplier_id (FK), user_id (FK), purchase_number (unique), total, status (ENUM), purchased_at, timestamps | |
| `purchase_items` | id, purchase_id (FK), product_id (FK), quantity, unit_cost, subtotal, timestamps | |
| `sales` | id, user_id (FK), sale_number (unique), total, status (ENUM), sold_at, timestamps | |
| `sale_items` | id, sale_id (FK), product_id (FK), quantity, unit_price, subtotal, timestamps | |

---

## 6. UI Pages

### Authentication & Master Data
- Login page
- Dashboard (role-specific)
- Product: index / create / edit / detail
- Category: index / create / edit
- Supplier: index / create / edit
- User management: index / create / edit (Admin only)
- Low-stock product list

### Transactions & Reports
- Purchase: list / create / detail
- Sales: list / create / detail
- Add multiple items per transaction (dynamic form)
- Transaction status and detail view
- Search and date filters
- Pagination
- Validation and error state display
- Responsive layout

---

## 7. Deliverables

- Source code (Laravel) in GitHub repository
- Database migrations and seeders
- README.md with step-by-step installation
- ERD and relation explanation
- Screenshots of main pages and transactions
- Test cases and test result evidence
- Demo account (no personal credentials)
- Demo and code ownership interview

### 7.1 README Sections

1. Project overview
2. Requirements
3. Installation
4. Environment setup
5. Migration & seeding
6. Demo account
7. Features
8. Known limitations

---

## 8. Assessment Criteria

- Application runnable from README on clean environment
- Login and role-based access working
- Admin can manage categories, products, and suppliers
- Purchase transactions increase stock correctly
- Sales transactions decrease stock correctly
- Sales with insufficient stock rejected without data changes
- Relations, migration, seeder, search, and pagination functional
- Dashboard pulls actual data from database
- No secrets committed to repository
