# RESTAURANT_ERP Database Migration Runner - PowerShell Script
# This script runs the PHP migration runner using XAMPP's PHP

$phpPath = "C:\xampp\php\php.exe"
$scriptPath = "run_migrations.php"

if (Test-Path $phpPath) {
    & $phpPath $scriptPath
} else {
    Write-Host "Error: PHP not found at $phpPath" -ForegroundColor Red
    Write-Host "Please install XAMPP or update the PHP path in this script." -ForegroundColor Yellow
}

Read-Host "Press Enter to exit"
