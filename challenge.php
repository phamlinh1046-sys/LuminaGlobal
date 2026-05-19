<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/tenant.php';
require_once __DIR__ . '/includes/auth.php';

$tenant = detect_tenant();

// Main domain: show public placeholder
if (!$tenant) {
    $lang = $_COOKIE['lumina_lang'] ?? 'vi';
    ?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lumina — <?= $lang === 'vi' ? 'Thử thách 30 ngày' : '30-Day Challenges' ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/lumina.css">
  <style>
    body { background: #07071a; color: #fff; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; }
    .coming-icon { font-size: 4rem; margin-bottom: 24px; }
    .coming-h { font-size: 2.2rem; font-weight: 800; margin-bottom: 12px; }
    .coming-sub { color: rgba(255,255,255,.55); font-size: 1rem; margin-bottom: 32px; }
    .back-link { color: #c4b5fd; text-decoration: none; font-weight: 600; border: 1.5px solid rgba(108,71,255,.4); border-radius: 100px; padding: 10px 24px; transition: all .2s; }
    .back-link:hover { background: rgba(108,71,255,.2); }
  </style>
</head>
<body>
  <div class="coming-icon">⚡</div>
  <h1 class="coming-h"><?= $lang === 'vi' ? 'Thử thách 30 ngày' : '30-Day Challenges' ?></h1>
  <p class="coming-sub"><?= $lang === 'vi' ? 'Chuỗi thử thách micro-habit đang được thiết kế. Sắp ra mắt!' : 'Micro-habit challenge series coming soon!' ?></p>
  <a href="/" class="back-link">← <?= $lang === 'vi' ? 'Quay lại trang chủ' : 'Back to home' ?></a>
</body>
</html>
    <?php exit;
}

$user = auth_user();
if (!$user || $user['tenant_id'] != $tenant['id']) { header('Location: /login.php'); exit; }

$assessment_id = (int)($_GET['id'] ?? 0);
if (!$assessment_id) { header('Location: /dashboard.php'); exit; }

$assessment = db_row('SELECT * FROM assessments WHERE id=:id AND user_id=:u', [':id' => $assessment_id, ':u' => $user['id']]);
if (!$assessment || $assessment['status'] !== 'completed') { header('Location: /dashboard.php'); exit; }

$result = db_row('SELECT * FROM results WHERE assessment_id=:a', [':a' => $assessment_id]);
$archetype = htmlspecialchars($result['archetype'] ?? 'Người Khám Phá');
$brand_color = $tenant['primary_color'] ?? '#6C47FF';

$current_day = 4; // Mock current day
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>30-Day Challenge — Lumina</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/lumina.css">
  <style>
    :root{--brand:<?= $brand_color ?>}
    body { background: #07071a; color: #fff; padding-top: 62px; }

    /* Nav */
    .app-nav {
      position:fixed; top:0; left:0; right:0; z-index:100;
      background:rgba(7,7,26,.9); backdrop-filter:blur(12px);
      border-bottom:1px solid rgba(255,255,255,.08); padding:14px 0;
    }
    .app-nav .nav-inner { display:flex; align-items:center; justify-content:space-between; }
    .app-nav-logo img { height:32px; width:auto; }
    .app-nav-right { display:flex; align-items:center; gap:10px; }
    .app-nav-link-ghost {
      padding:7px 18px; border-radius:100px; font-size:.83rem; font-weight:600; text-decoration:none;
      background:rgba(255,255,255,.08); color:rgba(255,255,255,.7); border:1px solid rgba(255,255,255,.1);
      transition:all .2s;
    }
    .app-nav-link-ghost:hover { background:rgba(255,255,255,.15); color:#fff; }

    /* Hero */
    .chal-hero { padding: 60px 0 40px; text-align: center; position: relative; }
    .chal-hero::before { content:''; position:absolute; inset:0; background:radial-gradient(ellipse 50% 50% at 50% 50%, rgba(108,71,255,.25), transparent); pointer-events:none; }
    .chal-badge { display:inline-flex; align-items:center; gap:8px; background:rgba(0,201,177,.15); border:1px solid rgba(0,201,177,.3); color:var(--teal); padding:5px 16px; border-radius:100px; font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:16px; }
    .chal-title { font-size: 2.5rem; font-weight: 900; margin-bottom: 12px; letter-spacing: -.03em; }
    .chal-desc { color: rgba(255,255,255,.6); font-size: 1.05rem; max-width: 540px; margin: 0 auto; line-height: 1.6; }

    /* Grid */
    .grid-wrap { max-width: 900px; margin: 0 auto 80px; padding: 0 24px; }
    .day-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 16px; }
    @media(max-width: 768px) { .day-grid { grid-template-columns: repeat(4, 1fr); } }
    @media(max-width: 480px) { .day-grid { grid-template-columns: repeat(3, 1fr); } }

    .day-card {
      background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
      border-radius: 16px; aspect-ratio: 1; display: flex; flex-direction: column;
      align-items: center; justify-content: center; cursor: pointer; transition: all .2s;
      position: relative; overflow: hidden;
    }
    .day-card:hover:not(.locked) { border-color: var(--brand); transform: translateY(-3px); box-shadow: 0 8px 24px rgba(108,71,255,.2); background: rgba(108,71,255,.1); }
    .day-card.done { background: rgba(0,201,177,.1); border-color: rgba(0,201,177,.3); color: var(--teal); }
    .day-card.active { border-color: var(--brand); background: rgba(108,71,255,.15); box-shadow: 0 0 0 2px rgba(108,71,255,.4); }
    .day-card.locked { opacity: .4; cursor: not-allowed; background: transparent; }

    .day-num { font-size: 1.8rem; font-weight: 800; line-height: 1; margin-bottom: 4px; }
    .day-lbl { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,.5); }
    .day-card.done .day-lbl { color: var(--teal); }
    .day-card.active .day-lbl { color: #c4b5fd; }
    
    .lock-icon { position: absolute; font-size: 1.2rem; opacity: .5; }
    .day-card.locked .day-num, .day-card.locked .day-lbl { display: none; }

    /* Modal */
    .modal-overlay {
      position: fixed; inset: 0; background: rgba(0,0,0,.7); backdrop-filter: blur(8px);
      z-index: 200; display: flex; align-items: center; justify-content: center;
      opacity: 0; pointer-events: none; transition: opacity .3s; padding: 24px;
    }
    .modal-overlay.show { opacity: 1; pointer-events: auto; }
    .modal-box {
      background: #1a1a3e; border: 1px solid rgba(255,255,255,.15); border-radius: 24px;
      width: 100%; max-width: 440px; padding: 40px; text-align: center;
      transform: translateY(20px); transition: transform .3s cubic-bezier(.4,0,.2,1);
      box-shadow: 0 24px 80px rgba(0,0,0,.5); position: relative;
    }
    .modal-overlay.show .modal-box { transform: none; }
    .modal-close { position: absolute; top: 20px; right: 20px; background: transparent; border: none; color: rgba(255,255,255,.5); font-size: 1.5rem; cursor: pointer; }
    .modal-close:hover { color: #fff; }
    
    .m-day { font-size: .85rem; font-weight: 700; color: var(--brand); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
    .m-title { font-size: 1.6rem; font-weight: 800; margin-bottom: 16px; color: #fff; }
    .m-desc { color: rgba(255,255,255,.7); font-size: .95rem; line-height: 1.6; margin-bottom: 32px; }
    .btn-done {
      display: inline-flex; width: 100%; justify-content: center; padding: 14px;
      background: var(--brand); color: #fff; border-radius: 100px; font-weight: 700;
      border: none; cursor: pointer; font-family: var(--font); font-size: .95rem;
      transition: all .2s;
    }
    .btn-done:hover { box-shadow: 0 8px 24px rgba(108,71,255,.4); transform: translateY(-2px); }
  </style>
</head>
<body>

<nav class="app-nav">
  <div class="container nav-inner">
    <a href="/" class="app-nav-logo"><img src="/assets/Logo.png" alt="Lumina"></a>
    <div class="app-nav-right">
      <a href="/results.php?id=<?= $assessment_id ?>" class="app-nav-link-ghost">← Về Kết quả</a>
    </div>
  </div>
</nav>

<div class="chal-hero">
  <div class="container">
    <div class="chal-badge">✦ 30-Day Challenge</div>
    <h1 class="chal-title">Mở khóa tiềm năng <?= $archetype ?></h1>
    <p class="chal-desc">Mỗi ngày một hành động nhỏ được thiết kế riêng cho Archetype của bạn để tạo ra thói quen chuyển hoá.</p>
  </div>
</div>

<div class="grid-wrap">
  <div class="day-grid">
    <?php for ($i = 1; $i <= 30; $i++): 
      $status = '';
      if ($i < $current_day) $status = 'done';
      elseif ($i == $current_day) $status = 'active';
      else $status = 'locked';
    ?>
    <div class="day-card <?= $status ?>" onclick="openDay(<?= $i ?>, '<?= $status ?>')">
      <?php if ($status === 'locked'): ?>
        <div class="lock-icon">🔒</div>
      <?php endif; ?>
      <div class="day-num"><?= $i ?></div>
      <div class="day-lbl">Day</div>
    </div>
    <?php endfor; ?>
  </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal()">×</button>
    <div class="m-day" id="mDay">Day X</div>
    <div class="m-title" id="mTitle">Nhiệm vụ hôm nay</div>
    <div class="m-desc" id="mDesc">Thực hành lắng nghe sâu trong 1 cuộc hội thoại. Hãy tập trung 100% vào người đối diện mà không chuẩn bị sẵn câu trả lời.</div>
    <button class="btn-done" onclick="completeTask()">Hoàn thành nhiệm vụ ✓</button>
  </div>
</div>

<script>
let currentSelectedDay = null;

function openDay(day, status) {
  if (status === 'locked') return;
  currentSelectedDay = day;
  document.getElementById('mDay').textContent = `Ngày ${day}`;
  
  const titles = [
    'Phá vỡ vòng lặp', 'Nhận diện điểm mù', 'Tĩnh tâm 15 phút', 'Hành động can đảm', 'Reframe góc nhìn'
  ];
  document.getElementById('mTitle').textContent = titles[(day-1) % titles.length];
  
  const btn = document.querySelector('.btn-done');
  if (status === 'done') {
    btn.textContent = 'Đã hoàn thành ✓';
    btn.style.background = 'var(--teal)';
    btn.disabled = true;
  } else {
    btn.textContent = 'Hoàn thành nhiệm vụ ✓';
    btn.style.background = 'var(--brand)';
    btn.disabled = false;
  }
  
  document.getElementById('modalOverlay').classList.add('show');
}

function closeModal() {
  document.getElementById('modalOverlay').classList.remove('show');
}

function completeTask() {
  const cards = document.querySelectorAll('.day-card');
  cards[currentSelectedDay - 1].classList.remove('active');
  cards[currentSelectedDay - 1].classList.add('done');
  
  if (currentSelectedDay < 30) {
    cards[currentSelectedDay].classList.remove('locked');
    cards[currentSelectedDay].classList.add('active');
  }
  closeModal();
}
</script>
</body>
</html>
