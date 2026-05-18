<?php
// ============================================================
//  Becoming A Trainer — dùng chung config của Lumina root
// ============================================================
require_once __DIR__ . '/../../config.php';

// BAT-specific constants
define('SITE_URL', _env('SITE_URL', 'https://luminaglobal.info.vn/becomingatrainer/'));
define('BAT_DB',   __DIR__ . '/brain.db');
