# HEMIS campus API smoke — requires curl.exe (Windows 10+).
# Usage:
#   .\tools\hemis_api_smoke.ps1 -BaseUrl "http://127.0.0.1/A%20Final%20College%20System/index.php" -BearerToken "<JWT or opaque tokens.token>"
# Set API_CORS_ALLOW_ORIGIN on the server if browser clients need a fixed origin; curl is unaffected.
param(
    [Parameter(Mandatory = $true)][string]$BaseUrl,
    [Parameter(Mandatory = $true)][string]$BearerToken
)

$ErrorActionPreference = "Stop"
$auth = "Authorization: Bearer $BearerToken"
$h = @($auth, "Accept: application/json")

function Invoke-Smoke {
    param([string]$Method, [string]$Path)
    $url = "$BaseUrl/$Path"
    Write-Host "`n=== $Method $Path ===" -ForegroundColor Cyan
    $out = curl.exe -sS -w "`nHTTP_CODE:%{http_code}" -X $Method -H $auth -H "Accept: application/json" $url
    Write-Host $out
}

Invoke-Smoke "GET" "api/ProgramMgmt/GetCollegePrograms"
Invoke-Smoke "GET" "api/Buildings"
Invoke-Smoke "GET" "api/Labs"
Invoke-Smoke "GET" "api/Library"
Invoke-Smoke "GET" "api/Student/GetAllStudent"

Write-Host "`nDone. Expect 401 if token invalid; 200/404 depending on data." -ForegroundColor Green
