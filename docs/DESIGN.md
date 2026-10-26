# DESIGN — Organic Modern Commerce

> **Project:** Sora — Inventory & Sales Management System (Laravel Blade back-office)
> **Design direction:** Organic Modern Commerce
> **Version:** 1.0
> **Date:** 2026-10-27
> **Stack:** Laravel 11 · MySQL 8 · Blade · Vanilla CSS/JS · Chart.js (CDN)
> **Related documents:** `PRD.md` (requirements & business rules), `ARCHITECTURE.md` (technical & database), `TASKS.md` (implementation plan), `sora_db.sql` (schema)
> **Primary goals:** data readability, consistency, accessibility, responsive behaviour, and maintainability.

This document adapts a mature design system from a previous e-commerce project into a design system for Sora, a stock and sales management application. Users are staff and owners who work with dense data, not shoppers browsing a catalogue. The visual identity is preserved, but components, layout, and priorities are adjusted to match.

---

## 1. Design Direction and Principles

Organic Modern Commerce blends three styles in different proportions:

| Style | Role | Application in Sora |
|---|---|---|
| **Editorial Minimalism** | Dominant | Strong typography, clear hierarchy, intentional whitespace, thin dividers, large dashboard numbers |
| **Organic Modern** | Supporting | Natural palette (sage, cream, mustard), measured rounded corners, warm surfaces, calm visual rhythm |
| **Glassmorphism** | Very selective | Only on a few floating elements (section 6); never on tables, forms, or numbers |

### Core principles

1. **Data first.** Transaction numbers, SKUs, stock counts, prices, and totals are the primary content; decoration must not compete with them.
2. **Correct before beautiful.** Aligned with `PRD.md`: the UI must not obscure or replace business rules. Business-rule error messages are as important as the page layout.
3. **Predictable interactions.** The same component behaves the same way on every page.
4. **Mobile-first.** Staff can record transactions from small devices; all pages must be responsive.
5. **Accessible by default.** Contrast, keyboard navigation, visible focus, semantic markup, and error messages are part of the design.
6. **Honest data.** Never show fabricated numbers, trends, or statuses. The dashboard displays real values from the database, including 0 when there is no data. Do not add trend arrows or percentages without an actual server-side calculation.
7. **Maintainable.** Tokens, Blade components, and business logic are separated.

### From the old design to Sora

| From the old design | Treatment |
|---|---|
| Five-colour palette, semantic tokens, light theme | Retained; dark theme tokens removed (light only) |
| Plus Jakarta Sans and Inter typography | Retained; scale adjusted for tables and data |
| 4 px spacing scale, radius, breakpoints | Retained |
| Accessibility rules, states, definition of done | Retained and expanded for transaction forms and POS |
| Glassmorphism only for floating elements | Retained; list of elements clarified and limited (section 6) |
| Homepage, hero, product gallery, wishlist, cart, checkout, buyer account | Removed; not relevant to a back-office |
| Product card and catalogue grid | Replaced by data tables, stat cards, and POS product grid |
| Variants, discounts, ratings, shipping | Removed; not in `PRD.md` |
| Dark theme tokens and `[data-theme="dark"]` | Removed; only light theme is implemented |
| Indonesian UI language | Changed to English |
| Product form fields: unit, description | Removed; not in `sora_db.sql` schema |

The database schema has no product images. Do not add image placeholders to product lists or details.

---

## 2. Colour Palette

### 2.1 Brand colours

| Token | Hex | Primary role |
|---|---|---|
| `brand.sage` | `#81A186` | Brand accent, primary button background, selected state |
| `brand.cream` | `#FCFFD0` | Page canvas |
| `brand.mustard` | `#C4AC46` | Secondary emphasis and marker; background use only |
| `brand.brown` | `#574749` | Supporting text |
| `brand.navy` | `#0C0F22` | Primary text, strong contrast, sidebar |

### 2.2 Semantic tokens — Light theme

Components use semantic tokens, not brand hex values directly.

| Token | Value | Usage |
|---|---|---|
| `color.bg.page` | `#FCFFD0` | Page canvas |
| `color.bg.surface` | `#FFFFFF` | Tables, forms, dialogs, total panels |
| `color.bg.surface-muted` | `#F4F4E8` | Subtle grouping, table headers |
| `color.bg.selected` | `#DCE9DF` | Selected row or item |
| `color.text.primary` | `#0C0F22` | Text and headings |
| `color.text.secondary` | `#574749` | Supporting text and metadata |
| `color.text.placeholder` | `#6F7264` | Input placeholders |
| `color.border.default` | `#DEDFD0` | Decorative dividers |
| `color.border.input` | `#6F7264` | Input, select, checkbox borders |
| `color.action.primary` | `#81A186` | Primary button background |
| `color.action.primary-hover` | `#74977A` | Primary button hover |
| `color.action.primary-pressed` | `#678C6E` | Primary button pressed |
| `color.text.on-action` | `#0C0F22` | Text on primary buttons |
| `color.action.strong` | `#2F5D3A` | Links, icons, and green accent text on light surfaces |
| `color.action.danger` | `#A33A2A` | Destructive button background (white text) |
| `color.highlight` | `#C4AC46` | Marker background (navy text) |
| `color.focus` | `#0C0F22` | Keyboard focus ring |
| `color.nav.bg` | `#0C0F22` | Sidebar |
| `color.nav.text` | `#FCFFD0` | Sidebar text |
| `color.nav.text-muted` | `#BCAEB0` | Sidebar secondary text |

### 2.3 Functional status colours

The brand palette does not cover error status. It is extended with four functional colours that remain warm and harmonious. These are not brand colours and are used for status only.

| Status | Text / Background | Example usage |
|---|---|---|
| `status.success` | `#24502F` / `#E4F0E6` | Completed transaction, success message, active user |
| `status.warning` | `#5A4600` / `#F7EEC4` | Low stock |
| `status.danger` | `#8A2A1C` / `#F8E3DE` | Cancelled transaction, out of stock, error |
| `status.neutral` | `#3D3F52` / `#E8EAF0` | Neutral status, role badge, inactive user |

### 2.4 Contrast verification results

Calculated using the WCAG relative luminance contrast ratio formula. Requirement: 4.5:1 for normal text, 3:1 for large text and non-text components (input borders, focus indicators).

| Pair | Ratio | Result |
|---|---:|---|
| Navy on cream | 18.39 | Pass |
| Navy on white | 18.97 | Pass |
| Brown on cream / white / muted | 8.47 / 8.74 / 7.89 | Pass |
| Navy on sage (primary button) | 6.66 | Pass |
| Navy on sage hover / pressed | 5.83 / 5.02 | Pass |
| White on sage | 2.85 | **Fail** |
| Sage as text on white / cream | 2.85 / 2.76 | **Fail** |
| Mustard as text on cream | 2.18 | **Fail** |
| Navy on mustard | 8.43 | Pass |
| `action.strong` `#2F5D3A` on white / cream / `bg.selected` | 7.64 / 7.41 / 6.10 | Pass |
| White on `#2F5D3A` | 7.64 | Pass |
| Navy / brown on `bg.selected` | 15.14 / 6.98 | Pass |
| `border.input` `#6F7264` on white / cream / muted (non-text) | 4.92 / 4.77 / 4.44 | Pass (3:1 threshold) |
| Placeholder `#6F7264` on white | 4.92 | Pass |
| `border.default` `#DEDFD0` on white | 1.35 | Decorative only; do not use as input border |
| Status success / warning / danger / neutral (text on its bg) | 7.91 / 7.78 / 7.01 / 8.59 | Pass |
| Text `status.danger` on white | 8.65 | Pass |
| White on `action.danger` `#A33A2A` | 6.57 | Pass |
| Sidebar: cream on navy / secondary `#BCAEB0` on navy | 18.39 / 8.87 | Pass |
| Sidebar active: navy on sage | 6.66 | Pass |

**Key finding:** navy text on sage buttons passes AA, so no darker button token is needed. However, sage must not be used as text colour or small icon colour on light surfaces, and white must not be used on sage. For green-accent links and icons on light surfaces, use `color.action.strong`.

### 2.5 Colour usage rules

- Cream as broad canvas; tables, forms, and total panels always sit on white surfaces.
- Navy for primary text and structural elements (sidebar).
- Sage for primary buttons, active navigation items, and selected markers. Not for small text.
- Mustard only as marker background with navy text. Never as text colour on light surfaces.
- Brown for supporting text and metadata.
- Never convey status with colour alone. Low stock, out of stock, completed, and cancelled always include text on a badge.
- Every new colour combination must be contrast-verified before use.

---

## 3. Typography

### Font families

- **Headings and UI:** Plus Jakarta Sans, weights 500–700.
- **Body, tables, and dense data:** Inter, weights 400–600.
- **Fallback:** `system-ui`, `sans-serif`.

Host fonts locally (`woff2` files with `font-display: swap`) so the application does not depend on external services and layout remains usable while fonts load.

### Type scale

| Role | Desktop | Mobile | Weight |
|---|---:|---:|---:|
| Page title | 32 px | 26 px | 700 |
| Section title | 22 px | 20 px | 600 |
| Stat card number | 36 px | 30 px | 700 |
| Panel / card title | 16 px | 16 px | 600 |
| Body and table cells | 14–16 px | 14–16 px | 400 |
| Labels and metadata | 12–14 px | 12–14 px | 500 |
| Eyebrow (small caps) | 12 px | 12 px | 600 |

### Typography rules

- Use tabular numerals (`font-variant-numeric: tabular-nums`) for prices, quantities, stock, and totals so number columns align.
- Number columns right-aligned; text columns left-aligned.
- Body line height 1.5–1.7; heading line height 1.1–1.25.
- Table text and form labels never smaller than 14 px and 12 px respectively.
- Visually separate unit price, subtotal, and grand total; grand total always most prominent.
- Uppercase only for short eyebrows; avoid uppercase paragraphs.

### Data formatting

| Data | Format |
|---|---|
| Currency | `Rp 1.250.000`, dot as thousands separator, no decimals in display |
| Quantity | Integer with unit, e.g. `12 units` |
| Date and time | `20 Oct 2026, 14:30` |
| Transaction number | Monospace or tabular font, never truncated |

---

## 4. Spacing, Grid, Shape, and Elevation

### Spacing scale

Base unit 4 px.

| Token | Value |
|---|---:|
| `space.1` | 4 px |
| `space.2` | 8 px |
| `space.3` | 12 px |
| `space.4` | 16 px |
| `space.6` | 24 px |
| `space.8` | 32 px |
| `space.12` | 48 px |
| `space.16` | 64 px |
| `space.24` | 96 px |

### Application layout

- Structure: left sidebar (width 264 px) and content area. Sticky topbar 64 px tall above content.
- Maximum content width `1280px`; horizontal padding 32–48 px on desktop, 16–20 px on mobile.
- Stat cards: 4 columns on desktop, 2 on tablet, 1–2 on mobile; gap 16 px (mobile) and 24 px (desktop).
- Forms: two columns on desktop when fields are short; one column on mobile.
- Breakpoints: `640px`, `768px`, `1024px`, `1200px`.
- Sidebar becomes a drawer below `1024px`.

### Radius

| Token | Value | Usage |
|---|---:|---|
| `radius.sm` | 8 px | Chips, small badges |
| `radius.md` | 12 px | Inputs and buttons |
| `radius.lg` | 16 px | Panels, cards, tables |
| `radius.xl` | 24 px | Login card, prominent panels |
| `radius.pill` | 9999 px | Status badges and filter chips |

### Elevation

- Prefer thin borders and surface contrast for cards and panels.
- Subtle shadows only when indicating layers: dropdowns, dialogs, toasts, floating bars.
- Honour `prefers-reduced-motion`; no parallax. Short transitions (150–200 ms) only for hover, focus, and panel open/close.

### Organic touches

- Warm surfaces (cream and white), rounded corners, and natural colours are the primary expression.
- Soft decorative shapes (low-opacity radial gradients of sage and mustard) only on the login page and empty states. Decorative elements are `aria-hidden` and must not sit behind dense text.
- Editorial dividers are thin horizontal rules; avoid vertical rules and thick colour blocks in tables.

---

## 5. Core Components

Every interactive component has documented states, accessible name, keyboard behaviour, and responsive behaviour.

### 5.1 Buttons

| Variant | Appearance | Usage |
|---|---|---|
| `primary` | Sage background, navy text | One primary action per view: Save, Save sale |
| `secondary` | Surface background, `border.input` border, primary text | Supporting actions: Cancel, Back |
| `ghost` | No background, `action.strong` text | Lightweight actions: Reset filters |
| `destructive` | `action.danger` background, white text | Cancel transaction, delete |

States: default, hover, pressed, `focus-visible`, disabled, loading.

Rules:
- Labels are specific verbs: "Save product", "Cancel transaction", not "OK" or "Submit".
- Loading and disabled states are not distinguished by colour alone: loading shows "Saving..." text and an indicator; disabled uses the `disabled` attribute.
- **Prevent double submission:** after a transaction form is submitted, the button is immediately disabled and shows loading state. This prevents duplicate transactions from repeated clicks.
- Touch target approximately 44 × 44 px.

### 5.2 Input, select, textarea

States: empty, focus, filled, invalid, disabled, read-only.

Rules:
- Labels always visible above the field; placeholders are not label substitutes.
- Required fields marked with "(required)" text on the label, not just a coloured asterisk.
- Error messages directly below the field, explaining the problem and how to fix it, connected via `aria-describedby`; field marked `aria-invalid="true"`.
- Input borders use `color.border.input` (minimum 3:1 contrast); invalid fields use `status.danger` border and icon or text.
- Valid values already entered persist after errors (`old()` in Blade).
- Stock field does not appear in the product form; on the edit page, stock is displayed as read-only text with the note "Stock changes through purchases and sales."

### 5.3 Search and filters

Filter bar above lists: search field, category or status select, date range, "Apply" button, and "Reset" link.

Rules:
- Product search: placeholder "Search by SKU or product name". Transaction search: "Search by transaction number".
- Date filter uses two date fields labelled "From date" and "To date"; invalid range error displayed below.
- Active filters shown as removable chips.
- Mobile: filters hidden behind a "Filter" button that shows the active filter count.
- Filters persist across pagination (`withQueryString()`) and when returning from detail pages.
- Provide an empty state "No results found" with a "Reset filters" button.

### 5.4 Data tables

- Soft header (`bg.surface-muted`), label text 12–14 px, weight 600.
- Horizontal rules only between rows; no vertical rules and no zebra striping. Minimum row height 48 px.
- Number columns right-aligned with tabular numerals; action column rightmost.
- Clickable rows must have a real text link on the primary column (transaction number or product name), not just an `onclick` on the row.
- Mobile (below `768px`): table transforms to stacked cards, each card containing label–value pairs; important columns (number, status, total) at the top.
- Table footer contains pagination and range text, e.g. "Showing 16 to 30 of 87 records".
- States: has data, empty, empty because of filters, load error.

### 5.5 Pagination

- Previous and next buttons, page numbers, and active page marked with `aria-current="page"`.
- Preserves filter parameters.
- On mobile: previous, next, and an indicator "Page 2 of 6".

### 5.6 Status badges

Pill shape with text; background and text from status tokens (section 2.3).

| Badge text | Token | Used on |
|---|---|---|
| Completed | `status.success` | Transactions |
| Pending | `status.warning` | Transactions |
| Cancelled | `status.danger` | Transactions |
| Low Stock | `status.warning` | Products with stock ≤ minimum_stock |
| Out of Stock | `status.danger` | Products with stock = 0 |
| Active | `status.success` | Users |
| Inactive | `status.neutral` | Users |
| Admin, Staff | `status.neutral` | Roles |

### 5.7 Stat cards (dashboard)

- Small eyebrow label, large tabular number, one-line description.
- White surface with thin border; no glass.
- Values come from database queries. No decorative charts, no fake trend percentages.
- Currency numbers use Rupiah format; count numbers use thousands separator.

### 5.8 Charts (Admin dashboard)

Chart.js loaded from CDN. Charts follow the brand palette and are accessibility-aware.

#### Chart specifications

| Chart | Type | Data source | PRD ref |
|---|---|---|---|
| Sales last 7 days | **Bar** chart | SUM of completed sales per day, last 7 days | DASH-A06 |
| Monthly sales | **Line** chart | SUM of completed sales per month, current year | DASH-A07 |

#### Chart styling

| Property | Value |
|---|---|
| Bar / line colour | `brand.sage` (`#81A186`) |
| Bar hover | `action.primary-hover` (`#74977A`) |
| Line point colour | `brand.sage` |
| Grid lines | `color.border.default` (`#DEDFD0`) |
| Axis labels | `color.text.secondary` (`#574749`), Inter 12 px |
| Tooltip background | `color.bg.surface` (`#FFFFFF`) with `border.default` border |
| Tooltip text | `color.text.primary` |
| Font family | `Inter, system-ui, sans-serif` |
| Legend | Hidden for single-dataset charts; shown if multiple datasets |
| Animation | 400 ms ease-out on load; respect `prefers-reduced-motion` |

#### Chart responsive behaviour

- Desktop: charts sit in surface cards with 16 px padding, 2-column grid alongside stat cards.
- Tablet: charts stack in single column below stat cards.
- Mobile: charts full width, horizontal scroll not needed (Chart.js `responsive: true`, `maintainAspectRatio: false`, container has a minimum height of 200 px).
- Y-axis uses Rupiah format without decimals. X-axis uses short date labels (e.g. "Mon", "Tue" for 7-day; "Jan", "Feb" for monthly).
- Tooltip shows full date and formatted Rupiah value.

### 5.9 Top products and recent transactions (Admin dashboard)

| Widget | Layout | PRD ref |
|---|---|---|
| **Top 5 best-selling products** | Numbered list or horizontal bar chart showing product name and quantity sold | DASH-A08 |
| **Recent transactions** | Compact table: 10 most recent completed purchases + sales, columns: type icon, number, date, total, status badge | DASH-A09 |

- Both widgets sit in surface cards.
- "Top 5" can use a simple horizontal bar visualisation using `brand.sage` bars with navy text labels, or a numbered list — whichever reads cleaner.
- "Recent transactions" uses a compact version of the data table component (5.4).

### 5.10 POS Quick Sale (Staff dashboard)

The primary interaction surface for Staff users (DASH-S01). Designed for speed and minimal friction.

#### Layout

```
┌────────────────────────────────────────────────────────────────┐
│  STAFF DASHBOARD                                               │
├──────────────────────────────┬─────────────────────────────────┤
│                              │                                 │
│  ┌─ Search ─────────────┐    │  ┌─ Cart ─────────────────────┐ │
│  │ 🔍 Search product... │    │  │                             │ │
│  └──────────────────────┘    │  │  Product A        2  Rp xx  │ │
│                              │  │  Product B        1  Rp xx  │ │
│  ┌─ Popular Products ──────┐ │  │                             │ │
│  │ ┌────┐ ┌────┐ ┌────┐   │ │  │─────────────────────────────│ │
│  │ │Prod│ │Prod│ │Prod│   │ │  │  Total:          Rp xxx.xxx │ │
│  │ │ A  │ │ B  │ │ C  │   │ │  │  [Save Sale]                │ │
│  │ └────┘ └────┘ └────┘   │ │  └─────────────────────────────┘ │
│  │ ┌────┐ ┌────┐ ┌────┐   │ │                                 │
│  │ │Prod│ │Prod│ │Prod│   │ │  ┌─ Today's Stats ────────────┐ │
│  │ │ D  │ │ E  │ │ F  │   │ │  │  My Sales Today: Rp xx.xxx │ │
│  │ └────┘ └────┘ └────┘   │ │  └─────────────────────────────┘ │
│  └──────────────────────────┘ │                                 │
│                              │  ┌─ Recent Sales ─────────────┐ │
│                              │  │  SAL-xxx ... Rp xx.xxx      │ │
│                              │  │  SAL-xxx ... Rp xx.xxx      │ │
│                              │  └─────────────────────────────┘ │
└──────────────────────────────┴─────────────────────────────────┘
```

#### POS product grid

- Shows top/popular products (top 5 by sales quantity from DASH-A08 query, or all products if fewer than 5).
- Each card: product name, SKU (small), selling price, stock available.
- Card has a sage left border accent. White surface, `radius.md`.
- Click/tap on a card adds 1 unit to the cart. If already in cart, increment quantity.
- Products with stock = 0 are greyed out with "Out of Stock" badge overlaid; not clickable.
- Low-stock products show a small `status.warning` dot indicator.

#### POS search

- Search field above the product grid. Placeholder: "Search by SKU or product name".
- Filters the grid in real-time (vanilla JS, client-side filtering of pre-loaded product list).
- If the product list is large (> 50), consider server-side search with debounced fetch.

#### POS cart

- Right column panel (desktop) or bottom sheet (mobile).
- Each line: product name, quantity with +/− buttons, unit price (read-only from `selling_price`), line subtotal.
- Remove button (ghost `destructive`) on each line.
- Cart footer: item count, estimated total (labelled "Estimate"), and primary "Save Sale" button.
- Total labelled as estimate with note "Total is recalculated by the server when saved."
- Cart is managed entirely in vanilla JS (in-memory array); submitted as a form POST to `SaleController@store`.
- On submit, button enters loading state ("Saving...") and is disabled.
- Server response: redirect with success flash, or reload with error flash and cart state preserved via `old()`.

#### POS responsive behaviour

- Desktop: 2-column layout (product grid left, cart + stats right).
- Mobile: product grid fills width; cart is a collapsible bottom panel with a sticky "Cart (N items)" bar. Tapping the bar slides up the full cart.

### 5.11 Alert and flash messages

| Type | Token | Behaviour |
|---|---|---|
| Success | `status.success` | `role="status"`, appears above content after redirect |
| Warning | `status.warning` | `role="status"` |
| Error | `status.danger` | `role="alert"`; can contain a summary |

- Alerts always contain text (and an icon if present); never colour alone.
- Validation errors: show a summary at the top of the form ("3 fields need to be fixed") with links to each field, in addition to messages below fields.
- Business rule errors (e.g. insufficient stock): shown at the top of the form, mentioning the product, available stock, and requested quantity; inputs remain filled.
- Database errors: generic message without technical details.

### 5.12 Line-item component (multi-item transactions)

The core of purchase and sale forms. Each row represents one product.

| Column | Purchase | Sale |
|---|---|---|
| Product | Select showing SKU and name | Select showing SKU and name |
| Stock info | None | Text below selection: "Available: 12 units" |
| Quantity | Number input (minimum 1) | Number input (minimum 1) |
| Unit price | Price input (purchase price) | Read-only text from product selling price |
| Subtotal | Read-only text (estimate) | Read-only text (estimate) |
| Remove row | Icon button with accessible name "Remove row" | Same |

Rules:
- Minimum one row; "Add item" button adds a new row.
- Products already selected in another row are disabled in other rows to prevent duplicates (convenience only; server still validates, see BR-05 in `PRD.md`).
- Subtotal and total on screen are labelled **"Estimate"** with note "Total is recalculated by the server when saved." Stored values come from the server only (BR-05).
- Displayed stock is the stock at page load and may change; the actual check happens server-side at save time.
- On error, all rows are re-rendered from `old('items')`, with per-row error messages where applicable (`aria-describedby`).
- Mobile: each row becomes a stacked card with labels above each field.

### 5.13 Total panel

- Displays item count, subtotal, and total (estimate on forms; final on detail pages).
- Desktop: panel in the right column of the form, sticky.
- Mobile: sticky bar at the bottom of the screen with total and save button (one of the permitted glass elements, section 6). The bar must not cover the last field; add bottom padding to content.

### 5.14 Confirmation dialog

- Used for transaction cancellation and deletion.
- Solid surface (not glass), clear title, consequence explanation, and "Back" and specifically-labelled destructive buttons.
- Cancellation dialog states the impact clearly: "This will cancel the transaction. Stock will not be affected since it is still pending."
- Focus moves to the dialog when opened, is trapped inside, and returns to the trigger when closed; `Escape` closes the dialog.

### 5.15 Other components

- Page header: module name eyebrow, title, one-line description, primary action on the right, thin rule below.
- Description list (detail pages): label–value pairs in a grid.
- Empty state: optional organic illustration, one-sentence explanation, one action.
- Skeleton loader for separately loaded areas (if any).
- Toast for non-critical temporary notifications (glass permitted); important messages use persistent alerts.
- Breadcrumb on detail pages and forms.

---

## 6. Glassmorphism: Selective Usage

Glass is an accent, not a default surface. Only four elements may use it:

| Element | Reason |
|---|---|
| Sticky topbar when content scrolls beneath it | Maintains context without a solid colour block |
| Sticky total and save bar on mobile transaction forms | Floating element over scrolling content |
| Toast notifications | Temporary floating element |
| Login card over decorative organic shapes | Only place with a safe decorative background |

**Must not use glass:** tables and data cells, forms and inputs, stat cards and dashboard numbers, desktop total panel, error messages and alerts, dialogs, sidebar, chart containers, POS cart panel, or anything containing long text or important numbers.

Specification (light theme only):

| Property | Value |
|---|---|
| Background | `rgb(255 255 255 / 0.80)` |
| Blur | `blur(12px) saturate(140%)` |
| Border | `1px solid rgb(12 15 34 / 0.12)` |

Worst-case contrast verification:

| Text | Worst ratio | Result |
|---|---:|---|
| Navy on glass (0.80 opacity) | 12.19 | Pass |
| Brown on glass (0.80 opacity) | 5.61 | Pass |

Additional rules:
- Background must fall back to a solid surface if `backdrop-filter` is not supported, or if the user selects `prefers-reduced-transparency: reduce` or `prefers-contrast: more`.
- Do not stack glass on glass.
- Glass elements must not cover keyboard focus or primary content being edited.

---

## 7. Page Layouts

### 7.1 Navigation

| Item | Admin | Staff |
|---|---|---|
| Dashboard | Yes | Yes |
| Products | Yes | Yes (view only) |
| Low Stock | Yes | Yes |
| Purchases | Yes | Yes |
| Sales | Yes | Yes |
| Categories | Yes | No |
| Suppliers | Yes | No |
| Users | Yes | No |

- Navy sidebar; active item has sage background with navy text, heavier weight, and a left-edge marker (not colour alone).
- "Low Stock" item shows the count of low-stock products computed from the database.
- Topbar contains breadcrumb or title, user name with role badge, and logout button.
- Items the user does not have access to are not displayed; the server still rejects with 403 (see `ARCHITECTURE.md`).

### 7.2 Page list

| Page | Structure | Special states |
|---|---|---|
| **Login** | Login card (glass) over organic background; title, email, password, "Sign In" button | Wrong credentials, inactive account, button loading |
| **Admin Dashboard** | Header; stat card row (sales today, sales this month, transactions today, total products); chart row (7-day sales bar, monthly sales line); top 5 products panel; recent transactions table; low-stock panel with link | No transactions yet: numbers show 0, charts show empty state |
| **Staff Dashboard** | Header; POS quick sale (section 5.10); staff's sales total today; recent sales; low-stock warning panel | Empty cart, no sales today |
| **Products: list** | Header with "Add Product" button (Admin); filter bar (search, category); table: SKU, name, category, selling price, stock, badge; pagination | Empty, empty from filters |
| **Products: add / edit** | Form: category, SKU, name, selling price, minimum stock; no stock field | Duplicate SKU error, validation errors |
| **Products: detail** | Description list, stock and badge, brief transaction history, edit and delete buttons (Admin) | Deleted product: not accessible from list |
| **Categories: list / add / edit** | Table: name; simple form | Delete rejected because products use it |
| **Suppliers: list / add / edit** | Table: name, phone, email; form | Delete rejected because purchases use it |
| **Users** | Table: name, email, role, status; form with role and active status | Last admin rejection |
| **Low Stock** | Table of products with stock ≤ minimum_stock; columns: stock, minimum, gap; search | Empty: "No low-stock products" |
| **Purchases: list** | Filter bar (number, date, status); table: number, date, supplier, recorder, total, status; filter result summary (Admin) | Empty from filters |
| **Purchases: create** | Supplier select; line-item component; total panel; save button | Validation errors, business rule errors, loading |
| **Purchases: detail** | Metadata (number, date, supplier, recorder, status); item table with transaction-time prices; total; cancellation block (Admin) | Cancelled: banner and cancellation trail |
| **Sales: list / create / detail** | Same as purchases, without supplier; unit price read-only; stock info on rows | Insufficient stock |

### 7.3 Transaction detail pages

- Header with transaction number and status badge.
- Metadata in a description list: date and time, recorder, notes.
- Item table: product, quantity, transaction-time price, subtotal. Deleted products are still displayed.
- Total with the strongest emphasis on the page.
- Admin on a pending transaction: "Complete" button (primary) and "Cancel Transaction" destructive button that opens a confirmation dialog (5.14).
- Completed transactions: no edit or cancel buttons (BR-07).
- Cancelled transactions: `status.danger` banner with who cancelled, when, and why; cancellation button no longer shown.

---

## 8. Responsive Behaviour

### Mobile

- Compact topbar with a menu button that opens the sidebar drawer.
- Tables become stacked cards; filters in a collapsible panel.
- Line-item rows become stacked cards; sticky total bar at the bottom.
- Primary buttons on forms can be full-width.
- Avoid horizontal scroll, except on intentionally wide tables with their own scroll container.
- Dialogs, dropdowns, and drawers fit within the viewport and support keyboard and screen readers.
- POS: product grid becomes 2-column, cart becomes a bottom sheet.

### Tablet

- Stat cards in two columns; forms in one or two columns depending on width.
- Sidebar remains a drawer up to `1024px`.
- POS: product grid and cart can side-by-side or stacked depending on width.

### Desktop

- Fixed sidebar; content in a container with maximum width and consistent gutters.
- Transaction forms in two columns: line-items on the left, sticky total panel on the right.
- POS: two-column layout (product grid + search left, cart + stats right).
- Avoid excessive whitespace on very wide screens.

---

## 9. Interaction and State Requirements

For every relevant page, design and implement:

- Empty state
- Error state
- Success and confirmation state
- Disabled state
- Keyboard focus state
- Mobile state
- Loading state on submit buttons

Required examples:

- Product list: has data, no data, active filters with no results.
- Sale form: stock sufficient, stock insufficient (rejected, inputs preserved), duplicate product, quantity 0.
- Purchase form: invalid price or quantity, supplier not selected.
- Transaction detail: completed, cancelled, cancellation rejected.
- Admin dashboard: no data yet (numbers 0, charts empty), has low stock.
- Staff dashboard: empty POS cart, no sales today, product out of stock in grid.
- Access denied (403) and data not found (404): clear page with a back link.

---

## 10. Content and Language

The interface is in **English**, concise, specific, and without emoji or emoticons. Use words, not decorative symbols.

| Situation | Example message |
|---|---|
| Insufficient stock | `Stock for "Hydraulic Filter HX-200" is insufficient. Available: 3 units, requested: 5.` |
| Purchase cancellation | `This purchase is still pending. Cancelling will not affect stock. Proceed?` |
| Duplicate SKU | `This SKU is already in use. Please use a different SKU.` |
| Invalid quantity | `Quantity must be a whole number of at least 1.` |
| Delete category rejected | `This category cannot be deleted because it is used by 12 products.` |
| System failure | `The transaction could not be saved and no data was changed. Please try again.` |
| Success | `Sale recorded successfully with number SAL-20261020-0001.` |

Examples above are style illustrations only; product names and numbers come from live data at runtime.

Button labels: "Save Product", "Save Purchase", "Save Sale", "Cancel Transaction", "Apply", "Reset Filters", "Add Item", "Complete", "Sign In", "Sign Out".

---

## 11. Accessibility and Usability Checklist

- [ ] Text contrast meets WCAG 2.2 AA: 4.5:1 for normal text, 3:1 for large text and non-text components.
- [ ] Keyboard focus is visible and not covered by sticky or glass elements.
- [ ] All controls have accessible names, including icon buttons.
- [ ] All form fields have labels and useful error messages.
- [ ] Errors are connected to their fields (`aria-describedby`, `aria-invalid`); error summary uses `role="alert"`.
- [ ] Touch targets approximately 44 × 44 px where possible.
- [ ] Colour is not the only means of conveying information: status, stock, and errors always accompanied by text.
- [ ] `prefers-reduced-motion` and `prefers-contrast` preferences are honoured.
- [ ] Tables use semantic markup (`th` with `scope`); mobile cards preserve label–value pairs.
- [ ] Navigation, drawers, dialogs, and dropdowns are operable with keyboard and assistive technology.
- [ ] Zoom at 200% and narrow viewports do not hide save buttons or critical information.
- [ ] Transaction form submit buttons are disabled after submission to prevent double submission.
- [ ] POS cart interaction is keyboard-accessible (product cards focusable, Enter to add, cart navigable).

---

## 12. Frontend Token Structure

Store tokens in a single source and map to semantic variables. CSS implementation:

```css
:root {
  color-scheme: light;

  /* Brand */
  --brand-sage: #81A186;
  --brand-cream: #FCFFD0;
  --brand-mustard: #C4AC46;
  --brand-brown: #574749;
  --brand-navy: #0C0F22;

  /* Semantic */
  --bg-page: var(--brand-cream);
  --bg-surface: #FFFFFF;
  --bg-surface-muted: #F4F4E8;
  --bg-selected: #DCE9DF;
  --text-primary: var(--brand-navy);
  --text-secondary: var(--brand-brown);
  --text-placeholder: #6F7264;
  --border-default: #DEDFD0;
  --border-input: #6F7264;
  --action-primary: var(--brand-sage);
  --action-primary-hover: #74977A;
  --action-primary-pressed: #678C6E;
  --text-on-action: var(--brand-navy);
  --action-strong: #2F5D3A;
  --action-danger: #A33A2A;
  --highlight: var(--brand-mustard);
  --focus-ring: var(--brand-navy);
  --nav-bg: var(--brand-navy);
  --nav-text: var(--brand-cream);
  --nav-text-muted: #BCAEB0;

  /* Status */
  --status-success-text: #24502F;  --status-success-bg: #E4F0E6;
  --status-warning-text: #5A4600;  --status-warning-bg: #F7EEC4;
  --status-danger-text:  #8A2A1C;  --status-danger-bg:  #F8E3DE;
  --status-neutral-text: #3D3F52;  --status-neutral-bg: #E8EAF0;

  /* Glass */
  --glass-bg: rgb(255 255 255 / 0.80);
  --glass-border: rgb(12 15 34 / 0.12);

  /* Spacing */
  --space-1: 4px;  --space-2: 8px;   --space-3: 12px; --space-4: 16px;
  --space-6: 24px; --space-8: 32px;  --space-12: 48px; --space-16: 64px;

  /* Radius */
  --radius-sm: 8px; --radius-md: 12px; --radius-lg: 16px;
  --radius-xl: 24px; --radius-pill: 9999px;

  /* Typography */
  --font-ui: "Plus Jakarta Sans", system-ui, sans-serif;
  --font-body: "Inter", system-ui, sans-serif;

  /* Chart */
  --chart-primary: var(--brand-sage);
  --chart-primary-hover: var(--action-primary-hover);
  --chart-grid: var(--border-default);
  --chart-label: var(--text-secondary);
}

body {
  background: var(--bg-page);
  color: var(--text-primary);
  font-family: var(--font-body);
}

.num { font-variant-numeric: tabular-nums; text-align: right; }

:focus-visible {
  outline: 2px solid var(--focus-ring);
  outline-offset: 2px;
}

/* Glass: only for elements permitted in section 6 */
.glass {
  background: var(--glass-bg);
  -webkit-backdrop-filter: blur(12px) saturate(140%);
  backdrop-filter: blur(12px) saturate(140%);
  border: 1px solid var(--glass-border);
}

@supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
  .glass { background: var(--bg-surface); }
}

@media (prefers-reduced-transparency: reduce), (prefers-contrast: more) {
  .glass {
    background: var(--bg-surface);
    -webkit-backdrop-filter: none;
    backdrop-filter: none;
  }
}

@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
```

**Implementation requirement:** verification results in sections 2.4 and 6 apply to the values above. If any value is changed, recalculate contrast before using it.

---

## 13. Frontend Component Structure

Separate by responsibility. For Blade, anonymous components reside in `resources/views/components`.

```text
resources/
  css/
    tokens.css          # variables from section 12
    app.css             # reset, typography, utilities, components
  views/
    layouts/
      app.blade.php     # sidebar, topbar, content slot, flash message
      guest.blade.php   # login page
    components/
      ui/
        button  input  select  textarea  badge  alert
        modal  toast  pagination  empty-state
      data/
        table  stat-card  description-list  filter-bar
        page-header  status-badge  money  chart-card
      transaction/
        line-items       # product row, quantity, price, subtotal
        totals-panel
        cancel-dialog
      pos/
        product-grid     # clickable product cards
        cart-panel       # cart lines, total, save button
        search-bar       # real-time product search
    auth/  dashboard/  products/  categories/  suppliers/
    users/  low-stock/  purchases/  sales/
```

Business logic remains in Controllers, Services, and Models (see `ARCHITECTURE.md`); view components only present data and do not calculate stored totals.

---

## 14. Definition of Done

A page or component is considered ready when:

- It follows the tokens and layout rules of this document.
- It functions in the light theme.
- It is responsive on mobile, tablet, and desktop.
- Interactive states are actually implemented, not just designed.
- Keyboard navigation and visible focus work.
- Empty, error, disabled, loading, and success states are handled where relevant.
- All numbers, stock, prices, and statuses come from real data.
- Validation and business-rule error messages are clearly displayed and valid inputs are preserved.
- The component can be reused without copying its styles or logic.
- Critical flows (login, create purchase, create sale, reject insufficient stock, cancellation, POS quick sale) are tested with scenarios from `PRD.md` section 8.
- Chart data matches dashboard queries and renders correctly at all breakpoints.

---

## 15. Resolved Decisions

Previously open decisions from the old design, now finalised for Sora:

| Topic | Decision | Rationale |
|---|---|---|
| CSS approach | **Vanilla CSS with custom properties** | Matches the Laravel + Blade + Vanilla JS stack; tokens map directly to CSS variables (section 12) |
| Line-item interactivity | **Vanilla JavaScript** | No additional dependencies; handles add/remove rows and estimate calculations |
| Dark theme | **Not implemented** | Light theme only; all dark theme tokens removed from this document |
| UI language | **English** | Consistent with PRD and TASKS documentation |
| Font hosting | **Local `woff2` files** | No external dependencies; ensure licensing and perform subsetting |
| Double-submit prevention | **Client-side button disable + loading state** | Server-side idempotency tokens out of scope for MVP |
| Dashboard charts | **Chart.js from CDN** | Simple, lightweight, responsive; bar chart for 7-day sales, line chart for monthly |
| POS quick sale | **Client-side JS cart with form POST** | Products loaded on page, filtered client-side; cart managed in JS, submitted as standard form |

---

## Final Direction

**Use Organic Modern Commerce as the design system.** Retain the five brand colours, use semantic tokens for the light theme, add verified functional status colours, use editorial layout for scannable data, and limit glass effects to four floating elements. Build transaction flows, POS quick sale, and reusable components first before adding decorative effects. Charts use Chart.js with brand colours and respect accessibility preferences.
