<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

// Request access (main domain - no tenant needed)
if ($action === 'request_access') {
    $name  = trim($body['name']  ?? '');
    $email = trim($body['email'] ?? '');
    $phone = trim($body['phone'] ?? '');
    $org   = trim($body['org']   ?? '');
    $msg   = trim($body['msg']   ?? '');

    if (!$name || !$email || !$phone) { echo json_encode(['error' => 'Vui lòng điền đầy đủ thông tin (*)']); exit; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['error' => 'Email không hợp lệ']); exit; }

    // Check duplicate
    $dup = db_row('SELECT id FROM access_requests WHERE email=:e AND created_at > :t',
        [':e' => strtolower($email), ':t' => time() - 86400]);
    if ($dup) { echo json_encode(['error' => 'Email này đã gửi yêu cầu trong 24h qua']); exit; }

    db_exec('INSERT INTO access_requests(name,email,phone,organization,message) VALUES(:n,:e,:ph,:o,:m)',
        [':n' => $name, ':e' => strtolower($email), ':ph' => $phone, ':o' => $org, ':m' => $msg]);

    notify_admin_access_request($name, $email, $org, $phone);
    echo json_encode(['ok' => true]);
    exit;
}

// Detect tenant (may be null on main domain)
$tenant = detect_tenant();

// login from main domain — no tenant required
if ($action === 'login') {
    if ($tenant) {
        // Normal subdomain login
        $res = login_user($tenant['id'], $body['email'] ?? '', $body['password'] ?? '');
        $slug = $tenant['slug'];
    } else {
        // Main domain: search across all tenants
        $res  = login_user_any_tenant($body['email'] ?? '', $body['password'] ?? '');
        $slug = $res['slug'] ?? '';
    }

    if (isset($res['error'])) {
        echo json_encode(['error' => $res['error'], 'message' => $res['message'] ?? '']);
        exit;
    }

    // Build redirect URL (absolute for cross-domain, relative for same-domain)
    $latest = db_row(
        'SELECT id, status FROM assessments WHERE user_id=:u ORDER BY created_at DESC LIMIT 1',
        [':u' => $res['id']]
    );
    $path = ($latest && $latest['status'] === 'completed')
        ? '/results.php?id=' . $latest['id']
        : '/assessment.php';

    if (!$tenant && $slug) {
        $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $redirect = "$scheme://$slug." . _env('LUMINA_BASE_DOMAIN', 'luminaglobal.info.vn') . $path;
    } else {
        $redirect = $path;
    }

    echo json_encode(['ok' => true, 'redirect' => $redirect]);
    exit;
}

// logout — works from any domain
if ($action === 'logout') {
    auth_logout();
    echo json_encode(['ok' => true]);
    exit;
}

// Remaining actions require a tenant
if (!$tenant) { echo json_encode(['error' => 'Tenant không xác định']); exit; }

if ($action === 'register') {
    $res = register_user($tenant['id'], $body['email'] ?? '', $body['password'] ?? '', $body['name'] ?? '', $body['phone'] ?? '');
    if (isset($res['error'])) { echo json_encode(['error' => $res['error']]); exit; }

    notify_admin_new_registration($res, $tenant);
    echo json_encode(['ok' => true, 'pending' => true]);

} else {
    http_response_code(400);
    echo json_encode(['error' => 'Action không hợp lệ']);
}
