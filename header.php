<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/functions.php';
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Terra Kala | Eco Recycled Store</title>
  <meta name="description" content="Toko online eco-friendly yang menjual produk daur ulang dan ramah lingkungan">
  <meta name="author" content="Terra Kala">
  <link rel="icon" type="image/png" href="logo1.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --bg: #f5f8ef;
      --card: #f2f7e8;
      --text: #223426;
      --muted: #5f6f5c;
      --accent: #2f6b3f;
      --accent-2: #6d9440;
      --accent-soft: #dcebc5;
      --border: #c7d9b6;
    }

    * {
      font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, 'Apple Color Emoji', 'Segoe UI Emoji'
    }

    h1,
    h2,
    h3,
    .navbar-brand {
      font-family: 'Bebas Neue', 'Inter', sans-serif;
      letter-spacing: .5px
    }

    body {
      background: linear-gradient(180deg, #f9fcf3 0%, var(--bg) 100%);
      color: var(--text)
    }

    a {
      color: var(--accent)
    }

    a:hover {
      color: var(--accent-2)
    }

    .navbar-brand {
      font-weight: 700
    }

    .brand-neon {
      color: var(--accent)
    }

    .card {
      background: linear-gradient(145deg, #f7fceb 0%, var(--card) 100%);
      border: 1px solid var(--border);
      border-radius: 18px;
      box-shadow: 0 10px 24px rgba(47, 107, 63, .08);
    }

    .text-muted {
      color: var(--muted) !important
    }

    .badge-size {
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 2.4rem;
      height: 2.2rem;
      padding: 0 0.7rem;
      margin: 0.2rem 0.2rem 0.2rem 0;
      border: 1px solid var(--border) !important;
      background: #fdfef9 !important;
      color: var(--text) !important;
      border-radius: 999px;
      font-size: 0.85rem;
      font-weight: 600;
      line-height: 1;
      transition: all .2s ease;
      box-shadow: inset 0 1px 0 rgba(255,255,255,.5);
    }

    .badge-size:hover {
      background: var(--accent-soft) !important;
      border-color: var(--accent) !important;
      transform: translateY(-1px);
    }

    .badge-size.is-selected,
    .badge-size.bg-dark,
    .badge-size.text-white {
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-2) 100%) !important;
      color: #fff !important;
      border-color: var(--accent) !important;
      box-shadow: 0 4px 10px rgba(47, 107, 63, .16);
    }

    .btn-primary {
      background: var(--accent);
      border-color: var(--accent);
      color: #fff
    }

    .btn-primary:hover {
      background: var(--accent-2);
      border-color: var(--accent-2);
      color: #fff;
      transform: translateY(-1px);
      box-shadow: 0 6px 14px rgba(47, 107, 63, .18);
    }

    .btn-outline-secondary {
      color: var(--text);
      border-color: rgba(255, 255, 255, .2)
    }

    .btn-outline-secondary:hover {
      background: rgba(255, 255, 255, .08)
    }

    .table {
      color: var(--text)
    }

    .table thead th {
      border-color: rgba(255, 255, 255, .1)
    }

    .table td,
    .table th {
      border-color: rgba(255, 255, 255, .06)
    }

    .form-control,
    .form-select {
      background: #fbfdf7;
      color: var(--text);
      border-color: var(--border)
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 .25rem rgba(57, 255, 20, .15)
    }

    .divider {
      height: 3px;
      width: 90px;
      background: linear-gradient(90deg, var(--accent), var(--accent-2));
      border-radius: 999px;
      margin: 8px 0 18px 0
    }

    .hero-gradient {
      background:
        radial-gradient(1000px 500px at -10% -10%, rgba(109, 148, 64, .18), rgba(109, 148, 64, 0) 65%),
        radial-gradient(800px 400px at 110% 10%, rgba(47, 107, 63, .18), rgba(47, 107, 63, 0) 60%),
        linear-gradient(135deg, #1c3a24 0%, #2f6b3f 45%, #5a8f3f 100%);
      color: #fff;
    }

    .glass-card {
      background: rgba(248, 252, 242, .92);
      border: 1px solid rgba(255,255,255,.45);
      color: #223426;
      backdrop-filter: blur(8px);
      box-shadow: 0 10px 24px rgba(17, 41, 23, .12);
    }

    .grid-icons .icon {
      width: 42px;
      height: 42px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 12px;
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-2) 100%);
      color: #fff;
    }

    .timeline {
      position: relative;
      padding-left: 2rem
    }

    .timeline:before {
      content: '';
      position: absolute;
      left: 14px;
      top: 0;
      bottom: 0;
      width: 2px;
      background: linear-gradient(180deg, var(--accent), var(--accent-soft));
    }

    .timeline .t-item {
      position: relative;
      margin-bottom: 1rem;
      padding-left: 2rem
    }

    .timeline .t-item:before {
      content: '';
      position: absolute;
      left: 6px;
      top: .4rem;
      width: 10px;
      height: 10px;
      background: var(--accent-2);
      border-radius: 50%;
      box-shadow: 0 0 0 3px rgba(109, 148, 64, .2)
    }

    .carousel-caption {
      backdrop-filter: blur(2px)
    }

    /* THEME RESET LIGHT */
    html,
    body {
      background: #f7fbf0 !important;
      color: #223426 !important;
    }

    .navbar {
      background: #f7fbf0 !important;
      border-bottom: 1px solid var(--border);
    }

    .card {
      background: linear-gradient(145deg, #f8fcea 0%, #eef6e3 100%);
    }

    .text-light {
      color: #212529 !important;
    }

    /* prevent accidental light text */
    a.nav-link {
      color: #212529 !important;
    }

    .carousel-caption {
      color: #212529;
    }

    /* Admin Products — equal height & tidy */
    .admin-products-row {
      align-items: stretch;
    }

    .admin-card {
      height: 100%;
    }

    .admin-card .item {
      display: flex;
      gap: 16px;
      align-items: flex-start;
    }

    .product-thumb {
      width: 96px;
      height: 96px;
      object-fit: cover;
      border-radius: 12px;
      display: block;
    }

    .admin-card .actions {
      margin-top: auto;
    }


    /* Admin Products — stable spacing & clean look */
    .admin-products-row {
      align-items: stretch;
    }

    .admin-card {
      height: 100%;
      border: 1px solid #e9ecef;
      border-radius: 14px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, .06);
      overflow: hidden;
      transition: box-shadow .2s ease;
    }

    .admin-card:hover {
      box-shadow: 0 12px 28px rgba(0, 0, 0, .10);
    }

    .admin-card .item {
      display: flex;
      gap: 16px;
      align-items: flex-start;
    }

    .thumb-box {
      padding: 6px;
      border-radius: 12px;
      background: #f6f9ff;
      border: 1px solid #e7effc;
      box-shadow: 0 2px 8px rgba(13, 110, 253, .04);
      flex: 0 0 auto;
    }

    .product-thumb {
      width: 110px;
      height: 110px;
      border-radius: 10px;
      object-fit: cover;
      display: block;
    }

    @media (min-width: 992px) {
      .product-thumb {
        width: 120px;
        height: 120px;
      }
    }

    .admin-card .actions {
      margin-top: auto;
    }

    /* Admin Products — meta flex fix */
    .admin-card .meta {
      flex: 1 1 auto;
      min-width: 0;
      display: flex;
      flex-direction: column;
    }

    .admin-card .item {
      width: 100%;
    }

    .glass-card,
    .glass-card h4,
    .glass-card h5,
    .glass-card h6,
    .glass-card p,
    .glass-card span,
    .glass-card li {
      color: #111 !important;
    }

    .card,
    .card h1,
    .card h2,
    .card h3,
    .card h4,
    .card h5,
    .card h6,
    .card p,
    .card span,
    .card li {
      color: #111;
    }

    .container {
      max-width: 1200px;
    }

    .navbar {
      box-shadow: 0 6px 16px rgba(47, 107, 63, .08) !important;
      padding: .7rem 0;
    }

    .navbar-brand img {
      display: block;
      max-height: 40px;
    }

    .nav-link {
      font-weight: 600;
      color: #223426 !important;
      padding: .6rem .85rem !important;
      border-radius: 999px;
      transition: background .2s ease, color .2s ease;
    }

    .nav-link:hover {
      background: var(--accent-soft);
      color: var(--accent) !important;
    }

    .btn {
      border-radius: 999px;
      font-weight: 600;
      padding: .65rem 1rem;
      transition: transform .15s ease, box-shadow .15s ease;
    }

    .btn:hover {
      transform: translateY(-1px);
    }

    .btn-sm {
      padding: .4rem .75rem;
    }

    .form-control,
    .form-select,
    .form-check-input {
      border-radius: 12px;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 .2rem rgba(47, 107, 63, .16);
    }

    .table {
      border-collapse: separate;
      border-spacing: 0;
      background: #fff;
      border-radius: 14px;
      overflow: hidden;
    }

    .table thead th {
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-2) 100%);
      color: #fff;
      border: 0;
      font-weight: 600;
      letter-spacing: .2px;
    }

    .table td,
    .table th {
      padding: .8rem .9rem;
      vertical-align: middle;
      border-color: #e8efe0;
    }

    .table tbody tr:nth-child(even) {
      background: #fafdf6;
    }

    .table-hover tbody tr:hover {
      background: #f3f8e7;
    }

    .alert {
      border-radius: 14px;
      border: 1px solid var(--border);
    }
    .site-navbar .container { gap:1rem; }
    .site-nav-links { align-items:center; gap:.25rem; }
    .site-nav-link { display:inline-flex; align-items:center; gap:.5rem; }
    .site-cart-link { position:relative; display:inline-flex!important; align-items:center; justify-content:center; width:44px; height:44px; padding:0!important; border:1px solid var(--border); border-radius:12px!important; }
    .site-cart-count { position:absolute; top:-5px; right:-5px; min-width:19px; height:19px; display:grid; place-items:center; padding:0 4px; border-radius:999px; background:var(--accent); color:#fff!important; font-size:.68rem; line-height:1; }
    .site-nav-menu { border:1px solid var(--border); border-radius:12px; box-shadow:0 10px 24px rgba(34,52,38,.12); padding:.4rem; }
    .site-nav-menu .dropdown-item { display:flex; align-items:center; gap:.6rem; padding:.6rem .75rem; border-radius:8px; }
    .site-nav-menu .dropdown-item i { width:1rem; color:var(--accent); text-align:center; }
    .site-nav-menu .dropdown-divider { margin:.35rem 0; }
    .site-nav-toggle { border:0; background:transparent; }
    @media (max-width:991.98px) {
      .site-navbar .container { gap:.5rem; }
      .site-nav-links { align-items:stretch; padding:.5rem 0 .25rem; }
      .site-cart-link { width:auto; height:auto; min-height:44px; justify-content:flex-start; padding:.6rem .85rem!important; border:0; }
      .site-cart-count { position:static; min-width:21px; height:21px; margin-left:.15rem; }
      .site-nav-menu { border:0; box-shadow:none; padding-left:1rem; }
    }
  </style>
</head>
<?php
$BASE_PATH = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../' : '';
$cartItemCount = isset($_SESSION['cart']) ? array_sum(array_map(function($item){ return (int)($item['qty'] ?? 0); }, $_SESSION['cart'])) : 0;
$isAdmin = !empty($_SESSION['is_admin']);
$isCustomer = !empty($_SESSION['customer_id']);
$isSeller = !empty($_SESSION['seller_logged_in']) && !empty($_SESSION['seller_id']);
?>

<body>
  <nav class="navbar bg-white navbar-expand-lg shadow-sm site-navbar">
    <div class="container">
      <a class="navbar-brand brand-neon" href="<?php echo $BASE_PATH; ?>index.php">
        <img src="<?php echo $BASE_PATH; ?>logo1.png" alt="Terra Kala" height="40">
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Buka navigasi">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div id="nav" class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto site-nav-links">
          <li class="nav-item"><a class="nav-link site-nav-link" href="<?php echo $BASE_PATH; ?>index.php"><i class="fa-solid fa-house"></i><span>Beranda</span></a></li>
          <li class="nav-item"><a class="nav-link site-nav-link" href="<?php echo $BASE_PATH; ?>community.php"><i class="fa-solid fa-seedling"></i><span>Komunitas</span></a></li>
          <li class="nav-item">
            <a class="nav-link site-cart-link" href="<?php echo $BASE_PATH; ?>cart.php" aria-label="Keranjang, <?php echo (int)$cartItemCount; ?> item" title="Keranjang">
              <i class="fa-solid fa-bag-shopping"></i><span class="site-cart-count"><?php echo $cartItemCount > 99 ? '99+' : (int)$cartItemCount; ?></span>
            </a>
          </li>
          <?php if($isAdmin || $isCustomer || $isSeller): ?>
            <li class="nav-item dropdown">
              <button class="nav-link dropdown-toggle site-nav-link site-nav-toggle" id="accountMenu" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid <?php echo $isAdmin ? 'fa-user-shield' : ($isSeller ? 'fa-store' : 'fa-user'); ?>"></i><span><?php echo $isAdmin ? 'Menu admin' : ($isSeller ? 'Menu seller' : 'Akun'); ?></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end site-nav-menu" aria-labelledby="accountMenu">
                <?php if($isAdmin): ?>
                  <li><a class="dropdown-item" href="<?php echo $BASE_PATH; ?>admin/products.php"><i class="fa-solid fa-box"></i>Kelola produk</a></li>
                  <li><a class="dropdown-item" href="<?php echo $BASE_PATH; ?>admin/community.php"><i class="fa-solid fa-seedling"></i>Kelola komunitas</a></li>
                  <li><a class="dropdown-item" href="<?php echo $BASE_PATH; ?>admin/orders.php"><i class="fa-solid fa-receipt"></i>Verifikasi pesanan</a></li>
                <?php elseif($isSeller): ?>
                  <li><a class="dropdown-item" href="<?php echo $BASE_PATH; ?>seller_dashboard.php"><i class="fa-solid fa-store"></i>Dashboard seller</a></li>
                <?php elseif($isCustomer): ?>
                  <li><a class="dropdown-item" href="<?php echo $BASE_PATH; ?>account.php"><i class="fa-solid fa-circle-user"></i>Akun pelanggan</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?php echo $BASE_PATH; ?>logout.php"><i class="fa-solid fa-right-from-bracket text-danger"></i>Keluar</a></li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item dropdown">
              <button class="nav-link dropdown-toggle site-nav-link site-nav-toggle" id="loginMenu" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-user"></i><span>Masuk</span></button>
              <ul class="dropdown-menu dropdown-menu-end site-nav-menu" aria-labelledby="loginMenu">
                <li><a class="dropdown-item" href="<?php echo $BASE_PATH; ?>customer_login.php"><i class="fa-solid fa-user"></i>Login pelanggan</a></li>
                <li><a class="dropdown-item" href="<?php echo $BASE_PATH; ?>customer_register.php"><i class="fa-solid fa-user-plus"></i>Daftar pelanggan</a></li>
                <li><a class="dropdown-item" href="<?php echo $BASE_PATH; ?>seller_login.php"><i class="fa-solid fa-store"></i>Login seller</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?php echo $BASE_PATH; ?>admin/login.php"><i class="fa-solid fa-user-shield"></i>Login admin</a></li>
              </ul>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>
  <div class="container my-4">