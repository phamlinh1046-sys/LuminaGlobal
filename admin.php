<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

session_start();

// ── Admin Auth ──────────────────────────────────────────────
$admin_user = _env('ADMIN_USERNAME', 'admin');
$admin_hash = _env('ADMIN_PASSWORD_HASH', '');

if (isset($_POST['admin_login'])) {
    if ($_POST['username'] === $admin_user && $admin_hash && password_verify($_POST['password'], $admin_hash)) {
        $_SESSION['lumina_admin'] = true;
    } else {
        $login_error = 'Sai tên đăng nhập hoặc mật khẩu';
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: /admin.php'); exit;
}

$is_admin = !empty($_SESSION['lumina_admin']);

// ── Admin Actions (POST JSON) ───────────────────────────────
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

        // Email user if approved
        if ($action === 'approve') {
            require_once __DIR__ . '/includes/auth.php';
            $u = db_row('SELECT u.*,t.slug,t.name AS tenant_name FROM users u JOIN tenants t ON t.id=u.tenant_id WHERE u.id=:id', [':id'=>$user_id]);
            if ($u) {
                $url  = 'https://' . $u['slug'] . '.' . LUMINA_BASE_DOMAIN . '/login.php';
                $html = "
                <div style='font-family:Inter,sans-serif;max-width:560px;margin:0 auto;background:#07071a;color:#fff;padding:32px;border-radius:16px'>
                  <h2 style='color:#00C9B1;margin-bottom:8px'>✅ Tài khoản đã được phê duyệt!</h2>
                  <p style='color:rgba(255,255,255,.7);margin-bottom:24px'>Xin chào {$u['name']},</p>
                  <p style='color:rgba(255,255,255,.6);line-height:1.7;margin-bottom:24px'>Tài khoản Lumina của bạn tại <strong style='color:#fff'>{$u['tenant_name']}</strong> đã được phê duyệt. Bạn có thể đăng nhập và bắt đầu hành trình phát triển bản thân ngay bây giờ.</p>
                  <a href='{$url}' style='display:inline-block;padding:12px 28px;background:#6C47FF;color:#fff;border-radius:100px;text-decoration:none;font-weight:700'>Đăng nhập ngay →</a>
                  <p style='color:rgba(255,255,255,.3);font-size:.8rem;margin-top:24px'>Lumina Global · Đo lường & Chuyển hoá Hành vi</p>
                </div>";
                send_email($u['email'], $u['name'], '✅ Tài khoản Lumina đã được phê duyệt', $html);
            }
        }
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'close_request') {
        $id = (int)($body['id'] ?? 0);
        if ($id) db_exec('UPDATE access_requests SET status="reviewed" WHERE id=:id', [':id'=>$id]);
        echo json_encode(['ok' => true]);
        exit;
    }

    echo json_encode(['error' => 'Unknown action']); exit;
}

// ── Load data ───────────────────────────────────────────────
if ($is_admin) {
    $pending_users   = db_rows("SELECT u.*,t.name AS tenant_name,t.slug FROM users u JOIN tenants t ON t.id=u.tenant_id WHERE u.status='pending' ORDER BY u.created_at DESC");
    $all_users       = db_rows("SELECT u.*,t.name AS tenant_name,t.slug FROM users u JOIN tenants t ON t.id=u.tenant_id ORDER BY u.created_at DESC LIMIT 100");
    $access_requests = db_rows("SELECT * FROM access_requests WHERE status='new' ORDER BY created_at DESC");
    $stats = [
        'total'    => db_row('SELECT COUNT(*) AS c FROM users')['c'] ?? 0,
        'pending'  => db_row("SELECT COUNT(*) AS c FROM users WHERE status='pending'")['c'] ?? 0,
        'approved' => db_row("SELECT COUNT(*) AS c FROM users WHERE status='approved'")['c'] ?? 0,
        'requests' => db_row("SELECT COUNT(*) AS c FROM access_requests WHERE status='new'")['c'] ?? 0,
    ];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — Lumina Global</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Inter',-apple-system,sans-serif;background:#07071a;color:#fff;min-height:100vh;-webkit-font-smoothing:antialiased}

    /* Login */
    .login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
    .login-box{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);border-radius:24px;padding:40px;width:360px;backdrop-filter:blur(20px)}
    .login-logo{text-align:center;margin-bottom:28px}
    .login-logo img{height:36px}
    .login-title{font-size:1.1rem;font-weight:700;text-align:center;margin-bottom:24px;color:rgba(255,255,255,.8)}
    .login-group{margin-bottom:14px}
    .login-label{display:block;font-size:.82rem;font-weight:600;color:rgba(255,255,255,.6);margin-bottom:5px}
    .login-input{width:100%;padding:11px 14px;background:rgba(255,255,255,.08);border:1.5px solid rgba(255,255,255,.12);border-radius:10px;color:#fff;font-size:.92rem;font-family:inherit;outline:none}
    .login-input:focus{border-color:rgba(108,71,255,.7)}
    .login-btn{width:100%;padding:12px;border-radius:100px;background:#6C47FF;color:#fff;font-weight:700;font-size:.9rem;border:none;cursor:pointer;margin-top:6px;font-family:inherit}
    .login-error{color:#f87171;font-size:.82rem;margin-bottom:10px;text-align:center}

    /* Layout */
    .admin-nav{background:rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.08);padding:14px 32px;display:flex;align-items:center;justify-content:space-between}
    .admin-nav-logo{display:flex;align-items:center;gap:10px}
    .admin-nav-logo img{height:28px}
    .admin-nav-title{font-size:.85rem;font-weight:700;color:rgba(255,255,255,.5)}
    .admin-logout{padding:6px 16px;border-radius:100px;background:rgba(255,255,255,.08);color:rgba(255,255,255,.6);border:none;cursor:pointer;font-size:.8rem;font-family:inherit;text-decoration:none}
    .admin-logout:hover{background:rgba(255,255,255,.15);color:#fff}
    .admin-main{max-width:1100px;margin:0 auto;padding:32px 24px}

    /* Stats */
    .stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:32px}
    @media(max-width:700px){.stats-row{grid-template-columns:repeat(2,1fr)}}
    .stat-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:20px;text-align:center}
    .stat-num{font-size:2rem;font-weight:900;line-height:1}
    .stat-lbl{font-size:.75rem;color:rgba(255,255,255,.4);margin-top:5px;text-transform:uppercase;letter-spacing:.5px}
    .stat-card.warn .stat-num{color:#fbbf24}
    .stat-card.success .stat-num{color:#34d399}
    .stat-card.accent .stat-num{color:#a78bfa}
    .stat-card.teal .stat-num{color:#2dd4bf}

    /* Section */
    .section{margin-bottom:36px}
    .section-head{display:flex;align-items:center;gap:10px;margin-bottom:16px}
    .section-title{font-size:1rem;font-weight:700;color:#fff}
    .badge{display:inline-flex;align-items:center;justify-content:center;min-width:22px;height:22px;border-radius:100px;font-size:.72rem;font-weight:800;padding:0 7px}
    .badge-warn{background:#fbbf24;color:#07071a}
    .badge-teal{background:#2dd4bf;color:#07071a}

    /* Table */
    .table-wrap{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:16px;overflow:hidden}
    table{width:100%;border-collapse:collapse}
    th{padding:12px 16px;text-align:left;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:rgba(255,255,255,.4);border-bottom:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.02)}
    td{padding:14px 16px;font-size:.87rem;border-bottom:1px solid rgba(255,255,255,.05);vertical-align:middle}
    tr:last-child td{border-bottom:none}
    tr:hover td{background:rgba(255,255,255,.02)}
    .td-name{font-weight:600;color:#fff}
    .td-email{color:rgba(255,255,255,.5)}
    .td-tenant{color:#a78bfa;font-size:.8rem;font-weight:600}
    .td-date{color:rgba(255,255,255,.35);font-size:.8rem}
    .td-org{color:rgba(255,255,255,.6)}

    /* Status badges */
    .status-pill{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:100px;font-size:.75rem;font-weight:700}
    .sp-pending{background:rgba(251,191,36,.12);color:#fbbf24;border:1px solid rgba(251,191,36,.25)}
    .sp-approved{background:rgba(52,211,153,.12);color:#34d399;border:1px solid rgba(52,211,153,.25)}
    .sp-rejected{background:rgba(248,113,113,.12);color:#f87171;border:1px solid rgba(248,113,113,.25)}
    .sp-new{background:rgba(45,212,191,.12);color:#2dd4bf;border:1px solid rgba(45,212,191,.25)}

    /* Action buttons */
    .btn-approve{padding:5px 14px;border-radius:100px;background:rgba(52,211,153,.15);color:#34d399;border:1px solid rgba(52,211,153,.3);cursor:pointer;font-size:.78rem;font-weight:700;font-family:inherit;transition:background .2s}
    .btn-approve:hover{background:rgba(52,211,153,.3)}
    .btn-reject{padding:5px 14px;border-radius:100px;background:rgba(248,113,113,.1);color:#f87171;border:1px solid rgba(248,113,113,.25);cursor:pointer;font-size:.78rem;font-weight:700;font-family:inherit;transition:background .2s}
    .btn-reject:hover{background:rgba(248,113,113,.2)}
    .btn-close{padding:5px 14px;border-radius:100px;background:rgba(255,255,255,.06);color:rgba(255,255,255,.4);border:1px solid rgba(255,255,255,.1);cursor:pointer;font-size:.78rem;font-weight:600;font-family:inherit;transition:background .2s}
    .btn-close:hover{background:rgba(255,255,255,.12);color:#fff}
    .action-row{display:flex;gap:6px;align-items:center}

    .empty-row td{text-align:center;padding:32px;color:rgba(255,255,255,.3);font-size:.88rem}
    .note-input{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:8px;padding:5px 10px;color:#fff;font-size:.8rem;font-family:inherit;width:160px;outline:none}
    .note-input:focus{border-color:rgba(108,71,255,.6)}
    .toast{position:fixed;bottom:24px;right:24px;background:#1a1a3e;border:1px solid rgba(108,71,255,.4);color:#fff;padding:12px 20px;border-radius:12px;font-size:.87rem;font-weight:600;transform:translateY(80px);opacity:0;transition:all .3s;z-index:999}
    .toast.show{transform:none;opacity:1}
  </style>
</head>
<body>

<?php if (!$is_admin): ?>
<!-- ── LOGIN ── -->
<div class="login-wrap">
  <div class="login-box">
    <div class="login-logo"><img src="/assets/Logo.png" alt="Lumina"></div>
    <div class="login-title">Admin Panel</div>
    <?php if (!empty($login_error)): ?>
    <div class="login-error"><?= htmlspecialchars($login_error) ?></div>
    <?php endif; ?>
    <?php if (!$admin_hash): ?>
    <div style="color:#fbbf24;font-size:.83rem;text-align:center;margin-bottom:12px">
      ⚠ Chưa cấu hình ADMIN_PASSWORD_HASH trong .env<br>
      <code style="font-size:.75rem;color:rgba(255,255,255,.5)">php -r "echo password_hash('your_password', PASSWORD_BCRYPT);"</code>
    </div>
    <?php endif; ?>
    <form method="post">
      <div class="login-group">
        <label class="login-label">Tên đăng nhập</label>
        <input class="login-input" type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autocomplete="username">
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
  <div class="admin-nav-logo">
    <img src="/assets/Logo.png" alt="Lumina">
    <span class="admin-nav-title">Admin Panel</span>
  </div>
  <a href="/admin.php?logout=1" class="admin-logout">Đăng xuất</a>
</nav>

<div class="admin-main">

  <!-- Stats -->
  <div class="stats-row">
    <div class="stat-card accent"><div class="stat-num"><?= $stats['total'] ?></div><div class="stat-lbl">Tổng users</div></div>
    <div class="stat-card warn"><div class="stat-num"><?= $stats['pending'] ?></div><div class="stat-lbl">Chờ duyệt</div></div>
    <div class="stat-card success"><div class="stat-num"><?= $stats['approved'] ?></div><div class="stat-lbl">Đã duyệt</div></div>
    <div class="stat-card teal"><div class="stat-num"><?= $stats['requests'] ?></div><div class="stat-lbl">Access requests</div></div>
  </div>

  <!-- Access Requests (main domain leads) -->
  <?php if (!empty($access_requests)): ?>
  <div class="section">
    <div class="section-head">
      <span class="section-title">📬 Yêu cầu truy cập</span>
      <span class="badge badge-teal"><?= count($access_requests) ?></span>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Tên</th><th>Email</th><th>SĐT</th><th>Tổ chức</th><th>Mục đích</th><th>Thời gian</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($access_requests as $r): ?>
          <tr id="req-<?= $r['id'] ?>">
            <td class="td-name"><?= htmlspecialchars($r['name']) ?></td>
            <td class="td-email"><?= htmlspecialchars($r['email']) ?></td>
            <td class="td-org"><?= htmlspecialchars($r['phone'] ?? '—') ?></td>
            <td class="td-org"><?= htmlspecialchars($r['organization'] ?: '—') ?></td>
            <td class="td-org" style="max-width:200px;font-size:.8rem;color:rgba(255,255,255,.4)"><?= htmlspecialchars($r['message'] ?: '—') ?></td>
            <td class="td-date"><?= date('d/m H:i', (int)$r['created_at']) ?></td>
            <td>
              <button class="btn-close" onclick="closeRequest(<?= $r['id'] ?>)">✓ Đã xử lý</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- Pending Users -->
  <div class="section">
    <div class="section-head">
      <span class="section-title">⏳ Chờ phê duyệt</span>
      <?php if ($stats['pending'] > 0): ?>
      <span class="badge badge-warn"><?= $stats['pending'] ?></span>
      <?php endif; ?>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Tên</th><th>Email</th><th>SĐT</th><th>Tenant</th><th>Đăng ký</th><th>Ghi chú</th><th>Hành động</th></tr></thead>
        <tbody>
          <?php if (empty($pending_users)): ?>
          <tr class="empty-row"><td colspan="7">Không có user nào đang chờ duyệt ✓</td></tr>
          <?php else: ?>
          <?php foreach ($pending_users as $u): ?>
          <tr id="user-<?= $u['id'] ?>">
            <td class="td-name"><?= htmlspecialchars($u['name']) ?></td>
            <td class="td-email"><?= htmlspecialchars($u['email']) ?></td>
            <td class="td-org"><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
            <td class="td-tenant"><?= htmlspecialchars($u['tenant_name']) ?></td>
            <td class="td-date"><?= date('d/m/Y H:i', (int)$u['created_at']) ?></td>
            <td><input class="note-input" type="text" placeholder="Ghi chú..." id="note-<?= $u['id'] ?>"></td>
            <td>
              <div class="action-row">
                <button class="btn-approve" onclick="userAction(<?= $u['id'] ?>,'approve')">✓ Duyệt</button>
                <button class="btn-reject"  onclick="userAction(<?= $u['id'] ?>,'reject')">✕ Từ chối</button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- All Users -->
  <div class="section">
    <div class="section-head">
      <span class="section-title">👥 Tất cả users</span>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Tên</th><th>Email</th><th>SĐT</th><th>Tenant</th><th>Trạng thái</th><th>Đăng ký</th><th>Hành động</th></tr></thead>
        <tbody>
          <?php foreach ($all_users as $u):
            $sp = ['pending'=>'sp-pending','approved'=>'sp-approved','rejected'=>'sp-rejected'][$u['status']] ?? 'sp-pending';
            $sl = ['pending'=>'⏳ Chờ duyệt','approved'=>'✓ Đã duyệt','rejected'=>'✕ Từ chối'][$u['status']] ?? $u['status'];
          ?>
          <tr id="u-<?= $u['id'] ?>">
            <td class="td-name"><?= htmlspecialchars($u['name']) ?></td>
            <td class="td-email"><?= htmlspecialchars($u['email']) ?></td>
            <td class="td-org"><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
            <td class="td-tenant"><?= htmlspecialchars($u['tenant_name']) ?></td>
            <td><span class="status-pill <?= $sp ?>"><?= $sl ?></span></td>
            <td class="td-date"><?= date('d/m/Y', (int)$u['created_at']) ?></td>
            <td>
              <?php if ($u['status'] !== 'approved'): ?>
              <button class="btn-approve" onclick="userAction(<?= $u['id'] ?>,'approve')" style="font-size:.75rem">Duyệt</button>
              <?php endif; ?>
              <?php if ($u['status'] !== 'rejected'): ?>
              <button class="btn-reject" onclick="userAction(<?= $u['id'] ?>,'reject')" style="font-size:.75rem">Từ chối</button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div><!-- /admin-main -->

<div class="toast" id="toast"></div>

<script>
function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2500);
}

async function userAction(userId, action) {
  const note = document.getElementById('note-' + userId)?.value || '';
  const res  = await fetch('/admin.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ action, user_id: userId, note })
  });
  const json = await res.json();
  if (json.ok) {
    // Remove from pending table
    const pendingRow = document.getElementById('user-' + userId);
    if (pendingRow) pendingRow.remove();
    // Update status in all users table
    const allRow = document.getElementById('u-' + userId);
    if (allRow) {
      const pill = allRow.querySelector('.status-pill');
      if (pill) {
        pill.className = 'status-pill ' + (action==='approve' ? 'sp-approved' : 'sp-rejected');
        pill.textContent = action==='approve' ? '✓ Đã duyệt' : '✕ Từ chối';
      }
    }
    showToast(action === 'approve' ? '✓ Đã phê duyệt & gửi email thông báo' : '✕ Đã từ chối tài khoản');
  }
}

async function closeRequest(id) {
  await fetch('/admin.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ action: 'close_request', id })
  });
  document.getElementById('req-' + id)?.remove();
  showToast('✓ Đã đánh dấu xử lý');
}
</script>
<?php endif; ?>
</body>
</html>
