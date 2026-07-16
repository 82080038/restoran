---
description: Clean generated artifacts and obsolete test harnesses from RESTAURANT_ERP
---

# Project Cleanup Workflow

## Purpose

Remove generated artifacts, stale log files, and obsolete manual test harnesses from the RESTAURANT_ERP working tree.

## Prerequisites

- PowerShell 5.1+
- Repository at `c:\xampp\htdocs\restoran`
- Git available

## Steps

1. **Review the cleanup target list**
   - Read `DOCUMENTATION/PROJECT_AUDIT_CLEANUP_AND_TESTING_PLAN.md` section 3.

2. **Remove ignored/generated artifacts (no git rm needed)**

```powershell
Remove-Item -Recurse -Force -ErrorAction SilentlyContinue BACKEND/test-results, BACKEND/playwright-report, BACKEND/screenshots
Remove-Item -Force -ErrorAction SilentlyContinue BACKEND/.phpunit.result.cache, BACKEND/public/.htaccess.backup, BACKEND/package-lock.json, BACKEND/logs/app.log
```

3. **Remove optional tool binaries (only if Composer/PHPUnit are installed globally)**

```powershell
Remove-Item -Force -ErrorAction SilentlyContinue BACKEND/composer.phar, BACKEND/phpunit-9.6.phar
```

4. **Remove obsolete tracked files (git rm)**

```powershell
$obsolete = @(
  'BACKEND/tests/consumer-direct-sim.php','BACKEND/tests/consumer-direct-test.php','BACKEND/tests/consumer-full-test.php','BACKEND/tests/consumer-manual-test.php','BACKEND/tests/consumer-sim.php','BACKEND/tests/consumer-simple-test.php','BACKEND/tests/consumer-step-test.php','BACKEND/tests/consumer-test.php',
  'BACKEND/tests/test-featured.php','BACKEND/tests/test-login.php',
  'BACKEND/tests/run-consumer-tests.sh','BACKEND/tests/test-phase1-modules.sh','BACKEND/tests/test-role-api.sh',
  'BACKEND/tests/test-role-based-ui.html','BACKEND/tests/test-role-ui-manual.md',
  'BACKEND/tests/PRODUCTION_SIMULATION_REPORT_2026-07-05.md','BACKEND/tests/REAL_ACTIVITY_SIMULATION_REPORT_2026-07-05.md','BACKEND/tests/SIMULATION_REPORT_2026-07-05.md','BACKEND/tests/TEST_REPORT_2026-07-05.md',
  'BACKEND/tests/production-simulation.php'
)
foreach ($f in $obsolete) { git rm --cached $f -q }
git rm --cached -r BACKEND/tests/production-simulation-reports
Remove-Item -Recurse -Force -ErrorAction SilentlyContinue BACKEND/tests/production-simulation-reports
```

5. **Verify**

```powershell
git status --short
Get-ChildItem BACKEND -Directory | select Name, @{n='SizeMB';e={[math]::Round((Get-ChildItem $_.FullName -Recurse -File -ErrorAction SilentlyContinue | Measure-Object Length -Sum).Sum/1MB,2)}}
```

## Safety

- Do **not** delete `BACKEND/vendor/` or `BACKEND/node_modules/` unless you are ready to regenerate them.
- Keep `BACKEND/.env` and `BACKEND/.env.example`.
- Keep all files under `BACKEND/core/`, `BACKEND/modules/`, `BACKEND/routes/`, `BACKEND/migrations/`.
