# RESTAURANT_ERP — Deep Audit, Cleanup Plan & Playwright Testing Knowledge

## 1. Executive Summary

Project `/opt/lampp/htdocs/restauran` (RESTAURANT_ERP) contains a PHP/MySQL backend, HTML/JS/CSS frontend, Playwright/PHPUnit tests, and extensive documentation. The repository is functional but carries a large amount of generated artifacts, obsolete manual test harnesses, and duplicated configuration that inflate the working tree and slow CI/package operations.

Key facts from the audit:

- **2,815 tracked files** (Git), dominated by `BACKEND/`.
- **BACKEND/vendor/** is tracked (≈6 MB, 1,821 files) because `bootstrap.php` requires `vendor/autoload.php` for PSR-4 autoloading of `App\Core\` and `App\Modules\`.
- **BACKEND/tests/** contains many obsolete consumer test scripts, shell harnesses, and 10 MB of simulation reports.
- **Generated artifacts** (`test-results`, `playwright-report`, `screenshots`, `logs`, `.phpunit.result.cache`) are ignored by Git but still present on disk (≈34 MB).
- Two `playwright.config.ts` files exist (root and `BACKEND/`) with different `baseURL` and `testDir` settings.
- One Devin workflow exists (`.devin/workflows/ai-development-cycle.md`); it should be extended with cleanup and testing workflows.

## 2. Project Structure Snapshot

```
restoran/
├── BACKEND/           # PHP API, core, modules, routes, migrations, vendor, tests
│   ├── public/        # API router (index.php) + compiled SPA (index.html)
│   ├── core/          # Router, JWT, Database, Middleware, Engines
│   ├── modules/       # 46+ feature modules
│   ├── routes/        # api.php + per-module route files in routes/api/
│   ├── migrations/    # PHP migration runner + 038 migration files
│   ├── vendor/        # Composer dependencies (required, tracked)
│   ├── tests/         # PHPUnit + Playwright tests
│   └── DOCUMENTATION/
├── FRONTEND/          # consumer/, dashboard/, kiosk/, mobile/, css/, js/
├── DATABASE/          # schema, seed data, ERD docs
├── DOCUMENTATION/     # guides, reports, research, prompting
├── tests/e2e/         # root-level Playwright E2E spec
├── .devin/workflows/  # AI development-cycle workflow
├── playwright.config.ts
└── package.json
```

## 3. Findings — Files/Folders That Are Not Needed

### 3.1 Generated / Ignored Artifacts (safe to delete from working tree)

| Path | Approx Size | Reason |
|------|-------------|--------|
| `BACKEND/test-results/` | 17 MB | Playwright test artifacts (videos, traces, screenshots) |
| `BACKEND/playwright-report/` | 15 MB | HTML Playwright report output |
| `BACKEND/screenshots/` | 0.3 MB | Ad-hoc screenshot output |
| `BACKEND/logs/app.log` | 1.4 MB | Runtime log |
| `BACKEND/.phpunit.result.cache` | 3 KB | PHPUnit cache |
| `BACKEND/public/.htaccess.backup` | <1 KB | Backup file |
| `BACKEND/package-lock.json` | 3 KB | Duplicated lock; root lock exists |

### 3.2 Obsolete Tracked Test Harnesses & Reports (safe to `git rm`)

24 files, ≈10.2 MB, located under `BACKEND/tests/`:

- `consumer-direct-sim.php`, `consumer-direct-test.php`, `consumer-full-test.php`, `consumer-manual-test.php`, `consumer-sim.php`, `consumer-simple-test.php`, `consumer-step-test.php`, `consumer-test.php`
- `test-featured.php`, `test-login.php`
- `run-consumer-tests.sh`, `test-phase1-modules.sh`, `test-role-api.sh`
- `test-role-based-ui.html`, `test-role-ui-manual.md`
- `PRODUCTION_SIMULATION_REPORT_2026-07-05.md`
- `REAL_ACTIVITY_SIMULATION_REPORT_2026-07-05.md`
- `SIMULATION_REPORT_2026-07-05.md`
- `TEST_REPORT_2026-07-05.md`
- `production-simulation.php`
- `production-simulation-reports/` (`daily-data.csv`, `production-simulation-data.json`, `production-simulation-report.html`, `production-simulation-summary.txt`)

These are one-off manual scripts/reports replaced by the Playwright/PHPUnit suite.

### 3.3 Optional Tool Binaries

- `BACKEND/composer.phar` (3.6 MB)
- `BACKEND/phpunit-9.6.phar` (5.1 MB)

Both are ignored by Git. If Composer/PHPUnit are installed globally, they can be removed and `composer.json` scripts should point to `vendor/bin/phpunit` instead of the `.phar`.

### 3.4 Keep

- `BACKEND/vendor/` — required for `vendor/autoload.php` and PSR-4 autoloading.
- `BACKEND/node_modules/` — required to run Playwright; can be regenerated with `npm install`.
- `BACKEND/tests/*.spec.ts`, `BACKEND/tests/api/`, `BACKEND/tests/unit/`, `BACKEND/tests/integration/` — active test assets.
- `BACKEND/migrations/` — active database migrations.
- `DATABASE/*` — canonical schema and seed data.

## 4. Cleanup Commands

Run from `/opt/lampp/htdocs/restauran`.

```bash
# 1. Remove generated artifacts from working tree (ignored, no git rm needed)
rm -rf BACKEND/test-results BACKEND/playwright-report BACKEND/screenshots
rm -f BACKEND/.phpunit.result.cache BACKEND/public/.htaccess.backup BACKEND/package-lock.json BACKEND/logs/app.log

# 2. Remove optional tool binaries if global composer/phpunit are available
rm -f BACKEND/composer.phar BACKEND/phpunit-9.6.phar

# 3. Remove obsolete tracked files (git rm)
obsolete_files=(
  'BACKEND/tests/consumer-direct-sim.php' 'BACKEND/tests/consumer-direct-test.php' 'BACKEND/tests/consumer-full-test.php' 'BACKEND/tests/consumer-manual-test.php' 'BACKEND/tests/consumer-sim.php' 'BACKEND/tests/consumer-simple-test.php' 'BACKEND/tests/consumer-step-test.php' 'BACKEND/tests/consumer-test.php'
  'BACKEND/tests/test-featured.php' 'BACKEND/tests/test-login.php'
  'BACKEND/tests/run-consumer-tests.sh' 'BACKEND/tests/test-phase1-modules.sh' 'BACKEND/tests/test-role-api.sh'
  'BACKEND/tests/test-role-based-ui.html' 'BACKEND/tests/test-role-ui-manual.md'
  'BACKEND/tests/PRODUCTION_SIMULATION_REPORT_2026-07-05.md' 'BACKEND/tests/REAL_ACTIVITY_SIMULATION_REPORT_2026-07-05.md' 'BACKEND/tests/SIMULATION_REPORT_2026-07-05.md' 'BACKEND/tests/TEST_REPORT_2026-07-05.md'
  'BACKEND/tests/production-simulation.php' 'BACKEND/tests/production-simulation-reports/daily-data.csv' 'BACKEND/tests/production-simulation-reports/production-simulation-data.json' 'BACKEND/tests/production-simulation-reports/production-simulation-report.html' 'BACKEND/tests/production-simulation-reports/production-simulation-summary.txt'
)
for f in "${obsolete_files[@]}"; do git rm --cached "$f" -q; done
rm -rf BACKEND/tests/production-simulation-reports
```

## 5. Playwright-Headed-Browser Testing Knowledge

This section is the canonical reference for `playwright-headed-browser` and any AI-driven Playwright test runs.

### 5.1 Entry Points

| Environment | URL |
|-------------|-----|
| Login page | `http://localhost/restoran/FRONTEND/login.html` |
| Consumer app | `http://localhost/restoran/FRONTEND/consumer/index.html` |
| Dashboard | `http://localhost/restoran/FRONTEND/dashboard/index.html` |
| Kiosk | `http://localhost/restoran/FRONTEND/kiosk/index.html` |
| Mobile/Waiter | `http://localhost/restoran/FRONTEND/mobile/index.html` |
| API base (root config) | `http://localhost/restoran/BACKEND/public/api/v1` |
| API base (BACKEND dev server) | `http://localhost:8000/api/v1` |

### 5.2 Default Test Users

| Username | Password | Role |
|----------|----------|------|
| `admin` | `admin123` | Administrator |
| `manager` | `admin123` | Restaurant Manager |
| `waiter` | `admin123` | Waiter |
| `kitchen` | `admin123` | Kitchen Staff |
| `cashier` | `admin123` | Cashier |
| `inventory` | `admin123` | Inventory Manager |
| `host` | `admin123` | Host/Hostess |

### 5.3 Key DOM Selectors

**Login (`FRONTEND/login.html`)**
- `form#loginForm`
- `input#username`
- `input#password`
- `button#loginBtn`
- `div#errorMessage`

**Dashboard (`FRONTEND/dashboard/index.html`)**
- `aside#sidebar`
- `.sidebar-nav .nav-item` (with `data-page` attribute: `overview`, `orders`, `menu`, `tables`, `inventory`, `kitchen`, `reservations`, `customers`, `reports`, `ai`, `settings`)
- `button#logoutBtn`
- `p#userName`
- `#userRoles .role-badge`
- `#roleSwitcherContainer` / `select#roleSwitcher`
- `#soloModeContainer` / `button#soloModeToggle`

**Consumer App (`FRONTEND/consumer/index.html`)**
- `button#menuBtn`
- `nav#sidebar` / `button#closeSidebar`
- `.menu-item` with `data-page`: `home`, `search`, `reservations`, `orders`, `favorites`, `loyalty`, `settings`, `help`
- `div#userInfo`
- `button#langBtn`
- `button#profileBtn`
- `div#homePage`
- `p#locationText`

**Kiosk (`FRONTEND/kiosk/index.html`)**
- `nav#categoryNav`
- `.category-btn` (data-category `all` + dynamic)
- `div#menuGrid`
- `aside#orderSummary`
- `button#clearOrder`
- `#offlineIndicator`

**Mobile/Waiter (`FRONTEND/mobile/index.html`)**
- `button#menuBtn`
- `nav#sidebar` / `button#closeSidebar`
- `.menu-item` with `data-page`: `orders`, `tables`, `reservations`, `payments`, `settings`
- `p#branchName`
- `p#userName`
- `a#logoutBtn`

### 5.4 Recommended Test Scenarios (Headed Browser)

1. **Login smoke**
   - Navigate to `/FRONTEND/login.html`.
   - Fill `#username` and `#password`, click `#loginBtn`.
   - Assert no `#errorMessage` and localStorage contains `authToken`.

2. **Role-based dashboard navigation**
   - Login as `admin`.
   - Assert `#sidebar` visible, `#userName` text correct.
   - Click each `.nav-item[data-page]` and assert `networkidle` + no console errors.

3. **Consumer app flow**
   - Navigate to `/FRONTEND/consumer/index.html`.
   - Open/close sidebar via `#menuBtn` / `#closeSidebar`.
   - Assert `#homePage` rendered and `#locationText` populated.

4. **Kiosk order building**
   - Navigate to `/FRONTEND/kiosk/index.html`.
   - Wait for `#categoryNav` buttons.
   - Click a category, click a product in `#menuGrid`, assert `#orderSummary` updates.

5. **API smoke**
   - `GET /auth/health`
   - `POST /auth/login` → capture `access_token`
   - `GET /settings`, `GET /menu/categories`, `GET /menu/products`
   - `GET /tables`, `GET /tables/available`
   - `GET /reservations`, `GET /inventory`, `GET /inventory/low-stock`
   - `GET /kitchen/orders`, `GET /reports/sales?date_from=2024-01-01&date_to=2024-12-31`

6. **Authorization**
   - `GET /settings` without token → expect `401`.
   - `GET /settings` with `Bearer invalid_token` → expect `401`.

### 5.5 Playwright Config Notes

- `BACKEND/playwright.config.ts`:
  - `testDir: './tests'`
  - `baseURL: 'http://localhost:8000'`
  - `headless: false` inside Chromium project
  - `viewport: { width: 1440, height: 900 }`
- `root/playwright.config.ts`:
  - `testDir: './tests/e2e'`
  - `baseURL: 'http://localhost/restoran/BACKEND/public/api/v1'`
  - `headless: false`
  - `viewport: { width: 1920, height: 1080 }`
  - `launchOptions.args: ['--display=:0', '--start-maximized']`

When launching with `npx playwright test --headed`, ensure the dev server is running (`php -S localhost:8000 -t BACKEND/public`) and MySQL is up.

## 6. Devin Configuration Updates

- `.devin/workflows/ai-development-cycle.md` — updated to include Cleanup and Testing gates.
- `.devin/workflows/project-cleanup.md` — new workflow with the exact cleanup commands.
- `.devin/workflows/comprehensive-testing.md` — new workflow describing Playwright/PHPUnit setup and scenario matrix.
- `.devin/knowledge/playwright-headed-browser.md` — canonical headless/headed browser testing context and selectors.

## 7. Verification After Cleanup

```bash
# Check working tree status
git status --short

# Run smoke tests (ensure Apache/MySQL are running)
cd /opt/lampp/htdocs/restauran/BACKEND
/opt/lampp/bin/php vendor/phpunit/phpunit/phpunit --testsuite "Smoke Tests"
npx playwright test --project chromium --headed
```

---
**Last updated**: 2026-07-19
**Environment**: Linux XAMPP (`/opt/lampp/`)
**Audit scope**: Full repository structure, dependency footprint, generated artifacts, and test harness inventory.
