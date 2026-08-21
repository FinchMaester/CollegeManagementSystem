<?php
/**
 * Backfill students.ethnicity ↔ students.cast (HEMIS ethnicity is SoT).
 *
 * - Empty ethnicity + cast set  → ethnicity = cast
 * - Empty cast + ethnicity set → cast = ethnicity
 *
 * Dry-run: php application/sql/backfill_ethnicity_from_cast.php
 * Apply:   php application/sql/backfill_ethnicity_from_cast.php --apply
 */
$apply = in_array('--apply', $argv ?? [], true);

$m = new mysqli('127.0.0.1', 'root', 'root', 'a_final_college');
$m->set_charset('utf8mb4');
if ($m->connect_error) {
    fwrite(STDERR, $m->connect_error . PHP_EOL);
    exit(1);
}

$fromCast = (int) $m->query("
    SELECT COUNT(*) c FROM students
    WHERE (ethnicity IS NULL OR TRIM(ethnicity) = '')
      AND cast IS NOT NULL AND TRIM(cast) <> ''
")->fetch_assoc()['c'];

$fromEth = (int) $m->query("
    SELECT COUNT(*) c FROM students
    WHERE (cast IS NULL OR TRIM(cast) = '')
      AND ethnicity IS NOT NULL AND TRIM(ethnicity) <> ''
")->fetch_assoc()['c'];

echo "Would set ethnicity from cast: {$fromCast}\n";
echo "Would set cast from ethnicity: {$fromEth}\n";

if (!$apply) {
    echo "Dry-run only. Re-run with --apply to write.\n";
    exit(0);
}

$m->query("
    UPDATE students
    SET ethnicity = TRIM(cast)
    WHERE (ethnicity IS NULL OR TRIM(ethnicity) = '')
      AND cast IS NOT NULL AND TRIM(cast) <> ''
");
echo "ethnicity filled from cast: " . $m->affected_rows . "\n";

$m->query("
    UPDATE students
    SET cast = TRIM(ethnicity)
    WHERE (cast IS NULL OR TRIM(cast) = '')
      AND ethnicity IS NOT NULL AND TRIM(ethnicity) <> ''
");
echo "cast filled from ethnicity: " . $m->affected_rows . "\n";

$r = $m->query("
    SELECT
      SUM(ethnicity IS NULL OR TRIM(ethnicity)='') empty_eth,
      SUM(cast IS NULL OR TRIM(cast)='') empty_cast
    FROM students
")->fetch_assoc();
echo "After: empty ethnicity={$r['empty_eth']} empty cast={$r['empty_cast']}\n";
echo "Done.\n";
