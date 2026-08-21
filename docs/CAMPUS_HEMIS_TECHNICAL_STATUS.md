# Campus HEMIS Technical Status & API Integration Documentation

**Institution:** Kanchan Vidhya Mandir Samudayik College (KVM)  
**Production URL:** https://hemis.kvmcollege.edu.np  
**UGC Central HEMIS API:** https://hemisapi.ugcnepal.edu.np  
**Campus / College ID:** 198  
**System name:** A Final College System (CodeIgniter campus MIS)  
**Document version:** 1.0  
**Date:** July 2026  

---

## Executive summary

KVM College operates a campus Management Information System (MIS) integrated with the University Grants Commission (UGC) Nepal central HEMIS API. The system stores academic and administrative data locally in MySQL, exposes UGC-aligned REST endpoints for campus clients, and synchronizes student, staff, and infrastructure records to UGC using Bearer JWT authentication. Reference data (addresses, batches, fiscal years, programs) is pulled from UGC and cached locally. Sync attempts and failures are logged for audit and troubleshooting.

---

## 1. Campus HEMIS Technical Status

### a. System Architecture

| Item | Detail |
|------|--------|
| **Pattern** | Three-tier web application |
| **Client** | Web browser (admin, staff, teachers, students, parents) |
| **Application server** | PHP 7.x+ with CodeIgniter 3 MVC |
| **Database** | MySQL / MariaDB |
| **External integration** | UGC HEMIS REST API (outbound HTTPS) |
| **Deployment** | LAMP-style hosting (production: `hemis.kvmcollege.edu.np`) |
| **Front controller** | `index.php` (CodeIgniter bootstrap) |

**Key integration components:**

- `application/libraries/Hemis_client.php` — outbound UGC HTTP client
- `application/libraries/Hemis_ugc_auth.php` — UGC login and token refresh
- `application/helpers/hemis_sync_post_from_db_helper.php` — student/staff payload builder
- `application/models/Hemis_integration_model.php` — central API settings
- `application/config/hemis.php` — default sync flags and env merge

---

### b. Presentation Layer

| Item | Detail |
|------|--------|
| **UI framework** | Bootstrap-based admin theme |
| **Client scripting** | JavaScript, jQuery |
| **Components** | DataTables, FullCalendar, CKEditor, Select2, Nepali date picker |
| **Role dashboards** | Admin, Staff, Teacher, Student, Parent, Accountant, Librarian |
| **HEMIS admin screens** | Central API (HEMIS) settings, UGC Sync, UGC Error Log, student/staff sync status |
| **Sync status badges** | Synced (green), Error (red), Not synced (muted) on student/staff lists |

**Admin paths (examples):**

- System Settings → Central API (HEMIS): `admin/hemis_integration`
- UGC manual sync: `admin/ugc_sync`
- UGC error audit: `admin/ugc_error_log`
- Student sync debug: `admin/ugc_sync/debug_student/{id}`

---

### c. Application Layer

| Item | Detail |
|------|--------|
| **Framework** | CodeIgniter 3 (MVC) |
| **Controllers** | `application/controllers/` (admin UI), `application/controllers/api/` (REST) |
| **Models** | Data access, business rules (`student_model`, `staff_model`, `ugc_*` models) |
| **Libraries** | `Hemis_client`, `Hemis_ugc_auth`, `Hemis_rest_auth`, `Rbac`, `REST_Controller` |
| **Helpers** | HEMIS field mapping, multipart wire preparation, Nepali calendar |
| **Hooks / cron** | `Cron.php` — optional automated database backup |

**Campus REST API controllers** (`application/controllers/api/`):

| Module | Controller |
|--------|------------|
| Student | `Student.php` |
| Student upgrade | `StudentUpgrade.php` |
| Drop out | `DropOut.php` |
| Graduation | `Graduation.php` |
| Employee | `Employee.php` |
| Buildings | `Buildings.php` |
| Labs | `Labs.php` |
| Library | `Library.php` |
| Hostels | `Hostels.php` |
| Lands | `Lands.php` |
| Furniture / Equipment / Vehicle | `FurnitureEquipmentVehicle.php` |
| Management (dept / section) | `Management.php` |
| Batch | `Batch.php` |
| Fiscal year | `Fiscalyear.php` |
| Address | `Address.php` |
| Program management | `ProgramMgmt.php` |
| Employee positions | `EmployeePositions.php` |

---

### d. Data Layer

| Item | Detail |
|------|--------|
| **RDBMS** | MySQL / MariaDB |
| **Core tables** | `students`, `staff`, `student_session`, programs, classes, sections, fees, exams, attendance |
| **HEMIS columns** | `hemis_student_id`, `hemis_sync_at`, `hemis_sync_error`, `hemis_employee_id` (on students/staff) |
| **UGC reference cache** | `ugc_addresses`, `ugc_batches`, `ugc_fiscal_years`, `ugc_programs`, `ugc_employee_positions` |
| **Integration config** | `hemis_integration_settings` (single-row toggles and API credentials) |
| **Audit / sync state** | `ugc_error_logs`, `ugc_sync_states` |
| **SQL migrations** | `application/migrations/`, `application/sql/hemis_*.sql` |

**Environment configuration** (`.env`, not committed):

- `HEMIS_REMOTE_BASE_URL`
- `HEMIS_REMOTE_BEARER_TOKEN`
- `HEMIS_COLLEGE_ID` (198)
- `HEMIS_MOCK` / `HEMIS_SYNC_ENABLED`
- `HEMIS_REMOTE_LOGIN_EMAIL` / `HEMIS_REMOTE_LOGIN_PASSWORD`

---

### e. API Gateway

| Item | Detail |
|------|--------|
| **Dedicated API gateway** | Not used |
| **Outbound** | `Hemis_client` calls UGC directly via cURL with `Authorization: Bearer {JWT}` |
| **Inbound** | Campus exposes UGC-shaped REST routes; authentication via `Hemis_rest_auth` |
| **Routing** | CodeIgniter `routes.php` + REST method suffixes (`create_post`, `index_get`, etc.) |

---

### f. Third Party Integration

| Integration | Purpose | Status |
|-------------|---------|--------|
| **UGC HEMIS API** | Student, staff, infrastructure sync; metadata pull | Primary — production |
| **UGC DEV API** | Pre-production testing (`hemisapidev.ugcnepal.edu.np`) | Used for compliance testing |
| **Payment gateways** | Fee collection (Omnipay: Stripe, PayPal, etc.) | Campus fees only |
| **Email (PHPMailer)** | Notifications | General campus use |
| **SMS** | Optional notifications | Where configured |

**UGC modules synced (configurable in `hemis_integration_settings`):**

- Student create / update
- Student upgrade, dropout, graduation
- Employee create / update
- Buildings, labs, library, hostels, lands, furniture/equipment/vehicle
- Management (department, section)
- Pull: batch, fiscal year, address, program management, employee positions

---

### g. Data Migration and Synchronization

| Item | Detail |
|------|--------|
| **Pattern** | Local save first → outbound sync to UGC |
| **Student create** | `POST /api/Student/Create` (multipart) when `hemis_student_id` is empty |
| **Student update** | `PATCH /api/Student/{hemis_student_id}` when ID exists |
| **Employee** | `POST /api/Employee/{universityId}` or legacy path; `PATCH /api/Employee/{id}` |
| **Payload source** | `hemis_student_db_row_to_create_post()` from DB row |
| **Reference pull** | `Hemis_client::hemis_get_*` → cached in `ugc_*` tables |
| **Bulk import** | CSV student import with UGC column checklist |
| **Sanitization** | Admin data sanitization tool (ethnicity, nationality, citizenship defaults) |
| **Failure handling** | `hemis_sync_error` on row; full request/response in `ugc_error_logs` |
| **Manual retry** | UGC Sync admin UI; per-student debug page |

**Sync flow (student):**

```
Save student locally
    → Build multipart payload from DB
    → Preflight validation (campusId, batchId, fiscalYearId, address codes)
    → POST UGC Student/Create OR PATCH Student/{id}
    → Store hemis_student_id on success; log errors on failure
```

---

### h. Disaster Recovery and Backup

| Item | Detail |
|------|--------|
| **Manual backup** | Admin → Backup (`admin/backup`) — SQL export via CodeIgniter `dbutil` |
| **Automated backup** | `Cron::autobackup` (cron key required) |
| **Operator guidance** | Backup recommended before HEMIS go-live and data sanitization |
| **Secrets** | `.env` and DB credentials — not in source control |
| **DR site** | Not defined in application; relies on hosting provider backups |
| **Recovery** | Restore MySQL dump + redeploy application files + restore `.env` |

**Status:** Partial — backup tools exist; formal DR runbook is operator/hosting responsibility.

---

## 2. API Integration Status

### a. RESTful API-based data sharing

| Item | Detail |
|------|--------|
| **Standard** | REST over HTTPS |
| **Formats** | JSON (most modules); multipart/form-data (Student, Employee create) |
| **Methods** | GET, POST, PUT, PATCH, DELETE, OPTIONS |
| **Campus base** | `https://hemis.kvmcollege.edu.np/api/...` |
| **UGC base** | `https://hemisapi.ugcnepal.edu.np/api/...` |

---

### b. System adherence to API regulation

| Item | Detail |
|------|--------|
| **UGC endpoint naming** | Mirrors official paths (`Student/Create`, `Employee`, `Address/GetAll`, etc.) |
| **Address wire format** | PascalCase: `PProvince`, `PDistrict`, `PLocalLevel`, `PWardNo`, `pCombinedCode` |
| **Dates** | BS: `doBBS` (yyyy/mm/dd); AD: `doBAD`, `dateOfEnrollment` (yyyy-mm-dd) |
| **IDs** | `campusId` 198, `programId`, `batchId`, `admissionYearId`, `fiscalYearId` from UGC cache |
| **Booleans** | Wire strings `true` / `false` for multipart |
| **Configuration** | Admin UI + `.env` overrides merged in `Hemis_integration_model` |

---

### c. Data accuracy, integrity and security

| Item | Detail |
|------|--------|
| **Preflight checks** | Required fields validated before UGC POST (reduces opaque 500s) |
| **Address alignment** | `ugc_addresses` cache used for authoritative province/district/local level labels |
| **Audit trail** | `ugc_error_logs`: endpoint, HTTP code, request preview, response body |
| **Transport** | HTTPS to UGC production API |
| **Token storage** | DB + `.env`; not exposed in UI or client-side code |
| **PII in logs** | Request previews truncated; operators access logs via admin RBAC |

---

### d. API testing on UGC test HEMIS server

| Item | Detail |
|------|--------|
| **Dev API** | `https://hemisapidev.ugcnepal.edu.np` |
| **Compliance report** | `UGC_HEMIS_DEV_Master_Sync_Compliance_Report.md` |
| **Postman** | `postman_tests/HEMIS_Automation.postman_collection.json` |
| **Environment** | `postman_tests/HEMIS_Test.postman_environment.json` |
| **Admin test login** | HEMIS Integration → Test UGC login (issues/refreshes Bearer token) |
| **Mock mode** | `HEMIS_MOCK=true` — local fake IDs without HTTP (development only) |
| **Production** | `HEMIS_MOCK=false`, `HEMIS_REMOTE_BASE_URL` → production UGC API |

**Dev test results (sample):** Login 200, Address/Batch/FiscalYear/Program GET 200, Student Create 200, Buildings 201, Employee (dev DB issues noted in report).

---

### e. HEMIS infrastructure as per UGC guidelines

| UGC module | Campus implementation |
|------------|----------------------|
| Student | Create, update, sync status |
| StudentUpgrade | Session/year promotion sync |
| DropOut | JSON sync |
| Graduation | Multipart application sync |
| Employee | Multipart create/update |
| Buildings, Labs, Library, Hostels, Lands, FEV | REST + `Hemis_client` sync |
| Management | Department and section |
| Metadata | Batch, fiscal year, address, programs, employee positions (pull) |

All modules can be toggled in **hemis_integration_settings**.

---

## 3. HEMIS Authorization and Access Control

### a. Role based access control (RBAC) for API access

| Layer | Mechanism |
|-------|-----------|
| **Admin UI** | `Rbac` library — role × privilege (`can_view`, `can_edit`, etc.) |
| **HEMIS settings** | Restricted to Super Admin (`superadmin` privilege) |
| **Campus REST API** | Bearer token required on every request (except OPTIONS) |
| **Validation** | `Hemis_rest_auth::validate_campus_bearer_array()` |
| **Token types** | Opaque DB tokens + JWT (`authorization_token` / Firebase PHP-JWT) |

---

### b. Unique API keys and authentication tokens

| Direction | Authentication |
|-----------|----------------|
| **Campus → UGC** | JWT from `POST /api/User/Login` (college admin credentials) |
| **Token storage** | `hemis_integration_settings.remote_bearer_token` + `.env` `HEMIS_REMOTE_BEARER_TOKEN` |
| **Auto refresh** | `Hemis_ugc_auth::merge_auto_login_into_config()` when JWT expires |
| **JWT claims** | `InstitutionId` = 198 (college), `CollegeAdmin` role |
| **Campus API clients** | JWT from `scripts/generate_hemis_api_jwt.php` or issued login tokens |

**Security note:** Rotate tokens if leaked; never commit `.env` to version control.

---

### c. Unauthorized API access and data breach prevention

| Control | Implementation |
|---------|----------------|
| **Missing/invalid token** | HTTP 401 JSON response |
| **Admin session** | CodeIgniter session + RBAC on admin routes |
| **Credential UI** | Password fields; superadmin-only edit |
| **Error messages** | Generic messages to end users; details in server logs |
| **HTTPS** | Production site and UGC API over TLS |

---

## 4. Security Standards

### a. Rate limiting and throttling

| Item | Detail |
|------|--------|
| **Campus inbound API** | No application-level rate limiter implemented |
| **Outbound UGC** | Configurable timeout (`remote_timeout`, default 30 seconds) |
| **Retries** | Limited retry on Student Create (e.g. strip attachments on 413/500) |
| **UGC side** | Subject to UGC/nginx limits (502/503/504 handled as transient) |

**Status:** Partial — relies on UGC and hosting provider for rate control.

---

### b. CORS (Cross-Origin Resource Sharing)

| Item | Detail |
|------|--------|
| **Config file** | `application/config/api_cors.php` |
| **Env override** | `API_CORS_ALLOW_ORIGIN` (e.g. `https://app.yourcollege.edu.np`) |
| **Default** | `*` (open) if env not set |
| **Methods** | GET, POST, PUT, DELETE, PATCH, OPTIONS |
| **Headers** | Content-Type, Authorization, X-Requested-With |
| **Applied in** | `REST_Controller` constructor |

---

### c. Input validation and error handling

| Item | Detail |
|------|--------|
| **Forms** | CodeIgniter form validation on student/staff create/edit |
| **HEMIS preflight** | `Hemis_client` — missing campusId, batchId, fiscalYearId, address codes |
| **Wire sanitization** | NBSP trim, BS→AD date conversion, forbidden field stripping |
| **UGC errors** | Logged to `ugc_error_logs`; `hemis_sync_error` on student/staff row |
| **Debug tools** | `admin/ugc_sync/debug_student/{id}`, `admin/ugc_error_log` |
| **Operator messages** | Synced / Error badges with truncated error on hover |

---

### d. Postman collections

| Asset | Path |
|-------|------|
| Automation collection | `postman_tests/HEMIS_Automation.postman_collection.json` |
| Test environment | `postman_tests/HEMIS_Test.postman_environment.json` |
| JWT generator script | `scripts/generate_hemis_api_jwt.php` |
| Smoke script | `tools/hemis_api_smoke.ps1` |

Used for UGC login, Student/Employee create, and integration regression testing.

---

### e. HTTP standard error codes

**Campus REST API** (`REST_Controller`):

| Code | Meaning |
|------|---------|
| 200 | OK |
| 201 | Created |
| 400 | Bad request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not found |
| 500 | Internal server error |

**UGC client handling:**

| Code | Handling |
|------|----------|
| 200–299 | Success; parse `id` from JSON |
| 401 | Token invalid/expired; re-login |
| 413 | Request too large; retry without file attachments |
| 500 | Logged; optional DEV blocker flag for operators |
| 502, 503, 504 | Treated as upstream busy / transient |

---

## 5. Compliance checklist summary

| Section | Overall status | Notes |
|---------|----------------|-------|
| 1. Campus HEMIS Technical Status | **Yes** | DR/backup = partial (manual + cron only) |
| 2. API Integration Status | **Yes** | Tested on UGC dev; production in use |
| 3. Authorization and Access Control | **Yes** | RBAC + JWT Bearer |
| 4. Security Standards | **Partial** | No inbound rate limiting; CORS configurable |

---

## 6. Key file reference

| Purpose | Path |
|---------|------|
| HEMIS operator guide | `HEMIS_README.md` |
| Integration config | `application/config/hemis.php` |
| Settings SQL | `application/sql/hemis_integration_settings.sql` |
| UGC HTTP client | `application/libraries/Hemis_client.php` |
| UGC auth | `application/libraries/Hemis_ugc_auth.php` |
| Student payload helper | `application/helpers/hemis_sync_post_from_db_helper.php` |
| Dev compliance report | `UGC_HEMIS_DEV_Master_Sync_Compliance_Report.md` |
| Progress / stack overview | `PROGRESS_REPORT.md` |

---

## 7. Contacts and maintenance

| Role | Responsibility |
|------|----------------|
| **College IT / HEMIS operator** | Central API credentials, token refresh, manual sync, error log review |
| **UGC HEMIS support** | Central API outages, opaque HTTP 500 on valid payloads |
| **Hosting provider** | Server uptime, SSL, database backups |

---

*This document describes the KVM College deployment of the A Final College System as implemented in the codebase. Update campus ID, URLs, or module toggles if the deployment changes.*
