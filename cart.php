<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) $_SESSION['cart'] = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $action = (string)($_POST['action'] ?? '');
  $id = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
  if ($action === 'add' && $id) {
    $stmt = $pdo->prepare('SELECT id FROM products WHERE id=? AND is_active=1');
    $stmt->execute([$id]);
    if ($stmt->fetch()) $_SESSION['cart'][$id] = min(99, (int)($_SESSION['cart'][$id] ?? 0) + 1);
  } elseif ($action === 'update' && $id) {
    $qty = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT, ['options'=>['min_range'=>0,'max_range'=>99]]);
    if ($qty === false || $qty === 0) unset($_SESSION['cart'][$id]);
    else $_SESSION['cart'][$id] = $qty;
  } elseif ($action === 'remove' && $id) unset($_SESSION['cart'][$id]);
  elseif ($action === 'clear') $_SESSION['cart'] = [];
  header('Location: cart.php'); exit;
}
$items = []; $subtotal = 0.0;
if ($_SESSION['cart']) {
  $ids = array_values(array_filter(array_map('intval', array_keys($_SESSION['cart'])), fn($id)=>$id>0));
  if ($ids) {
    $marks = implode(',', array_fill(0,count($ids),'?'));
    $stmt = $pdo->prepare("SELECT id,name,image_url,price FROM products WHERE is_active=1 AND id IN ($marks)");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $p) {
      $qty = (int)($_SESSION['cart'][$p['id']] ?? 0);
      if ($qty < 1) continue;
      $p['quantity'] = $qty; $p['line_total'] = (float)$p['price']*$qty;
      $subtotal += $p['line_total']; $items[] = $p;
    }
  }
}
$discount = is_logged_in() && !is_admin() ? round($subtotal * 0.10, 2) : 0.0;
$total = $subtotal - $discount;
$page_title = 'Your cart'; require __DIR__ . '/includes/header.php';
?>
<h1 class="section-title fw-bold mb-4">Your shopping cart</h1>
<?php if (!$items): ?><div class="alert alert-info">Your cart is empty. <a href="index.php">Browse the menu</a>.</div><?php else: ?>
<div class="table-responsive"><table class="table align-middle bg-white"><thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Line total</th><th></th></tr></thead><tbody>
<?php foreach ($items as $item): ?><tr>
<td><div class="d-flex align-items-center gap-3"><?php if ($item['image_url']): ?><img class="product-thumb" src="<?= e($item['image_url']) ?>" alt=""><?php endif; ?><strong><?= e($item['name']) ?></strong></div></td>
<td><?= money((float)$item['price']) ?></td>
<td><form class="d-flex gap-2" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="update"><input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>"><input class="form-control" style="max-width:90px" type="number" min="0" max="99" name="quantity" value="<?= (int)$item['quantity'] ?>"><button class="btn btn-outline-secondary btn-sm">Update</button></form></td>
<td><?= money((float)$item['line_total']) ?></td>
<td><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="remove"><input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>"><button class="btn btn-outline-danger btn-sm">Remove</button></form></td>
</tr><?php endforeach; ?>
</tbody></table></div>
<div class="row justify-content-end"><div class="col-md-5 col-lg-4"><div class="card card-body">
<div class="d-flex justify-content-between"><span>Subtotal</span><strong><?= money($subtotal) ?></strong></div>
<div class="d-flex justify-content-between mt-2"><span>Member discount (10%)</span><strong class="<?= $discount ? 'text-success':'' ?>"><?= $discount ? '-'.money($discount) : money(0) ?></strong></div>
<?php if (!is_logged_in()): ?><p class="small text-secondary mt-2 mb-0"><a href="login.php">Log in</a> for 10% off, or continue as a guest.</p><?php elseif (is_admin()): ?><p class="small text-secondary mt-2 mb-0">Admin accounts do not receive the customer discount.</p><?php endif; ?>
<hr><div class="d-flex justify-content-between fs-5"><strong>Total</strong><strong><?= money($total) ?></strong></div>
<a class="btn btn-coffee mt-3" href="checkout.php">Continue to checkout</a>
<form method="post" class="mt-2"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="clear"><button class="btn btn-outline-secondary w-100">Clear cart</button></form>
</div></div></div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
