<?php
// ============================================================
//  LUMINA DB — SQLite schema + helpers
// ============================================================
require_once __DIR__ . '/config.php';

// Default: Desktop/Lumina/lumina.db (local). Override via LUMINA_DB_PATH in .env (VPS).
define('LUMINA_DB', _env('LUMINA_DB_PATH', __DIR__ . '/lumina.db'));

function lumina_db(): SQLite3 {
    static $db = null;
    if ($db) return $db;

    $db = new SQLite3(LUMINA_DB);
    $db->enableExceptions(true);
    $db->exec('PRAGMA journal_mode=WAL');
    $db->exec('PRAGMA foreign_keys=ON');

    $db->exec("CREATE TABLE IF NOT EXISTS tenants (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT UNIQUE NOT NULL,
        name TEXT NOT NULL,
        logo_url TEXT,
        primary_color TEXT DEFAULT '#6C47FF',
        config_json TEXT DEFAULT '{}',
        created_at INTEGER DEFAULT (strftime('%s','now'))
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tenant_id INTEGER NOT NULL,
        email TEXT NOT NULL,
        password_hash TEXT NOT NULL,
        name TEXT NOT NULL,
        role TEXT DEFAULT 'member',
        created_at INTEGER DEFAULT (strftime('%s','now')),
        UNIQUE(tenant_id, email),
        FOREIGN KEY(tenant_id) REFERENCES tenants(id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS sessions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        token TEXT UNIQUE NOT NULL,
        expires_at INTEGER NOT NULL,
        FOREIGN KEY(user_id) REFERENCES users(id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS assessments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        type TEXT NOT NULL CHECK(type IN ('quick','deep')),
        status TEXT DEFAULT 'pending' CHECK(status IN ('pending','completed')),
        created_at INTEGER DEFAULT (strftime('%s','now')),
        completed_at INTEGER,
        FOREIGN KEY(user_id) REFERENCES users(id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS answers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        assessment_id INTEGER NOT NULL,
        question_key TEXT NOT NULL,
        answer_value TEXT NOT NULL,
        FOREIGN KEY(assessment_id) REFERENCES assessments(id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS results (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        assessment_id INTEGER UNIQUE NOT NULL,
        archetype TEXT,
        archetype_desc TEXT,
        strengths_json TEXT,
        blind_spots_json TEXT,
        motivation_drivers_json TEXT,
        life_wheel_json TEXT,
        internal_conflict TEXT,
        growth_direction TEXT,
        ai_raw_json TEXT,
        created_at INTEGER DEFAULT (strftime('%s','now')),
        FOREIGN KEY(assessment_id) REFERENCES assessments(id)
    )");

    // Migration: add status column to users (existing users → approved)
    try {
        $db->exec("ALTER TABLE users ADD COLUMN status TEXT NOT NULL DEFAULT 'approved'");
    } catch (Exception $e) { /* column already exists */ }

    // Migration: add notes column to users
    try {
        $db->exec("ALTER TABLE users ADD COLUMN admin_note TEXT DEFAULT ''");
    } catch (Exception $e) { /* column already exists */ }

    // Migration: add phone column to users
    try {
        $db->exec("ALTER TABLE users ADD COLUMN phone TEXT DEFAULT ''");
    } catch (Exception $e) { /* column already exists */ }

    // Migration: add org column to users
    try {
        $db->exec("ALTER TABLE users ADD COLUMN org TEXT DEFAULT ''");
    } catch (Exception $e) { /* column already exists */ }

    // Migration: add phone column to access_requests
    try {
        $db->exec("ALTER TABLE access_requests ADD COLUMN phone TEXT DEFAULT ''");
    } catch (Exception $e) { /* column already exists */ }

    // Access requests from main domain (invite-only leads)
    $db->exec("CREATE TABLE IF NOT EXISTS access_requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        organization TEXT DEFAULT '',
        message TEXT DEFAULT '',
        status TEXT DEFAULT 'new',
        created_at INTEGER DEFAULT (strftime('%s','now'))
    )");

    // Seed demo tenant
    $check = $db->querySingle("SELECT id FROM tenants WHERE slug='demo'");
    if (!$check) {
        $db->exec("INSERT INTO tenants(slug,name,primary_color) VALUES('demo','Lumina Demo','#6C47FF')");
    }

    return $db;
}

function db_row(string $sql, array $params = []): ?array {
    $db = lumina_db();
    $stmt = $db->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    return $row ?: null;
}

function db_rows(string $sql, array $params = []): array {
    $db = lumina_db();
    $stmt = $db->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $result = $stmt->execute();
    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) $rows[] = $row;
    return $rows;
}

function db_exec(string $sql, array $params = []): int {
    $db = lumina_db();
    $stmt = $db->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->execute();
    return $db->lastInsertRowID();
}
