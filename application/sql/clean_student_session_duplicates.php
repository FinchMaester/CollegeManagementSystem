<?php
/**
 * Clean a_final_college student_session data:
 * 1) Remove orphan enrollments (no matching student) + dependent rows
 * 2) Deduplicate (student_id, session_id) keeping best row; remap dependents
 * 3) Sync students.class_id/section_id/program_id from active placement
 *
 * Dry-run: php tmp_clean_student_session.php
 * Apply:   php tmp_clean_student_session.php --apply
 */
$apply = in_array('--apply', $argv, true);

$m = new mysqli('127.0.0.1', 'root', 'root', 'a_final_college');
$m->set_charset('utf8mb4');
if ($m->connect_error) {
    fwrite(STDERR, $m->connect_error . PHP_EOL);
    exit(1);
}

$childTables = [
    'daily_assignment',
    'exam_group_class_batch_exam_students',
    'exam_group_students',
    'homework_evaluation',
    'offline_fees_payments',
    'onlineexam_students',
    'student_applyleave',
    'student_attendences',
    'student_fees',
    'student_fees_discounts',
    'student_fees_master',
    'student_subject_attendances',
    'student_transport_fees',
    'visitors_book',
];

function existingChildTables(mysqli $m, array $tables): array
{
    $out = [];
    foreach ($tables as $t) {
        $q = $m->query("SHOW TABLES LIKE '" . $m->real_escape_string($t) . "'");
        if ($q && $q->num_rows) {
            $c = $m->query("SHOW COLUMNS FROM `$t` LIKE 'student_session_id'");
            if ($c && $c->num_rows) {
                $out[] = $t;
            }
        }
    }
    return $out;
}

function countOnIds(mysqli $m, string $table, array $ids): int
{
    if (!$ids) {
        return 0;
    }
    $in = implode(',', array_map('intval', $ids));
    $r = $m->query("SELECT COUNT(*) c FROM `$table` WHERE student_session_id IN ($in)");
    return (int) $r->fetch_assoc()['c'];
}

function deleteOnIds(mysqli $m, string $table, array $ids): int
{
    if (!$ids) {
        return 0;
    }
    $in = implode(',', array_map('intval', $ids));
    $m->query("DELETE FROM `$table` WHERE student_session_id IN ($in)");
    return $m->affected_rows;
}

function remapIds(mysqli $m, string $table, int $from, int $to): int
{
    // Avoid duplicate fee-group assignments when remapping masters
    if ($table === 'student_fees_master') {
        $existing = [];
        $q = $m->query("SELECT fee_session_group_id FROM student_fees_master WHERE student_session_id=$to");
        while ($r = $q->fetch_assoc()) {
            $existing[(int) $r['fee_session_group_id']] = true;
        }
        $moved = 0;
        $q = $m->query("SELECT id, fee_session_group_id FROM student_fees_master WHERE student_session_id=$from");
        while ($r = $q->fetch_assoc()) {
            $gid = (int) $r['fee_session_group_id'];
            $fid = (int) $r['id'];
            if (isset($existing[$gid])) {
                // Drop duplicate assignment on loser (no unique payments assumed if amount 0 / same group)
                // First move any student_fees pointing at this master? student_fees uses student_session_id mostly.
                $m->query("DELETE FROM student_fees_master WHERE id=$fid");
            } else {
                $m->query("UPDATE student_fees_master SET student_session_id=$to WHERE id=$fid");
                $existing[$gid] = true;
                $moved++;
            }
        }
        return $moved;
    }

    if ($table === 'student_fees_discounts') {
        $existing = [];
        $q = $m->query("SELECT fees_discount_id FROM student_fees_discounts WHERE student_session_id=$to");
        while ($r = $q->fetch_assoc()) {
            $existing[(int) $r['fees_discount_id']] = true;
        }
        $moved = 0;
        $q = $m->query("SELECT id, fees_discount_id FROM student_fees_discounts WHERE student_session_id=$from");
        while ($r = $q->fetch_assoc()) {
            $did = (int) $r['fees_discount_id'];
            $fid = (int) $r['id'];
            if (isset($existing[$did])) {
                $m->query("DELETE FROM student_fees_discounts WHERE id=$fid");
            } else {
                $m->query("UPDATE student_fees_discounts SET student_session_id=$to WHERE id=$fid");
                $existing[$did] = true;
                $moved++;
            }
        }
        return $moved;
    }

    $m->query("UPDATE `$table` SET student_session_id=$to WHERE student_session_id=$from");
    return $m->affected_rows;
}

function chooseWinner(array $rows): array
{
    usort($rows, function ($a, $b) {
        // active first
        $aa = (($a['is_active'] ?? '') === 'yes') ? 1 : 0;
        $ba = (($b['is_active'] ?? '') === 'yes') ? 1 : 0;
        if ($aa !== $ba) {
            return $ba - $aa;
        }
        // more fees
        if ((int) $a['fee_cnt'] !== (int) $b['fee_cnt']) {
            return (int) $b['fee_cnt'] - (int) $a['fee_cnt'];
        }
        // higher class (later year)
        if ((int) $a['class_id'] !== (int) $b['class_id']) {
            return (int) $b['class_id'] - (int) $a['class_id'];
        }
        // newest id
        return (int) $b['id'] - (int) $a['id'];
    });
    return $rows[0];
}

$children = existingChildTables($m, $childTables);
echo ($apply ? "APPLY MODE\n" : "DRY-RUN (pass --apply to write)\n");
echo "Child tables: " . implode(', ', $children) . "\n\n";

// ---------- 1) ORPHANS ----------
$orphanIds = [];
$q = $m->query("SELECT ss.id FROM student_session ss LEFT JOIN students s ON s.id=ss.student_id WHERE s.id IS NULL");
while ($r = $q->fetch_assoc()) {
    $orphanIds[] = (int) $r['id'];
}
echo "=== ORPHANS ===\n";
echo "orphan student_session rows: " . count($orphanIds) . "\n";
$orphanChildCounts = [];
foreach ($children as $t) {
    $c = countOnIds($m, $t, $orphanIds);
    if ($c > 0) {
        $orphanChildCounts[$t] = $c;
        echo "  will delete $t: $c\n";
    }
}

// ---------- 2) DUPLICATES ----------
echo "\n=== DUPLICATES ===\n";
$dupGroups = [];
$q = $m->query("
SELECT student_id, session_id, GROUP_CONCAT(id ORDER BY id) ids, COUNT(*) cnt
FROM student_session
WHERE student_id > 0
GROUP BY student_id, session_id
HAVING COUNT(*) > 1
ORDER BY student_id, session_id
");
while ($r = $q->fetch_assoc()) {
    $dupGroups[] = $r;
}
echo "duplicate groups: " . count($dupGroups) . "\n";

$plan = []; // keep => [drop...]
foreach ($dupGroups as $g) {
    $ids = array_map('intval', explode(',', $g['ids']));
    $rows = [];
    foreach ($ids as $id) {
        $row = $m->query("SELECT id, student_id, session_id, class_id, section_id, program_id, is_active FROM student_session WHERE id=$id")->fetch_assoc();
        $fee = $m->query("SELECT COUNT(*) c FROM student_fees_master WHERE student_session_id=$id")->fetch_assoc();
        $row['fee_cnt'] = (int) $fee['c'];
        $rows[] = $row;
    }
    $winner = chooseWinner($rows);
    $losers = [];
    foreach ($rows as $row) {
        if ((int) $row['id'] !== (int) $winner['id']) {
            $losers[] = (int) $row['id'];
        }
    }
    $plan[] = [
        'student_id' => (int) $g['student_id'],
        'session_id' => (int) $g['session_id'],
        'keep' => (int) $winner['id'],
        'keep_class' => (int) $winner['class_id'],
        'keep_active' => $winner['is_active'],
        'drop' => $losers,
    ];
}
echo "will drop extra rows: " . array_sum(array_map(function ($p) { return count($p['drop']); }, $plan)) . "\n";
echo "sample plans:\n";
foreach (array_slice($plan, 0, 5) as $p) {
    echo json_encode($p) . "\n";
}

// also student_id=0 duplicates treated as orphans-ish
$zeroIds = [];
$q = $m->query("SELECT id FROM student_session WHERE student_id=0 OR student_id IS NULL");
while ($r = $q->fetch_assoc()) {
    $zeroIds[] = (int) $r['id'];
}
// zeros already in orphans if no student 0; still list
echo "\nstudent_id zero/null rows: " . count($zeroIds) . "\n";

if (!$apply) {
    echo "\nDry-run complete. Re-run with --apply to execute.\n";
    exit(0);
}

// ---------- APPLY ----------
$m->begin_transaction();
try {
    // Ensure keep row is active when we choose it
    foreach ($plan as $p) {
        $m->query("UPDATE student_session SET is_active='yes' WHERE id=" . (int) $p['keep']);
    }

    // Remap then delete losers
    $remapped = [];
    $droppedSs = 0;
    foreach ($plan as $p) {
        $keep = (int) $p['keep'];
        foreach ($p['drop'] as $drop) {
            foreach ($children as $t) {
                $n = remapIds($m, $t, (int) $drop, $keep);
                if ($n > 0) {
                    $remapped[$t] = ($remapped[$t] ?? 0) + $n;
                }
            }
            $m->query("DELETE FROM student_session WHERE id=" . (int) $drop);
            $droppedSs += $m->affected_rows;
        }
    }
    echo "\nRemapped:\n";
    foreach ($remapped as $t => $n) {
        echo "  $t: $n\n";
    }
    echo "Dropped duplicate student_session: $droppedSs\n";

    // Delete orphan dependents + rows
    $deletedChildren = [];
    foreach ($children as $t) {
        $n = deleteOnIds($m, $t, $orphanIds);
        if ($n > 0) {
            $deletedChildren[$t] = $n;
        }
    }
    // Delete orphan/zero student_session rows by primary key
    $allDeleteSs = array_values(array_unique(array_merge($orphanIds, $zeroIds)));
    if ($allDeleteSs) {
        $in = implode(',', array_map('intval', $allDeleteSs));
        $m->query("DELETE FROM student_session WHERE id IN ($in)");
        $orphanDeleted = $m->affected_rows;
    } else {
        $orphanDeleted = 0;
    }
    echo "\nOrphan/zero child deletes:\n";
    foreach ($deletedChildren as $t => $c) {
        echo "  $t: $c\n";
    }
    echo "Deleted orphan/zero student_session: $orphanDeleted\n";

    // Sync students placement from preferred active session row (active sch session if present else latest)
    $settings = $m->query("SELECT session_id FROM sch_settings LIMIT 1")->fetch_assoc();
    $activeSession = (int) ($settings['session_id'] ?? 0);
    $updatedStudents = 0;
    $q = $m->query("SELECT id FROM students");
    while ($st = $q->fetch_assoc()) {
        $sid = (int) $st['id'];
        $ss = null;
        if ($activeSession > 0) {
            $ss = $m->query("SELECT class_id, section_id, program_id FROM student_session
                WHERE student_id=$sid AND session_id=$activeSession
                ORDER BY (is_active='yes') DESC, id DESC LIMIT 1")->fetch_assoc();
        }
        if (!$ss) {
            $ss = $m->query("SELECT class_id, section_id, program_id FROM student_session
                WHERE student_id=$sid
                ORDER BY (is_active='yes') DESC, id DESC LIMIT 1")->fetch_assoc();
        }
        if (!$ss) {
            continue;
        }
        $m->query("UPDATE students SET
            class_id=" . (int) $ss['class_id'] . ",
            section_id=" . (int) $ss['section_id'] . ",
            program_id=" . (int) ($ss['program_id'] ?: 0) . "
            WHERE id=$sid");
        if ($m->affected_rows >= 0) {
            // affected_rows can be 0 if same values
            $updatedStudents++;
        }
    }
    echo "Synced students placement mirror for $updatedStudents students (attempted all with session).\n";

    $m->commit();
} catch (Throwable $e) {
    $m->rollback();
    fwrite(STDERR, "FAILED: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

// Verify
echo "\n=== VERIFY ===\n";
$orphans = $m->query("SELECT COUNT(*) c FROM student_session ss LEFT JOIN students s ON s.id=ss.student_id WHERE s.id IS NULL")->fetch_assoc()['c'];
$dups = $m->query("SELECT COUNT(*) c FROM (SELECT student_id, session_id FROM student_session GROUP BY student_id, session_id HAVING COUNT(*)>1) t")->fetch_assoc()['c'];
$total = $m->query("SELECT COUNT(*) c FROM student_session")->fetch_assoc()['c'];
$students = $m->query("SELECT COUNT(*) c FROM students")->fetch_assoc()['c'];
$mis = $m->query("
SELECT
  SUM(IFNULL(s.class_id,0) <> IFNULL(ss.class_id,0)) class_mismatch,
  SUM(IFNULL(s.section_id,0) <> IFNULL(ss.section_id,0)) section_mismatch,
  SUM(IFNULL(s.program_id,0) <> IFNULL(ss.program_id,0)) program_mismatch
FROM students s
INNER JOIN student_session ss ON ss.id = (
  SELECT ss2.id FROM student_session ss2
  WHERE ss2.student_id=s.id
  ORDER BY (ss2.session_id=" . (int) $activeSession . ") DESC, (ss2.is_active='yes') DESC, ss2.id DESC
  LIMIT 1
)")->fetch_assoc();
echo "orphans=$orphans dup_groups=$dups student_session=$total students=$students\n";
echo "mirror mismatches: " . json_encode($mis) . "\n";
echo "DONE\n";
