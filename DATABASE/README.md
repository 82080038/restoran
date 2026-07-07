# RESTAURANT_ERP Database Schema

This directory contains the complete database schema for the RESTAURANT_ERP system according to MEGAPLAN.md.

## Structure

### Schema Files
- `EBP_RESTAURANT_CAFE_MYSQL_SCHEMA.sql` - Complete database schema (main schema file)
- `EBP_DESAIN_DATABASE_RESTAURANT_CAFE.md` - Database design documentation
- `EBP_ERD_RESTAURANT_CAFE.md` - Entity Relationship Diagram documentation

### Seed Data
- `SEED_DATA.sql` - Initial seed data for development

## Migration Runner

Use the automated migration runner to set up the database:

```bash
cd BACKEND
php run_migrations.php
```

The migration runner will:
- Automatically connect to MySQL (supports XAMPP, MAMP, Docker)
- Create the database if it doesn't exist
- Import the complete schema from `EBP_RESTAURANT_CAFE_MYSQL_SCHEMA.sql`
- Track schema version in `schema_migrations` table
- Provide detailed output with success/failure status

## Manual Setup

### 1. Create Database
```sql
CREATE DATABASE IF NOT EXISTS ebp_restaurant_erp 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Import Schema
```bash
mysql -u root ebp_restaurant_erp < DATABASE/EBP_RESTAURANT_CAFE_MYSQL_SCHEMA.sql
```

### 3. Import Seed Data (Optional)
```bash
mysql -u root ebp_restaurant_erp < DATABASE/SEED_DATA.sql
```

## Database Configuration

Default configuration (can be changed in `BACKEND/run_migrations.php`):
- **Host**: localhost
- **Port**: 3306
- **Database**: ebp_restaurant_erp
- **User**: root (development)
- **Password**: (empty for development)

## Migration Tracking

The system tracks schema version in the `schema_migrations` table:
- `migration_file` - Name of the schema file
- `executed_at` - Timestamp when schema was imported
- `checksum` - MD5 checksum of the schema file

## Module Coverage

The database covers all 19 modules from MEGAPLAN.md:

1. ✅ Foundation & Trust (Reconciliation, Integration, Offline, Compliance, Security, i18n)
2. ✅ Core Operations (POS, Inventory, Staff, Menu)
3. ✅ Customer Experience (Reservations, Loyalty, Feedback, Online Ordering)
4. ✅ Analytics & Intelligence (BI Dashboard, Sales, Customer, Performance)
5. ✅ Supply Chain & Procurement (Suppliers, Purchase Orders, Analytics)
6. ✅ Sustainability & Future-Ready (Sustainability, IoT, Innovation)
7. ✅ Extended Capabilities (Marketing, International, Franchise, Ghost Kitchen, Emerging Tech, Segments, Integration Hub)
8. ✅ Consumer-Facing Application (Consumer app features)
9. ✅ Recipe & Ingredient Sourcing (Sourcing classification, Production recipes, Halal compliance)
10. ✅ Business Scope & Flexibility (Tenant configuration, Modular features, Business types)
11. ✅ Risk Assessment & Mitigation (Redundancy, Security, Risk management)
12. ✅ Launch Strategy & Growth (Beta program, Geographic expansion, Growth acceleration)
13. ✅ Advertising & Monetization (Advertising, Supplier ads, Data monetization)
14. ✅ AI Implementation (Infrastructure, Predictive analytics, Decision support)
15. ✅ Spin-off Applications (Supplier marketplace, Food discovery, Staff marketplace)
16. ✅ Accounting & Financial Management (Core accounting)
17. ✅ Role-Based Navigation & Permissions (RBAC)
18. ✅ Platform Owner & Multi-Tenant Management (Platform dashboard)
19. ✅ Image Upload & Media Management (Media library)

## Total Tables

The complete database schema includes **78 tables** across all modules.

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
