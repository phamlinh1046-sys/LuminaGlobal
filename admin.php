<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

session_start();

$admin_user = _env('ADMIN_USERNAME', 'admin');
$admin_hash = _env('ADMIN_PASSWORD_HASH', '');

if (isset($_POST['admin_login'])) {
    if ($_POST['username'] === $admin_user && $admin_hash && password_verify($_POST['password'], $admin_hash)) {
        $_SESSION['lumina_admin'] = true;
    } else {
        $login_error = 'Sai tên đăng nhập hoặc mật khẩu';
    }
}
if (isset($_GET['logout'])) { session_destroy(); header('Location: /admin.php'); exit; }

$is_admin = !empty($_SESSION['lumina_admin']);

// ── Admin Actions (JSON POST) ───────────────────────────────
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['admin_login'])) {
    header('Content-Type: application/json');
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $body['action'] ?? '';

    if ($action === 'approve' || $action === 'reject') {
        $user_id = (int)($body['user_id'] ?? 0);
        $status  = $action === 'approve' ? 'approved' : 'rejected';
        $note    = trim($body['note'] ?? '');
        if (!$user_id) { echo json_encode(['error' => 'user_id bắt buộc']); exit; }
        db_exec('UPDATE users SET status=:s, admin_note=:n WHERE id=:id', [':s'=>$status,':n'=>$note,':id'=>$user_id]);

        if ($action === 'approve') {
            require_once __DIR__ . '/includes/auth.php';
            $u = db_row('SELECT u.*,t.slug,t.name AS tenant_name FROM users u JOIN tenants t ON t.id=u.tenant_id WHERE u.id=:id', [':id'=>$user_id]);
            if ($u) {
                $url  = 'https://' . $u['slug'] . '.' . LUMINA_BASE_DOMAIN . '/login.php';
                $html = "<div style='font-family:Inter,sans-serif;max-width:560px;margin:0 auto;background:#07071a;color:#fff;padding:32px;border-radius:16px'>
                  <h2 style='color:#00C9B1;margin-bottom:8px'>✅ Tài khoản đã được phê duyệt!</h2>
                  <p style='color:rgba(255,255,255,.7);margin-bottom:20px'>Xin chào {$u['name']},</p>
                  <p style='color:rgba(255,255,255,.6);line-height:1.7;margin-bottom:24px'>Tài khoản Lumina của bạn tại <strong style='color:#fff'>{$u['tenant_name']}</strong> đã được phê duyệt. Bạn có thể đăng nhập và bắt đầu hành trình ngay bây giờ.</p>
                  <a href='{$url}' style='display:inline-block;padding:12px 28px;background:#6C47FF;color:#fff;border-radius:100px;text-decoration:none;font-weight:700'>Đăng nhập ngay →</a>
                  <p style='color:rgba(255,255,255,.3);font-size:.8rem;margin-top:24px'>Lumina Global · Đo lường & Chuyển hoá Hành vi</p>
                </div>";
                send_email($u['email'], $u['name'], '✅ Tài khoản Lumina đã được phê duyệt', $html);
            }
        }
        echo json_encode(['ok' => true]); exit;
    }

    if ($action === 'close_request') {
        $id = (int)($body['id'] ?? 0);
        if ($id) db_exec('UPDATE access_requests SET status="reviewed" WHERE id=:id', [':id'=>$id]);
        echo json_encode(['ok' => true]); exit;
    }

    echo json_encode(['error' => 'Unknown action']); exit;
}

// ── Load data ───────────────────────────────────────────────
if ($is_admin) {
    $pending_users   = db_rows("SELECT u.*,t.name AS tenant_name,t.slug FROM users u JOIN tenants t ON t.id=u.tenant_id WHERE u.status='pending' ORDER BY u.created_at DESC");
    $all_users       = db_rows("SELECT u.*,t.name AS tenant_name,t.slug FROM users u JOIN tenants t ON t.id=u.tenant_id ORDER BY u.created_at DESC LIMIT 200");
    $access_requests = db_rows("SELECT * FROM access_requests WHERE status='new' ORDER BY created_at DESC");
    $stats = [
        'total'    => db_row('SELECT COUNT(*) AS c FROM users')['c'] ?? 0,
        'pending'  => db_row("SELECT COUNT(*) AS c FROM users WHERE status='pending'")['c'] ?? 0,
        'approved' => db_row("SELECT COUNT(*) AS c FROM users WHERE status='approved'")['c'] ?? 0,
        'requests' => db_row("SELECT COUNT(*) AS c FROM access_requests WHERE status='new'")['c'] ?? 0,
    ];
}

function time_ago(int $ts): string {
    $diff = time() - $ts;
    if ($diff < 60)   return 'vừa xong';
    if ($diff < 3600) return floor($diff/60) . ' phút trước';
    if ($diff < 86400) return floor($diff/3600) . ' giờ trước';
    return date('d/m/Y H:i', $ts);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — Lumina Global</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Inter',-apple-system,sans-serif;background:#07071a;color:#fff;min-height:100vh;-webkit-font-smoothing:antialiased}

    /* ── Login ── */
    .login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
      background:radial-gradient(ellipse 60% 60% at 70% 30%,rgba(108,71,255,.2),transparent)}
    .login-box{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);border-radius:24px;padding:44px 40px;width:380px;backdrop-filter:blur(20px);box-shadow:0 32px 80px rgba(0,0,0,.4)}
    .login-logo{text-align:center;margin-bottom:28px}
    .login-logo img{height:40px}
    .login-title{font-size:1rem;font-weight:700;text-align:center;margin-bottom:24px;color:rgba(255,255,255,.6);letter-spacing:.3px}
    .login-group{margin-bottom:14px}
    .login-label{display:block;font-size:.82rem;font-weight:600;color:rgba(255,255,255,.6);margin-bottom:6px}
    .login-input{width:100%;padding:12px 14px;background:rgba(255,255,255,.08);border:1.5px solid rgba(255,255,255,.12);border-radius:12px;color:#fff;font-size:.93rem;font-family:inherit;outline:none;transition:border-color .2s}
    .login-input:focus{border-color:rgba(108,71,255,.7);box-shadow:0 0 0 3px rgba(108,71,255,.15)}
    .login-btn{width:100%;padding:13px;border-radius:100px;background:#6C47FF;color:#fff;font-weight:700;font-size:.95rem;border:none;cursor:pointer;margin-top:6px;font-family:inherit;transition:box-shadow .2s}
    .login-btn:hover{box-shadow:0 8px 24px rgba(108,71,255,.4)}
    .login-error{color:#f87171;font-size:.83rem;margin-bottom:12px;text-align:center;padding:10px;background:rgba(248,113,113,.1);border-radius:10px;border:1px solid rgba(248,113,113,.2)}

    /* ── Navbar ── */
    .admin-nav{background:rgba(255,255,255,.03);border-bottom:1px solid rgba(255,255,255,.07);padding:13px 32px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;backdrop-filter:blur(12px)}
    .admin-nav-left{display:flex;align-items:center;gap:12px}
    .admin-nav-left img{height:28px}
    .admin-nav-title{font-size:.82rem;font-weight:700;color:rgba(255,255,255,.4);letter-spacing:.3px}
    .admin-logout{padding:6px 16px;border-radius:100px;background:rgba(255,255,255,.07);color:rgba(255,255,255,.5);border:1px solid rgba(255,255,255,.1);cursor:pointer;font-size:.8rem;font-family:inherit;text-decoration:none;transition:all .2s}
    .admin-logout:hover{background:rgba(255,255,255,.15);color:#fff}

    /* ── Main ── */
    .admin-main{max-width:1200px;margin:0 auto;padding:32px 28px}

    /* ── Stats ── */
    .stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:36px}
    @media(max-width:700px){.stats-row{grid-template-columns:repeat(2,1fr)}}
    .stat-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:18px;padding:22px 20px;display:flex;align-items:center;gap:16px}
    .stat-icon{font-size:1.8rem;flex-shrink:0}
    .stat-num{font-size:2rem;font-weight:900;line-height:1;letter-spacing:-.02em}
    .stat-lbl{font-size:.72rem;color:rgba(255,255,255,.35);margin-top:4px;text-transform:uppercase;letter-spacing:.7px;font-weight:600}
    .c-purple{color:#a78bfa} .c-yellow{color:#fbbf24} .c-green{color:#34d399} .c-teal{color:#2dd4bf}

    /* ── Section heading ── */
    .sec-head{display:flex;align-items:center;gap:10px;margin-bottom:16px}
    .sec-title{font-size:1rem;font-weight:700}
    .sec-badge{display:inline-flex;align-items:center;justify-content:center;min-width:22px;height:22px;border-radius:100px;font-size:.72rem;font-weight:800;padding:0 7px}
    .sb-yellow{background:#fbbf24;color:#07071a}
    .sb-teal{background:#2dd4bf;color:#07071a}
    .section{margin-bottom:40px}

    /* ── User cards (pending) ── */
    .user-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(360px,1fr));gap:14px}
    .user-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:18px;padding:22px;transition:border-color .2s}
    .user-card:hover{border-color:rgba(108,71,255,.35)}
    .uc-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px}
    .uc-avatar{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#6C47FF,#00C9B1);display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:800;color:#fff;flex-shrink:0}
    .uc-name{font-size:.97rem;font-weight:700;color:#fff;margin-bottom:3px}
    .uc-time{font-size:.75rem;color:rgba(255,255,255,.3)}
    .uc-tenant-pill{background:rgba(108,71,255,.15);border:1px solid rgba(108,71,255,.25);color:#a78bfa;border-radius:100px;padding:3px 10px;font-size:.73rem;font-weight:700;white-space:nowrap}
    .uc-fields{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px}
    .uc-field{background:rgba(255,255,255,.04);border-radius:10px;padding:10px 12px}
    .uc-field-lbl{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:rgba(255,255,255,.3);margin-bottom:4px}
    .uc-field-val{font-size:.87rem;color:#fff;font-weight:500;word-break:break-all}
    .uc-field-val.phone{color:#2dd4bf;font-weight:700}
    .uc-field.full{grid-column:1/-1}
    .uc-note{margin-bottom:12px}
    .uc-note input{width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:9px 12px;color:#fff;font-size:.83rem;font-family:inherit;outline:none}
    .uc-note input:focus{border-color:rgba(108,71,255,.5)}
    .uc-note input::placeholder{color:rgba(255,255,255,.25)}
    .uc-actions{display:flex;gap:8px}
    .btn-approve{flex:1;padding:9px;border-radius:100px;background:rgba(52,211,153,.15);color:#34d399;border:1px solid rgba(52,211,153,.3);cursor:pointer;font-size:.83rem;font-weight:700;font-family:inherit;transition:all .2s;text-align:center}
    .btn-approve:hover{background:rgba(52,211,153,.3);transform:translateY(-1px)}
    .btn-reject{flex:1;padding:9px;border-radius:100px;background:rgba(248,113,113,.1);color:#f87171;border:1px solid rgba(248,113,113,.25);cursor:pointer;font-size:.83rem;font-weight:700;font-family:inherit;transition:all .2s;text-align:center}
    .btn-reject:hover{background:rgba(248,113,113,.2);transform:translateY(-1px)}

    /* ── Access Request cards ── */
    .req-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px}
    .req-card{background:rgba(45,212,191,.05);border:1px solid rgba(45,212,191,.18);border-radius:18px;padding:20px}
    .rc-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px}
    .rc-name{font-size:.97rem;font-weight:700;color:#fff}
    .rc-time{font-size:.72rem;color:rgba(255,255,255,.3)}
    .rc-fields{display:flex;flex-direction:column;gap:8px;margin-bottom:14px}
    .rc-row{display:flex;align-items:center;gap:8px;font-size:.84rem}
    .rc-icon{font-size:.9rem;flex-shrink:0;width:20px;text-align:center}
    .rc-val{color:rgba(255,255,255,.75)}
    .rc-val.highlight{color:#2dd4bf;font-weight:600}
    .rc-purpose{background:rgba(255,255,255,.04);border-radius:10px;padding:10px 12px;font-size:.82rem;color:rgba(255,255,255,.55);line-height:1.5;font-style:italic;margin-bottom:12px}
    .btn-done{width:100%;padding:8px;border-radius:100px;background:rgba(255,255,255,.07);color:rgba(255,255,255,.5);border:1px solid rgba(255,255,255,.1);cursor:pointer;font-size:.8rem;font-weight:600;font-family:inherit;transition:all .2s}
    .btn-done:hover{background:rgba(255,255,255,.14);color:#fff}

    /* ── All users table ── */
    .table-wrap{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:18px;overflow:hidden;overflow-x:auto}
    table{width:100%;border-collapse:collapse;min-width:700px}
    th{padding:11px 16px;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:rgba(255,255,255,.35);border-bottom:1px solid rgba(255,255,255,.07);background:rgba(255,255,255,.02);white-space:nowrap}
    td{padding:13px 16px;font-size:.85rem;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:middle}
    tr:last-child td{border-bottom:none}
    tr:hover td{background:rgba(255,255,255,.02)}
    .td-name{font-weight:600;color:#fff}
    .td-email{color:rgba(255,255,255,.5);font-size:.83rem}
    .td-phone{color:#2dd4bf;font-weight:600;font-size:.83rem}
    .td-tenant{color:#a78bfa;font-size:.8rem;font-weight:600}
    .td-date{color:rgba(255,255,255,.3);font-size:.78rem;white-space:nowrap}
    .td-note{color:rgba(255,255,255,.4);font-size:.78rem;font-style:italic;max-width:140px}
    .sp{display:inline-flex;align-items:center;padding:3px 10px;border-radius:100px;font-size:.73rem;font-weight:700;white-space:nowrap}
    .sp-pending{background:rgba(251,191,36,.12);color:#fbbf24;border:1px solid rgba(251,191,36,.2)}
    .sp-approved{background:rgba(52,211,153,.1);color:#34d399;border:1px solid rgba(52,211,153,.2)}
    .sp-rejected{background:rgba(248,113,113,.1);color:#f87171;border:1px solid rgba(248,113,113,.2)}
    .tbl-btn{padding:4px 12px;border-radius:100px;font-size:.75rem;font-weight:600;cursor:pointer;font-family:inherit;border:none;transition:background .2s}
    .tbl-approve{background:rgba(52,211,153,.12);color:#34d399}
    .tbl-approve:hover{background:rgba(52,211,153,.25)}
    .tbl-reject{background:rgba(248,113,113,.08);color:#f87171}
    .tbl-reject:hover{background:rgba(248,113,113,.2)}

    .empty-card{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:18px;padding:40px;text-align:center;color:rgba(255,255,255,.25);font-size:.9rem}
    .empty-icon{font-size:2rem;margin-bottom:10px}

    /* Toast */
    .toast{position:fixed;bottom:24px;right:24px;background:#1a1a3e;border:1px solid rgba(108,71,255,.4);color:#fff;padding:12px 20px;border-radius:12px;font-size:.87rem;font-weight:600;transform:translateY(80px);opacity:0;transition:all .3s;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,.4)}
    .toast.show{transform:none;opacity:1}
  </style>
</head>
<body>

<?php if (!$is_admin): ?>
<!-- ── LOGIN ── -->
<div class="login-wrap">
  <div class="login-box">
    <div class="login-logo"><img src="/assets/Logo.png" alt="Lumina"></div>
    <div class="login-title">ADMIN PANEL</div>
    <?php if (!empty($login_error)): ?>
    <div class="login-error"><?= htmlspecialchars($login_error) ?></div>
    <?php endif; ?>
    <?php if (!$admin_hash): ?>
    <div style="color:#fbbf24;font-size:.8rem;padding:12px;background:rgba(251,191,36,.08);border-radius:10px;margin-bottom:16px;line-height:1.5">
      ⚠ Chưa cấu hình <code>ADMIN_PASSWORD_HASH</code> trong .env
    </div>
    <?php endif; ?>
    <form method="post">
      <div class="login-group">
        <label class="login-label">Tên đăng nhập</label>
        <input class="login-input" type="text" name="username" required autocomplete="username">
      </div>
      <div class="login-group">
        <label class="login-label">Mật khẩu</label>
        <input class="login-input" type="password" name="password" required autocomplete="current-password">
      </div>
      <button class="login-btn" type="submit" name="admin_login">Đăng nhập →</button>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ── ADMIN PANEL ── -->
<nav class="admin-nav">
  <div class="admin-nav-left">
    <img src="/assets/Logo.png" alt="Lumina">
    <span class="admin-nav-title">ADMIN PANEL</span>
  </div>
  <a href="/admin.php?logout=1" class="admin-logout">Đăng xuất</a>
</nav>

<div class="admin-main">

  <!-- Stats -->
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-icon">👥</div>
      <div><div class="stat-num c-purple"><?= $stats['total'] ?></div><div class="stat-lbl">Tổng users</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">⏳</div>
      <div><div class="stat-num c-yellow"><?= $stats['pending'] ?></div><div class="stat-lbl">Chờ duyệt</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">✅</div>
      <div><div class="stat-num c-green"><?= $stats['approved'] ?></div><div class="stat-lbl">Đã duyệt</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">📬</div>
      <div><div class="stat-num c-teal"><?= $stats['requests'] ?></div><div class="stat-lbl">Access requests</div></div>
    </div>
  </div>

  <!-- ── Access Requests ── -->
  <?php if (!empty($access_requests)): ?>
  <div class="section">
    <div class="sec-head">
      <span class="sec-title">📬 Yêu cầu truy cập mới</span>
      <span class="sec-badge sb-teal"><?= count($access_requests) ?></span>
    </div>
    <div class="req-cards">
      <?php foreach ($access_requests as $r): ?>
      <div class="req-card" id="req-<?= $r['id'] ?>">
        <div class="rc-top">
          <div class="rc-name"><?= htmlspecialchars($r['name']) ?></div>
          <div class="rc-time"><?= time_ago((int)$r['created_at']) ?></div>
        </div>
        <div class="rc-fields">
          <div class="rc-row"><span class="rc-icon">📧</span><span class="rc-val"><?= htmlspecialchars($r['email']) ?></span></div>
          <div class="rc-row"><span class="rc-icon">📱</span><span class="rc-val highlight"><?= htmlspecialchars($r['phone'] ?? '—') ?></span></div>
          <?php if (!empty($r['organization'])): ?>
          <div class="rc-row"><span class="rc-icon">🏢</span><span class="rc-val"><?= htmlspecialchars($r['organization']) ?></span></div>
          <?php endif; ?>
        </div>
        <?php if (!empty($r['message'])): ?>
        <div class="rc-purpose">"<?= htmlspecialchars($r['message']) ?>"</div>
        <?php endif; ?>
        <button class="btn-done" onclick="closeRequest(<?= $r['id'] ?>)">✓ Đã xử lý</button>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── Pending Users ── -->
  <div class="section">
    <div class="sec-head">
      <span class="sec-title">⏳ Chờ phê duyệt</span>
      <?php if ($stats['pending'] > 0): ?>
      <span class="sec-badge sb-yellow"><?= $stats['pending'] ?></span>
      <?php endif; ?>
    </div>

    <?php if (empty($pending_users)): ?>
    <div class="empty-card"><div class="empty-icon">✓</div>Không có user nào đang chờ duyệt</div>
    <?php else: ?>
    <div class="user-cards">
      <?php foreach ($pending_users as $u):
        $initials = mb_strtoupper(mb_substr($u['name'], 0, 1));
      ?>
      <div class="user-card" id="user-<?= $u['id'] ?>">
        <div class="uc-top">
          <div style="display:flex;align-items:center;gap:10px;flex:1">
            <div class="uc-avatar"><?= $initials ?></div>
            <div>
              <div class="uc-name"><?= htmlspecialchars($u['name']) ?></div>
              <div class="uc-time"><?= time_ago((int)$u['created_at']) ?></div>
            </div>
          </div>
          <div class="uc-tenant-pill"><?= htmlspecialchars($u['tenant_name']) ?></div>
        </div>

        <div class="uc-fields">
          <div class="uc-field">
            <div class="uc-field-lbl">📧 Email</div>
            <div class="uc-field-val"><?= htmlspecialchars($u['email']) ?></div>
          </div>
          <div class="uc-field">
            <div class="uc-field-lbl">📱 Số điện thoại</div>
            <div class="uc-field-val phone"><?= htmlspecialchars($u['phone'] ?: '—') ?></div>
          </div>
          <div class="uc-field">
            <div class="uc-field-lbl">🗓 Đăng ký lúc</div>
            <div class="uc-field-val"><?= date('d/m/Y H:i', (int)$u['created_at']) ?></div>
          </div>
          <div class="uc-field">
            <div class="uc-field-lbl">🏠 Subdomain</div>
            <div class="uc-field-val"><?= htmlspecialchars($u['slug']) ?>.luminaglobal.info.vn</div>
          </div>
        </div>

        <div class="uc-note">
          <input type="text" id="note-<?= $u['id'] ?>" placeholder="Ghi chú cho tài khoản này...">
        </div>
        <div class="uc-actions">
          <button class="btn-approve" onclick="userAction(<?= $u['id'] ?>,'approve')">✓ Phê duyệt & gửi email</button>
          <button class="btn-reject"  onclick="userAction(<?= $u['id'] ?>,'reject')">✕ Từ chối</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- ── All Users Table ── -->
  <div class="section">
    <div class="sec-head">
      <span class="sec-title">👥 Tất cả người dùng</span>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Họ tên</th>
            <th>Email</th>
            <th>SĐT</th>
            <th>Tenant</th>
            <th>Trạng thái</th>
            <th>Đăng ký</th>
            <th>Ghi chú</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($all_users)): ?>
          <tr><td colspan="8" style="text-align:center;padding:32px;color:rgba(255,255,255,.2)">Chưa có user nào</td></tr>
          <?php else: ?>
          <?php foreach ($all_users as $u):
            $sp_class = ['pending'=>'sp-pending','approved'=>'sp-approved','rejected'=>'sp-rejected'][$u['status']] ?? 'sp-pending';
            $sp_label = ['pending'=>'⏳ Chờ duyệt','approved'=>'✓ Đã duyệt','rejected'=>'✕ Từ chối'][$u['status']] ?? $u['status'];
          ?>
          <tr id="u-<?= $u['id'] ?>">
            <td class="td-name"><?= htmlspecialchars($u['name']) ?></td>
            <td class="td-email"><?= htmlspecialchars($u['email']) ?></td>
            <td class="td-phone"><?= htmlspecialchars($u['phone'] ?: '—') ?></td>
            <td class="td-tenant"><?= htmlspecialchars($u['tenant_name']) ?></td>
            <td><span class="sp <?= $sp_class ?>" id="sp-<?= $u['id'] ?>"><?= $sp_label ?></span></td>
            <td class="td-date"><?= date('d/m/Y H:i', (int)$u['created_at']) ?></td>
            <td class="td-note"><?= htmlspecialchars($u['admin_note'] ?: '') ?></td>
            <td style="white-space:nowrap">
              <?php if ($u['status'] !== 'approved'): ?>
              <button class="tbl-btn tbl-approve" onclick="userAction(<?= $u['id'] ?>,'approve')">Duyệt</button>
              <?php endif; ?>
              <?php if ($u['status'] !== 'rejected'): ?>
              <button class="tbl-btn tbl-reject" onclick="userAction(<?= $u['id'] ?>,'reject')" style="margin-left:4px">Từ chối</button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div><!-- /admin-main -->
<div class="toast" id="toast"></div>

<script>
function showToast(msg, ok = true) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.borderColor = ok ? 'rgba(52,211,153,.4)' : 'rgba(248,113,113,.4)';
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}

async function userAction(userId, action) {
  const note = document.getElementById('note-' + userId)?.value || '';
  const res  = await fetch('/admin.php', {
    method: 'POST', headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ action, user_id: userId, note })
  });
  const json = await res.json();
  if (!json.ok) { showToast('Có lỗi xảy ra', false); return; }

  // Remove from pending cards
  const card = document.getElementById('user-' + userId);
  if (card) { card.style.opacity = '0'; card.style.transform = 'scale(.95)'; card.style.transition = 'all .3s'; setTimeout(() => card.remove(), 300); }

  // Update status pill in table
  const pill = document.getElementById('sp-' + userId);
  if (pill) {
    pill.className = 'sp ' + (action === 'approve' ? 'sp-approved' : 'sp-rejected');
    pill.textContent = action === 'approve' ? '✓ Đã duyệt' : '✕ Từ chối';
  }

  // Update action buttons in table
  const row = document.getElementById('u-' + userId);
  if (row) {
    const btns = row.querySelectorAll('.tbl-btn');
    btns.forEach(b => {
      if (action === 'approve' && b.classList.contains('tbl-approve')) b.remove();
      if (action === 'reject'  && b.classList.contains('tbl-reject'))  b.remove();
    });
  }

  showToast(action === 'approve' ? '✓ Đã phê duyệt & gửi email thông báo' : '✕ Đã từ chối tài khoản');
}

async function closeRequest(id) {
  await fetch('/admin.php', {
    method: 'POST', headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ action: 'close_request', id })
  });
  const card = document.getElementById('req-' + id);
  if (card) { card.style.opacity='0'; card.style.transition='opacity .3s'; setTimeout(()=>card.remove(),300); }
  showToast('✓ Đã đánh dấu đã xử lý');
}
</script>
<?php endif; ?>
</body>
</html>
