<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || !$_SESSION['cart']) { header('Location: cart.php'); exit; }
function get_cart_products(PDO $pdo, array $cart): array {
  $ids = array_values(array_filter(array_map('intval', array_keys($cart)), fn($id)=>$id>0));
  if (!$ids) return [];
  $marks = implode(',', array_fill(0,count($ids),'?'));
  $stmt = $pdo->prepare("SELECT id,name,price FROM products WHERE is_active=1 AND id IN ($marks)");
  $stmt->execute($ids); $rows = [];
  foreach ($stmt->fetchAll() as $p) {
    $qty = (int)($cart[$p['id']] ?? 0);
    if ($qty < 1 || $qty > 99) continue;
    $p['quantity'] = $qty; $p['line_total'] = (float)$p['price']*$qty; $rows[]=$p;
  }
  return $rows;
}
$items = get_cart_products($pdo, $_SESSION['cart']);
if (!$items) { $_SESSION['cart']=[]; header('Location: cart.php'); exit; }
$errors = []; $name = is_logged_in()?$_SESSION['user']['name']:''; $email = is_logged_in()?$_SESSION['user']['email']:''; $phone=''; $address='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $name = trim((string)($_POST['name'] ?? ''));
  $email = strtolower(trim((string)($_POST['email'] ?? '')));
  $phone = trim((string)($_POST['phone'] ?? ''));
  $address = trim((string)($_POST['address'] ?? ''));
  if ($name==='' || mb_strlen($name)>100) $errors[]='Enter your name (maximum 100 characters).';
  if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Enter a valid email address.';
  if ($phone==='' || mb_strlen($phone)>40) $errors[]='Enter a valid phone number.';
  if ($address==='' || mb_strlen($address)>2000) $errors[]='Enter your delivery address.';
  // Re-read prices from the database at checkout; never trust prices submitted by the browser.
  $items = get_cart_products($pdo, $_SESSION['cart']);
  if (!$items) $errors[]='Your cart is no longer available. Please review it.';
  if (!$errors) {
    $subtotal = 0.0;
    foreach ($items as $item) $subtotal += (float)$item['price'] * (int)$item['quantity'];
    $discount = is_logged_in() && !is_admin() ? round($subtotal * 0.10, 2) : 0.0;
    $total = $subtotal - $discount;
    try {
      $pdo->beginTransaction();
      $stmt = $pdo->prepare('INSERT INTO orders (user_id,customer_name,customer_email,customer_phone,customer_address,subtotal,discount_amount,total_amount) VALUES (?,?,?,?,?,?,?,?)');
      $uid = is_logged_in() ? (int)$_SESSION['user']['id'] : null;
      $stmt->execute([$uid,$name,$email,$phone,$address,$subtotal,$discount,$total]);
      $orderId = (int)$pdo->lastInsertId();
      $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id,product_id,product_name,unit_price,quantity,line_total) VALUES (?,?,?,?,?,?)');
      foreach ($items as $item) $itemStmt->execute([$orderId,(int)$item['id'],$item['name'],(float)$item['price'],(int)$item['quantity'],(float)$item['price']*(int)$item['quantity']]);
      $pdo->commit();
      $_SESSION['cart'] = [];
      $_SESSION['last_order_id'] = $orderId;
      header('Location: order_success.php'); exit;
    } catch (Throwable $e) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      $errors[]='Could not save the order. Please try again.';
    }
  }
}
$subtotal=0.0; foreach ($items as $item) $subtotal += (float)$item['price']*(int)$item['quantity'];
$discount=is_logged_in() && !is_admin()?round($subtotal*0.10,2):0.0; $total=$subtotal-$discount;
$page_title='Checkout'; require __DIR__ . '/includes/header.php';
?>
<h1 class="section-title fw-bold mb-4">Checkout</h1>
<?php foreach ($errors as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
<div class="row g-4"><div class="col-lg-7"><div class="form-card">
<h2 class="h5 mb-3">Your details</h2>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="mb-3"><label class="form-label" for="name">Full name</label><input class="form-control" id="name" name="name" maxlength="100" required value="<?= e($name) ?>"></div>
<div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" type="email" id="email" name="email" required value="<?= e($email) ?>"></div>
<div class="mb-3"><label class="form-label" for="phone">Phone number</label><input class="form-control" id="phone" name="phone" maxlength="40" required value="<?= e($phone) ?>"></div>
<div class="mb-3"><label class="form-label" for="address">Delivery address</label><textarea class="form-control" id="address" name="address" rows="3" maxlength="2000" required><?= e($address) ?></textarea></div>
<p class="small text-secondary">Demo project: this saves an order only; it does not process online payments.</p>
<button class="btn btn-coffee btn-lg w-100">Place order</button>
</form></div></div>
<div class="col-lg-5"><div class="card card-body"><h2 class="h5">Order summary</h2>
<?php foreach ($items as $item): ?><div class="d-flex justify-content-between gap-3 py-2"><span><?= e($item['name']) ?> × <?= (int)$item['quantity'] ?></span><strong><?= money((float)$item['line_total']) ?></strong></div><?php endforeach; ?>
<hr><div class="d-flex justify-content-between"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
<div class="d-flex justify-content-between mt-2"><span>Discount</span><span class="text-success">-<?= money($discount) ?></span></div>
<hr><div class="d-flex justify-content-between fs-5"><strong>Total</strong><strong><?= money($total) ?></strong></div>
<?php if (!$discount && !is_logged_in()): ?><p class="small mt-3 mb-0"><a href="login.php">Log in</a> to receive 10% off before placing your order.</p><?php endif; ?>
</div></div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
