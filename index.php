<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/tenant.php';
require_once __DIR__ . '/includes/auth.php';

$tenant = detect_tenant();
$user   = $tenant ? auth_user() : null;

if ($user && $tenant && $user['tenant_id'] == $tenant['id']) {
    header('Location: /dashboard.php'); exit;
}

// Tenant subdomain + chưa đăng nhập → thẳng đến login
if ($tenant && !$user) {
    header('Location: /login.php'); exit;
}

$is_main     = !$tenant;
$brand_color = $tenant['primary_color'] ?? '#6C47FF';
$tenant_name = $tenant ? htmlspecialchars($tenant['name']) : 'Lumina';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $is_main ? 'Lumina — Đo Lường & Chuyển Hoá Hành Vi' : "$tenant_name | Lumina" ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/lumina.css">
  <style>:root{--brand:<?= $brand_color ?>}</style>
</head>
<body class="page-landing">

<!-- ── NAVIGATION ── -->
<nav class="lumi-nav" id="navbar">
  <div class="container nav-inner">
    <a href="/" class="nav-logo">
      <img src="/assets/Logo.png" alt="Lumina" class="nav-logo-img">
    </a>
    <div class="nav-links-pill">
      <a href="#how-it-works" class="nav-link">Cách hoạt động</a>
      <a href="#results" class="nav-link">Kết quả</a>
      <?php if ($is_main): ?>
      <a href="#pricing" class="nav-link">Dành cho tổ chức</a>
      <?php endif; ?>
    </div>
    <div class="nav-actions">
      <?php if ($is_main): ?>
      <a href="https://demo.<?= LUMINA_BASE_DOMAIN ?>/login.php" class="btn btn-outline btn-sm">Demo</a>
      <a href="https://demo.<?= LUMINA_BASE_DOMAIN ?>" class="btn btn-primary btn-sm">Bắt đầu →</a>
      <?php else: ?>
      <a href="/login.php" class="btn btn-outline btn-sm">Đăng nhập</a>
      <a href="/login.php#register" class="btn btn-primary btn-sm" onclick="sessionStorage.setItem('lumina_tab','register')">Bắt đầu miễn phí</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- ── HERO ── -->
<section class="lumi-hero" id="hero">

  <!-- Animated background -->
  <div class="hero-bg">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="grid-lines"></div>
  </div>

  <!-- Optional: swap in a real video by dropping hero-bg.mp4 into /assets/
  <video class="hero-video" autoplay muted loop playsinline>
    <source src="/assets/hero-bg.mp4" type="video/mp4">
  </video>
  -->

  <div class="container hero-container">
    <div class="hero-content">

      <div class="hero-badge animate-fade-up" style="animation-delay:.1s">
        <span class="pulse-dot"></span>
        <?= $is_main ? 'Nền tảng AI · Chuyển Hoá Hành Vi' : "$tenant_name · Đánh Giá AI" ?>
      </div>

      <h1 class="hero-title animate-fade-up" style="animation-delay:.2s">
        <?php if ($is_main): ?>
        Đo lường &amp; chuyển hoá<br>
        <span class="shiny-text">con người thực sự</span><br>
        của bạn
        <?php else: ?>
        Khám phá <span class="shiny-text">bản sắc cốt lõi</span><br>
        và hướng phát triển
        <?php endif; ?>
      </h1>

      <p class="hero-sub animate-fade-up" style="animation-delay:.35s">
        <?php if ($is_main): ?>
        Lumina không chỉ cho bạn biết bạn là ai — mà giúp bạn <strong>thực sự thay đổi</strong>.
        Vòng lặp: Đo lường → Phân tích → Chuyển hoá → Theo dõi tiến trình.
        <?php else: ?>
        Trả lời 15–35 câu hỏi chuyên sâu. AI sẽ phân tích và trao cho bạn bức tranh toàn diện
        về bản sắc, điểm mạnh, điểm mù và hướng phát triển cụ thể.
        <?php endif; ?>
      </p>

      <div class="hero-actions animate-fade-up" style="animation-delay:.5s">
        <?php if ($is_main): ?>
        <a href="https://demo.<?= LUMINA_BASE_DOMAIN ?>" class="btn-hero-cta">
          Trải nghiệm miễn phí
          <svg class="btn-arrow" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <a href="#how-it-works" class="btn-hero-ghost">Xem cách hoạt động</a>
        <?php else: ?>
        <a href="/login.php" class="btn-hero-cta">
          Bắt đầu miễn phí
          <svg class="btn-arrow" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <?php endif; ?>
      </div>

      <div class="hero-stats animate-fade-up" style="animation-delay:.65s">
        <div class="hero-stat"><span class="stat-num">8</span><span class="stat-label">Identity Archetypes</span></div>
        <div class="stat-divider"></div>
        <div class="hero-stat"><span class="stat-num">7</span><span class="stat-label">Chiều phân tích</span></div>
        <div class="stat-divider"></div>
        <div class="hero-stat"><span class="stat-num">90</span><span class="stat-label">Ngày kế hoạch</span></div>
      </div>

    </div>

    <!-- Floating visual card -->
    <div class="hero-visual animate-fade-up" style="animation-delay:.4s" aria-hidden="true">
      <div class="result-preview-card">
        <div class="rpc-header">
          <div class="rpc-avatar">🔥</div>
          <div>
            <div class="rpc-archetype">Chất Xúc Tác</div>
            <div class="rpc-sub">Identity Archetype</div>
          </div>
        </div>
        <div class="rpc-bars">
          <div class="rpc-bar-row">
            <span>Tầm nhìn</span>
            <div class="rpc-track"><div class="rpc-fill" data-w="88"></div></div>
            <span class="rpc-pct">88%</span>
          </div>
          <div class="rpc-bar-row">
            <span>Ảnh hưởng</span>
            <div class="rpc-track"><div class="rpc-fill" data-w="75"></div></div>
            <span class="rpc-pct">75%</span>
          </div>
          <div class="rpc-bar-row">
            <span>Hành động</span>
            <div class="rpc-track"><div class="rpc-fill" data-w="62"></div></div>
            <span class="rpc-pct">62%</span>
          </div>
        </div>
        <div class="rpc-insight">"Câu hỏi đột phá dành cho bạn..."</div>
      </div>
    </div>
  </div>

  <a href="#how-it-works" class="scroll-hint" aria-label="Cuộn xuống">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
  </a>
</section>

<!-- ── HOW IT WORKS ── -->
<section class="section section-dark" id="how-it-works">
  <div class="container">
    <div class="text-center section-header">
      <div class="section-label">Vòng lặp Lumina</div>
      <h2>Không chỉ là assessment —<br>là hành trình chuyển hoá thực sự</h2>
    </div>
    <div class="loop-steps">
      <?php
      $steps = [
        ['01','Đo Lường','15–35 câu hỏi được thiết kế bởi chuyên gia tâm lý, đào sâu vào bản sắc, điểm mù và động lực thực sự của bạn.','📊'],
        ['02','Phân Tích AI','GoClaw AI với context 200K phân tích toàn diện — không phải kết quả chung chung, mà cá nhân hóa hoàn toàn cho bạn.','🔬'],
        ['03','Chuyển Hoá','Kế hoạch 90 ngày cụ thể, câu hỏi đột phá và hướng hành động rõ ràng để bắt đầu thay đổi ngay hôm nay.','⚡'],
        ['04','Theo Dõi','Dashboard tiến trình — đo lường sự thay đổi thực tế theo thời gian, không phải chỉ "xem kết quả" rồi thôi.','📈'],
      ];
      foreach ($steps as [$num, $title, $desc, $icon]): ?>
      <div class="loop-step">
        <div class="loop-step-num"><?= $num ?></div>
        <div class="loop-step-icon"><?= $icon ?></div>
        <h3 class="loop-step-title"><?= $title ?></h3>
        <p class="loop-step-desc"><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── 7 RESULT SECTIONS ── -->
<section class="section" id="results">
  <div class="container">
    <div class="text-center section-header">
      <div class="section-label">Kết Quả Bạn Nhận Được</div>
      <h2>7 chiều sâu phân tích toàn diện</h2>
      <p class="section-sub">Mỗi chiều phân tích là một lớp hiểu biết về bản thân — từ bề mặt đến gốc rễ sâu nhất.</p>
    </div>
    <div class="features-grid">
      <?php
      $features = [
        ['🎭','Identity Archetype','1 trong 8 bản sắc cốt lõi — vai trò tự nhiên của bạn trong cuộc sống và công việc','--brand'],
        ['💪','Điểm Mạnh Cốt Lõi','4 sức mạnh thiên phú bạn thường coi thường vì quá quen — bắt đầu tận dụng chúng','--teal'],
        ['🔍','Điểm Mù','3 rào cản ngầm đang kéo chậm sự phát triển — nhận ra để phá vỡ','--gold'],
        ['🔥','Động Lực Sâu','Những gì thực sự thúc đẩy bạn — không phải điều bạn nghĩ mà là điều bạn cảm','--brand'],
        ['⚖️','Bánh Xe Cuộc Sống','Radar chart 8 lĩnh vực — bức tranh trực quan về sự cân bằng hiện tại','--teal'],
        ['🌀','Xung Đột Nội Tâm','Cuộc chiến bên trong và cách reframe để biến nó thành sức mạnh','--gold'],
        ['🚀','Hướng Phát Triển 90 Ngày','Kế hoạch cụ thể + câu hỏi đột phá được cá nhân hóa hoàn toàn cho bạn','--brand'],
      ];
      foreach ($features as $i => [$icon, $title, $desc, $accent]): ?>
      <div class="feature-card" style="--accent: var(<?= $accent ?>)">
        <div class="feature-icon"><?= $icon ?></div>
        <h4 class="feature-title"><?= $title ?></h4>
        <p class="feature-desc"><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── ASSESSMENT TYPES ── -->
<section class="section section-dark" id="pricing">
  <div class="container">
    <div class="text-center section-header">
      <div class="section-label">Chọn Độ Sâu</div>
      <h2>Phù hợp với thời gian<br>và mức độ cam kết của bạn</h2>
    </div>
    <div class="type-cards-motion">
      <div class="type-card-motion">
        <div class="tcm-icon">⚡</div>
        <div class="tcm-duration">~ 5 phút</div>
        <h3 class="tcm-title">Quick Scan</h3>
        <p class="tcm-desc">15 câu hỏi thiết yếu. Nhanh, chính xác và đủ sâu để bắt đầu hành trình của bạn.</p>
        <ul class="tcm-features">
          <li>Identity Archetype</li>
          <li>3 điểm mạnh cốt lõi</li>
          <li>Hướng phát triển ngắn hạn</li>
        </ul>
      </div>
      <div class="type-card-motion type-card-featured">
        <div class="tcm-badge">PHỔ BIẾN NHẤT</div>
        <div class="tcm-icon">🔭</div>
        <div class="tcm-duration tcm-duration-brand">~ 15–20 phút</div>
        <h3 class="tcm-title">Deep Discovery</h3>
        <p class="tcm-desc">35 câu hỏi chuyên sâu. Phân tích toàn diện nhất — cá nhân hóa cao và kế hoạch 90 ngày đầy đủ.</p>
        <ul class="tcm-features">
          <li>Tất cả 7 chiều phân tích</li>
          <li>Bánh xe cuộc sống radar chart</li>
          <li>Kế hoạch 90 ngày cụ thể</li>
          <li>Câu hỏi đột phá cá nhân hóa</li>
        </ul>
      </div>
    </div>
    <div class="text-center" style="margin-top:48px">
      <?php if ($is_main): ?>
      <a href="https://demo.<?= LUMINA_BASE_DOMAIN ?>" class="btn-hero-cta">
        Bắt đầu ngay miễn phí
        <svg class="btn-arrow" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <?php else: ?>
      <a href="/login.php" class="btn-hero-cta">
        Bắt đầu miễn phí
        <svg class="btn-arrow" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if ($is_main): ?>
<!-- ── B2B CTA ── -->
<section class="section section-b2b">
  <div class="container">
    <div class="b2b-inner">
      <div class="b2b-text">
        <div class="section-label">Dành Cho Tổ Chức</div>
        <h2>Triển khai Lumina<br>cho đội nhóm của bạn</h2>
        <p>Mỗi tổ chức nhận một subdomain riêng. Quản lý toàn bộ hành trình phát triển của thành viên từ một dashboard duy nhất.</p>
        <a href="mailto:hello@luminaglobal.info.vn" class="btn btn-primary" style="margin-top:24px;display:inline-flex">Liên hệ ngay →</a>
      </div>
      <div class="b2b-features">
        <?php
        $b2b = [
          ['🏢','Subdomain riêng','demo.luminaglobal.info.vn cho tổ chức của bạn'],
          ['👥','Quản lý thành viên','Dashboard theo dõi tiến trình toàn đội'],
          ['📊','Analytics sâu','Báo cáo hành vi và xu hướng phát triển'],
          ['🎯','Customizable','Tuỳ chỉnh theo văn hoá tổ chức'],
        ];
        foreach ($b2b as [$icon, $title, $desc]): ?>
        <div class="b2b-feature">
          <div class="b2b-feature-icon"><?= $icon ?></div>
          <div>
            <div class="b2b-feature-title"><?= $title ?></div>
            <div class="b2b-feature-desc"><?= $desc ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── FOOTER ── -->
<footer class="lumi-footer">
  <div class="container footer-inner">
    <div class="footer-brand">
      <img src="/assets/Logo.png" alt="Lumina" class="footer-logo">
      <p class="footer-tagline">Đo lường &amp; chuyển hoá hành vi người dùng</p>
    </div>
    <div class="footer-links">
      <a href="#how-it-works">Cách hoạt động</a>
      <a href="#results">Kết quả</a>
      <?php if ($is_main): ?>
      <a href="mailto:hello@luminaglobal.info.vn">Liên hệ</a>
      <?php endif; ?>
    </div>
    <div class="footer-copy">
      © <?= date('Y') ?> Lumina · Powered by GoClaw AI
    </div>
  </div>
</footer>

<script>
// Navbar scroll effect
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  navbar.classList.toggle('nav-scrolled', window.scrollY > 40);
}, { passive: true });

// Intersection Observer for scroll animations
const animEls = document.querySelectorAll('.feature-card, .loop-step, .type-card-motion, .b2b-feature');
const obs = new IntersectionObserver((entries) => {
  entries.forEach((e, i) => {
    if (e.isIntersecting) {
      setTimeout(() => e.target.classList.add('in-view'), i * 80);
      obs.unobserve(e.target);
    }
  });
}, { threshold: 0.12 });
animEls.forEach(el => obs.observe(el));

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
  });
});

// Animate result preview bars on load
setTimeout(() => {
  document.querySelectorAll('.rpc-fill[data-w]').forEach(el => {
    el.style.transition = 'width 1.2s cubic-bezier(.4,0,.2,1)';
    el.style.width = el.dataset.w + '%';
  });
}, 600);
</script>
</body>
</html>
