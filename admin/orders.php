<?php
require_once __DIR__.'/../functions.php';
if(session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__.'/auth.php';
$error = null;
$notice = null;
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  try{
    if(!csrf_verify($_POST['csrf'] ?? '')) throw new RuntimeException('Token CSRF tidak valid.');
    $order = updateOrderPaymentStatus((int)($_POST['order_id'] ?? 0), $_POST['payment_status'] ?? '');
    $notice = $order['payment_status'] === 'paid'
      ? 'Pembayaran dikonfirmasi. Poin dan voucher pelanggan sudah diperbarui.'
      : 'Pesanan dibatalkan.';
  }catch(Throwable $e){ $error = $e->getMessage(); }
}
$orders = getAllOrders();
include __DIR__.'/../header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><div><h1>Verifikasi Pesanan</h1><p class="text-muted mb-0">Cocokkan pembayaran masuk sebelum menandai pesanan lunas.</p></div><a class="btn btn-outline-secondary" href="products.php"><i class="fa fa-arrow-left me-1"></i>Admin utama</a></div>
<?php if($error): ?><div class="alert alert-danger"><?php echo esc($error); ?></div><?php endif; ?>
<?php if($notice): ?><div class="alert alert-success"><?php echo esc($notice); ?></div><?php endif; ?>
<div class="alert alert-warning">Konfirmasi ini manual. Pastikan dana sudah diterima sebelum memilih “Tandai lunas”. Order lama dimigrasikan sebagai lunas agar histori poin sebelumnya tetap utuh.</div>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Pesanan</th><th>Pelanggan</th><th class="text-end">Subtotal</th><th class="text-end">Biaya</th><th class="text-end">Total</th><th>Status</th><th>Poin</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($orders as $order): ?><tr>
  <td><strong><?php echo esc($order['order_no']); ?></strong><div class="small text-muted"><?php echo esc($order['created_at']); ?></div></td>
  <td><?php echo esc($order['customer_name']); ?><div class="small text-muted"><?php echo esc($order['customer_phone']); ?></div></td>
  <td class="text-end"><?php echo formatRupiah($order['subtotal']); ?></td><td class="text-end"><?php echo formatRupiah($order['service_fee'] + $order['shipping_cost']); ?></td><td class="text-end fw-bold"><?php echo formatRupiah($order['total']); ?></td>
  <td><span class="badge <?php echo $order['payment_status']==='paid'?'text-bg-success':($order['payment_status']==='cancelled'?'text-bg-secondary':'text-bg-warning'); ?>"><?php echo esc($order['payment_status']); ?></span></td>
  <td><?php echo (int)$order['points_earned']; ?> poin</td><td>
    <?php if($order['payment_status']==='pending'): ?><form method="post" class="d-flex flex-wrap gap-1"><input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>"><input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>"><button class="btn btn-sm btn-success" name="payment_status" value="paid">Tandai lunas</button><button class="btn btn-sm btn-outline-danger" name="payment_status" value="cancelled" onclick="return confirm('Batalkan pesanan ini?')">Batalkan</button></form><?php else: ?><span class="small text-muted">Sudah diproses</span><?php endif; ?>
  </td></tr><?php endforeach; ?>
<?php if(!$orders): ?><tr><td colspan="8" class="text-center text-muted">Belum ada pesanan.</td></tr><?php endif; ?>
</tbody></table></div>
<?php include __DIR__.'/../footer.php'; ?>