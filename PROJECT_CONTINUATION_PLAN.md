# GPDISTRO ERP & E-Commerce Continuation Plan

Audit date: 2026-05-31  
Project: GPDISTRO ERP & E-Commerce  
Company: GPDISTRO Racing Indonesia

## Executive Summary

The repository is an early Laravel 12 foundation, not yet a functional ERP or
e-commerce system. It already contains authentication, role middleware, a
premium admin dashboard shell, reusable dashboard UI components, and an
initial database schema for several business domains.

The database scaffold is significantly further ahead than the application
layer. Most business tables do not yet have Eloquent models, repositories,
services, policies, form requests, routes, controllers, API resources, jobs,
or tests. The dashboard is currently a styled prototype with hard-coded
numbers and placeholder links.

Development should continue incrementally. The first milestone must stabilize
the foundation, production dependency installation, authorization boundaries,
asset build behavior, seeders, and Railway deployment before implementing ERP
modules one by one.

## Audit Scope And Evidence

The audit covered:

- Application code under `app/`
- Routes under `routes/`
- Blade views and frontend assets under `resources/`
- All migrations, factories, and seeders under `database/`
- Laravel configuration under `config/`
- Composer and Node dependencies
- CI workflows under `.github/workflows/`
- Git status and existing user-owned working tree changes
- Local runtime checks with the installed XAMPP PHP executable

Runtime verification results:

- Laravel version: `12.61.0`
- Local PHP version: `8.2.12`
- Local database: SQLite
- Registered application routes: `21`
- Applied migrations: `5 / 5`
- Local database tables: `29`
- Composer manifest validation: passed
- Production asset build: completed, but generated manifest keys are invalid
  for Laravel on this Windows environment
- Test suite: `17 passed`, `8 failed`

Existing working tree edits in localized error views, `lang/id.json`,
`PROJECT_CONTEXT.md`, and `erp-gpdistro.code-workspace` were not modified by
this audit.

## 1. Current Project Architecture Report

### Current Structure

The application currently follows the default Laravel Breeze Blade structure
with a small ERP admin layer:

| Layer | Current State |
| --- | --- |
| Framework | Laravel 12 application skeleton |
| Authentication | Laravel Breeze controllers, requests, views, and profile management |
| Authorization | Spatie role middleware configured for the admin dashboard |
| Controllers | Breeze auth controllers, profile controller, one admin dashboard controller |
| Models | Only `User` exists |
| Service layer | Not implemented |
| Repository layer | Not implemented |
| Policies | Not implemented |
| Events and listeners | Only Laravel authentication events |
| Jobs and queues | Framework queue tables exist; no application jobs |
| API resources | Not implemented |
| API routes | Not implemented |
| UI | Breeze views plus a custom premium admin dashboard shell |
| Database | SQLite locally; MySQL configuration exists but is not active locally |

### Existing Patterns Worth Keeping

- Laravel Form Requests are already used for login and profile updates.
- Admin routes are grouped under an `admin` prefix with role middleware.
- The dashboard uses small reusable Blade components:
  `x-ui.stat-card`, `x-ui.card`, and `x-ui.action-tile`.
- The admin shell has a sticky top bar, responsive navigation, mobile menu,
  dark automotive styling, and wide content containers.
- UUID columns are already present on several externally addressable business
  records.
- Foreign keys, uniqueness constraints, and several query-oriented indexes
  are present in the initial schema.

### Required Target Architecture

Continue with a modular monolith. Each business module should own its domain
code while still using Laravel conventions:

```text
app/
  Domain/
    Inventory/
    Sales/
    Purchasing/
    Production/
    Finance/
    HRD/
    CRM/
    Ecommerce/
    Integrations/
  Http/
    Controllers/
    Requests/
    Resources/
  Models/
  Policies/
```

Within each module, introduce repositories only where query reuse or data
access complexity justifies them. Use services for business transactions,
events for cross-module reactions, and queued jobs for slow external work.
Keep controllers thin and wrap stock, order, and payment mutations in database
transactions.

## 2. Existing Module Report

Status meanings:

- **Available**: usable application behavior exists.
- **Prototype**: visible UI or schema exists, but the workflow is not usable.
- **Schema only**: tables exist without an application layer.
- **Missing**: no meaningful implementation exists.

| Module | Status | Evidence |
| --- | --- | --- |
| Authentication | Available with issues | Login, registration, password reset, profile, logout, and verification routes exist |
| Roles | Prototype | Spatie tables, middleware aliases, and `Super Admin`, `Owner`, `Manager` seed roles exist |
| ERP dashboard | Prototype | Premium UI exists, but metrics, activity, warnings, and actions are hard-coded |
| Inventory | Schema only | `warehouses`, `brands`, `categories`, `products`, `inventories` |
| Warehouse | Schema only | Warehouse master table exists |
| Sales orders | Schema only | `customers`, `orders`, `order_items` |
| Payments | Schema only | `payments` |
| Shipping | Schema only | `shipments` |
| Suppliers | Schema only | `suppliers` |
| Production | Schema only | `production_orders` |
| HRD | Schema only | `attendances`, `payrolls` |
| Finance | Schema only | `expenses`; payment records exist |
| Purchasing | Missing | Supplier master exists, but purchase workflow tables do not |
| CRM | Prototype | Customer table exists without CRM workflow |
| Reporting | Missing | No queries, exports, charts, or report screens |
| E-commerce storefront | Missing | No public catalog, product detail, or customer storefront |
| Cart and checkout | Missing | No cart, addresses, checkout, or fulfillment flow |
| Customer account | Missing | No customer portal or order tracking UI |
| Wishlist and reviews | Missing | No tables or application layer |
| Integrations | Missing | No Raja Ongkir, Midtrans, Xendit, WhatsApp, or marketplace adapters |

## 3. Missing Module Report

### ERP Gaps

- Inventory needs product variants, stock ledger movements, adjustments,
  reservations, transfers, stock opname, and low-stock workflows.
- Warehouse needs warehouse CRUD, locations or bins if required, transfer
  approval, and receiving workflows.
- Purchasing needs purchase requests, purchase orders, purchase order items,
  goods receipts, supplier invoices, and approval states.
- Production needs production items, bill of materials or material
  requirements, design approval artifacts, workflow history, QC records, and
  work assignment.
- Finance needs chart of accounts, journal entries, receivables, payables,
  expense attachments, reconciliation, and period reporting.
- HRD needs employee records separate from login users, departments, shifts,
  leave requests, payroll detail, and approval workflows.
- CRM needs contacts, addresses, notes, interactions, segments, and sales
  history.
- Reporting needs operational dashboards, filtering, exports, and scheduled
  report generation.

### E-Commerce Gaps

- Product variants, images, inventory-aware catalog visibility, SEO metadata,
  and pricing rules
- Cart, checkout, address book, shipping quote selection, voucher handling,
  and customer order history
- Payment gateway callbacks with idempotency and signature verification
- Shipment tracking and customer notifications
- Wishlist and verified product review workflow

### Integration Gaps

- Raja Ongkir shipping rate adapter and location mapping
- Midtrans and Xendit payment adapters with webhook handling
- WhatsApp provider adapter with queued notification jobs
- Marketplace connector contracts, sync jobs, retry strategy, and audit log

## 4. Technical Debt Report

### Critical

1. `spatie/laravel-permission` is listed in `require-dev`, but runtime code uses
   `HasRoles` and Spatie middleware. A production install with
   `composer install --no-dev` will fail.
2. The generated Vite manifest uses absolute Windows keys such as
   `D:/xampp/htdocs/erp-gpdistro/resources/css/app.css`. Laravel requests the
   relative key `resources/css/app.css`, causing rendered pages to return HTTP
   500 after the build. This is the direct cause of the 8 failing tests.
3. `DatabaseSeeder` creates `test@example.com` without a password. On a clean
   database, the non-null `users.password` column can make seeding fail.

### High

1. Business tables have no Eloquent models or domain services.
2. The dashboard displays hard-coded sample metrics and sample activity.
3. Two admin layout implementations exist:
   `resources/views/components/layouts/admin.blade.php` is used, while
   `resources/views/layouts/admin.blade.php` is duplicated and stale.
4. The initial ERP schema is stored in one large migration. Future changes
   should use small additive migrations rather than editing it after release.
5. The dashboard contains placeholder `#` links and actions with no workflow.
6. The dashboard activity text contains mojibake (`Â·`) and should be normalized
   to UTF-8 or replaced with an ASCII separator.
7. The repository README is still the default Laravel README.

### Medium

1. The admin sidebar status claims queues, workers, and APIs are active even
   though no runtime health check supports that statement.
2. `HorizonServiceProvider` exists but is not registered in
   `bootstrap/providers.php`.
3. Horizon is configured for Redis workers while local queues use the database
   driver. The intended production queue architecture is not documented.
4. The public landing page is the default Laravel welcome view.
5. CI only runs PHP tests and does not build frontend assets.
6. The CI push branch filter targets `master`, while the repository branch is
   `main`.
7. The local runtime uses PHP `8.2.12`, while the stated desired stack is PHP
   `8.3`. `composer.json` currently allows PHP `^8.2`.

## 5. Security Issue Report

### Critical And High Priority

1. Public registration is enabled. Newly registered users can access
   `/dashboard`, which renders the ERP dashboard without role middleware.
   Decide whether registration should be storefront-only, invitation-only, or
   disabled until customer accounts are separated from staff accounts.
2. `User` does not implement `MustVerifyEmail`, even though dashboard routes
   use the `verified` middleware. The interface is commented out, so email
   verification enforcement is ineffective.
3. The role system seeds roles but not granular permissions or policies.
   Sensitive future finance, payroll, and stock actions must not rely only on
   broad route-level roles.
4. Payment webhook endpoints do not exist yet. When introduced, require
   signature verification, replay protection, idempotency keys, and audit
   logs.

### Production Hardening Needed

- Set `APP_ENV=production`, `APP_DEBUG=false`, and the public `APP_URL`.
- Set `SESSION_SECURE_COOKIE=true` behind HTTPS.
- Decide whether `SESSION_ENCRYPT=true` is required for the production threat
  model.
- Restrict Horizon access and register its provider before exposing it.
- Add audit trails for inventory mutation, payment verification, payroll
  approval, expense changes, and role changes.
- Store uploads on durable object storage with validation and private access
  rules where appropriate.
- Add rate limits for sensitive public endpoints and future integrations.

## 6. Performance Issue Report

The current application has little live business logic, so the main risks are
architectural:

1. Sessions, cache, and queues default to the database. This is acceptable for
   local development but should move to Redis for production scaling.
2. Horizon is installed but cannot manage database queues; use Redis
   consistently if Horizon is retained.
3. Local filesystem storage is unsuitable for durable uploads on ephemeral
   Railway containers. Use S3-compatible object storage.
4. Dashboard metrics must be query-backed carefully. Cache expensive
   aggregates and avoid recalculating all operational summaries per request.
5. Stock must use an append-only movement ledger with transactional updates
   rather than relying only on mutable aggregate inventory values.
6. List screens should use pagination, indexed filters, eager loading, and
   query monitoring as modules are added.
7. External gateway and notification calls should run through queued jobs with
   retries, backoff, and idempotency.

## 7. Database Structure Report

### Framework And Access Tables

| Tables | Purpose |
| --- | --- |
| `users`, `password_reset_tokens`, `sessions` | Authentication and sessions |
| `cache`, `cache_locks` | Database cache |
| `jobs`, `job_batches`, `failed_jobs` | Database queue infrastructure |
| `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` | Spatie authorization |

### ERP Core Tables

| Domain | Tables | Notes |
| --- | --- | --- |
| Catalog | `brands`, `categories`, `products` | Basic product master; variants and media missing |
| Inventory | `warehouses`, `inventories` | Aggregate stock per product and warehouse; movement ledger missing |
| CRM | `customers`, `suppliers` | Basic contact records only |
| Sales | `orders`, `order_items` | Basic sales order totals and snapshot item names |
| Payments | `payments` | Basic manual and gateway-oriented payment record |
| Shipping | `shipments` | Basic courier, tracking, and lifecycle fields |
| Production | `production_orders` | Header-level production workflow only |
| HRD | `attendances`, `payrolls` | Basic user-linked attendance and payroll |
| Finance | `expenses` | Basic expense record only |

### Schema Strengths

- Foreign keys are present for primary relationships.
- Several public-facing records include UUIDs.
- Financial values use decimal columns.
- Categories support hierarchy through `parent_id`.
- Inventory uniqueness is enforced per product and warehouse.
- Useful status and lookup indexes are already present.

### Schema Improvements Before Module Expansion

Do not duplicate the existing tables. Add small migrations as workflows are
implemented:

- Introduce product variants and product media.
- Add stock movements, reservations, transfers, and stock opname details.
- Add customer addresses rather than a single free-text customer address.
- Add purchasing tables and receiving records.
- Add order status history and payment webhook event records.
- Add production materials, workflow history, QC, and attachments.
- Add employee profiles separate from login users.
- Add finance ledger structures when accounting scope is confirmed.
- Add actor references and audit logs for sensitive mutations.

## 8. Railway Deployment Readiness Report

### Current Readiness: Not Ready

No Railway deployment definition is present:

- No `Dockerfile`
- No `railway.json`
- No `Procfile`
- No `nixpacks.toml`
- No documented web start command
- No documented release migration command
- No worker service definition

### Required Railway Baseline

1. Choose a deployment strategy: Docker is recommended for predictable PHP
   extensions, Node asset build, and web server configuration.
2. Configure a web service with a health check against Laravel's existing
   `/up` endpoint.
3. Configure a release step for `php artisan migrate --force`.
4. Add a separate Redis-backed worker service. If Horizon is retained, run
   `php artisan horizon`; otherwise use `php artisan queue:work`.
5. Configure Railway MySQL and Redis services and production environment
   variables.
6. Use S3-compatible durable storage for uploaded product media, payment proof,
   and production artifacts.
7. Run production optimization commands during deploy:
   `config:cache`, `route:cache`, and `view:cache`.
8. Set `APP_NAME`, `APP_ENV`, `APP_DEBUG`, `APP_URL`, `APP_KEY`, database,
   Redis, mail, storage, session, and queue variables explicitly.

## Completed Features

- Laravel 12 skeleton and dependency installation
- Breeze authentication and profile workflow
- Spatie role table migration and admin role middleware aliases
- Initial role seeder for `Super Admin`, `Owner`, and `Manager`
- Initial ERP core database migration
- Premium responsive admin shell with dark visual language
- Reusable dashboard Blade UI components
- Local Indonesian locale configuration and localized admin labels
- Basic CI PHP test workflow
- Laravel `/up` health endpoint

## In-Progress Features

- ERP dashboard: polished shell, but no live metrics or real actions
- Authorization: roles exist, but permission matrix and policies do not
- Inventory, sales, payments, shipment, production, HRD, and finance:
  database headers exist, but workflows do not
- Localization: partly implemented
- Horizon: installed and configured, but production architecture is incomplete

## Recommended Development Roadmap

### Phase 0: Stabilize The Foundation

1. Move `spatie/laravel-permission` into production Composer dependencies.
2. Fix Vite manifest generation on Windows and make the test suite green.
3. Repair the seeder and replace the unsafe default test account strategy.
4. Decide staff versus customer authentication boundaries.
5. Enforce email verification intentionally or remove misleading middleware.
6. Remove the stale admin layout and replace hard-coded system health claims.
7. Update CI to build assets, test the `main` branch, and validate production
   dependency installation.
8. Replace the default README with project setup instructions.

### Phase 1: Railway Deployment Baseline

1. Add Docker and Railway configuration.
2. Add MySQL, Redis, durable storage, environment documentation, health check,
   release migration, and worker process.
3. Register and secure Horizon if Redis queues are selected.
4. Verify a staging deploy before adding operational modules.

### Phase 2: Inventory And Warehouse

1. Add models, relationships, policies, form requests, services, repositories,
   and tests for warehouse, catalog, and inventory.
2. Add variants, stock movements, adjustments, reservations, transfers, and
   stock opname.
3. Connect dashboard low-stock metrics to real queries.

### Phase 3: Purchasing And Suppliers

1. Add purchase requests, purchase orders, receiving, supplier invoices, and
   approvals.
2. Connect inventory receipt posting through domain services and events.

### Phase 4: Sales, CRM, And Fulfillment

1. Implement customer profiles, addresses, sales orders, order histories,
   payments, and shipments.
2. Add stock reservation and release rules with database transactions.
3. Add queued notifications and operational audit logs.

### Phase 5: Production

1. Implement custom garment production details, material requirements, design
   approval, production progress, QC, and attachments.
2. Connect production demand to stock and purchasing.

### Phase 6: E-Commerce

1. Build catalog, product detail, cart, checkout, customer account, order
   tracking, wishlist, and reviews.
2. Reuse sales, inventory, shipment, and customer services from ERP modules.

### Phase 7: Integrations

1. Add Raja Ongkir shipping quotes.
2. Add Midtrans and Xendit adapters with secure webhook processing.
3. Add WhatsApp queued notifications.
4. Add marketplace sync contracts and implement connectors incrementally.

### Phase 8: Finance, HRD, And Reporting

1. Confirm accounting scope and add ledger-backed finance workflows.
2. Expand HRD with employee profiles, leave, shifts, and payroll details.
3. Add exports, dashboards, scheduled reports, and operational monitoring.

## Implementation Rules For The Next Iterations

- Continue the current application; do not rewrite it wholesale.
- Check the existing schema before adding any table.
- Prefer additive migrations after the existing core migration.
- Keep controllers thin.
- Put transaction-oriented business rules in services.
- Use repositories for reusable or complex persistence queries.
- Use Form Requests for validation and Policies for authorization.
- Use Events and Listeners for cross-domain side effects.
- Use Jobs and Queues for integrations, notifications, imports, exports, and
  expensive processing.
- Add API Resources when building JSON endpoints or integration surfaces.
- Add tests with each module, including authorization and failure paths.
- Preserve the premium responsive admin UI and improve it incrementally.

## Immediate Next Milestone

Phase 0 foundation stabilization and the first Inventory/Warehouse vertical
slice were implemented on 2026-05-31:

- Fixed production dependency classification for Spatie Permission.
- Fixed Windows Vite manifest normalization and restored a green test suite.
- Disabled public registration until customer authentication is designed.
- Enforced email verification for users.
- Repaired the default admin seeder.
- Added Docker and Railway deployment baseline documentation.
- Added Warehouse creation, Product creation with initial stock, Inventory
  listing, stock adjustment, and append-only stock movement records.
- Added Supplier creation and Purchase Order creation with item totals.
- Added owner approval, partial goods receipt records, per-item received
  quantities, and transactional receipt posting into inventory.
- Added role policies, form requests, repositories, services, and tests.

The next implementation milestone is **Supplier Invoice And Sales Order
Foundation**: add supplier invoice handling, then begin customer, sales order,
stock reservation, and fulfillment workflows that reuse the inventory ledger.

## Local Development Runbook

Local-first setup was added on 2026-05-31. The default local path uses SQLite
and Laravel's development server, so Apache and MySQL XAMPP do not need to run.

1. Run `local-setup.bat`.
2. Run `local-start.bat`.
3. Open `http://127.0.0.1:8000`.
4. Login with `admin@gpdistro.test` and `change-me-now`.

The setup script applies migrations, inserts idempotent demo data, and builds
frontend assets. Demo data includes a warehouse, supplier, normal-stock SKU,
and low-stock SKU for dashboard and inventory testing.
