<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/tenant.php';
require_once __DIR__ . '/includes/auth.php';

$tenant = detect_tenant();
if (!$tenant) {
    header('Location: https://demo.' . LUMINA_BASE_DOMAIN . '/login.php');
    exit;
}

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
  <style>
    :root{--brand:<?= $brand_color ?>}
    body { background: #07071a; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 16px; }

    /* orbs (reuse from landing) */
    .bg-orbs { position: fixed; inset: 0; pointer-events: none; overflow: hidden; }
    .bg-orbs .orb-1 { position:absolute; width:500px; height:500px; border-radius:50%; filter:blur(90px); opacity:.45; background:radial-gradient(circle,rgba(108,71,255,.7),transparent 70%); top:-100px; right:-80px; }
    .bg-orbs .orb-2 { position:absolute; width:350px; height:350px; border-radius:50%; filter:blur(80px); opacity:.4; background:radial-gradient(circle,rgba(0,201,177,.5),transparent 70%); bottom:0; left:-60px; }

    /* Glass auth box */
    .auth-glass {
      position: relative; z-index: 10;
      width: 100%; max-width: 440px;
      background: rgba(255,255,255,.05);
      border: 1px solid rgba(255,255,255,.12);
      border-radius: 28px;
      padding: 48px 40px;
      backdrop-filter: blur(24px);
      box-shadow: 0 32px 80px rgba(0,0,0,.5), 0 0 0 1px rgba(108,71,255,.15);
      animation: fadeUp .5s ease both;
    }
    @keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:none} }

    .auth-logo-wrap { text-align: center; margin-bottom: 32px; }
    .auth-logo-img { height: 44px; width: auto; }
    .auth-tenant { font-size: .83rem; color: rgba(255,255,255,.4); margin-top: 8px; }

    /* Tabs */
    .auth-tabs {
      display: flex; background: rgba(255,255,255,.06);
      border-radius: 100px; padding: 4px; margin-bottom: 28px;
      border: 1px solid rgba(255,255,255,.08);
    }
    .auth-tab {
      flex: 1; text-align: center; padding: 10px;
      border-radius: 100px; font-weight: 600; font-size: .88rem;
      cursor: pointer; transition: all .2s;
      color: rgba(255,255,255,.45); border: none; background: transparent;
    }
    .auth-tab.active { background: rgba(108,71,255,.8); color: #fff; box-shadow: 0 2px 12px rgba(108,71,255,.4); }

    /* Form */
    .form-group { margin-bottom: 16px; }
    .form-label { display: block; font-weight: 600; font-size: .85rem; color: rgba(255,255,255,.75); margin-bottom: 7px; }
    .form-input {
      width: 100%; padding: 13px 16px;
      background: rgba(255,255,255,.07); border: 1.5px solid rgba(255,255,255,.12);
      border-radius: 12px; font-size: .95rem; font-family: var(--font);
      color: #fff; transition: border-color .2s, box-shadow .2s; outline: none;
    }
    .form-input::placeholder { color: rgba(255,255,255,.28); }
    .form-input:focus { border-color: rgba(108,71,255,.7); box-shadow: 0 0 0 3px rgba(108,71,255,.2); background: rgba(108,71,255,.08); }
    .form-error { color: #f87171; font-size: .83rem; margin-top: 5px; }

    .btn-auth {
      width: 100%; padding: 14px; border-radius: 100px;
      font-size: .95rem; font-weight: 700; cursor: pointer; border: none;
      background: var(--brand); color: #fff; font-family: var(--font);
      transition: transform .2s, box-shadow .2s; margin-top: 4px;
      position: relative; overflow: hidden;
    }
    .btn-auth:hover { transform: translateY(-1px); box-shadow: 0 8px 28px rgba(108,71,255,.5); }
    .btn-auth:disabled { opacity: .6; cursor: not-allowed; transform: none !important; }

    .auth-footer { text-align: center; margin-top: 20px; font-size: .8rem; color: rgba(255,255,255,.3); }
  </style>
</head>
<body>
<div class="bg-orbs"><div class="orb-1"></div><div class="orb-2"></div></div>

<div class="auth-glass">
  <div class="auth-logo-wrap">
    <img src="/assets/Logo.png" alt="Lumina" class="auth-logo-img">
    <div class="auth-tenant"><?= $tenant_name ?> — Nền tảng phát triển bản thân</div>
  </div>

  <div class="auth-tabs" role="tablist">
    <button class="auth-tab active" id="tab-login" onclick="switchTab('login')">Đăng nhập</button>
    <button class="auth-tab" id="tab-register" onclick="switchTab('register')">Đăng ký</button>
  </div>

  <!-- LOGIN -->
  <form id="form-login" onsubmit="submitAuth(event,'login')">
    <div class="form-group">
      <label class="form-label">Email</label>
      <input class="form-input" type="email" name="email" placeholder="you@example.com" required autocomplete="email">
    </div>
    <div class="form-group">
      <label class="form-label">Mật khẩu</label>
      <input class="form-input" type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
    </div>
    <div id="err-login" class="form-error" style="display:none;margin-bottom:12px"></div>
    <button class="btn-auth" type="submit" id="btn-login">Đăng nhập →</button>
  </form>

  <!-- REGISTER -->
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
    <div id="err-register" class="form-error" style="display:none;margin-bottom:12px"></div>
    <button class="btn-auth" type="submit" id="btn-register">Tạo tài khoản miễn phí →</button>
  </form>

  <p class="auth-footer">Bằng cách đăng ký, bạn đồng ý với điều khoản sử dụng.</p>
</div>

<script>
function switchTab(tab) {
  document.getElementById('form-login').style.display = tab === 'login' ? '' : 'none';
  document.getElementById('form-register').style.display = tab === 'register' ? '' : 'none';
  document.getElementById('tab-login').classList.toggle('active', tab === 'login');
  document.getElementById('tab-register').classList.toggle('active', tab === 'register');
}

// Auto-switch to register if coming from landing CTA
if (sessionStorage.getItem('lumina_tab') === 'register') {
  sessionStorage.removeItem('lumina_tab');
  switchTab('register');
}

async function submitAuth(e, action) {
  e.preventDefault();
  const form = e.target;
  const btn  = document.getElementById('btn-' + action);
  const err  = document.getElementById('err-' + action);
  const data = Object.fromEntries(new FormData(form));
  data.action = action;

  btn.disabled = true;
  btn.textContent = 'Đang xử lý...';
  err.style.display = 'none';

  try {
    const res  = await fetch('/api/auth.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(data) });
    const json = await res.json();
    if (json.error) {
      err.textContent = json.error;
      err.style.display = 'block';
      btn.disabled = false;
      btn.textContent = action === 'login' ? 'Đăng nhập →' : 'Tạo tài khoản miễn phí →';
    } else {
      btn.textContent = '✓ Thành công!';
      setTimeout(() => { window.location.href = json.redirect || '/assessment.php'; }, 300);
    }
  } catch {
    err.textContent = 'Lỗi kết nối, vui lòng thử lại.';
    err.style.display = 'block';
    btn.disabled = false;
    btn.textContent = action === 'login' ? 'Đăng nhập →' : 'Tạo tài khoản miễn phí →';
  }
}
</script>
</body>
</html>
