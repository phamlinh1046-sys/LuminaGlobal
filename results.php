<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/tenant.php';
require_once __DIR__ . '/includes/auth.php';

$tenant = detect_tenant();
if (!$tenant) { header('Location: https://demo.' . LUMINA_BASE_DOMAIN . '/login.php'); exit; }

$user = auth_user();
if (!$user || $user['tenant_id'] != $tenant['id']) { header('Location: /login.php'); exit; }

$assessment_id = (int)($_GET['id'] ?? 0);
if (!$assessment_id) { header('Location: /dashboard.php'); exit; }

$assessment = db_row(
    'SELECT a.*, u.name AS user_name FROM assessments a JOIN users u ON u.id=a.user_id WHERE a.id=:id AND a.user_id=:u',
    [':id' => $assessment_id, ':u' => $user['id']]
);
if (!$assessment || $assessment['status'] !== 'completed') { header('Location: /dashboard.php'); exit; }

$result = db_row('SELECT * FROM results WHERE assessment_id=:a', [':a' => $assessment_id]);
if (!$result) { header('Location: /dashboard.php'); exit; }

$adesc       = json_decode($result['archetype_desc'],          true) ?? [];
$strengths   = json_decode($result['strengths_json'],          true) ?? [];
$blind_spots = json_decode($result['blind_spots_json'],        true) ?? [];
$motivations = json_decode($result['motivation_drivers_json'], true) ?? [];
$life_wheel  = json_decode($result['life_wheel_json'],         true) ?? [];
$conflict    = json_decode($result['internal_conflict'],       true) ?? [];
$growth      = json_decode($result['growth_direction'],        true) ?? [];

$archetype   = htmlspecialchars($result['archetype'] ?? 'Người Khám Phá');
$brand_color = $tenant['primary_color'] ?? '#6C47FF';

$archetype_icons = [
    'Nhà Kiến Tạo'   => '🏛️', 'Chất Xúc Tác'   => '⚡',
    'Người Bảo Vệ'   => '🛡️', 'Người Tìm Kiếm' => '🔭',
    'Người Khai Phá' => '🗺️', 'Người Dẫn Dắt'  => '🌟',
    'Người Thực Thi' => '⚙️', 'Người Kết Nối'  => '🤝',
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
    body { background: #f4f5f9; }

    /* ── NAVBAR ── */
    .app-nav {
      position:fixed; top:0; left:0; right:0; z-index:100;
      background:#07071a; border-bottom:1px solid rgba(255,255,255,.08); padding:14px 0;
    }
    .app-nav .nav-inner { display:flex; align-items:center; justify-content:space-between; }
    .app-nav-logo img { height:32px; width:auto; }
    .app-nav-right { display:flex; align-items:center; gap:10px; }
    .app-nav-link {
      padding:7px 18px; border-radius:100px; font-size:.83rem; font-weight:600;
      text-decoration:none; transition:background .2s;
    }
    .app-nav-link-ghost { background:rgba(255,255,255,.08); color:rgba(255,255,255,.7); border:1px solid rgba(255,255,255,.1); }
    .app-nav-link-ghost:hover { background:rgba(255,255,255,.15); color:#fff; }
    .app-nav-link-outline { background:transparent; color:rgba(255,255,255,.6); border:1px solid rgba(255,255,255,.15); }
    .app-nav-link-outline:hover { color:#fff; border-color:rgba(255,255,255,.35); }

    /* ── ARCHETYPE HERO ── */
    .results-hero {
      background:linear-gradient(160deg,#07071a 0%,#1a0f3c 60%,#0b1a2e 100%);
      padding:100px 0 70px; text-align:center; position:relative; overflow:hidden; margin-top:62px;
    }
    .results-hero::before {
      content:''; position:absolute; inset:0;
      background:radial-gradient(ellipse 60% 70% at 50% 50%,rgba(108,71,255,.3),transparent);
    }
    .arch-icon {
      width:90px; height:90px; border-radius:24px;
      background:linear-gradient(135deg,var(--brand),var(--teal));
      display:flex; align-items:center; justify-content:center;
      font-size:2.5rem; margin:0 auto 20px;
      box-shadow:0 12px 40px rgba(108,71,255,.4);
      position:relative;
    }
    .arch-type-badge {
      display:inline-flex; align-items:center; gap:8px;
      background:rgba(0,201,177,.15); border:1px solid rgba(0,201,177,.35);
      color:var(--teal); border-radius:100px; padding:5px 16px;
      font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:1px;
      margin-bottom:16px; position:relative;
    }
    .arch-name {
      font-size:clamp(2.2rem,5vw,3.8rem); font-weight:900; color:#fff;
      letter-spacing:-.03em; margin-bottom:8px; position:relative;
    }
    .arch-tagline { color:rgba(255,255,255,.6); font-size:1.05rem; font-style:italic; position:relative; }
    .arch-desc {
      color:rgba(255,255,255,.8); max-width:600px; margin:20px auto 0;
      line-height:1.75; font-size:1rem; position:relative;
    }
    .arch-summary {
      max-width:620px; margin:28px auto 0; position:relative;
      background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.1);
      border-radius:16px; padding:22px 28px; text-align:left;
    }
    .arch-summary-label { font-size:.75rem; font-weight:700; color:var(--teal); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; }
    .arch-summary p { color:rgba(255,255,255,.8); line-height:1.7; font-size:.95rem; }

    /* ── RESULT SECTIONS ── */
    .results-body { max-width:860px; margin:0 auto; padding:0 24px 80px; }

    .rs {
      padding:56px 0;
      border-bottom:1px solid #e5e7eb;
      opacity:0; transform:translateY(20px);
      transition:opacity .5s ease, transform .5s ease;
    }
    .rs.in-view { opacity:1; transform:none; }
    .rs:last-child { border-bottom:none; }

    .rs-label {
      display:inline-flex; align-items:center; gap:7px;
      background:rgba(108,71,255,.08); border:1px solid rgba(108,71,255,.18);
      color:var(--brand); border-radius:100px; padding:4px 14px;
      font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:14px;
    }
    .rs-title { font-size:1.6rem; font-weight:800; color:#111827; letter-spacing:-.02em; margin-bottom:8px; }
    .rs-sub { color:#6b7280; font-size:.9rem; line-height:1.6; margin-bottom:28px; }

    /* Strengths */
    .strength-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    @media(max-width:600px){ .strength-grid{grid-template-columns:1fr} }
    .s-card {
      background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:22px;
      border-left:4px solid var(--brand);
    }
    .s-title { font-weight:700; color:var(--brand); margin-bottom:6px; font-size:.9rem; }
    .s-desc { color:#6b7280; font-size:.87rem; line-height:1.6; }

    /* Blind spots */
    .bs-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; }
    @media(max-width:700px){ .bs-grid{grid-template-columns:1fr} }
    .bs-card {
      background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:22px;
      border-left:4px solid #f59e0b;
    }
    .bs-title { font-weight:700; color:#d97706; margin-bottom:6px; font-size:.9rem; }
    .bs-desc { color:#6b7280; font-size:.85rem; line-height:1.6; }

    /* Motivation */
    .mot-item { margin-bottom:22px; }
    .mot-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
    .mot-name { font-weight:700; font-size:.93rem; color:#111827; }
    .mot-pct { font-weight:800; color:var(--brand); font-size:.93rem; }
    .mot-track { background:#e5e7eb; border-radius:100px; height:8px; }
    .mot-fill { height:100%; border-radius:100px; background:linear-gradient(90deg,var(--brand),var(--teal)); transition:width 1s ease; }
    .mot-desc { font-size:.83rem; color:#9ca3af; margin-top:5px; }

    /* Life wheel */
    .wheel-chart-wrap { max-width:380px; margin:0 auto 32px; }
    .wheel-cells { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-top:24px; }
    @media(max-width:500px){ .wheel-cells{grid-template-columns:repeat(2,1fr)} }
    .wheel-cell { text-align:center; }
    .wheel-score { font-size:2rem; font-weight:900; line-height:1; margin-bottom:4px; }
    .wheel-lbl { font-size:.7rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.4px; }
    .wheel-bar-track { background:#e5e7eb; border-radius:100px; height:4px; margin-top:6px; }
    .wheel-bar-fill { height:100%; border-radius:100px; }

    /* Conflict */
    .conflict-box {
      background:linear-gradient(135deg,#fffbeb,#fff);
      border:1px solid #fde68a; border-radius:20px; padding:32px;
    }
    .conflict-head { font-size:1.15rem; font-weight:800; color:#92400e; margin-bottom:12px; }
    .conflict-desc { color:#78350f; line-height:1.75; margin-bottom:20px; font-size:.93rem; }
    .conflict-reframe {
      background:#ecfdf5; border-radius:12px; padding:16px 20px;
      color:#065f46; border-left:4px solid var(--teal); font-size:.92rem; line-height:1.6;
    }

    /* Growth */
    .growth-box {
      background:linear-gradient(135deg,rgba(108,71,255,.06),#fff);
      border:1px solid rgba(108,71,255,.18); border-radius:20px; padding:32px;
    }
    .growth-head { font-size:1.15rem; font-weight:800; color:var(--brand); margin-bottom:12px; }
    .growth-days { color:#374151; line-height:1.75; margin-bottom:20px; font-size:.93rem; }
    .power-q {
      background:#07071a; color:#fff; border-radius:14px; padding:20px 24px;
      font-size:1rem; font-style:italic; font-weight:600; margin-bottom:20px; line-height:1.6;
    }
    .resource-chips { display:flex; flex-wrap:wrap; gap:8px; }
    .resource-chip {
      display:inline-flex; align-items:center; gap:6px;
      background:#fff; border:1.5px solid #e5e7eb; border-radius:100px;
      padding:6px 14px; font-size:.83rem; font-weight:600; color:#374151;
    }

    /* CTA section */
    .cta-section {
      background:linear-gradient(160deg,#07071a,#1a0f3c);
      padding:80px 0; text-align:center; border-radius:24px;
      margin-top:48px; position:relative; overflow:hidden;
    }
    .cta-section::before {
      content:''; position:absolute; inset:0;
      background:radial-gradient(ellipse 50% 60% at 50% 100%,rgba(108,71,255,.25),transparent);
    }
    .cta-title { font-size:1.8rem; font-weight:800; color:#fff; margin-bottom:8px; position:relative; }
    .cta-sub { color:rgba(255,255,255,.55); font-size:.95rem; position:relative; }
    .cta-cards-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-top:36px; position:relative; }
    @media(max-width:800px){ .cta-cards-grid{grid-template-columns:repeat(2,1fr)} }
    @media(max-width:480px){ .cta-cards-grid{grid-template-columns:1fr} }
    .cta-card {
      background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1);
      border-radius:16px; padding:24px 16px; cursor:pointer;
      transition:all .2s; text-decoration:none; color:#fff;
      display:flex; flex-direction:column; align-items:center; gap:10px;
    }
    .cta-card:hover { background:rgba(108,71,255,.25); border-color:rgba(108,71,255,.5); transform:translateY(-3px); }
    .cta-card-icon { font-size:2rem; }
    .cta-card-title { font-weight:700; font-size:.9rem; }
    .cta-card-desc { font-size:.78rem; color:rgba(255,255,255,.5); line-height:1.4; text-align:center; }
    .cta-footer { color:rgba(255,255,255,.3); font-size:.8rem; margin-top:28px; position:relative; }
    .cta-footer a { color:var(--teal); }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="app-nav">
  <div class="container nav-inner">
    <a href="/" class="app-nav-logo"><img src="/assets/Logo.png" alt="Lumina"></a>
    <div class="app-nav-right">
      <a href="/assessment.php" class="app-nav-link app-nav-link-ghost">Làm lại</a>
      <a href="/dashboard.php" class="app-nav-link app-nav-link-outline">Dashboard</a>
    </div>
  </div>
</nav>

<!-- ARCHETYPE HERO -->
<section class="results-hero">
  <div class="container">
    <div class="arch-icon"><?= $icon ?></div>
    <div class="arch-type-badge">
      <?= $assessment['type'] === 'quick' ? 'Quick Scan' : 'Deep Discovery' ?> · Identity Archetype
    </div>
    <h1 class="arch-name"><?= $archetype ?></h1>
    <?php if (!empty($adesc['tagline'])): ?>
    <div class="arch-tagline"><?= htmlspecialchars($adesc['tagline']) ?></div>
    <?php endif; ?>
    <?php if (!empty($adesc['desc'])): ?>
    <p class="arch-desc"><?= nl2br(htmlspecialchars($adesc['desc'])) ?></p>
    <?php endif; ?>
    <?php if (!empty($adesc['summary'])): ?>
    <div class="arch-summary">
      <div class="arch-summary-label">✦ Điều quan trọng nhất bạn cần biết</div>
      <p><?= nl2br(htmlspecialchars($adesc['summary'])) ?></p>
    </div>
    <?php endif; ?>
  </div>
</section>

<div class="results-body">

  <!-- STRENGTHS -->
  <div class="rs" id="rs-str">
    <div class="rs-label">💪 Điểm Mạnh Cốt Lõi</div>
    <h2 class="rs-title">Bạn có những sức mạnh thiên phú</h2>
    <p class="rs-sub">Những tài năng tự nhiên mà bạn thường coi thường vì chúng quá quen thuộc.</p>
    <div class="strength-grid">
      <?php foreach ($strengths as $s): ?>
      <div class="s-card">
        <div class="s-title">✦ <?= htmlspecialchars($s['title'] ?? '') ?></div>
        <p class="s-desc"><?= htmlspecialchars($s['desc'] ?? '') ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- BLIND SPOTS -->
  <div class="rs" id="rs-bs">
    <div class="rs-label">🔍 Điểm Mù</div>
    <h2 class="rs-title">Những gì đang kéo bạn lại</h2>
    <p class="rs-sub">Nhận ra điểm mù là bước đột phá quan trọng nhất trong hành trình phát triển.</p>
    <div class="bs-grid">
      <?php foreach ($blind_spots as $bs): ?>
      <div class="bs-card">
        <div class="bs-title">⚠ <?= htmlspecialchars($bs['title'] ?? '') ?></div>
        <p class="bs-desc"><?= htmlspecialchars($bs['desc'] ?? '') ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- MOTIVATION -->
  <div class="rs" id="rs-mot">
    <div class="rs-label">🔥 Động Lực Sâu</div>
    <h2 class="rs-title">Điều thực sự thúc đẩy bạn</h2>
    <p class="rs-sub">Hiểu động lực thực sự giúp bạn làm việc với năng lượng bền vững và đúng hướng.</p>
    <div style="max-width:600px">
      <?php foreach ($motivations as $m): ?>
      <div class="mot-item">
        <div class="mot-header">
          <span class="mot-name"><?= htmlspecialchars($m['driver'] ?? '') ?></span>
          <span class="mot-pct"><?= (int)($m['percentage'] ?? 0) ?>%</span>
        </div>
        <div class="mot-track">
          <div class="mot-fill" style="width:0%" data-pct="<?= (int)($m['percentage'] ?? 0) ?>"></div>
        </div>
        <?php if (!empty($m['desc'])): ?>
        <div class="mot-desc"><?= htmlspecialchars($m['desc']) ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- LIFE WHEEL -->
  <div class="rs" id="rs-wheel">
    <div class="rs-label">⚖️ Bánh Xe Cuộc Sống</div>
    <h2 class="rs-title">Bức tranh toàn diện về cuộc sống</h2>
    <p class="rs-sub">Sự cân bằng giữa 8 lĩnh vực quyết định chất lượng cuộc sống tổng thể.</p>
    <div class="wheel-chart-wrap">
      <canvas id="wheelChart" width="380" height="380"></canvas>
    </div>
    <div class="wheel-cells">
      <?php foreach ($life_wheel as $lbl => $score):
        $pct   = min(100, max(0, ($score / 10) * 100));
        $color = $score >= 7 ? 'var(--teal)' : ($score >= 4 ? 'var(--brand)' : '#f59e0b');
      ?>
      <div class="wheel-cell">
        <div class="wheel-score" style="color:<?= $color ?>"><?= (int)$score ?></div>
        <div class="wheel-lbl"><?= htmlspecialchars($lbl) ?></div>
        <div class="wheel-bar-track"><div class="wheel-bar-fill" style="width:<?= $pct ?>%;background:<?= $color ?>"></div></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- INTERNAL CONFLICT -->
  <div class="rs" id="rs-ic">
    <div class="rs-label">🌀 Xung Đột Nội Tâm</div>
    <h2 class="rs-title">Cuộc chiến bên trong bạn</h2>
    <p class="rs-sub">Xung đột nội tâm không phải điểm yếu — đó là bằng chứng bạn đang phát triển.</p>
    <div style="max-width:660px">
      <div class="conflict-box">
        <?php if (!empty($conflict['headline'])): ?>
        <div class="conflict-head">⚡ <?= htmlspecialchars($conflict['headline']) ?></div>
        <?php endif; ?>
        <?php if (!empty($conflict['description'])): ?>
        <p class="conflict-desc"><?= nl2br(htmlspecialchars($conflict['description'])) ?></p>
        <?php endif; ?>
        <?php if (!empty($conflict['reframe'])): ?>
        <div class="conflict-reframe"><strong>✦ Góc nhìn mới:</strong> <?= htmlspecialchars($conflict['reframe']) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- GROWTH DIRECTION -->
  <div class="rs" id="rs-gd">
    <div class="rs-label">🚀 Hướng Phát Triển</div>
    <h2 class="rs-title">Con đường phía trước của bạn</h2>
    <p class="rs-sub">Dựa trên toàn bộ phân tích — đây là hướng đi phù hợp nhất với bạn.</p>
    <div style="max-width:660px">
      <div class="growth-box">
        <?php if (!empty($growth['headline'])): ?>
        <div class="growth-head">🎯 <?= htmlspecialchars($growth['headline']) ?></div>
        <?php endif; ?>
        <?php if (!empty($growth['next_90_days'])): ?>
        <p class="growth-days"><?= nl2br(htmlspecialchars($growth['next_90_days'])) ?></p>
        <?php endif; ?>
        <?php if (!empty($growth['power_question'])): ?>
        <div class="power-q">"<?= htmlspecialchars($growth['power_question']) ?>"</div>
        <?php endif; ?>
        <?php if (!empty($growth['resources'])): ?>
        <div>
          <div style="font-size:.78rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.8px;margin-bottom:10px">Bước hành động gợi ý</div>
          <div class="resource-chips">
            <?php foreach ($growth['resources'] as $r): ?>
            <span class="resource-chip">→ <?= htmlspecialchars($r) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- CTA -->
  <div class="cta-section">
    <div class="container">
      <div class="cta-title">Bước tiếp theo của bạn</div>
      <div class="cta-sub">Lumina đồng hành cùng bạn trên toàn bộ hành trình chuyển hoá</div>
      <div class="cta-cards-grid">
        <a href="/growth-plan.php?id=<?= $assessment_id ?>" class="cta-card">
          <div class="cta-card-icon">🗺️</div>
          <div class="cta-card-title">Growth Plan</div>
          <div class="cta-card-desc">Kế hoạch phát triển cá nhân hóa</div>
        </a>
        <a href="/challenge.php?id=<?= $assessment_id ?>" class="cta-card">
          <div class="cta-card-icon">⚡</div>
          <div class="cta-card-title">30-Day Challenge</div>
          <div class="cta-card-desc">Thử thách thiết kế cho archetype của bạn</div>
        </a>
        <a href="/mentor.php?id=<?= $assessment_id ?>" class="cta-card">
          <div class="cta-card-icon">🤖</div>
          <div class="cta-card-title">AI Mentor</div>
          <div class="cta-card-desc">Chat với AI cá nhân hóa theo hồ sơ bạn</div>
        </a>
        <a href="/learning.php?id=<?= $assessment_id ?>" class="cta-card">
          <div class="cta-card-icon">📚</div>
          <div class="cta-card-title">Learning Path</div>
          <div class="cta-card-desc">Lộ trình học phù hợp mục tiêu & điểm mạnh</div>
        </a>
      </div>
      <div class="cta-footer">
        Kết quả được lưu trong hồ sơ ·
        <a href="/dashboard.php">Xem lại bất cứ lúc nào</a>
      </div>
    </div>
  </div>

</div><!-- /results-body -->

<script>
// Animate result sections on scroll
const rsSections = document.querySelectorAll('.rs');
const rsObs = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in-view'); rsObs.unobserve(e.target); } });
}, { threshold: 0.08 });
rsSections.forEach(el => rsObs.observe(el));

// Animate motivation bars
const fills = document.querySelectorAll('.mot-fill');
const fillObs = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.style.width = e.target.dataset.pct + '%'; fillObs.unobserve(e.target); } });
}, { threshold: 0.3 });
fills.forEach(f => fillObs.observe(f));

// Life Wheel Radar
const labels = <?= json_encode($wheel_labels) ?>;
const values = <?= json_encode(array_map('floatval', $wheel_values)) ?>;
new Chart(document.getElementById('wheelChart').getContext('2d'), {
  type: 'radar',
  data: {
    labels,
    datasets: [{
      data: values,
      backgroundColor: 'rgba(108,71,255,.12)',
      borderColor: 'rgba(108,71,255,.8)',
      borderWidth: 2,
      pointBackgroundColor: '#6C47FF',
      pointRadius: 5, fill: true
    }]
  },
  options: {
    responsive: true,
    scales: {
      r: {
        min:0, max:10, ticks:{stepSize:2,display:false},
        grid:{color:'rgba(0,0,0,.07)'},
        pointLabels:{font:{size:11,weight:'600'},color:'#374151'},
        angleLines:{color:'rgba(0,0,0,.07)'}
      }
    },
    plugins:{legend:{display:false}}
  }
});
</script>
</body>
</html>
