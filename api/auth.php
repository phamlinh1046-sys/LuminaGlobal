<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

// ── Request access (main domain) ───────────────────────────
if ($action === 'request_access') {
    $name  = trim($body['name']  ?? '');
    $email = trim($body['email'] ?? '');
    $phone = trim($body['phone'] ?? '');
    $org   = trim($body['org']   ?? '');
    $msg   = trim($body['msg']   ?? '');

    if (!$name || !$email || !$phone) { echo json_encode(['error' => 'Vui lòng điền đầy đủ thông tin (*)']); exit; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['error' => 'Email không hợp lệ']); exit; }

    $dup = db_row('SELECT id FROM access_requests WHERE email=:e AND created_at > :t',
        [':e' => strtolower($email), ':t' => time() - 86400]);
    if ($dup) { echo json_encode(['error' => 'Email này đã gửi yêu cầu trong 24h qua']); exit; }

    db_exec('INSERT INTO access_requests(name,email,phone,organization,message) VALUES(:n,:e,:ph,:o,:m)',
        [':n' => $name, ':e' => strtolower($email), ':ph' => $phone, ':o' => $org, ':m' => $msg]);

    notify_admin_access_request($name, $email, $org, $phone);
    echo json_encode(['ok' => true]);
    exit;
}

$tenant = detect_tenant();

// ── Login (works on both main domain and subdomains) ───────
if ($action === 'login') {
    $email    = $body['email']    ?? '';
    $password = $body['password'] ?? '';

    if ($tenant) {
        $res  = login_user($tenant['id'], $email, $password);
        $slug = $tenant['slug'];
    } else {
        $res  = login_user_any_tenant($email, $password);
        $slug = $res['slug'] ?? '';
    }

    if (isset($res['error'])) {
        echo json_encode(['error' => $res['error'], 'message' => $res['message'] ?? '']);
        exit;
    }

    $latest = db_row(
        'SELECT id, status FROM assessments WHERE user_id=:u ORDER BY created_at DESC LIMIT 1',
        [':u' => $res['id']]
    );
    $path = ($latest && $latest['status'] === 'completed')
        ? '/results.php?id=' . $latest['id']
        : '/assessment.php';

    // Cross-domain redirect when logging in from main domain
    if (!$tenant && $slug) {
        $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $redirect = "$scheme://$slug." . _env('LUMINA_BASE_DOMAIN', 'luminaglobal.info.vn') . $path;
    } else {
        $redirect = $path;
    }

    echo json_encode(['ok' => true, 'redirect' => $redirect]);
    exit;
}

// ── Logout ─────────────────────────────────────────────────
if ($action === 'logout') {
    auth_logout();
    echo json_encode(['ok' => true]);
    exit;
}

// ── Change password (requires active session) ──────────────
if ($action === 'change_password') {
    $user = auth_user();
    if (!$user) { echo json_encode(['error' => 'Phiên đăng nhập đã hết hạn']); exit; }

    $current = $body['current_password'] ?? '';
    $new     = $body['new_password']     ?? '';
    $confirm = $body['confirm_password'] ?? '';

    if ($new !== $confirm) { echo json_encode(['error' => 'Mật khẩu xác nhận không khớp']); exit; }

    $res = change_user_password($user['id'], $current, $new);
    echo json_encode($res);
    exit;
}

// ── Remaining actions require a tenant ─────────────────────
if (!$tenant) { echo json_encode(['error' => 'Tenant không xác định']); exit; }

// ── Register (auto-approved, login immediately) ────────────
if ($action === 'register') {
    $res = register_user(
        $tenant['id'],
        $body['email']    ?? '',
        $body['password'] ?? '',
        $body['name']     ?? '',
        $body['phone']    ?? '',
        $body['org']      ?? ''
    );
    if (isset($res['error'])) { echo json_encode(['error' => $res['error']]); exit; }

    // Auto-login right after registration
    auth_login($res['id']);

    $redirect = '/assessment.php';
    echo json_encode(['ok' => true, 'redirect' => $redirect]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Action không hợp lệ']);
