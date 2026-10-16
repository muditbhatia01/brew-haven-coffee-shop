<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (is_logged_in()) { header('Location: index.php'); exit; }
$errors = [];
$name = ''; $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $name = trim((string)($_POST['name'] ?? ''));
  $email = strtolower(trim((string)($_POST['email'] ?? '')));
  $password = (string)($_POST['password'] ?? '');
  if ($name === '' || mb_strlen($name) > 100) $errors[] = 'Enter a name up to 100 characters.';
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
  if (strlen($password) < 8) $errors[] = 'Password must contain at least 8 characters.';
  if (!$errors) {
    try {
      $stmt = $pdo->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)');
      $stmt->execute([$name,$email,password_hash($password, PASSWORD_DEFAULT),'customer']);
      session_regenerate_id(true);
      $_SESSION['user'] = ['id'=>(int)$pdo->lastInsertId(),'name'=>$name,'email'=>$email,'role'=>'customer'];
      header('Location: index.php'); exit;
    } catch (PDOException $e) {
      if ($e->getCode() === '23000') $errors[] = 'That email address is already registered.';
      else throw $e;
    }
  }
}
$page_title = 'Create account'; require __DIR__ . '/includes/header.php';
?>
<div class="form-card">
<h1 class="h3 section-title fw-bold">Create your account</h1>
<p class="text-secondary">Members get 10% off their order.</p>
<?php foreach ($errors as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="mb-3"><label class="form-label" for="name">Full name</label><input class="form-control" id="name" name="name" maxlength="100" required value="<?= e($name) ?>"></div>
<div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" type="email" id="email" name="email" required value="<?= e($email) ?>"></div>
<div class="mb-3"><label class="form-label" for="password">Password (minimum 8 characters)</label><input class="form-control" type="password" id="password" name="password" minlength="8" required autocomplete="new-password"></div>
<button class="btn btn-coffee w-100">Register and get 10% off</button>
</form><p class="mt-3 mb-0">Already registered? <a href="login.php">Log in</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
