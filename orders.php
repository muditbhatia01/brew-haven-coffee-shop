<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (!is_logged_in()) { header('Location: login.php'); exit; }
if (is_admin()) { header('Location: admin/orders.php'); exit; }
$stmt = $pdo->prepare('SELECT id,subtotal,discount_amount,total_amount,status,created_at FROM orders WHERE user_id=? ORDER BY id DESC');
$stmt->execute([$_SESSION['user']['id']]); $orders=$stmt->fetchAll();
$page_title='My orders'; require __DIR__ . '/includes/header.php';
?>
<h1 class="section-title fw-bold mb-4">My orders</h1>
<?php if (!$orders): ?><div class="alert alert-info">You have not placed any orders while logged in yet.</div><?php else: ?>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Order</th><th>Date</th><th>Subtotal</th><th>Discount</th><th>Total</th><th>Status</th></tr></thead><tbody>
<?php foreach($orders as $o): ?><tr><td>#<?= (int)$o['id'] ?></td><td><?= e($o['created_at']) ?></td><td><?= money((float)$o['subtotal']) ?></td><td><?= money((float)$o['discount_amount']) ?></td><td><?= money((float)$o['total_amount']) ?></td><td><?= e($o['status']) ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
