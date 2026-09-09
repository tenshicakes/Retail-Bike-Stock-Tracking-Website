# Retail Bike Stock Tracking System

> **Alvin's Bike Repair Shop** — Inventory management, transaction processing, and audit logging for a retail bicycle business.

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [Core Functionalities & Key Features](#core-functionalities--key-features)
3. [Technical Architecture](#technical-architecture)
4. [UI/UX Overview](#uiux-overview)
5. [System Workflow](#system-workflow)
6. [Getting Started](#getting-started)
7. [Testing](#testing)

---

## Project Overview

The Retail Bike Stock Tracking System is a full-stack Laravel web application purpose-built for **Alvin's Bike Repair Shop**. It digitizes and centralizes the shop's inventory operations — from product cataloguing and stock replenishment to transaction logging and staff accountability. Rather than relying on spreadsheets or ad-hoc record-keeping, the system provides a role-aware, auditable interface for every stock movement in the shop.

The application is designed around the real-world workflow of a small-to-medium retail bike shop: parts are catalogued by bike type (Whole Bikes, Bike Parts, Accessories), stock levels are tracked in real time, and every stock-in or stock-out event is recorded with the responsible staff member, unit pricing, and timestamps. Authorized personnel can also export these records as formal PDF invoices.

**Target audience:** Shop owners, administrators, staff, and mechanics who need structured, searchable, and auditable access to inventory data with varying permission levels.

---

## Core Functionalities & Key Features

### Authentication & Authorization

- **Custom authentication model** (`accounts` table) — The system uses a dedicated `Account` model with `Username`, `Password`, and `Role` fields rather than Laravel's default `User` model, configured via `config/auth.php`.
- **Session-based login** with CSRF protection, password hashing via `Hash::make`, and session regeneration on successful login to prevent fixation attacks.
- **Role-Based Access Control (RBAC)** with four distinct roles:
  - `Administrator` — Full system access including user account management
  - `Owner` — Access to low-stock, no-stock, and log views; cannot manage accounts
  - `Staff` — Access to logs; can process stock transactions; cannot access low/no stock reports or accounts
  - `Mechanic` — Can view products and process stock transactions; cannot access logs, low/no stock reports, or accounts
- **Page-level authorization** enforced server-side via the custom `RoleAccess` middleware (`role:Administrator,Owner,...` syntax) and the `Account::canAccessPage()` method.
- **Back-navigation prevention** via `PreventBackHistory` middleware — sets aggressive `Cache-Control` headers to prevent authenticated pages from being cached by the browser.

### Dashboard & Analytics

- **Real-time digital clock** (HH:MM:SS AM/PM) and current date display on the home dashboard.
- **Three KPI stat cards** providing at-a-glance metrics:
  - **Products Listed** — Total count of all products in the catalog
  - **Low Stocks** — Products with stock count > 0 and ≤ 2 (configurable threshold)
  - **No Stocks** — Products with stock count exactly at 0
- **Paginated product overview** on the home page, showing the latest products added to inventory.

### Product Management

- **Full product CRUD** — Create, read, update, and delete products (Administrator and Owner roles only for write operations).
- **Hierarchical categorization** — Products are organized into a two-level taxonomy:
  - **Categories** (e.g., Whole Bikes, Bike Parts, Accessories)
  - **SubCategories** (e.g., Mountain Bikes, Framesets, Safety Gear)
  - Validated server-side against `config/bike_categories.php` to prevent invalid category/subcategory combinations.
- **Real-time AJAX search** — Product name search with debounced input (300ms) and live table re-rendering without full page reload.
- **Dynamic cascading filters** — Category dropdown populates SubCategory options via `/api/subcategories` endpoint; both filters work in combination.
- **Bulk product editing** — Select multiple products and batch-update ProductName, Category, SubCategory, and Price via a modal grid interface.
- **Quick inline actions** — Per-row Stock-In (+1) and Stock-Out (-1) buttons for rapid single-item updates.

### Stock Transaction Processing

- **Batch stock operations** — Select multiple products across paginated results and process them in a single transaction.
- **Stock-In / Stock-Out modes** — Toggle between adding stock (inbound) and removing stock (outbound). Stock-out is validated to prevent negative inventory.
- **Paginated transaction modal** — Selected items are rendered in a paginated modal (5 items per page) with per-item quantity controls (+/- buttons, direct input), item removal, and total summary.
- **Atomic database transactions** — All stock changes within a batch are wrapped in a `DB::transaction()` with `commit()` on success or `rollBack()` on failure, ensuring data consistency.
- **UUID batch tracking** — Each batch operation generates a unique `BatchID` (via `Str::uuid()`) to group related log entries.
- **Automatic log creation** — Every stock movement is logged to the `logs` table with user attribution, product linkage, quantities, unit prices, total prices, timestamps, and an optional description.

### Log History & Audit Trail

- **Paginated transaction log table** with Eager Loading (`Log::with(['product', 'account'])`) for performance.
- **Cascading category/subcategory filters** — Filter logs by the product's category and subcategory via `whereHas` relationships.
- **Individual PDF export** — Each log row has a dedicated "Export" button that generates a single-page PDF invoice via DomPDF.
- **Batch PDF export** — Select multiple logs via checkboxes (selection mode) and generate a combined invoice PDF with totals, signature blocks, and line-item details.
- **Persistent selection state** — Log selections are stored in `sessionStorage` so they survive pagination and page refreshes.
- **Invoice PDF template** — Professional layout with shop branding, transaction table, grand totals, and signature lines.

### Account Management

- **Administrator-only page** — Only users with the `Administrator` role can access the accounts management interface.
- **Full account lifecycle** — Create, edit, and delete accounts (with self-deletion protection — the logged-in Administrator cannot delete their own account).
- **Password confirmation validation** — Laravel's `confirmed` rule enforces matching password fields on create and update.
- **Real-time username search** — AJAX-powered search with instant table filtering.
- **Role assignment** — Assign one of four roles at creation or update time.

### Category Configuration

- **Centralized category map** in `config/bike_categories.php` — A single source of truth for valid category/subcategory pairs.
- **AJAX subcategory endpoints** — `/api/subcategories` returns valid subcategories for a given category, consumed by both the products and logs pages.

---

## Technical Architecture

### Technology Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Backend Framework | Laravel | ^13.17 |
| PHP Runtime | PHP | ^8.3 |
| Database | SQLite | (via `database/database.sqlite`) |
| PDF Generation | barryvdh/laravel-dompdf | ^3.1 |
| Frontend CSS | Bootstrap 5, TailwindCSS 4 | ^5.3.8, ^4.0.0 |
| Frontend JS | jQuery, Bootstrap JS (with Popper) | ^4.0.0, ^5.3.8 |
| Build Tool | Vite | ^8.0.0 |
| Testing | Pest PHP | ^5.1 |
| Code Style | Laravel Pint | ^1.27 |

### Directory Structure

```
ELEC3_Montalan/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AccountController.php       # Account CRUD (Admin only)
│   │   │   ├── AuthController.php          # Login/logout
│   │   │   ├── Controller.php              # Base controller
│   │   │   ├── DashboardController.php     # Home dashboard + stock processing
│   │   │   ├── LogController.php           # Log history + PDF export
│   │   │   └── ProductController.php       # Product CRUD, search, bulk update, stock pages
│   │   └── Middleware/
│   │       ├── PreventBackHistory.php      # No-cache headers for auth pages
│   │       └── RoleAccess.php              # Role-based route authorization
│   ├── Models/
│   │   ├── Account.php                     # Authenticatable model (custom auth)
│   │   ├── Log.php                         # Transaction log with relationships
│   │   ├── Product.php                     # Inventory item
│   │   └── User.php                        # Default Laravel user (unused for auth)
│   └── Providers/                          # Service providers
├── config/
│   ├── auth.php                            # Auth guard configured for Account model
│   ├── bike_categories.php                 # Valid category/subcategory taxonomy
│   └── ...                                 # Standard Laravel config files
├── database/
│   ├── database.sqlite                      # SQLite database file
│   ├── factories/                           # Model factories
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 2026_08_13_190402_create_accounts_table.php
│   │   ├── 2026_08_13_190409_create_products_table.php
│   │   ├── 2026_08_13_190414_create_logs_table.php
│   │   └── 2026_08_14_175714_standardize_product_categories.php
│   └── seeders/
│       ├── AccountSeeder.php               # Default account fixtures
│       ├── DatabaseSeeder.php
│       └── ProductSeeder.php               # Comprehensive product catalog
├── public/
│   └── images/
│       └── JFM.png                         # Shop logo
├── resources/
│   ├── css/
│   │   ├── app.scss                        # Global styles + Bootstrap + custom tokens
│   │   ├── dashboard.scss
│   │   └── login.scss
│   ├── js/
│   │   ├── app.js                          # jQuery + Bootstrap global imports
│   │   ├── product-table.js                # Home page: batch stock, inline actions
│   │   ├── search-result.js                # Products page: AJAX search, filters, modals
│   │   ├── lowstock-page.js                # Low stock page (similar pattern)
│   │   ├── nostock-page.js                 # No stock page (similar pattern)
│   │   ├── accounts-page.js                # Account management: search, modals
│   │   └── log-page.js                     # Log page: cascading filters
│   └── views/
│       ├── components/
│       │   ├── layout.blade.php            # Shared dashboard shell (sidebar + header)
│       │   ├── toast.blade.php             # Global toast notification component
│       │   ├── product-table.blade.php     # Home page product table component
│       │   ├── search-result.blade.php     # Reusable AJAX-renderable product table
│       │   └── log-table.blade.php         # Log table with batch PDF export
│       ├── dashboard/
│       │   ├── home.blade.php              # Dashboard home with KPIs
│       │   ├── products.blade.php          # Product management page
│       │   ├── lowstock.blade.php          # Low stock alert page
│       │   ├── nostock.blade.php           # Out-of-stock page
│       │   ├── logs.blade.php              # Transaction history page
│       │   └── accounts.blade.php          # User management page
│       ├── login.blade.php                 # Standalone login page
│       └── pdf/
│           └── invoice.blade.php           # DomPDF invoice template
├── routes/
│   └── web.php                             # All HTTP route definitions
├── tests/
│   ├── Feature/
│   │   └── AccountManagementTest.php       # Pest feature tests for accounts
│   ├── Unit/
│   ├── Pest.php
│   └── TestCase.php
├── composer.json
├── package.json
├── vite.config.js
└── boost.json
```

### Data Layer

#### Database Schema

**`accounts` table**
| Column | Type | Notes |
|--------|------|-------|
| `UserID` | `id` (PK) | Auto-incrementing primary key |
| `Username` | `string` | Unique login identifier |
| `Password` | `string` | Hashed via `Hash::make` |
| `Role` | `string` | One of: Administrator, Owner, Staff, Mechanic |
| `timestamps` | | `created_at`, `updated_at` |

**`products` table**
| Column | Type | Notes |
|--------|------|-------|
| `ProductID` | `id` (PK) | Auto-incrementing primary key |
| `ProductName` | `string` | |
| `Category` | `string` | Validated against `bike_categories` config |
| `SubCategory` | `string` | |
| `Price` | `decimal(10,2)` | Philippine Peso (₱) |
| `Stocks` | `bigInteger` | Default 0 |
| `ImagePath` | `string` (nullable) | Reserved for future product images |
| `timestamps` | | `created_at`, `updated_at` |

**`logs` table**
| Column | Type | Notes |
|--------|------|-------|
| `LogID` | `id` (PK) | Auto-incrementing primary key |
| `UserID` | `unsignedBigInteger` | FK → `accounts.UserID` (onDelete cascade) |
| `ProductID` | `unsignedBigInteger` | FK → `products.ProductID` (onDelete cascade) |
| `ActionType` | `string` | "Stock-In" or "Stock-Out" |
| `Quantity` | `integer` | |
| `UnitPrice` | `decimal(10,2)` | Price at time of transaction |
| `TotalPrice` | `decimal(10,2)` | `Quantity × UnitPrice` |
| `LogDate` | `dateTime` | Custom timestamp (no Laravel timestamps) |
| `LogDescription` | `text` (nullable) | Optional operator notes |
| `BatchID` | `uuid` | Groups related batch transactions |

#### Eloquent Relationships

- `Product` — Standalone model; no relationships defined.
- `Account` — Custom `Authenticatable` extending Laravel's base; provides `hasRole()`, `canAccessPage()`, and `canEditProducts()` methods.
- `Log` — Belongs to `Product` (`ProductID`) and `Account` (`UserID`); loaded eagerly on log listing via `Log::with(['product', 'account'])`.

### Routing & Middleware

All routes are defined in `routes/web.php` using route groups:

```
Guest routes
  GET  /                  → AuthController@showLogin
  POST /login             → AuthController@authenticate

Authenticated routes (auth + PreventBackHistory)
  GET  /dashboard/home                    → DashboardController@home
  POST /dashboard/process-stock           → DashboardController@processStock [Administrator,Owner,Staff,Mechanic]
  GET  /api/subcategories                 → ProductController@getSubcategories [Administrator,Owner,Staff,Mechanic]
  GET  /dashboard/products                → ProductController@index [Administrator,Owner,Staff,Mechanic]
  POST /dashboard/products                → ProductController@store [Administrator,Owner]
  GET  /api/products/search               → ProductController@search [Administrator,Owner,Staff,Mechanic]
  POST /dashboard/products/bulk-update    → ProductController@bulkUpdate [Administrator,Owner,Mechanic]
  GET  /dashboard/logs                    → LogController@index [Administrator,Owner,Staff]
  POST /dashboard/logs/export             → LogController@exportPdf [Administrator,Owner,Staff]
  GET  /dashboard/lowstock                → ProductController@lowStockIndex [Administrator,Owner]
  GET  /api/lowstock/search               → ProductController@lowStockSearch [Administrator,Owner]
  GET  /dashboard/nostock                 → ProductController@noStockIndex [Administrator,Owner]
  GET  /api/nostock/search                → ProductController@noStockSearch [Administrator,Owner]
  GET  /dashboard/accounts                → AccountController@index [Administrator]
  POST /dashboard/accounts                → AccountController@store [Administrator]
  PUT  /dashboard/accounts/{account}      → AccountController@update [Administrator]
  DELETE /dashboard/accounts              → AccountController@destroy [Administrator]
  GET  /logout                            → AuthController@logout
```

### Authentication Configuration

Laravel's default `users` provider in `config/auth.php` is **overridden** to use `App\Models\Account`:

```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\Account::class,
    ],
],
```

The `Account` model overrides `getAuthPassword()` to return the `Password` column instead of Laravel's default `password` column, enabling seamless integration with `Auth::attempt()`.

### Frontend Build Pipeline

- **Vite** (`vite.config.js`) compiles SCSS and JS via `@vite()` directives in Blade.
- **Bootstrap 5** provides the grid system, modals, form controls, and utility classes.
- **TailwindCSS 4** is available via `@tailwindcss/vite` plugin, though the current styling is primarily Bootstrap-driven with a custom SCSS layer.
- **jQuery** is used globally for DOM manipulation, AJAX requests, and event delegation (imported in `resources/js/app.js` and bound to `window.$`).
- **Bootstrap Icons** (CDN) provides the iconography throughout the UI.
- **DomPDF** (`barryvdh/laravel-dompdf`) renders server-side PDF invoices from Blade templates.

### Testing

- **Pest PHP** is the testing framework.
- Feature tests are located in `tests/Feature/`.
- The `AccountManagementTest.php` covers: admin access to accounts page, account creation with password hashing, username search filtering, and role-based permission assertions (`canEditProducts()`).
- Tests use in-memory schema creation (`Schema::create` in `beforeEach`, `Schema::dropIfExists` in `afterEach`) to isolate test state.

---

## UI/UX Overview

### Design System

The application uses a **Bootstrap 5.3** foundation enhanced with a custom SCSS layer (`resources/css/app.scss`):

- **Custom CSS variables** for consistent stock action coloring:
  - `--stock-in-bg: #97b9f1` (soft blue)
  - `--stock-out-bg: #92b3d2` (muted blue-grey)
  - `--price-blue: #92b3d2`
- **Glassmorphism login screen** — The login page features a split-screen layout with a branded left panel and a translucent "glass" login form on the right (`glass-login-box` class).
- **Responsive sidebar** — Collapsible sidebar with brand logo, icon+text navigation links, and a logout button. On mobile (`≤ 768px`), the sidebar slides in as an overlay rather than collapsing inline.
- **Toast notifications** — A global `x-toast` component renders fixed-position Bootstrap toasts at the bottom-right, triggered by `window.showToast(message, type)`.
- **Sticky selection footers** — When a mode (Stock-In, Stock-Out, Edit, Delete, Export) is activated, a dark sticky footer appears below the viewport with selection count and action buttons.

### Pages & Components

| Page | Route | Accessible To | Purpose |
|------|-------|--------------|---------|
| Login | `/` | Guests only | Split-screen login with shop branding |
| Home Dashboard | `/dashboard/home` | All roles | KPI stat cards + latest products table |
| Products | `/dashboard/products` | Admin, Owner, Staff, Mechanic | Full product listing with search, filters, batch stock, bulk edit |
| Low Stock | `/dashboard/lowstock` | Admin, Owner | Products with 1–2 units remaining |
| No Stock | `/dashboard/nostock` | Admin, Owner | Products with 0 stock |
| Logs | `/dashboard/logs` | Admin, Owner, Staff | Transaction history with category filters + PDF export |
| Accounts | `/dashboard/accounts` | Administrator only | User management with role assignment |

### Shared Components (Blade Components)

- **`<x-layout>`** — Master dashboard wrapper containing the sidebar, top header bar with hamburger toggle, content slot (`{{ $slot }}`), and global toast. Used by all dashboard pages.
- **`<x-product-table>`** — Home page product table with inline stock action buttons, batch mode triggers, and an embedded paginated transaction modal. Enqueues `product-table.js`.
- **`<x-search-result>`** — AJAX-renderable product table fragment used by the Products, Low Stock, and No Stock pages. Contains category badges, quick stock buttons, and pagination links. Enqueues `search-result.js`.
- **`<x-log-table>`** — Transaction log table with checkboxes, per-row PDF export, batch selection mode, and an export review modal. Enqueues `log-page.js` inline.
- **`<x-toast>`** — Global toast container rendered at the bottom of the layout.

### Key UX Patterns

- **Inline action buttons** — Stock-In/Out buttons appear directly in each product row for single-item quick actions.
- **Mode activation** — Clicking "Stock-In", "Stock-Out", "Edit", "Delete", or "Export" activates a selection mode that hides inline action buttons and reveals checkboxes.
- **Cross-page memory** — Selected items and active mode are stored in `sessionStorage`, preserving state across paginated results and even page navigation (within the same page context).
- **Paginated modals** — When many items are selected, the transaction modal paginates the item list (5 per page) to keep the UI usable.
- **Real-time validation** — Stock-out quantities are clamped to available stock; invalid quantities trigger toast error messages.
- **AJAX-first interactions** — Search, filtering, product creation, bulk editing, and stock processing all happen via AJAX with partial DOM updates rather than full page reloads.

---

## System Workflow

### 1. Authentication Flow

```
User → GET / → showLogin() → login.blade.php
User submits credentials → POST /login → AuthController@authenticate()
  ├─ Validate Username + Password
  ├─ Auth::attempt() against Account model (custom Password column)
  ├─ On success: session regenerate → redirect /dashboard/home
  └─ On failure: redirect back with error
```

### 2. Stock-In / Stock-Out Transaction Flow

```
User selects mode (Stock-In or Stock-Out)
  ├─ UI enters Selection Mode: checkboxes appear, inline buttons hide
  ├─ User selects products (checkboxes persist in sessionStorage)
  ├─ User clicks "Review & Process" → modal opens with paginated item list
  │   ├─ User adjusts quantities per item
  │   └─ Modal enforces: Stock-Out qty ≤ current stock
  └─ User submits → POST /dashboard/process-stock
      ├─ DashboardController@processStock()
      ├─ DB::beginTransaction()
      ├─ Generate BatchID (UUID), capture timestamp + user
      ├─ For each item:
      │   ├─ Update Product.Stocks (+qty or -qty)
      │   ├─ Create Log entry with ActionType, Quantity, UnitPrice, TotalPrice, BatchID
      ├─ DB::commit()
      └─ Return JSON success → UI shows toast → page reloads
```

### 3. Product Search & Filter Flow

```
User types in search box or changes category/subcategory dropdown
  ├─ Debounced AJAX call (300ms) to /api/products/search
  ├─ ProductController@search() applies WHERE clauses
  ├─ Paginates results (5 per page)
  └─ Returns rendered <x-search-result> HTML fragment
      └─ #tableContainer is replaced with new HTML
          └─ If in Selection Mode, checkboxes and selection state are reapplied
```

### 4. Log Export Flow

```
User navigates to /dashboard/logs
  ├─ LogController@index() loads logs with eager-loaded product + account
  ├─ Category/subcategory filters applied via whereHas()
  └─ <x-log-table> renders paginated log rows

User selects "Batch Export PDF"
  ├─ Selection mode activates, checkboxes appear
  ├─ Selections stored in sessionStorage
  ├─ User clicks "Review & Generate" → export modal opens
  ├─ User confirms → POST /dashboard/logs/export with log_ids[]
  └─ LogController@exportPdf()
      ├─ Fetches selected Logs with relationships
      ├─ DomPDF loads pdf.invoice template
      ├─ Renders professional invoice PDF
      └─ Forces download: Transaction_Invoice_YYYYMMDD_HHMMSS.pdf
```

### 5. Account Management Flow

```
Administrator navigates to /dashboard/accounts
  ├─ AccountController@index() paginates accounts (5 per page)
  ├─ Real-time AJAX search filters by Username
  └─ Admin can:
      ├─ Add Account → modal form → POST /dashboard/accounts
      │   ├─ Validate: Username unique, Password min 6 + confirmed, Role in allowed list
      │   ├─ Hash::make(Password) before insert
      │   └─ Redirect with success toast
      ├─ Edit Account → modal pre-filled → PUT /dashboard/accounts/{id}
      │   └─ Password updated only if provided (nullable confirmed)
      └─ Delete Accounts → confirmation modal → DELETE /dashboard/accounts
          └─ Self-deletion blocked (current UserID filtered out)
```

---

## Getting Started

### Prerequisites

- PHP ^8.3
- Composer
- Node.js & npm
- SQLite (or configure a different database in `.env`)

### Installation

```bash
# 1. Clone the repository
git clone <repository-url>
cd ELEC3_Montalan

# 2. Install PHP dependencies
composer install

# 3. Copy environment configuration
cp .env.example .env
php artisan key:generate

# 4. Configure database connection in .env
#    Default is SQLite at database/database.sqlite

# 5. Run migrations
php artisan migrate --force

# 6. Seed the database (optional but recommended)
php artisan db:seed --class=AccountSeeder
php artisan db:seed --class=ProductSeeder

# 7. Install frontend dependencies and build assets
npm install
npm run build
```

### Running the Development Server

```bash
# Start all services (Laravel server, queue worker, Vite dev server)
composer run dev

# Or start individually:
php artisan serve
npm run dev
```

---

## Testing

```bash
# Run all tests (Pest)
php artisan test --compact

# Run a specific test file
php artisan test --compact --filter=AccountManagementTest
```

---

## License

MIT
