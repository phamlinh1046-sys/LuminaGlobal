<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';

$tenant = detect_tenant();
if (!$tenant) { echo json_encode(['error' => 'Tenant không xác định']); exit; }

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? ($_POST['action'] ?? '');

if ($action === 'register') {
    $res = register_user(
        $tenant['id'],
        $body['email'] ?? '',
        $body['password'] ?? '',
        $body['name'] ?? ''
    );
    if (isset($res['error'])) { echo json_encode(['error' => $res['error']]); exit; }
    auth_login($res['id']);
    echo json_encode(['ok' => true, 'redirect' => '/assessment.php']);

} elseif ($action === 'login') {
    $res = login_user($tenant['id'], $body['email'] ?? '', $body['password'] ?? '');
    if (isset($res['error'])) { echo json_encode(['error' => $res['error']]); exit; }
    // Check if user has completed assessment
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
    echo json_encode(['ok' => true, 'redirect' => '/login.php']);

} else {
    http_response_code(400);
    echo json_encode(['error' => 'Action không hợp lệ']);
}
