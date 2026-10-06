<?php
require_once __DIR__.'/functions.php';
if (session_status()===PHP_SESSION_NONE) session_start();

$error = '';
$next = trim($_GET['next'] ?? ($_POST['next'] ?? 'checkout.php'));
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $phone = trim($_POST['phone'] ?? '');
  if($phone === ''){
    $error = 'Nomor WA/HP wajib diisi.';
  } else {
    $customer = getCustomerByPhone($phone);
    if($customer){
      unset($_SESSION['is_admin'], $_SESSION['seller_logged_in'], $_SESSION['seller_id'], $_SESSION['seller_name']);
      $_SESSION['customer_id'] = (int)$customer['id'];
      $_SESSION['customer_name'] = $customer['name'];
      $_SESSION['customer_phone'] = $customer['phone'];
      $_SESSION['customer_address'] = $customer['address'];
      $_SESSION['customer_city'] = $customer['city'];
      $_SESSION['customer_province'] = $customer['province'];
      $_SESSION['customer_postal_code'] = $customer['postal_code'];
      $_SESSION['customer_points_balance'] = (int)$customer['points_balance'];
      $_SESSION['customer_voucher_count'] = (int)$customer['voucher_count'];
      header('Location: '.($next ?: 'checkout.php')); exit;
    }
    $error = 'Nomor belum terdaftar. Silakan daftar terlebih dahulu.';
  }
}
include 'header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card p-4">
      <h3 class="mb-3">Login Pelanggan</h3>
      <?php if($error): ?><div class="alert alert-danger"><?php echo esc($error); ?></div><?php endif; ?>
      <div class="alert alert-info">Masukkan nomor WA/HP untuk masuk. Jika belum punya akun, daftar dulu.</div>
      <form method="post">
        <input type="hidden" name="next" value="<?php echo esc($next); ?>">
        <div class="mb-3">
          <label class="form-label">No. HP / WA</label>
          <input type="tel" name="phone" class="form-control" required autofocus>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <a href="index.php" class="btn btn-outline-secondary">Kembali</a>
          <button class="btn btn-primary">Masuk</button>
        </div>
      </form>
      <div class="mt-3 small text-muted">
        Belum punya akun? <a href="customer_register.php?next=<?php echo urlencode($next); ?>">Daftar di sini</a>.
      </div>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
