<?php
require_once __DIR__ . '/bootstrap.php';
$page_title = $page_title ?? 'Brew Haven Coffee';
$base_url = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') ? '../' : '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($page_title) ?> | Brew Haven</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e($base_url) ?>assets/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark coffee-nav">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= e($base_url) ?>index.php">☕ Brew Haven</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="navMain">
      <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
        <a class="nav-link" href="<?= e($base_url) ?>index.php">Menu</a>
        <a class="nav-link" href="<?= e($base_url) ?>cart.php">Cart (<?= cart_count() ?>)</a>
        <?php if (is_admin()): ?>
          <a class="nav-link" href="<?= e($base_url) ?>admin/index.php">Admin</a>
          <a class="nav-link" href="<?= e($base_url) ?>admin/orders.php">Orders</a>
        <?php endif; ?>
        <?php if (is_logged_in()): ?>
          <span class="navbar-text me-lg-2">Hi, <?= e($_SESSION['user']['name']) ?></span>
          <form method="post" action="<?= e($base_url) ?>logout.php" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <button class="btn btn-sm btn-outline-light" type="submit">Log out</button>
          </form>
        <?php else: ?>
          <a class="nav-link" href="<?= e($base_url) ?>login.php">Log in</a>
          <a class="btn btn-sm btn-light" href="<?= e($base_url) ?>register.php">Register</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<main class="container py-4">
