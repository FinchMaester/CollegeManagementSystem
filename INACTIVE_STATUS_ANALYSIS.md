# Inactive Status Analysis – Students and Staff

## Summary

| Area | Staff | Students |
|------|--------|----------|
| **Login blocked when inactive** | ✅ Yes | ❌ **No** (gap) |
| **Status stored** | `staff.is_active` (1/0) | `students.is_active` ('yes'/'no') + `users.is_active` for login |
| **Disable handler** | ✅ `admin/Staff::disablestaff()` | ❌ **Missing** `Student::disable_reason()` |
| **Where inactive is respected** | Login, attendance, reports, lists | Lists, parent children, many reports; **not login** |

---

## 1. Staff – Inactive Status

### How it works
- **Storage:** `staff.is_active` (1 = active, 0 = inactive). Inactive is a soft delete.
- **Setting inactive:** Admin uses “Disable” → `admin/Staff` controller calls `staff_model->disablestaff($data)` with `is_active => 0` (and optional `disable_at`).

### Where it has effect
- **Login (admin panel):** `Site::login()` uses `staff_model->checkLogin()` which returns the staff row. Then it checks `if ($result->is_active)`. If inactive (0), user sees *“Your account is disabled...”*. ✅ **Login is blocked.**
- **Forgot password:** Only sends reset if `$result->is_active == '1'`. ✅ Inactive staff cannot reset password.
- **Staff attendance:** `Staffattendancemodel` and related code use `staff.is_active = 1`. ✅ Inactive staff are excluded from attendance.
- **Reports / lists:** Most staff lists and dashboards filter with `staff.is_active = 1` (e.g. `Staff_model::getAll`, `getActiveStaffCount`, ID cards, etc.). ✅ Inactive staff are excluded where intended.

### Conclusion – Staff
Inactive status for staff is **effective**: they cannot log in, cannot reset password, and are excluded from attendance and normal staff lists/reports.

---

## 2. Students – Inactive Status

### How it works
- **Storage:**  
  - `students.is_active` ('yes' / 'no') – main student record.  
  - **Login** uses `users` table: `users.is_active` ('yes' / 'no') where `users.user_id = students.id` and `users.role = 'student'`.
- **Setting inactive:**  
  - UI: Student profile → “Disable” → modal with reason/date/note → form posts via AJAX to **`student/disable_reason`**.  
  - **Problem:** There is **no** `disable_reason()` method in `application/controllers/Student.php`. So this URL returns 404 and the disable action **never runs**.  
  - Model: `Student_model::disableStudent($id, $data)` only updates the `students` table; it does **not** update `users`.

### Where it has effect (students.is_active only)
- **Parent’s child list:** `Student_model` uses `students.is_active = 'yes'`. ✅ Inactive students are excluded from parent’s list.
- **Many reports/lists:** e.g. certificate, generate certificate, diagnosis, graduation, dashboard, HEMIS-style reports, etc. use `students.is_active = 'yes'` or `student_session.is_active = 'yes'`. ✅ Inactive students are excluded.
- **Admin student list (search):** Does **not** filter by `is_active`; shows all students and displays an Active/Inactive badge. So admins can still see and manage inactive students. ✅ Acceptable.

### Where it does **not** have effect (login)
- **Student/parent login:** `Site::userlogin()` uses `User_model->checkLogin()`, which reads **`users.is_active`**. The check is `if ($user->is_active == "yes")`. So only the **users** row controls login.
- When a student is “disabled”, only `students.is_active` is set to `'no'` (and even that only if the missing handler is added). The corresponding **`users.is_active`** is **never** set to `'no'`.
- **Result:** Even if we fix the handler and update `students.is_active`, the student can **still log in** until `users.is_active` is also set to `'no'`. ❌ **Inactive status does not block student login.**

### Conclusion – Students
- **Disable action is broken:** No controller method for `student/disable_reason`, so the disable form does nothing (404).
- **Even after fixing that:** Only `students` is updated; `users` is not. So **inactive students can still log in** until the code is updated to sync `users.is_active` when disabling (and to set it back to `'yes'` when re-enabling).

---

## 3. Recommendations (and what was done)

1. **Add missing disable handler** ✅ **Done**  
   In `application/controllers/Student.php`, a method `disable_reason()` was added that:
   - Accepts POST (`student_id`, `reason`, `disable_date`, `note`), validates, and updates `students` via `student_model->disableStudent()` with `is_active = 'no'`, `dis_reason`, `dis_note`, `disable_at`, `updated_at`.
   - Returns JSON for the existing AJAX form on the student show page.

2. **Sync `users.is_active` when disabling a student** ✅ **Done**  
   Inside `disable_reason()`, after updating the student record, the code now runs:
   - `UPDATE users SET is_active = 'no' WHERE user_id = <student_id> AND role = 'student'`.
   So inactive students can no longer log in.

3. **Re-enable (if you add it)**  
   If you add an “Enable student” action (or when editing a student and setting `is_active` back to `'yes'`), also set `users.is_active = 'yes'` for that student’s login row so they can log in again.

4. **Optional – login check on student table**  
   For extra safety, in `Site::userlogin()` you could also check `students.is_active` when the role is student. Prefer still syncing `users.is_active` so one source of truth is consistent everywhere.

---

## 4. Files Referenced

- **Login:** `application/controllers/Site.php` (admin login ~L104, user login ~L457).
- **Staff disable:** `application/controllers/admin/Staff.php` (disablestaff), `application/models/Staff_model.php` (disablestaff, checkLogin/getByEmail).
- **Student disable UI:** `application/views/student/studentShow.php` (disable modal, AJAX to `student/disable_reason`).
- **Student disable model:** `application/models/Student_model.php` (`disableStudent()`).
- **User login check:** `application/models/User_model.php` (`checkLogin()` uses `users.is_active`).
