# EBP Restaurant ERP - Backend

## Project Structure

**Note:** Frontend files are located in `../FRONTEND/frontend/` directory for better separation of concerns.

```
PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/
├── BACKEND/          (This directory - PHP API Server)
│   ├── public/
│   ├── core/
│   ├── modules/
│   ├── routes/
│   ├── database/
│   ├── tests/
│   ├── vendor/
│   └── DOCUMENTATION/
├── FRONTEND/         (Frontend assets - mobile, kiosk, consumer, dashboard, css, js)
│   └── frontend/
│       ├── consumer/
│       ├── kiosk/
│       ├── mobile/
│       ├── dashboard/
│       ├── css/
│       └── js/
├── DATABASE/         (Database schema & migrations)
└── DOCUMENTATION/    (Documentation, research, prompting)
```

├── public/
│   ├── index.php
│   └── pos.js
│
├── core/
│   ├── Router.php
│   ├── Response.php
│   ├── JWT.php
│   ├── Transaction.php
│   ├── Audit.php
│   ├── Logger.php
│   ├── Database.php
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   ├── TenantMiddleware.php
│   │   ├── PermissionMiddleware.php
│   │   └── ErrorHandler.php
│   └── Engines/
│       ├── StockEngine.php
│       ├── KitchenEngine.php
│       └── AccountingEngine.php
│
├── modules/
│   ├── Auth/
│   │   └── Controllers/
│   │       └── AuthController.php
│   └── Sales/
│       ├── Controllers/
│       │   └── OrderController.php
│       ├── Services/
│       │   └── OrderService.php
│       ├── Repositories/
│       │   └── OrderRepository.php
│       └── Models/
│           └── Order.php
│
├── routes/
│   ├── api.php              (bootstrap + include route files)
│   ├── controllers.php      (controller requires until full PSR-4)
│   └── api/                 (per-module route files)
│       ├── 001_Auth_Routes.php
│       ├── 004_Sales_Routes.php
│       └── ...

├── database/
│   ├── schema.sql
│   ├── current_data.sql
│   └── migration_*.sql

├── DOCUMENTATION/
│   ├── API_DOCUMENTATION.md
│   ├── CODING_STANDARD_ID.md
│   ├── TESTING_GUIDE.md
│   └── DEPLOYMENT.md

├── tests/
│   ├── unit/
│   └── integration/

├── logs/
│   └── app.log

├── .env
├── .env.example
├── bootstrap.php
├── composer.json
├── composer.lock
├── phpunit.xml
├── Dockerfile
├── docker-compose.yml
└── openapi.json
```

## Setup

1. **Database Setup (migration-based):**
   ```bash
   php run_php_migrations.php
   ```
   This runs all PHP migrations in `migrations/` and tracks them in the `migrations` table.

2. **Seed initial data:**
   ```bash
   mysql -u ebp_app -p ebp_restaurant_db < ../DATABASE/SEED_DATA.sql
   ```

3. **Configure environment variables:**
   - Copy `.env.example` to `.env`
   - Update database credentials and **change default JWT secret / DB password before production**
   - Key variables: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `JWT_SECRET`

4. **Autoloading:**
   - Composer PSR-4 is configured in `composer.json`
   - Regenerate autoload files with the bundled Composer phar:
     ```bash
     php composer.phar dump-autoload --no-dev
     # or, via Composer script
     php composer.phar run dump-autoload
     ```

5. Configure web server to point to `public/` directory

6. **Start dev server (optional):**
   ```bash
   php -S localhost:8080 -t public
   ```

## Database

The database is synced with the project in the `database/` directory:

- **current_data.sql** - Latest database export from phpMyAdmin (schema + data)
- **schema.sql** - Database schema structure only
- **migration_phase*.sql** - Migration files for development history

**Export current database:**
```bash
mysqldump -u ebp_app -p ebp_restaurant_db > database/current_data.sql
```

**Restore database:**
```bash
mysql -u ebp_app -p ebp_restaurant_db < database/current_data.sql
```

See `database/README.md` for detailed database documentation.

## API Endpoints

### Login

**POST** `/api/v1/auth/login`

**Request Body:**
```json
{
  "username": "admin",
  "password": "admin123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "username": "admin",
      "role": "manager"
    }
  }
}
```

### Create Order

**POST** `/api/v1/orders`

**Headers:**
```
Authorization: Bearer {access_token}
```

**Request Body:**
```json
{
  "order_type": "TAKE_AWAY",
  "items": [
    {
      "product_id": 1,
      "qty": 2,
      "price": 30000,
      "notes": "Test order"
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Order berhasil",
  "data": {
    "order_id": 105,
    "total": 60000
  }
}
```

## Architecture Refactors

- **PSR-4 Autoloading** - `App\Core\` and `App\Modules\` namespaces via Composer
- **Lazy Controllers** - Controllers wrapped in `LazyController` so only the requested route instantiates a controller
- **Split Routes** - `routes/api.php` delegates to per-module route files in `routes/api/`
- **Centralized DB** - `db()` helper and `Database` singleton replace hard-coded PDO blocks
- **Environment Config** - Credentials live in `.env` / `bootstrap.php` defaults, not in module files

## Enterprise Features Implemented

✅ **JWT Authentication** - Token-based authentication with expiration
✅ **RBAC Permission Check** - Role-based access control
✅ **Tenant Isolation** - Multi-tenant data separation
✅ **Database Transaction** - ACID compliance with rollback on error
✅ **Stock Engine** - Automatic inventory deduction from recipe
✅ **Kitchen Queue** - Kitchen order creation
✅ **Accounting Journal** - Automatic journal entry generation
✅ **Audit Trail** - Complete activity logging

## Order Transaction Flow

```
Request
  ↓
JWT Authentication
  ↓
Permission Check (ORDER_CREATE)
  ↓
Validation
  ↓
BEGIN TRANSACTION
  ↓
Create Order
  ↓
Create Order Details
  ↓
Stock Engine (Deduct Inventory)
  ↓
Kitchen Engine (Create Kitchen Order)
  ↓
Accounting Engine (Create Journal)
  ↓
Audit Trail (Log Activity)
  ↓
COMMIT TRANSACTION
  ↓
Response
```

## Architecture Layers

1. **Controller** - Handles HTTP requests, middleware execution
2. **Service** - Business logic, transaction management
3. **Repository** - Database access layer
4. **Model** - Data representation
5. **Middleware** - Authentication, authorization, tenant isolation
6. **Engines** - Business engines (Stock, Kitchen, Accounting)
7. **Audit** - Activity logging

## Testing

Run the PHPUnit smoke tests (public endpoints + login) against the dev server:

```bash
php -S localhost:8080 -t public
C:\xampp\php\php.exe phpunit-9.6.phar --testsuite "Smoke Tests"
# or, via Composer script
php composer.phar test
```

Or lint all PHP route files:

```powershell
Get-ChildItem -Path routes\api -Filter *.php | ForEach-Object { & C:\xampp\php\php.exe -l $_.FullName }
```

## Security Features

- JWT token authentication
- Password hashing (bcrypt)
- Permission-based access control
- Tenant data isolation
- SQL injection prevention (PDO prepared statements)
- CORS headers configuration

> **Important:** Change `JWT_SECRET` and database credentials in `.env` before deploying to production. The default values in `bootstrap.php` are only for local development.

---

## Cleanup & Maintenance

A deep audit of the repository is documented in `DOCUMENTATION/PROJECT_AUDIT_CLEANUP_AND_TESTING_PLAN.md`.

**Quick cleanup targets:**
- Generated artifacts: `BACKEND/test-results/`, `BACKEND/playwright-report/`, `BACKEND/screenshots/`, `BACKEND/logs/`, `BACKEND/.phpunit.result.cache`
- Obsolete manual harnesses under `BACKEND/tests/` (consumer-*.php, test-*.php, run-*.sh, test-role-based-ui.html, simulation reports)
- `BACKEND/public/.htaccess.backup`

See `.devin/workflows/project-cleanup.md` for the exact PowerShell commands.

## Playwright Headed-Browser Testing

Canonical testing knowledge for the `playwright-headed-browser` agent and AI-driven E2E runs is in:

- `.devin/knowledge/playwright-headed-browser.md`
- `DOCUMENTATION/PROJECT_AUDIT_CLEANUP_AND_TESTING_PLAN.md` (section 5)

Key commands:

```powershell
# Start dev server
cd BACKEND
C:\xampp\php\php.exe -S localhost:8000 -t public

# PHPUnit smoke tests
C:\xampp\php\php.exe vendor\phpunit\phpunit\phpunit --testsuite "Smoke Tests"

# Playwright headed UI tests
npx playwright test --project chromium --headed
```

## Devin Configuration

Project-specific Devin workflows live in `.devin/workflows/`:

- `ai-development-cycle.md` — main AI-driven development cycle.
- `project-cleanup.md` — repository cleanup procedure.
- `comprehensive-testing.md` — Playwright/PHPUnit testing procedure.
