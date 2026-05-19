<?php
// ============================================================
//  AUTH — session management + approval flow
// ============================================================
require_once __DIR__ . '/../db.php';

const SESSION_COOKIE = 'lumina_sess';
const SESSION_TTL    = 86400 * 30; // 30 days

function auth_user(): ?array {
    static $cache = false;
    if ($cache !== false) return $cache;

    $token = $_COOKIE[SESSION_COOKIE] ?? '';
    if (!$token) { $cache = null; return null; }

    $row = db_row(
        'SELECT u.*, t.slug AS tenant_slug, t.name AS tenant_name, t.primary_color
         FROM sessions s
         JOIN users u ON u.id = s.user_id
         JOIN tenants t ON t.id = u.tenant_id
         WHERE s.token=:t AND s.expires_at > :now AND u.status="approved"',
        [':t' => $token, ':now' => time()]
    );
    $cache = $row ?: null;
    return $cache;
}

function auth_require(int $tenant_id = 0): array {
    $user = auth_user();
    if (!$user) { header('Location: /login.php'); exit; }
    if ($tenant_id && $user['tenant_id'] != $tenant_id) { http_response_code(403); exit('Access denied'); }
    return $user;
}

function auth_login(int $user_id): string {
    $token = bin2hex(random_bytes(32));
    db_exec(
        'INSERT INTO sessions(user_id,token,expires_at) VALUES(:u,:t,:e)',
        [':u' => $user_id, ':t' => $token, ':e' => time() + SESSION_TTL]
    );
    $base   = _env('LUMINA_BASE_DOMAIN', '');
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $opts   = [
        'expires'  => time() + SESSION_TTL,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => $secure,
    ];
    // Share cookie across all subdomains on production (domain contains a dot)
    if ($base && substr_count($base, '.') >= 1) {
        $opts['domain'] = '.' . $base;
    }
    setcookie(SESSION_COOKIE, $token, $opts);
    return $token;
}

// Login from main domain — search across all tenants by email
function login_user_any_tenant(string $email, string $password): array {
    $email = strtolower(trim($email));
    $user  = db_row(
        'SELECT u.*, t.slug FROM users u JOIN tenants t ON t.id=u.tenant_id WHERE u.email=:e ORDER BY u.created_at DESC LIMIT 1',
        [':e' => $email]
    );
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['error' => 'Email hoặc mật khẩu không đúng'];
    }
    if ($user['status'] === 'pending') {
        return ['error' => 'pending', 'message' => 'Tài khoản đang chờ admin phê duyệt. Bạn sẽ nhận thông báo khi được duyệt.'];
    }
    if ($user['status'] === 'rejected') {
        return ['error' => 'rejected', 'message' => 'Tài khoản không được phê duyệt. Liên hệ hello@luminaglobal.info.vn để biết thêm.'];
    }
    auth_login($user['id']);
    return ['id' => $user['id'], 'slug' => $user['slug']];
}

function auth_logout(): void {
    $token = $_COOKIE[SESSION_COOKIE] ?? '';
    if ($token) db_exec('DELETE FROM sessions WHERE token=:t', [':t' => $token]);
    $base   = _env('LUMINA_BASE_DOMAIN', '');
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $opts   = ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax', 'secure' => $secure];
    if ($base && substr_count($base, '.') >= 1) $opts['domain'] = '.' . $base;
    setcookie(SESSION_COOKIE, '', $opts);
}

function register_user(int $tenant_id, string $email, string $password, string $name, string $phone = ''): array {
    $email = strtolower(trim($email));
    $phone = trim($phone);
    if (strlen(trim($name)) < 2) return ['error' => 'Vui lòng nhập họ tên'];
    if (!$phone) return ['error' => 'Số điện thoại là bắt buộc'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['error' => 'Email không hợp lệ'];
    if (strlen($password) < 6) return ['error' => 'Mật khẩu tối thiểu 6 ký tự'];

    $exists = db_row('SELECT id FROM users WHERE tenant_id=:t AND email=:e', [':t' => $tenant_id, ':e' => $email]);
    if ($exists) return ['error' => 'Email này đã được đăng ký'];

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $id = db_exec(
        'INSERT INTO users(tenant_id,email,password_hash,name,phone,role,status) VALUES(:t,:e,:h,:n,:p,"member","pending")',
        [':t' => $tenant_id, ':e' => $email, ':h' => $hash, ':n' => trim($name), ':p' => $phone]
    );
    return ['id' => $id, 'email' => $email, 'name' => trim($name), 'phone' => $phone];
}

function login_user(int $tenant_id, string $email, string $password): array {
    $email = strtolower(trim($email));
    $user  = db_row('SELECT * FROM users WHERE tenant_id=:t AND email=:e', [':t' => $tenant_id, ':e' => $email]);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['error' => 'Email hoặc mật khẩu không đúng'];
    }
    if ($user['status'] === 'pending') {
        return ['error' => 'pending', 'message' => 'Tài khoản đang chờ admin phê duyệt. Bạn sẽ nhận thông báo khi được duyệt.'];
    }
    if ($user['status'] === 'rejected') {
        return ['error' => 'rejected', 'message' => 'Tài khoản không được phê duyệt. Liên hệ hello@luminaglobal.info.vn để biết thêm.'];
    }

    auth_login($user['id']);
    return ['id' => $user['id']];
}

// Send email via Resend API
function send_email(string $to, string $to_name, string $subject, string $html): bool {
    $api_key = _env('RESEND_API_KEY', '');
    if (!$api_key || str_starts_with($api_key, 'YOUR') || str_starts_with($api_key, 're_xxx')) return false;

    $payload = json_encode([
        'from'    => _env('FROM_EMAIL', 'Lumina <hello@luminaglobal.info.vn>'),
        'to'      => ["{$to_name} <{$to}>"],
        'subject' => $subject,
        'html'    => $html,
    ]);

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $api_key, 'Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 10,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $code === 200;
}

// Notify admin about new registration
function notify_admin_new_registration(array $user, array $tenant): void {
    $admin_email = _env('ADMIN_EMAIL', 'hello@luminaglobal.info.vn');
    $admin_url   = 'https://' . _env('LUMINA_BASE_DOMAIN', 'luminaglobal.info.vn') . '/admin.php';
    $html = "
    <div style='font-family:Inter,sans-serif;max-width:560px;margin:0 auto;background:#07071a;color:#fff;padding:32px;border-radius:16px'>
      <h2 style='color:#c4b5fd;margin-bottom:8px'>🔔 Người dùng mới đăng ký</h2>
      <p style='color:rgba(255,255,255,.6);margin-bottom:24px'>Cần phê duyệt trên Lumina Admin</p>
      <table style='width:100%;border-collapse:collapse'>
        <tr><td style='padding:10px 0;color:rgba(255,255,255,.5);width:120px'>Tên</td><td style='color:#fff;font-weight:700'>{$user['name']}</td></tr>
        <tr><td style='padding:10px 0;color:rgba(255,255,255,.5)'>Email</td><td style='color:#fff'>{$user['email']}</td></tr>
        <tr><td style='padding:10px 0;color:rgba(255,255,255,.5)'>SĐT</td><td style='color:#fff'>" . ($user['phone'] ?: '—') . "</td></tr>
        <tr><td style='padding:10px 0;color:rgba(255,255,255,.5)'>Tenant</td><td style='color:#00C9B1'>{$tenant['name']} ({$tenant['slug']})</td></tr>
        <tr><td style='padding:10px 0;color:rgba(255,255,255,.5)'>Thời gian</td><td style='color:#fff'>" . date('d/m/Y H:i') . "</td></tr>
      </table>
      <a href='{$admin_url}' style='display:inline-block;margin-top:24px;padding:12px 28px;background:#6C47FF;color:#fff;border-radius:100px;text-decoration:none;font-weight:700'>Phê duyệt ngay →</a>
    </div>";
    send_email($admin_email, 'Lumina Admin', "🔔 Người dùng mới: {$user['name']} — {$tenant['name']}", $html);
}

// Notify admin about access request from main domain
function notify_admin_access_request(string $name, string $email, string $org, string $phone = ''): void {
    $admin_email = _env('ADMIN_EMAIL', 'hello@luminaglobal.info.vn');
    $admin_url   = 'https://' . _env('LUMINA_BASE_DOMAIN', 'luminaglobal.info.vn') . '/admin.php';
    $html = "
    <div style='font-family:Inter,sans-serif;max-width:560px;margin:0 auto;background:#07071a;color:#fff;padding:32px;border-radius:16px'>
      <h2 style='color:#00C9B1;margin-bottom:8px'>📬 Yêu cầu truy cập mới</h2>
      <p style='color:rgba(255,255,255,.6);margin-bottom:24px'>Từ trang luminaglobal.info.vn</p>
      <table style='width:100%;border-collapse:collapse'>
        <tr><td style='padding:10px 0;color:rgba(255,255,255,.5);width:120px'>Tên</td><td style='color:#fff;font-weight:700'>{$name}</td></tr>
        <tr><td style='padding:10px 0;color:rgba(255,255,255,.5)'>Email</td><td style='color:#fff'>{$email}</td></tr>
        <tr><td style='padding:10px 0;color:rgba(255,255,255,.5)'>SĐT</td><td style='color:#fff'>" . ($phone ?: '—') . "</td></tr>
        <tr><td style='padding:10px 0;color:rgba(255,255,255,.5)'>Tổ chức</td><td style='color:#fff'>{$org}</td></tr>
      </table>
      <a href='{$admin_url}' style='display:inline-block;margin-top:24px;padding:12px 28px;background:#00C9B1;color:#07071a;border-radius:100px;text-decoration:none;font-weight:700'>Xem trên Admin →</a>
    </div>";
    send_email($admin_email, 'Lumina Admin', "📬 Yêu cầu truy cập: {$name} — {$org}", $html);
}
