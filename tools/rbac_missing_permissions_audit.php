<?php
/**
 * Audit + SQL generator: find RBAC permission_category short_codes used in code
 * (via $this->rbac->hasPrivilege('short_code','can_view|can_add|...')) that are missing from DB.
 *
 * Outputs:
 * - Missing short_codes
 * - A best-effort INSERT SQL for permission_category rows (matching table columns)
 * - Optional SQL to grant role_id=7 (Super Admin) full rights on newly inserted categories
 *
 * Usage:
 *   php tools/rbac_missing_permissions_audit.php
 *   php tools/rbac_missing_permissions_audit.php --grant-superadmin=1
 *   php tools/rbac_missing_permissions_audit.php --paths=application/controllers,application/views,application/helpers,application/libraries
 */
declare(strict_types=1);

function arg(string $name, ?string $default = null): ?string
{
    foreach ($_SERVER['argv'] as $a) {
        if (str_starts_with($a, "--{$name}=")) {
            return substr($a, strlen("--{$name}="));
        }
    }
    return $default;
}

function stdout(string $s): void
{
    fwrite(STDOUT, $s);
}

function stderr(string $s): void
{
    fwrite(STDERR, $s);
}

function normalize_short_code(string $s): string
{
    $s = trim($s);
    $s = strtolower($s);
    $s = preg_replace('/\s+/', '_', $s) ?? $s;
    $s = preg_replace('/[^a-z0-9_]/', '', $s) ?? $s;
    return $s;
}

/**
 * @return array{0:array<string,true>,1:array<string,array<string,true>>}
 */
function scan_hasPrivilege_categories(array $paths): array
{
    $cats = [];
    $catToPerms = [];

    $re = "/hasPrivilege\\(\\s*'([^']+)'\\s*,\\s*'([^']+)'\\s*\\)/";

    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(getcwd(), FilesystemIterator::SKIP_DOTS));
    foreach ($rii as $file) {
        /** @var SplFileInfo $file */
        if (!$file->isFile()) {
            continue;
        }
        $rel = str_replace('\\', '/', substr($file->getPathname(), strlen(getcwd()) + 1));

        $allow = false;
        foreach ($paths as $p) {
            $p = trim(str_replace('\\', '/', $p), '/');
            if ($p === '') {
                continue;
            }
            if (str_starts_with($rel, $p . '/')) {
                $allow = true;
                break;
            }
        }
        if (!$allow) {
            continue;
        }
        if (!str_ends_with(strtolower($rel), '.php')) {
            continue;
        }

        $content = @file_get_contents($file->getPathname());
        if ($content === false || $content === '') {
            continue;
        }
        if (!preg_match_all($re, $content, $m, PREG_SET_ORDER)) {
            continue;
        }
        foreach ($m as $match) {
            $cat = normalize_short_code((string) $match[1]);
            $perm = trim((string) $match[2]);
            if ($cat === '' || $perm === '') {
                continue;
            }
            $cats[$cat] = true;
            if (!isset($catToPerms[$cat])) {
                $catToPerms[$cat] = [];
            }
            $catToPerms[$cat][$perm] = true;
        }
    }

    return [$cats, $catToPerms];
}

/**
 * @return array<string,true>
 */
function db_existing_permission_category_short_codes(mysqli $db): array
{
    $existing = [];
    $res = $db->query("SELECT short_code FROM permission_category");
    if (!$res) {
        throw new RuntimeException("DB query failed: " . $db->error);
    }
    while ($r = $res->fetch_assoc()) {
        $sc = normalize_short_code((string) ($r['short_code'] ?? ''));
        if ($sc !== '') {
            $existing[$sc] = true;
        }
    }
    return $existing;
}

/**
 * @return array<string,array{Type:string,Null:string,Default:mixed,Extra:string}>
 */
function db_table_columns(mysqli $db, string $table): array
{
    $cols = [];
    $res = $db->query("SHOW COLUMNS FROM `" . $db->real_escape_string($table) . "`");
    if (!$res) {
        throw new RuntimeException("SHOW COLUMNS failed for {$table}: " . $db->error);
    }
    while ($r = $res->fetch_assoc()) {
        $field = (string) ($r['Field'] ?? '');
        if ($field === '') {
            continue;
        }
        $cols[$field] = [
            'Type' => (string) ($r['Type'] ?? ''),
            'Null' => (string) ($r['Null'] ?? ''),
            'Default' => $r['Default'] ?? null,
            'Extra' => (string) ($r['Extra'] ?? ''),
        ];
    }
    return $cols;
}

/**
 * Choose a permission_group id to attach new categories to.
 * Prefers Library group when available; otherwise lowest id.
 */
function pick_permission_group_id(mysqli $db): ?int
{
    $res = $db->query("SELECT id, name, short_code FROM permission_group ORDER BY id ASC");
    if (!$res) {
        return null;
    }
    $first = null;
    $library = null;
    while ($r = $res->fetch_assoc()) {
        $id = (int) ($r['id'] ?? 0);
        if ($id < 1) {
            continue;
        }
        if ($first === null) {
            $first = $id;
        }
        $name = strtolower((string) ($r['name'] ?? ''));
        $sc = strtolower((string) ($r['short_code'] ?? ''));
        if ($library === null && (str_contains($name, 'library') || $sc === 'library')) {
            $library = $id;
        }
    }
    return $library ?? $first;
}

function sql_quote(mysqli $db, ?string $v): string
{
    if ($v === null) {
        return "NULL";
    }
    return "'" . $db->real_escape_string($v) . "'";
}

function main(): int
{
    $pathsRaw = (string) arg('paths', 'application/controllers,application/views,application/helpers,application/libraries');
    $paths = array_values(array_filter(array_map('trim', explode(',', $pathsRaw))));
    $grantSuperadmin = ((string) arg('grant-superadmin', '0')) === '1';

    // DB connection: prefer CodeIgniter config defaults via env vars (same as application/config/database.php)
    $host = getenv('DB_HOSTNAME') ?: 'localhost';
    $user = getenv('DB_USERNAME') ?: 'root';
    $pass = getenv('DB_PASSWORD') ?: 'root';
    $name = getenv('DB_DATABASE') ?: 'a_final_college';

    $db = new mysqli($host, $user, $pass, $name);
    if ($db->connect_error) {
        stderr("DB connect failed: " . $db->connect_error . PHP_EOL);
        return 2;
    }
    $db->set_charset('utf8mb4');

    [$catsUsed, $catToPerms] = scan_hasPrivilege_categories($paths);
    $existing = db_existing_permission_category_short_codes($db);

    $missing = [];
    foreach ($catsUsed as $cat => $_) {
        if (!isset($existing[$cat])) {
            $missing[$cat] = true;
        }
    }

    stdout("scan_paths=" . implode(',', $paths) . PHP_EOL);
    stdout("used_categories=" . count($catsUsed) . PHP_EOL);
    stdout("db_categories=" . count($existing) . PHP_EOL);
    stdout("missing_categories=" . count($missing) . PHP_EOL);

    if (empty($missing)) {
        stdout("OK: no missing permission_category short_codes found.\n");
        return 0;
    }

    // Determine column set for permission_category inserts.
    $pcCols = db_table_columns($db, 'permission_category');
    $hasPermGroup = array_key_exists('perm_group_id', $pcCols);
    $hasEnableView = array_key_exists('enable_view', $pcCols);
    $hasEnableAdd = array_key_exists('enable_add', $pcCols);
    $hasEnableEdit = array_key_exists('enable_edit', $pcCols);
    $hasEnableDelete = array_key_exists('enable_delete', $pcCols);

    $permGroupId = $hasPermGroup ? pick_permission_group_id($db) : null;

    stdout("\n-- Missing short_codes (copy/paste)\n");
    foreach (array_keys($missing) as $cat) {
        $perms = isset($catToPerms[$cat]) ? implode(',', array_keys($catToPerms[$cat])) : '';
        stdout("- {$cat}" . ($perms !== '' ? " (used perms: {$perms})" : "") . PHP_EOL);
    }

    stdout("\n-- SQL: insert missing permission_category rows (idempotent)\n");
    stdout("START TRANSACTION;\n");

    foreach (array_keys($missing) as $cat) {
        // Best-effort human name
        $nameHuman = ucwords(str_replace('_', ' ', $cat));

        $cols = ['name', 'short_code'];
        $vals = [sql_quote($db, $nameHuman), sql_quote($db, $cat)];

        if ($hasPermGroup && $permGroupId !== null) {
            $cols[] = 'perm_group_id';
            $vals[] = (string) (int) $permGroupId;
        }

        if ($hasEnableView) {
            $cols[] = 'enable_view';
            $vals[] = '1';
        }
        if ($hasEnableAdd) {
            $cols[] = 'enable_add';
            $vals[] = '1';
        }
        if ($hasEnableEdit) {
            $cols[] = 'enable_edit';
            $vals[] = '1';
        }
        if ($hasEnableDelete) {
            $cols[] = 'enable_delete';
            $vals[] = '1';
        }

        $colList = implode(',', array_map(fn($c) => "`{$c}`", $cols));
        $valList = implode(',', $vals);

        stdout("INSERT INTO `permission_category` ({$colList})\n");
        stdout("SELECT {$valList}\n");
        stdout("WHERE NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code`=" . sql_quote($db, $cat) . " LIMIT 1);\n\n");
    }

    if ($grantSuperadmin) {
        stdout("\n-- SQL: grant role_id=7 full rights on newly added categories\n");
        foreach (array_keys($missing) as $cat) {
            stdout("INSERT INTO `roles_permissions` (`role_id`,`perm_cat_id`,`can_view`,`can_add`,`can_edit`,`can_delete`)\n");
            stdout("SELECT 7, pc.id, 1, 1, 1, 1 FROM `permission_category` pc\n");
            stdout("WHERE pc.`short_code`=" . sql_quote($db, $cat) . "\n");
            stdout("  AND NOT EXISTS (\n");
            stdout("    SELECT 1 FROM `roles_permissions` rp WHERE rp.`role_id`=7 AND rp.`perm_cat_id`=pc.id LIMIT 1\n");
            stdout("  );\n\n");
        }
    }

    stdout("COMMIT;\n");

    stdout("\n-- Notes\n");
    stdout("-- 1) After running SQL, reload /admin/roles/permission/{roleId}\n");
    stdout("-- 2) If you don't see changes, log out/in to clear the RBAC session cache.\n");

    return 0;
}

try {
    exit(main());
} catch (Throwable $e) {
    stderr("ERROR: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

