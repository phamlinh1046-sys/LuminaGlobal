<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';

$tenant = detect_tenant();
if (!$tenant) { echo json_encode(['error' => 'Tenant không xác định']); exit; }

$user = auth_user();
if (!$user) { echo json_encode(['error' => 'Chưa đăng nhập', 'redirect' => '/login.php']); exit; }

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$type    = $body['type'] ?? '';
$answers = $body['answers'] ?? [];

if (!in_array($type, ['quick', 'deep']) || empty($answers)) {
    echo json_encode(['error' => 'Dữ liệu không hợp lệ']); exit;
}

$db = lumina_db();

// Create assessment
$assessment_id = db_exec(
    'INSERT INTO assessments(user_id,type,status) VALUES(:u,:t,"pending")',
    [':u' => $user['id'], ':t' => $type]
);

// Save answers
$stmt = $db->prepare('INSERT INTO answers(assessment_id,question_key,answer_value) VALUES(:a,:k,:v)');
foreach ($answers as $key => $val) {
    $stmt->bindValue(':a', $assessment_id);
    $stmt->bindValue(':k', (string)$key);
    $stmt->bindValue(':v', is_array($val) ? implode(',', $val) : (string)$val);
    $stmt->execute();
    $stmt->reset();
}

echo json_encode(['ok' => true, 'assessment_id' => $assessment_id]);
