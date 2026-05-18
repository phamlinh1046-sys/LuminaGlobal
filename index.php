<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/tenant.php';
require_once __DIR__ . '/includes/auth.php';

$tenant = detect_tenant();
$user   = $tenant ? auth_user() : null;

// Logged-in user → dashboard
if ($user && $tenant && $user['tenant_id'] == $tenant['id']) {
    header('Location: /dashboard.php'); exit;
}

// No subdomain → main Lumina landing
if (!$tenant) {
    // Show main Lumina marketing page
    $is_main = true;
} else {
    $is_main = false;
}

$brand_color  = $tenant['primary_color'] ?? '#6C47FF';
$tenant_name  = $tenant ? htmlspecialchars($tenant['name']) : 'Lumina';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $is_main ? 'Lumina — Nền tảng Phát Triển Bản Thân AI' : "$tenant_name | Lumina" ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/lumina.css">
  <style>:root{--brand:<?= $brand_color ?>}</style>
</head>
<body>

<nav class="lumi-nav">
  <div class="container nav-inner">
    <a href="/" class="nav-logo">✦ <span>Lumina</span></a>
    <div class="nav-actions">
      <?php if ($is_main): ?>
      <a href="https://demo.<?= LUMINA_BASE_DOMAIN ?>/login.php" class="btn btn-outline">Thử Demo</a>
      <?php else: ?>
      <a href="/login.php" class="btn btn-outline">Đăng nhập</a>
      <a href="/login.php#register" class="btn btn-primary" onclick="sessionStorage.setItem('lumina_tab','register')">Bắt đầu miễn phí</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="lumi-hero">
  <div class="container">
    <div class="hero-content fade-in">
      <?php if ($is_main): ?>
      <div class="hero-badge">Powered by Claude AI · Anthropic</div>
      <h1 class="hero-title">
        Khám phá <em>con người thực sự</em><br>của bạn
      </h1>
      <p class="hero-sub">
        Lumina là nền tảng đánh giá bản thân AI đầu tiên được cá nhân hóa hoàn toàn —
        từ Identity Archetype, Điểm Mạnh, đến Hướng Phát Triển trong 90 ngày tới.
      </p>
      <div class="hero-actions">
        <a href="https://demo.<?= LUMINA_BASE_DOMAIN ?>" class="btn btn-primary btn-lg">Trải nghiệm miễn phí →</a>
        <a href="#how-it-works" class="btn btn-outline btn-lg">Xem cách hoạt động</a>
      </div>
      <?php else: ?>
      <div class="hero-badge"><?= $tenant_name ?> · Đánh Giá Bản Thân AI</div>
      <h1 class="hero-title">
        Khám phá <em>con người thực sự</em><br>của bạn
      </h1>
      <p class="hero-sub">
        Trả lời 15–35 câu hỏi chuyên sâu. AI sẽ phân tích và trao cho bạn bức tranh
        toàn diện về bản sắc, điểm mạnh, điểm mù và hướng phát triển cụ thể.
      </p>
      <div class="hero-actions">
        <a href="/login.php" class="btn btn-primary btn-lg">Bắt đầu miễn phí →</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- WHAT YOU GET -->
<section class="section" id="how-it-works">
  <div class="container">
    <div class="text-center" style="margin-bottom:48px">
      <div class="section-label" style="display:inline-flex;margin-bottom:12px">Kết Quả Bạn Nhận Được</div>
      <h2>7 chiều sâu phân tích toàn diện</h2>
    </div>
    <div class="grid-3" style="gap:20px">
      <?php
      $features = [
        ['🎭','Identity Archetype','Bản sắc cốt lõi và vai trò tự nhiên của bạn trong cuộc sống'],
        ['💪','Điểm Mạnh Cốt Lõi','4 sức mạnh thiên phú bạn thường coi thường vì quá quen'],
        ['🔍','Điểm Mù','3 rào cản ngầm đang kéo chậm sự phát triển của bạn'],
        ['🔥','Động Lực Sâu','Những gì thực sự thúc đẩy bạn — không phải điều bạn nghĩ'],
        ['⚖️','Bánh Xe Cuộc Sống','Bức tranh 8 lĩnh vực với radar chart trực quan'],
        ['🌀','Xung Đột Nội Tâm','Cuộc chiến bên trong và cách biến nó thành sức mạnh'],
        ['🚀','Hướng Phát Triển','Kế hoạch 90 ngày và câu hỏi đột phá dành riêng cho bạn'],
      ];
      foreach ($features as [$icon, $title, $desc]): ?>
      <div class="card fade-in">
        <div style="font-size:1.8rem;margin-bottom:12px"><?= $icon ?></div>
        <h4 style="margin-bottom:6px"><?= $title ?></h4>
        <p class="text-muted" style="font-size:.9rem;line-height:1.5"><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ASSESSMENT TYPES -->
<section class="section" style="background:var(--navy);color:var(--white)">
  <div class="container">
    <div class="text-center" style="margin-bottom:48px">
      <h2 style="color:var(--white)">Chọn độ sâu phù hợp với bạn</h2>
    </div>
    <div class="grid-2" style="max-width:800px;margin:0 auto;gap:24px">
      <div style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:36px">
        <div style="font-size:2.5rem;margin-bottom:16px">⚡</div>
        <h3 style="color:var(--white)">Quick Scan</h3>
        <p style="color:rgba(255,255,255,.6);margin:12px 0">15 câu hỏi thiết yếu. Nhanh và chính xác.</p>
        <div style="color:var(--teal);font-weight:700;font-size:1.2rem">~ 5 phút</div>
      </div>
      <div style="background:rgba(108,71,255,.2);border:2px solid var(--brand);border-radius:20px;padding:36px;position:relative">
        <div style="position:absolute;top:-12px;right:20px;background:var(--teal);color:var(--navy);padding:4px 14px;border-radius:100px;font-size:.75rem;font-weight:800">PHỔ BIẾN NHẤT</div>
        <div style="font-size:2.5rem;margin-bottom:16px">🔭</div>
        <h3 style="color:var(--white)">Deep Discovery</h3>
        <p style="color:rgba(255,255,255,.6);margin:12px 0">35 câu hỏi. Phân tích toàn diện và cá nhân hóa cao nhất.</p>
        <div style="color:var(--brand);font-weight:700;font-size:1.2rem">~ 15–20 phút</div>
      </div>
    </div>
    <div style="text-align:center;margin-top:40px">
      <?php if ($is_main): ?>
      <a href="https://demo.<?= LUMINA_BASE_DOMAIN ?>" class="btn btn-primary btn-lg">Bắt đầu ngay →</a>
      <?php else: ?>
      <a href="/login.php" class="btn btn-primary btn-lg">Bắt đầu ngay →</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer style="background:var(--navy-mid);color:rgba(255,255,255,.5);padding:40px 0;text-align:center;border-top:1px solid rgba(255,255,255,.06)">
  <p>✦ Lumina · Powered by Claude AI · <?= date('Y') ?></p>
  <?php if ($is_main): ?>
  <p style="margin-top:8px;font-size:.85rem">
    Bạn là trainer hoặc tổ chức? <a href="mailto:hello@luminaglobal.info.vn" style="color:var(--brand)">Liên hệ để tạo subdomain riêng</a>
  </p>
  <?php endif; ?>
</footer>

<script>
const fadeEls = document.querySelectorAll('.fade-in');
const obs = new IntersectionObserver(entries => entries.forEach(e => { if(e.isIntersecting) e.target.style.animationPlayState='running'; }), {threshold:.1});
fadeEls.forEach(el => { el.style.animationPlayState='paused'; obs.observe(el); });

// Auto-switch tab on login page if coming from register CTA
if (sessionStorage.getItem('lumina_tab') === 'register') {
  sessionStorage.removeItem('lumina_tab');
}
</script>
</body>
</html>
