# Playwright Headed Browser Knowledge — RESTAURANT_ERP

## Project Context

This knowledge file is used by the `playwright-headed-browser` agent (and any AI test runner) for comprehensive E2E/API testing of RESTAURANT_ERP.

- Stack: PHP 8.x backend (XAMPP/LAMPP on Linux), MySQL 8.x, vanilla HTML/JS/CSS frontend, Playwright 1.61.1, PHPUnit 9.6.
- Backend entry: `BACKEND/public/index.php` (routes `/api/v1/*`).
- Frontend apps: `FRONTEND/login.html`, `FRONTEND/dashboard/index.html`, `FRONTEND/consumer/index.html`, `FRONTEND/kiosk/index.html`, `FRONTEND/mobile/index.html`.
- PHP binary: `/opt/lampp/bin/php`
- Project root: `/opt/lampp/htdocs/restoran`

## URLs

| Page | URL |
|------|-----|
| Login | `http://localhost/restoran/FRONTEND/login.html` |
| Dashboard | `http://localhost/restoran/FRONTEND/dashboard/index.html` |
| Consumer app | `http://localhost/restoran/FRONTEND/consumer/index.html` |
| Kiosk | `http://localhost/restoran/FRONTEND/kiosk/index.html` |
| Mobile | `http://localhost/restoran/FRONTEND/mobile/index.html` |
| API base (root config) | `http://localhost/restoran/BACKEND/public/api/v1` |
| API base (dev server) | `http://localhost:8000/api/v1` |

## Test Accounts

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | Administrator |
| manager | admin123 | Restaurant Manager |
| waiter | admin123 | Waiter |
| kitchen | admin123 | Kitchen Staff |
| cashier | admin123 | Cashier |
| inventory | admin123 | Inventory Manager |
| host | admin123 | Host/Hostess |

## DOM Selectors

### Login (`FRONTEND/login.html`)

```ts
const loginForm = page.locator('form#loginForm');
const usernameInput = page.locator('input#username');
const passwordInput = page.locator('input#password');
const loginBtn = page.locator('button#loginBtn');
const errorMessage = page.locator('div#errorMessage');
```

### Dashboard (`FRONTEND/dashboard/index.html`)

```ts
const sidebar = page.locator('aside#sidebar');
const navItem = (pageName: string) => page.locator(`.sidebar-nav .nav-item[data-page="${pageName}"]`);
const logoutBtn = page.locator('button#logoutBtn');
const userName = page.locator('p#userName');
const userRoles = page.locator('#userRoles .role-badge');
const roleSwitcher = page.locator('select#roleSwitcher');
const soloModeToggle = page.locator('button#soloModeToggle');
```

### Consumer App (`FRONTEND/consumer/index.html`)

```ts
const menuBtn = page.locator('button#menuBtn');
const sidebar = page.locator('nav#sidebar');
const closeSidebar = page.locator('button#closeSidebar');
const menuItem = (pageName: string) => page.locator(`.menu-item[data-page="${pageName}"]`);
const langBtn = page.locator('button#langBtn');
const profileBtn = page.locator('button#profileBtn');
const homePage = page.locator('div#homePage');
const locationText = page.locator('p#locationText');
```

### Kiosk (`FRONTEND/kiosk/index.html`)

```ts
const categoryNav = page.locator('nav#categoryNav');
const menuGrid = page.locator('div#menuGrid');
const orderSummary = page.locator('aside#orderSummary');
const clearOrder = page.locator('button#clearOrder');
const offlineIndicator = page.locator('#offlineIndicator');
```

### Mobile/Waiter (`FRONTEND/mobile/index.html`)

```ts
const menuBtn = page.locator('button#menuBtn');
const sidebar = page.locator('nav#sidebar');
const closeSidebar = page.locator('button#closeSidebar');
const menuItem = (pageName: string) => page.locator(`.menu-item[data-page="${pageName}"]`);
const branchName = page.locator('p#branchName');
const userName = page.locator('p#userName');
const logoutBtn = page.locator('a#logoutBtn');
```

## Recommended Test Scenarios

1. **Authentication**
   - Login with valid credentials and assert `localStorage.authToken`.
   - Login with invalid credentials and assert `#errorMessage` visible.

2. **Dashboard navigation**
   - Login as `admin`, wait for `#sidebar`.
   - Click every `.nav-item[data-page]` and verify page content + 0 console errors.

3. **Role-based UI**
   - Login as `waiter`, assert only allowed pages visible.
   - Login as `kitchen`, assert kitchen order section accessible.

4. **Consumer flow**
   - Open consumer app, toggle sidebar, navigate pages, verify `#homePage`.

5. **Kiosk order building**
   - Wait for `#categoryNav` buttons.
   - Click a category, click a product in `#menuGrid`, verify `#orderSummary` update.

6. **API smoke**
   - `GET /auth/health` → 200
   - `POST /auth/login` → token
   - With token: `GET /settings`, `/menu/categories`, `/menu/products`, `/tables`, `/tables/available`, `/reservations`, `/inventory`, `/inventory/low-stock`, `/kitchen/orders`, `/reports/sales?date_from=2024-01-01&date_to=2024-12-31`
   - Without token / invalid token → 401

## Configuration Notes

- `BACKEND/playwright.config.ts` runs tests against `http://localhost:8000` with `testDir: './tests'`, project-level `headless: false` (headed).
- Root `playwright.config.ts` runs `tests/e2e` against `http://localhost/restoran/api/v1` with `headless: true`.
- Launch backend dev server with: `cd /opt/lampp/htdocs/restoran/BACKEND && /opt/lampp/bin/php -S localhost:8000 -t public`.
- Or use XAMPP Apache directly: API at `http://localhost/restoran/api/v1/...`.

## Commands

```bash
# Backend tests (headed)
cd /opt/lampp/htdocs/restoran/BACKEND
npx playwright test --project chromium --headed

# Root E2E spec
cd /opt/lampp/htdocs/restoran
npx playwright test --config playwright.config.ts

# PHPUnit smoke
cd /opt/lampp/htdocs/restoran/BACKEND
/opt/lampp/bin/php vendor/phpunit/phpunit/phpunit --testsuite "Smoke Tests"
```
