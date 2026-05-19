<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/tenant.php';
require_once __DIR__ . '/includes/auth.php';

$tenant = detect_tenant();

// Tenant subdomain: redirect logged-in users to dashboard, others to login
if ($tenant) {
    $user = auth_user();
    if ($user && $user['tenant_id'] == $tenant['id']) {
        header('Location: /dashboard.php'); exit;
    }
    header('Location: /login.php'); exit;
}

// Main domain: public website
$lang = $_COOKIE['lumina_lang'] ?? 'vi';
if (isset($_GET['lang']) && in_array($_GET['lang'], ['vi', 'en'])) {
    $lang = $_GET['lang'];
    setcookie('lumina_lang', $lang, time() + 86400 * 365, '/');
}

$t = [
  'vi' => [
    'nav_home'      => 'Trang chủ',
    'nav_courses'   => 'Thư viện khoá học',
    'nav_path'      => 'Lộ trình',
    'nav_challenge' => 'Thử thách',
    'nav_login'     => 'Đăng nhập',
    'nav_register'  => 'Đăng ký',
    'badge'         => 'Nền tảng đánh giá hành vi AI',
    'hero_title'    => 'Đo lường & Chuyển hoá <em>Hành vi</em> của bạn',
    'hero_sub'      => 'Lumina giúp bạn hiểu sâu bản thân, phát triển điểm mạnh và xây dựng thói quen bền vững thông qua trí tuệ nhân tạo.',
    'cta_start'     => 'Bắt đầu miễn phí',
    'cta_learn'     => 'Tìm hiểu thêm',
    'how_title'     => 'Hành trình của bạn với Lumina',
    'how_sub'       => 'Bốn bước đơn giản để hiểu rõ và phát triển bản thân.',
    'step1_title'   => 'Đánh giá',
    'step1_desc'    => 'Hoàn thành bài kiểm tra hành vi thông minh, chỉ mất 10–15 phút.',
    'step2_title'   => 'Phân tích',
    'step2_desc'    => 'AI phân tích kết quả và xác định nguyên mẫu hành vi độc đáo của bạn.',
    'step3_title'   => 'Chuyển hoá',
    'step3_desc'    => 'Nhận lộ trình phát triển cá nhân hoá và khoá học phù hợp.',
    'step4_title'   => 'Theo dõi',
    'step4_desc'    => 'Ghi nhận tiến trình và duy trì thay đổi tích cực dài hạn.',
    'feat_title'    => 'Tại sao chọn Lumina?',
    'feat_sub'      => 'Được xây dựng cho cá nhân và tổ chức muốn tạo ra sự thay đổi thực sự.',
    'f1_title'      => '8 Nguyên mẫu hành vi',
    'f1_desc'       => 'Hệ thống phân loại độc quyền giúp bạn hiểu rõ phong cách hành động và tư duy.',
    'f2_title'      => 'AI cá nhân hoá',
    'f2_desc'       => 'Báo cáo và lộ trình được tạo riêng cho từng cá nhân, không phải công thức chung.',
    'f3_title'      => 'Thử thách 30 ngày',
    'f3_desc'       => 'Chuỗi thử thách micro-habit giúp xây dựng thói quen mới một cách bền vững.',
    'f4_title'      => 'Thư viện khoá học',
    'f4_desc'       => 'Nội dung được tuyển chọn phù hợp với từng nguyên mẫu và giai đoạn phát triển.',
    'f5_title'      => 'Dành cho doanh nghiệp',
    'f5_desc'       => 'Triển khai cho đội nhóm với bảng điều khiển quản lý và báo cáo tổ chức.',
    'f6_title'      => 'Bảo mật dữ liệu',
    'f6_desc'       => 'Dữ liệu của bạn được mã hoá và bảo vệ theo tiêu chuẩn bảo mật cao nhất.',
    'archetypes_title' => '8 Nguyên mẫu Hành vi',
    'archetypes_sub'   => 'Bạn thuộc nguyên mẫu nào? Hoàn thành bài đánh giá để khám phá.',
    'cta_section_title' => 'Sẵn sàng khám phá bản thân?',
    'cta_section_sub'   => 'Hàng nghìn người đã sử dụng Lumina để hiểu rõ mình hơn và tạo ra sự thay đổi thực sự.',
    'cta_request'       => 'Yêu cầu truy cập',
    'footer_copy'       => '© 2026 Lumina Global. Bản quyền thuộc về Lumina.',
    'footer_email'      => 'hello@luminaglobal.info.vn',
  ],
  'en' => [
    'nav_home'      => 'Home',
    'nav_courses'   => 'Course Library',
    'nav_path'      => 'Learning Path',
    'nav_challenge' => 'Challenges',
    'nav_login'     => 'Sign In',
    'nav_register'  => 'Get Started',
    'badge'         => 'AI Behavior Assessment Platform',
    'hero_title'    => 'Measure & Transform <em>Your Behavior</em>',
    'hero_sub'      => 'Lumina helps you deeply understand yourself, develop your strengths, and build lasting habits through artificial intelligence.',
    'cta_start'     => 'Start for Free',
    'cta_learn'     => 'Learn More',
    'how_title'     => 'Your Journey with Lumina',
    'how_sub'       => 'Four simple steps to understand and grow.',
    'step1_title'   => 'Assess',
    'step1_desc'    => 'Complete a smart behavioral assessment in just 10–15 minutes.',
    'step2_title'   => 'Analyze',
    'step2_desc'    => 'AI analyzes your results and identifies your unique behavioral archetype.',
    'step3_title'   => 'Transform',
    'step3_desc'    => 'Receive a personalized development roadmap and curated courses.',
    'step4_title'   => 'Track',
    'step4_desc'    => 'Monitor your progress and maintain positive change long-term.',
    'feat_title'    => 'Why Lumina?',
    'feat_sub'      => 'Built for individuals and organizations who want to create real change.',
    'f1_title'      => '8 Behavioral Archetypes',
    'f1_desc'       => 'A proprietary classification system to understand your action and thinking style.',
    'f2_title'      => 'AI Personalization',
    'f2_desc'       => 'Reports and roadmaps generated uniquely for each individual — not one-size-fits-all.',
    'f3_title'      => '30-Day Challenges',
    'f3_desc'       => 'Micro-habit challenge series that sustainably build new behaviors.',
    'f4_title'      => 'Course Library',
    'f4_desc'       => 'Curated content matched to each archetype and development stage.',
    'f5_title'      => 'For Organizations',
    'f5_desc'       => 'Deploy to teams with management dashboards and organizational reporting.',
    'f6_title'      => 'Data Security',
    'f6_desc'       => 'Your data is encrypted and protected to the highest security standards.',
    'archetypes_title' => '8 Behavioral Archetypes',
    'archetypes_sub'   => 'Which archetype are you? Complete the assessment to discover.',
    'cta_section_title' => 'Ready to discover yourself?',
    'cta_section_sub'   => 'Thousands of people use Lumina to understand themselves better and create real change.',
    'cta_request'       => 'Request Access',
    'footer_copy'       => '© 2026 Lumina Global. All rights reserved.',
    'footer_email'      => 'hello@luminaglobal.info.vn',
  ],
];
$txt = $t[$lang];

$archetypes = [
  ['icon' => '🦁', 'name_vi' => 'Nhà Tiên phong', 'name_en' => 'The Pioneer',   'color' => '#6C47FF'],
  ['icon' => '🌊', 'name_vi' => 'Người Kết nối',  'name_en' => 'The Connector', 'color' => '#00C9B1'],
  ['icon' => '🔥', 'name_vi' => 'Người Kiến tạo', 'name_en' => 'The Builder',   'color' => '#F5A623'],
  ['icon' => '🌿', 'name_vi' => 'Nhà Tư duy',     'name_en' => 'The Thinker',   'color' => '#34D399'],
  ['icon' => '⚡', 'name_vi' => 'Người Thực thi',  'name_en' => 'The Executor',  'color' => '#F87171'],
  ['icon' => '🎯', 'name_vi' => 'Nhà Chiến lược',  'name_en' => 'The Strategist','color' => '#60A5FA'],
  ['icon' => '💡', 'name_vi' => 'Người Đổi mới',  'name_en' => 'The Innovator', 'color' => '#A78BFA'],
  ['icon' => '🤝', 'name_vi' => 'Người Dẫn dắt',  'name_en' => 'The Leader',    'color' => '#FBBF24'],
];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lumina Global — <?= $lang === 'vi' ? 'Đo lường & Chuyển hoá Hành vi' : 'Measure & Transform Your Behavior' ?></title>
  <meta name="description" content="<?= $lang === 'vi' ? 'Nền tảng đánh giá và chuyển hoá hành vi ứng dụng trí tuệ nhân tạo.' : 'AI-powered behavioral assessment and transformation platform.' ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/lumina.css">
  <style>
    /* ── LANDING-SPECIFIC OVERRIDES ── */
    body { background: #07071a; color: #fff; }

    /* ── NAV ── */
    .pub-nav {
      position: fixed; top: 0; left: 0; right: 0; z-index: 200;
      background: rgba(7,7,26,.85);
      backdrop-filter: blur(20px) saturate(180%);
      border-bottom: 1px solid rgba(108,71,255,.18);
      padding: 0;
      transition: background .3s;
    }
    .pub-nav.scrolled { background: rgba(7,7,26,.98); }
    .pub-nav-inner {
      max-width: 1200px; margin: 0 auto; padding: 0 28px;
      display: flex; align-items: center; height: 68px; gap: 8px;
    }
    .pub-logo {
      display: flex; align-items: center; gap: 10px;
      text-decoration: none; margin-right: 32px; flex-shrink: 0;
    }
    .pub-logo img { height: 36px; width: auto; }
    .pub-logo-text {
      font-size: 1.25rem; font-weight: 800; color: #fff; letter-spacing: -.5px;
    }
    .pub-logo-text span { color: #6C47FF; }
    .pub-links {
      display: flex; gap: 4px; flex: 1; align-items: center;
    }
    .pub-link {
      padding: 8px 14px; border-radius: 10px; font-size: .92rem; font-weight: 500;
      color: rgba(255,255,255,.7); text-decoration: none;
      transition: color .2s, background .2s;
    }
    .pub-link:hover, .pub-link.active { color: #fff; background: rgba(108,71,255,.2); }
    .pub-nav-right { display: flex; align-items: center; gap: 10px; margin-left: auto; }
    .lang-toggle {
      display: flex; align-items: center; gap: 6px;
      background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.15);
      border-radius: 100px; padding: 5px 12px; font-size: .82rem; font-weight: 600;
      color: rgba(255,255,255,.7); cursor: pointer; text-decoration: none;
      transition: all .2s;
    }
    .lang-toggle:hover { background: rgba(108,71,255,.25); color: #fff; border-color: rgba(108,71,255,.4); }
    .lang-flag { font-size: 1rem; }
    .pub-btn-login {
      padding: 9px 20px; border-radius: 100px; font-size: .88rem; font-weight: 600;
      color: rgba(255,255,255,.85); text-decoration: none; border: 1.5px solid rgba(255,255,255,.2);
      transition: all .2s;
    }
    .pub-btn-login:hover { border-color: #6C47FF; color: #c4b5fd; }
    .pub-btn-register {
      padding: 9px 20px; border-radius: 100px; font-size: .88rem; font-weight: 700;
      background: #6C47FF; color: #fff; text-decoration: none;
      transition: all .2s; box-shadow: 0 4px 18px rgba(108,71,255,.4);
    }
    .pub-btn-register:hover { background: #7c57ff; transform: translateY(-1px); box-shadow: 0 6px 24px rgba(108,71,255,.55); }

    /* ── HAMBURGER (mobile) ── */
    .nav-hamburger {
      display: none; flex-direction: column; gap: 5px; cursor: pointer;
      padding: 8px; background: none; border: none; margin-left: auto;
    }
    .nav-hamburger span { display: block; width: 24px; height: 2px; background: #fff; border-radius: 2px; transition: .3s; }
    .mobile-menu {
      display: none; position: fixed; top: 68px; left: 0; right: 0;
      background: #0d0d2b; border-bottom: 1px solid rgba(108,71,255,.2);
      padding: 20px 28px; flex-direction: column; gap: 4px; z-index: 199;
    }
    .mobile-menu.open { display: flex; }
    .mobile-menu a { padding: 12px 0; color: rgba(255,255,255,.8); text-decoration: none; font-weight: 500; border-bottom: 1px solid rgba(255,255,255,.06); }
    .mobile-menu a:last-child { border: none; }

    @media (max-width: 900px) {
      .pub-links { display: none; }
      .pub-nav-right .pub-btn-login { display: none; }
      .nav-hamburger { display: flex; }
    }
    @media (max-width: 600px) {
      .pub-btn-register { display: none; }
    }

    /* ── HERO ── */
    .pub-hero {
      min-height: 100vh; display: flex; align-items: center;
      padding: 120px 0 100px; position: relative; overflow: hidden;
      background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(108,71,255,.22) 0%, transparent 70%);
    }
    .orb {
      position: absolute; border-radius: 50%;
      filter: blur(80px); pointer-events: none; animation: orb-float 8s ease-in-out infinite;
    }
    .orb-1 { width: 500px; height: 500px; background: rgba(108,71,255,.18); top: -100px; right: -100px; animation-duration: 9s; }
    .orb-2 { width: 350px; height: 350px; background: rgba(0,201,177,.12); bottom: 50px; left: -80px; animation-duration: 12s; animation-delay: -4s; }
    .orb-3 { width: 250px; height: 250px; background: rgba(245,166,35,.1); top: 40%; left: 55%; animation-duration: 7s; animation-delay: -2s; }
    @keyframes orb-float {
      0%, 100% { transform: translateY(0) scale(1); }
      50% { transform: translateY(-30px) scale(1.05); }
    }
    .hero-inner { max-width: 1200px; margin: 0 auto; padding: 0 28px; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; position: relative; }
    @media (max-width: 900px) { .hero-inner { grid-template-columns: 1fr; } .hero-visual { display: none; } }
    .hero-badge-pub {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(108,71,255,.2); border: 1px solid rgba(108,71,255,.4);
      color: #c4b5fd; border-radius: 100px; padding: 6px 16px; font-size: .82rem; font-weight: 600;
      margin-bottom: 24px;
    }
    .hero-title-pub {
      font-size: clamp(2.4rem, 5vw, 3.8rem); font-weight: 900; line-height: 1.12;
      color: #fff; margin-bottom: 22px; letter-spacing: -.02em;
    }
    .hero-title-pub em {
      font-style: normal;
      background: linear-gradient(135deg, #6C47FF 0%, #00C9B1 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .hero-sub-pub { color: rgba(255,255,255,.65); font-size: 1.1rem; line-height: 1.7; margin-bottom: 40px; max-width: 520px; }
    .hero-actions-pub { display: flex; gap: 14px; flex-wrap: wrap; }
    .btn-hero-cta {
      padding: 16px 36px; border-radius: 100px; font-size: 1rem; font-weight: 700;
      background: linear-gradient(135deg, #6C47FF, #00C9B1); color: #fff;
      text-decoration: none; transition: all .2s;
      box-shadow: 0 8px 32px rgba(108,71,255,.45);
      position: relative; overflow: hidden;
    }
    .btn-hero-cta::before {
      content: ''; position: absolute; inset: 0;
      background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,.2) 50%, transparent 100%);
      transform: translateX(-100%); animation: shine-sweep 3s ease-in-out infinite;
    }
    @keyframes shine-sweep { 0% { transform: translateX(-100%); } 60%,100% { transform: translateX(100%); } }
    .btn-hero-cta:hover { transform: translateY(-2px); box-shadow: 0 12px 40px rgba(108,71,255,.6); }
    .btn-hero-outline {
      padding: 16px 36px; border-radius: 100px; font-size: 1rem; font-weight: 600;
      border: 1.5px solid rgba(255,255,255,.25); color: rgba(255,255,255,.85);
      text-decoration: none; transition: all .2s;
    }
    .btn-hero-outline:hover { border-color: #6C47FF; color: #c4b5fd; }
    .hero-stats { display: flex; gap: 32px; margin-top: 48px; flex-wrap: wrap; }
    .hero-stat { }
    .hero-stat-num { font-size: 1.8rem; font-weight: 800; color: #fff; }
    .hero-stat-num span { color: #6C47FF; }
    .hero-stat-label { font-size: .8rem; color: rgba(255,255,255,.5); margin-top: 2px; }

    /* Hero visual */
    .hero-visual { position: relative; }
    .hero-card-float {
      background: rgba(255,255,255,.06); backdrop-filter: blur(20px);
      border: 1px solid rgba(255,255,255,.12); border-radius: 20px;
      padding: 28px; color: #fff;
      animation: card-float 6s ease-in-out infinite;
    }
    @keyframes card-float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
    .hero-card-title { font-size: .75rem; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: .1em; margin-bottom: 14px; }
    .archetype-pill {
      display: inline-flex; align-items: center; gap: 8px;
      background: linear-gradient(135deg, rgba(108,71,255,.3), rgba(0,201,177,.2));
      border: 1px solid rgba(108,71,255,.4); border-radius: 100px;
      padding: 8px 18px; font-size: .95rem; font-weight: 700; margin-bottom: 20px;
    }
    .wheel-bars { display: flex; flex-direction: column; gap: 8px; }
    .wheel-bar-row { display: flex; align-items: center; gap: 10px; font-size: .8rem; color: rgba(255,255,255,.7); }
    .wheel-bar-label { width: 90px; flex-shrink: 0; }
    .wheel-bar-track { flex: 1; height: 6px; background: rgba(255,255,255,.1); border-radius: 3px; overflow: hidden; }
    .wheel-bar-fill { height: 100%; border-radius: 3px; transition: width .8s ease; }
    .floating-badge {
      position: absolute; right: -20px; top: 30px;
      background: rgba(0,201,177,.15); border: 1px solid rgba(0,201,177,.35);
      border-radius: 14px; padding: 12px 16px; font-size: .8rem; color: #00C9B1; font-weight: 600;
      animation: badge-pop 4s ease-in-out infinite; animation-delay: -1.5s;
    }
    @keyframes badge-pop { 0%,100%{transform:translateY(0) rotate(-2deg)} 50%{transform:translateY(-8px) rotate(2deg)} }
    .floating-badge2 {
      position: absolute; left: -15px; bottom: 20px;
      background: rgba(245,166,35,.15); border: 1px solid rgba(245,166,35,.35);
      border-radius: 14px; padding: 10px 14px; font-size: .78rem; color: #F5A623; font-weight: 600;
      animation: badge-pop 5s ease-in-out infinite;
    }

    /* ── HOW IT WORKS ── */
    .pub-section { padding: 100px 0; }
    .pub-section-inner { max-width: 1200px; margin: 0 auto; padding: 0 28px; }
    .section-label {
      font-size: .78rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
      color: #6C47FF; margin-bottom: 12px;
    }
    .section-h2 { font-size: clamp(1.8rem, 3.5vw, 2.8rem); font-weight: 800; color: #fff; margin-bottom: 14px; }
    .section-sub-pub { color: rgba(255,255,255,.55); font-size: 1.05rem; margin-bottom: 64px; max-width: 560px; }

    .steps-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
    @media (max-width: 900px) { .steps-grid { grid-template-columns: repeat(2,1fr); } }
    @media (max-width: 500px) { .steps-grid { grid-template-columns: 1fr; } }
    .step-card {
      background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08);
      border-radius: 20px; padding: 28px 22px; position: relative;
      transition: background .3s, border-color .3s, transform .3s;
    }
    .step-card:hover { background: rgba(108,71,255,.1); border-color: rgba(108,71,255,.3); transform: translateY(-4px); }
    .step-num {
      font-size: 2.5rem; font-weight: 900; line-height: 1;
      background: linear-gradient(135deg, #6C47FF, #00C9B1);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      margin-bottom: 16px;
    }
    .step-title { font-size: 1.05rem; font-weight: 700; color: #fff; margin-bottom: 8px; }
    .step-desc { font-size: .88rem; color: rgba(255,255,255,.55); line-height: 1.6; }

    /* ── FEATURES ── */
    .features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
    @media (max-width: 900px) { .features-grid { grid-template-columns: repeat(2,1fr); } }
    @media (max-width: 550px) { .features-grid { grid-template-columns: 1fr; } }
    .feat-card {
      background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08);
      border-radius: 20px; padding: 28px;
      transition: background .3s, border-color .3s, transform .3s;
    }
    .feat-card:hover { background: rgba(108,71,255,.1); border-color: rgba(108,71,255,.3); transform: translateY(-4px); }
    .feat-icon {
      width: 48px; height: 48px; border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.4rem; margin-bottom: 16px;
      background: rgba(108,71,255,.2);
    }
    .feat-title { font-size: 1rem; font-weight: 700; color: #fff; margin-bottom: 8px; }
    .feat-desc { font-size: .88rem; color: rgba(255,255,255,.55); line-height: 1.6; }

    /* ── ARCHETYPES ── */
    .archetypes-bg { background: rgba(108,71,255,.06); border-top: 1px solid rgba(108,71,255,.15); border-bottom: 1px solid rgba(108,71,255,.15); }
    .arch-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    @media (max-width: 900px) { .arch-grid { grid-template-columns: repeat(2,1fr); } }
    @media (max-width: 500px) { .arch-grid { grid-template-columns: repeat(2,1fr); } }
    .arch-card {
      background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.07);
      border-radius: 16px; padding: 22px 18px; text-align: center;
      cursor: default; transition: all .25s;
    }
    .arch-card:hover { transform: translateY(-4px); }
    .arch-icon { font-size: 2rem; margin-bottom: 10px; }
    .arch-name { font-size: .9rem; font-weight: 700; color: #fff; }

    /* ── CTA SECTION ── */
    .cta-section {
      text-align: center; padding: 100px 0;
      background: radial-gradient(ellipse 60% 60% at 50% 50%, rgba(108,71,255,.2) 0%, transparent 70%);
    }
    .cta-h2 { font-size: clamp(2rem, 4vw, 3rem); font-weight: 900; color: #fff; margin-bottom: 18px; }
    .cta-sub { color: rgba(255,255,255,.6); font-size: 1.05rem; margin-bottom: 40px; max-width: 480px; margin-left: auto; margin-right: auto; }

    /* ── FOOTER ── */
    .pub-footer {
      border-top: 1px solid rgba(255,255,255,.08);
      padding: 48px 0; color: rgba(255,255,255,.4); font-size: .85rem;
    }
    .footer-inner { max-width: 1200px; margin: 0 auto; padding: 0 28px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; }
    .footer-left { display: flex; align-items: center; gap: 16px; }
    .footer-logo { font-size: 1.1rem; font-weight: 800; color: rgba(255,255,255,.7); }
    .footer-logo span { color: #6C47FF; }
    .footer-links { display: flex; gap: 20px; }
    .footer-links a { color: rgba(255,255,255,.4); text-decoration: none; transition: color .2s; }
    .footer-links a:hover { color: rgba(255,255,255,.8); }

    /* ── SCROLL REVEAL ── */
    .reveal { opacity: 0; transform: translateY(28px); transition: opacity .6s ease, transform .6s ease; }
    .reveal.in-view { opacity: 1; transform: translateY(0); }
    .reveal-d1 { transition-delay: .1s; }
    .reveal-d2 { transition-delay: .2s; }
    .reveal-d3 { transition-delay: .3s; }
    .reveal-d4 { transition-delay: .4s; }
  </style>
</head>
<body>

<!-- NAV -->
<nav class="pub-nav" id="pubNav">
  <div class="pub-nav-inner">
    <a href="/" class="pub-logo">
      <?php if (file_exists(__DIR__ . '/assets/Logo.png')): ?>
        <img src="/assets/Logo.png" alt="Lumina">
      <?php else: ?>
        <span class="pub-logo-text">Lumina<span>.</span></span>
      <?php endif; ?>
    </a>

    <div class="pub-links">
      <a href="/" class="pub-link active"><?= $txt['nav_home'] ?></a>
      <a href="/courses.php" class="pub-link"><?= $txt['nav_courses'] ?></a>
      <a href="/path.php" class="pub-link"><?= $txt['nav_path'] ?></a>
      <a href="/challenge.php" class="pub-link"><?= $txt['nav_challenge'] ?></a>
    </div>

    <div class="pub-nav-right">
      <a href="?lang=<?= $lang === 'vi' ? 'en' : 'vi' ?>" class="lang-toggle">
        <span class="lang-flag"><?= $lang === 'vi' ? '🇺🇸' : '🇻🇳' ?></span>
        <span><?= $lang === 'vi' ? 'EN' : 'VI' ?></span>
      </a>
      <a href="/login.php" class="pub-btn-login"><?= $txt['nav_login'] ?></a>
      <a href="/login.php#register" class="pub-btn-register"><?= $txt['nav_register'] ?></a>
    </div>

    <button class="nav-hamburger" id="hamburger" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<!-- MOBILE MENU -->
<div class="mobile-menu" id="mobileMenu">
  <a href="/"><?= $txt['nav_home'] ?></a>
  <a href="/courses.php"><?= $txt['nav_courses'] ?></a>
  <a href="/path.php"><?= $txt['nav_path'] ?></a>
  <a href="/challenge.php"><?= $txt['nav_challenge'] ?></a>
  <a href="/login.php"><?= $txt['nav_login'] ?></a>
  <a href="/login.php#register" style="color:#c4b5fd;font-weight:700"><?= $txt['nav_register'] ?></a>
  <a href="?lang=<?= $lang === 'vi' ? 'en' : 'vi' ?>"><?= $lang === 'vi' ? '🇺🇸 English' : '🇻🇳 Tiếng Việt' ?></a>
</div>

<!-- HERO -->
<section class="pub-hero">
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>
  <div class="hero-inner">
    <div class="hero-left">
      <div class="hero-badge-pub">✦ <?= $txt['badge'] ?></div>
      <h1 class="hero-title-pub"><?= $txt['hero_title'] ?></h1>
      <p class="hero-sub-pub"><?= $txt['hero_sub'] ?></p>
      <div class="hero-actions-pub">
        <a href="/login.php#register" class="btn-hero-cta"><?= $txt['cta_start'] ?> →</a>
        <a href="#how" class="btn-hero-outline"><?= $txt['cta_learn'] ?></a>
      </div>
      <div class="hero-stats">
        <div class="hero-stat">
          <div class="hero-stat-num">8<span>+</span></div>
          <div class="hero-stat-label"><?= $lang === 'vi' ? 'Nguyên mẫu hành vi' : 'Behavioral archetypes' ?></div>
        </div>
        <div class="hero-stat">
          <div class="hero-stat-num">10<span>–15'</span></div>
          <div class="hero-stat-label"><?= $lang === 'vi' ? 'Thời gian đánh giá' : 'Assessment time' ?></div>
        </div>
        <div class="hero-stat">
          <div class="hero-stat-num">AI<span>×</span></div>
          <div class="hero-stat-label"><?= $lang === 'vi' ? 'Phân tích cá nhân hoá' : 'Personalized analysis' ?></div>
        </div>
      </div>
    </div>

    <div class="hero-visual">
      <div style="position:relative;">
        <div class="hero-card-float">
          <div class="hero-card-title"><?= $lang === 'vi' ? 'KẾT QUẢ ĐÁNH GIÁ' : 'ASSESSMENT RESULT' ?></div>
          <div class="archetype-pill">🦁 <?= $lang === 'vi' ? 'Nhà Tiên phong' : 'The Pioneer' ?></div>
          <div class="wheel-bars">
            <?php
            $bars = $lang === 'vi'
              ? [['Quyết đoán', '#6C47FF', 88], ['Sáng tạo', '#00C9B1', 72], ['Kết nối', '#F5A623', 65], ['Tư duy', '#A78BFA', 80]]
              : [['Decisive', '#6C47FF', 88], ['Creative', '#00C9B1', 72], ['Social', '#F5A623', 65], ['Analytical', '#A78BFA', 80]];
            foreach ($bars as $b): ?>
            <div class="wheel-bar-row">
              <span class="wheel-bar-label"><?= $b[0] ?></span>
              <div class="wheel-bar-track">
                <div class="wheel-bar-fill" style="width:<?= $b[2] ?>%;background:<?= $b[1] ?>"></div>
              </div>
              <span style="font-size:.75rem;color:rgba(255,255,255,.5)"><?= $b[2] ?>%</span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="floating-badge">✓ <?= $lang === 'vi' ? 'Phân tích AI hoàn tất' : 'AI analysis complete' ?></div>
        <div class="floating-badge2">🎯 <?= $lang === 'vi' ? 'Lộ trình cá nhân' : 'Personal roadmap' ?></div>
      </div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="pub-section" id="how">
  <div class="pub-section-inner">
    <div class="section-label">HOW IT WORKS</div>
    <h2 class="section-h2 reveal"><?= $txt['how_title'] ?></h2>
    <p class="section-sub-pub reveal reveal-d1"><?= $txt['how_sub'] ?></p>
    <div class="steps-grid">
      <?php
      $steps = [
        ['01', $txt['step1_title'], $txt['step1_desc'], '📋'],
        ['02', $txt['step2_title'], $txt['step2_desc'], '🧠'],
        ['03', $txt['step3_title'], $txt['step3_desc'], '🚀'],
        ['04', $txt['step4_title'], $txt['step4_desc'], '📈'],
      ];
      foreach ($steps as $i => $s): ?>
      <div class="step-card reveal reveal-d<?= $i+1 ?>">
        <div class="step-num"><?= $s[0] ?></div>
        <div style="font-size:1.5rem;margin-bottom:12px"><?= $s[3] ?></div>
        <div class="step-title"><?= $s[1] ?></div>
        <div class="step-desc"><?= $s[2] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="pub-section" style="padding-top:20px">
  <div class="pub-section-inner">
    <div class="section-label">FEATURES</div>
    <h2 class="section-h2 reveal"><?= $txt['feat_title'] ?></h2>
    <p class="section-sub-pub reveal reveal-d1"><?= $txt['feat_sub'] ?></p>
    <div class="features-grid">
      <?php
      $feats = [
        ['🎭', $txt['f1_title'], $txt['f1_desc'], 'rgba(108,71,255,.25)'],
        ['🤖', $txt['f2_title'], $txt['f2_desc'], 'rgba(0,201,177,.2)'],
        ['⚡', $txt['f3_title'], $txt['f3_desc'], 'rgba(245,166,35,.2)'],
        ['📚', $txt['f4_title'], $txt['f4_desc'], 'rgba(167,139,250,.2)'],
        ['🏢', $txt['f5_title'], $txt['f5_desc'], 'rgba(96,165,250,.2)'],
        ['🔒', $txt['f6_title'], $txt['f6_desc'], 'rgba(52,211,153,.2)'],
      ];
      foreach ($feats as $i => $f): ?>
      <div class="feat-card reveal reveal-d<?= ($i%3)+1 ?>">
        <div class="feat-icon" style="background:<?= $f[3] ?>"><?= $f[0] ?></div>
        <div class="feat-title"><?= $f[1] ?></div>
        <div class="feat-desc"><?= $f[2] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ARCHETYPES -->
<section class="pub-section archetypes-bg">
  <div class="pub-section-inner">
    <div class="section-label">ARCHETYPES</div>
    <h2 class="section-h2 reveal"><?= $txt['archetypes_title'] ?></h2>
    <p class="section-sub-pub reveal reveal-d1"><?= $txt['archetypes_sub'] ?></p>
    <div class="arch-grid">
      <?php foreach ($archetypes as $i => $a): ?>
      <div class="arch-card reveal reveal-d<?= ($i%4)+1 ?>" style="border-color:<?= $a['color'] ?>22">
        <div class="arch-icon"><?= $a['icon'] ?></div>
        <div class="arch-name" style="color:<?= $a['color'] ?>"><?= $lang === 'vi' ? $a['name_vi'] : $a['name_en'] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <div class="pub-section-inner">
    <h2 class="cta-h2 reveal"><?= $txt['cta_section_title'] ?></h2>
    <p class="cta-sub reveal reveal-d1"><?= $txt['cta_section_sub'] ?></p>
    <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap" class="reveal reveal-d2">
      <a href="/login.php#register" class="btn-hero-cta"><?= $txt['nav_register'] ?> →</a>
      <a href="mailto:<?= $txt['footer_email'] ?>" class="btn-hero-outline">📬 <?= $txt['cta_request'] ?></a>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="pub-footer">
  <div class="footer-inner">
    <div class="footer-left">
      <span class="footer-logo">Lumina<span>.</span></span>
      <span><?= $txt['footer_copy'] ?></span>
    </div>
    <div class="footer-links">
      <a href="mailto:<?= $txt['footer_email'] ?>"><?= $txt['footer_email'] ?></a>
      <a href="/login.php"><?= $txt['nav_login'] ?></a>
    </div>
  </div>
</footer>

<script>
  // Nav scroll effect
  const nav = document.getElementById('pubNav');
  window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 30);
  });

  // Hamburger
  const ham = document.getElementById('hamburger');
  const mob = document.getElementById('mobileMenu');
  ham.addEventListener('click', () => mob.classList.toggle('open'));

  // Scroll reveal
  const revealEls = document.querySelectorAll('.reveal');
  const io = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in-view'); io.unobserve(e.target); } });
  }, { threshold: 0.12 });
  revealEls.forEach(el => io.observe(el));

  // Animate hero bars on load
  window.addEventListener('load', () => {
    document.querySelectorAll('.wheel-bar-fill').forEach(bar => {
      const w = bar.style.width; bar.style.width = '0';
      setTimeout(() => bar.style.width = w, 300);
    });
  });

  // Close mobile menu on link click
  document.querySelectorAll('.mobile-menu a').forEach(a => a.addEventListener('click', () => mob.classList.remove('open')));
</script>
</body>
</html>
