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

$assessment_id = (int)($_GET['id'] ?? 0);
if (!$assessment_id) { header('Location: /dashboard.php'); exit; }

$assessment = db_row(
    'SELECT a.*, u.name AS user_name FROM assessments a JOIN users u ON u.id=a.user_id WHERE a.id=:id AND a.user_id=:u',
    [':id' => $assessment_id, ':u' => $user['id']]
);
if (!$assessment || $assessment['status'] !== 'completed') {
    header('Location: /dashboard.php'); exit;
}

$result = db_row('SELECT * FROM results WHERE assessment_id=:a', [':a' => $assessment_id]);
if (!$result) { header('Location: /dashboard.php'); exit; }

// Decode JSON fields
$adesc       = json_decode($result['archetype_desc'],       true) ?? [];
$strengths   = json_decode($result['strengths_json'],       true) ?? [];
$blind_spots = json_decode($result['blind_spots_json'],     true) ?? [];
$motivations = json_decode($result['motivation_drivers_json'], true) ?? [];
$life_wheel  = json_decode($result['life_wheel_json'],      true) ?? [];
$conflict    = json_decode($result['internal_conflict'],    true) ?? [];
$growth      = json_decode($result['growth_direction'],     true) ?? [];

$archetype   = htmlspecialchars($result['archetype'] ?? 'Người Khám Phá');
$brand_color = $tenant['primary_color'] ?? '#6C47FF';

// Archetype icons
$archetype_icons = [
    'Nhà Kiến Tạo'   => '🏛️',
    'Chất Xúc Tác'   => '⚡',
    'Người Bảo Vệ'   => '🛡️',
    'Người Tìm Kiếm' => '🔭',
    'Người Khai Phá' => '🗺️',
    'Người Dẫn Dắt'  => '🌟',
    'Người Thực Thi' => '⚙️',
    'Người Kết Nối'  => '🤝',
];
$icon = $archetype_icons[$result['archetype'] ?? ''] ?? '✦';

$wheel_labels = array_keys($life_wheel);
$wheel_values = array_values($life_wheel);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $archetype ?> — Kết quả Lumina</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/lumina.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    :root{--brand:<?= $brand_color ?>}
    .results-wrap { padding-top: 80px; }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="lumi-nav">
  <div class="container nav-inner">
    <a href="/" class="nav-logo">✦ <span>Lumina</span></a>
    <div class="nav-actions">
      <a href="/assessment.php" class="btn btn-ghost" style="padding:8px 18px;font-size:.85rem">Làm lại</a>
      <a href="/dashboard.php" class="btn btn-outline" style="padding:8px 18px;font-size:.85rem">Dashboard</a>
    </div>
  </div>
</nav>

<div class="results-wrap">

  <!-- ── ARCHETYPE HERO ── -->
  <section class="results-hero">
    <div class="container" style="position:relative">
      <div class="archetype-badge fade-in">
        <div class="archetype-icon"><?= $icon ?></div>
        <div class="tag tag-teal" style="margin-bottom:12px">
          <?= $assessment['type'] === 'quick' ? 'Quick Scan' : 'Deep Discovery' ?> · Identity Archetype
        </div>
        <div class="archetype-name"><?= $archetype ?></div>
        <div class="archetype-tagline"><?= htmlspecialchars($adesc['tagline'] ?? '') ?></div>
        <?php if (!empty($adesc['desc'])): ?>
        <p class="archetype-desc"><?= nl2br(htmlspecialchars($adesc['desc'])) ?></p>
        <?php endif; ?>
      </div>

      <?php if (!empty($adesc['summary'])): ?>
      <div style="max-width:640px;margin:32px auto 0;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:16px;padding:24px;color:rgba(255,255,255,.85);line-height:1.7">
        <strong style="color:var(--teal)">Điều quan trọng nhất bạn cần biết về bản thân:</strong><br>
        <?= nl2br(htmlspecialchars($adesc['summary'])) ?>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- ── STRENGTHS ── -->
  <section class="result-section">
    <div class="container">
      <div class="section-label">💪 Điểm Mạnh Cốt Lõi</div>
      <h2 class="section-title">Bạn có những sức mạnh thiên phú</h2>
      <p class="text-muted" style="margin-bottom:32px">Đây là những tài năng tự nhiên mà bạn thường coi thường vì chúng quá dễ dàng.</p>
      <div class="grid-2">
        <?php foreach ($strengths as $s): ?>
        <div class="strength-card fade-in">
          <div class="strength-title">✦ <?= htmlspecialchars($s['title'] ?? '') ?></div>
          <p style="color:var(--text-muted);font-size:.95rem;line-height:1.6"><?= htmlspecialchars($s['desc'] ?? '') ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ── BLIND SPOTS ── -->
  <section class="result-section result-section-alt">
    <div class="container">
      <div class="section-label">🔍 Điểm Mù</div>
      <h2 class="section-title">Những gì đang kéo bạn lại</h2>
      <p class="text-muted" style="margin-bottom:32px">Nhận ra điểm mù là bước đột phá quan trọng nhất trong hành trình phát triển.</p>
      <div class="grid-3">
        <?php foreach ($blind_spots as $bs): ?>
        <div class="blindspot-card fade-in">
          <div class="blindspot-title">⚠ <?= htmlspecialchars($bs['title'] ?? '') ?></div>
          <p style="color:var(--text-muted);font-size:.9rem;line-height:1.6"><?= htmlspecialchars($bs['desc'] ?? '') ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ── MOTIVATION DRIVERS ── -->
  <section class="result-section">
    <div class="container">
      <div class="section-label">🔥 Động Lực Sâu</div>
      <h2 class="section-title">Điều thực sự thúc đẩy bạn</h2>
      <p class="text-muted" style="margin-bottom:32px">Hiểu được động lực thực sự giúp bạn làm việc với năng lượng bền vững.</p>
      <div style="max-width:640px">
        <?php foreach ($motivations as $m): ?>
        <div class="motivation-item fade-in">
          <div class="motivation-header">
            <span class="motivation-name"><?= htmlspecialchars($m['driver'] ?? '') ?></span>
            <span class="motivation-pct"><?= (int)($m['percentage'] ?? 0) ?>%</span>
          </div>
          <div class="motivation-track">
            <div class="motivation-fill" style="width:0%" data-pct="<?= (int)($m['percentage'] ?? 0) ?>"></div>
          </div>
          <?php if (!empty($m['desc'])): ?>
          <div class="motivation-desc"><?= htmlspecialchars($m['desc']) ?></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ── LIFE WHEEL ── -->
  <section class="result-section result-section-alt">
    <div class="container">
      <div class="section-label">⚖️ Bánh Xe Cuộc Sống</div>
      <h2 class="section-title">Bức tranh toàn diện về cuộc sống</h2>
      <p class="text-muted" style="margin-bottom:32px">Sự cân bằng giữa 8 lĩnh vực quyết định chất lượng cuộc sống tổng thể.</p>

      <div class="wheel-canvas-wrap">
        <canvas id="wheelChart" width="400" height="400"></canvas>
      </div>

      <div class="wheel-grid">
        <?php foreach ($life_wheel as $label => $score):
          $pct = min(100, max(0, ($score / 10) * 100));
          $color = $score >= 7 ? 'var(--teal)' : ($score >= 4 ? 'var(--brand)' : 'var(--gold)');
        ?>
        <div class="wheel-cell fade-in">
          <div class="wheel-score" style="color:<?= $color ?>"><?= (int)$score ?></div>
          <div class="wheel-label"><?= htmlspecialchars($label) ?></div>
          <div class="wheel-bar-wrap"><div class="wheel-bar" style="width:<?= $pct ?>%;background:<?= $color ?>"></div></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ── INTERNAL CONFLICT ── -->
  <section class="result-section">
    <div class="container">
      <div class="section-label">🌀 Xung Đột Nội Tâm</div>
      <h2 class="section-title">Cuộc chiến bên trong bạn</h2>
      <p class="text-muted" style="margin-bottom:32px">Xung đột nội tâm không phải điểm yếu — đó là bằng chứng bạn đang phát triển.</p>
      <div style="max-width:680px" class="fade-in">
        <div class="conflict-box">
          <?php if (!empty($conflict['headline'])): ?>
          <div class="conflict-headline">⚡ <?= htmlspecialchars($conflict['headline']) ?></div>
          <?php endif; ?>
          <?php if (!empty($conflict['description'])): ?>
          <p class="conflict-desc"><?= nl2br(htmlspecialchars($conflict['description'])) ?></p>
          <?php endif; ?>
          <?php if (!empty($conflict['reframe'])): ?>
          <div class="conflict-reframe">
            <strong>✦ Góc nhìn mới:</strong> <?= htmlspecialchars($conflict['reframe']) ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- ── GROWTH DIRECTION ── -->
  <section class="result-section result-section-alt">
    <div class="container">
      <div class="section-label">🚀 Hướng Phát Triển</div>
      <h2 class="section-title">Con đường phía trước của bạn</h2>
      <p class="text-muted" style="margin-bottom:32px">Dựa trên toàn bộ phân tích, đây là hướng đi phù hợp nhất với bạn.</p>
      <div style="max-width:680px" class="fade-in">
        <div class="growth-box">
          <?php if (!empty($growth['headline'])): ?>
          <div class="growth-headline">🎯 <?= htmlspecialchars($growth['headline']) ?></div>
          <?php endif; ?>
          <?php if (!empty($growth['next_90_days'])): ?>
          <p class="growth-days"><?= nl2br(htmlspecialchars($growth['next_90_days'])) ?></p>
          <?php endif; ?>
          <?php if (!empty($growth['power_question'])): ?>
          <div class="power-question">"<?= htmlspecialchars($growth['power_question']) ?>"</div>
          <?php endif; ?>
          <?php if (!empty($growth['resources'])): ?>
          <div style="margin-top:12px">
            <strong style="font-size:.85rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px">Bước hành động gợi ý</strong>
            <div style="margin-top:10px">
              <?php foreach ($growth['resources'] as $r): ?>
              <span class="resource-chip">→ <?= htmlspecialchars($r) ?></span>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- ── CTA ── -->
  <section class="cta-section">
    <div class="container">
      <h2 style="color:var(--white);margin-bottom:8px">Bước tiếp theo của bạn</h2>
      <p style="color:rgba(255,255,255,.6);font-size:1.05rem">Lumina đồng hành cùng bạn trên toàn bộ hành trình phát triển</p>

      <div class="cta-grid">
        <a href="/growth-plan.php?id=<?= $assessment_id ?>" class="cta-card">
          <div class="cta-icon">🗺️</div>
          <div class="cta-title">Build Growth Plan</div>
          <div class="cta-desc">Xây dựng kế hoạch phát triển cá nhân hóa theo kết quả</div>
        </a>
        <a href="/challenge.php?id=<?= $assessment_id ?>" class="cta-card">
          <div class="cta-icon">⚡</div>
          <div class="cta-title">Start Challenge</div>
          <div class="cta-desc">30-day challenge được thiết kế riêng cho archetype của bạn</div>
        </a>
        <a href="/mentor.php?id=<?= $assessment_id ?>" class="cta-card">
          <div class="cta-icon">🤖</div>
          <div class="cta-title">AI Mentor</div>
          <div class="cta-desc">Chat với AI mentor được cá nhân hóa theo hồ sơ của bạn</div>
        </a>
        <a href="/learning.php?id=<?= $assessment_id ?>" class="cta-card">
          <div class="cta-icon">📚</div>
          <div class="cta-title">Learning Path</div>
          <div class="cta-desc">Lộ trình học tập phù hợp với mục tiêu và điểm mạnh</div>
        </a>
      </div>

      <p style="color:rgba(255,255,255,.4);font-size:.85rem;margin-top:32px">
        Kết quả được lưu trong hồ sơ của bạn · <a href="/dashboard.php" style="color:var(--teal)">Xem lại bất cứ lúc nào</a>
      </p>
    </div>
  </section>

</div><!-- /results-wrap -->

<script>
// Animate motivation bars on scroll
const fills = document.querySelectorAll('.motivation-fill');
const observer = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      const el = e.target;
      el.style.width = el.dataset.pct + '%';
      observer.unobserve(el);
    }
  });
}, { threshold: 0.3 });
fills.forEach(f => observer.observe(f));

// Life Wheel Radar Chart
const labels = <?= json_encode($wheel_labels) ?>;
const values = <?= json_encode(array_map('floatval', $wheel_values)) ?>;
const ctx = document.getElementById('wheelChart').getContext('2d');

new Chart(ctx, {
  type: 'radar',
  data: {
    labels,
    datasets: [{
      data: values,
      backgroundColor: 'rgba(108,71,255,.15)',
      borderColor: 'rgba(108,71,255,.8)',
      borderWidth: 2,
      pointBackgroundColor: 'rgba(108,71,255,1)',
      pointRadius: 5,
      fill: true
    }]
  },
  options: {
    responsive: true,
    scales: {
      r: {
        min: 0, max: 10, ticks: { stepSize: 2, display: false },
        grid: { color: 'rgba(0,0,0,.08)' },
        pointLabels: { font: { size: 11, weight: '600' }, color: '#374151' },
        angleLines: { color: 'rgba(0,0,0,.08)' }
      }
    },
    plugins: { legend: { display: false } }
  }
});

// Fade-in on scroll
const fadeEls = document.querySelectorAll('.fade-in');
const fadeObs = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) e.target.style.animationPlayState = 'running'; });
}, { threshold: 0.1 });
fadeEls.forEach(el => { el.style.animationPlayState = 'paused'; fadeObs.observe(el); });
</script>
</body>
</html>
