<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/tenant.php';
require_once __DIR__ . '/includes/auth.php';

$tenant = detect_tenant();
if ($tenant) {
    $user = auth_user();
    if ($user && $user['tenant_id'] == $tenant['id']) { header('Location: /dashboard.php'); exit; }
    header('Location: /login.php'); exit;
}

$lang = $_COOKIE['lumina_lang'] ?? 'vi';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lumina — <?= $lang === 'vi' ? 'Thư viện khoá học' : 'Course Library' ?></title>
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
  <div class="coming-icon">📚</div>
  <h1 class="coming-h"><?= $lang === 'vi' ? 'Thư viện khoá học' : 'Course Library' ?></h1>
  <p class="coming-sub"><?= $lang === 'vi' ? 'Nội dung đang được chuẩn bị. Sắp ra mắt!' : 'Content is being prepared. Coming soon!' ?></p>
  <a href="/" class="back-link">← <?= $lang === 'vi' ? 'Quay lại trang chủ' : 'Back to home' ?></a>
</body>
</html>
