<?php
require_once __DIR__.'/functions.php';
if (session_status()===PHP_SESSION_NONE) session_start();
if(empty($_SESSION['customer_id'])){
  header('Location: customer_login.php?next=account.php'); exit;
}
$customer = getCustomerById($_SESSION['customer_id']);
if(!$customer){
  header('Location: customer_logout.php'); exit;
}
$rewardOptions = getVoucherRewards();
$redemptions = getCustomerVoucherRedemptions($customer['id'], 10);
$orders = getCustomerOrders($customer['id'], 20);
$redeemMessage = null;
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  if(($_POST['action'] ?? '') === 'redeem'){
    try{
      if(!csrf_verify($_POST['csrf'] ?? '')){
        throw new RuntimeException('Token CSRF tidak valid.');
      }
      $customer = redeemCustomerVoucher($customer['id'], $_POST['redeem_reward'] ?? '');
      $_SESSION['customer_voucher_count'] = $customer['voucher_count'];
      $redeemMessage = 'Berhasil menukar voucher untuk: ' . esc($customer['reward_name']);
      $redemptions = getCustomerVoucherRedemptions($customer['id'], 10);
    }catch(Throwable $e){
      $error = $e->getMessage();
    }
  } else {
    if(!csrf_verify($_POST['csrf'] ?? '')){
      $error = 'Token CSRF tidak valid.';
    } else {
      $customer = updateCustomerById($customer['id'], [
        'name' => $_POST['name'] ?? '',
        'address' => $_POST['address'] ?? '',
        'city' => $_POST['city'] ?? '',
        'province' => $_POST['province'] ?? '',
        'postal_code' => $_POST['postal_code'] ?? '',
      ]);
      $_SESSION['customer_name'] = $customer['name'];
      $_SESSION['customer_address'] = $customer['address'];
      $_SESSION['customer_city'] = $customer['city'];
      $_SESSION['customer_province'] = $customer['province'];
      $_SESSION['customer_postal_code'] = $customer['postal_code'];
      $updated = true;
    }
  }
}
include 'header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card p-4">
      <h3 class="mb-3">Akun Pelanggan</h3>
      <?php if(!empty($updated)): ?><div class="alert alert-success">Profil berhasil diperbarui.</div><?php endif; ?>
      <div class="mb-3">
        <div class="fw-semibold">Nama</div>
        <div><?php echo esc($customer['name']); ?></div>
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="card p-3 mb-3">
            <div class="fw-semibold">Saldo Poin</div>
            <div class="fs-2 text-success"><?php echo (int)$customer['points_balance']; ?></div>
            <div class="small text-muted">Poin saat ini</div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card p-3 mb-3">
            <div class="fw-semibold">Voucher Terkumpul</div>
            <div class="fs-2 text-success"><?php echo (int)$customer['voucher_count']; ?></div>
            <div class="small text-muted">Voucher hadiah yang sudah bisa kamu tukarkan</div>
          </div>
        </div>
      </div>
      <?php if(!empty($redeemMessage)): ?><div class="alert alert-success"><?php echo esc($redeemMessage); ?></div><?php endif; ?>
      <?php if(!empty($error)): ?><div class="alert alert-danger"><?php echo esc($error); ?></div><?php endif; ?>
      <div class="alert alert-info">
        <p class="mb-1"><strong>Aturan Poin:</strong></p>
        <p class="mb-0">Setiap transaksi lunas minimal Rp 10.000 memperoleh 1 poin. Lima poin otomatis menjadi 1 voucher hadiah.</p>
      </div>
      <div class="alert alert-success">
        <p class="mb-2"><strong>Tukar Voucher</strong></p>
        <?php if((int)$customer['voucher_count'] > 0): ?>
          <form method="post" class="row g-2 align-items-end">
            <input type="hidden" name="action" value="redeem">
            <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>">
            <div class="col-md-8">
              <label class="form-label">Pilih Hadiah</label>
              <select class="form-select" name="redeem_reward" required>
                <option value="">-- Pilih hadiah --</option>
                <?php foreach($rewardOptions as $key=>$reward): ?>
                  <option value="<?php echo esc($key); ?>"><?php echo esc($reward['name']); ?> (<?php echo esc($reward['cost']); ?> voucher)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <button class="btn btn-success w-100">Tukar Sekarang</button>
            </div>
          </form>
          <div class="small mt-2">Voucher tersisa: <strong><?php echo (int)$customer['voucher_count']; ?></strong></div>
        <?php else: ?>
          <div class="small">Kamu belum punya voucher untuk ditukar. Kumpulkan 5 poin dulu.</div>
        <?php endif; ?>
      </div>
      <?php if($redemptions): ?>
        <div class="card p-3 mb-3">
          <h5 class="mb-3">Riwayat Penukaran</h5>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead><tr><th>Hadiah</th><th>Biaya Voucher</th><th>Waktu</th></tr></thead>
              <tbody>
                <?php foreach($redemptions as $row): ?>
                  <tr>
                    <td><?php echo esc($row['reward_name']); ?></td>
                    <td><?php echo (int)$row['voucher_cost']; ?></td>
                    <td><?php echo esc($row['created_at']); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>
      <h5 class="mt-4">Ubah Profil</h5>
      <form method="post">
        <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>">
        <div class="mb-3">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" name="name" class="form-control" required value="<?php echo esc($customer['name']); ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">No. HP / WA</label>
          <input type="text" class="form-control" value="<?php echo esc($customer['phone']); ?>" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label">Alamat Lengkap</label>
          <textarea name="address" class="form-control" rows="3"><?php echo esc($customer['address']); ?></textarea>
        </div>
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">Kota / Kabupaten</label>
            <input type="text" name="city" class="form-control" value="<?php echo esc($customer['city']); ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Provinsi</label>
            <input type="text" name="province" class="form-control" value="<?php echo esc($customer['province']); ?>">
          </div>
        </div>
        <div class="row g-2 mt-2">
          <div class="col-md-6">
            <label class="form-label">Kode Pos</label>
            <input type="text" name="postal_code" class="form-control" value="<?php echo esc($customer['postal_code']); ?>">
          </div>
          <div class="col-md-6 d-flex align-items-end">
            <button class="btn btn-primary w-100">Simpan Perubahan</button>
          </div>
        </div>
      </form>
      <div class="mt-3">
        <a class="btn btn-outline-secondary" href="checkout.php">Kembali ke Checkout</a>
      </div>
    </div>
  </div>
</div>
<div class="row justify-content-center mt-4"><div class="col-md-8"><section class="card p-3"><h4>Riwayat Pesanan</h4><div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>No. order</th><th>Tanggal</th><th class="text-end">Total</th><th>Status</th><th>Poin</th></tr></thead><tbody>
<?php foreach($orders as $order): $statusLabels=['pending'=>'Menunggu pembayaran','paid'=>'Lunas','cancelled'=>'Dibatalkan']; ?><tr><td><?php echo esc($order['order_no']); ?></td><td><?php echo esc($order['created_at']); ?></td><td class="text-end"><?php echo formatRupiah($order['total']); ?></td><td><?php echo esc($statusLabels[$order['payment_status']] ?? $order['payment_status']); ?></td><td><?php echo (int)$order['points_earned']; ?></td></tr><?php endforeach; ?>
<?php if(!$orders): ?><tr><td colspan="5" class="text-muted text-center">Belum ada pesanan.</td></tr><?php endif; ?></tbody></table></div></section></div></div>
<?php include 'footer.php'; ?>
