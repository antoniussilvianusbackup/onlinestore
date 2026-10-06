<?php
if(session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__.'/functions.php';
if(empty($_SESSION['customer_id'])){
  header('Location: customer_login.php?next=checkout.php'); exit;
}
if($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf'] ?? '')){
  http_response_code(400);
  exit('Permintaan tidak valid. Silakan kembali ke checkout dan coba lagi.');
}
$submittedOrderToken = $_POST['order_token'] ?? '';
if(empty($_SESSION['_order_submission_token']) || $submittedOrderToken === '' || !hash_equals($_SESSION['_order_submission_token'], $submittedOrderToken)){
  http_response_code(409);
  exit('Pesanan ini sudah diproses atau form checkout sudah kedaluwarsa. Buka checkout kembali untuk membuat pesanan baru.');
}
$customer = getCustomerById((int)$_SESSION['customer_id']);
if(!$customer){
  header('Location: customer_logout.php'); exit;
}
$cart = $_SESSION['cart'] ?? [];
if(!$cart){
  include 'header.php';
  echo '<div class="alert alert-info">Keranjang kosong. <a href="index.php">Belanja dulu</a>.</div>';
  include 'footer.php'; exit;
}
include 'header.php';

$required = ['full_name','phone','address','city','province','postal_code'];
$missing = [];
foreach($required as $r){ if(empty($_POST[$r])) $missing[] = $r; }

if($missing){
  echo '<div class="alert alert-danger">Mohon lengkapi data pengiriman.</div>';
  echo '<a class="btn btn-primary" href="checkout.php">Kembali ke Checkout</a>';
  include 'footer.php'; exit;
}

$subtotal = 0;
foreach($cart as $item){
  if(!empty($item['is_reward'])){ continue; }
  $subtotal += $item['price'] * $item['qty'];
}

$courier = $_POST['courier'] ?? 'JNE';
$service = $_POST['service'] ?? 'REG';
$province = $_POST['province'] ?? null;
$shipping = calcShipping($courier, $service, $province);
$serviceFee = calculateServiceFee($subtotal);
$total = $subtotal + $serviceFee + $shipping;

$orderNo = 'INV'.date('YmdHis').rand(100,999);
$pointsPending = calculatePointsFromSubtotal($subtotal);
$customerData = [
  'name' => $_POST['full_name'],
  'phone' => $_SESSION['customer_phone'] ?? $_POST['phone'],
  'address' => $_POST['address'],
  'city' => $_POST['city'],
  'province' => $_POST['province'],
  'postal_code' => $_POST['postal_code'],
];
$customerId = (int)($_SESSION['customer_id'] ?? 0);
$customerInfo = $customer;
$customerInfo = updateCustomerById($customerId, $customerData);
$_SESSION['customer_name'] = $customerInfo['name'];
$_SESSION['customer_phone'] = $customerInfo['phone'];
$_SESSION['customer_address'] = $customerInfo['address'];
$_SESSION['customer_city'] = $customerInfo['city'];
$_SESSION['customer_province'] = $customerInfo['province'];
$_SESSION['customer_postal_code'] = $customerInfo['postal_code'];
$_SESSION['customer_points_balance'] = (int)$customerInfo['points_balance'];
$_SESSION['customer_voucher_count'] = (int)$customerInfo['voucher_count'];
$pointsEarned = 0;

$lastOrder = [
  'order_no' => $orderNo,
  'items' => array_values($cart),
  'subtotal' => $subtotal,
  'service_fee' => $serviceFee,
  'courier' => $courier,
  'service' => $service,
  'shipping' => $shipping,
  'total' => $total,
  'points_earned' => $pointsEarned,
  'points_pending' => $pointsPending,
  'payment_status' => 'pending',
  'voucher_awarded' => 0,
  'points_balance' => $customerInfo['points_balance'] ?? 0,
  'voucher_count' => $customerInfo['voucher_count'] ?? 0,
  'customer' => [
    'full_name' => $_POST['full_name'],
    'phone' => $_POST['phone'],
    'address' => $_POST['address'],
    'city' => $_POST['city'],
    'province' => $_POST['province'],
    'postal_code' => $_POST['postal_code'],
    'notes' => $_POST['notes'] ?? ''
  ],
  'created_at' => date('Y-m-d H:i:s')
];
$_SESSION['last_order'] = $lastOrder;

createOrder([
  'order_no' => $orderNo,
  'customer_id' => $_SESSION['customer_id'] ?? ($customerInfo['id'] ?? null),
  'customer_name' => $_POST['full_name'],
  'customer_phone' => $_SESSION['customer_phone'] ?? $_POST['phone'],
  'customer_address' => $_POST['address'],
  'customer_city' => $_POST['city'],
  'customer_province' => $_POST['province'],
  'customer_postal_code' => $_POST['postal_code'],
  'customer_notes' => $_POST['notes'] ?? '',
  'courier' => $courier,
  'service' => $service,
  'subtotal' => $subtotal,
  'service_fee' => $serviceFee,
  'shipping_cost' => $shipping,
  'total' => $total,
  'payment_status' => 'pending',
  'points_earned' => $pointsEarned,
  'voucher_awarded' => $customerInfo['voucher_awarded'] ?? 0,
], array_values($cart));
unset($_SESSION['_order_submission_token']);

// ===== WhatsApp message =====
require_once __DIR__.'/config.php';
$phoneAdmin = defined('WHATSAPP_PHONE') ? WHATSAPP_PHONE : '+6287874872257';

$lines = [];
$lines[] = '*Pesanan Baru dari Terra Kala*';
$lines[] = 'Invoice: #' . $orderNo;
$lines[] = 'Nama: ' . $_POST['full_name'];
$lines[] = 'No. WhatsApp: ' . $_POST['phone'];
$lines[] = 'Alamat: ' . trim($_POST['address'].' '.$_POST['city'].' '.$_POST['province'].' '.$_POST['postal_code']);
$lines[] = '';
$lines[] = '*Rincian Pesanan:*';
foreach($cart as $it){
  if(!empty($it['is_reward'])){ continue; }
  $sellerText = !empty($it['seller_name']) ? ' [Seller: ' . $it['seller_name'] . ']' : '';
  $lines[] = '- ' . ((int)$it['qty']) . 'x ' . $it['name'] . ' (Ukuran: ' . $it['size'] . ')' . $sellerText;
}
$lines[] = '';
$lines[] = 'Subtotal: ' . formatRupiah($subtotal);
$lines[] = 'Biaya layanan (5%): ' . formatRupiah($serviceFee);
$lines[] = 'Ongkir: ' . formatRupiah($shipping) . ' (' . $courier . ' ' . $service . ')';
$lines[] = 'Total: ' . formatRupiah($total);
$lines[] = 'Status pembayaran: Menunggu konfirmasi';
$lines[] = '';
$lines[] = '*Silakan konfirmasi pesanan ini segera.*';

$waText = implode("\n", $lines);
$waLink = 'https://api.whatsapp.com/send/?phone='.$phoneAdmin.'&text='.rawurlencode($waText).'&type=phone_number&app_absent=0';

?>
<div class="card p-4">
  <h3 class="mb-3">Pesanan dibuat, menunggu pembayaran.</h3>
  <div class="mb-2">No. Pesanan: <strong><?php echo esc($orderNo); ?></strong></div>
  <div class="row g-4 mt-1">
    <div class="col-md-6">
      <h5>Alamat Pengiriman</h5>
      <div><?php echo esc($_POST['full_name']); ?></div>
      <div><?php echo esc($_POST['phone']); ?></div>
      <div><?php echo nl2br(esc($_POST['address'])); ?></div>
      <div><?php echo esc($_POST['city']); ?>, <?php echo esc($_POST['province']); ?> <?php echo esc($_POST['postal_code']); ?></div>
      <?php if(!empty($_POST['notes'])): ?><div>Catatan: <?php echo esc($_POST['notes']); ?></div><?php endif; ?>
    </div>
    <div class="col-md-6">
      <h5>Ringkasan</h5>
      <div class="d-flex justify-content-between"><span>Subtotal</span><strong><?php echo formatRupiah($subtotal); ?></strong></div>
      <div class="d-flex justify-content-between"><span>Biaya layanan (5%)</span><strong><?php echo formatRupiah($serviceFee); ?></strong></div>
      <div class="d-flex justify-content-between"><span>Kurir</span><span><?php echo esc($courier); ?> - <?php echo esc($service); ?></span></div>
      <div class="d-flex justify-content-between"><span>Ongkir</span><strong><?php echo formatRupiah($shipping); ?></strong></div>
      <hr>
      <div class="d-flex justify-content-between"><span>Total</span><strong><?php echo formatRupiah($total); ?></strong></div>
      <div class="mt-3 alert alert-success py-2">
        <div><strong>Status:</strong> Menunggu konfirmasi pembayaran.</div>
        <div><strong>Poin setelah pembayaran terkonfirmasi:</strong> <?php echo (int)$pointsPending; ?> poin.</div>
        <div><strong>Poin Saat Ini:</strong> <?php echo (int)($customerInfo['points_balance'] ?? 0); ?> poin.</div>
        <div><strong>Voucher Terkumpul:</strong> <?php echo (int)($customerInfo['voucher_count'] ?? 0); ?> voucher.</div>
        <?php if((int)$pointsPending === 0): ?>
          <div class="small">Pesanan ini belum mencapai Rp 10.000 untuk mendapat 1 poin. Tambahkan belanja lebih dari Rp 10.000.</div>
        <?php endif; ?>
        <small>Setiap 10.000 belanja = 1 poin. 5 poin ditukar menjadi hadiah eco-friendly.</small>
      </div>
    </div>
  </div>

  <div class="table-responsive mt-3">
    <table class="table">
      <thead><tr><th>Produk</th><th>Seller</th><th>Ukuran</th><th class="text-end">Harga</th><th class="text-center">Qty</th><th class="text-end">Subtotal</th></tr></thead>
      <tbody>
      <?php foreach($cart as $item): $isReward = !empty($item['is_reward']); $st = $isReward ? 0 : ($item['price'] * $item['qty']); ?>
        <tr>
          <td>
            <?php echo esc($item['name']); ?>
            <?php if($isReward): ?><span class="badge bg-success ms-2">Reward klaim</span><?php endif; ?>
          </td>
          <td><?php echo !empty($item['seller_name']) ? esc($item['seller_name']) : '-'; ?></td>
          <td><?php echo esc($item['size']); ?></td>
          <td class="text-end"><?php echo formatRupiah($item['price']); ?></td>
          <td class="text-center"><?php echo (int)$item['qty']; ?></td>
          <td class="text-end"><?php echo $isReward ? 'Gratis' : formatRupiah($st); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="d-flex flex-wrap gap-2">
  <!-- <a id="btnOpenInvoice" class="btn btn-outline-primary" target="_blank" href="invoice.php?no=<?php echo urlencode($orderNo); ?>">
    <i class="fa fa-file-invoice"></i> Buka Invoice
  </a> -->
  <a id="btnOpenWA" class="btn btn-success" target="_blank" href="<?php echo $waLink; ?>">
    <i class="fa-brands fa-whatsapp"></i> Kirim ke WhatsApp
  </a>
  <a class="btn btn-primary" href="index.php">Belanja Lagi</a>
  <div class="btn-group">
    <button class="btn btn-primary dropdown-toggle" id="btnInvoiceDropdown" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="fa fa-file-invoice"></i> Invoice
    </button>
    <ul class="dropdown-menu">
      <li><a class="dropdown-item" target="_blank" href="invoice.php?no=<?php echo urlencode($orderNo); ?>">Buka Invoice</a></li>
      <li><a class="dropdown-item" href="#" onclick="openInvoice('print');return false;">Cetak</a></li>
      <li><a class="dropdown-item" href="#" onclick="openInvoice('pdf');return false;">Simpan ke PDF</a></li>
    </ul>
  </div>
</div>
<script>
function openInvoice(mode){
  const url = 'invoice.php?no=<?php echo urlencode($orderNo); ?>&mode='+mode;
  const w = window.open(url, '_blank');
  if(!w){ alert('Popup diblok. Izinkan popup untuk situs ini.'); return; }
  w.onload = () => { if(mode==='print' || mode==='pdf'){ w.focus(); w.print(); } };
}

// Auto open invoice & WhatsApp (may be blocked by popup policy)
setTimeout(function(){
  try{
    var winInv = window.open('invoice.php?no=<?php echo urlencode($orderNo); ?>','_blank');
    var winWA  = window.open('<?php echo $waLink; ?>','_blank');
    if(!winInv || !winWA){
      console.log('Popup diblokir. Silakan klik tombol Invoice/WhatsApp.');
    }
  }catch(e){ console.log(e); }
}, 300);
</script>
</div>
<?php
$_SESSION['cart'] = [];
include 'footer.php'; ?>
