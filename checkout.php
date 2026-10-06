<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__.'/functions.php';

if(empty($_SESSION['customer_id'])){
  header('Location: customer_login.php?next=checkout.php');
  exit;
}

$cart = $_SESSION['cart'] ?? [];
$subtotal = 0;
foreach($cart as $item){
  if(!empty($item['is_reward'])){ continue; }
  $subtotal += $item['price'] * $item['qty'];
}
$customerName = $_SESSION['customer_name'] ?? '';
$customerPhone = $_SESSION['customer_phone'] ?? '';
$customerAddress = $_SESSION['customer_address'] ?? '';
$customerCity = $_SESSION['customer_city'] ?? '';
$customerProvince = $_SESSION['customer_province'] ?? '';
$customerPostal = $_SESSION['customer_postal_code'] ?? '';
$customerPoints = $_SESSION['customer_points_balance'] ?? 0;
$customerVouchers = $_SESSION['customer_voucher_count'] ?? 0;
$orderSubmissionToken = $_SESSION['_order_submission_token'] ?? '';
if($orderSubmissionToken === ''){
  $orderSubmissionToken = bin2hex(random_bytes(24));
  $_SESSION['_order_submission_token'] = $orderSubmissionToken;
}
$serviceFee = calculateServiceFee($subtotal);
$rewardOptions = getVoucherRewards();
$redeemMessage = null;
$error = null;
if($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'redeem'){
  if(!csrf_verify($_POST['csrf'] ?? '')){
    $error = 'Token CSRF tidak valid.';
  } elseif(empty($_POST['redeem_reward'])){
    $error = 'Pilih hadiah reward.';
  } else {
    try {
      $customer = getCustomerById($_SESSION['customer_id']);
      if(!$customer){ throw new RuntimeException('Pelanggan tidak ditemukan.'); }
      $updated = redeemCustomerVoucher($customer['id'], $_POST['redeem_reward']);
      $_SESSION['customer_voucher_count'] = $updated['voucher_count'];
      $customerVouchers = $updated['voucher_count'];
      $_SESSION['reward_claim_message'] = 'Berhasil menukar reward: ' . ($updated['reward_name'] ?? 'Reward');
      header('Location: checkout.php?reward_claimed=1');
      exit;
    } catch(Throwable $e){
      $error = $e->getMessage();
    }
  }
}
if(!empty($_SESSION['reward_claim_message'])){
  $redeemMessage = $_SESSION['reward_claim_message'];
  unset($_SESSION['reward_claim_message']);
}

if(!$cart){
  include 'header.php';
  echo '<div class="alert alert-info">Keranjang kosong. <a href="index.php">Belanja dulu</a>.</div>';
  include 'footer.php';
  exit;
}

include 'header.php';
?>
<style>
  .checkout-summary-table { width:100%; min-width:640px; table-layout:fixed; font-variant-numeric:tabular-nums; }
  .checkout-summary-table th,
  .checkout-summary-table td { vertical-align:middle; }
  .checkout-summary-table th:nth-child(4),
  .checkout-summary-table th:nth-child(5),
  .checkout-summary-table th:nth-child(6),
  .checkout-summary-table th:nth-child(3),
  .checkout-summary-table td:nth-child(3),
  .checkout-summary-table td:nth-child(4),
  .checkout-summary-table td:nth-child(5),
  .checkout-summary-table td:nth-child(6),
  .checkout-summary-table tfoot th:last-child { white-space:nowrap; }
  .checkout-summary-table tfoot th:first-child { white-space:nowrap; }
  .checkout-summary-table th:nth-child(1) { width:30%; }
  .checkout-summary-table th:nth-child(2) { width:13%; }
  .checkout-summary-table th:nth-child(3) { width:12%; }
  .checkout-summary-table th:nth-child(4) { width:17%; }
  .checkout-summary-table th:nth-child(5) { width:8%; }
  .checkout-summary-table th:nth-child(6) { width:20%; }
  .checkout-money-row { gap:.5rem; }
  .checkout-money-row strong { white-space:nowrap; font-variant-numeric:tabular-nums; }
  @media (max-width:380px) {
    .checkout-money-row { flex-wrap:wrap; }
    .checkout-money-row strong { margin-left:auto; }
  }
</style>
<div class="row g-3 align-items-start">
  <div class="col-lg-8">
    <div class="card p-3">
      <h4 class="mb-2">Ringkasan Pesanan</h4>
      <div class="table-responsive">
        <table class="table table-sm align-middle mb-2 checkout-summary-table">
          <thead><tr><th>Produk</th><th>Seller</th><th>Ukuran</th><th class="text-end">Harga</th><th class="text-center">Qty</th><th class="text-end">Subtotal</th></tr></thead>
          <tbody>
          <?php foreach($cart as $item): $isReward = !empty($item['is_reward']); $st = $isReward ? 0 : ($item['price'] * $item['qty']); ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <?php if($item['image']): ?><img src="<?php echo esc($item['image']); ?>" width="44" class="rounded" style="object-fit:cover; height:44px; flex-shrink:0;"><?php endif; ?>
                  <div class="small fw-semibold" style="word-break: break-word;">
                    <?php echo esc($item['name']); ?>
                    <?php if($isReward): ?><span class="badge bg-success ms-2">Reward klaim</span><?php endif; ?>
                  </div>
                </div>
              </td>
              <td class="small"><?php echo !empty($item['seller_name']) ? esc($item['seller_name']) : '-'; ?></td>
              <td class="small"><?php echo esc($item['size']); ?></td>
              <td class="text-end small"><?php echo formatRupiah($item['price']); ?></td>
              <td class="text-center small"><?php echo (int)$item['qty']; ?></td>
              <td class="text-end small"><?php echo $isReward ? 'Gratis' : formatRupiah($st); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="5" class="text-end">Subtotal</th>
              <th class="text-end"><?php echo formatRupiah($subtotal); ?></th>
            </tr>
            <tr>
              <th colspan="5" class="text-end">Biaya layanan (5%)</th>
              <th class="text-end"><?php echo formatRupiah($serviceFee); ?></th>
            </tr>
          </tfoot>
        </table>
      </div>
      <a href="cart.php" class="btn btn-primary btn-sm">Kembali ke Keranjang</a>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card p-3">
      <h4 class="mb-2">Alamat Pengiriman</h4>
      <form method="post" action="place_order.php" novalidate>
        <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>">
        <input type="hidden" name="order_token" value="<?php echo esc($orderSubmissionToken); ?>">
        <div class="mb-2">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" class="form-control" name="full_name" required value="<?php echo esc($customerName); ?>">
        </div>
        <div class="mb-2">
          <label class="form-label">No. HP / WA</label>
          <input type="tel" class="form-control" name="phone" required value="<?php echo esc($customerPhone); ?>" readonly>
          <div class="form-text">Nomor telepon digunakan sebagai identitas pelanggan.</div>
        </div>
        <div class="mb-2">
          <label class="form-label">Alamat Lengkap</label>
          <textarea class="form-control" name="address" rows="3" required><?php echo esc($customerAddress); ?></textarea>
        </div>
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">Kota / Kabupaten</label>
            <input type="text" class="form-control" name="city" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Provinsi</label>
            <input type="text" class="form-control" name="province" required>
          </div>
        </div>
        <div class="row g-2 mt-1">
          <div class="col-md-6">
            <label class="form-label">Kode Pos</label>
            <input type="text" class="form-control" name="postal_code" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Catatan (opsional)</label>
            <input type="text" class="form-control" name="notes">
          </div>
        </div>

        <div class="row g-2 mt-2">
          <div class="col-md-6">
            <label class="form-label">Kurir</label>
            <select class="form-select" name="courier" id="courier" required>
              <option value="JNE">JNE</option>
              <option value="SiCepat">SiCepat</option>
              <option value="AnterAja">AnterAja</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Layanan</label>
            <select class="form-select" name="service" id="service" required></select>
          </div>
        </div>
        <input type="hidden" name="shipping_cost" id="shipping_cost" value="0">

        <div class="border-top mt-2 pt-2">
          <div class="d-flex justify-content-between checkout-money-row"><span>Subtotal</span><strong id="subtotal_text"><?php echo formatRupiah($subtotal); ?></strong></div>
          <div class="d-flex justify-content-between checkout-money-row"><span>Biaya layanan (5%)</span><strong id="service_fee_text"><?php echo formatRupiah($serviceFee); ?></strong></div>
          <div class="d-flex justify-content-between checkout-money-row"><span>Ongkir</span><strong id="shipping_text">Rp 0</strong></div>
          <hr>
          <div class="d-flex justify-content-between checkout-money-row fs-5"><span>Total</span><strong id="total_text"><?php echo formatRupiah($subtotal + $serviceFee); ?></strong></div>
          <div class="mt-2 d-flex justify-content-end">
            <button class="btn btn-success btn-sm">Buat Pesanan</button>
          </div>
        </div>
      </form>
      <div class="small text-muted mt-2">* Ongkir dihitung sederhana (flat-rate).</div>
      <?php if(!empty($redeemMessage)): ?><div class="alert alert-success small mt-2 mb-0"><?php echo esc($redeemMessage); ?></div><?php endif; ?>
      <?php if(!empty($error)): ?><div class="alert alert-danger small mt-2 mb-0"><?php echo esc($error); ?></div><?php endif; ?>
      <div class="alert alert-success small mt-2 mb-0">
        Halo <?php echo esc($customerName ?: 'Pelanggan'); ?>, poin kamu saat ini <strong><?php echo (int)$customerPoints; ?></strong> dan voucher terkumpul <strong><?php echo (int)$customerVouchers; ?></strong>.
      </div>
      <div class="alert alert-info small mt-2 mb-0">
        Setiap transaksi lunas minimal Rp 10.000 mendapatkan 1 poin. Lima poin dapat ditukar menjadi voucher hadiah.
      </div>
      <div class="card p-3 mt-3">
        <h5 class="mb-3">Klaim Reward</h5>
        <?php if((int)$customerVouchers > 0 && $rewardOptions): ?>
          <form method="post" class="row g-2 align-items-end">
            <input type="hidden" name="action" value="redeem">
            <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>">
            <div class="col-md-8">
              <label class="form-label">Pilih Reward</label>
              <select name="redeem_reward" class="form-select" required>
                <option value="">-- Pilih reward --</option>
                <?php foreach($rewardOptions as $key => $reward): ?>
                  <option value="<?php echo esc($key); ?>" <?php echo (int)$reward['stock'] <= 0 ? 'disabled' : ''; ?>>
                    <?php echo esc($reward['name']); ?> (<?php echo (int)$reward['cost']; ?> voucher, sisa stock <?php echo (int)$reward['stock']; ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <button class="btn btn-success w-100">Klaim Sekarang</button>
            </div>
          </form>
          <div class="small mt-2">Voucher tersisa: <strong><?php echo (int)$customerVouchers; ?></strong></div>
        <?php else: ?>
          <div class="small">Kamu belum punya voucher untuk ditukar atau tidak ada stock reward tersedia.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const subtotal = <?php echo (int)$subtotal; ?>;
  const serviceFee = <?php echo (int)$serviceFee; ?>;
  const courierEl = document.getElementById('courier');
  const serviceEl = document.getElementById('service');
  const shipHidden = document.getElementById('shipping_cost');
  const shipText = document.getElementById('shipping_text');
  const totalText = document.getElementById('total_text');
  const provinceEl = document.querySelector('input[name="province"]');
  const RATES = {
    'JNE': {'REG':20000,'YES':35000},
    'SiCepat': {'REG':18000,'BEST':28000},
    'AnterAja': {'REG':17000,'NDS':27000}
  };
  function fillServices(){
    const c = courierEl.value;
    const services = RATES[c];
    serviceEl.innerHTML = '';
    Object.keys(services).forEach(s=>{
      const opt = document.createElement('option'); opt.value = s; opt.textContent = s; serviceEl.appendChild(opt);
    });
  }
  function calcShipping(){
    const c = courierEl.value, s = serviceEl.value;
    let cost = (RATES[c] && RATES[c][s])? RATES[c][s] : 0;
    const prov = (provinceEl && provinceEl.value || '').toLowerCase();
    if(prov.includes('jakarta') || prov.includes('dki')) cost = Math.max(10000, Math.round(cost*0.8));
    return cost;
  }
  function formatRupiah(n){ return 'Rp ' + (n||0).toLocaleString('id-ID'); }
  function updateTotals(){
    const ship = calcShipping();
    shipHidden.value = ship;
    shipText.textContent = formatRupiah(ship);
    totalText.textContent = formatRupiah(subtotal + serviceFee + ship);
  }
  courierEl.addEventListener('change', ()=>{ fillServices(); updateTotals(); });
  serviceEl.addEventListener('change', updateTotals);
  if(provinceEl){ provinceEl.addEventListener('input', updateTotals); }
  fillServices(); updateTotals();
})();
</script>
<?php include 'footer.php'; ?>
