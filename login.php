<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/tenant.php';
require_once __DIR__ . '/includes/auth.php';

$tenant    = detect_tenant();
$is_main   = !$tenant;

// Already logged in on tenant → dashboard
if (!$is_main) {
    $user = auth_user();
    if ($user && $user['tenant_id'] == $tenant['id']) {
        header('Location: /dashboard.php'); exit;
    }
}

$brand_color = $tenant['primary_color'] ?? '#6C47FF';
$tenant_name = $tenant ? htmlspecialchars($tenant['name']) : 'Lumina Global';
$base_domain = LUMINA_BASE_DOMAIN;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $is_main ? 'Yêu cầu truy cập — Lumina Global' : "Đăng nhập — $tenant_name | Lumina" ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/lumina.css">
  <style>
    :root{--brand:<?= $brand_color ?>}
    body {
      background:#07071a; min-height:100vh;
      display:flex; align-items:center; justify-content:center; padding:40px 16px;
    }
    .bg-orbs{position:fixed;inset:0;pointer-events:none;overflow:hidden}
    .bg-orbs .o1{position:absolute;width:500px;height:500px;border-radius:50%;filter:blur(90px);opacity:.4;background:radial-gradient(circle,rgba(108,71,255,.7),transparent 70%);top:-120px;right:-80px}
    .bg-orbs .o2{position:absolute;width:350px;height:350px;border-radius:50%;filter:blur(80px);opacity:.35;background:radial-gradient(circle,rgba(0,201,177,.5),transparent 70%);bottom:0;left:-60px}

    .auth-glass{
      position:relative;z-index:10;width:100%;max-width:440px;
      background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);
      border-radius:28px;padding:44px 40px;
      backdrop-filter:blur(24px);
      box-shadow:0 32px 80px rgba(0,0,0,.5),0 0 0 1px rgba(108,71,255,.12);
      animation:fadeUp .5s ease both;
    }
    @keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}

    .auth-logo-wrap{text-align:center;margin-bottom:28px}
    .auth-logo-img{height:40px;width:auto}
    .auth-tenant{font-size:.82rem;color:rgba(255,255,255,.4);margin-top:8px}

    /* Tabs */
    .auth-tabs{display:flex;background:rgba(255,255,255,.06);border-radius:100px;padding:4px;margin-bottom:24px;border:1px solid rgba(255,255,255,.08)}
    .auth-tab{flex:1;text-align:center;padding:9px;border-radius:100px;font-weight:600;font-size:.87rem;cursor:pointer;transition:all .2s;color:rgba(255,255,255,.4);border:none;background:transparent;font-family:var(--font)}
    .auth-tab.active{background:rgba(108,71,255,.8);color:#fff;box-shadow:0 2px 12px rgba(108,71,255,.4)}

    /* Form */
    .form-group{margin-bottom:14px}
    .form-label{display:block;font-weight:600;font-size:.83rem;color:rgba(255,255,255,.7);margin-bottom:6px}
    .form-input{
      width:100%;padding:12px 15px;
      background:rgba(255,255,255,.07);border:1.5px solid rgba(255,255,255,.12);
      border-radius:12px;font-size:.93rem;font-family:var(--font);
      color:#fff;transition:border-color .2s,box-shadow .2s;outline:none;
    }
    .form-input::placeholder{color:rgba(255,255,255,.25)}
    .form-input:focus{border-color:rgba(108,71,255,.7);box-shadow:0 0 0 3px rgba(108,71,255,.18);background:rgba(108,71,255,.07)}
    .form-error{color:#f87171;font-size:.82rem;margin-top:4px}

    .btn-auth{
      width:100%;padding:13px;border-radius:100px;font-size:.93rem;font-weight:700;
      cursor:pointer;border:none;background:var(--brand);color:#fff;font-family:var(--font);
      transition:transform .2s,box-shadow .2s;margin-top:6px;position:relative;overflow:hidden;
    }
    .btn-auth:hover{transform:translateY(-1px);box-shadow:0 8px 28px rgba(108,71,255,.5)}
    .btn-auth:disabled{opacity:.55;cursor:not-allowed;transform:none!important}

    /* Pending state */
    .pending-box{
      text-align:center;padding:24px 16px;
      background:rgba(0,201,177,.08);border:1px solid rgba(0,201,177,.25);border-radius:16px;
      display:none;
    }
    .pending-box.show{display:block}
    .pending-icon{font-size:2.5rem;margin-bottom:12px}
    .pending-title{font-size:1rem;font-weight:700;color:#fff;margin-bottom:6px}
    .pending-sub{font-size:.83rem;color:rgba(255,255,255,.5);line-height:1.6}

    /* State error (pending/rejected) */
    .status-alert{
      padding:12px 16px;border-radius:12px;font-size:.85rem;margin-bottom:14px;
      display:none;line-height:1.5;
    }
    .status-alert.show{display:block}
    .status-alert.pending{background:rgba(0,201,177,.1);border:1px solid rgba(0,201,177,.25);color:#5eead4}
    .status-alert.rejected{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.25);color:#f87171}

    /* Main domain: request access form */
    .request-header{text-align:center;margin-bottom:28px}
    .request-badge{display:inline-flex;align-items:center;gap:7px;background:rgba(108,71,255,.15);border:1px solid rgba(108,71,255,.3);color:#a78bfa;border-radius:100px;padding:5px 16px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:14px}
    .request-title{font-size:1.35rem;font-weight:800;color:#fff;margin-bottom:8px}
    .request-sub{font-size:.85rem;color:rgba(255,255,255,.45);line-height:1.6}

    .auth-footer{text-align:center;margin-top:18px;font-size:.78rem;color:rgba(255,255,255,.25);line-height:1.5}
    .auth-footer a{color:rgba(108,71,255,.8);text-decoration:none}
  </style>
</head>
<body>
<div class="bg-orbs"><div class="o1"></div><div class="o2"></div></div>

<div class="auth-glass">
  <div class="auth-logo-wrap">
    <img src="/assets/Logo.png" alt="Lumina" class="auth-logo-img">
    <div class="auth-tenant"><?= $tenant_name ?></div>
  </div>

  <?php if ($is_main): ?>
  <!-- ── MAIN DOMAIN: Login + Request Access tabs ── -->
  <div class="auth-tabs" role="tablist">
    <button class="auth-tab active" id="tab-main-login" onclick="switchMainTab('login')">Đăng nhập</button>
    <button class="auth-tab" id="tab-main-request" onclick="switchMainTab('request')">Yêu cầu truy cập</button>
  </div>

  <!-- Status alerts -->
  <div id="alert-pending-main" class="status-alert pending">
    ⏳ <strong>Tài khoản đang chờ phê duyệt.</strong> Admin sẽ liên hệ khi được duyệt.
  </div>
  <div id="alert-rejected-main" class="status-alert rejected">
    ✕ <strong>Tài khoản không được phê duyệt.</strong> Liên hệ <a href="mailto:hello@<?= $base_domain ?>" style="color:inherit">hello@<?= $base_domain ?></a>.
  </div>

  <!-- LOGIN TAB -->
  <div id="main-login-section">
    <form id="form-main-login" onsubmit="submitMainLogin(event)">
      <div class="form-group">
        <label class="form-label">Email</label>
        <input class="form-input" type="email" name="email" placeholder="you@example.com" required autocomplete="email">
      </div>
      <div class="form-group">
        <label class="form-label">Mật khẩu</label>
        <input class="form-input" type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
      </div>
      <div id="err-main-login" class="form-error" style="display:none;margin-bottom:8px"></div>
      <button class="btn-auth" type="submit" id="btn-main-login">Đăng nhập →</button>
    </form>
  </div>

  <!-- REQUEST ACCESS TAB -->
  <div id="main-request-section" style="display:none">
    <div style="text-align:center;margin-bottom:20px">
      <div class="request-badge" style="display:inline-flex;align-items:center;gap:7px;background:rgba(108,71,255,.15);border:1px solid rgba(108,71,255,.3);color:#a78bfa;border-radius:100px;padding:5px 16px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:10px">✦ Invite Only</div>
      <div style="font-size:.85rem;color:rgba(255,255,255,.45);line-height:1.6">Điền thông tin để admin liên hệ và cấp quyền truy cập.</div>
    </div>
    <div id="req-form-wrap">
      <form id="req-form" onsubmit="submitRequest(event)">
        <div class="form-group">
          <label class="form-label">Họ và tên *</label>
          <input class="form-input" type="text" name="name" placeholder="Nguyễn Văn A" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email công việc *</label>
          <input class="form-input" type="email" name="email" placeholder="you@company.com" required>
        </div>
        <div class="form-group">
          <label class="form-label">Số điện thoại <span style="color:#f87171">*</span></label>
          <input class="form-input" type="tel" name="phone" placeholder="0901 234 567" autocomplete="tel" required>
        </div>
        <div class="form-group">
          <label class="form-label">Tổ chức / Công ty</label>
          <input class="form-input" type="text" name="org" placeholder="Tên công ty">
        </div>
        <div class="form-group">
          <label class="form-label">Bạn muốn dùng Lumina để làm gì?</label>
          <input class="form-input" type="text" name="msg" placeholder="VD: đánh giá đội nhóm, coaching cá nhân...">
        </div>
        <div id="req-error" class="form-error" style="display:none;margin-bottom:10px"></div>
        <button class="btn-auth" type="submit" id="req-btn">Gửi yêu cầu →</button>
      </form>
    </div>
    <div class="pending-box" id="req-success">
      <div class="pending-icon">📬</div>
      <div class="pending-title">Đã gửi yêu cầu thành công!</div>
      <div class="pending-sub">Admin sẽ liên hệ với bạn qua email trong 1–2 ngày làm việc.<br>Cảm ơn bạn đã quan tâm đến Lumina.</div>
    </div>
  </div>

  <p class="auth-footer">© Lumina Global · <a href="/">Trang chủ</a></p>

  <?php else: ?>
  <!-- ── TENANT SUBDOMAIN: Register / Login ── -->

  <div class="auth-tabs" role="tablist">
    <button class="auth-tab active" id="tab-login" onclick="switchTab('login')">Đăng nhập</button>
    <button class="auth-tab" id="tab-register" onclick="switchTab('register')">Đăng ký</button>
  </div>

  <!-- Status alerts (pending / rejected) -->
  <div id="alert-pending" class="status-alert pending">
    ⏳ <strong>Tài khoản đang chờ phê duyệt.</strong> Admin sẽ liên hệ bạn khi được duyệt.
  </div>
  <div id="alert-rejected" class="status-alert rejected">
    ✕ <strong>Tài khoản không được phê duyệt.</strong> Liên hệ <a href="mailto:hello@<?= $base_domain ?>" style="color:inherit">hello@<?= $base_domain ?></a> để biết thêm.
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
    <div id="err-login" class="form-error" style="display:none;margin-bottom:8px"></div>
    <button class="btn-auth" type="submit" id="btn-login">Đăng nhập →</button>
  </form>

  <!-- REGISTER -->
  <form id="form-register" style="display:none" onsubmit="submitAuth(event,'register')">
    <div class="form-group">
      <label class="form-label">Họ và tên</label>
      <input class="form-input" type="text" name="name" placeholder="Nguyễn Văn A" required autocomplete="name">
    </div>
    <div class="form-group">
      <label class="form-label">Số điện thoại <span style="color:#f87171">*</span></label>
      <input class="form-input" type="tel" name="phone" placeholder="0901 234 567" autocomplete="tel" required>
    </div>
    <div class="form-group">
      <label class="form-label">Email</label>
      <input class="form-input" type="email" name="email" placeholder="you@example.com" required autocomplete="email">
    </div>
    <div class="form-group">
      <label class="form-label">Mật khẩu</label>
      <input class="form-input" type="password" name="password" placeholder="Tối thiểu 6 ký tự" required autocomplete="new-password" minlength="6">
    </div>
    <div id="err-register" class="form-error" style="display:none;margin-bottom:8px"></div>
    <button class="btn-auth" type="submit" id="btn-register">Đăng ký →</button>
  </form>

  <!-- Pending state (after register) -->
  <div class="pending-box" id="pending-box">
    <div class="pending-icon">⏳</div>
    <div class="pending-title">Tài khoản đang chờ duyệt</div>
    <div class="pending-sub">
      Đăng ký thành công! Admin sẽ phê duyệt tài khoản của bạn.<br>
      Bạn sẽ nhận thông báo khi được duyệt.
    </div>
  </div>

  <p class="auth-footer">Bằng cách đăng ký, bạn đồng ý với điều khoản sử dụng của Lumina.</p>
  <?php endif; ?>
</div>

<script>
<?php if (!$is_main): ?>
function switchTab(tab) {
  document.getElementById('form-login').style.display    = tab === 'login'    ? '' : 'none';
  document.getElementById('form-register').style.display = tab === 'register' ? '' : 'none';
  document.getElementById('tab-login').classList.toggle('active',    tab === 'login');
  document.getElementById('tab-register').classList.toggle('active', tab === 'register');
  document.getElementById('alert-pending').classList.remove('show');
  document.getElementById('alert-rejected').classList.remove('show');
}

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
  document.getElementById('alert-pending').classList.remove('show');
  document.getElementById('alert-rejected').classList.remove('show');

  try {
    const res  = await fetch('/api/auth.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data) });
    const json = await res.json();

    if (action === 'register' && json.ok && json.pending) {
      // Show pending state
      form.style.display = 'none';
      document.getElementById('tab-login').parentElement.style.display = 'none';
      document.getElementById('pending-box').classList.add('show');
      return;
    }

    if (json.error === 'pending') {
      document.getElementById('alert-pending').classList.add('show');
      btn.disabled = false;
      btn.textContent = 'Đăng nhập →';
      return;
    }

    if (json.error === 'rejected') {
      document.getElementById('alert-rejected').classList.add('show');
      btn.disabled = false;
      btn.textContent = 'Đăng nhập →';
      return;
    }

    if (json.error) {
      err.textContent = json.message || json.error;
      err.style.display = 'block';
      btn.disabled = false;
      btn.textContent = action === 'login' ? 'Đăng nhập →' : 'Đăng ký →';
      return;
    }

    btn.textContent = '✓ Thành công!';
    setTimeout(() => { window.location.href = json.redirect || '/assessment.php'; }, 300);

  } catch {
    err.textContent = 'Lỗi kết nối, vui lòng thử lại.';
    err.style.display = 'block';
    btn.disabled = false;
    btn.textContent = action === 'login' ? 'Đăng nhập →' : 'Đăng ký →';
  }
}

<?php else: ?>
// Main domain
function switchMainTab(tab) {
  document.getElementById('main-login-section').style.display    = tab === 'login'   ? '' : 'none';
  document.getElementById('main-request-section').style.display  = tab === 'request' ? '' : 'none';
  document.getElementById('tab-main-login').classList.toggle('active',   tab === 'login');
  document.getElementById('tab-main-request').classList.toggle('active', tab === 'request');
  document.getElementById('alert-pending-main').classList.remove('show');
  document.getElementById('alert-rejected-main').classList.remove('show');
}

if (location.hash === '#register') { switchMainTab('request'); }

async function submitMainLogin(e) {
  e.preventDefault();
  const form = e.target;
  const btn  = document.getElementById('btn-main-login');
  const err  = document.getElementById('err-main-login');
  document.getElementById('alert-pending-main').classList.remove('show');
  document.getElementById('alert-rejected-main').classList.remove('show');
  btn.disabled = true; btn.textContent = 'Đang xử lý...'; err.style.display = 'none';

  try {
    const res  = await fetch('/api/auth.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'login', email: form.email.value, password: form.password.value })
    });
    const json = await res.json();

    if (json.error === 'pending') {
      document.getElementById('alert-pending-main').classList.add('show');
      btn.disabled = false; btn.textContent = 'Đăng nhập →'; return;
    }
    if (json.error === 'rejected') {
      document.getElementById('alert-rejected-main').classList.add('show');
      btn.disabled = false; btn.textContent = 'Đăng nhập →'; return;
    }
    if (json.error) {
      err.textContent = json.message || json.error;
      err.style.display = 'block';
      btn.disabled = false; btn.textContent = 'Đăng nhập →'; return;
    }
    btn.textContent = '✓ Thành công!';
    setTimeout(() => { window.location.href = json.redirect || '/'; }, 300);
  } catch {
    err.textContent = 'Lỗi kết nối, vui lòng thử lại.';
    err.style.display = 'block';
    btn.disabled = false; btn.textContent = 'Đăng nhập →';
  }
}

async function submitRequest(e) {
  e.preventDefault();
  const form = e.target;
  const btn  = document.getElementById('req-btn');
  const err  = document.getElementById('req-error');
  const data = {
    action : 'request_access',
    name   : form.name.value,
    email  : form.email.value,
    phone  : form.phone.value,
    org    : form.org.value,
    msg    : form.msg.value,
  };

  btn.disabled = true;
  btn.textContent = 'Đang gửi...';
  err.style.display = 'none';

  try {
    const res  = await fetch('/api/auth.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data) });
    const json = await res.json();

    if (json.error) {
      err.textContent = json.error;
      err.style.display = 'block';
      btn.disabled = false;
      btn.textContent = 'Gửi yêu cầu →';
      return;
    }

    document.getElementById('req-form-wrap').style.display = 'none';
    document.getElementById('req-success').classList.add('show');

  } catch {
    err.textContent = 'Lỗi kết nối, vui lòng thử lại.';
    err.style.display = 'block';
    btn.disabled = false;
    btn.textContent = 'Gửi yêu cầu →';
  }
}
<?php endif; ?>
</script>
</body>
</html>
