<?php
require_once __DIR__.'/../functions.php';
if (session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__.'/auth.php';
$action = $_GET['action'] ?? '';
$error = null;

try{
  if($_SERVER['REQUEST_METHOD']==='POST'){
    if($action==='create'){
      addProduct($_POST, $_FILES);
      header('Location: products.php?msg=created'); exit;
    } elseif($action==='update' && isset($_GET['id'])){
      updateProduct((int)$_GET['id'], $_POST, $_FILES);
      header('Location: products.php?msg=updated'); exit;
    }
  }
  if($action==='delete' && isset($_GET['id'])){
    deleteProduct((int)$_GET['id']);
    header('Location: products.php?msg=deleted'); exit;
  }
}catch(Throwable $e){
  $error = $e->getMessage();
}
?>
<?php include '../header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Produk (Admin)</h3>
  <div class="d-flex gap-2">
    <a href="sellers.php" class="btn btn-outline-secondary"><i class="fa fa-store"></i> Kelola Seller</a>
    <a href="voucher_rewards.php" class="btn btn-outline-success"><i class="fa fa-gift"></i> Kelola Reward Voucher</a>
    <a href="community.php" class="btn btn-outline-success"><i class="fa fa-seedling"></i> Kelola Komunitas</a>
    <a href="orders.php" class="btn btn-outline-primary"><i class="fa fa-receipt"></i> Verifikasi Pesanan</a>
    <a href="products.php?action=new" class="btn btn-primary"><i class="fa fa-plus"></i> Produk Baru</a>
  </div>
</div>
<?php if($error): ?><div class="alert alert-danger"><?php echo esc($error); ?></div><?php endif; ?>
<?php if(isset($_GET['msg'])): ?><div class="alert alert-success">Sukses <?php echo esc($_GET['msg']); ?>.</div><?php endif; ?>

<?php
$sellers = getSellers();
if(($action==='new') || ($action==='edit' && isset($_GET['id']))):
  $prod = ['name'=>'','description'=>'','price'=>'','sizes'=>'All Size,S,M,L,XL','image_path'=>'','seller_id'=>null,'is_best_seller'=>0];
  if($action==='edit'){ $prod = getProduct((int)$_GET['id']); }
?>
<div class="card admin-card h-100 p-3 mb-4">
  <form method="post" enctype="multipart/form-data" action="products.php?action=<?php echo $action==='new'?'create':'update&id='.(int)($_GET['id']??0); ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Nama</label>
        <input class="form-control" name="name" required value="<?php echo esc($prod['name']); ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Harga (angka)</label>
        <input class="form-control" name="price" type="number" min="0" required value="<?php echo esc($prod['price']); ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Label</label>
        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" name="is_best_seller" value="1" <?php echo !empty($prod['is_best_seller'])?'checked':''; ?>>
          <label class="form-check-label">Best Seller</label>
        </div>
      </div>
      <div class="col-md-4">
        <label class="form-label">Seller</label>
        <select class="form-select" name="seller_id">
          <option value="">-- Tidak ada seller (Admin sendiri) --</option>
          <?php foreach($sellers as $seller): ?>
            <option value="<?php echo (int)$seller['id']; ?>" <?php echo (!empty($prod['seller_id']) && (int)$prod['seller_id'] === (int)$seller['id']) ? 'selected' : ''; ?>>
              <?php echo esc($seller['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="form-text">Bisa dikosongkan kalau admin ingin jualan sendiri.</div>
      </div>
      <div class="col-12">
        <label class="form-label">Ukuran Tersedia</label>
        <?php $allSizes = ['All Size','XS','S','M','L','XL','XXL']; $sel = array_map('trim', explode(',', $prod['sizes'])); ?>
        <div class="d-flex flex-wrap gap-2">
          <?php foreach($allSizes as $s): ?>
            <label class="form-check me-2">
              <input type="checkbox" class="form-check-input" name="sizes[]" value="<?php echo $s; ?>" <?php echo in_array($s,$sel)?'checked':''; ?>>
              <span class="form-check-label"><?php echo $s; ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-12">
        <label class="form-label">Deskripsi</label>
        <textarea class="form-control" rows="3" name="description"><?php echo esc($prod['description']); ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label">Foto Produk (JPG/PNG/WebP, max 2MB)</label>
        <input type="file" class="form-control" name="image" <?php echo $action==='new'?'required':''; ?>>
        <?php if($prod['image_path']): ?><img src="../<?php echo esc($prod['image_path']); ?>" class="mt-2 rounded" width="160"><?php endif; ?>
      </div>
      <div class="col-12">
        <button class="btn btn-primary"><?php echo $action==='new'?'Simpan':'Update'; ?></button>
        <a href="products.php" class="btn btn-primary">Batal</a>
      </div>
    </div>
  </form>
</div>
<?php else:
  $rows = getProducts(); ?>
  <div class="row row-cols-1 row-cols-lg-2 g-4 admin-products-row">
    <?php foreach($rows as $p): ?>
      <div class="col-12 col-md-6">
        <div class="card admin-card h-100 p-3 d-flex flex-row align-items-center">
          <?php if($p['image_path']): ?><div class="thumb-box"><img class="product-thumb" src="../<?php echo esc($p['image_path']); ?>" width="90" class="rounded me-3"><?php endif; ?>
          <div class="flex-grow-1">
            <div class="fw-bold"><?php echo esc($p['name']); ?> <span class="text-muted">— <?php echo formatRupiah($p['price']); ?></span></div>
            <div class="small text-muted">Seller: <?php echo esc(!empty($p['seller_name']) ? $p['seller_name'] : '-'); ?></div>
            <div class="small text-muted mb-2">Ukuran: <?php echo esc($p['sizes']); ?></div>
            <div>
              <a class="btn btn-sm btn-outline-primary" href="products.php?action=edit&id=<?php echo $p['id']; ?>"><i class="fa fa-pen"></i> Edit</a>
              <a class="btn btn-sm btn-outline-danger" href="products.php?action=delete&id=<?php echo $p['id']; ?>" onclick="return confirm('Hapus produk ini?')"><i class="fa fa-trash"></i> Hapus</a>
            </div>
          </div>
        </div>
      
      </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include '../footer.php'; ?>
