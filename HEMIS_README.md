# HEMIS / UGC integration — operator guide

This campus system syncs student data to the UGC Nepal HEMIS API. Use this document for credentials, testing, UI badges, and how “school” table names map to a **college** deployment.

## Bearer token (`.env`)

1. Copy `.env.example` to `.env` in the project root (same folder as `index.php`).
2. Set:

   ```env
   HEMIS_REMOTE_BEARER_TOKEN=your_token_here
   ```

   The token is read via `app_env()` and merged into effective HEMIS config (`application/config/hemis.php`, `hemis_integration_model` / DB overrides).

3. After changing `.env`, restart PHP / clear opcode cache if your host caches environment variables.

4. **Security:** never commit `.env` (it is gitignored). Rotate the token if it leaks.

Related keys (optional):

- `HEMIS_REMOTE_BASE_URL` — default `https://hemisapi.ugcnepal.edu.np`
- `HEMIS_REMOTE_LOGIN_EMAIL` / `HEMIS_REMOTE_LOGIN_PASSWORD` — used when bearer token is empty and auto-login is configured in DB.

## Mock mode (testing without UGC)

Set in **`.env`** (recommended); values merge into effective config (`hemis_integration_model` + DB overrides):

```env
HEMIS_MOCK=true
```

- **`HEMIS_MOCK=true` (default in `.env.example`):** no HTTP to UGC; a fake `hemis_student_id` is stored locally after create (good for dev).
- **`HEMIS_MOCK=false`:** uses `HEMIS_LOCAL_MOCK_BASE_URL` if set (local mock HTTP), otherwise `HEMIS_REMOTE_BASE_URL` with bearer token.

If `HEMIS_MOCK` is present in `.env`, it overrides `mock_mode` from the admin DB screen. Use **`HEMIS_MOCK=false`** only on hosts that should call real UGC.

File reference: `application/config/hemis.php` sets `$config['hemis_mock_mode']` and the `hemis` array `mock_mode` from `HEMIS_MOCK`.

## “HEMIS Status” badges (student list)

| Display | Meaning |
|--------|---------|
| **Synced** (green) | `hemis_student_id` is set and `hemis_sync_error` is empty. |
| **Error** (red) | `hemis_sync_error` has text; hover for truncated message. Use **Retry** (refresh icon) to push again. |
| **—** (muted) | Not synced yet (`hemis_student_id` empty) and no error stored. |

## Bulk sync (student search list)

Use **Bulk Sync to HEMIS**: the browser loads a **queue** of student IDs (`student/hemis_sync_queue_ids`) and calls **`student/manual_sync/{id}`** **one student at a time** (short pause between requests). This avoids PHP gateway timeouts. If UGC returns **three consecutive** gateway/server-busy outcomes (HTTP 502/503/504 or `Server Busy:` errors), the queue **stops** and alerts the operator.

The student list shows **Mode: MOCK** (yellow) or **Mode: LIVE** (green) next to the button, driven by effective `mock_mode` / `HEMIS_MOCK`.

## Stale sync errors (optional DB column)

To prune old error messages automatically, add the column (once):

- `application/sql/hemis_sync_error_at.sql`

Then schedule (same secret as other cron routes: `sch_settings.cron_secret_key`):

```text
GET https://YOUR_HOST/index.php/cron/hemis_errors_prune/YOUR_CRON_SECRET
```

Clears `hemis_sync_error` when `hemis_sync_error_at` is older than 30 days.

**Go-live checklist (one-time):** fill `application/config/ugc_program_codes.php` with official UGC program codes, then:

```text
GET https://YOUR_HOST/index.php/cron/hemis_go_live/YOUR_CRON_SECRET?mode=report
```

Optional: `mode=fix_errors` (clear conservative dev/test `hemis_sync_error` patterns), `mode=fix_gender` (map Male/Female/Other → `1`/`2`/`3` in DB — **backup first**), or `mode=fix_all`. See `application/sql/hemis_go_live_production.sql`.

## School vs college: `class_id` / `section_id`

The database still uses CodeIgniter table names **`classes`** and **`sections`** for historical reasons. In this **college** product they mean:

- **`student_session.class_id` → `classes`:** academic level / year row (e.g. Bachelor Year 1), **not** “Grade 3”.
- **`student_session.section_id` → `sections`:** semester or term cohort (e.g. Semester 1), used as the HEMIS **`semester`** label in `api/Student` list output.
- **Program** comes from **`programs`** / `student_session.program_id` / `class_sections` — that is the degree programme for UGC.

The REST mapper documents this on `get_level_name()` / `get_semester()` in `application/controllers/api/Student.php`.

## Bikram Sambat (BS) dates

UGC expects BS fields such as **`doBBS`** on outbound payloads. Use the global helper:

- `convert_ad_to_bs($ad_date)` in `application/helpers/nepali_calendar_helper.php` (autoloaded).

It delegates to `Customlib::convertADDateToBS()` when available. For calendar-accurate BS (not approximate), plug in a dedicated Nepali calendar library later.

## Health check

Staff (logged in): `check/health` — JSON for disk space, writable log/session dirs, and env URL shape.
