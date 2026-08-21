# A Final College System — Progress Report

**Document:** System Progress Report  
**Scope:** Whole system analysis and development progress  
**Date:** February 2026  

---

## 1. Background

### 1.1 System Overview

**A Final College System** (also referred to as *Systemcollege* in the codebase) is a **college/school management information system** built to support day-to-day operations of an educational institution. It provides role-based access for administrators, staff, teachers, students, parents, accountants, and librarians, with separate dashboards and workflows for each role.

### 1.2 Technology Stack

| Layer | Technology |
|--------|------------|
| **Framework** | CodeIgniter (PHP MVC) |
| **Backend** | PHP (with CodeIgniter 3.x structure) |
| **Database** | MySQL / MariaDB (tables referenced: `students`, `staff`, `exam_groups`, `subject_groups`, etc.) |
| **Frontend** | HTML, CSS, JavaScript, jQuery; Bootstrap-based admin theme |
| **Assets** | Backend themes, DataTables, FullCalendar, CKEditor, Select2, date/time pickers, Nepali date picker |
| **APIs** | CodeIgniter REST server (`codeigniter-restserver`), JWT (Firebase PHP-JWT) for authentication |
| **Email** | PHPMailer |
| **Payments** | Omnipay (Stripe, PayPal, Razorpay, Paytm, Paystack, SSLCommerz, Flutterwave, PayU, etc.), plus gateway-specific integrations (Midtrans, Billplz, 2Checkout, Toyyibpay, Skrill, Payhere, Payfast) |
| **Deployment** | Typical LAMP-style (e.g. Laragon on Windows); `index.php` as front controller |

### 1.3 Scope of the System

The system covers:

- **Academic:** Programs, classes, sections, batches, sessions, subjects, subject groups, timetables, syllabus, lesson plans.
- **People:** Students (with sessions, fees, attendance, ID cards), staff, teachers (with designations, departments, attendance, payroll).
- **Examinations:** Exam groups, exams per class/batch, exam subjects (with per-exam and exam-group subject groups), exam results, marks, marksheets, grades, rank generation, online exams.
- **Finance:** Fee types, fee groups, fee master, fee collection (offline/online), discounts, transport fee, income/expense, payroll.
- **Library:** Books (with program/class/section locking), library cards, issue/return, members.
- **Other:** Hostel (rooms, room types), transport (routes, vehicles, pickup points), visitors, inventory (items, categories, stores, stock, suppliers), notifications, homework, leave requests, timeline, calendar, chat, content, video tutorials.
- **Front CMS:** Pages, notices, menus, gallery, events, banner.
- **Access control:** Roles, permissions, sidebar menus (`sidebar_menus`, `sidebar_sub_menus`), module-based privileges (e.g. `exam`, `exam_subject`, `exam_marks`, `generate_rank`).

---

## 2. Objectives

The system is designed to achieve the following:

1. **Centralise institutional operations** — Single platform for student lifecycle, staff management, examinations, fees, library, and reporting.
2. **Support multiple roles** — Clear separation between admin, teacher, student, parent, accountant, and librarian workflows.
3. **Flexible examination setup** — Support for exam groups, multiple exams per group, and subject groups at both exam-group (global) and per-exam level, so subjects in “Add Exam Subject” reflect the correct subject group(s).
4. **Financial management** — Fee structure, collection (offline and multiple online gateways), reminders, and basic finance reports.
5. **Compliance and reporting** — Attendance, marks, results, certificates, ID cards, and other reports (e.g. HEMIS-style, collection reports).
6. **Security and access** — Role-based access, login blocking for inactive staff and students (with sync of `users.is_active` when disabling students), and documented handling of inactive status.
7. **Extensibility** — REST APIs for students, employees, programs, library, labs, graduation, student upgrade, drop-out, and integration points for payment gateways and third-party tools.

Recent development has focused on:

- Making **exam subject selection** strictly follow subject groups (per-exam and, when no per-exam assignment exists, exam-group level), and never show the full subject list.
- Fixing **disabled students** (disable handler and syncing `users.is_active`) so inactive students cannot log in.
- Improving **database and UI consistency** (e.g. disabled students menu URL, book–program/level locking, diagnostic SQL for exam subject groups).

---

## 3. Methodology

### 3.1 Architecture

- **MVC:** Controllers in `application/controllers` (admin, user, api, onlineadmission, gateway_ins, etc.), models in `application/models`, views in `application/views` with role-based subfolders (admin, user, student, layout, print).
- **Configuration:** `application/config` (config, database, routes, autoload, etc.); environment-specific base URL and index page handling.
- **Libraries & helpers:** Custom and third-party in `application/libraries`, `application/helpers`; `application/third_party` for Omnipay, Midtrans, Billplz, etc.
- **Language:** Multi-language support via `application/language` (e.g. English, Nepali, Hindi, and many others for UI strings).

### 3.2 Database

- **Schema:** Tables created/updated via SQL scripts in `application/sql` (no automated migration runner). Key tables include `exam_subject_groups` (per-exam subject group assignment), `exam_group_subject_groups` (exam-group-level subject groups), `subject_group_subjects`, `sidebar_menus` / `sidebar_sub_menus`, `students`, `users`, `staff`, etc.
- **Diagnostics:** SQL scripts (e.g. `check_exam_subject_groups.sql`) used to verify assignment of subject groups and explain behaviour of “Add Exam Subject” (per-exam vs global).

### 3.3 Development Approach

- **Incremental feature and fix delivery:** New behaviour (e.g. per-exam subject groups, disable student + sync `users.is_active`) implemented in discrete steps with controller/model/view and SQL changes.
- **Backward compatibility:** Exam-group (global) subject groups retained as fallback when an exam has no per-exam subject groups, preserving previous “global” behaviour while allowing per-exam override.
- **Documentation:** In-repo documents (e.g. `INACTIVE_STATUS_ANALYSIS.md`, `PROGRESS_REPORT.md`) and inline comments used to record logic (e.g. when subject list is per-exam vs global, and why `users.is_active` is synced on disable).

---

## 4. Outcomes

### 4.1 Delivered Modules and Features

| Area | Outcome |
|------|----------|
| **Students** | Admission, sessions, fees, attendance, ID cards, certificates, bulk operations, multi-class, disabled students (with working disable handler and login block). |
| **Staff & teachers** | CRUD, designations, departments, attendance, payroll, ID cards; teacher–subject/class assignment; inactive staff blocked at login. |
| **Examinations** | Exam groups and exams; per-exam and exam-group subject groups; “Add Exam Subject” filtered by subject groups; link students (levels from subject groups); exam results, marks, marksheets, grades, rank generation. |
| **Fees & finance** | Fee types/groups/master, collection (offline + many gateways), discounts, transport fee, finance reports. |
| **Library** | Books (with optional program/class/section lock), library cards, generate cards, issue/return, members. |
| **Hostel & transport** | Hostels, rooms, room types; routes, vehicles, pickup points. |
| **Timetable & syllabus** | Timetables (admin/user), syllabus, lesson plan. |
| **Online exam** | Online exams, questions, attempts, results. |
| **Online admission** | Checkout and payment gateway integrations for admission fees. |
| **Front CMS** | Pages, notices, menus, gallery, events, banner. |
| **APIs** | Student, Employee, ProgramMgmt, Library, Labs, Graduation, StudentUpgrade, DropOut (REST + JWT where applicable). |
| **Access & UX** | Role-based menus and permissions; sidebar and routes aligned (e.g. disabled students menu URL fixed). |

### 4.2 Recent Improvements (Examination & Subject Groups)

- **Per-exam subject groups:** Table `exam_subject_groups` and UI “Assign Subject Group” per exam so each exam can have its own subject group(s).
- **Subject list in Add Exam Subject:**  
  - If an exam has per-exam subject groups → only subjects from those groups are shown.  
  - If not → subjects from the exam group’s (global) subject groups are shown.  
  - No fallback to “all subjects”; empty list with clear message when no groups are assigned.
- **Link students:** Levels (section/program/class) in “Assign Student” restricted to levels from the exam’s subject group(s); validation when subject groups are assigned.
- **Database and diagnostics:** `add_exam_subject_groups.sql`, `check_exam_subject_groups.sql` for setup and troubleshooting; query builder reset in model to avoid polluted queries.

### 4.3 Other Documented Outcomes

- **Disabled students:** `disable_reason()` controller added; `students.is_active` and `users.is_active` updated together so inactive students cannot log in (see `INACTIVE_STATUS_ANALYSIS.md`).
- **Books:** Optional program/class/section/session lock via `add_book_program_level_lock.sql`.
- **Menus:** Disabled students menu URL corrected via `fix_disabled_students_menu_url.sql`.

---

## 5. Conclusion

A Final College System is a **feature-rich college management system** built on CodeIgniter and MySQL, with clear separation of roles, extensive examination and fee management, and multiple payment and API integration points. Recent work has strengthened **examination and subject group behaviour** (per-exam vs global, correct subject list in Add Exam Subject and link students) and **student inactive status** (working disable and login block). The system is suitable for institutional use with ongoing maintenance and extension. Documentation (including this report and the inactive-status analysis) supports future development and troubleshooting.

---

## 6. Recommendations

### 6.1 Short Term

1. **README and setup:** Expand `README.md` with installation steps (e.g. Laragon/XAMPP), database creation, running SQL scripts in `application/sql`, `base_url` and config, and optional cron for scheduled tasks.
2. **Environment:** Use a single place (e.g. `.env` or config by environment) for base URL and database credentials in production to avoid hardcoding.
3. **Exam subject groups:** After deployment, run `check_exam_subject_groups.sql` to confirm per-exam vs global assignments; train staff on “Assign Subject Group” per exam when different exams need different subject sets.

### 6.2 Medium Term

1. **Student re-enable:** When re-enabling a student, set `users.is_active = 'yes'` so login is restored (as noted in `INACTIVE_STATUS_ANALYSIS.md`).
2. **Testing:** Add automated tests (e.g. PHPUnit) for critical flows (login, exam subject list logic, disable student, fee collection) to reduce regressions.
3. **Logging and errors:** Standardise logging for exam subject group and payment flows; ensure no sensitive data in logs; consider a simple error-reporting page for admins.

### 6.3 Long Term

1. **CodeIgniter and PHP:** Plan upgrade path to a supported PHP version and, if needed, to CodeIgniter 4 or another framework, with incremental refactoring.
2. **APIs:** Version API endpoints and document them (OpenAPI/Swagger) for external integrations.
3. **Security:** Regular review of authentication, authorization, and input validation; CSRF and XSS protection; secure session and password reset handling.
4. **Backup and recovery:** Documented DB backup and restore procedure; optional automated backups for production.

---

*End of Progress Report*
