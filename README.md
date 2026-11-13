# Reimagin OS

A studio operating system for **Woodex**, a bespoke furniture workshop in Lahore:
an admin panel, a block-based website CMS with a visual page builder, a live
storefront, and the commerce, CRM and workshop back office behind it.

Built on Laravel 13 (Blade + Tailwind 4 + Alpine), one MySQL-compatible schema,
one source of truth per entity.

---

## What is in it

| Area | Module | What it does |
| --- | --- | --- |
| Overview | Dashboard | Revenue trend, pipeline, workshop load, outstanding balances, recent activity |
| Website | Pages | Draft/published snapshots, revisions, duplication, scheduling, per-page SEO |
| Website | Page builder | Drag-and-drop canvas, 20 block types, autosave, live preview, publish/unpublish |
| Website | Blog, Services, Projects, Testimonials | Content CRUD with slugs, SEO fields and publish flags |
| Website | Navigation | Header/footer menus pointing at pages or URLs |
| Website | Media library | Uploads, alt text, dimensions, usage counts, picker for the builder |
| Commerce | Products, Categories | Prices in minor units, cost, stock, variants, featured flags |
| Commerce | Orders | Manual payment ledger, balances, status flow, notes |
| Commerce | Quotations | Line items, discount/tax/delivery maths, revisions, approve → convert to order |
| Commerce | Appointments | Slot availability, showroom visits, site surveys, consultations |
| Customers | Leads | Pipeline, scoring, assignment, follow-ups, conversion to customer |
| Customers | Customers | Profiles, lifetime value, linked orders and quotations |
| Customers | Conversations | Multi-channel inbox, staff replies, AI-drafted answers, handover |
| Customers | AI sales agent | Rules-engine fallback with an optional model key; intents and escalation log |
| Operations | Inventory | Stock ledger, adjustments, low-stock list, valuation |
| Operations | Purchasing | Suppliers, purchase orders, goods received into stock |
| Operations | Production | Workshop board across the order flow, project stage tracking |
| Operations | Finance | Collected/invoiced/outstanding, monthly revenue, payment mix, debtors |
| Growth | SEO | Meta audit across pages, products and articles |
| Growth | Reports | Date-ranged sales, lead sources, best sellers, top customers, stock value |
| System | Team | Role-based accounts (owner / admin / manager / staff), deactivate instead of delete |
| System | Settings | Brand, contact, commerce, social and integration settings |
| System | Security | Posture checks, active sessions with revoke, recent activity, sign-in history |

Storefront: home, shop + category, product, cart, checkout, order lookup,
journal, projects, services and a CMS catch-all so any published page has a URL.

---

## Running it

```bash
npm install                     # PHP-in-WASM runner, Vite, Tailwind, Alpine
npm run setup                   # vendor manifest + autoloader + key + migrate
npm run build                   # assets
npm run serve                   # http://localhost:8000
```

`npm run setup` finishes by seeding a complete studio dataset — four accounts (`owner@`, `admin@`,
`manager@`, `sales@reimaginos.test`, password `password`), pages, products,
customers, orders, quotations, a live project, suppliers, appointments and
conversations.

Tests:

```bash
node --experimental-wasm-jspi tools/wasm/test.mjs      # 107 feature tests
```

### Why php-wasm

This checkout runs PHP 8.4 inside WebAssembly (`@php-wasm/node`) rather than
using a system PHP binary or Composer, because the build sandbox has neither and
cannot reach Packagist. `tools/wasm/` contains the equivalent plumbing:

- `serve.mjs` — HTTP server that proxies every request into the PHP runtime, so
  Laravel's router, sessions and Blade run unmodified. Binds `0.0.0.0`.
- `artisan.mjs` / `exec.mjs` / `test.mjs` — CLI entry points.
- `gen-autoload.py` + `psr4.php` — generates the autoloader that Composer would
  normally write (`npm run autoload` after adding classes or packages).
- `tools/vendor/` — the dependency fetcher that pulls each package's source
  archive and extracts it into `vendor/`.

Nothing in `app/`, `routes/`, `resources/` or `database/` knows about any of
this: the same code runs under ordinary PHP-FPM with `composer install`.

### Database

**Target: MySQL 8.** Every migration sticks to portable schema features (no
SQLite-only syntax, no `strftime`, dates bucketed in PHP), and `.env.example`
documents the MySQL block.

**Local fallback: SQLite.** There is no database server in the sandbox, so
`.env` and `phpunit.xml` point at `database/database.sqlite` (and
`database/testing.sqlite` for the suite). Switch `DB_CONNECTION` to `mysql`
where a server exists; no code changes are needed.

---

## Structure

```
app/
  Enums/            Role, order/lead/quotation status vocabularies
  Http/
    Controllers/
      Admin/        one controller per module (26)
      Auth/         session sign-in, active-account check, rate limiting
      Site/         storefront + contact/appointment intake
    Middleware/     EnsureAdminPanelAccess, EnsureUserHasRole
    Requests/       (validation lives in controllers via $request->validate)
  Models/           37 Eloquent models, money in integer minor units
  Services/         Cart, QuotationService, StockService, LeadService,
                    AppointmentService, PageService, MediaService,
                    AiAgentService, DashboardService
  Support/          Cart, Money, Slug, Navigation, BlockRegistry, helpers.php
database/
  migrations/       10 files, 41 tables, reversible
  seeders/          one coherent studio dataset
resources/views/
  admin/            panel screens, one folder per module
  site/             storefront + 20 block views
  components/       ui/*, admin/*, site/*, layouts/*
routes/web.php      admin (gated) → storefront → CMS catch-all, in that order
tests/Feature/      AdminSmokeTest, AdminModulesTest, AdminWriteTest,
                    StorefrontTest, CommerceTest
```

### Conventions worth knowing

- **Money** is `*_minor` integers; helpers `money()` and `Money::ofPercent()`
  handle display and percentage maths. Forms collect rupees, controllers
  convert.
- **Roles** are ordered owner > admin > manager > staff. The sidebar hides what
  a role cannot reach *and* the routes enforce it, so the two can never drift.
  Accounts are deactivated, never deleted, so records stay attributed.
- **Publishing** is a two-column draft/published model: the builder never
  writes to the live site until `PageService::publish()` copies the snapshot.
- **Stock** only changes through `StockService`, which writes a ledger row for
  every movement and clamps at zero — the ledger is the source of truth, the
  product column is a cache that can be rebuilt.
- **Quotations → orders** is guarded by a unique `orders.quotation_id` plus an
  explicit check, so a double submit cannot double-count revenue.
- **Unknown block types** are dropped on save rather than rendering nothing.

---

## Verified in this repo

- 107 feature tests, 247 assertions, green under PHP 8.4/WASM.
- Every admin screen renders against seeded data: `AdminModulesTest` walks all
  27 index screens, 13 create screens, 11 edit screens, the revisions and
  preview views, and asserts no named admin GET route is dangling.
- Every write path is exercised through its real route and the redirect target
  is rendered (`AdminWriteTest`, 19 cases).
- The buying path — add to cart, checkout, order lookup — creates a real order,
  customer and stock movement (`StorefrontTest`).
- Money rules: payment cannot exceed the balance, delivered orders cannot
  reopen, stock cannot go negative, quotations recalculate and convert once
  (`CommerceTest`).
