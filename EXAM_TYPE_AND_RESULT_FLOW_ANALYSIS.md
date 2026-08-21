# Exam Type and Result Flow — Analysis

This document describes how **exam types** (General Pass/Fail, School Based, College Based, GPA, Average Passing) are defined, stored, and used across exam groups, exams, and result flows.

---

## 1. Exam types (config and labels)

Defined in **`application/config/app-config.php`**:

| Key | English label (from language) |
|-----|--------------------------------|
| `basic_system` | General Purpose (Pass/Fail) |
| `school_grade_system` | School Based Grading System |
| `coll_grade_system` | College Based Grading System |
| `gpa` | GPA Grading System |
| `average_passing` | Average Passing |

Labels come from `application/language/.../system_lang.php` (e.g. `basic_system`, `school_grade_system`, `coll_grade_system`, `gpa_grading_system`, `average_passing`).

---

## 2. Where exam type is set and stored

### 2.1 Exam group (creation / edit)

- **Screens:** **Exam Group** list → Add, or Edit (e.g. `admin/examgroup`, `examgroupList.php`, `examgroupEdit.php`).
- **Field:** Dropdown **Exam Type** with options from `$config['exam_type']`.
- **Stored in:** `exam_groups.exam_type` (one value per exam group).

So the **exam type is chosen once per exam group** and applies to all exams under that group.

### 2.2 Adding an exam under a group

- **Screen:** **Add Exam** (e.g. `admin/examgroup/addexam/<exam_group_id>`).
- **Behaviour:** The exam type is **not** chosen again. The form sends a **hidden** `exam_type` with value from the current exam group: `$examgroup->exam_type`.
- **Stored:**  
  - The **type** is not duplicated on the exam row; it is always read from the parent **exam group** when needed (e.g. `getExamByID()` joins `exam_groups` and returns `exam_groups.exam_type` as `exam_group_type`).  
  - For **Average Passing** only, the form shows **Exam Passing Percentage**; that value is saved in **`exam_group_class_batch_exams.passing_percentage`** (per exam).

So: **type = exam group level**; **passing_percentage = exam level** (only for Average Passing).

---

## 3. How result flow uses exam type

Whenever results are shown (Exam Result, Rank, Marksheet, PDF, student/teacher views), the code:

1. Loads the exam (e.g. `examgroup_model->getExamByID($exam_id)`).
2. Gets **exam_group_type** from that exam (which is `exam_groups.exam_type` for the group).
3. Loads **grades** for that type: `grade_model->getByExamType($exam_group_type)` (from `grades` table where `grades.exam_type` = that type).
4. Branches logic and UI on **exam_group_type** (and, for Average Passing, on **passing_percentage**).

So **the flow of results is driven by the exam type of the exam group** and by the grade set configured for that type.

---

## 4. Behaviour by exam type

### 4.1 General Purpose (Pass/Fail) — `basic_system`

- **Pass/Fail:**  
  - Per subject: if **get_marks < min_marks** for that subject → fail.  
  - Absent in any subject → fail.
- **Grades:** Uses the grade set for `basic_system` from **Grades** (mark_from, mark_upto, grade name, point) for display; pass/fail is still based on min_marks.
- **Rank:** By total marks (or percentage); failed students can be excluded or ranked after pass.
- **UI:** Typically shows marks and grade; no GPA column; result = Pass/Fail.

### 4.2 School Based Grading System — `school_grade_system`

- **Grades:** Uses grade set for `school_grade_system` (percent range → grade letter/point).
- **Pass/Fail:**  
  - Grade letter **NG** (or equivalent) → fail.  
  - Below subject **min_marks** → fail.  
  - Absent → fail.
- **Rank:** By total marks or percentage; grade shown from percent using the school grade set.
- **UI:** Marks, grade letter, result; no GPA column (unless implemented in a specific view).

### 4.3 College Based Grading System — `coll_grade_system`

- Same idea as **School Based**, but uses the grade set for `coll_grade_system`.
- Pass/fail and rank logic are the same pattern: use **grades** for that type, NG/below min/absent = fail.

### 4.4 GPA Grading System — `gpa`

- **Calculation:**  
  - Per subject: percentage → **grade point** (from `grades` for `gpa`).  
  - **Quality point** = grade point × credit_hours.  
  - **GPA** = total_quality_point / total_credit_hour (for that exam or consolidated).
- **Pass/Fail:**  
  - **NG** (or equivalent) in any subject → fail.  
  - **Absent** in any subject → fail (no GPA for that exam).
- **Rank:** By GPA or by total marks depending on screen; GPA and grade letter are shown.
- **UI:** Shows **GPA**, credit hours, quality points, grade letter (e.g. from `grade_from_points()` in rank report). Columns differ from non-GPA types (e.g. “%” vs “GPA”).

### 4.5 Average Passing — `average_passing`

- **Pass/Fail:**  
  - **Overall percentage** = (total get_marks / total max_marks) × 100.  
  - Compare to **exam’s passing_percentage** (from `exam_group_class_batch_exams.passing_percentage`).  
  - If **total_percentage < passing_percentage** → fail.  
  - Absent can also be treated as fail (implementation may vary by view).
- **Grades:** Uses grade set for `average_passing` for displaying grade letter from percentage.
- **Rank:** By total marks or percentage; pass/fail status uses the above rule.
- **UI:** Marks, percentage, grade; “Pass”/“Fail” based on average vs passing_percentage. Passing % can be different per exam (each exam can have its own `passing_percentage`).

---

## 5. Where this is implemented (key files)

| Area | File(s) | What happens |
|------|--------|----------------|
| Exam type config | `application/config/app-config.php` | Defines keys and lang keys for all 5 types. |
| Exam group form | `examgroupList.php`, `examgroupEdit.php` | Exam type dropdown; save to `exam_groups.exam_type`. |
| Add exam form | `addexam.php` | Hidden `exam_type` from group; **passing_percentage** field only when `exam_type == "average_passing"`. |
| Exam validation | `Examgroup.php` (add exam) | Requires **passing_percentage** when `exam_type == "average_passing"`. |
| Load exam + type | `Examgroup_model::getExamByID()` | Returns `exam_groups.exam_type` as **exam_group_type**; exam row gives **passing_percentage**. |
| Grades by type | `Grade_model::getByExamType($exam_type)` | Returns `grades` rows for that exam_type. |
| Result – grades | `Examresult.php` (multiple methods) | `$exam_grades = $this->grade_model->getByExamType($exam->exam_group_type);` then passed to views. |
| Result – pass/fail & rank | `examresult/index.php`, `_partialexamrank.php`, `rankreport.php` | Branch on `exam_details->exam_group_type` (gpa, average_passing, basic_system, etc.) for fail logic and columns. |
| Marksheet / PDF | `_printpdfmarksheet.php`, `_printmarksheet.php` | Same: branch on `exam->exam_group_type` for GPA vs marks, grade display, consolidated result. |
| Student/teacher result | `studentShow.php`, `user/examresult/index.php`, `user/profile.php`, theme `examresult.php` | Use `exam_value->exam_type` (same as exam_group_type) and same branching for GPA, average_passing, basic_system. |

So **yes, the flow of results is according to the exam type**: the type chosen for the exam group controls which grade set is used and how pass/fail and display (GPA vs marks, average passing, etc.) are calculated and shown.

---

## 6. What you must configure for each type

1. **Grades (admin)**  
   For each exam type you use, define a **grade set** in the **Grades** module with `exam_type` = that key (`basic_system`, `school_grade_system`, `coll_grade_system`, `gpa`, `average_passing`). Each set typically has rows with mark_from, mark_upto, name (e.g. A, B, NG), and point (for GPA).

2. **Exam group**  
   When creating/editing an exam group, select the correct **Exam type**. All exams under that group will use that type.

3. **Average Passing only**  
   When adding/editing an **exam** under an **Average Passing** group, fill **Exam Passing Percentage** so the system can decide pass/fail by overall percentage.

4. **GPA exams**  
   Ensure exam subjects have **credit_hours** set so GPA (quality point / credit hour) is correct.

---

## 7. Summary

- **Exam type** is set **once per exam group** and stored in `exam_groups.exam_type`.
- **Result flow** always uses that type (as `exam_group_type`) to load the right **grades** and to apply the right **pass/fail and display rules** (General Pass/Fail, School/College grade, GPA, or Average Passing).
- So the behaviour of results **is** according to the exam type; ensuring the correct type is selected for the group and the right grade sets and (for Average Passing) passing percentages are set keeps everything consistent.
