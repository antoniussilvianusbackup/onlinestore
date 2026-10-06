<?php
require_once __DIR__.'/../functions.php';
if (session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__.'/auth.php';

$pdo = getPDO();
$error = null;
$success = null;

try {
  $rows = getAllVoucherRedemptions(50);
} catch(Throwable $e) {
  $error = $e->getMessage();
}

include '../header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h3>Riwayat Penukaran Voucher</h3>
    <p class="small text-muted">Lihat kegiatan penukaran voucher hadiah oleh pelanggan.</p>
  </div>
  <a href="products.php" class="btn btn-primary"><i class="fa fa-arrow-left"></i> Kembali ke Produk</a>
</div>
<?php if($error): ?><div class="alert alert-danger"><?php echo esc($error); ?></div><?php endif; ?>
<div class="card p-3">
  <div class="table-responsive">
    <table class="table align-middle table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Pelanggan</th>
          <th>Hadiah</th>
          <th>Biaya Voucher</th>
          <th>Waktu</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($rows)): ?>
          <tr><td colspan="5" class="text-center text-muted">Belum ada penukaran voucher.</td></tr>
        <?php endif; ?>
        <?php foreach($rows as $row): ?>
          <tr>
            <td><?php echo (int)$row['id']; ?></td>
            <td><?php echo esc($row['customer_name']); ?> (<?php echo esc($row['customer_phone']); ?>)</td>
            <td><?php echo esc($row['reward_name']); ?></td>
            <td><?php echo (int)$row['voucher_cost']; ?></td>
            <td><?php echo esc($row['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../footer.php'; ?>