---
description: Run comprehensive Playwright-headed and PHPUnit tests for RESTAURANT_ERP
---

# Comprehensive Testing Workflow

## Purpose

Execute a full test pass covering API smoke tests, UI/UX flows, and role-based navigation using Playwright in headed mode and PHPUnit.

## Prerequisites

- XAMPP (LAMPP) Apache and MySQL running (`sudo /opt/lampp/lampp start`).
- Database imported (`DATABASE/EBP_RESTAURANT_CAFE_MYSQL_SCHEMA.sql` + `DATABASE/SEED_DATA.sql`).
- `BACKEND/.env` configured.
- Root `node_modules` installed (run `npm install` in project root if missing).
- `BACKEND/node_modules` installed (run `npm install` in `BACKEND` if missing).

## Test Users

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | Administrator |
| manager | admin123 | Restaurant Manager |
| waiter | admin123 | Waiter |
| kitchen | admin123 | Kitchen Staff |
| cashier | admin123 | Cashier |
| inventory | admin123 | Inventory Manager |
| host | admin123 | Host/Hostess |

## Steps

1. **Start the backend dev server (optional — Apache via XAMPP also works)**

```bash
cd /opt/lampp/htdocs/restauran/BACKEND
/opt/lampp/bin/php -S localhost:8000 -t public
```

Alternatively, use XAMPP Apache: `http://localhost/restoran/api/v1/...`

2. **Run PHPUnit smoke tests**

```bash
cd /opt/lampp/htdocs/restauran/BACKEND
/opt/lampp/bin/php vendor/phpunit/phpunit/phpunit --testsuite "Smoke Tests"
```

3. **Run Playwright tests (root E2E specs)**

```bash
cd /opt/lampp/htdocs/restauran
npx playwright test --config playwright.config.ts
```

4. **Run Playwright tests (BACKEND specs, headed mode)**

```bash
cd /opt/lampp/htdocs/restauran/BACKEND
npx playwright test --project chromium --headed
```

## Scenarios to Cover

1. **Authentication** — login happy path and invalid credentials.
2. **Dashboard navigation** — each `data-page` in `.sidebar-nav`.
3. **Role switching** — `select#roleSwitcher` updates visible menu items.
4. **Consumer app** — open/close sidebar, navigate pages.
5. **Kiosk ordering** — select category, add product, verify `#orderSummary`.
6. **Mobile/Waiter** — navigate `orders`, `tables`, `reservations`.
7. **API smoke** — `/auth/health`, `/auth/login`, `/settings`, `/menu/*`, `/tables/*`, `/reservations`, `/inventory`, `/kitchen/orders`, `/reports/sales`.
8. **Authorization** — missing/invalid token returns `401`.
9. **Tier 1-4 endpoints** — run `tests/e2e/tier1-4-api.spec.ts` for POS reconciliation, beverage variance, recipe depletion, batch expiry, settlements, event profitability, BEO proposals, nightclub advanced, karaoke advanced, beach club, sports bar, operations, venue, and misc features.

## Verification

- Console errors should be 0.
- Network failures should be 0.
- All test assertions green.

## Reports

- Playwright HTML report: `BACKEND/playwright-report/index.html` (after BACKEND run).
- Root Playwright HTML report: `playwright-report/index.html` (after root run).
- PHPUnit coverage: `BACKEND/coverage/index.html` (run with `--coverage-html coverage`).
