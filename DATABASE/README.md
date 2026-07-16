# RESTAURANT_ERP Database Schema

This directory contains the complete database schema for the RESTAURANT_ERP system according to MEGAPLAN.md.

## Structure

### Schema Files
- `EBP_RESTAURANT_CAFE_MYSQL_SCHEMA.sql` - Legacy complete schema (kept for reference)
- `EBP_RESTAURANT_CAFE_COMPLETE_SCHEMA.sql` - Full schema export (reference)
- `EBP_DESAIN_DATABASE_RESTAURANT_CAFE.md` - Database design documentation
- `EBP_ERD_RESTAURANT_CAFE.md` - Entity Relationship Diagram documentation

### Seed Data
- `SEED_DATA.sql` - Initial seed data for development (aligned with the migration schema)

### Migration Files
- `BACKEND/migrations/*.php` - Incremental PHP migrations (the active schema source)
- `BACKEND/migrations/MigrationRunner.php` - Migration tracker/executor

## Migration Runner (Recommended)

Use the PHP migration runner to set up the database:

```bash
cd BACKEND
C:\xampp\php\php.exe run_php_migrations.php migrate
```

Backend entry point: `BACKEND/public/index.php`.
After migrations and seeding, verify with:

```powershell
Invoke-RestMethod -Uri "http://localhost:8080/api/v1/public/menu/categories" -Method GET
```

Check migration status:

```bash
C:\xampp\php\php.exe run_php_migrations.php status
```

The runner will:
- Automatically connect to MySQL using `BACKEND/.env` or built-in defaults
- Create the database if it doesn't exist
- Execute pending PHP migrations in order
- Track executed migrations in the `migrations` table

## Manual Setup

### 1. Create Database
```sql
CREATE DATABASE IF NOT EXISTS ebp_restaurant_db
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Create Application User
```sql
CREATE USER IF NOT EXISTS 'ebp_app'@'localhost' IDENTIFIED BY 'ebp_secure_password_2026';
GRANT ALL PRIVILEGES ON ebp_restaurant_db.* TO 'ebp_app'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Import Seed Data (Optional)
```bash
mysql -u ebp_app -pebp_secure_password_2026 ebp_restaurant_db < DATABASE/SEED_DATA.sql
```

## Database Configuration

Default configuration (can be changed in `BACKEND/.env`):
- **Host**: localhost
- **Port**: 3306
- **Database**: ebp_restaurant_db
- **User**: ebp_app
- **Password**: ebp_secure_password_2026

## Migration Tracking

The PHP migration runner tracks applied migrations in the `migrations` table:
- `migration` - Name of the migration file
- `executed_at` - Timestamp when migration was applied

## Module Coverage

The database covers all 19 modules from MEGAPLAN.md:

1. ✅ Foundation & Trust
2. ✅ Core Operations
3. ✅ Customer Experience
4. ✅ Analytics & Intelligence
5. ✅ Supply Chain & Procurement
6. ✅ Sustainability & Future-Ready
7. ✅ Extended Capabilities
8. ✅ Consumer-Facing Application
9. ✅ Recipe & Ingredient Sourcing
10. ✅ Business Scope & Flexibility
11. ✅ Risk Assessment & Mitigation
12. ✅ Launch Strategy & Growth
13. ✅ Advertising & Monetization
14. ✅ AI Implementation
15. ✅ Spin-off Applications
16. ✅ Accounting & Financial Management
17. ✅ Role-Based Navigation & Permissions
18. ✅ Platform Owner & Multi-Tenant Management
19. ✅ Image Upload & Media Management

## Total Tables

The migrated schema currently includes **90+ tables** across all modules.

## Troubleshooting

### Schema Import Failures
If schema import fails:
1. Check the error message for specific SQL issues
2. Verify database connection in `run_migrations.php`
3. Ensure MySQL server is running
4. Check for table conflicts or existing data

### Reset Database
To completely reset the database:
```sql
DROP DATABASE IF EXISTS ebp_restaurant_erp;
CREATE DATABASE ebp_restaurant_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then re-run the migration runner.

## Notes

- All tables use UTF-8 (utf8mb4) encoding
- Foreign key constraints are included where appropriate
- Indexes are added for performance optimization
- The schema supports multi-tenant architecture via tenant_id columns
- All tables include created_at and updated_at timestamps for audit trail
- The schema uses `CREATE TABLE IF NOT EXISTS` to handle existing tables gracefully
