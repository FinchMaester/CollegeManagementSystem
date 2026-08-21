<?php
/**
 * Safe data alignment for a_final_college (no column drops, no HEMIS ID rewrites).
 * Usage: php tmp_align_data.php [--apply]
 */
$apply = in_array('--apply', $argv, true);
$m = new mysqli('127.0.0.1', 'root', 'root', 'a_final_college');
$m->set_charset('utf8mb4');
if ($m->connect_error) { die($m->connect_error); }

echo ($apply ? "APPLY\n" : "DRY-RUN\n");

$actions = [];

// 1) Fill legacy batch_id from ugc_batch_id
$n = (int)$m->query("SELECT COUNT(*) c FROM students WHERE (batch_id IS NULL OR batch_id=0) AND ugc_batch_id IS NOT NULL AND ugc_batch_id<>0")->fetch_assoc()['c'];
$actions[] = "fill batch_id from ugc_batch_id: $n";
if ($apply && $n) {
    $m->query("UPDATE students SET batch_id = ugc_batch_id WHERE (batch_id IS NULL OR batch_id=0) AND ugc_batch_id IS NOT NULL AND ugc_batch_id<>0");
    echo "updated batch_id: {$m->affected_rows}\n";
}

// 2) Fill fiscal_year_id from ugc if needed (should be 0)
$n = (int)$m->query("SELECT COUNT(*) c FROM students WHERE (fiscal_year_id IS NULL OR fiscal_year_id=0) AND ugc_fiscal_year_id IS NOT NULL AND ugc_fiscal_year_id<>0")->fetch_assoc()['c'];
$actions[] = "fill fiscal_year_id from ugc_fiscal_year_id: $n";
if ($apply && $n) {
    $m->query("UPDATE students SET fiscal_year_id = ugc_fiscal_year_id WHERE (fiscal_year_id IS NULL OR fiscal_year_id=0) AND ugc_fiscal_year_id IS NOT NULL AND ugc_fiscal_year_id<>0");
    echo "updated fiscal_year_id: {$m->affected_rows}\n";
}

// 3) Fix AD admission_year 2023 -> 2080 (AD+57)
$n = (int)$m->query("SELECT COUNT(*) c FROM students WHERE admission_year='2023'")->fetch_assoc()['c'];
$actions[] = "admission_year 2023 -> 2080: $n";
if ($apply && $n) {
    $m->query("UPDATE students SET admission_year='2080' WHERE admission_year='2023'");
    echo "updated admission_year 2023: {$m->affected_rows}\n";
}

// 4) Fix admission_year 0000 from batch nepali when possible
$n = (int)$m->query("SELECT COUNT(*) c FROM students s INNER JOIN ugc_batches b ON b.id=s.ugc_batch_id WHERE s.admission_year IN ('0000','') AND b.batch_nepali IS NOT NULL AND b.batch_nepali<>''")->fetch_assoc()['c'];
$actions[] = "admission_year 0000 from batch_nepali: $n";
if ($apply && $n) {
    $m->query("UPDATE students s INNER JOIN ugc_batches b ON b.id=s.ugc_batch_id
        SET s.admission_year = b.batch_nepali
        WHERE s.admission_year IN ('0000','') AND b.batch_nepali IS NOT NULL AND b.batch_nepali<>''");
    echo "updated admission_year from batch: {$m->affected_rows}\n";
}
$n2 = (int)$m->query("SELECT COUNT(*) c FROM students WHERE admission_year IN ('0000','')")->fetch_assoc()['c'];
$actions[] = "remaining empty/0000 admission_year (leave as-is or set 0000): $n2";
// admission_year is NOT NULL — do not set NULL; leave remaining for manual review
if ($apply && $n2) {
    echo "left $n2 admission_year empty/0000 for manual review (column NOT NULL)\n";
}

// 5) Fill batch_name / batch_name_nepali from ugc_batches when empty
$n = (int)$m->query("SELECT COUNT(*) c FROM students s INNER JOIN ugc_batches b ON b.id=s.ugc_batch_id
 WHERE (s.batch_name IS NULL OR s.batch_name='') AND b.name IS NOT NULL")->fetch_assoc()['c'];
$actions[] = "fill batch_name from ugc_batches: $n";
if ($apply && $n) {
    $m->query("UPDATE students s INNER JOIN ugc_batches b ON b.id=s.ugc_batch_id
        SET s.batch_name = b.name
        WHERE (s.batch_name IS NULL OR s.batch_name='') AND b.name IS NOT NULL");
    echo "updated batch_name: {$m->affected_rows}\n";
}
$n = (int)$m->query("SELECT COUNT(*) c FROM students s INNER JOIN ugc_batches b ON b.id=s.ugc_batch_id
 WHERE (s.batch_name_nepali IS NULL OR s.batch_name_nepali='') AND b.batch_nepali IS NOT NULL")->fetch_assoc()['c'];
$actions[] = "fill batch_name_nepali from ugc_batches: $n";
if ($apply && $n) {
    $m->query("UPDATE students s INNER JOIN ugc_batches b ON b.id=s.ugc_batch_id
        SET s.batch_name_nepali = b.batch_nepali
        WHERE (s.batch_name_nepali IS NULL OR s.batch_name_nepali='') AND b.batch_nepali IS NOT NULL");
    echo "updated batch_name_nepali: {$m->affected_rows}\n";
}

// 6) Where admissionYearId empty but batch set, set admissionYearId = ugc_batch_id
// (KBMC HEMIS model: Batch catalog IS the admission-year id space)
$n = (int)$m->query("SELECT COUNT(*) c FROM students WHERE (admissionYearId IS NULL OR admissionYearId=0) AND ugc_batch_id IS NOT NULL AND ugc_batch_id<>0")->fetch_assoc()['c'];
$actions[] = "fill empty admissionYearId from ugc_batch_id: $n";
if ($apply && $n) {
    $m->query("UPDATE students SET admissionYearId = ugc_batch_id WHERE (admissionYearId IS NULL OR admissionYearId=0) AND ugc_batch_id IS NOT NULL AND ugc_batch_id<>0");
    echo "updated admissionYearId: {$m->affected_rows}\n";
}

// 7) Sync placement mirror from active student_session again
$active = (int)$m->query("SELECT session_id FROM sch_settings LIMIT 1")->fetch_assoc()['session_id'];
$actions[] = "resync students placement from session (active=$active)";
if ($apply) {
    $updated = 0;
    $q = $m->query("SELECT id FROM students");
    while ($st = $q->fetch_assoc()) {
        $sid = (int)$st['id'];
        $ss = $m->query("SELECT class_id, section_id, program_id FROM student_session
            WHERE student_id=$sid
            ORDER BY (session_id=$active) DESC, (is_active='yes') DESC, id DESC LIMIT 1")->fetch_assoc();
        if (!$ss) continue;
        $m->query("UPDATE students SET class_id=".(int)$ss['class_id'].", section_id=".(int)$ss['section_id'].", program_id=".(int)($ss['program_id']?:0)." WHERE id=$sid");
        $updated++;
    }
    echo "placement sync attempted: $updated\n";
}

// 8) Broken student_session refs — soft-fix known garbage row if student is junk
$bad = $m->query("SELECT ss.id, ss.student_id, s.admission_no, s.firstname, ss.program_id, ss.section_id
 FROM student_session ss
 LEFT JOIN programs p ON p.id=ss.program_id
 LEFT JOIN students s ON s.id=ss.student_id
 WHERE ss.program_id IS NOT NULL AND ss.program_id<>0 AND p.id IS NULL")->fetch_assoc();
if ($bad) {
    $actions[] = "broken program/section session id={$bad['id']} student={$bad['student_id']} adm={$bad['admission_no']}";
    if ($apply) {
        // Invalid FKs — null them (do not invent campus program/section)
        $m->query("UPDATE student_session SET program_id=NULL, section_id=NULL WHERE id=".(int)$bad['id']);
        // students.section_id/program_id are NOT NULL in schema — set to 0
        $m->query("UPDATE students SET program_id=0, section_id=0 WHERE id=".(int)$bad['student_id']);
        echo "nulled broken FKs on ss {$bad['id']} / student {$bad['student_id']} (test-like row)\n";
    }
}

// 9) Unique index to prevent future duplicates
$idx = $m->query("SHOW INDEX FROM student_session WHERE Key_name='uniq_student_session'")->fetch_assoc();
$actions[] = "unique (student_id, session_id): " . ($idx ? 'exists' : 'will add');
if ($apply && !$idx) {
    // ensure no dups
    $d = (int)$m->query("SELECT COUNT(*) c FROM (SELECT 1 FROM student_session GROUP BY student_id, session_id HAVING COUNT(*)>1) t")->fetch_assoc()['c'];
    if ($d === 0) {
        $m->query("ALTER TABLE student_session ADD UNIQUE KEY uniq_student_session (student_id, session_id)");
        echo "added unique key uniq_student_session\n";
    } else {
        echo "SKIP unique key — still have $d dup groups\n";
    }
}

// 10) Build admission_year_id_map suggestion from batches (for config) — print only
echo "\nSuggested admission_year_id_map from ugc_batches:\n";
$map = [];
$q = $m->query("SELECT id, batch_nepali FROM ugc_batches WHERE batch_nepali REGEXP '^[0-9]{4}$' ORDER BY batch_nepali");
while ($r = $q->fetch_assoc()) {
    $map[$r['batch_nepali']] = (int)$r['id'];
    echo "  '{$r['batch_nepali']}' => {$r['id']},\n";
}

echo "\nPlan:\n- ".implode("\n- ", $actions)."\n";
if (!$apply) echo "\nRe-run with --apply to execute.\n";
else {
    echo "\n=== VERIFY ===\n";
    echo json_encode($m->query("SELECT
      SUM(batch_id IS NOT NULL AND batch_id<>0) has_batch_id,
      SUM(ugc_batch_id IS NOT NULL AND ugc_batch_id<>0 AND batch_id=ugc_batch_id) batch_synced,
      SUM(admission_year='2023') ay_2023,
      SUM(admission_year='0000') ay_0000,
      SUM((admissionYearId IS NULL OR admissionYearId=0) AND ugc_batch_id IS NOT NULL AND ugc_batch_id<>0) aid_missing_with_batch
    FROM students")->fetch_assoc())."\n";
    $idx = $m->query("SHOW INDEX FROM student_session WHERE Key_name='uniq_student_session'")->num_rows;
    echo "unique index present: ".($idx?1:0)."\n";
}
