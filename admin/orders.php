<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
  verify_csrf();
  $id=filter_var($_POST['order_id']??null,FILTER_VALIDATE_INT);
  $status=(string)($_POST['status']??'');
  $allowed=['Pending','Preparing','Ready','Completed','Cancelled'];
  if ($id && in_array($status,$allowed,true)) {
    $stmt=$pdo->prepare('UPDATE orders SET status=? WHERE id=?'); $stmt->execute([$status,$id]);
    header('Location: orders.php?updated=1'); exit;
  }
  $errors[]='Invalid order or status.';
}
$orders=$pdo->query('SELECT * FROM orders ORDER BY created_at DESC')->fetchAll();
$page_title='Admin orders'; require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4"><h1 class="section-title fw-bold mb-0">Customer orders</h1><a class="btn btn-outline-dark" href="index.php">Manage products</a></div>
<?php if(isset($_GET['updated'])): ?><div class="alert alert-success">Order status updated.</div><?php endif; ?>
<?php foreach($errors as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
<?php if(!$orders): ?><div class="alert alert-info">No orders yet.</div><?php else: ?>
<?php foreach($orders as $o): ?>
<div class="card card-body mb-3"><div class="d-flex flex-wrap justify-content-between gap-2"><div><h2 class="h5">Order #<?= (int)$o['id'] ?> <span class="badge text-bg-secondary"><?= e($o['status']) ?></span></h2><div class="text-secondary small"><?= e($o['created_at']) ?> · <?= $o['user_id']===null?'Guest checkout':'Registered customer' ?></div></div><strong class="fs-5"><?= money((float)$o['total_amount']) ?></strong></div>
<div class="row mt-2"><div class="col-md-6"><strong><?= e($o['customer_name']) ?></strong><br><?= e($o['customer_email']) ?><br><?= e($o['customer_phone']) ?><br><?= nl2br(e($o['customer_address'])) ?></div><div class="col-md-6"><div>Subtotal: <?= money((float)$o['subtotal']) ?></div><div>Discount: <?= money((float)$o['discount_amount']) ?></div>
<?php $stmt=$pdo->prepare('SELECT product_name,quantity,unit_price,line_total FROM order_items WHERE order_id=?'); $stmt->execute([$o['id']]); ?>
<ul class="mt-2"><?php foreach($stmt->fetchAll() as $item): ?><li><?= e($item['product_name']) ?> × <?= (int)$item['quantity'] ?> — <?= money((float)$item['line_total']) ?></li><?php endforeach; ?></ul>
<form method="post" class="d-flex gap-2 mt-2"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>"><select class="form-select" name="status"><?php foreach(['Pending','Preparing','Ready','Completed','Cancelled'] as $s): ?><option <?= $o['status']===$s?'selected':'' ?> value="<?= e($s) ?>"><?= e($s) ?></option><?php endforeach; ?></select><button class="btn btn-coffee">Update</button></form>
</div></div></div>
<?php endforeach; endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
