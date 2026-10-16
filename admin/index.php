<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
$errors=[]; $editing=null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
  verify_csrf();
  $action=(string)($_POST['action']??'');
  if ($action==='save') {
    $id=filter_var($_POST['id']??0,FILTER_VALIDATE_INT) ?: 0;
    $name=trim((string)($_POST['name']??'')); $description=trim((string)($_POST['description']??''));
    $image=trim((string)($_POST['image_url']??'')); $price=filter_var($_POST['price']??null,FILTER_VALIDATE_FLOAT);
    if ($name==='' || mb_strlen($name)>120) $errors[]='Product name is required (maximum 120 characters).';
    if ($image!=='' && !filter_var($image,FILTER_VALIDATE_URL)) $errors[]='Image must be a valid URL, or leave it blank.';
    if ($price===false || $price<=0 || $price>99999999) $errors[]='Enter a price greater than zero.';
    if (!$errors) {
      if ($id>0) {
        $stmt=$pdo->prepare('UPDATE products SET name=?,description=?,image_url=?,price=? WHERE id=?');
        $stmt->execute([$name,$description,$image?:null,$price,$id]);
      } else {
        $stmt=$pdo->prepare('INSERT INTO products (name,description,image_url,price) VALUES (?,?,?,?)');
        $stmt->execute([$name,$description,$image?:null,$price]);
      }
      header('Location: index.php?saved=1'); exit;
    }
    $editing=['id'=>$id,'name'=>$name,'description'=>$description,'image_url'=>$image,'price'=>$price ?: ''];
  } elseif ($action==='delete') {
    $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
    if ($id) {
      // Soft-delete so existing order history stays intact.
      $stmt=$pdo->prepare('UPDATE products SET is_active=0 WHERE id=?'); $stmt->execute([$id]);
    }
    header('Location: index.php?deleted=1'); exit;
  } elseif ($action==='edit') {
    $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
    if ($id) { $stmt=$pdo->prepare('SELECT * FROM products WHERE id=?'); $stmt->execute([$id]); $editing=$stmt->fetch() ?: null; }
  }
}
if (!$editing && isset($_GET['edit'])) {
  $id=filter_var($_GET['edit'],FILTER_VALIDATE_INT);
  if ($id) { $stmt=$pdo->prepare('SELECT * FROM products WHERE id=?'); $stmt->execute([$id]); $editing=$stmt->fetch() ?: null; }
}
$products=$pdo->query('SELECT * FROM products ORDER BY is_active DESC,id DESC')->fetchAll();
$page_title='Admin products'; require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4"><div><h1 class="section-title fw-bold mb-1">Product management</h1><p class="text-secondary mb-0">Add, edit, deactivate, and update product images and prices.</p></div><a href="orders.php" class="btn btn-outline-dark">View orders</a></div>
<?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Product saved.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Product deactivated.</div><?php endif; ?>
<?php foreach($errors as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
<div class="card card-body mb-4"><h2 class="h5"><?= $editing?'Edit product':'Add a product' ?></h2>
<form method="post" class="row g-3"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($editing['id']??0) ?>">
<div class="col-md-6"><label class="form-label" for="name">Product name</label><input class="form-control" id="name" name="name" maxlength="120" required value="<?= e($editing['name']??'') ?>"></div>
<div class="col-md-6"><label class="form-label" for="price">Price (IDR)</label><input class="form-control" type="number" id="price" name="price" min="1" step="0.01" required value="<?= e(isset($editing['price'])?(string)$editing['price']:'') ?>"></div>
<div class="col-12"><label class="form-label" for="description">Description</label><textarea class="form-control" id="description" name="description" rows="2"><?= e($editing['description']??'') ?></textarea></div>
<div class="col-12"><label class="form-label" for="image_url">Image URL</label><input class="form-control" type="url" id="image_url" name="image_url" placeholder="https://example.com/coffee.jpg" value="<?= e($editing['image_url']??'') ?>"><div class="form-text">Use an image URL you have permission to use.</div></div>
<div class="col-12"><button class="btn btn-coffee"><?= $editing?'Update product':'Add product' ?></button> <?php if($editing): ?><a class="btn btn-outline-secondary" href="index.php">Cancel edit</a><?php endif; ?></div>
</form></div>
<div class="table-responsive"><table class="table bg-white align-middle"><thead><tr><th>Product</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($products as $p): ?><tr><td><div class="d-flex gap-3 align-items-center"><?php if($p['image_url']): ?><img class="product-thumb" src="<?= e($p['image_url']) ?>" alt=""><?php endif; ?><div><strong><?= e($p['name']) ?></strong><div class="small text-secondary"><?= e($p['description']) ?></div></div></div></td><td><?= money((float)$p['price']) ?></td><td><?= $p['is_active']?'Active':'Inactive' ?></td><td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="index.php?edit=<?= (int)$p['id'] ?>">Edit</a>
<?php if($p['is_active']): ?><form method="post" class="d-inline" data-confirm="Deactivate this product?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button class="btn btn-sm btn-outline-danger">Deactivate</button></form><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
