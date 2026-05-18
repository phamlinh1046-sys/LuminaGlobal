<?php
require_once __DIR__ . '/config.php';

error_reporting(0);
ini_set('display_errors', 0);

date_default_timezone_set('Asia/Ho_Chi_Minh');

$db_file = __DIR__ . '/brain.db';
$db      = new SQLite3($db_file);

@$db->exec('ALTER TABLE customers ADD COLUMN created_at INTEGER DEFAULT 0');
@$db->exec('ALTER TABLE customers ADD COLUMN email_2_sent INTEGER DEFAULT 0');
@$db->exec('ALTER TABLE customers ADD COLUMN email_3_sent INTEGER DEFAULT 0');

function send_resend_email_cron($to_email, $subject, $html_body) {
    $api_key    = RESEND_API_KEY;
    $from_email = FROM_EMAIL;

    $ch = curl_init('https://api.resend.com/emails');
    $payload = json_encode([
        'from'    => $from_email,
        'to'      => [$to_email],
        'subject' => $subject,
        'html'    => $html_body,
    ]);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $api_key,
        'Content-Type: application/json',
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

$now        = time();
$two_days   = 2 * 24 * 60 * 60;
$three_days = 3 * 24 * 60 * 60;
$count2     = 0;
$count3     = 0;

// Email 2 — gửi sau 2 ngày
$res2 = $db->query("SELECT id, name, email FROM customers WHERE created_at > 0 AND email_2_sent = 0 AND ($now - created_at) >= $two_days AND id NOT IN (SELECT customer_id FROM orders WHERE status = 'Đã thanh toán')");
if ($res2) {
    while ($row = $res2->fetchArray(SQLITE3_ASSOC)) {
        if (empty($row['email'])) continue;

        $name     = $row['name'];
        $subject2 = "Tại sao \"Giỏi chuyên môn\" chưa chắc đã \"Dạy được\"? 🤔";
        $body2    = "<p>Chào " . htmlspecialchars($name) . ",</p>
                <p>Trong quá trình làm việc với hàng trăm chuyên gia và cấp quản lý, tôi nhận thấy một mô-típ rất quen thuộc: Chúng ta mất 5 năm, 10 năm để trở nên xuất sắc trong ngành của mình, nhưng lại \"đứng hình\" khi cố gắng truyền đạt điều đó cho người khác.</p>
                <p>Đó không phải là lỗi của anh/chị. Đó là khoảng trống giữa <strong>Chuyên môn (Know-how)</strong> và <strong>Sư phạm (Andragogy)</strong>.</p>
                <p>Khi đứng trước nhân sự mới hoặc học viên, rào cản lớn nhất không phải là \"nói gì\", mà là \"hệ thống hóa nó như thế nào\". Rất nhiều chuyên gia mắc kẹt trong việc nhồi nhét quá nhiều kiến thức kỹ thuật khô khan, khiến người nghe quá tải.</p>
                <p><strong>Bí quyết: Đừng dạy tất cả những gì bạn biết. Hãy dạy những gì họ cần để làm được việc.</strong></p>
                <p>Ngày mai, tôi sẽ chia sẻ lộ trình cụ thể để đóng gói chất xám của anh/chị thành một \"di sản\" thực sự.</p>
                <p>Chúc anh/chị một ngày làm việc hiệu quả,<br><strong>Lumina Global Team</strong></p>";

        send_resend_email_cron($row['email'], $subject2, $body2);
        $db->exec("UPDATE customers SET email_2_sent = 1 WHERE id = " . (int)$row['id']);
        $count2++;
    }
}

// Email 3 — gửi sau 3 ngày
$res3 = $db->query("SELECT id, name, email FROM customers WHERE created_at > 0 AND email_2_sent = 1 AND email_3_sent = 0 AND ($now - created_at) >= $three_days AND id NOT IN (SELECT customer_id FROM orders WHERE status = 'Đã thanh toán')");
if ($res3) {
    while ($row = $res3->fetchArray(SQLITE3_ASSOC)) {
        if (empty($row['email'])) continue;

        $name     = $row['name'];
        $subject3 = "Đã đến lúc biến \"Chất xám\" thành Di sản (Và thu nhập xứng tầm) ✨";
        $body3    = "<p>Chào " . htmlspecialchars($name) . ",</p>
                <p>Hôm qua chúng ta đã nói về khoảng trống giữa việc \"Biết\" và việc \"Dạy\". Hôm nay, tôi muốn mời anh/chị chính thức bước qua khoảng trống đó.</p>
                <p>Chương trình <strong>Becoming A Trainer</strong> được thiết kế để đi sâu vào thực chiến dành riêng cho những người có \"chất\" như anh/chị:</p>
                <ol>
                    <li><strong>Instructional Design:</strong> Kỹ thuật đóng gói tri thức, biến kinh nghiệm thành giáo trình có tính ứng dụng cao.</li>
                    <li><strong>Facilitation:</strong> Nghệ thuật điều phối lớp học, thu hút người trưởng thành bằng chiều sâu chuyên môn.</li>
                    <li><strong>Branding &amp; Pricing:</strong> Định hình thương hiệu độc bản, định giá chất xám và xây dựng mô hình kinh doanh bền vững.</li>
                </ol>
                <p>🎁 <strong>Đặc quyền dành riêng cho anh/chị:</strong><br>
                Mức phí gốc 9.990.000 VNĐ — ưu đãi đặc biệt chỉ còn <strong>99.999 VNĐ</strong>.</p>
                <p>👉 <strong><a href=\"" . SITE_URL . "\">Bấm vào đây để giữ chỗ ngay</a></strong></p>
                <p>Trân trọng,<br><strong>Lumina Global Team</strong></p>";

        send_resend_email_cron($row['email'], $subject3, $body3);
        $db->exec("UPDATE customers SET email_3_sent = 1 WHERE id = " . (int)$row['id']);
        $count3++;
    }
}

echo "Cron finished at " . date('Y-m-d H:i:s') . ". Email2: $count2, Email3: $count3.\n";
