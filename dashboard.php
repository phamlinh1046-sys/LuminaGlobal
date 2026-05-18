<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/tenant.php';
require_once __DIR__ . '/includes/auth.php';

$tenant = detect_tenant();
if (!$tenant) { header('Location: https://demo.' . LUMINA_BASE_DOMAIN . '/login.php'); exit; }

$user = auth_user();
if (!$user || $user['tenant_id'] != $tenant['id']) {
    header('Location: /login.php'); exit;
}

// Load user's assessments
$assessments = db_rows(
    'SELECT a.id, a.type, a.status, a.created_at, a.completed_at, r.archetype
     FROM assessments a
     LEFT JOIN results r ON r.assessment_id = a.id
     WHERE a.user_id=:u ORDER BY a.created_at DESC',
    [':u' => $user['id']]
);

$latest_result = null;
foreach ($assessments as $a) {
    if ($a['status'] === 'completed' && $a['archetype']) {
        $latest_result = $a; break;
    }
}

$brand_color  = $tenant['primary_color'] ?? '#6C47FF';
$user_name    = htmlspecialchars($user['name']);
$tenant_name  = htmlspecialchars($tenant['name']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — <?= $user_name ?> | Lumina</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/lumina.css">
  <style>:root{--brand:<?= $brand_color ?>}</style>
</head>
<body>

<nav class="lumi-nav">
  <div class="container nav-inner">
    <a href="/" class="nav-logo">✦ <span>Lumina</span></a>
    <div class="nav-actions">
      <span style="color:rgba(255,255,255,.6);font-size:.9rem"><?= $user_name ?></span>
      <a href="#" class="btn btn-outline" style="padding:8px 18px;font-size:.85rem"
         onclick="fetch('/api/auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})}).then(()=>location.href='/login.php');return false">
        Đăng xuất
      </a>
    </div>
  </div>
</nav>

<div class="dash-wrap">

  <!-- SIDEBAR -->
  <aside class="dash-sidebar">
    <div style="padding:8px 14px 20px;border-bottom:1px solid var(--border);margin-bottom:16px">
      <div style="font-size:.75rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px"><?= $tenant_name ?></div>
    </div>
    <nav>
      <a href="/dashboard.php" class="nav-item active"><span class="nav-icon">🏠</span> Dashboard</a>
      <a href="/assessment.php" class="nav-item"><span class="nav-icon">📋</span> Đánh Giá Mới</a>
      <?php if ($latest_result): ?>
      <a href="/results.php?id=<?= $latest_result['id'] ?>" class="nav-item"><span class="nav-icon">📊</span> Kết Quả Gần Nhất</a>
      <a href="/growth-plan.php?id=<?= $latest_result['id'] ?>" class="nav-item"><span class="nav-icon">🗺️</span> Growth Plan</a>
      <a href="/mentor.php?id=<?= $latest_result['id'] ?>" class="nav-item"><span class="nav-icon">🤖</span> AI Mentor</a>
      <?php endif; ?>
    </nav>
  </aside>

  <!-- MAIN -->
  <main class="dash-main">

    <!-- Welcome card -->
    <div class="card card-brand" style="margin-bottom:28px">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
        <div>
          <h2 style="margin-bottom:4px">Xin chào, <?= $user_name ?> 👋</h2>
          <p class="text-muted">
            <?php if ($latest_result): ?>
              Archetype của bạn: <strong style="color:var(--brand)"><?= htmlspecialchars($latest_result['archetype']) ?></strong>
            <?php else: ?>
              Hãy bắt đầu đánh giá đầu tiên để khám phá bản thân
            <?php endif; ?>
          </p>
        </div>
        <a href="/assessment.php" class="btn btn-primary">
          <?= $latest_result ? 'Đánh giá lại' : 'Bắt đầu đánh giá →' ?>
        </a>
      </div>
    </div>

    <?php if ($latest_result): ?>
    <!-- Quick actions -->
    <div class="grid-2" style="margin-bottom:28px">
      <a href="/results.php?id=<?= $latest_result['id'] ?>" class="card" style="text-decoration:none;color:inherit">
        <div style="font-size:1.5rem;margin-bottom:8px">📊</div>
        <h4>Xem kết quả đầy đủ</h4>
        <p class="text-muted" style="font-size:.85rem;margin-top:4px">7 chiều phân tích chuyên sâu</p>
      </a>
      <a href="/mentor.php?id=<?= $latest_result['id'] ?>" class="card" style="text-decoration:none;color:inherit">
        <div style="font-size:1.5rem;margin-bottom:8px">🤖</div>
        <h4>Chat với AI Mentor</h4>
        <p class="text-muted" style="font-size:.85rem;margin-top:4px">Được cá nhân hóa theo hồ sơ của bạn</p>
      </a>
    </div>
    <?php endif; ?>

    <!-- Assessment history -->
    <h3 style="margin-bottom:16px">Lịch sử đánh giá</h3>
    <?php if (empty($assessments)): ?>
    <div class="card" style="text-align:center;padding:48px">
      <div style="font-size:3rem;margin-bottom:16px">🔭</div>
      <h3>Chưa có đánh giá nào</h3>
      <p class="text-muted" style="margin:8px 0 24px">Bắt đầu hành trình khám phá bản thân ngay hôm nay</p>
      <a href="/assessment.php" class="btn btn-primary">Bắt đầu đánh giá →</a>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:12px">
      <?php foreach ($assessments as $a):
        $completed_date = $a['completed_at']
          ? date('d/m/Y H:i', (int)$a['completed_at'])
          : date('d/m/Y H:i', (int)$a['created_at']);
        $status_label = $a['status'] === 'completed' ? '<span class="tag tag-teal">Hoàn thành</span>' : '<span class="tag tag-gold">Đang làm</span>';
        $type_label = $a['type'] === 'quick' ? '⚡ Quick Scan' : '🔭 Deep Discovery';
      ?>
      <div class="card" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding:20px 24px">
        <div>
          <div style="font-weight:700;margin-bottom:4px"><?= $type_label ?></div>
          <div style="font-size:.85rem;color:var(--text-muted)"><?= $completed_date ?></div>
          <?php if ($a['archetype']): ?>
          <div style="font-size:.9rem;margin-top:4px">Archetype: <strong style="color:var(--brand)"><?= htmlspecialchars($a['archetype']) ?></strong></div>
          <?php endif; ?>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
          <?= $status_label ?>
          <?php if ($a['status'] === 'completed'): ?>
          <a href="/results.php?id=<?= $a['id'] ?>" class="btn btn-ghost" style="padding:6px 14px;font-size:.85rem">Xem kết quả →</a>
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
