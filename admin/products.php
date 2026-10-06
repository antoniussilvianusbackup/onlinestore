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
<style>
  .product-admin-toolbar { display:flex; flex-wrap:wrap; align-items:center; gap:.55rem; }
  .product-admin-toolbar .btn { display:inline-flex; align-items:center; justify-content:center; gap:.45rem; min-height:42px; white-space:nowrap; }
  .product-admin-toolbar .product-admin-create { margin-left:.25rem; }
  .product-admin-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(min(100%,320px),1fr)); gap:1rem; }
  .product-admin-card { overflow:hidden; height:100%; }
  .product-admin-image { display:block; width:100%; aspect-ratio:4/3; object-fit:cover; background:#e8eee2; }
  .product-admin-placeholder { display:grid; place-items:center; aspect-ratio:4/3; background:#e8eee2; color:#5f6f5c; font-size:2rem; }
  .product-admin-meta { display:flex; flex-wrap:wrap; gap:.4rem; }
  .product-admin-actions { display:flex; flex-wrap:wrap; gap:.5rem; }
  .existing-image-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(110px,1fr)); gap:.65rem; }
  .existing-image-option { position:relative; cursor:pointer; border:1px solid var(--border); border-radius:8px; overflow:hidden; background:#fff; }
  .existing-image-option:has(input:checked) { outline:2px solid var(--accent); outline-offset:1px; }
  .existing-image-option img { display:block; width:100%; aspect-ratio:1; object-fit:cover; background:#e8eee2; }
  .existing-image-option .form-check-input { position:absolute; z-index:1; top:.4rem; left:.4rem; margin:0; }
  .existing-image-option span { display:block; overflow:hidden; padding:.35rem; font-size:.68rem; text-overflow:ellipsis; white-space:nowrap; }
  @media (max-width:575.98px) {
    .product-admin-toolbar { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); width:100%; }
    .product-admin-toolbar .btn { width:100%; white-space:normal; }
    .product-admin-toolbar .product-admin-create { grid-column:1 / -1; margin-left:0; }
  }
</style>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div><h1 class="mb-1">Produk</h1><div class="text-muted">Kelola katalog dan informasi produk.</div></div>
  <div class="product-admin-toolbar">
    <a href="sellers.php" class="btn btn-outline-success"><i class="fa fa-store"></i><span>Kelola Seller</span></a>
    <a href="voucher_rewards.php" class="btn btn-outline-success"><i class="fa fa-gift"></i><span>Reward Voucher</span></a>
    <a href="community.php" class="btn btn-outline-success"><i class="fa fa-seedling"></i><span>Komunitas</span></a>
    <a href="orders.php" class="btn btn-outline-success"><i class="fa fa-receipt"></i><span>Verifikasi Pesanan</span></a>
    <a href="products.php?action=new" class="btn btn-primary product-admin-create"><i class="fa fa-plus"></i><span>Produk Baru</span></a>
  </div>
</div>
<?php if($error): ?><div class="alert alert-danger"><?php echo esc($error); ?></div><?php endif; ?>
<?php if(isset($_GET['msg'])): ?><div class="alert alert-success">Sukses <?php echo esc($_GET['msg']); ?>.</div><?php endif; ?>

<?php
$sellers = getSellers();
$uploadedImages = getUploadedProductImages();
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
        <input type="file" class="form-control" name="image" <?php echo $action==='new' && !$uploadedImages?'required':''; ?>>
        <div class="form-text">Foto baru akan menggantikan foto pilihan di bawah.</div>
      </div>
      <?php if($uploadedImages): ?><div class="col-md-6">
        <label class="form-label">Pilih foto lama dari uploads</label>
        <div class="existing-image-grid">
          <?php foreach($uploadedImages as $imagePath): ?><label class="existing-image-option">
            <input class="form-check-input" type="radio" name="existing_image_path" value="<?php echo esc($imagePath); ?>" <?php echo !empty($prod['image_path']) && $prod['image_path']===$imagePath?'checked':''; ?>>
            <img src="../<?php echo esc($imagePath); ?>" alt="Foto <?php echo esc(basename($imagePath)); ?>" loading="lazy" onerror="this.hidden=true">
            <span title="<?php echo esc(basename($imagePath)); ?>"><?php echo esc(basename($imagePath)); ?></span>
          </label><?php endforeach; ?>
        </div>
        <div class="form-text">Foto baru di kolom sebelah akan menggantikan pilihan ini.</div>
        <?php if(!empty($prod['image_path'])): ?><img src="../<?php echo esc($prod['image_path']); ?>" alt="Foto <?php echo esc($prod['name']); ?>" class="mt-2 rounded" style="width:140px;height:110px;object-fit:cover" onerror="this.hidden=true"><?php endif; ?>
      </div>
      <?php endif; ?>
      <div class="col-12">
        <button class="btn btn-primary"><?php echo $action==='new'?'Simpan':'Update'; ?></button>
        <a href="products.php" class="btn btn-primary">Batal</a>
      </div>
    </div>
  </form>
</div>
<?php else:
  $rows = getProducts(); ?>
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
    <div class="small text-muted"><strong><?php echo count($rows); ?></strong> produk terdaftar</div>
    <?php if($rows): ?><div class="input-group" style="max-width:360px"><span class="input-group-text"><i class="fa fa-search"></i></span><input id="admin-product-search" type="search" class="form-control" placeholder="Cari nama atau seller" aria-label="Cari produk"></div><?php endif; ?>
  </div>
  <?php if(!$rows): ?><div class="border-top border-bottom py-5 text-center"><i class="fa fa-box-open fs-2 text-muted mb-3"></i><h2 class="h5">Belum ada produk</h2><p class="text-muted">Tambahkan produk pertama ke katalog.</p><a href="products.php?action=new" class="btn btn-primary"><i class="fa fa-plus me-1"></i>Produk Baru</a></div>
  <?php else: ?><div class="product-admin-grid" id="admin-product-grid">
    <?php foreach($rows as $p): ?>
      <article class="card product-admin-card" data-product-search="<?php echo esc(strtolower($p['name'].' '.($p['seller_name'] ?? ''))); ?>">
        <?php if(!empty($p['image_path'])): ?><img class="product-admin-image" src="../<?php echo esc($p['image_path']); ?>" alt="<?php echo esc($p['name']); ?>" loading="lazy" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
        <?php else: ?><div class="product-admin-placeholder" aria-label="Tidak ada foto produk"><i class="fa fa-image"></i></div><?php endif; ?>
        <?php if(!empty($p['image_path'])): ?><div class="product-admin-placeholder" aria-label="Foto produk tidak ditemukan" hidden><i class="fa fa-image"></i></div><?php endif; ?>
        <div class="card-body d-flex flex-column">
          <div class="d-flex justify-content-between align-items-start gap-3 mb-2"><h2 class="h5 mb-0"><?php echo esc($p['name']); ?></h2><strong class="text-nowrap"><?php echo formatRupiah($p['price']); ?></strong></div>
          <div class="small text-muted mb-2"><i class="fa fa-store me-1"></i><?php echo esc(!empty($p['seller_name']) ? $p['seller_name'] : 'Dikelola admin'); ?></div>
          <p class="small text-muted flex-grow-1 mb-3"><?php echo esc($p['description'] ?: 'Belum ada deskripsi.'); ?></p>
          <div class="product-admin-meta small text-muted mb-3"><span class="badge text-bg-light border">Ukuran: <?php echo esc($p['sizes'] ?: 'All Size'); ?></span><?php if(!empty($p['is_best_seller'])): ?><span class="badge text-bg-warning">Best Seller</span><?php endif; ?></div>
          <div class="product-admin-actions mt-auto">
            <a class="btn btn-sm btn-outline-primary" href="products.php?action=edit&id=<?php echo (int)$p['id']; ?>"><i class="fa fa-pen me-1"></i>Edit</a>
            <a class="btn btn-sm btn-outline-danger" href="products.php?action=delete&id=<?php echo (int)$p['id']; ?>" onclick="return confirm('Hapus produk ini?')"><i class="fa fa-trash me-1"></i>Hapus</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div><p id="admin-product-no-match" class="text-muted text-center py-4 d-none">Tidak ada produk yang cocok.</p><?php endif; ?>
<?php endif; ?>

<script>
(() => {
  const search = document.getElementById('admin-product-search');
  const cards = Array.from(document.querySelectorAll('[data-product-search]'));
  const noMatch = document.getElementById('admin-product-no-match');
  if (!search) return;
  search.addEventListener('input', () => {
    const query = search.value.trim().toLowerCase();
    let visible = 0;
    cards.forEach((card) => {
      const matches = (card.dataset.productSearch || '').includes(query);
      card.hidden = !matches;
      if (matches) visible += 1;
    });
    noMatch.classList.toggle('d-none', visible > 0);
  });
})();
</script>

<?php include '../footer.php'; ?>
