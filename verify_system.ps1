# System Verification Script
# Checks if the College System is properly set up and can run

Write-Host "College System - Verification Script" -ForegroundColor Green
Write-Host "====================================" -ForegroundColor Green
Write-Host ""

$errors = @()
$warnings = @()

# Check 1: Verify critical directories exist
Write-Host "Checking directory structure..." -ForegroundColor Cyan
$criticalDirs = @(
    "application",
    "application\config",
    "application\controllers",
    "application\models",
    "application\views",
    "system",
    "backend"
)

foreach ($dir in $criticalDirs) {
    if (Test-Path $dir) {
        Write-Host "  [OK] $dir" -ForegroundColor Green
    }
    else {
        Write-Host "  [ERROR] $dir - Missing!" -ForegroundColor Red
        $errors += "Missing directory: $dir"
    }
}

Write-Host ""

# Check 2: Verify critical files exist
Write-Host "Checking critical files..." -ForegroundColor Cyan
$criticalFiles = @(
    "index.php",
    "application\config\config.php",
    "application\config\database.php",
    "composer.json"
)

foreach ($file in $criticalFiles) {
    if (Test-Path $file) {
        Write-Host "  [OK] $file" -ForegroundColor Green
    }
    else {
        Write-Host "  [ERROR] $file - Missing!" -ForegroundColor Red
        $errors += "Missing file: $file"
    }
}

Write-Host ""

# Check 3: Check for long path issues in Google API services
Write-Host "Checking Google API services (common long path issue)..." -ForegroundColor Cyan
$googleApiPath = "application\third_party\omnipay\vendor\google\apiclient-services\src\PaymentsResellerSubscription"
if (Test-Path $googleApiPath) {
    Write-Host "  [OK] Google API services directory exists" -ForegroundColor Green
    
    # Check for the problematic file
    $problemFile = Join-Path $googleApiPath "GoogleCloudPaymentsResellerSubscriptionV1PromotionIntroductoryPricingDetails.php"
    if (Test-Path $problemFile) {
        Write-Host "  [OK] Problematic file exists: $($problemFile.Length) chars path length" -ForegroundColor Green
    }
    else {
        Write-Host "  [WARNING] Some files may be missing in Google API services" -ForegroundColor Yellow
        $warnings += "Some Google API service files may be missing"
    }
}
else {
    Write-Host "  [WARNING] Google API services directory not found (may not be needed)" -ForegroundColor Yellow
    $warnings += "Google API services directory not found"
}

Write-Host ""

# Check 4: Check PHP configuration
Write-Host "Checking PHP availability..." -ForegroundColor Cyan
try {
    $phpVersion = php -v 2>&1 | Select-Object -First 1
    if ($phpVersion -match "PHP") {
        Write-Host "  [OK] PHP is available: $phpVersion" -ForegroundColor Green
    }
    else {
        Write-Host "  [WARNING] PHP may not be properly configured" -ForegroundColor Yellow
        $warnings += "PHP configuration issue"
    }
}
catch {
    Write-Host "  [WARNING] Could not verify PHP installation" -ForegroundColor Yellow
    $warnings += "Could not verify PHP"
}

Write-Host ""

# Check 5: Check for path length issues
Write-Host "Checking for potential path length issues..." -ForegroundColor Cyan
$basePath = (Get-Location).Path
$maxPathLength = 260
$currentPathLength = $basePath.Length

if ($currentPathLength -gt ($maxPathLength - 100)) {
    Write-Host "  [WARNING] Base path is long ($currentPathLength chars). Deep directories may exceed Windows limit." -ForegroundColor Yellow
    $warnings += "Base path is long - may cause issues with deep directories"
}
else {
    Write-Host "  [OK] Base path length is acceptable ($currentPathLength chars)" -ForegroundColor Green
}

Write-Host ""

# Summary
Write-Host "====================================" -ForegroundColor Green
Write-Host "Verification Summary" -ForegroundColor Green
Write-Host "====================================" -ForegroundColor Green

if ($errors.Count -eq 0 -and $warnings.Count -eq 0) {
    Write-Host "System appears to be properly set up!" -ForegroundColor Green
    exit 0
}
else {
    if ($errors.Count -gt 0) {
        Write-Host "Errors found: $($errors.Count)" -ForegroundColor Red
        $errors | ForEach-Object { Write-Host "  - $_" -ForegroundColor Red }
    }
    
    if ($warnings.Count -gt 0) {
        Write-Host "Warnings: $($warnings.Count)" -ForegroundColor Yellow
        $warnings | ForEach-Object { Write-Host "  - $_" -ForegroundColor Yellow }
    }
    
    if ($errors.Count -gt 0) {
        exit 1
    }
    else {
        exit 0
    }
}

