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

    if (!$name || !$email) { echo json_encode(['error' => 'Vui lòng điền đầy đủ thông tin']); exit; }
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

// All other actions require a tenant
$tenant = detect_tenant();
if (!$tenant) { echo json_encode(['error' => 'Tenant không xác định']); exit; }

if ($action === 'register') {
    $res = register_user($tenant['id'], $body['email'] ?? '', $body['password'] ?? '', $body['name'] ?? '', $body['phone'] ?? '');
    if (isset($res['error'])) { echo json_encode(['error' => $res['error']]); exit; }

    // Notify admin
    notify_admin_new_registration($res, $tenant);

    echo json_encode(['ok' => true, 'pending' => true]);

} elseif ($action === 'login') {
    $res = login_user($tenant['id'], $body['email'] ?? '', $body['password'] ?? '');

    if (isset($res['error'])) {
        // pending/rejected are special states — return them as-is so JS can show correct UI
        echo json_encode(['error' => $res['error'], 'message' => $res['message'] ?? '']);
        exit;
    }

    // Redirect after login: results if done, else assessment
    $latest = db_row(
        'SELECT id, status FROM assessments WHERE user_id=:u ORDER BY created_at DESC LIMIT 1',
        [':u' => $res['id']]
    );
    $redirect = ($latest && $latest['status'] === 'completed')
        ? '/results.php?id=' . $latest['id']
        : '/assessment.php';

    echo json_encode(['ok' => true, 'redirect' => $redirect]);

} elseif ($action === 'logout') {
    auth_logout();
    echo json_encode(['ok' => true]);

} else {
    http_response_code(400);
    echo json_encode(['error' => 'Action không hợp lệ']);
}
