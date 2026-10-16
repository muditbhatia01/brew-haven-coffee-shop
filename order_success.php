<?php
require_once __DIR__ . '/includes/bootstrap.php';
$orderId = (int)($_SESSION['last_order_id'] ?? 0);
unset($_SESSION['last_order_id']);
$page_title='Order placed'; require __DIR__ . '/includes/header.php';
?>
<div class="form-card text-center">
<div class="display-3 mb-3">☕</div><h1 class="section-title fw-bold">Thank you for your order!</h1>
<p>Your order has been saved successfully.</p>
<?php if ($orderId): ?><p class="fs-5">Order number: <strong>#<?= $orderId ?></strong></p><?php endif; ?>
<p class="text-secondary">This demo does not process payments. The shop can review your order in the admin dashboard.</p>
<a class="btn btn-coffee" href="index.php">Back to menu</a>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
