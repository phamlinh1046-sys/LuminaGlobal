<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/tenant.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/questions.php';

set_time_limit(120);

$tenant = detect_tenant();
if (!$tenant) { echo json_encode(['error' => 'Tenant không xác định']); exit; }

$user = auth_user();
if (!$user) { echo json_encode(['error' => 'Chưa đăng nhập']); exit; }

$body          = json_decode(file_get_contents('php://input'), true) ?? [];
$assessment_id = (int)($body['assessment_id'] ?? 0);
if (!$assessment_id) { echo json_encode(['error' => 'assessment_id bắt buộc']); exit; }

$assessment = db_row(
    'SELECT * FROM assessments WHERE id=:id AND user_id=:u',
    [':id' => $assessment_id, ':u' => $user['id']]
);
if (!$assessment) { echo json_encode(['error' => 'Không tìm thấy assessment']); exit; }

// Đã phân tích rồi → trả về luôn
$existing = db_row('SELECT id FROM results WHERE assessment_id=:a', [':a' => $assessment_id]);
if ($existing) {
    echo json_encode(['ok' => true, 'redirect' => '/results.php?id=' . $assessment_id]);
    exit;
}

// Load câu trả lời
$rows = db_rows('SELECT question_key, answer_value FROM answers WHERE assessment_id=:a', [':a' => $assessment_id]);
$answers_map = [];
foreach ($rows as $r) $answers_map[$r['question_key']] = $r['answer_value'];

// Build nội dung câu hỏi + câu trả lời
$questions = get_questions($assessment['type']);
$q_index   = [];
foreach ($questions as $q) $q_index[$q['key']] = $q;

$summary_lines = [];
foreach ($answers_map as $key => $val) {
    $q = $q_index[$key] ?? null;
    if (!$q) continue;
    $display_val = $val;
    if ($q['type'] === 'choice' && isset($q['options'][$val])) {
        $display_val = "[{$val}] " . $q['options'][$val];
    } elseif ($q['type'] === 'multi') {
        $parts  = explode(',', $val);
        $labels = array_filter(array_map(fn($p) => $q['options'][$p] ?? null, $parts));
        $display_val = implode(' | ', $labels);
    } elseif ($q['type'] === 'slider') {
        $display_val = $val . '/10 — ' . ($q['label'] ?? $key);
    }
    $summary_lines[] = "- [{$q['section']}] {$q['text']}\n  → {$display_val}";
}
$answers_text = implode("\n", $summary_lines);

$prompt = <<<PROMPT
Bạn là chuyên gia tâm lý và phát triển bản thân hàng đầu. Dựa trên kết quả đánh giá dưới đây, hãy phân tích sâu và trả về JSON chính xác.

## Câu trả lời đánh giá (loại: {$assessment['type']}):
{$answers_text}

## Yêu cầu:
Phân tích toàn diện và trả về JSON với cấu trúc SAU (không thêm bất kỳ text ngoài JSON):

{
  "archetype": "Tên archetype (1 trong 8: Nhà Kiến Tạo | Chất Xúc Tác | Người Bảo Vệ | Người Tìm Kiếm | Người Khai Phá | Người Dẫn Dắt | Người Thực Thi | Người Kết Nối)",
  "archetype_tagline": "Câu tagline ngắn gọn cho archetype (dưới 10 từ)",
  "archetype_desc": "Mô tả 3-4 câu về archetype này trong bối cảnh người dùng",
  "strengths": [
    {"title": "Tên điểm mạnh", "desc": "Mô tả ngắn gọn 1-2 câu"},
    {"title": "...", "desc": "..."},
    {"title": "...", "desc": "..."},
    {"title": "...", "desc": "..."}
  ],
  "blind_spots": [
    {"title": "Tên điểm mù", "desc": "Mô tả và gợi ý nhận thức"},
    {"title": "...", "desc": "..."},
    {"title": "...", "desc": "..."}
  ],
  "motivation_drivers": [
    {"driver": "Tên động lực", "percentage": 85, "desc": "Mô tả ngắn"},
    {"driver": "...", "percentage": 70, "desc": "..."},
    {"driver": "...", "percentage": 55, "desc": "..."},
    {"driver": "...", "percentage": 40, "desc": "..."}
  ],
  "life_wheel": {
    "Sự Nghiệp": 7,
    "Sức Khỏe": 6,
    "Mối Quan Hệ": 8,
    "Tài Chính": 5,
    "Phát Triển": 7,
    "Niềm Vui": 6,
    "Mục Đích": 8,
    "Môi Trường": 7
  },
  "internal_conflict": {
    "headline": "Tên xung đột chính (dưới 8 từ)",
    "description": "3-4 câu mô tả xung đột nội tâm và nguồn gốc",
    "reframe": "1-2 câu reframe tích cực"
  },
  "growth_direction": {
    "headline": "Hướng phát triển cốt lõi (dưới 10 từ)",
    "next_90_days": "3-4 câu về bước cụ thể trong 90 ngày tới",
    "power_question": "Một câu hỏi mạnh mẽ để suy ngẫm",
    "resources": ["Hành động gợi ý 1", "...", "..."]
  },
  "summary_insight": "2-3 câu tổng kết — điều quan trọng nhất người dùng cần biết về bản thân"
}

QUAN TRỌNG:
- Phân tích cá nhân hóa cao dựa trên câu trả lời thực tế
- Ngôn ngữ: tiếng Việt, ấm áp và chuyên sâu
- life_wheel score phải khớp với câu trả lời slider nếu có
- Trả về JSON thuần túy, không có markdown code block
PROMPT;

// ── Gọi GoClaw API (OpenAI-compatible) ───────────────────────
$api_key  = GOCLAW_API_KEY;
$base_url = rtrim(GOCLAW_BASE_URL, '/');
$agent    = GOCLAW_AGENT;

if (!$api_key) {
    echo json_encode(['error' => 'GOCLAW_API_KEY chưa được cấu hình trong .env']); exit;
}

$payload = json_encode([
    'agent'    => $agent,
    'messages' => [
        ['role' => 'user', 'content' => $prompt]
    ],
    'stream'   => false,
]);

$ch = curl_init($base_url . '/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_TIMEOUT        => 90,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $api_key,
        'Content-Type: application/json',
    ],
]);

$resp      = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!$resp || $http_code !== 200) {
    error_log("GoClaw API error $http_code: $resp");
    echo json_encode(['error' => 'AI phân tích thất bại (HTTP ' . $http_code . '), vui lòng thử lại']);
    exit;
}

// Parse OpenAI-compatible response
$api_data = json_decode($resp, true);
$raw_json = $api_data['choices'][0]['message']['content'] ?? '';

if (!$raw_json) {
    error_log("GoClaw empty content. Raw: $resp");
    echo json_encode(['error' => 'Kết quả từ AI trống, vui lòng thử lại']);
    exit;
}

// Xóa markdown code fences nếu có
$raw_json = preg_replace('/^```json?\s*/m', '', $raw_json);
$raw_json = preg_replace('/^```\s*$/m', '', $raw_json);
$raw_json = trim($raw_json);

$analysis = json_decode($raw_json, true);
if (!$analysis) {
    error_log("JSON parse error. Raw: $raw_json");
    echo json_encode(['error' => 'Lỗi phân tích kết quả AI']);
    exit;
}

// Lưu kết quả vào DB
db_exec(
    'INSERT INTO results(assessment_id,archetype,archetype_desc,strengths_json,blind_spots_json,
      motivation_drivers_json,life_wheel_json,internal_conflict,growth_direction,ai_raw_json)
     VALUES(:a,:arch,:adesc,:str,:bs,:md,:lw,:ic,:gd,:raw)',
    [
        ':a'     => $assessment_id,
        ':arch'  => $analysis['archetype'] ?? '',
        ':adesc' => json_encode([
            'tagline' => $analysis['archetype_tagline'] ?? '',
            'desc'    => $analysis['archetype_desc']    ?? '',
            'summary' => $analysis['summary_insight']   ?? '',
        ]),
        ':str'   => json_encode($analysis['strengths']          ?? []),
        ':bs'    => json_encode($analysis['blind_spots']        ?? []),
        ':md'    => json_encode($analysis['motivation_drivers'] ?? []),
        ':lw'    => json_encode($analysis['life_wheel']         ?? []),
        ':ic'    => json_encode($analysis['internal_conflict']  ?? []),
        ':gd'    => json_encode($analysis['growth_direction']   ?? []),
        ':raw'   => $raw_json,
    ]
);

// Đánh dấu hoàn thành
db_exec(
    "UPDATE assessments SET status='completed', completed_at=strftime('%s','now') WHERE id=:id",
    [':id' => $assessment_id]
);

echo json_encode(['ok' => true, 'redirect' => '/results.php?id=' . $assessment_id]);
