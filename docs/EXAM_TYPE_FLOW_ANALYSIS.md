# Exam Type Flow Analysis

This document describes how **Exam Type** (selected in Exam Group) flows through the system and how it affects result calculation and display in Exam Result, Generate Rank, and Marksheet.

---

## 1. Where Exam Type Is Stored and Loaded

### Storage
- **Table:** `exam_groups`
- **Column:** `exam_type` (string)
- **Set when:** Creating or editing an Exam Group at **admin/examgroup** (add or edit form).
- **Config labels** (`application/config/app-config.php`):
  - `basic_system` → "General Purpose (Pass/Fail)"
  - `school_grade_system` → School grade system
  - `coll_grade_system` → College grade system
  - `gpa` → GPA grading system
  - `average_passing` → Average / passing percentage

### How It Reaches “Exam” Level
- Each **exam** is a row in `exam_group_class_batch_exams` and belongs to one `exam_group_id`.
- The **exam group’s** `exam_type` is exposed as **`exam_group_type`** whenever an exam is loaded:
  - **Model:** `Examgroup_model->getExamByID($id)`  
    SQL: `exam_groups.exam_type as exam_group_type`  
    So every place that loads “exam” by ID gets `$exam->exam_group_type` (or `$exam_details->exam_group_type`).

### Grades per Exam Type
- **Table:** `grades` (mark_from, mark_upto, point, name, exam_type, …)
- **Model:** `Grade_model->getByExamType($exam_type)`
- Grading boundaries (and grade points for GPA) are **per exam type**; the correct set is always loaded using the exam’s `exam_group_type`.

---

## 2. admin/examresult (Exam Result Table)

**URL:** `http://localhost:8000/admin/examresult`  
**Controller:** `Examresult->index()`  
**View:** `application/views/admin/examresult/index.php`  
**Data:** `$exam_details` = `getExamByID($exam_id)` → has `exam_group_type`, `passing_percentage`.  
**Grades:** `$exam_grades` = `getByExamType($exam_details->exam_group_type)`.

### How Result Is Calculated (per student)

1. **Precompute (in view)** using `$__exam_type = $exam_details->exam_group_type`:
   - **gpa**
     - Failed if: any subject **Absent** OR any subject grade = **NG**.
     - Rank: by total **obtained marks** (not GPA).
   - **average_passing**
     - Failed if: any **Absent** OR **overall %** < `exam_details->passing_percentage`.
   - **basic_system**
     - Failed if: any **Absent** OR any subject **get_marks < min_marks**.
   - **Other (school_grade_system, coll_grade_system)**
     - Failed if: any **Absent** OR any subject grade = **NG**.

2. **Subject columns**
   - Same for all types: show **marks** and **grade** from `get_ExamGrade($exam_grades, percentage)` (and for GPA, `findGradePoints` is used for quality points in the controller precompute).

3. **Result column**
   - **Absent:** show "Absent".
   - **Failed:** show "Fail".
   - **basic_system + passed:** show **"Pass"**.
   - **gpa + passed:** show **`TotalQP/TotalCH=GPA [Letter]`** (e.g. `12.00/4.00=3.00 [B]`) where Letter = `grade_from_points(final_gpa)` (same mapping as marksheet). **gpa + failed:** "Fail".
   - **Others + passed:** show **grade point + [Grade]** from overall percentage.

### Controller Precompute (index)
- `Examresult->index()` precomputes for each student:
  - `_absent`, `_ng`, `_total_max`, `_total_obt`
  - For **gpa:** `_final_gpa`, `_final_grade` via `findGradePoints()` and `getGradeFromPoints()` (implemented in controller).
- Rank is by **total obtained marks**; eligible = not absent and not NG.

So **exam type** drives: fail rules, what goes in the Result cell (Pass/Fail vs GPA vs grade points), and which grade set is used.

---

## 3. Generate Rank (admin/examgroup/addexam/{id})

**URL:** e.g. `http://localhost:8000/admin/examgroup/addexam/6` → **Generate Rank** tab.  
**Controller:** `Examresult->examrank()` (AJAX).  
**View:** `application/views/admin/examresult/_partialexamrank.php`  
**Data:** `$exam_details` = `getExamByID($exam_id)` → same `exam_group_type`.  
**Grades:** `$exam_grades` = `getByExamType($exam_details->exam_group_type)`.

### How Result Is Calculated (per student)

1. **Pass/Fail and totals**
   - **gpa**
     - For each subject: percentage → `findGradePoints($exam_grades, percentage)` → quality points; sum `point * credit_hours` → `total_quality_point`; sum `credit_hours` → `total_credit_hour`.
     - Fail if: any **Absent** OR any subject grade = **NG** (`student_exam_status = 0`).
   - **average_passing**
     - After computing total marks and percentage: fail if `exam_details->passing_percentage > total_percentage`.
   - **Other (basic_system, school/coll_grade)**
     - Fail if: any **Absent** OR any subject **get_marks < min_marks**.

2. **Result column in table**
   - **gpa:**  
     `total_quality_point / total_credit_hour = GPA [Letter]` (Letter from cumulative GPA via same scale as exam result / marksheet), or **Fail** if absent/NG; **"--"** if no credit hours.
   - **Non-GPA:**  
     `get_marks / total_marks` (no percentage column for GPA; percentage column only for non-GPA).

So **exam type** controls: how pass/fail is decided, whether GPA and credit hours are used, and how the Result cell and optional % column are shown.

---

## 4. Marksheet (Print / View Result)

**URLs:**
- **Criteria form:** `http://localhost:8000/admin/examresult/marksheet`  
  (exam group, exam, session, section, class, program, template → then “Print” or “View”).
- **Print/PDF:** same controller methods that load the marksheet **views** with result data.

**Controller:** `Examresult`: `viewmarksheet()`, `printmarksheet()`, `pdftmarksheet()`, and internal `_prepareMarksheetData()`.  
**Data:** `$data['exam']` = `getExamByID($exam_id)` → `$exam->exam_group_type`.  
**Grades:** `getByExamType($exam->exam_group_type)` (or fallback from `grades` if exam missing).  
**Views:** `_printmarksheet.php` (HTML print) and `_printpdfmarksheet.php` (PDF).

### How Exam Type Affects the Marksheet

- **basic_system (General Purpose Pass/Fail)**  
  - **Subject table:** Columns: Subject, Max Marks, Min Marks, Marks Obtained, **Result** (Pass/Fail per subject).  
  - **Overall:** One row: **Result: Pass** or **Result: Fail** (fail if any absent or any subject below min).

- **gpa**  
  - **Subject table:** SN, Subject, Credit Hours, Grade Point, Grade, Final Grade (and TH/IN pairing where used).  
  - **Summary row:** Grade Point Average (GPA).  
  - No “Result: Pass/Fail” line; result is expressed as GPA and grades.

- **school_grade_system / coll_grade_system**  
  - Similar to GPA-style table (credit hours, grade point, grade, final grade) and grading block; result is grade-based.

- **average_passing**  
  - In PDF marksheet: result is **Pass** if overall percentage ≥ `exam->passing_percentage`, else **Fail**.

So **exam type** decides: table columns (marks vs credit/points/grades), presence of Pass/Fail row, and whether result is Pass/Fail vs GPA vs grade-based.

### admin/marksheet vs admin/examresult/marksheet

- **admin/marksheet**  
  - Controller: `Marksheet`.  
  - Purpose: **Design marksheet templates** (logos, headers, layout, which fields to show).  
  - Does **not** compute or show exam results; it only manages template design.

- **admin/examresult/marksheet**  
  - Controller: `Examresult`.  
  - Purpose: **Select exam + class/section/session and template**, then **print/view** the actual marksheet with results.  
  - Result content and layout (including exam-type-specific tables and Pass/Fail) come from `_printmarksheet` / `_printpdfmarksheet` and are driven by **exam_group_type** as above.

---

## 5. End-to-End Flow Summary

| Step | What happens |
|------|-----------------------------|
| 1 | User creates/edits **Exam Group** at admin/examgroup and selects **Exam Type** → saved in `exam_groups.exam_type`. |
| 2 | User creates **exams** under that group (e.g. at addexam/6). Each exam inherits the group’s type via join: `getExamByID()` returns `exam_group_type` = that `exam_type`. |
| 3 | **Grades** for that type are in `grades` with `exam_type` = same key; loaded everywhere with `getByExamType($exam_details->exam_group_type)` or `getByExamType($exam->exam_group_type)`. |
| 4 | **admin/examresult:** Index loads exam + grades by type; precomputes fail and (for GPA) final GPA/grade; view shows subject marks/grades and **Result** (Pass/Fail for basic_system, GPA [Grade] for gpa, etc.). |
| 5 | **Generate Rank:** examrank() loads same exam + grades; _partialexamrank computes pass/fail and (for GPA) quality points and shows Result column and optional % by type. |
| 6 | **Marksheet:** viewmarksheet/printmarksheet load exam + grades; _printmarksheet and _printpdfmarksheet branch on `exam->exam_group_type` to show the right table (e.g. Pass/Fail table for basic_system, GPA table for gpa) and the right “Result” line (Pass/Fail or GPA/grades). |

So **exam type is chosen once per exam group** and then **propagates via `exam_group_type`** to result calculation and display everywhere: exam result page, generate rank tab, and marksheet print/view. The only place that does **not** use exam type for result content is **admin/marksheet**, which only handles template design.
