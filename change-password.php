<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/tenant.php';
require_once __DIR__ . '/includes/auth.php';

$tenant = detect_tenant();
if (!$tenant) { header('Location: /login.php'); exit; }

$user = auth_require($tenant['id']);
$brand_color = $tenant['primary_color'] ?? '#6C47FF';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đổi mật khẩu — Lumina</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/lumina.css">
  <style>
    :root { --brand: <?= $brand_color ?> }
    body {
      background: #07071a; min-height: 100vh;
      display: flex; align-items: center; justify-content: center; padding: 40px 16px;
    }
    .bg-orbs { position: fixed; inset: 0; pointer-events: none; overflow: hidden; }
    .bg-orbs .o1 { position: absolute; width: 400px; height: 400px; border-radius: 50%; filter: blur(90px); opacity: .3; background: radial-gradient(circle, rgba(108,71,255,.7), transparent 70%); top: -100px; right: -60px; }
    .bg-orbs .o2 { position: absolute; width: 300px; height: 300px; border-radius: 50%; filter: blur(80px); opacity: .25; background: radial-gradient(circle, rgba(0,201,177,.5), transparent 70%); bottom: 0; left: -40px; }

    .card {
      position: relative; z-index: 10; width: 100%; max-width: 420px;
      background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.12);
      border-radius: 28px; padding: 44px 40px;
      backdrop-filter: blur(24px);
      box-shadow: 0 32px 80px rgba(0,0,0,.5), 0 0 0 1px rgba(108,71,255,.12);
      animation: fadeUp .5s ease both;
    }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(20px) } to { opacity: 1; transform: none } }

    .back-link {
      display: inline-flex; align-items: center; gap: 6px;
      color: rgba(255,255,255,.4); font-size: .82rem; text-decoration: none;
      margin-bottom: 28px; transition: color .2s;
    }
    .back-link:hover { color: rgba(255,255,255,.8); }

    .card-icon { font-size: 2rem; margin-bottom: 12px; }
    .card-title { font-size: 1.4rem; font-weight: 800; color: #fff; margin-bottom: 6px; }
    .card-sub { font-size: .85rem; color: rgba(255,255,255,.45); margin-bottom: 28px; line-height: 1.5; }

    .form-group { margin-bottom: 16px; }
    .form-label { display: block; font-weight: 600; font-size: .83rem; color: rgba(255,255,255,.7); margin-bottom: 6px; }
    .form-input {
      width: 100%; padding: 12px 15px;
      background: rgba(255,255,255,.07); border: 1.5px solid rgba(255,255,255,.12);
      border-radius: 12px; font-size: .93rem; font-family: var(--font);
      color: #fff; transition: border-color .2s, box-shadow .2s; outline: none;
    }
    .form-input::placeholder { color: rgba(255,255,255,.25); }
    .form-input:focus { border-color: rgba(108,71,255,.7); box-shadow: 0 0 0 3px rgba(108,71,255,.18); background: rgba(108,71,255,.07); }

    .form-error { color: #f87171; font-size: .82rem; margin-top: 4px; display: none; }
    .form-error.show { display: block; }

    .btn-submit {
      width: 100%; padding: 13px; border-radius: 100px; font-size: .93rem; font-weight: 700;
      cursor: pointer; border: none; background: var(--brand); color: #fff;
      font-family: var(--font); transition: transform .2s, box-shadow .2s; margin-top: 6px;
    }
    .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 8px 28px rgba(108,71,255,.5); }
    .btn-submit:disabled { opacity: .55; cursor: not-allowed; transform: none !important; }

    .success-box {
      text-align: center; padding: 24px;
      background: rgba(0,201,177,.08); border: 1px solid rgba(0,201,177,.25);
      border-radius: 16px; display: none;
    }
    .success-box.show { display: block; }
    .success-icon { font-size: 2.5rem; margin-bottom: 10px; }
    .success-title { font-size: 1rem; font-weight: 700; color: #fff; margin-bottom: 6px; }
    .success-sub { font-size: .82rem; color: rgba(255,255,255,.5); }

    .hint {
      margin-top: 16px; padding: 12px 14px;
      background: rgba(245,166,35,.08); border: 1px solid rgba(245,166,35,.2);
      border-radius: 10px; font-size: .8rem; color: rgba(255,255,255,.5); line-height: 1.5;
    }
    .hint strong { color: #F5A623; }
  </style>
</head>
<body>
<div class="bg-orbs"><div class="o1"></div><div class="o2"></div></div>

<div class="card">
  <a href="/dashboard.php" class="back-link">← Về Dashboard</a>

  <div id="form-section">
    <div class="card-icon">🔑</div>
    <div class="card-title">Đổi mật khẩu</div>
    <div class="card-sub">Xin chào <strong style="color:#fff"><?= htmlspecialchars($user['name']) ?></strong> — nhập mật khẩu hiện tại và mật khẩu mới.</div>

    <form id="change-form" onsubmit="submitChange(event)">
      <div class="form-group">
        <label class="form-label">Mật khẩu hiện tại</label>
        <input class="form-input" type="password" name="current_password" placeholder="••••••••" required autocomplete="current-password">
      </div>
      <div class="form-group">
        <label class="form-label">Mật khẩu mới</label>
        <input class="form-input" type="password" name="new_password" id="new_pass" placeholder="Tối thiểu 6 ký tự" required autocomplete="new-password" minlength="6">
      </div>
      <div class="form-group">
        <label class="form-label">Xác nhận mật khẩu mới</label>
        <input class="form-input" type="password" name="confirm_password" id="confirm_pass" placeholder="Nhập lại mật khẩu mới" required autocomplete="new-password" minlength="6">
      </div>
      <div class="form-error" id="err-msg"></div>
      <button class="btn-submit" type="submit" id="btn-submit">Đổi mật khẩu →</button>
    </form>

    <div class="hint">
      <strong>Lưu ý:</strong> Sau khi đổi mật khẩu, tất cả các thiết bị khác sẽ bị đăng xuất. Chỉ thiết bị hiện tại giữ nguyên phiên.
    </div>
  </div>

  <div class="success-box" id="success-box">
    <div class="success-icon">✅</div>
    <div class="success-title">Đổi mật khẩu thành công!</div>
    <div class="success-sub">Đang chuyển về Dashboard...</div>
  </div>
</div>

<script>
async function submitChange(e) {
  e.preventDefault();
  const form = e.target;
  const btn  = document.getElementById('btn-submit');
  const err  = document.getElementById('err-msg');

  const newPass     = document.getElementById('new_pass').value;
  const confirmPass = document.getElementById('confirm_pass').value;

  err.classList.remove('show');

  if (newPass !== confirmPass) {
    err.textContent = 'Mật khẩu xác nhận không khớp';
    err.classList.add('show'); return;
  }

  btn.disabled = true;
  btn.textContent = 'Đang xử lý...';

  try {
    const res  = await fetch('/api/auth.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action:           'change_password',
        current_password: form.current_password.value,
        new_password:     newPass,
        confirm_password: confirmPass,
      })
    });
    const json = await res.json();

    if (json.error) {
      err.textContent = json.error;
      err.classList.add('show');
      btn.disabled = false;
      btn.textContent = 'Đổi mật khẩu →';
      return;
    }

    document.getElementById('form-section').style.display = 'none';
    document.getElementById('success-box').classList.add('show');
    setTimeout(() => { window.location.href = '/dashboard.php'; }, 2000);

  } catch {
    err.textContent = 'Lỗi kết nối, vui lòng thử lại.';
    err.classList.add('show');
    btn.disabled = false;
    btn.textContent = 'Đổi mật khẩu →';
  }
}
</script>
</body>
</html>
