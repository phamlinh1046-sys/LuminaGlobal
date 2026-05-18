<?php
// ============================================================
//  TENANT — detect from subdomain, load context
// ============================================================
require_once __DIR__ . '/../db.php';

function detect_tenant(): ?array {
    $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
    // Strip port if any
    $host = explode(':', $host)[0];

    // Match subdomain.luminaglobal.info.vn or subdomain.localhost
    if (preg_match('/^([a-z0-9-]+)\.' . preg_quote(LUMINA_BASE_DOMAIN, '/') . '$/', $host, $m)) {
        $slug = $m[1];
        if (in_array($slug, ['www', 'lumina'])) return null;
        return db_row('SELECT * FROM tenants WHERE slug=:s', [':s' => $slug]);
    }
    // Local dev: subdomain.lumina.test or subdomain.localhost
    if (preg_match('/^([a-z0-9-]+)\.(lumina\.test|localhost)$/', $host, $m)) {
        $slug = $m[1];
        return db_row('SELECT * FROM tenants WHERE slug=:s', [':s' => $slug]);
    }
    return null;
}

function tenant_url(string $slug, string $path = '/'): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return "$scheme://$slug." . LUMINA_BASE_DOMAIN . $path;
}

function create_tenant(string $slug, string $name, string $color = '#6C47FF'): int {
    return db_exec(
        'INSERT INTO tenants(slug,name,primary_color) VALUES(:s,:n,:c)',
        [':s' => $slug, ':n' => $name, ':c' => $color]
    );
}
