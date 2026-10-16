<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (is_logged_in()) { header('Location: index.php'); exit; }
$error = ''; $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $email = strtolower(trim((string)($_POST['email'] ?? '')));
  $password = (string)($_POST['password'] ?? '');
  $stmt = $pdo->prepare('SELECT id,name,email,password_hash,role FROM users WHERE email=? LIMIT 1');
  $stmt->execute([$email]); $user = $stmt->fetch();
  if ($user && password_verify($password, $user['password_hash'])) {
    session_regenerate_id(true);
    $_SESSION['user'] = ['id'=>(int)$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']];
    header('Location: index.php'); exit;
  }
  $error = 'Email or password is incorrect.';
}
$page_title = 'Log in'; require __DIR__ . '/includes/header.php';
?>
<div class="form-card">
<h1 class="h3 section-title fw-bold">Welcome back</h1><p class="text-secondary">Log in to receive your 10% member discount.</p>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" type="email" id="email" name="email" required autocomplete="email" value="<?= e($email) ?>"></div>
<div class="mb-3"><label class="form-label" for="password">Password</label><input class="form-control" type="password" id="password" name="password" required autocomplete="current-password"></div>
<button class="btn btn-coffee w-100">Log in</button>
</form><p class="mt-3 mb-0">New here? <a href="register.php">Create an account</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
