<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/tenant.php';
require_once __DIR__ . '/includes/auth.php';

$tenant = detect_tenant();
if (!$tenant) {
    // Main domain - redirect to demo
    header('Location: https://demo.' . LUMINA_BASE_DOMAIN . '/login.php');
    exit;
}

// Already logged in?
$user = auth_user();
if ($user && $user['tenant_id'] == $tenant['id']) {
    header('Location: /dashboard.php');
    exit;
}

$brand_color = $tenant['primary_color'] ?? '#6C47FF';
$tenant_name = htmlspecialchars($tenant['name']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng nhập — <?= $tenant_name ?> | Lumina</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/lumina.css">
  <style>:root{--brand:<?= $brand_color ?>}</style>
</head>
<body>
<div class="auth-wrap">
  <div class="auth-box fade-in">
    <div class="auth-logo">
      <h1>✦ <span>Lumina</span></h1>
      <p><?= $tenant_name ?> — Nền tảng phát triển bản thân</p>
    </div>

    <div class="auth-tabs" role="tablist">
      <button class="auth-tab active" id="tab-login" onclick="switchTab('login')">Đăng nhập</button>
      <button class="auth-tab" id="tab-register" onclick="switchTab('register')">Đăng ký</button>
    </div>

    <!-- LOGIN FORM -->
    <form id="form-login" onsubmit="submitAuth(event,'login')">
      <div class="form-group">
        <label class="form-label">Email</label>
        <input class="form-input" type="email" name="email" placeholder="you@example.com" required autocomplete="email">
      </div>
      <div class="form-group">
        <label class="form-label">Mật khẩu</label>
        <input class="form-input" type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
      </div>
      <div id="err-login" class="form-error" style="display:none"></div>
      <button class="btn btn-primary btn-full" type="submit" id="btn-login">Đăng nhập</button>
    </form>

    <!-- REGISTER FORM -->
    <form id="form-register" style="display:none" onsubmit="submitAuth(event,'register')">
      <div class="form-group">
        <label class="form-label">Họ và tên</label>
        <input class="form-input" type="text" name="name" placeholder="Nguyễn Văn A" required autocomplete="name">
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input class="form-input" type="email" name="email" placeholder="you@example.com" required autocomplete="email">
      </div>
      <div class="form-group">
        <label class="form-label">Mật khẩu</label>
        <input class="form-input" type="password" name="password" placeholder="Tối thiểu 6 ký tự" required autocomplete="new-password" minlength="6">
      </div>
      <div id="err-register" class="form-error" style="display:none"></div>
      <button class="btn btn-primary btn-full" type="submit" id="btn-register">Tạo tài khoản miễn phí</button>
    </form>

    <p style="text-align:center;margin-top:20px;font-size:.85rem;color:var(--text-muted)">
      Bằng cách đăng ký, bạn đồng ý với điều khoản sử dụng của Lumina.
    </p>
  </div>
</div>

<script>
function switchTab(tab) {
  document.getElementById('form-login').style.display = tab === 'login' ? '' : 'none';
  document.getElementById('form-register').style.display = tab === 'register' ? '' : 'none';
  document.getElementById('tab-login').classList.toggle('active', tab === 'login');
  document.getElementById('tab-register').classList.toggle('active', tab === 'register');
}

async function submitAuth(e, action) {
  e.preventDefault();
  const form = e.target;
  const btn = document.getElementById('btn-' + action);
  const err = document.getElementById('err-' + action);
  const data = Object.fromEntries(new FormData(form));
  data.action = action;

  btn.disabled = true;
  btn.textContent = 'Đang xử lý...';
  err.style.display = 'none';

  try {
    const res = await fetch('/api/auth.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(data)
    });
    const json = await res.json();
    if (json.error) {
      err.textContent = json.error;
      err.style.display = 'block';
      btn.disabled = false;
      btn.textContent = action === 'login' ? 'Đăng nhập' : 'Tạo tài khoản miễn phí';
    } else {
      window.location.href = json.redirect || '/assessment.php';
    }
  } catch (ex) {
    err.textContent = 'Lỗi kết nối, vui lòng thử lại.';
    err.style.display = 'block';
    btn.disabled = false;
    btn.textContent = action === 'login' ? 'Đăng nhập' : 'Tạo tài khoản miễn phí';
  }
}
</script>
</body>
</html>
