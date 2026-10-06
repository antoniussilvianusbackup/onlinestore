<?php
require_once __DIR__.'/functions.php';
if (session_status()===PHP_SESSION_NONE) session_start();

$error = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $seller = getSellerByUsername($username);
  $canLogin = false;

  if($seller && !empty($seller['is_active'])){
    $storedHash = $seller['password_hash'] ?? '';
    if($storedHash && password_verify($password, $storedHash)){
      $canLogin = true;
    } elseif(($seller['username'] ?? '') === 'sellerdemo' && $password === 'seller123'){
      $canLogin = true;
      $pdo = getPDO();
      $pdo->prepare("UPDATE sellers SET password_hash = ? WHERE id = ?")
        ->execute([password_hash($password, PASSWORD_DEFAULT), (int)$seller['id']]);
    }
  }

  if($canLogin){
    foreach(['is_admin','customer_id','customer_name','customer_phone','customer_address','customer_city','customer_province','customer_postal_code','customer_points_balance','customer_voucher_count'] as $key){ unset($_SESSION[$key]); }
    $_SESSION['seller_logged_in'] = true;
    $_SESSION['seller_id'] = (int)$seller['id'];
    $_SESSION['seller_name'] = $seller['name'];
    header('Location: seller_dashboard.php'); exit;
  }
  $error = 'Username atau password seller salah.';
}
include 'header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card p-4">
      <h3 class="mb-3">Login Seller</h3>
      <?php if($error): ?><div class="alert alert-danger"><?php echo esc($error); ?></div><?php endif; ?>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Username Seller</label>
          <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <a href="index.php" class="btn btn-primary">Kembali</a>
          <button class="btn btn-primary">Masuk</button>
        </div>
        <div class="small text-muted mt-2">Demo: <strong>sellerdemo</strong> / <strong>seller123</strong></div>
      </form>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
