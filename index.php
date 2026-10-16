<?php
require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'Coffee Menu';
$stmt = $pdo->query('SELECT id,name,description,image_url,price FROM products WHERE is_active=1 ORDER BY id DESC');
$products = $stmt->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="hero mb-5">
  <span class="badge text-bg-light mb-3">Coffee made with care</span>
  <h1 class="display-4 fw-bold">Your daily cup of comfort.</h1>
  <p class="lead">Discover your favourite coffee, freshly prepared and ready to order.</p>
  <a href="#menu" class="btn btn-light btn-lg">Explore the menu</a>
  <?php if (is_logged_in() && !is_admin()): ?><p class="mt-3 mb-0 fw-semibold">Welcome back! Your 10% member discount applies at checkout.</p><?php endif; ?>
</section>
<div class="d-flex flex-wrap align-items-end justify-content-between mb-3" id="menu">
  <div><h2 class="section-title fw-bold mb-1">Our coffee menu</h2><p class="text-secondary mb-0">Choose something delicious.</p></div>
  <?php if (is_admin()): ?><a class="btn btn-coffee" href="admin/index.php">Manage products</a><?php endif; ?>
</div>
<?php if (!$products): ?><div class="alert alert-info">No products are available yet.</div><?php else: ?>
<div class="row g-4">
<?php foreach ($products as $p): ?>
  <div class="col-sm-6 col-lg-3">
    <article class="card product-card">
      <?php if ($p['image_url']): ?><img src="<?= e($p['image_url']) ?>" class="card-img-top" alt="<?= e($p['name']) ?>" loading="lazy"><?php else: ?><div class="product-placeholder">☕</div><?php endif; ?>
      <div class="card-body d-flex flex-column">
        <h3 class="h5"><?= e($p['name']) ?></h3>
        <p class="text-secondary small flex-grow-1"><?= e($p['description']) ?></p>
        <div class="price mb-3"><?= money((float)$p['price']) ?></div>
        <form method="post" action="cart.php">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
          <button class="btn btn-coffee w-100" type="submit">Add to cart</button>
        </form>
      </div>
    </article>
  </div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
