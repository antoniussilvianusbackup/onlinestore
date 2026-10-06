<?php
if (session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__.'/functions.php';

$no = $_GET['no'] ?? null;
$mode = $_GET['mode'] ?? null;
$order = $_SESSION['last_order'] ?? null;
if(!$order || ($no && $order['order_no'] !== $no)){
  http_response_code(404);
  echo '<!doctype html><html><head><meta charset="utf-8"><title>Invoice</title></head><body><div style="font-family:Arial,sans-serif;padding:24px">'
      .'Invoice tidak ditemukan. Silakan buat pesanan terlebih dahulu.'
      .'</div></body></html>';
  exit;
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Invoice <?php echo esc($order['order_no']); ?> | Terra Kala</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#f5f8ef;
  --card:#f2f7e8;
  --text:#223426;
  --muted:#5f6f5c;
  --accent:#2f6b3f;
  --accent-2:#6d9440;
  --border:#c7d9b6;
}
*{font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif}
h1,h2,h3,.brand-title{font-family:'Manrope','Inter',sans-serif}
body{background:linear-gradient(180deg,#f9fcf3 0%,var(--bg) 100%); color:var(--text)}
.invoice-page{max-width:900px; margin:24px auto; padding:0 12px}
.logo{display:flex; align-items:center; gap:10px}
.logo img{height:36px; width:auto}
.brand-title{font-size:1.15rem; color:var(--accent)}
.badge-order{font-size:.85rem; background:linear-gradient(135deg,var(--accent) 0%,var(--accent-2) 100%); border:none}
.card{background:linear-gradient(145deg,#f7fceb 0%,var(--card) 100%); border:1px solid var(--border); border-radius:18px; box-shadow:0 10px 24px rgba(47,107,63,.08)}
.table th, .table td{vertical-align:middle; border-color:var(--border)}
.table thead th{background:rgba(47,107,63,.06)}
.btn-primary{background:var(--accent); border-color:var(--accent)}
.btn-primary:hover{background:var(--accent-2); border-color:var(--accent-2)}
.btn-outline-secondary{color:var(--text); border-color:var(--border)}
.btn-outline-secondary:hover{background:rgba(47,107,63,.08); color:var(--text)}
@media print{
  body{background:#fff}
  .no-print{display:none!important}
  .card{box-shadow:none; border:1px solid #ddd}
}
</style>
</head>
<body>
<div class="invoice-page">
  <div class="d-flex justify-content-between align-items-start mb-3">
    <div class="logo">
      <img src="logo1.png" alt="Logo Terra Kala" onerror="this.onerror=null;this.src='favicon.svg';">
      <div>
        <div class="fw-bold brand-title">Terra Kala</div>
        <div class="text-muted small">Produk Guna Ulang dan Upcycle<br>Kp. Pitara Rangkapanjaya, Depok<br>Telp/WA: 081414002303</div>
      </div>
    </div>
    <div class="text-end">
      <div class="badge bg-primary badge-order">Invoice</div>
      <div class="fw-bold">#<?php echo esc($order['order_no']); ?></div>
      <div class="text-muted small"><?php echo esc($order['created_at']); ?></div>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <div class="card p-3">
        <div class="fw-semibold mb-1">Kepada:</div>
        <div><?php echo esc($order['customer']['full_name']); ?></div>
        <div class="text-muted small"><?php echo esc($order['customer']['phone']); ?></div>
        <div class="text-muted small"><?php echo nl2br(esc($order['customer']['address'])); ?></div>
        <div class="text-muted small"><?php echo esc($order['customer']['city']); ?>, <?php echo esc($order['customer']['province']); ?> <?php echo esc($order['customer']['postal_code']); ?></div>
        <?php if(!empty($order['customer']['notes'])): ?>
        <div class="text-muted small">Catatan: <?php echo esc($order['customer']['notes']); ?></div>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card p-3">
        <div class="fw-semibold mb-1">Pengiriman:</div>
        <div class="text-muted small">Status pembayaran: <?php echo esc(ucfirst($order['payment_status'] ?? 'pending')); ?></div>
        <div class="text-muted small">Kurir: <?php echo esc($order['courier']); ?> (<?php echo esc($order['service']); ?>)</div>
        <div class="text-muted small">Ongkir: <?php echo formatRupiah($order['shipping']); ?></div>
        <div class="text-muted small">Subtotal: <?php echo formatRupiah($order['subtotal']); ?></div>
        <div class="text-muted small">Biaya layanan (5%): <?php echo formatRupiah($order['service_fee'] ?? 0); ?></div>
        <?php if(($order['payment_status'] ?? 'pending') === 'pending'): ?><div class="text-muted small">Poin yang akan diperoleh setelah pembayaran: <?php echo (int)($order['points_pending'] ?? 0); ?> poin</div><?php elseif(!empty($order['points_earned']) || !empty($order['voucher_awarded'])): ?>
        <div class="text-muted small">Poin Diperoleh: <?php echo (int)($order['points_earned'] ?? 0); ?> poin</div>
        <div class="text-muted small">Poin Saat Ini: <?php echo (int)($order['points_balance'] ?? 0); ?> poin</div>
        <div class="text-muted small">Voucher Terkumpul: <?php echo (int)($order['voucher_count'] ?? 0); ?> voucher</div>
        <?php endif; ?>
        <div class="fw-bold">Total: <?php echo formatRupiah($order['total']); ?></div>
      </div>
    </div>
  </div>

  <div class="card p-3">
    <div class="table-responsive">
      <table class="table">
        <thead><tr><th>Produk</th><th>Seller</th><th class="text-center">Ukuran</th><th class="text-end">Harga</th><th class="text-center">Qty</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
        <?php foreach($order['items'] as $it): $isReward = !empty($it['is_reward']); $st = $isReward ? 0 : ($it['price'] * $it['qty']); ?>
          <tr>
            <td>
              <?php echo esc($it['name']); ?>
              <?php if($isReward): ?><span class="badge bg-success ms-2">Reward klaim</span><?php endif; ?>
            </td>
            <td><?php echo !empty($it['seller_name']) ? esc($it['seller_name']) : '-'; ?></td>
            <td class="text-center"><?php echo esc($it['size']); ?></td>
            <td class="text-end"><?php echo formatRupiah($it['price']); ?></td>
            <td class="text-center"><?php echo (int)$it['qty']; ?></td>
            <td class="text-end"><?php echo $isReward ? 'Gratis' : formatRupiah($st); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr><th colspan="5" class="text-end">Subtotal</th><th class="text-end"><?php echo formatRupiah($order['subtotal']); ?></th></tr>
          <tr><th colspan="5" class="text-end">Biaya layanan (5%)</th><th class="text-end"><?php echo formatRupiah($order['service_fee'] ?? 0); ?></th></tr>
          <tr><th colspan="5" class="text-end">Ongkir (<?php echo esc($order['courier'].' - '.$order['service']); ?>)</th><th class="text-end"><?php echo formatRupiah($order['shipping']); ?></th></tr>
          <tr><th colspan="5" class="text-end">Total</th><th class="text-end"><?php echo formatRupiah($order['total']); ?></th></tr>
        </tfoot>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2 no-print">
      <div class="text-muted small">* Simpan sebagai PDF via dialog print browser.</div>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" onclick="window.close()">Tutup</button>
        <button class="btn btn-primary" onclick="window.print()">Cetak</button>
      </div>
    </div>
  </div>
</div>

<?php if($mode==='print' || $mode==='pdf'): ?>
<script>
window.addEventListener('load', function(){ window.print(); });
</script>
<?php endif; ?>
</body>
</html>
