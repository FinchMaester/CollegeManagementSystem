# Comprehensive HEMIS campus API audit runner (local).
# Requires: curl.exe, local PHP server running (e.g. `php -S localhost:8000 router.php`)
#
# Usage:
#   .\tools\hemis_api_audit.ps1 -BaseUrl "http://localhost:8000" -BearerToken "test123456"
#
# Notes:
# - This tests *campus* endpoints implemented under application/controllers/api/*
# - In HEMIS_MOCK=true mode, outbound sync to UGC is disabled (no remote HTTP). The endpoints should still return valid mock-mode sync blocks.

param(
  [Parameter(Mandatory = $true)][string]$BaseUrl,
  [Parameter(Mandatory = $true)][string]$BearerToken
)

$ErrorActionPreference = "Stop"

function Invoke-CampusJson {
  param(
    [Parameter(Mandatory=$true)][string]$Method,
    [Parameter(Mandatory=$true)][string]$EndpointPath,
    [string]$JsonBody = $null
  )
  $url = ($BaseUrl.TrimEnd('/')) + '/' + $EndpointPath.TrimStart('/')
  $curlArgs = @("-sS", "-w", "`nHTTP_CODE:%{http_code}`n", "-X", $Method, "-H", "Authorization: Bearer $BearerToken", "-H", "Accept: application/json")
  if ($JsonBody -ne $null) {
    $curlArgs += @("-H", "Content-Type: application/json", "--data-binary", $JsonBody)
  }
  $curlArgs += @($url)
  $out = & curl.exe @curlArgs
  $http = 0
  if ($out -match "HTTP_CODE:(\d{3})") { $http = [int]$Matches[1] }
  [pscustomobject]@{ method=$Method; path=$EndpointPath; http_code=$http; raw=$out }
}

function Invoke-CampusMultipart {
  param(
    [Parameter(Mandatory=$true)][string]$Method,
    [Parameter(Mandatory=$true)][string]$EndpointPath,
    [Parameter(Mandatory=$true)][hashtable]$Fields
  )
  $url = ($BaseUrl.TrimEnd('/')) + '/' + $EndpointPath.TrimStart('/')
  $curlArgs = @("-sS", "-w", "`nHTTP_CODE:%{http_code}`n", "-X", $Method, "-H", "Authorization: Bearer $BearerToken", "-H", "Accept: application/json")
  foreach ($k in $Fields.Keys) {
    $v = $Fields[$k]
    $curlArgs += @("-F", "$k=$v")
  }
  $curlArgs += @($url)
  $out = & curl.exe @curlArgs
  $http = 0
  if ($out -match "HTTP_CODE:(\d{3})") { $http = [int]$Matches[1] }
  [pscustomobject]@{ method=$Method; path=$EndpointPath; http_code=$http; raw=$out }
}

function Try-Json {
  param([string]$Raw)
  $body = ($Raw -split "HTTP_CODE:\d{3}")[0]
  $body = $body.Trim()
  if ($body -eq "") { return $null }
  try { return ($body | ConvertFrom-Json -ErrorAction Stop) } catch { return $null }
}

$results = @()

Write-Host "== Reference GETs ==" -ForegroundColor Cyan
$results += Invoke-CampusJson -Method "GET" -EndpointPath "api/Address/getall"
$results += Invoke-CampusJson -Method "GET" -EndpointPath "api/Batch"
$results += Invoke-CampusJson -Method "GET" -EndpointPath "api/Fiscalyear"
$results += Invoke-CampusJson -Method "GET" -EndpointPath "api/EmployeePositions"
$results += Invoke-CampusJson -Method "GET" -EndpointPath "api/ProgramMgmt/GetCollegePrograms"

Write-Host "== Infrastructure modules ==" -ForegroundColor Cyan
# Buildings
$b = Invoke-CampusJson -Method "POST" -EndpointPath "api/Buildings" -JsonBody (@{
  houseName="QA Building $(Get-Date -Format yyyyMMddHHmmss)"
  areaCoveredByBuilding=123.4
  noOfClassrooms=10
  areaCoveredByAllRooms=456.7
  ownershipOfBuilding="Owned"
  hasInternetConnection="Yes"
  remarks="QA audit"
} | ConvertTo-Json -Compress)
$results += $b
$bJson = Try-Json $b.raw
$buildingId = if ($bJson -and $bJson.id) { [int]$bJson.id } else { 0 }
$buildingIdForChild = if ($buildingId -gt 0) { $buildingId } else { 1 }
if ($buildingId -gt 0) {
  $results += Invoke-CampusJson -Method "GET" -EndpointPath ("api/Buildings/view/{0}" -f $buildingId)
  $results += Invoke-CampusJson -Method "PUT" -EndpointPath ("api/Buildings/{0}" -f $buildingId) -JsonBody (@{ remarks="QA audit updated" } | ConvertTo-Json -Compress)
}

# Library (requires buildingId, campusId, areaOfReadingHall, noOfBooks)
$lib = Invoke-CampusJson -Method "POST" -EndpointPath "api/Library" -JsonBody (@{
  libraryName="QA Library $(Get-Date -Format yyyyMMddHHmmss)"
  buildingId=$buildingIdForChild
  campusId=1
  noOfLibraryRooms=2
  totalAreaOfLibraryRoom=250.0
  noOfReadingHalls=1
  areaOfReadingHall=150.5
  noOfBooks=5000
  noOfDailyJournals=1
  noOfWeeklyJournals=1
  noOfMonthlyJournals=1
  noOfAnnualJournals=1
  hasELibraryFacility=true
  remarks="QA audit"
} | ConvertTo-Json -Compress)
$results += $lib
$libJson = Try-Json $lib.raw
$libraryId = if ($libJson -and $libJson.id) { [int]$libJson.id } else { 0 }
if ($libraryId -gt 0) {
  $results += Invoke-CampusJson -Method "GET" -EndpointPath ("api/Library/view/{0}" -f $libraryId)
  $results += Invoke-CampusJson -Method "PUT" -EndpointPath ("api/Library/{0}" -f $libraryId) -JsonBody (@{ remarks="QA audit updated" } | ConvertTo-Json -Compress)
}

# FEV
$fev = Invoke-CampusJson -Method "POST" -EndpointPath "api/FurnitureEquipmentVehicle" -JsonBody (@{
  itemType="Furniture"
  itemName="QA Desk $(Get-Date -Format yyyyMMddHHmmss)"
  noOfQty=3
  itemDescription="QA audit"
  remarks="QA audit"
} | ConvertTo-Json -Compress)
$results += $fev
$fevJson = Try-Json $fev.raw
$fevId = if ($fevJson -and $fevJson.id) { [int]$fevJson.id } else { 0 }
if ($fevId -gt 0) {
  $results += Invoke-CampusJson -Method "GET" -EndpointPath ("api/FurnitureEquipmentVehicle/view/{0}" -f $fevId)
  $results += Invoke-CampusJson -Method "PUT" -EndpointPath ("api/FurnitureEquipmentVehicle/{0}" -f $fevId) -JsonBody (@{ remarks="QA audit updated" } | ConvertTo-Json -Compress)
}

# Hostels
$host = Invoke-CampusJson -Method "POST" -EndpointPath "api/Hostels" -JsonBody (@{
  hostelName="QA Hostel $(Get-Date -Format yyyyMMddHHmmss)"
  hostelType="Boys"
  noOfRooms=5
  totalAreaOfHostel=300.0
  hasInternetConnection=true
  remarks="QA audit"
} | ConvertTo-Json -Compress)
$results += $host

# Lands
$land = Invoke-CampusJson -Method "POST" -EndpointPath "api/Lands" -JsonBody (@{
  landName="QA Land $(Get-Date -Format yyyyMMddHHmmss)"
  areaOfLand=1000.0
  ownershipOfLand="Owned"
  remarks="QA audit"
} | ConvertTo-Json -Compress)
$results += $land

# Labs
$lab = Invoke-CampusJson -Method "POST" -EndpointPath "api/Labs" -JsonBody (@{
  labName="QA Lab $(Get-Date -Format yyyyMMddHHmmss)"
  buildingId=$buildingIdForChild
  campusId=1
  noOfRooms=1
  totalAreaOfLabRoom=120.0
  hasInternetConnection=true
  remarks="QA audit"
} | ConvertTo-Json -Compress)
$results += $lab

Write-Host "== Management modules ==" -ForegroundColor Cyan
$dept = Invoke-CampusJson -Method "POST" -EndpointPath "api/Management/add_department" -JsonBody (@{ departmentName="QA Dept $(Get-Date -Format yyyyMMddHHmmss)"; remarks="QA" } | ConvertTo-Json -Compress)
$results += $dept
$sec = Invoke-CampusJson -Method "POST" -EndpointPath "api/Management/add_section" -JsonBody (@{ sectionName="QA Section $(Get-Date -Format yyyyMMddHHmmss)"; remarks="QA" } | ConvertTo-Json -Compress)
$results += $sec

Write-Host "== Employee ==" -ForegroundColor Cyan
$emp = Invoke-CampusJson -Method "POST" -EndpointPath "api/Employee" -JsonBody (@{
  firstName="QA"
  middleName=""
  lastName="Employee"
  phoneNumber="9800000000"
  gender="male"
  email="qa.employee.$(Get-Date -Format yyyyMMddHHmmss)@example.test"
  ethnicity="Brahmin"
  campusId=1
  postName="Lecturer"
  joiningDate="2082-01-01"
  spouseName="QA Spouse"
  dependentInfo="QA dependent info"
} | ConvertTo-Json -Compress)
$results += $emp

Write-Host "== Student (multipart create + lifecycle) ==" -ForegroundColor Cyan
# Student/Create multipart (critical path)
$stuCreate = Invoke-CampusMultipart -Method "POST" -EndpointPath "api/Student/Create" -Fields @{
  firstName="QA"
  middleName=""
  lastName="Student"
  gender="male"
  admissionYearId="2082"
  campusId="1"
  nepaliName="क्यूए विद्यार्थी"
  doBBS="2067-02-15"
  doBAD="2010-05-29"
  ethnicity="Brahmin"
  nationality="Nepalese"
  disabilityStatus="No"
  pProvince="Bagmati"
  pDistrict="Kathmandu"
  pLocalLevel="Kathmandu"
  pWardNo="1"
  fatherName="QA Father"
  guardianName="QA Guardian"
  programId="5"
  programName="Bachelors of Business Studies"
  levelName="Bachelors - 1st Year"
  facultyName="Faculty of Management"
  complitionYear="2086"
  dateOfEnrollment="2082-01-01"
  fiscalYearId="1"
  year="1"
  programType="Semester"
  religion="Hindu"
}
$results += $stuCreate
$stuJson = Try-Json $stuCreate.raw
$studentId = if ($stuJson -and $stuJson.data -and $stuJson.data.id) { [int]$stuJson.data.id } elseif ($stuJson -and $stuJson.studentId) { [int]$stuJson.studentId } else { 0 }

# Student list + view existing known IDs
$results += Invoke-CampusJson -Method "GET" -EndpointPath "api/Student/GetAllStudent"
if ($studentId -gt 0) {
  $results += Invoke-CampusJson -Method "GET" -EndpointPath ("api/Student/view/{0}" -f $studentId)
}

# StudentUpgrade expects JSON array
if ($studentId -gt 0) {
  $upgrade = Invoke-CampusJson -Method "POST" -EndpointPath "api/StudentUpgrade" -JsonBody ( @(
    @{ studentId=$studentId; programId=5; year="2"; semester="1"; batchId=1 }
  ) | ConvertTo-Json -Compress)
  $results += $upgrade
}

# DropOut POST
if ($studentId -gt 0) {
  $drop = Invoke-CampusJson -Method "POST" -EndpointPath "api/DropOut" -JsonBody (@{
    studentId=$studentId
    reason="QA audit dropout reason (text)"
    dateOfEntry="2082-01-15"
    remarks="QA audit"
  } | ConvertTo-Json -Compress)
  $results += $drop
}

Write-Host ""
Write-Host "== Results ==" -ForegroundColor Cyan
$summary = $results | Select-Object method,path,http_code
$summary | Format-Table -AutoSize

$fail = $results | Where-Object { $_.http_code -lt 200 -or $_.http_code -ge 300 }
if ($fail.Count -gt 0) {
  Write-Host "`nSome endpoints failed (non-2xx). Review output above." -ForegroundColor Yellow
  exit 2
}

Write-Host "`nAll audited endpoints returned 2xx." -ForegroundColor Green

