<?php
// ============================================================
//  AUTH — session management
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
         WHERE s.token=:t AND s.expires_at > :now',
        [':t' => $token, ':now' => time()]
    );
    $cache = $row ?: null;
    return $cache;
}

function auth_require(int $tenant_id = 0): array {
    $user = auth_user();
    if (!$user) {
        header('Location: /login.php');
        exit;
    }
    if ($tenant_id && $user['tenant_id'] != $tenant_id) {
        http_response_code(403);
        exit('Access denied');
    }
    return $user;
}

function auth_login(int $user_id): string {
    $token = bin2hex(random_bytes(32));
    db_exec(
        'INSERT INTO sessions(user_id,token,expires_at) VALUES(:u,:t,:e)',
        [':u' => $user_id, ':t' => $token, ':e' => time() + SESSION_TTL]
    );
    setcookie(SESSION_COOKIE, $token, [
        'expires'  => time() + SESSION_TTL,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    return $token;
}

function auth_logout(): void {
    $token = $_COOKIE[SESSION_COOKIE] ?? '';
    if ($token) {
        db_exec('DELETE FROM sessions WHERE token=:t', [':t' => $token]);
    }
    setcookie(SESSION_COOKIE, '', time() - 3600, '/');
}

function register_user(int $tenant_id, string $email, string $password, string $name, string $role = 'member'): array {
    $email = strtolower(trim($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['error' => 'Email không hợp lệ'];
    if (strlen($password) < 6) return ['error' => 'Mật khẩu tối thiểu 6 ký tự'];

    $exists = db_row('SELECT id FROM users WHERE tenant_id=:t AND email=:e', [':t' => $tenant_id, ':e' => $email]);
    if ($exists) return ['error' => 'Email đã được đăng ký'];

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $id = db_exec(
        'INSERT INTO users(tenant_id,email,password_hash,name,role) VALUES(:t,:e,:h,:n,:r)',
        [':t' => $tenant_id, ':e' => $email, ':h' => $hash, ':n' => trim($name), ':r' => $role]
    );
    return ['id' => $id];
}

function login_user(int $tenant_id, string $email, string $password): array {
    $email = strtolower(trim($email));
    $user = db_row('SELECT * FROM users WHERE tenant_id=:t AND email=:e', [':t' => $tenant_id, ':e' => $email]);
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['error' => 'Email hoặc mật khẩu không đúng'];
    }
    $token = auth_login($user['id']);
    return ['id' => $user['id'], 'token' => $token];
}
