<?php
/**
 * Repair student_session.is_active flags for the current campus session.
 *
 * Policy:
 * - Current session enrollment rows are active (is_active=yes).
 * - Older session rows for the same student become inactive (is_active=no).
 * - Students who only exist in older sessions are left unchanged.
 * - sessions.is_active is aligned to sch_settings.session_id.
 *
 * Usage:
 *   php application/sql/repair_student_session_is_active.php
 *   php application/sql/repair_student_session_is_active.php --apply
 */
$apply = in_array('--apply', $argv ?? [], true);

$m = new mysqli('127.0.0.1', 'root', 'root', 'a_final_college');
$m->set_charset('utf8mb4');
if ($m->connect_error) {
    fwrite(STDERR, $m->connect_error . PHP_EOL);
    exit(1);
}

$current = (int) ($m->query('SELECT session_id FROM sch_settings LIMIT 1')->fetch_assoc()['session_id'] ?? 0);
if ($current <= 0) {
    fwrite(STDERR, "No current session in sch_settings.\n");
    exit(1);
}

$sess = $m->query("SELECT id, session, is_active FROM sessions WHERE id={$current}")->fetch_assoc();
echo "Current session: {$current} ({$sess['session']}), sessions.is_active={$sess['is_active']}\n";

$m->query("UPDATE sessions SET is_active='no' WHERE is_active='yes' AND id <> {$current}");
$m->query("UPDATE sessions SET is_active='yes' WHERE id = {$current}");

$before = $m->query("
    SELECT
      SUM(session_id = {$current} AND is_active = 'yes') AS cur_yes,
      SUM(session_id = {$current} AND is_active = 'no') AS cur_no,
      SUM(session_id <> {$current} AND is_active = 'yes') AS other_yes
    FROM student_session
")->fetch_assoc();
echo "Before: cur_yes={$before['cur_yes']} cur_no={$before['cur_no']} other_yes={$before['other_yes']}\n";

$to_activate = (int) $m->query(
    "SELECT COUNT(*) c FROM student_session WHERE session_id = {$current} AND is_active = 'no'"
)->fetch_assoc()['c'];

$to_deactivate = (int) $m->query("
    SELECT COUNT(*) c
    FROM student_session ss
    INNER JOIN student_session cur
      ON cur.student_id = ss.student_id AND cur.session_id = {$current}
    WHERE ss.session_id <> {$current} AND ss.is_active = 'yes'
")->fetch_assoc()['c'];

$to_reactivate_legacy = (int) $m->query("
    SELECT COUNT(*) c
    FROM student_session ss
    INNER JOIN (
      SELECT student_id, MAX(session_id) AS max_sid
      FROM student_session
      GROUP BY student_id
    ) t ON t.student_id = ss.student_id AND t.max_sid = ss.session_id
    LEFT JOIN student_session cur ON cur.student_id = ss.student_id AND cur.session_id = {$current}
    WHERE cur.id IS NULL AND ss.is_active = 'no'
")->fetch_assoc()['c'];

echo "Would activate current-session rows: {$to_activate}\n";
echo "Would deactivate older-session rows (students in current): {$to_deactivate}\n";
echo "Would reactivate latest row for students not in current session: {$to_reactivate_legacy}\n";

if (!$apply) {
    echo "Dry-run only. Re-run with --apply to write.\n";
    exit(0);
}

$m->begin_transaction();
$ok1 = $m->query("UPDATE student_session SET is_active = 'yes' WHERE session_id = {$current}");
$ok2 = $m->query("
    UPDATE student_session ss
    INNER JOIN student_session cur
      ON cur.student_id = ss.student_id AND cur.session_id = {$current}
    SET ss.is_active = 'no'
    WHERE ss.session_id <> {$current}
");
$ok3 = $m->query("
    UPDATE student_session ss
    INNER JOIN (
      SELECT student_id, MAX(session_id) AS max_sid
      FROM student_session
      GROUP BY student_id
    ) t ON t.student_id = ss.student_id AND t.max_sid = ss.session_id
    LEFT JOIN student_session cur ON cur.student_id = ss.student_id AND cur.session_id = {$current}
    SET ss.is_active = 'yes'
    WHERE cur.id IS NULL AND ss.is_active = 'no'
");
if (!$ok1 || !$ok2 || !$ok3) {
    $m->rollback();
    fwrite(STDERR, "Update failed: " . $m->error . PHP_EOL);
    exit(1);
}
$m->commit();

$after = $m->query("
    SELECT
      SUM(session_id = {$current} AND is_active = 'yes') AS cur_yes,
      SUM(session_id = {$current} AND is_active = 'no') AS cur_no,
      SUM(session_id <> {$current} AND is_active = 'yes') AS other_yes
    FROM student_session
")->fetch_assoc();
echo "After: cur_yes={$after['cur_yes']} cur_no={$after['cur_no']} other_yes={$after['other_yes']}\n";
echo "Done.\n";
