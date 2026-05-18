<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/tenant.php';
require_once __DIR__ . '/includes/auth.php';

$tenant = detect_tenant();
$user   = $tenant ? auth_user() : null;

// Logged-in user on tenant subdomain → dashboard
if ($user && $tenant && $user['tenant_id'] == $tenant['id']) {
    header('Location: /dashboard.php'); exit;
}

// Everyone else → login/register wall
header('Location: /login.php'); exit;
