---
description: Run comprehensive Playwright-headed and PHPUnit tests for RESTAURANT_ERP
---

# Comprehensive Testing Workflow

## Purpose

Execute a full test pass covering API smoke tests, UI/UX flows, and role-based navigation using Playwright in headed mode and PHPUnit.

## Prerequisites

- XAMPP Apache and MySQL running.
- Database imported (`DATABASE/EBP_RESTAURANT_CAFE_MYSQL_SCHEMA.sql` + `DATABASE/SEED_DATA.sql`).
- `BACKEND/.env` configured.
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

1. **Start the backend dev server**

```powershell
cd BACKEND
C:\xampp\php\php.exe -S localhost:8000 -t public
```

2. **Run PHPUnit smoke tests**

```powershell
cd BACKEND
C:\xampp\php\php.exe vendor\phpunit\phpunit\phpunit --testsuite "Smoke Tests"
```

3. **Run Playwright tests in headed mode**

```powershell
cd BACKEND
npx playwright test --project chromium --headed
```

To run only the root E2E spec:

```powershell
cd c:\xampp\htdocs\restoran
npx playwright test --config playwright.config.ts
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

## Verification

- Console errors should be 0.
- Network failures should be 0.
- All test assertions green.

## Reports

- Playwright HTML report: `BACKEND/playwright-report/index.html` (after run).
- PHPUnit coverage: `BACKEND/coverage/index.html` (run with `--coverage-html coverage`).
