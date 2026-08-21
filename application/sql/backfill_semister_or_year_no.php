<?php
/**
 * Backfill semister_or_year_no from class + UGC program_type.
 * Usage: php application/sql/backfill_semister_or_year_no.php [--apply]
 */
$apply = in_array('--apply', $argv ?? array(), true);

// Bootstrap minimal CI is heavy; call helper logic inline using mysqli + same regex rules.
$m = new mysqli('127.0.0.1', 'root', 'root', 'a_final_college');
$m->set_charset('utf8mb4');

function year_from_class_title($title)
{
    $blob = strtolower(trim((string) $title));
    if ($blob === '') {
        return '';
    }
    if (preg_match('/\b([1-4])(?:st|nd|rd|th)?\s*year\b/', $blob, $m)) {
        $map = array('1' => 'First', '2' => 'Second', '3' => 'Third', '4' => 'Fourth');
        return $map[$m[1]] ?? '';
    }
    if (preg_match('/\b([1-8])(?:st|nd|rd|th)?\s*sem/', $blob, $m)) {
        $ord = array(1 => 'First', 2 => 'Second', 3 => 'Third', 4 => 'Fourth', 5 => 'Fifth', 6 => 'Sixth', 7 => 'Seventh', 8 => 'Eighth');
        return $ord[(int) $m[1]] ?? '';
    }
    if (preg_match('/^[1-4]$/', $blob)) {
        $map = array('1' => 'First', '2' => 'Second', '3' => 'Third', '4' => 'Fourth');
        return $map[$blob] ?? '';
    }
    foreach (array('fourth' => 'Fourth', 'third' => 'Third', 'second' => 'Second', 'first' => 'First') as $w => $l) {
        if (strpos($blob, $w) !== false) {
            return $l;
        }
    }
    return '';
}

$classes = array();
$q = $m->query('SELECT id, class FROM classes');
while ($r = $q->fetch_assoc()) {
    $classes[(int) $r['id']] = $r['class'];
}
$progTypes = array();
$q = $m->query('SELECT program_id, program_type FROM ugc_programs');
while ($r = $q->fetch_assoc()) {
    $progTypes[(int) $r['program_id']] = strtolower((string) $r['program_type']);
}

echo ($apply ? "APPLY\n" : "DRY-RUN\n");

// student_session rows
$updatedSs = 0;
$q = $m->query('SELECT ss.id, ss.class_id, s.ugc_program_id, ss.semister_or_year_no
 FROM student_session ss
 INNER JOIN students s ON s.id = ss.student_id');
$plans = array();
while ($r = $q->fetch_assoc()) {
    $cid = (int) $r['class_id'];
    $title = isset($classes[$cid]) ? $classes[$cid] : '';
    $label = year_from_class_title($title);
    if ($label === '') {
        continue;
    }
    $cur = trim((string) $r['semister_or_year_no']);
    if ($cur === $label) {
        continue;
    }
    $plans[] = array((int) $r['id'], $label, $cur, $title);
}
echo "student_session to update: " . count($plans) . "\n";
if ($apply) {
    $stmt = $m->prepare('UPDATE student_session SET semister_or_year_no=? WHERE id=?');
    foreach ($plans as $p) {
        $stmt->bind_param('si', $p[1], $p[0]);
        $stmt->execute();
        $updatedSs++;
    }
    $stmt->close();
    echo "updated student_session: $updatedSs\n";
} else {
    foreach (array_slice($plans, 0, 8) as $p) {
        echo "  ss {$p[0]}: '{$p[2]}' -> '{$p[1]}' (class {$p[3]})\n";
    }
}

// students cache from active session placement
$active = (int) $m->query('SELECT session_id FROM sch_settings LIMIT 1')->fetch_assoc()['session_id'];
$stuPlans = array();
$q = $m->query("SELECT s.id, s.semister_or_year_no, ss.class_id
 FROM students s
 INNER JOIN student_session ss ON ss.id = (
   SELECT ss2.id FROM student_session ss2 WHERE ss2.student_id=s.id
   ORDER BY (ss2.session_id=$active) DESC, (ss2.is_active='yes') DESC, ss2.id DESC LIMIT 1
 )");
while ($r = $q->fetch_assoc()) {
    $cid = (int) $r['class_id'];
    $title = isset($classes[$cid]) ? $classes[$cid] : '';
    $label = year_from_class_title($title);
    if ($label === '') {
        continue;
    }
    $cur = trim((string) $r['semister_or_year_no']);
    if ($cur === $label) {
        continue;
    }
    $stuPlans[] = array((int) $r['id'], $label, $cur, $title);
}
echo "students to update: " . count($stuPlans) . "\n";
if ($apply) {
    $stmt = $m->prepare('UPDATE students SET semister_or_year_no=? WHERE id=?');
    $n = 0;
    foreach ($stuPlans as $p) {
        $stmt->bind_param('si', $p[1], $p[0]);
        $stmt->execute();
        $n++;
    }
    $stmt->close();
    echo "updated students: $n\n";
} else {
    foreach (array_slice($stuPlans, 0, 8) as $p) {
        echo "  student {$p[0]}: '{$p[2]}' -> '{$p[1]}' (class {$p[3]})\n";
    }
    echo "\nRe-run with --apply\n";
}
