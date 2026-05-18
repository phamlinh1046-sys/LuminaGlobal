<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/tenant.php';
require_once __DIR__ . '/includes/auth.php';

$tenant = detect_tenant();
if (!$tenant) { header('Location: https://demo.' . LUMINA_BASE_DOMAIN . '/login.php'); exit; }

$user = auth_user();
if (!$user || $user['tenant_id'] != $tenant['id']) { header('Location: /login.php'); exit; }

$assessments = db_rows(
    'SELECT a.id, a.type, a.status, a.created_at, a.completed_at, r.archetype
     FROM assessments a LEFT JOIN results r ON r.assessment_id=a.id
     WHERE a.user_id=:u ORDER BY a.created_at DESC',
    [':u' => $user['id']]
);

$latest_result = null;
foreach ($assessments as $a) {
    if ($a['status'] === 'completed' && $a['archetype']) { $latest_result = $a; break; }
}

$brand_color = $tenant['primary_color'] ?? '#6C47FF';
$user_name   = htmlspecialchars($user['name']);
$tenant_name = htmlspecialchars($tenant['name']);
$first_name  = explode(' ', $user_name)[0];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — <?= $user_name ?> | Lumina</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/lumina.css">
  <style>
    :root{--brand:<?= $brand_color ?>}

    body { background: #f4f5f9; }

    /* ── NAVBAR ── */
    .app-nav {
      position: fixed; top:0; left:0; right:0; z-index:100;
      background: #07071a;
      border-bottom: 1px solid rgba(255,255,255,.08);
      padding: 14px 0;
    }
    .app-nav .nav-inner { display:flex; align-items:center; justify-content:space-between; }
    .app-nav-logo img { height:32px; width:auto; }
    .app-nav-right { display:flex; align-items:center; gap:16px; }
    .app-nav-user { font-size:.85rem; color:rgba(255,255,255,.5); }
    .app-nav-logout {
      padding: 7px 18px; border-radius:100px; font-size:.83rem; font-weight:600;
      background: rgba(255,255,255,.08); color: rgba(255,255,255,.7);
      border: 1px solid rgba(255,255,255,.12); cursor:pointer; font-family:var(--font);
      transition: background .2s, color .2s;
    }
    .app-nav-logout:hover { background: rgba(255,255,255,.15); color:#fff; }

    /* ── LAYOUT ── */
    .app-layout { display:grid; grid-template-columns:240px 1fr; min-height:100vh; padding-top:62px; }
    @media(max-width:900px){ .app-layout{grid-template-columns:1fr} .app-sidebar{display:none} }

    /* ── SIDEBAR ── */
    .app-sidebar {
      background:#fff; border-right:1px solid #e5e7eb;
      padding:24px 12px; position:fixed; top:62px; bottom:0; width:240px;
      overflow-y:auto;
    }
    .sidebar-tenant {
      padding:0 8px 16px; margin-bottom:8px;
      border-bottom:1px solid #f0f0f0;
      font-size:.75rem; font-weight:700; text-transform:uppercase;
      letter-spacing:.8px; color:#9ca3af;
    }
    .sidebar-link {
      display:flex; align-items:center; gap:10px;
      padding:9px 12px; border-radius:10px;
      color:#6b7280; text-decoration:none; font-size:.88rem; font-weight:500;
      transition:all .15s; margin-bottom:2px;
    }
    .sidebar-link:hover, .sidebar-link.active {
      background: rgba(108,71,255,.08); color:var(--brand);
    }
    .sidebar-link.active { font-weight:700; }
    .sidebar-icon { font-size:1rem; }

    /* ── MAIN ── */
    .app-main { margin-left:240px; padding:32px 40px; }
    @media(max-width:900px){ .app-main{margin-left:0;padding:24px 16px} }

    /* ── WELCOME BANNER ── */
    .welcome-banner {
      background: linear-gradient(135deg, #07071a 0%, #1a0f3c 100%);
      border-radius:20px; padding:32px 36px; margin-bottom:28px;
      display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:20px;
      position:relative; overflow:hidden;
    }
    .welcome-banner::before {
      content:''; position:absolute; inset:0;
      background: radial-gradient(ellipse 50% 80% at 80% 50%, rgba(108,71,255,.35), transparent);
    }
    .welcome-text { position:relative; }
    .welcome-greeting { font-size:1.5rem; font-weight:800; color:#fff; margin-bottom:6px; }
    .welcome-sub { font-size:.9rem; color:rgba(255,255,255,.55); }
    .welcome-archetype { color:var(--teal); font-weight:700; }
    .welcome-btn {
      position:relative; display:inline-flex; align-items:center; gap:8px;
      padding:12px 24px; background:var(--brand); color:#fff;
      border-radius:100px; font-size:.9rem; font-weight:700; text-decoration:none;
      transition:box-shadow .2s, transform .2s; white-space:nowrap;
    }
    .welcome-btn:hover { transform:translateY(-1px); box-shadow:0 8px 24px rgba(108,71,255,.4); }

    /* ── STATS ROW ── */
    .stats-row { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:28px; }
    @media(max-width:600px){ .stats-row{grid-template-columns:1fr} }
    .stat-card {
      background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:20px 24px;
      display:flex; align-items:center; gap:16px;
    }
    .stat-icon { font-size:1.8rem; flex-shrink:0; }
    .stat-val { font-size:1.5rem; font-weight:900; color:var(--text); line-height:1; }
    .stat-lbl { font-size:.78rem; color:#9ca3af; margin-top:3px; }

    /* ── QUICK ACTIONS ── */
    .section-heading { font-size:1rem; font-weight:700; color:#374151; margin-bottom:14px; }
    .actions-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:14px; margin-bottom:28px; }
    @media(max-width:600px){ .actions-grid{grid-template-columns:1fr} }
    .action-card {
      background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:20px;
      text-decoration:none; color:inherit; display:flex; align-items:center; gap:14px;
      transition:box-shadow .2s, border-color .2s, transform .2s;
    }
    .action-card:hover { box-shadow:0 4px 20px rgba(108,71,255,.1); border-color:rgba(108,71,255,.25); transform:translateY(-2px); }
    .action-icon {
      width:44px; height:44px; border-radius:12px; flex-shrink:0;
      background:rgba(108,71,255,.1); display:flex; align-items:center; justify-content:center; font-size:1.3rem;
    }
    .action-title { font-weight:700; font-size:.93rem; color:#111827; margin-bottom:3px; }
    .action-desc { font-size:.8rem; color:#9ca3af; }

    /* ── HISTORY ── */
    .history-card {
      background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:20px 24px;
      display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;
      transition:box-shadow .15s;
    }
    .history-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.06); }
    .history-type { font-weight:700; font-size:.9rem; color:#111827; margin-bottom:3px; }
    .history-date { font-size:.8rem; color:#9ca3af; }
    .history-archetype { font-size:.85rem; color:var(--brand); font-weight:600; margin-top:3px; }
    .history-actions { display:flex; gap:8px; align-items:center; }
    .tag-done { background:#d1fae5; color:#065f46; border-radius:100px; padding:3px 12px; font-size:.75rem; font-weight:700; }
    .tag-pending { background:#fef3c7; color:#92400e; border-radius:100px; padding:3px 12px; font-size:.75rem; font-weight:700; }
    .btn-view {
      padding:6px 16px; border-radius:100px; font-size:.8rem; font-weight:600;
      background:rgba(108,71,255,.08); color:var(--brand); text-decoration:none;
      transition:background .2s;
    }
    .btn-view:hover { background:rgba(108,71,255,.18); }

    .empty-state { background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:56px 24px; text-align:center; }
    .empty-icon { font-size:3rem; margin-bottom:16px; }
    .empty-title { font-size:1.1rem; font-weight:700; color:#111827; margin-bottom:8px; }
    .empty-sub { color:#9ca3af; font-size:.9rem; margin-bottom:24px; }
    .btn-start {
      display:inline-flex; align-items:center; gap:8px;
      padding:12px 28px; background:var(--brand); color:#fff;
      border-radius:100px; font-size:.9rem; font-weight:700; text-decoration:none;
      transition:box-shadow .2s;
    }
    .btn-start:hover { box-shadow:0 8px 24px rgba(108,71,255,.35); }

    .history-list { display:flex; flex-direction:column; gap:10px; }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="app-nav">
  <div class="container nav-inner">
    <a href="/" class="app-nav-logo"><img src="/assets/Logo.png" alt="Lumina"></a>
    <div class="app-nav-right">
      <span class="app-nav-user"><?= $user_name ?></span>
      <button class="app-nav-logout"
        onclick="fetch('/api/auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})}).then(()=>location.href='/login.php')">
        Đăng xuất
      </button>
    </div>
  </div>
</nav>

<div class="app-layout">
  <!-- SIDEBAR -->
  <aside class="app-sidebar">
    <div class="sidebar-tenant"><?= $tenant_name ?></div>
    <a href="/dashboard.php" class="sidebar-link active"><span class="sidebar-icon">🏠</span> Dashboard</a>
    <a href="/assessment.php" class="sidebar-link"><span class="sidebar-icon">📋</span> Đánh Giá Mới</a>
    <?php if ($latest_result): ?>
    <a href="/results.php?id=<?= $latest_result['id'] ?>" class="sidebar-link"><span class="sidebar-icon">📊</span> Kết Quả</a>
    <a href="/growth-plan.php?id=<?= $latest_result['id'] ?>" class="sidebar-link"><span class="sidebar-icon">🗺️</span> Growth Plan</a>
    <a href="/mentor.php?id=<?= $latest_result['id'] ?>" class="sidebar-link"><span class="sidebar-icon">🤖</span> AI Mentor</a>
    <?php endif; ?>
  </aside>

  <!-- MAIN -->
  <main class="app-main">

    <!-- Welcome banner -->
    <div class="welcome-banner">
      <div class="welcome-text">
        <div class="welcome-greeting">Xin chào, <?= $first_name ?> 👋</div>
        <div class="welcome-sub">
          <?php if ($latest_result): ?>
            Identity Archetype: <span class="welcome-archetype"><?= htmlspecialchars($latest_result['archetype']) ?></span>
          <?php else: ?>
            Hãy bắt đầu đánh giá để khám phá bản thân của bạn
          <?php endif; ?>
        </div>
      </div>
      <a href="/assessment.php" class="welcome-btn">
        <?= $latest_result ? '+ Đánh giá lại' : 'Bắt đầu đánh giá →' ?>
      </a>
    </div>

    <!-- Stats -->
    <div class="stats-row">
      <?php
      $total = count($assessments);
      $done  = count(array_filter($assessments, fn($a) => $a['status'] === 'completed'));
      ?>
      <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div><div class="stat-val"><?= $total ?></div><div class="stat-lbl">Lượt đánh giá</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div><div class="stat-val"><?= $done ?></div><div class="stat-lbl">Hoàn thành</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🎯</div>
        <div><div class="stat-val"><?= $latest_result ? '1' : '0' ?></div><div class="stat-lbl">Archetype xác định</div></div>
      </div>
    </div>

    <?php if ($latest_result): ?>
    <!-- Quick actions -->
    <div class="section-heading">Hành động nhanh</div>
    <div class="actions-grid">
      <a href="/results.php?id=<?= $latest_result['id'] ?>" class="action-card">
        <div class="action-icon">📊</div>
        <div><div class="action-title">Xem kết quả đầy đủ</div><div class="action-desc">7 chiều phân tích chuyên sâu</div></div>
      </a>
      <a href="/mentor.php?id=<?= $latest_result['id'] ?>" class="action-card">
        <div class="action-icon">🤖</div>
        <div><div class="action-title">Chat AI Mentor</div><div class="action-desc">Cá nhân hóa theo hồ sơ của bạn</div></div>
      </a>
      <a href="/growth-plan.php?id=<?= $latest_result['id'] ?>" class="action-card">
        <div class="action-icon">🗺️</div>
        <div><div class="action-title">Growth Plan 90 ngày</div><div class="action-desc">Kế hoạch phát triển cụ thể</div></div>
      </a>
      <a href="/assessment.php" class="action-card">
        <div class="action-icon">🔄</div>
        <div><div class="action-title">Đánh giá lại</div><div class="action-desc">Theo dõi sự thay đổi theo thời gian</div></div>
      </a>
    </div>
    <?php endif; ?>

    <!-- History -->
    <div class="section-heading">Lịch sử đánh giá</div>
    <?php if (empty($assessments)): ?>
    <div class="empty-state">
      <div class="empty-icon">🔭</div>
      <div class="empty-title">Chưa có đánh giá nào</div>
      <div class="empty-sub">Bắt đầu hành trình khám phá bản thân ngay hôm nay</div>
      <a href="/assessment.php" class="btn-start">Bắt đầu đánh giá →</a>
    </div>
    <?php else: ?>
    <div class="history-list">
      <?php foreach ($assessments as $a):
        $dt = date('d/m/Y H:i', (int)($a['completed_at'] ?: $a['created_at']));
        $type_label = $a['type'] === 'quick' ? '⚡ Quick Scan' : '🔭 Deep Discovery';
      ?>
      <div class="history-card">
        <div>
          <div class="history-type"><?= $type_label ?></div>
          <div class="history-date"><?= $dt ?></div>
          <?php if ($a['archetype']): ?>
          <div class="history-archetype">✦ <?= htmlspecialchars($a['archetype']) ?></div>
          <?php endif; ?>
        </div>
        <div class="history-actions">
          <?php if ($a['status'] === 'completed'): ?>
            <span class="tag-done">Hoàn thành</span>
            <a href="/results.php?id=<?= $a['id'] ?>" class="btn-view">Xem kết quả →</a>
          <?php else: ?>
            <span class="tag-pending">Đang làm</span>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </main>
</div>

</body>
</html>
