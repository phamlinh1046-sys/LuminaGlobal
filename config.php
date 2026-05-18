<?php
// ============================================================
//  LUMINA CONFIG — load .env then expose constants
// ============================================================
$_envFile = __DIR__ . '/.env';
if (file_exists($_envFile)) {
    foreach (file($_envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_line) {
        $_line = trim($_line);
        if ($_line === '' || $_line[0] === '#') continue;
        if (strpos($_line, '=') !== false) {
            [$_k, $_v] = explode('=', $_line, 2);
            $_k = trim($_k); $_v = trim($_v);
            putenv("$_k=$_v");
            $_ENV[$_k] = $_v;
        }
    }
}
unset($_envFile, $_line, $_k, $_v);

function _env(string $key, string $default = ''): string {
    $v = getenv($key);
    return ($v !== false) ? $v : ($_ENV[$key] ?? $default);
}

define('RESEND_API_KEY',      _env('RESEND_API_KEY'));
define('FROM_EMAIL',          _env('FROM_EMAIL', 'Lumina <hello@luminaglobal.info.vn>'));
define('LUMINA_BASE_DOMAIN',  _env('LUMINA_BASE_DOMAIN', 'luminaglobal.info.vn'));
define('ADMIN_USERNAME',      _env('ADMIN_USERNAME', 'admin'));
define('ADMIN_PASSWORD_HASH', _env('ADMIN_PASSWORD_HASH'));

// GoClaw AI Gateway
define('GOCLAW_API_KEY',  _env('GOCLAW_API_KEY'));
define('GOCLAW_BASE_URL', _env('GOCLAW_BASE_URL', 'https://admin.luminaglobal.info.vn'));
define('GOCLAW_AGENT',    _env('GOCLAW_AGENT', 'lumi'));
