<?php
require_once __DIR__.'/functions.php';
if (session_status()===PHP_SESSION_NONE) session_start();
if(empty($_SESSION['seller_logged_in']) || empty($_SESSION['seller_id'])){
  header('Location: seller_login.php'); exit;
}

$sellerId = (int)$_SESSION['seller_id'];
$seller = getSeller($sellerId);
$error = '';
$success = '';
$editingProduct = null;

if($_SERVER['REQUEST_METHOD']==='POST'){
  if(isset($_POST['action']) && $_POST['action']==='create'){
    try{
      addProduct($_POST, $_FILES);
      $success = 'Produk berhasil ditambahkan.';
    } catch(Throwable $e){
      $error = $e->getMessage();
    }
  } elseif(isset($_POST['action']) && $_POST['action']==='update'){
    try{
      $productId = (int)($_POST['product_id'] ?? 0);
      if($productId <= 0){ throw new RuntimeException('Produk tidak valid.'); }
      updateProduct($productId, $_POST, $_FILES);
      $success = 'Produk berhasil diperbarui.';
    } catch(Throwable $e){
      $error = $e->getMessage();
    }
  } elseif(isset($_POST['action']) && $_POST['action']==='delete'){
    try{
      $productId = (int)($_POST['product_id'] ?? 0);
      if($productId <= 0){ throw new RuntimeException('Produk tidak valid.'); }
      $product = getProduct($productId);
      if(!$product || (int)$product['seller_id'] !== $sellerId){ throw new RuntimeException('Anda tidak berhak menghapus produk ini.'); }
      $pdo = getPDO();
      $pdo->prepare("DELETE FROM products WHERE id=? AND seller_id=?")->execute([$productId, $sellerId]);
      $success = 'Produk berhasil dihapus.';
    } catch(Throwable $e){
      $error = $e->getMessage();
    }
  }
}

if(isset($_GET['edit']) && (int)$_GET['edit'] > 0){
  $editingProduct = getProduct((int)$_GET['edit']);
  if(!$editingProduct || (int)$editingProduct['seller_id'] !== $sellerId){
    $editingProduct = null;
  }
}

$products = getProducts($sellerId);
include 'header.php';
?>
<div class="container py-4">
  <div class="card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h3 class="mb-1">Dashboard Seller</h3>
        <p class="text-muted mb-0">Halo, <?php echo esc($seller['name'] ?? 'Seller'); ?></p>
      </div>
      <a href="seller_login.php" class="btn btn-outline-secondary">Logout</a>
    </div>
  </div>

  <div class="card p-4 mb-4">
    <h5 class="mb-3"><?php echo $editingProduct ? 'Edit Produk' : 'Tambah Produk Baru'; ?></h5>
    <?php if(!empty($success)): ?><div class="alert alert-success"><?php echo esc($success); ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-danger"><?php echo esc($error); ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="<?php echo $editingProduct ? 'update' : 'create'; ?>">
      <?php if($editingProduct): ?><input type="hidden" name="product_id" value="<?php echo (int)$editingProduct['id']; ?>"><?php endif; ?>
      <input type="hidden" name="seller_id" value="<?php echo (int)$sellerId; ?>">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nama Produk</label>
          <input class="form-control" name="name" required value="<?php echo esc($editingProduct['name'] ?? ''); ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Harga (Rp)</label>
          <input class="form-control rupiah-input" type="text" inputmode="numeric" autocomplete="off" maxlength="13" name="price" required value="<?php echo esc(number_format((int)($editingProduct['price'] ?? 0), 0, ',', '.')); ?>" placeholder="Contoh: 1.000.000">
          <div class="form-text">Pemisah ribuan ditambahkan otomatis.</div>
        </div>
        <div class="col-md-3">
          <label class="form-label">Foto</label>
          <input class="form-control" type="file" name="image">
        </div>
        <div class="col-12">
          <label class="form-label">Deskripsi</label>
          <textarea class="form-control" name="description" rows="3"><?php echo esc($editingProduct['description'] ?? ''); ?></textarea>
        </div>
        <div class="col-12">
          <label class="form-label">Ukuran Tersedia</label>
          <div class="d-flex flex-wrap gap-2">
            <?php $selectedSizes = []; if($editingProduct){ $selectedSizes = array_map('trim', explode(',', $editingProduct['sizes'] ?? '')); } foreach(['All Size','XS','S','M','L','XL','XXL'] as $size): $checked = in_array($size, $selectedSizes, true) ? 'checked' : ''; ?>
              <label class="form-check">
                <input class="form-check-input" type="checkbox" name="sizes[]" value="<?php echo esc($size); ?>" <?php echo $checked; ?>>
                <span class="form-check-label"><?php echo esc($size); ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="col-12">
          <button class="btn btn-primary"><?php echo $editingProduct ? 'Update Produk' : 'Simpan Produk'; ?></button>
          <?php if($editingProduct): ?><a href="seller_dashboard.php" class="btn btn-outline-secondary">Batal</a><?php endif; ?>
        </div>
      </div>
    </form>
  </div>

  <h5 class="mb-3">Produk Saya</h5>
  <?php if(!$products): ?>
    <div class="alert alert-info">Belum ada produk yang Anda tambahkan.</div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach($products as $p): ?>
        <div class="col-md-4 col-lg-3">
          <div class="card p-2 h-100">
            <?php if(!empty($p['image_path'])): ?>
              <img src="<?php echo esc($p['image_path']); ?>" alt="<?php echo esc($p['name']); ?>" class="img-fluid rounded-3 mb-2" style="height:120px; object-fit:cover; width:100%;">
            <?php else: ?>
              <div class="d-flex align-items-center justify-content-center rounded-3 mb-2 bg-light" style="height:120px;">
                <span class="text-muted small">Belum ada foto</span>
              </div>
            <?php endif; ?>
            <div class="fw-bold small"><?php echo esc($p['name']); ?></div>
            <div class="small text-muted mb-1"><?php echo esc($p['sizes']); ?></div>
            <div class="text-muted small" style="display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;"><?php echo esc($p['description']); ?></div>
            <div class="fw-semibold mt-2 small"><?php echo formatRupiah($p['price']); ?></div>
            <div class="d-flex gap-2 mt-2">
              <a class="btn btn-sm btn-outline-primary py-1 px-2" href="seller_dashboard.php?edit=<?php echo (int)$p['id']; ?>">Edit</a>
              <form method="post" onsubmit="return confirm('Hapus produk ini?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                <button class="btn btn-sm btn-outline-danger py-1 px-2">Hapus</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
