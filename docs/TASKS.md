# Task Breakdown

## Sora — Inventory & Sales Management System

**Version:** 1.0  
**Date:** 2026-10-27  
**Estimated Total:** 40–55 hours

---

## Phase 1: Project Setup & Foundation

### TASK-001: Laravel Project Initialization
- **Priority:** Critical
- **Estimate:** 1h
- **Steps:**
  1. Create Laravel 11 project: `composer create-project laravel/laravel sora`
  2. Configure `.env` with `sora_db` database
  3. Set timezone to `Asia/Jakarta`
  4. Configure `.env.example` (no secrets)
  5. Initialize Git repository, set up `.gitignore`
- **Acceptance:** `php artisan serve` starts without errors

### TASK-002: Database Migrations
- **Priority:** Critical
- **Estimate:** 2h
- **Steps:**
  1. Create `users` migration (add `role` ENUM, `soft_deletes`)
  2. Create `categories` migration (unique name, soft_deletes)
  3. Create `products` migration (FK to categories, unique SKU, soft_deletes)
  4. Create `suppliers` migration (soft_deletes)
  5. Create `purchases` migration (FKs to suppliers & users, unique purchase_number, ENUM status)
  6. Create `purchase_items` migration (FKs to purchases & products)
  7. Create `sales` migration (FK to users, unique sale_number, ENUM status)
  8. Create `sale_items` migration (FKs to sales & products)
  9. Run `php artisan migrate` — all tables created
- **Acceptance:** `php artisan migrate:fresh` completes cleanly

### TASK-003: Eloquent Models & Relationships
- **Priority:** Critical
- **Estimate:** 2h
- **Steps:**
  1. `User` model — `$fillable`, SoftDeletes, `hasMany(Purchase)`, `hasMany(Sale)`
  2. `Category` model — `$fillable`, SoftDeletes, `hasMany(Product)`
  3. `Product` model — `$fillable`, SoftDeletes, `belongsTo(Category)`, `hasMany(PurchaseItem)`, `hasMany(SaleItem)`
  4. `Supplier` model — `$fillable`, SoftDeletes, `hasMany(Purchase)`
  5. `Purchase` model — `$fillable`, `belongsTo(Supplier)`, `belongsTo(User)`, `hasMany(PurchaseItem)`
  6. `PurchaseItem` model — `$fillable`, `belongsTo(Purchase)`, `belongsTo(Product)`
  7. `Sale` model — `$fillable`, `belongsTo(User)`, `hasMany(SaleItem)`
  8. `SaleItem` model — `$fillable`, `belongsTo(Sale)`, `belongsTo(Product)`
- **Acceptance:** Relationships testable via `php artisan tinker`

### TASK-004: User Seeder
- **Priority:** Critical
- **Estimate:** 30min
- **Steps:**
  1. Create `UserSeeder`
  2. Seed admin: `admin@sora.test` / `password` / role: admin
  3. Seed staff: `staff@sora.test` / `password` / role: staff
  4. Register in `DatabaseSeeder`
- **Acceptance:** `php artisan db:seed` creates 2 users

### TASK-005: Blade Layout & CSS Foundation
- **Priority:** Critical
- **Estimate:** 3h
- **Steps:**
  1. Create `layouts/app.blade.php` — sidebar nav, topbar, main content area
  2. Create `public/css/app.css` — design system (colors, typography, spacing, components)
  3. Create `public/js/app.js` — sidebar toggle, flash message dismiss
  4. Include navigation links (role-conditional)
  5. Flash message display (success/error)
  6. Responsive sidebar (collapsible on mobile)
- **Acceptance:** Layout renders with navigation, role-based menu items

---

## Phase 2: Authentication & Authorization

### TASK-006: Login / Logout
- **Priority:** Critical
- **Estimate:** 2h
- **Steps:**
  1. Create `LoginController` with `showLoginForm()`, `login()`, `logout()`
  2. Create `auth/login.blade.php` (email, password, CSRF)
  3. Validate credentials, redirect to dashboard
  4. Logout destroys session, redirects to login
  5. Guest middleware on login route
  6. Auth middleware on all other routes
- **Acceptance:** Login/logout flow works, guests redirected

### TASK-007: Role Middleware
- **Priority:** Critical
- **Estimate:** 1h
- **Steps:**
  1. Create `RoleMiddleware`
  2. Register middleware alias in `bootstrap/app.php`
  3. Check `auth()->user()->role` against required role
  4. Abort 403 if unauthorized
  5. Apply to admin-only route groups
- **Acceptance:** Staff cannot access admin routes, gets 403

---

## Phase 3: Master Data CRUD

### TASK-008: Category CRUD (Admin Only)
- **Priority:** High
- **Estimate:** 2h
- **Steps:**
  1. Create `CategoryController` (resource)
  2. Create `CategoryRequest` — name required, unique
  3. Create views: `index` (search, pagination), `create`, `edit`
  4. Soft delete with status indicator
  5. Admin-only middleware
- **Acceptance:** Full CRUD works, unique validation, soft delete, pagination

### TASK-009: Supplier CRUD (Admin Only)
- **Priority:** High
- **Estimate:** 2h
- **Steps:**
  1. Create `SupplierController` (resource)
  2. Create `SupplierRequest` — name required, phone, email
  3. Create views: `index` (search, pagination), `create`, `edit`
  4. Soft delete with status indicator
  5. Admin-only middleware
- **Acceptance:** Full CRUD works, soft delete, pagination

### TASK-010: Product CRUD
- **Priority:** High
- **Estimate:** 3h
- **Steps:**
  1. Create `ProductController` (resource)
  2. Create `ProductRequest` — SKU unique, category exists, prices ≥ 0, stock ≥ 0
  3. Create views: `index` (search by SKU/name, filter by category, pagination), `create`, `edit`, `show`
  4. Low-stock badge (stock ≤ minimum_stock)
  5. Soft delete with status indicator (admin only)
  6. Staff can view index and detail only
- **Acceptance:** CRUD works, validation, category filter, low-stock indicator

### TASK-011: User Management (Admin Only)
- **Priority:** High
- **Estimate:** 2h
- **Steps:**
  1. Create `UserController` (resource)
  2. Create `UserRequest` — name, email unique, password (optional on edit), role ENUM
  3. Create views: `index` (pagination), `create`, `edit`
  4. Cannot delete self
  5. Admin-only middleware
- **Acceptance:** CRUD works, unique email validation, self-delete blocked

---

## Phase 4: Transaction System

### TASK-012: Purchase Service Layer
- **Priority:** Critical
- **Estimate:** 3h
- **Steps:**
  1. Create `PurchaseService`
  2. Implement `createPurchase()`:
     - Generate `PUR-YYYYMMDD-XXXX` number
     - DB::transaction: create header + items
     - Server-side subtotal/total calculation
  3. Implement `completePurchase()`:
     - Validate status == pending
     - DB::transaction: increase stock per item, set completed
  4. Implement `cancelPurchase()`:
     - Validate status == pending
     - Set status = cancelled
- **Acceptance:** Purchase lifecycle works atomically

### TASK-013: Purchase UI
- **Priority:** Critical
- **Estimate:** 3h
- **Steps:**
  1. Create `PurchaseController` — index, create, store, show, complete, cancel
  2. Create `PurchaseRequest`
  3. Create `purchases/index.blade.php` — list with search, date filter, status filter, pagination
  4. Create `purchases/create.blade.php` — dynamic item rows (JS), supplier select, product select, quantity, unit_cost
  5. Create `purchases/show.blade.php` — detail with items, status, action buttons
  6. Admin sees all, Staff sees own
  7. Cancel button only for admin, only on pending
  8. Complete button on pending only
- **Acceptance:** Full purchase workflow including dynamic items

### TASK-014: Sale Service Layer
- **Priority:** Critical
- **Estimate:** 3h
- **Steps:**
  1. Create `SaleService`
  2. Implement `createSale()`:
     - Generate `SAL-YYYYMMDD-XXXX` number
     - DB::transaction: create header + items
     - Unit price from product.selling_price
     - Server-side subtotal/total calculation
  3. Implement `completeSale()`:
     - Validate status == pending
     - DB::transaction with pessimistic locking:
       - Check stock >= quantity for each item
       - Decrease stock
       - Set completed
     - On insufficient stock: rollback, return error
  4. Implement `cancelSale()`:
     - Validate status == pending
     - Set status = cancelled
- **Acceptance:** Sale lifecycle works, stock validated, overselling prevented

### TASK-015: Sale UI
- **Priority:** Critical
- **Estimate:** 3h
- **Steps:**
  1. Create `SaleController` — index, create, store, show, complete, cancel
  2. Create `SaleRequest`
  3. Create `sales/index.blade.php` — list with search, date filter, status filter, pagination
  4. Create `sales/create.blade.php` — dynamic item rows (JS), product select (show stock), quantity
  5. Create `sales/show.blade.php` — detail with items, status, action buttons
  6. Admin sees all, Staff sees own
  7. Cancel button only for admin, only on pending
  8. Complete button on pending only
  9. Insufficient stock error display
- **Acceptance:** Full sales workflow, stock validation shown to user

---

## Phase 5: Dashboards

### TASK-016: Admin Dashboard
- **Priority:** High
- **Estimate:** 4h
- **Steps:**
  1. Create `DashboardController@index` — route to admin or staff view based on role
  2. Admin dashboard queries:
     - Total sales revenue today (SUM of completed sales today)
     - Total sales revenue this month
     - Total sales transactions today (COUNT)
     - Total transactions all-time (purchases + sales COUNT)
     - Low-stock products (stock ≤ minimum_stock)
     - Sales data last 7 days (for chart)
     - Monthly sales data (for chart)
     - Top 5 products by quantity sold
     - Recent 10 transactions
  3. Create `dashboard/admin.blade.php`
  4. Stat cards at top
  5. Charts using Chart.js (CDN)
  6. Tables for low-stock and recent transactions
- **Acceptance:** All widgets show real data from database, charts render

### TASK-017: Staff Dashboard
- **Priority:** High
- **Estimate:** 3h
- **Steps:**
  1. Staff dashboard queries:
     - Staff's own sales total today
     - Staff's recent sales (last 10)
     - Low-stock products
  2. Create `dashboard/staff.blade.php`
  3. Quick sale section: product search/select, quantity, add to cart, submit
  4. Implement POS-style quick sale (JS cart functionality)
  5. Submit creates sale via SaleController
- **Acceptance:** POS form works, staff sees own data only

---

## Phase 6: Polish & Finalization

### TASK-018: Transaction Number Generation
- **Priority:** High
- **Estimate:** 1h
- **Steps:**
  1. Helper or service method to generate `PUR-YYYYMMDD-XXXX`
  2. Query latest number for the day, increment
  3. Handle race conditions (unique constraint as safety net)
  4. Same for `SAL-YYYYMMDD-XXXX`
- **Acceptance:** Numbers are sequential per day, unique constraint holds

### TASK-019: Search & Filter Implementation
- **Priority:** High
- **Estimate:** 2h
- **Steps:**
  1. Product search: where `name LIKE %query%` or `sku LIKE %query%`
  2. Product filter: where `category_id = ?`
  3. Transaction date filter: whereBetween `purchased_at`/`sold_at`
  4. Transaction status filter: where `status = ?`
  5. Preserve query params in pagination links (`appends()`)
- **Acceptance:** All filters work, pagination preserves filters

### TASK-020: UI Polish & Responsive Design
- **Priority:** Medium
- **Estimate:** 4h
- **Steps:**
  1. Refine CSS: consistent spacing, colors, hover states
  2. Mobile responsive: collapsible sidebar, stacked cards
  3. Status badges (pending=yellow, completed=green, cancelled=red)
  4. Low-stock warning badges
  5. Soft-deleted item visual indicator
  6. Loading states for form submissions
  7. Confirmation dialogs for delete/cancel actions
  8. Empty states for lists with no data
- **Acceptance:** Looks polished on desktop and mobile

### TASK-021: README & Documentation
- **Priority:** High
- **Estimate:** 1h
- **Steps:**
  1. Write README.md with sections:
     - Project overview
     - Requirements (PHP, Composer, MySQL)
     - Installation steps
     - Environment setup
     - Migration & seeding
     - Demo accounts
     - Features list
     - Known limitations
  2. Verify installation works from scratch following README
- **Acceptance:** Fresh clone → README → working app

### TASK-022: Testing & QA
- **Priority:** High
- **Estimate:** 3h
- **Steps:**
  1. Manual test: Auth flow (login, logout, wrong password)
  2. Manual test: Product CRUD + SKU uniqueness
  3. Manual test: Purchase lifecycle (create → complete → stock increases)
  4. Manual test: Sale lifecycle (create → complete → stock decreases)
  5. Manual test: Insufficient stock rejection
  6. Manual test: Atomicity (if one item fails, whole transaction rolls back)
  7. Manual test: Authorization (staff blocked from admin routes)
  8. Manual test: Search and pagination
  9. Take screenshots for submission
  10. Document test results
- **Acceptance:** All test scenarios pass

---

## Task Dependency Graph

```
TASK-001 (Setup)
    ├── TASK-002 (Migrations)
    │   └── TASK-003 (Models)
    │       └── TASK-004 (Seeder)
    │           └── TASK-006 (Auth)
    │               └── TASK-007 (Role Middleware)
    │                   ├── TASK-008 (Categories)
    │                   ├── TASK-009 (Suppliers)
    │                   ├── TASK-010 (Products)
    │                   ├── TASK-011 (Users)
    │                   ├── TASK-012 (Purchase Service)
    │                   │   └── TASK-013 (Purchase UI)
    │                   └── TASK-014 (Sale Service)
    │                       └── TASK-015 (Sale UI)
    │
    └── TASK-005 (Layout & CSS)
            └── All UI tasks

TASK-013 + TASK-015 → TASK-016 (Admin Dashboard)
TASK-015 → TASK-017 (Staff Dashboard)
TASK-012 + TASK-014 → TASK-018 (Number Generation)
All CRUD tasks → TASK-019 (Search & Filter)
All UI tasks → TASK-020 (Polish)
Everything → TASK-021 (README) → TASK-022 (Testing)
```

---

## Summary Table

| Phase | Tasks | Estimate |
|-------|-------|----------|
| 1. Setup & Foundation | TASK-001 to 005 | 8.5h |
| 2. Auth & Authorization | TASK-006 to 007 | 3h |
| 3. Master Data CRUD | TASK-008 to 011 | 9h |
| 4. Transactions | TASK-012 to 015 | 12h |
| 5. Dashboards | TASK-016 to 017 | 7h |
| 6. Polish & Finalization | TASK-018 to 022 | 11h |
| **Total** | **22 tasks** | **~50.5h** |
