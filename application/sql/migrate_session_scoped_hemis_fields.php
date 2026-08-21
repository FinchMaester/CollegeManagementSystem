<?php
/**
 * Phase A: session-scoped academic / HEMIS year fields.
 * - student_session.ugc_fiscal_year_id, semister_or_year_no
 * - sessions.ugc_fiscal_year_id (default FY for campus session)
 * - Backfill from students + map sessions → UGC FY
 *
 * Usage: php application/sql/migrate_session_scoped_hemis_fields.php [--apply]
 */
$apply = in_array('--apply', $argv ?? array(), true);
$root = dirname(__DIR__, 2);
if (is_file($root . '/.env')) {
    // optional
}

$m = new mysqli('127.0.0.1', 'root', 'root', 'a_final_college');
$m->set_charset('utf8mb4');
if ($m->connect_error) {
    fwrite(STDERR, $m->connect_error . PHP_EOL);
    exit(1);
}

function hasCol(mysqli $m, $table, $col)
{
    $r = $m->query("SHOW COLUMNS FROM `$table` LIKE '" . $m->real_escape_string($col) . "'");
    return $r && $r->num_rows > 0;
}

echo ($apply ? "APPLY\n" : "DRY-RUN\n");

$alters = array();
if (!hasCol($m, 'student_session', 'ugc_fiscal_year_id')) {
    $alters[] = "ALTER TABLE student_session ADD COLUMN ugc_fiscal_year_id INT NULL DEFAULT NULL AFTER program_id, ADD KEY idx_ss_ugc_fy (ugc_fiscal_year_id)";
}
if (!hasCol($m, 'student_session', 'semister_or_year_no')) {
    $alters[] = "ALTER TABLE student_session ADD COLUMN semister_or_year_no VARCHAR(100) NULL DEFAULT NULL AFTER ugc_fiscal_year_id";
}
if (!hasCol($m, 'sessions', 'ugc_fiscal_year_id')) {
    $alters[] = "ALTER TABLE sessions ADD COLUMN ugc_fiscal_year_id INT NULL DEFAULT NULL AFTER session, ADD KEY idx_sessions_ugc_fy (ugc_fiscal_year_id)";
}

echo "Schema changes: " . (count($alters) ? implode("\n  ", $alters) : "(none, already present)") . "\n";

if ($apply) {
    foreach ($alters as $sql) {
        if (!$m->query($sql)) {
            fwrite(STDERR, "ALTER failed: {$m->error}\n$sql\n");
            exit(1);
        }
        echo "OK: $sql\n";
    }
}

// Map sessions.session label → ugc_fiscal_years
// AD like 2025-26 → english 2025/026 or nepali start+57
// BS like 2082-83 → nepali 2082/083
function mapSessionToFyId(mysqli $m, $sessionLabel)
{
    $sessionLabel = trim((string) $sessionLabel);
    if ($sessionLabel === '' || !preg_match('/^(\d{4})\s*[-\/]/', $sessionLabel, $mm)) {
        return null;
    }
    $y1 = (int) $mm[1];
    $bsStart = ($y1 >= 2070 && $y1 <= 2100) ? $y1 : ($y1 + 57);
    $adStart = ($y1 >= 2070 && $y1 <= 2100) ? ($y1 - 57) : $y1;

    // Prefer exact nepali year start match
    $q = $m->prepare("SELECT id FROM ugc_fiscal_years WHERE year_nepali LIKE CONCAT(?, '%') ORDER BY active_fiscal_year DESC, id DESC LIMIT 1");
    $like = (string) $bsStart;
    $q->bind_param('s', $like);
    $q->execute();
    $res = $q->get_result()->fetch_assoc();
    $q->close();
    if ($res) {
        return (int) $res['id'];
    }

    $q = $m->prepare("SELECT id FROM ugc_fiscal_years WHERE year_english LIKE CONCAT(?, '%') ORDER BY active_fiscal_year DESC, id DESC LIMIT 1");
    $likeAd = (string) $adStart;
    $q->bind_param('s', $likeAd);
    $q->execute();
    $res = $q->get_result()->fetch_assoc();
    $q->close();
    return $res ? (int) $res['id'] : null;
}

echo "\nSession → UGC FY map:\n";
$sessionMap = array();
$q = $m->query("SELECT id, session FROM sessions ORDER BY id");
while ($r = $q->fetch_assoc()) {
    $fy = mapSessionToFyId($m, $r['session']);
    $sessionMap[(int) $r['id']] = $fy;
    echo "  session {$r['id']} {$r['session']} => fy " . ($fy ?: 'NULL') . "\n";
}

if ($apply && hasCol($m, 'sessions', 'ugc_fiscal_year_id')) {
    foreach ($sessionMap as $sid => $fy) {
        if ($fy) {
            $m->query("UPDATE sessions SET ugc_fiscal_year_id=" . (int) $fy . " WHERE id=" . (int) $sid);
        }
    }
    echo "Updated sessions.ugc_fiscal_year_id\n";
}

// Backfill student_session from students (prefer student values), else session default FY
$needBackfillFy = 0;
$needBackfillSy = 0;
if (hasCol($m, 'student_session', 'ugc_fiscal_year_id')) {
    $needBackfillFy = (int) $m->query("SELECT COUNT(*) c FROM student_session WHERE ugc_fiscal_year_id IS NULL")->fetch_assoc()['c'];
} else {
    $needBackfillFy = (int) $m->query("SELECT COUNT(*) c FROM student_session")->fetch_assoc()['c'];
}
if (hasCol($m, 'student_session', 'semister_or_year_no')) {
    $needBackfillSy = (int) $m->query("SELECT COUNT(*) c FROM student_session ss INNER JOIN students s ON s.id=ss.student_id
      WHERE (ss.semister_or_year_no IS NULL OR ss.semister_or_year_no='') AND s.semister_or_year_no IS NOT NULL AND s.semister_or_year_no<>''")->fetch_assoc()['c'];
}
echo "\nBackfill needed: fy_null_rows=$needBackfillFy semister_from_student=$needBackfillSy\n";

if ($apply && hasCol($m, 'student_session', 'ugc_fiscal_year_id')) {
    // Prefer campus session → UGC FY map (correct per academic year history)
    if (hasCol($m, 'sessions', 'ugc_fiscal_year_id')) {
        $m->query("UPDATE student_session ss
            INNER JOIN sessions sess ON sess.id = ss.session_id
            SET ss.ugc_fiscal_year_id = sess.ugc_fiscal_year_id
            WHERE sess.ugc_fiscal_year_id IS NOT NULL AND sess.ugc_fiscal_year_id <> 0");
        echo "FY from sessions map: {$m->affected_rows}\n";
    }
    // Fill any remaining from students.ugc_fiscal_year_id
    $m->query("UPDATE student_session ss
        INNER JOIN students s ON s.id = ss.student_id
        SET ss.ugc_fiscal_year_id = s.ugc_fiscal_year_id
        WHERE ss.ugc_fiscal_year_id IS NULL
          AND s.ugc_fiscal_year_id IS NOT NULL AND s.ugc_fiscal_year_id <> 0");
    echo "FY from students (remaining): {$m->affected_rows}\n";

    if (hasCol($m, 'student_session', 'semister_or_year_no')) {
        $m->query("UPDATE student_session ss
            INNER JOIN students s ON s.id = ss.student_id
            SET ss.semister_or_year_no = s.semister_or_year_no
            WHERE (ss.semister_or_year_no IS NULL OR ss.semister_or_year_no = '')
              AND s.semister_or_year_no IS NOT NULL AND s.semister_or_year_no <> ''");
        echo "semister from students: {$m->affected_rows}\n";
    }
}

echo "\n=== VERIFY ===\n";
if (hasCol($m, 'student_session', 'ugc_fiscal_year_id')) {
    echo json_encode($m->query("SELECT
      COUNT(*) total,
      SUM(ugc_fiscal_year_id IS NOT NULL) has_fy,
      SUM(semister_or_year_no IS NOT NULL AND semister_or_year_no<>'') has_semister
    FROM student_session")->fetch_assoc()) . "\n";
}
if (hasCol($m, 'sessions', 'ugc_fiscal_year_id')) {
    $q = $m->query("SELECT id, session, ugc_fiscal_year_id FROM sessions ORDER BY id");
    while ($r = $q->fetch_assoc()) {
        echo json_encode($r) . "\n";
    }
}

if (!$apply) {
    echo "\nRe-run with --apply to execute.\n";
}
