<?php
require_once __DIR__.'/config.php';

function esc($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function csrf_token(){
  if(session_status()===PHP_SESSION_NONE) session_start();
  if(empty($_SESSION['_csrf_token'])){
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(16));
  }
  return $_SESSION['_csrf_token'];
}

function csrf_verify($token){
  if(session_status()===PHP_SESSION_NONE) session_start();
  return !empty($token) && !empty($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], $token);
}

function ensureOrderSchema(){
  $pdo = getPDO();
  $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_no VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NULL,
    customer_name VARCHAR(120) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    customer_address TEXT NOT NULL,
    customer_city VARCHAR(100) NOT NULL,
    customer_province VARCHAR(100) NOT NULL,
    customer_postal_code VARCHAR(20) NOT NULL,
    customer_notes TEXT NULL,
    courier VARCHAR(50) NULL,
    service VARCHAR(50) NULL,
    subtotal INT NOT NULL DEFAULT 0,
    service_fee INT NOT NULL DEFAULT 0,
    shipping_cost INT NOT NULL DEFAULT 0,
    total INT NOT NULL DEFAULT 0,
    payment_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    auction_id INT NULL UNIQUE,
    points_earned INT NOT NULL DEFAULT 0,
    voucher_awarded INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_orders_customer_id (customer_id)
  ) ENGINE=InnoDB");

  $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NULL,
    product_name VARCHAR(255) NOT NULL,
    size VARCHAR(50) NULL,
    price INT NOT NULL DEFAULT 0,
    qty INT NOT NULL DEFAULT 1,
    seller_id INT NULL DEFAULT NULL,
    seller_name VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_order_items_order_id (order_id),
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
  ) ENGINE=InnoDB");

  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN customer_name VARCHAR(120) NOT NULL DEFAULT ''"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN customer_phone VARCHAR(50) NOT NULL DEFAULT ''"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN customer_address TEXT NOT NULL DEFAULT ''"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN customer_city VARCHAR(100) NOT NULL DEFAULT ''"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN customer_province VARCHAR(100) NOT NULL DEFAULT ''"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN customer_postal_code VARCHAR(20) NOT NULL DEFAULT ''"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN customer_notes TEXT NULL"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN courier VARCHAR(50) NULL"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN service VARCHAR(50) NULL"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN subtotal INT NOT NULL DEFAULT 0"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN service_fee INT NOT NULL DEFAULT 0"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN shipping_cost INT NOT NULL DEFAULT 0"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN total INT NOT NULL DEFAULT 0"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN payment_status VARCHAR(20) NOT NULL DEFAULT 'paid'"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN auction_id INT NULL UNIQUE"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN customer_id INT NULL"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN points_earned INT NOT NULL DEFAULT 0"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN voucher_awarded INT NOT NULL DEFAULT 0"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE order_items ADD COLUMN seller_id INT NULL DEFAULT NULL"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE order_items ADD COLUMN seller_name VARCHAR(255) NULL"); }catch(Throwable $e){}
}

function ensureProductsSchema(){
  $pdo = getPDO();
  // create products table if not exists
  $pdo->exec("CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    price INT NOT NULL DEFAULT 0,
    image_path VARCHAR(255) NULL,
    sizes VARCHAR(255) NULL,
    seller_id INT NULL DEFAULT NULL,
    is_best_seller TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB");
  // add columns if they don't exist (ignore errors)
  try{ $pdo->exec("ALTER TABLE products ADD COLUMN seller_id INT NULL DEFAULT NULL"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE products ADD COLUMN is_best_seller TINYINT(1) NOT NULL DEFAULT 0"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE products ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"); }catch(Throwable $e){}
}

function ensureSellerColumns(){
  $pdo = getPDO();
  $columns = [];
  foreach($pdo->query("SHOW COLUMNS FROM sellers")->fetchAll() as $col){
    $columns[$col['Field']] = true;
  }
  $alterParts = [];
  if(!isset($columns['username'])){ $alterParts[] = "ADD COLUMN username VARCHAR(100) NULL"; }
  if(!isset($columns['password_hash'])){ $alterParts[] = "ADD COLUMN password_hash VARCHAR(255) NULL"; }
  if(!isset($columns['is_active'])){ $alterParts[] = "ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1"; }
  if(!isset($columns['bonus_points_balance'])){ $alterParts[] = "ADD COLUMN bonus_points_balance INT NOT NULL DEFAULT 0"; }
  if($alterParts){
    $pdo->exec("ALTER TABLE sellers " . implode(', ', $alterParts));
  }
}

function ensureSellersSchema(){
  $pdo = getPDO();
  $pdo->exec("CREATE TABLE IF NOT EXISTS sellers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    owner_name VARCHAR(120) NULL,
    phone VARCHAR(50) NULL,
    email VARCHAR(120) NULL,
    address TEXT NULL,
    username VARCHAR(100) NULL,
    password_hash VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB");
  ensureSellerColumns();
  try{ $pdo->exec("ALTER TABLE products ADD COLUMN seller_id INT NULL DEFAULT NULL"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE products ADD INDEX idx_products_seller_id (seller_id)"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE products ADD CONSTRAINT fk_products_seller FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE SET NULL"); }catch(Throwable $e){}
  ensureDemoSeller();
}

function ensureCustomersSchema(){
  $pdo = getPDO();
  $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(50) NOT NULL UNIQUE,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    province VARCHAR(100) NULL,
    postal_code VARCHAR(20) NULL,
    points_balance INT NOT NULL DEFAULT 0,
    voucher_count INT NOT NULL DEFAULT 0,
    last_order_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB");
}

function ensureCommunitySchema(){
  $pdo = getPDO();
  ensureCustomersSchema();
  ensureSellersSchema();
  ensureOrderSchema();
  $pdo->exec("CREATE TABLE IF NOT EXISTS diy_resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    resource_type VARCHAR(20) NOT NULL DEFAULT 'video',
    resource_url VARCHAR(500) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB");
  $pdo->exec("CREATE TABLE IF NOT EXISTS diy_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    customer_name VARCHAR(120) NOT NULL,
    school_name VARCHAR(180) NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    video_path VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    points_awarded INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_diy_submissions_status (status)
  ) ENGINE=InnoDB");
  $pdo->exec("CREATE TABLE IF NOT EXISTS auctions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    image_url VARCHAR(500) NULL,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NOT NULL,
    starting_bid INT NOT NULL DEFAULT 0,
    min_increment INT NOT NULL DEFAULT 10000,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB");
  $pdo->exec("CREATE TABLE IF NOT EXISTS auction_bids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    auction_id INT NOT NULL,
    customer_id INT NOT NULL,
    customer_name VARCHAR(120) NOT NULL,
    bid_amount INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_auction_bids_auction (auction_id, bid_amount),
    CONSTRAINT fk_auction_bids_auction FOREIGN KEY (auction_id) REFERENCES auctions(id) ON DELETE CASCADE
  ) ENGINE=InnoDB");
  $pdo->exec("CREATE TABLE IF NOT EXISTS auction_settlements (
    auction_id INT PRIMARY KEY,
    winner_customer_id INT NULL,
    winner_name VARCHAR(120) NULL,
    winning_bid INT NOT NULL DEFAULT 0,
    order_id INT NULL UNIQUE,
    settlement_status VARCHAR(20) NOT NULL,
    finalized_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_auction_settlements_auction FOREIGN KEY (auction_id) REFERENCES auctions(id) ON DELETE CASCADE
  ) ENGINE=InnoDB");
  $pdo->exec("CREATE TABLE IF NOT EXISTS school_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    description TEXT NULL,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NOT NULL,
    top_prize VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_finalized TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB");
  try{ $pdo->exec("ALTER TABLE school_events ADD COLUMN is_finalized TINYINT(1) NOT NULL DEFAULT 0"); }catch(Throwable $e){}
  $pdo->exec("CREATE TABLE IF NOT EXISTS school_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    school_name VARCHAR(180) NOT NULL,
    team_name VARCHAR(180) NOT NULL,
    score INT NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_school_team_event (event_id, school_name, team_name),
    CONSTRAINT fk_school_scores_event FOREIGN KEY (event_id) REFERENCES school_events(id) ON DELETE CASCADE
  ) ENGINE=InnoDB");
  $pdo->exec("CREATE TABLE IF NOT EXISTS school_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    customer_id INT NOT NULL,
    customer_name VARCHAR(120) NOT NULL,
    school_name VARCHAR(180) NOT NULL,
    team_name VARCHAR(180) NOT NULL,
    mission_description TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_school_submissions_status (status),
    CONSTRAINT fk_school_submissions_event FOREIGN KEY (event_id) REFERENCES school_events(id) ON DELETE CASCADE
  ) ENGINE=InnoDB");
  $pdo->exec("CREATE TABLE IF NOT EXISTS school_awards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    rank_position TINYINT NOT NULL,
    school_name VARCHAR(180) NOT NULL,
    team_name VARCHAR(180) NOT NULL,
    prize VARCHAR(255) NULL,
    award_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    awarded_at TIMESTAMP NULL,
    UNIQUE KEY uq_school_award_rank (event_id, rank_position),
    CONSTRAINT fk_school_awards_event FOREIGN KEY (event_id) REFERENCES school_events(id) ON DELETE CASCADE
  ) ENGINE=InnoDB");
  $pdo->exec("CREATE TABLE IF NOT EXISTS seller_bonus_periods (
    quarter_start DATE PRIMARY KEY,
    finalized_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB");
  $pdo->exec("CREATE TABLE IF NOT EXISTS seller_bonus_awards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    quarter_start DATE NOT NULL,
    points INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_seller_quarter_bonus (seller_id, quarter_start)
  ) ENGINE=InnoDB");
}

function getDiyResources($activeOnly = true){
  ensureCommunitySchema();
  $where = $activeOnly ? 'WHERE is_active=1' : '';
  return getPDO()->query("SELECT * FROM diy_resources $where ORDER BY created_at DESC")->fetchAll();
}

function addDiyResource($data){
  ensureCommunitySchema();
  $type = in_array(($data['resource_type'] ?? ''), ['video','ebook'], true) ? $data['resource_type'] : 'video';
  $url = trim($data['resource_url'] ?? '');
  if(!filter_var($url, FILTER_VALIDATE_URL) || !in_array(parse_url($url, PHP_URL_SCHEME), ['http','https'], true)) throw new RuntimeException('URL panduan harus berupa tautan HTTP/HTTPS yang valid.');
  if(trim($data['title'] ?? '') === '') throw new RuntimeException('Judul panduan wajib diisi.');
  $st = getPDO()->prepare("INSERT INTO diy_resources (title, description, resource_type, resource_url, is_active) VALUES (?,?,?,?,1)");
  $st->execute([trim($data['title']), trim($data['description'] ?? ''), $type, $url]);
}

function getCommunityAuctions($activeOnly = true){
  ensureCommunitySchema();
  $active = $activeOnly ? 'WHERE a.is_active=1' : '';
  return getPDO()->query("SELECT a.*, COALESCE((SELECT MAX(bid_amount) FROM auction_bids WHERE auction_id=a.id), a.starting_bid) AS current_bid,
    (SELECT COUNT(*) FROM auction_bids WHERE auction_id=a.id) AS bid_count
    FROM auctions a $active ORDER BY a.ends_at ASC")->fetchAll();
}

function getAuctionSettlements(){
  ensureCommunitySchema();
  return getPDO()->query("SELECT s.*, a.title AS auction_title, o.order_no, o.payment_status
    FROM auction_settlements s JOIN auctions a ON a.id=s.auction_id LEFT JOIN orders o ON o.id=s.order_id
    ORDER BY s.finalized_at DESC")->fetchAll();
}

function getCustomerAuctionOrders($customerId){
  ensureCommunitySchema();
  $st = getPDO()->prepare("SELECT o.order_no, o.total, o.payment_status, a.title AS auction_title
    FROM orders o JOIN auctions a ON a.id=o.auction_id WHERE o.customer_id=? ORDER BY o.created_at DESC");
  $st->execute([(int)$customerId]);
  return $st->fetchAll();
}

function settleAuction($auctionId){
  ensureCommunitySchema();
  $pdo = getPDO();
  $pdo->beginTransaction();
  try{
    $existing = $pdo->prepare("SELECT * FROM auction_settlements WHERE auction_id=? FOR UPDATE");
    $existing->execute([(int)$auctionId]);
    $settlement = $existing->fetch();
    if($settlement){ $pdo->commit(); return $settlement; }
    $auctionSt = $pdo->prepare("SELECT * FROM auctions WHERE id=? FOR UPDATE");
    $auctionSt->execute([(int)$auctionId]);
    $auction = $auctionSt->fetch();
    if(!$auction) throw new RuntimeException('Lelang tidak ditemukan.');
    if(date('Y-m-d H:i:s') < $auction['ends_at']) throw new RuntimeException('Lelang belum mencapai waktu penutupan.');
    $bidSt = $pdo->prepare("SELECT * FROM auction_bids WHERE auction_id=? ORDER BY bid_amount DESC, id ASC LIMIT 1");
    $bidSt->execute([(int)$auctionId]);
    $winner = $bidSt->fetch();
    if(!$winner){
      $pdo->prepare("INSERT INTO auction_settlements (auction_id, settlement_status) VALUES (?, 'no_bids')")->execute([(int)$auctionId]);
      $pdo->prepare("UPDATE auctions SET is_active=0 WHERE id=?")->execute([(int)$auctionId]);
      $pdo->commit();
      return ['auction_id'=>(int)$auctionId, 'settlement_status'=>'no_bids'];
    }
    $customerSt = $pdo->prepare("SELECT * FROM customers WHERE id=?");
    $customerSt->execute([(int)$winner['customer_id']]);
    $customer = $customerSt->fetch();
    if(!$customer) throw new RuntimeException('Akun pemenang lelang tidak ditemukan.');
    $serviceFee = calculateServiceFee($winner['bid_amount']);
    $orderId = createOrder([
      'order_no'=>'AUC'.str_pad((string)$auctionId, 6, '0', STR_PAD_LEFT).date('ymd'),
      'customer_id'=>(int)$customer['id'], 'customer_name'=>$customer['name'],
      'customer_phone'=>$customer['phone'], 'customer_address'=>$customer['address'] ?? '',
      'customer_city'=>$customer['city'] ?? '', 'customer_province'=>$customer['province'] ?? '',
      'customer_postal_code'=>$customer['postal_code'] ?? '',
      'customer_notes'=>'Pemenang lelang: '.$auction['title'],
      'courier'=>'Lelang', 'service'=>'Koordinasi admin',
      'subtotal'=>(int)$winner['bid_amount'], 'service_fee'=>$serviceFee,
      'shipping_cost'=>0, 'total'=>(int)$winner['bid_amount']+$serviceFee,
      'payment_status'=>'pending', 'auction_id'=>(int)$auctionId,
      'points_earned'=>0, 'voucher_awarded'=>0,
    ], [[
      'id'=>null, 'name'=>'Lelang: '.$auction['title'], 'size'=>'-',
      'price'=>(int)$winner['bid_amount'], 'qty'=>1,
    ]], true);
    $pdo->prepare("INSERT INTO auction_settlements (auction_id, winner_customer_id, winner_name, winning_bid, order_id, settlement_status) VALUES (?,?,?,?,?,'pending_payment')")
      ->execute([(int)$auctionId, (int)$customer['id'], $customer['name'], (int)$winner['bid_amount'], $orderId]);
    $pdo->prepare("UPDATE auctions SET is_active=0 WHERE id=?")->execute([(int)$auctionId]);
    $pdo->commit();
    return ['auction_id'=>(int)$auctionId, 'winner_customer_id'=>(int)$customer['id'], 'winner_name'=>$customer['name'], 'winning_bid'=>(int)$winner['bid_amount'], 'order_id'=>$orderId, 'settlement_status'=>'pending_payment'];
  }catch(Throwable $e){
    if($pdo->inTransaction()) $pdo->rollBack();
    throw $e;
  }
}

function addCommunityAuction($data){
  ensureCommunitySchema();
  if(trim($data['title'] ?? '') === '') throw new RuntimeException('Nama barang lelang wajib diisi.');
  if(strtotime($data['ends_at'] ?? '') <= strtotime($data['starts_at'] ?? '')) throw new RuntimeException('Waktu akhir lelang harus setelah waktu mulai.');
  $imageUrl = trim($data['image_url'] ?? '');
  if($imageUrl !== '' && (!filter_var($imageUrl, FILTER_VALIDATE_URL) || !in_array(parse_url($imageUrl, PHP_URL_SCHEME), ['http','https'], true))) throw new RuntimeException('URL foto harus menggunakan HTTP/HTTPS.');
  $start = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $data['starts_at'])));
  $end = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $data['ends_at'])));
  $st = getPDO()->prepare("INSERT INTO auctions (title, description, image_url, starts_at, ends_at, starting_bid, min_increment, is_active) VALUES (?,?,?,?,?,?,?,1)");
  $st->execute([trim($data['title']), trim($data['description'] ?? ''), $imageUrl, $start, $end, max(1, (int)($data['starting_bid'] ?? 1)), max(1000, (int)($data['min_increment'] ?? 10000))]);
}

function placeAuctionBid($auctionId, $customerId, $customerName, $amount){
  ensureCommunitySchema();
  $pdo = getPDO();
  $pdo->beginTransaction();
  try{
    $st = $pdo->prepare("SELECT * FROM auctions WHERE id=? AND is_active=1 FOR UPDATE");
    $st->execute([(int)$auctionId]);
    $auction = $st->fetch();
    $now = date('Y-m-d H:i:s');
    if(!$auction || $now < $auction['starts_at'] || $now >= $auction['ends_at']) throw new RuntimeException('Lelang belum dibuka atau sudah berakhir.');
    $bidSt = $pdo->prepare("SELECT MAX(bid_amount) FROM auction_bids WHERE auction_id=?");
    $bidSt->execute([(int)$auctionId]);
    $highest = $bidSt->fetchColumn();
    $minimum = $highest === false || $highest === null ? max(1, (int)$auction['starting_bid']) : (int)$highest + (int)$auction['min_increment'];
    if((int)$amount < $minimum) throw new RuntimeException('Penawaran minimal adalah '.formatRupiah($minimum).'.');
    $insert = $pdo->prepare("INSERT INTO auction_bids (auction_id, customer_id, customer_name, bid_amount) VALUES (?,?,?,?)");
    $insert->execute([(int)$auctionId, (int)$customerId, $customerName, (int)$amount]);
    $pdo->commit();
  }catch(Throwable $e){
    if($pdo->inTransaction()) $pdo->rollBack();
    throw $e;
  }
}

function getBuyerLeaderboard($limit = 10){
  ensureCommunitySchema();
  $st = getPDO()->prepare("SELECT c.id, c.name, COUNT(o.id) AS order_count, COALESCE(SUM(o.subtotal),0) AS spend_total, COALESCE(SUM(o.points_earned),0) AS points_total
    FROM customers c JOIN orders o ON o.customer_id=c.id WHERE o.payment_status='paid' GROUP BY c.id, c.name ORDER BY points_total DESC, spend_total DESC LIMIT ?");
  $st->bindValue(1, (int)$limit, PDO::PARAM_INT);
  $st->execute();
  return $st->fetchAll();
}

function getSellerLeaderboard($limit = 10){
  ensureCommunitySchema();
  $year = (int)date('Y');
  $quarter = intdiv((int)date('n') - 1, 3);
  $start = sprintf('%04d-%02d-01', $year, $quarter * 3 + 1);
  $end = date('Y-m-d', strtotime($start.' +3 months'));
  $st = getPDO()->prepare("SELECT s.id, s.name,
    (SELECT COUNT(*) FROM products p WHERE p.seller_id=s.id AND p.created_at>=? AND p.created_at<?) AS products_created,
    (SELECT COALESCE(SUM(oi.qty),0) FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE oi.seller_id=s.id AND o.payment_status='paid' AND o.created_at>=? AND o.created_at<?) AS units_sold,
    (SELECT COALESCE(SUM(oi.price*oi.qty),0) FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE oi.seller_id=s.id AND o.payment_status='paid' AND o.created_at>=? AND o.created_at<?) AS sales_total
    , s.bonus_points_balance
    FROM sellers s WHERE s.is_active=1 ORDER BY sales_total DESC, products_created DESC, s.name ASC");
  $st->bindValue(1, $start); $st->bindValue(2, $end);
  $st->bindValue(3, $start); $st->bindValue(4, $end);
  $st->bindValue(5, $start); $st->bindValue(6, $end);
  $st->execute();
  $rows = array_values(array_filter($st->fetchAll(), function($row){ return (int)$row['sales_total'] > 0 || (int)$row['products_created'] > 0; }));
  $rows = array_slice($rows, 0, (int)$limit);
  foreach($rows as $index => &$row){ $row['bonus_points'] = [500, 300, 150][$index] ?? 0; }
  unset($row);
  return ['rows'=>$rows, 'period'=>$start.' - '.date('Y-m-d', strtotime($end.' -1 day'))];
}

function settleSellerQuarter($periodStart){
  ensureCommunitySchema();
  $timestamp = strtotime($periodStart);
  if($timestamp === false) throw new RuntimeException('Periode triwulan tidak valid.');
  $periodStart = date('Y-m-d', $timestamp);
  $month = (int)date('n', $timestamp);
  $quarterMonths = [1,4,7,10];
  if((int)date('j', strtotime($periodStart)) !== 1 || !in_array($month, $quarterMonths, true)) throw new RuntimeException('Periode harus dimulai pada awal triwulan.');
  $year = (int)date('Y');
  $currentQuarterStart = sprintf('%04d-%02d-01', $year, intdiv((int)date('n') - 1, 3) * 3 + 1);
  if($periodStart >= $currentQuarterStart) throw new RuntimeException('Bonus hanya dapat diselesaikan untuk triwulan yang sudah berakhir.');
  $pdo = getPDO();
  $periodEnd = date('Y-m-d', strtotime($periodStart.' +3 months'));
  $pdo->beginTransaction();
  try{
    $pendingOrdersSt = $pdo->prepare("SELECT o.id FROM orders o
      JOIN order_items oi ON oi.order_id=o.id
      WHERE o.payment_status='pending' AND o.created_at>=? AND o.created_at<? AND oi.seller_id IS NOT NULL
      LIMIT 1 FOR UPDATE");
    $pendingOrdersSt->execute([$periodStart, $periodEnd]);
    if($pendingOrdersSt->fetch()) throw new RuntimeException('Selesaikan atau batalkan semua pesanan seller yang masih pending pada triwulan ini sebelum settlement.');
    $period = $pdo->prepare("INSERT IGNORE INTO seller_bonus_periods (quarter_start) VALUES (?)");
    $period->execute([$periodStart]);
    if($period->rowCount() === 0){ $pdo->commit(); return false; }
    $ranking = $pdo->prepare("SELECT s.id,
      (SELECT COUNT(*) FROM products p WHERE p.seller_id=s.id AND p.created_at>=? AND p.created_at<?) AS products_created,
      (SELECT COALESCE(SUM(oi.price*oi.qty),0) FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE oi.seller_id=s.id AND o.payment_status='paid' AND o.created_at>=? AND o.created_at<?) AS sales_total
      FROM sellers s WHERE s.is_active=1 ORDER BY sales_total DESC, products_created DESC, s.name ASC");
    $ranking->execute([$periodStart, $periodEnd, $periodStart, $periodEnd]);
    $awardPoints = [500, 300, 150];
    $eligible = array_values(array_filter($ranking->fetchAll(), function($seller){ return (int)$seller['sales_total'] > 0 || (int)$seller['products_created'] > 0; }));
    foreach(array_slice($eligible, 0, 3) as $index => $seller){
      $points = $awardPoints[$index] ?? 0;
      if($points < 1) continue;
      $pdo->prepare("INSERT INTO seller_bonus_awards (seller_id, quarter_start, points) VALUES (?,?,?)")->execute([(int)$seller['id'], $periodStart, $points]);
      $pdo->prepare("UPDATE sellers SET bonus_points_balance=bonus_points_balance+? WHERE id=?")->execute([$points, (int)$seller['id']]);
    }
    $pdo->commit();
    return true;
  }catch(Throwable $e){
    if($pdo->inTransaction()) $pdo->rollBack();
    throw $e;
  }
}

function getSellerBonusAwards($limit = 50){
  ensureCommunitySchema();
  return getPDO()->query("SELECT a.quarter_start, a.points, a.created_at, s.name AS seller_name
    FROM seller_bonus_awards a JOIN sellers s ON s.id=a.seller_id ORDER BY a.quarter_start DESC, a.points DESC LIMIT ".(int)$limit)->fetchAll();
}

function getSchoolEvents($activeOnly = true){
  ensureCommunitySchema();
  $where = $activeOnly ? 'WHERE is_active=1 OR ends_at<=NOW()' : '';
  return getPDO()->query("SELECT * FROM school_events $where ORDER BY ends_at DESC")->fetchAll();
}

function addSchoolEvent($data){
  ensureCommunitySchema();
  if(trim($data['name'] ?? '') === '') throw new RuntimeException('Nama event wajib diisi.');
  if(strtotime($data['ends_at'] ?? '') <= strtotime($data['starts_at'] ?? '')) throw new RuntimeException('Waktu akhir event harus setelah waktu mulai.');
  $start = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $data['starts_at'])));
  $end = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $data['ends_at'])));
  $st = getPDO()->prepare("INSERT INTO school_events (name, description, starts_at, ends_at, top_prize, is_active) VALUES (?,?,?,?,?,1)");
  $st->execute([trim($data['name']), trim($data['description'] ?? ''), $start, $end, trim($data['top_prize'] ?? '')]);
}

function submitSchoolMission($data, $customerId, $customerName){
  ensureCommunitySchema();
  if(trim($data['school_name'] ?? '') === '' || trim($data['team_name'] ?? '') === '' || trim($data['mission_description'] ?? '') === '') throw new RuntimeException('Sekolah, tim, dan deskripsi misi wajib diisi.');
  $pdo = getPDO();
  $pdo->beginTransaction();
  try{
    $eventSt = $pdo->prepare("SELECT id FROM school_events WHERE id=? AND is_active=1 AND is_finalized=0 AND starts_at<=NOW() AND ends_at>NOW() FOR UPDATE");
    $eventSt->execute([(int)($data['event_id'] ?? 0)]);
    if(!$eventSt->fetchColumn()) throw new RuntimeException('Event tidak sedang berlangsung.');
    $st = $pdo->prepare("INSERT INTO school_submissions (event_id, customer_id, customer_name, school_name, team_name, mission_description) VALUES (?,?,?,?,?,?)");
    $st->execute([(int)$data['event_id'], (int)$customerId, $customerName, trim($data['school_name'] ?? ''), trim($data['team_name'] ?? ''), trim($data['mission_description'] ?? '')]);
    $pdo->commit();
  }catch(Throwable $e){
    if($pdo->inTransaction()) $pdo->rollBack();
    throw $e;
  }
}

function getSchoolLeaderboard($eventId){
  ensureCommunitySchema();
  $st = getPDO()->prepare("SELECT school_name, team_name, score FROM school_scores WHERE event_id=? ORDER BY score DESC, school_name ASC LIMIT 20");
  $st->execute([(int)$eventId]);
  return $st->fetchAll();
}

function getSchoolAwards($eventId){
  ensureCommunitySchema();
  $st = getPDO()->prepare("SELECT * FROM school_awards WHERE event_id=? ORDER BY rank_position ASC");
  $st->execute([(int)$eventId]);
  return $st->fetchAll();
}

function finalizeSchoolEvent($eventId){
  ensureCommunitySchema();
  $pdo = getPDO();
  $pdo->beginTransaction();
  try{
    $eventSt = $pdo->prepare("SELECT * FROM school_events WHERE id=? FOR UPDATE");
    $eventSt->execute([(int)$eventId]);
    $event = $eventSt->fetch();
    if(!$event) throw new RuntimeException('Event sekolah tidak ditemukan.');
    if(!empty($event['is_finalized'])){ $pdo->commit(); return; }
    if(date('Y-m-d H:i:s') < $event['ends_at']) throw new RuntimeException('Event hanya dapat ditutup setelah waktu berakhir.');
    $pendingSt = $pdo->prepare("SELECT COUNT(*) FROM school_submissions WHERE event_id=? AND status='pending'");
    $pendingSt->execute([(int)$eventId]);
    if((int)$pendingSt->fetchColumn() > 0) throw new RuntimeException('Moderasi semua misi yang menunggu sebelum menutup event.');
    $scoreSt = $pdo->prepare("SELECT school_name, team_name, score FROM school_scores WHERE event_id=? ORDER BY score DESC, school_name ASC, team_name ASC LIMIT 3");
    $scoreSt->execute([(int)$eventId]);
    $insert = $pdo->prepare("INSERT INTO school_awards (event_id, rank_position, school_name, team_name, prize) VALUES (?,?,?,?,?)");
    foreach($scoreSt->fetchAll() as $index=>$winner){
      $insert->execute([(int)$eventId, $index+1, $winner['school_name'], $winner['team_name'], $event['top_prize']]);
    }
    $pdo->prepare("UPDATE school_events SET is_active=0, is_finalized=1 WHERE id=?")->execute([(int)$eventId]);
    $pdo->commit();
  }catch(Throwable $e){
    if($pdo->inTransaction()) $pdo->rollBack();
    throw $e;
  }
}

function markSchoolAwardDelivered($awardId){
  ensureCommunitySchema();
  $st = getPDO()->prepare("UPDATE school_awards SET award_status='awarded', awarded_at=NOW() WHERE id=? AND award_status='pending'");
  $st->execute([(int)$awardId]);
  if($st->rowCount() < 1) throw new RuntimeException('Hadiah sudah dicatat atau tidak ditemukan.');
}

function saveSchoolScore($eventId, $schoolName, $teamName, $score){
  ensureCommunitySchema();
  $pdo = getPDO();
  $pdo->beginTransaction();
  try{
    $eventSt = $pdo->prepare("SELECT is_finalized FROM school_events WHERE id=? FOR UPDATE");
    $eventSt->execute([(int)$eventId]);
    $isFinalized = $eventSt->fetchColumn();
    if($isFinalized === false) throw new RuntimeException('Event sekolah tidak ditemukan.');
    if((int)$isFinalized === 1) throw new RuntimeException('Skor tidak dapat diubah setelah podium dikunci.');
    $st = $pdo->prepare("INSERT INTO school_scores (event_id, school_name, team_name, score) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE score=VALUES(score)");
    $st->execute([(int)$eventId, trim($schoolName), trim($teamName), max(0, (int)$score)]);
    $pdo->commit();
  }catch(Throwable $e){
    if($pdo->inTransaction()) $pdo->rollBack();
    throw $e;
  }
}

function incrementSchoolScore($eventId, $schoolName, $teamName, $score){
  $st = getPDO()->prepare("INSERT INTO school_scores (event_id, school_name, team_name, score) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE score=score+VALUES(score)");
  $st->execute([(int)$eventId, trim($schoolName), trim($teamName), max(0, (int)$score)]);
}

function setCommunityRecordActive($table, $id, $active){
  ensureCommunitySchema();
  $tables = ['diy_resources'=>'diy_resources', 'auctions'=>'auctions', 'school_events'=>'school_events'];
  if(!isset($tables[$table])) throw new RuntimeException('Jenis data tidak valid.');
  $st = getPDO()->prepare("UPDATE {$tables[$table]} SET is_active=? WHERE id=?");
  $st->execute([$active ? 1 : 0, (int)$id]);
}

function getPendingCommunitySubmissions(){
  ensureCommunitySchema();
  return getPDO()->query("SELECT * FROM diy_submissions WHERE status='pending' ORDER BY created_at ASC")->fetchAll();
}

function getPendingSchoolSubmissions(){
  ensureCommunitySchema();
  return getPDO()->query("SELECT ss.*, se.name AS event_name FROM school_submissions ss JOIN school_events se ON se.id=ss.event_id WHERE ss.status='pending' ORDER BY ss.created_at ASC")->fetchAll();
}

function reviewDiySubmission($submissionId, $status, $points = 0){
  ensureCommunitySchema();
  if(!in_array($status, ['approved','rejected'], true)) throw new RuntimeException('Status moderasi tidak valid.');
  $pdo = getPDO();
  $pdo->beginTransaction();
  try{
    $st = $pdo->prepare("SELECT * FROM diy_submissions WHERE id=? FOR UPDATE");
    $st->execute([(int)$submissionId]);
    $submission = $st->fetch();
    if(!$submission || $submission['status'] !== 'pending') throw new RuntimeException('Karya ini sudah dimoderasi atau tidak ditemukan.');
    $award = $status === 'approved' ? max(0, (int)$points) : 0;
    $pdo->prepare("UPDATE diy_submissions SET status=?, points_awarded=? WHERE id=?")->execute([$status, $award, (int)$submissionId]);
    if($award > 0){
      $customerSt = $pdo->prepare("SELECT points_balance, voucher_count FROM customers WHERE id=? FOR UPDATE");
      $customerSt->execute([(int)$submission['customer_id']]);
      $customer = $customerSt->fetch();
      if($customer){
        $total = (int)$customer['points_balance'] + $award;
        $pdo->prepare("UPDATE customers SET points_balance=?, voucher_count=? WHERE id=?")->execute([$total % 5, (int)$customer['voucher_count'] + intdiv($total, 5), (int)$submission['customer_id']]);
      }
    }
    $pdo->commit();
  }catch(Throwable $e){
    if($pdo->inTransaction()) $pdo->rollBack();
    throw $e;
  }
}

function reviewSchoolSubmission($submissionId, $approved, $score){
  ensureCommunitySchema();
  $pdo = getPDO();
  $pdo->beginTransaction();
  try{
    $st = $pdo->prepare("SELECT * FROM school_submissions WHERE id=? FOR UPDATE");
    $st->execute([(int)$submissionId]);
    $submission = $st->fetch();
    if(!$submission || $submission['status'] !== 'pending') throw new RuntimeException('Misi ini sudah dimoderasi atau tidak ditemukan.');
    $eventSt = $pdo->prepare("SELECT is_finalized FROM school_events WHERE id=? FOR UPDATE");
    $eventSt->execute([(int)$submission['event_id']]);
    if((int)$eventSt->fetchColumn() === 1) throw new RuntimeException('Event sekolah sudah difinalisasi.');
    $status = $approved ? 'approved' : 'rejected';
    $pdo->prepare("UPDATE school_submissions SET status=? WHERE id=?")->execute([$status, (int)$submissionId]);
    if($approved) incrementSchoolScore($submission['event_id'], $submission['school_name'], $submission['team_name'], max(0, (int)$score));
    $pdo->commit();
  }catch(Throwable $e){
    if($pdo->inTransaction()) $pdo->rollBack();
    throw $e;
  }
}

function submitDiyVideo($file, $data, $customerId, $customerName){
  ensureCommunitySchema();
  if(trim($data['title'] ?? '') === '') throw new RuntimeException('Judul karya wajib diisi.');
  $uploadError = $file['error'] ?? UPLOAD_ERR_NO_FILE;
  if($uploadError !== UPLOAD_ERR_OK){
    $limitText = formatFileSize(getEffectiveVideoUploadLimit());
    $messages = [
      UPLOAD_ERR_INI_SIZE => 'Video melebihi batas PHP upload_max_filesize. Batas efektif server saat ini '.$limitText.'.',
      UPLOAD_ERR_FORM_SIZE => 'Video melebihi batas ukuran form.',
      UPLOAD_ERR_PARTIAL => 'Video hanya terunggah sebagian. Coba unggah ulang.',
      UPLOAD_ERR_NO_FILE => 'Pilih video yang akan diunggah.',
      UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara upload PHP tidak tersedia.',
      UPLOAD_ERR_CANT_WRITE => 'Server gagal menulis file video ke penyimpanan.',
      UPLOAD_ERR_EXTENSION => 'Upload video dihentikan oleh konfigurasi ekstensi PHP.',
    ];
    throw new RuntimeException($messages[$uploadError] ?? 'Upload video gagal (kode '.$uploadError.').');
  }
  if((int)$file['size'] > 50 * 1024 * 1024) throw new RuntimeException('Ukuran video maksimal 50 MB.');
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $finfo->file($file['tmp_name']);
  $allowed = ['video/mp4'=>'mp4', 'video/webm'=>'webm', 'video/quicktime'=>'mov'];
  if(!isset($allowed[$mime])) throw new RuntimeException('Format video harus MP4, WebM, atau MOV.');
  $directory = __DIR__.'/uploads/creations';
  if(!is_dir($directory) && !mkdir($directory, 0755, true)) throw new RuntimeException('Folder unggahan tidak dapat dibuat.');
  $filename = bin2hex(random_bytes(16)).'.'.$allowed[$mime];
  if(!move_uploaded_file($file['tmp_name'], $directory.'/'.$filename)) throw new RuntimeException('Video gagal disimpan.');
  $st = getPDO()->prepare("INSERT INTO diy_submissions (customer_id, customer_name, school_name, title, description, video_path) VALUES (?,?,?,?,?,?)");
  $st->execute([(int)$customerId, $customerName, trim($data['school_name'] ?? ''), trim($data['title'] ?? ''), trim($data['description'] ?? ''), 'uploads/creations/'.$filename]);
}

function parseIniSizeBytes($value){
  $value = trim((string)$value);
  if($value === '' || $value === '-1') return PHP_INT_MAX;
  $unit = strtolower(substr($value, -1));
  $number = (float)$value;
  if($unit === 'g') $number *= 1024 * 1024 * 1024;
  elseif($unit === 'm') $number *= 1024 * 1024;
  elseif($unit === 'k') $number *= 1024;
  return (int)$number;
}

function getEffectiveVideoUploadLimit(){
  $uploadLimit = parseIniSizeBytes(ini_get('upload_max_filesize'));
  $postLimit = parseIniSizeBytes(ini_get('post_max_size'));
  $requestLimit = $postLimit === PHP_INT_MAX ? $uploadLimit : max(0, $postLimit - 1024 * 1024);
  return min(50 * 1024 * 1024, $uploadLimit, $requestLimit);
}

function formatFileSize($bytes){
  if($bytes >= 1024 * 1024) return rtrim(rtrim(number_format($bytes / (1024 * 1024), 1, '.', ''), '0'), '.').' MB';
  if($bytes >= 1024) return rtrim(rtrim(number_format($bytes / 1024, 1, '.', ''), '0'), '.').' KB';
  return (int)$bytes.' byte';
}

function ensureVoucherRewardsSchema(){
  $pdo = getPDO();
  $pdo->exec("CREATE TABLE IF NOT EXISTS voucher_rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reward_key VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    voucher_cost INT NOT NULL DEFAULT 1,
    voucher_stock INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB");
  try{ $pdo->exec("ALTER TABLE voucher_rewards ADD COLUMN voucher_stock INT NOT NULL DEFAULT 0"); }catch(Throwable $e){}
}

function ensureVoucherRedemptionsSchema(){
  ensureCustomersSchema();
  $pdo = getPDO();
  $pdo->exec("CREATE TABLE IF NOT EXISTS voucher_redemptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    reward_key VARCHAR(100) NOT NULL,
    reward_name VARCHAR(255) NOT NULL,
    voucher_cost INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_voucher_redemptions_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
  ) ENGINE=InnoDB");
}

function seedDefaultVoucherRewards(){
  ensureVoucherRewardsSchema();
  $pdo = getPDO();
  $count = (int)$pdo->query('SELECT COUNT(*) FROM voucher_rewards')->fetchColumn();
  if($count > 0){
    return;
  }
  $defaults = [
    ['reward_key' => 'eco_tote', 'name' => 'Eco Tote Bag', 'description' => 'Tas belanja kain reusable yang ramah lingkungan.', 'voucher_cost' => 1, 'voucher_stock' => 10],
    ['reward_key' => 'reusable_straws', 'name' => 'Reusable Straw Set', 'description' => 'Satu paket sedotan stainless untuk kurangi plastik sekali pakai.', 'voucher_cost' => 1, 'voucher_stock' => 10],
    ['reward_key' => 'seed_bookmark', 'name' => 'Seed Paper Bookmark', 'description' => 'Pembatas buku yang dapat ditanam jadi bunga.', 'voucher_cost' => 1, 'voucher_stock' => 10],
  ];
  $st = $pdo->prepare('INSERT INTO voucher_rewards (reward_key, name, description, voucher_cost, voucher_stock) VALUES (?,?,?,?,?)');
  foreach($defaults as $reward){
    $st->execute([$reward['reward_key'], $reward['name'], $reward['description'], $reward['voucher_cost'], $reward['voucher_stock']]);
  }
}

function getVoucherRewards($activeOnly = true){
  ensureVoucherRewardsSchema();
  seedDefaultVoucherRewards();
  $pdo = getPDO();
  if($activeOnly){
    $st = $pdo->prepare('SELECT * FROM voucher_rewards WHERE is_active = 1 ORDER BY id ASC');
    $st->execute();
  } else {
    $st = $pdo->query('SELECT * FROM voucher_rewards ORDER BY id ASC');
  }
  $rows = $st->fetchAll();
  $rewards = [];
  foreach($rows as $row){
    $rewards[$row['reward_key']] = [
      'id' => (int)$row['id'],
      'reward_key' => $row['reward_key'],
      'name' => $row['name'],
      'description' => $row['description'],
      'cost' => (int)$row['voucher_cost'],
      'stock' => (int)$row['voucher_stock'],
      'is_active' => (bool)$row['is_active'],
    ];
  }
  return $rewards;
}

function getVoucherRewardByKey($rewardKey){
  ensureVoucherRewardsSchema();
  $pdo = getPDO();
  $st = $pdo->prepare('SELECT * FROM voucher_rewards WHERE reward_key = ? LIMIT 1');
  $st->execute([trim($rewardKey)]);
  return $st->fetch();
}

function getVoucherRewardById($id){
  ensureVoucherRewardsSchema();
  $pdo = getPDO();
  $st = $pdo->prepare('SELECT * FROM voucher_rewards WHERE id = ? LIMIT 1');
  $st->execute([(int)$id]);
  return $st->fetch();
}

function getAllVoucherRewards($limit = 100){
  ensureVoucherRewardsSchema();
  $pdo = getPDO();
  return $pdo->query('SELECT * FROM voucher_rewards ORDER BY id DESC LIMIT '.(int)$limit)->fetchAll();
}

function addVoucherReward($data){
  ensureVoucherRewardsSchema();
  $pdo = getPDO();
  $key = trim($data['reward_key'] ?? '');
  if($key === ''){ throw new RuntimeException('Kode reward wajib diisi.'); }
  $st = $pdo->prepare('INSERT INTO voucher_rewards (reward_key, name, description, voucher_cost, voucher_stock, is_active) VALUES (?,?,?,?,?,?)');
  $st->execute([
    $key,
    trim($data['name'] ?? ''),
    trim($data['description'] ?? ''),
    max(1, (int)($data['voucher_cost'] ?? 1)),
    max(0, (int)($data['voucher_stock'] ?? 0)),
    !empty($data['is_active']) ? 1 : 0,
  ]);
  return $pdo->lastInsertId();
}

function updateVoucherReward($id, $data){
  ensureVoucherRewardsSchema();
  $pdo = getPDO();
  $st = $pdo->prepare('UPDATE voucher_rewards SET reward_key = ?, name = ?, description = ?, voucher_cost = ?, voucher_stock = ?, is_active = ? WHERE id = ?');
  $st->execute([
    trim($data['reward_key'] ?? ''),
    trim($data['name'] ?? ''),
    trim($data['description'] ?? ''),
    max(1, (int)($data['voucher_cost'] ?? 1)),
    max(0, (int)($data['voucher_stock'] ?? 0)),
    !empty($data['is_active']) ? 1 : 0,
    (int)$id,
  ]);
}

function deleteVoucherReward($id){
  ensureVoucherRewardsSchema();
  $pdo = getPDO();
  $st = $pdo->prepare('DELETE FROM voucher_rewards WHERE id = ?');
  $st->execute([(int)$id]);
}

function addRewardItemToCart($reward){
  if(session_status() === PHP_SESSION_NONE){ session_start(); }
  if(!isset($_SESSION['cart'])){ $_SESSION['cart'] = []; }

  $rewardKey = trim((string)($reward['reward_key'] ?? ''));
  if($rewardKey === ''){ return; }

  foreach($_SESSION['cart'] as $key => $item){
    if(($item['type'] ?? '') === 'reward' && (($item['reward_key'] ?? '') === $rewardKey)){
      $_SESSION['cart'][$key]['qty'] = (int)($_SESSION['cart'][$key]['qty'] ?? 1) + 1;
      return;
    }
  }

  $_SESSION['cart'][] = [
    'id' => 0,
    'type' => 'reward',
    'reward_key' => $rewardKey,
    'name' => $reward['name'] ?? 'Reward',
    'description' => $reward['description'] ?? 'Reward yang diklaim',
    'price' => 0,
    'qty' => 1,
    'image' => null,
    'size' => 'Reward',
    'seller_name' => null,
    'is_reward' => true,
  ];
}

function getCustomerVoucherRedemptions($customerId, $limit = 20){
  ensureVoucherRedemptionsSchema();
  $pdo = getPDO();
  $st = $pdo->prepare("SELECT * FROM voucher_redemptions WHERE customer_id = ? ORDER BY id DESC LIMIT ?");
  $st->bindValue(1, (int)$customerId, PDO::PARAM_INT);
  $st->bindValue(2, (int)$limit, PDO::PARAM_INT);
  $st->execute();
  return $st->fetchAll();
}

function redeemCustomerVoucher($customerId, $rewardKey){
  ensureVoucherRedemptionsSchema();
  $reward = getVoucherRewardByKey($rewardKey);
  if(!$reward || !$reward['is_active']){
    throw new RuntimeException('Hadiah pilihan tidak valid atau tidak aktif.');
  }
  $customer = getCustomerById($customerId);
  if(!$customer){
    throw new RuntimeException('Pelanggan tidak ditemukan.');
  }
  if($customer['voucher_count'] < (int)$reward['voucher_cost']){
    throw new RuntimeException('Voucher pelanggan tidak cukup untuk penukaran ini.');
  }
  if((int)$reward['voucher_stock'] <= 0){
    throw new RuntimeException('Stock reward sedang habis. Silakan pilih reward lain.');
  }
  $pdo = getPDO();
  $st = $pdo->prepare("UPDATE customers SET voucher_count = voucher_count - ? WHERE id = ?");
  $st->execute([(int)$reward['voucher_cost'], (int)$customerId]);
  $st = $pdo->prepare("UPDATE voucher_rewards SET voucher_stock = voucher_stock - 1 WHERE id = ?");
  $st->execute([(int)$reward['id']]);
  $st = $pdo->prepare("INSERT INTO voucher_redemptions (customer_id, reward_key, reward_name, voucher_cost) VALUES (?,?,?,?)");
  $st->execute([$customerId, $reward['reward_key'], $reward['name'], (int)$reward['voucher_cost']]);
  addRewardItemToCart($reward);
  $customer = getCustomerById($customerId);
  $customer['reward_name'] = $reward['name'];
  return $customer;
}

function getCustomerByPhone($phone){
  ensureCustomersSchema();
  $pdo = getPDO();
  $st = $pdo->prepare("SELECT * FROM customers WHERE phone = ? LIMIT 1");
  $st->execute([trim($phone)]);
  return $st->fetch();
}

function getCustomerById($id){
  ensureCustomersSchema();
  $pdo = getPDO();
  $st = $pdo->prepare("SELECT * FROM customers WHERE id = ? LIMIT 1");
  $st->execute([(int)$id]);
  return $st->fetch();
}

function getCustomers(){
  ensureCustomersSchema();
  $pdo = getPDO();
  return $pdo->query("SELECT * FROM customers ORDER BY id DESC")->fetchAll();
}

function updateCustomerById($id, $data){
  ensureCustomersSchema();
  $pdo = getPDO();
  $st = $pdo->prepare("UPDATE customers SET name=?, address=?, city=?, province=?, postal_code=? WHERE id=?");
  $st->execute([
    trim($data['name'] ?? ''),
    trim($data['address'] ?? ''),
    trim($data['city'] ?? ''),
    trim($data['province'] ?? ''),
    trim($data['postal_code'] ?? ''),
    (int)$id,
  ]);
  return getCustomerById($id);
}

function upsertCustomerPoints($data, $pointsEarned, $customerId = null){
  ensureCustomersSchema();
  $pdo = getPDO();
  $phone = trim($data['phone'] ?? '');
  if($phone === ''){ throw new RuntimeException('Nomor telepon customer wajib diisi untuk sistem poin.'); }
  if($customerId === null && !empty($_SESSION['customer_id'])){
    $customerId = (int)$_SESSION['customer_id'];
  }
  $customer = null;
  if($customerId){
    $customer = getCustomerById($customerId);
    if(!$customer){
      $customer = getCustomerByPhone($phone);
    }
  } else {
    $customer = getCustomerByPhone($phone);
  }
  $currentPoints = (int)($customer['points_balance'] ?? 0);
  $currentVouchers = (int)($customer['voucher_count'] ?? 0);
  $pointsEarned = max(0, (int)$pointsEarned);
  $newPointsTotal = $currentPoints + $pointsEarned;
  $earnedVouchers = intdiv($newPointsTotal, 5);
  $updatedPoints = $newPointsTotal % 5;
  $updatedVouchers = $currentVouchers + $earnedVouchers;
  $now = date('Y-m-d H:i:s');

  if($customer){
    $st = $pdo->prepare("UPDATE customers SET name=?, phone=?, address=?, city=?, province=?, postal_code=?, points_balance=?, voucher_count=?, last_order_at=? WHERE id=?");
    $st->execute([
      trim($data['name'] ?? ''),
      $phone,
      trim($data['address'] ?? ''),
      trim($data['city'] ?? ''),
      trim($data['province'] ?? ''),
      trim($data['postal_code'] ?? ''),
      $updatedPoints,
      $updatedVouchers,
      $now,
      $customer['id'],
    ]);
    $customer = getCustomerById($customer['id']);
    $customer['points_earned'] = $pointsEarned;
    $customer['voucher_awarded'] = $earnedVouchers;
    return $customer;
  }

  $st = $pdo->prepare("INSERT INTO customers (name, phone, address, city, province, postal_code, points_balance, voucher_count, last_order_at) VALUES (?,?,?,?,?,?,?,?,?)");
  $st->execute([
    trim($data['name'] ?? ''),
    $phone,
    trim($data['address'] ?? ''),
    trim($data['city'] ?? ''),
    trim($data['province'] ?? ''),
    trim($data['postal_code'] ?? ''),
    $updatedPoints,
    $updatedVouchers,
    $now,
  ]);
  $customer = getCustomerById((int)$pdo->lastInsertId());
  $customer['points_earned'] = $pointsEarned;
  $customer['voucher_awarded'] = $earnedVouchers;
  return $customer;
}

function ensureDemoSeller(){
  $pdo = getPDO();
  $existing = $pdo->prepare("SELECT id, password_hash FROM sellers WHERE username = ? LIMIT 1");
  $existing->execute(['sellerdemo']);
  $row = $existing->fetch();

  if($row){
    if(empty($row['password_hash'])){
      $pdo->prepare("UPDATE sellers SET password_hash = ? WHERE id = ?")
        ->execute([password_hash('seller123', PASSWORD_DEFAULT), (int)$row['id']]);
    }
    return;
  }

  $username = 'sellerdemo';
  $password = 'seller123';
  $pdo->prepare("INSERT INTO sellers (name, owner_name, phone, email, address, username, password_hash, is_active) VALUES (?,?,?,?,?,?,?,?)")
    ->execute([
      'Seller Demo',
      'Demo Owner',
      '081234567890',
      'sellerdemo@example.com',
      'Cibubur',
      $username,
      password_hash($password, PASSWORD_DEFAULT),
      1,
    ]);
}

function getProducts($sellerId = null, $searchQuery = null){
  ensureProductsSchema();
  ensureSellersSchema();
  $pdo = getPDO();

  $where = [];
  $params = [];

  if($sellerId !== null && $sellerId !== ''){
    $where[] = 'p.seller_id = ?';
    $params[] = (int)$sellerId;
  }

  $searchTerm = trim((string)$searchQuery);
  if($searchTerm !== ''){
    $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $searchTerm);
    $pattern = '%' . strtolower($escaped) . '%';
    $where[] = '(LOWER(p.name) LIKE ? OR LOWER(p.description) LIKE ? OR LOWER(s.name) LIKE ?)';
    $params[] = $pattern;
    $params[] = $pattern;
    $params[] = $pattern;
  }

  $sql = 'SELECT p.*, s.name AS seller_name FROM products p LEFT JOIN sellers s ON s.id = p.seller_id';
  if($where){
    $sql .= ' WHERE ' . implode(' AND ', $where);
  }
  $sql .= ' ORDER BY p.id DESC';

  $st = $pdo->prepare($sql);
  $st->execute($params);
  return $st->fetchAll();
}
function getProduct($id){
  ensureProductsSchema();
  ensureSellersSchema();
  $pdo = getPDO();
  $st = $pdo->prepare("SELECT p.*, s.name AS seller_name FROM products p LEFT JOIN sellers s ON s.id = p.seller_id WHERE p.id=?");
  $st->execute([$id]);
  return $st->fetch();
}
function getSellers(){
  ensureSellersSchema();
  $pdo = getPDO();
  return $pdo->query("SELECT * FROM sellers ORDER BY name ASC")->fetchAll();
}
function getSeller($id){
  ensureSellersSchema();
  $pdo = getPDO();
  $st = $pdo->prepare("SELECT * FROM sellers WHERE id=?");
  $st->execute([$id]);
  return $st->fetch();
}
function getSellerByUsername($username){
  ensureSellersSchema();
  $pdo = getPDO();
  $st = $pdo->prepare("SELECT * FROM sellers WHERE username=? LIMIT 1");
  $st->execute([trim($username)]);
  return $st->fetch();
}
function addSeller($data){
  ensureSellersSchema();
  $pdo = getPDO();
  $username = trim($data['username'] ?? '');
  $password = trim($data['password'] ?? '');
  if($username === ''){ throw new RuntimeException('Username seller wajib diisi.'); }
  if($password === ''){ throw new RuntimeException('Password seller wajib diisi.'); }
  $passwordHash = password_hash($password, PASSWORD_DEFAULT);
  $st = $pdo->prepare("INSERT INTO sellers (name, owner_name, phone, email, address, username, password_hash, is_active) VALUES (?,?,?,?,?,?,?,?)");
  try{
    $st->execute([
      trim($data['name'] ?? ''),
      trim($data['owner_name'] ?? ''),
      trim($data['phone'] ?? ''),
      trim($data['email'] ?? ''),
      trim($data['address'] ?? ''),
      $username,
      $passwordHash,
      !empty($data['is_active']) ? 1 : 0,
    ]);
  } catch(Throwable $e){
    if(stripos($e->getMessage(), 'column') !== false || stripos($e->getMessage(), 'doesn\'t exist') !== false){
      ensureSellerColumns();
      $st = $pdo->prepare("INSERT INTO sellers (name, owner_name, phone, email, address, username, password_hash, is_active) VALUES (?,?,?,?,?,?,?,?)");
      $st->execute([
        trim($data['name'] ?? ''),
        trim($data['owner_name'] ?? ''),
        trim($data['phone'] ?? ''),
        trim($data['email'] ?? ''),
        trim($data['address'] ?? ''),
        $username,
        $passwordHash,
        !empty($data['is_active']) ? 1 : 0,
      ]);
    } else {
      throw $e;
    }
  }
  return $pdo->lastInsertId();
}
function updateSeller($id, $data){
  ensureSellersSchema();
  $pdo = getPDO();
  $username = trim($data['username'] ?? '');
  if($username === ''){ throw new RuntimeException('Username seller wajib diisi.'); }
  $password = trim($data['password'] ?? '');
  if($password !== ''){
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $st = $pdo->prepare("UPDATE sellers SET name=?, owner_name=?, phone=?, email=?, address=?, username=?, password_hash=?, is_active=? WHERE id=?");
    $st->execute([
      trim($data['name'] ?? ''),
      trim($data['owner_name'] ?? ''),
      trim($data['phone'] ?? ''),
      trim($data['email'] ?? ''),
      trim($data['address'] ?? ''),
      $username,
      $passwordHash,
      !empty($data['is_active']) ? 1 : 0,
      $id,
    ]);
  }else{
    $st = $pdo->prepare("UPDATE sellers SET name=?, owner_name=?, phone=?, email=?, address=?, username=?, is_active=? WHERE id=?");
    $st->execute([
      trim($data['name'] ?? ''),
      trim($data['owner_name'] ?? ''),
      trim($data['phone'] ?? ''),
      trim($data['email'] ?? ''),
      trim($data['address'] ?? ''),
      $username,
      !empty($data['is_active']) ? 1 : 0,
      $id,
    ]);
  }
}
function deleteSeller($id){
  ensureSellersSchema();
  $pdo = getPDO();
  $pdo->prepare("UPDATE products SET seller_id = NULL WHERE seller_id=?")->execute([$id]);
  $pdo->prepare("DELETE FROM sellers WHERE id=?")->execute([$id]);
}
function calculatePointsFromSubtotal($subtotal){
  $subtotal = max(0, (int)$subtotal);
  return (int) floor($subtotal / 10000);
}

function calculateServiceFee($subtotal){
  return (int) round(max(0, (int)$subtotal) * 0.05);
}

function createOrder($orderData, $items, $schemaReady = false){
  if(!$schemaReady) ensureOrderSchema();
  $pdo = getPDO();
  $st = $pdo->prepare("INSERT INTO orders (order_no, customer_id, customer_name, customer_phone, customer_address, customer_city, customer_province, customer_postal_code, customer_notes, courier, service, subtotal, service_fee, shipping_cost, total, payment_status, auction_id, points_earned, voucher_awarded) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
  $st->execute([
    $orderData['order_no'],
    $orderData['customer_id'] ?? null,
    $orderData['customer_name'],
    $orderData['customer_phone'],
    $orderData['customer_address'],
    $orderData['customer_city'],
    $orderData['customer_province'],
    $orderData['customer_postal_code'],
    $orderData['customer_notes'] ?? '',
    $orderData['courier'] ?? null,
    $orderData['service'] ?? null,
    (int)$orderData['subtotal'],
    (int)($orderData['service_fee'] ?? 0),
    (int)$orderData['shipping_cost'],
    (int)$orderData['total'],
    $orderData['payment_status'] ?? 'pending',
    $orderData['auction_id'] ?? null,
    (int)$orderData['points_earned'],
    (int)$orderData['voucher_awarded'],
  ]);
  $orderId = (int)$pdo->lastInsertId();

  $itemSt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, size, price, qty, seller_id, seller_name) VALUES (?,?,?,?,?,?,?,?)");
  foreach($items as $item){
    $itemSt->execute([
      $orderId,
      $item['id'] ?? null,
      $item['name'] ?? '',
      $item['size'] ?? '',
      (int)($item['price'] ?? 0),
      (int)($item['qty'] ?? 1),
      isset($item['seller_id']) ? (int)$item['seller_id'] : null,
      $item['seller_name'] ?? null,
    ]);
  }

  return $orderId;
}

function getAllOrders($limit = 200){
  ensureOrderSchema();
  return getPDO()->query("SELECT id, order_no, customer_id, customer_name, customer_phone, subtotal, service_fee, shipping_cost, total, payment_status, points_earned, created_at FROM orders ORDER BY created_at DESC LIMIT ".(int)$limit)->fetchAll();
}

function getCustomerOrders($customerId, $limit = 20){
  ensureOrderSchema();
  $st = getPDO()->prepare("SELECT order_no, total, payment_status, points_earned, created_at FROM orders WHERE customer_id=? ORDER BY created_at DESC LIMIT ?");
  $st->bindValue(1, (int)$customerId, PDO::PARAM_INT);
  $st->bindValue(2, (int)$limit, PDO::PARAM_INT);
  $st->execute();
  return $st->fetchAll();
}

function updateOrderPaymentStatus($orderId, $newStatus){
  ensureOrderSchema();
  if(!in_array($newStatus, ['paid','cancelled'], true)) throw new RuntimeException('Status pembayaran tidak valid.');
  $pdo = getPDO();
  $pdo->beginTransaction();
  try{
    $st = $pdo->prepare("SELECT * FROM orders WHERE id=? FOR UPDATE");
    $st->execute([(int)$orderId]);
    $order = $st->fetch();
    if(!$order) throw new RuntimeException('Pesanan tidak ditemukan.');
    if($order['payment_status'] === $newStatus){ $pdo->commit(); return $order; }
    if($order['payment_status'] !== 'pending') throw new RuntimeException('Pesanan yang sudah diproses tidak dapat diubah.');
    $points = $newStatus === 'paid' ? calculatePointsFromSubtotal($order['subtotal']) : 0;
    $vouchers = 0;
    $customer = null;
    if($newStatus === 'paid' && !empty($order['customer_id'])){
      $customerSt = $pdo->prepare("SELECT points_balance, voucher_count FROM customers WHERE id=? FOR UPDATE");
      $customerSt->execute([(int)$order['customer_id']]);
      $customer = $customerSt->fetch();
      if($customer){
        $pointsTotal = (int)$customer['points_balance'] + $points;
        $vouchers = intdiv($pointsTotal, 5);
        $pdo->prepare("UPDATE customers SET points_balance=?, voucher_count=?, last_order_at=NOW() WHERE id=?")
          ->execute([$pointsTotal % 5, (int)$customer['voucher_count'] + $vouchers, (int)$order['customer_id']]);
      }
    }
    if($newStatus === 'paid' && !$customer) $points = 0;
    $pdo->prepare("UPDATE orders SET payment_status=?, points_earned=?, voucher_awarded=? WHERE id=?")
      ->execute([$newStatus, $points, $vouchers, (int)$orderId]);
    if(!empty($order['auction_id'])){
      $settlementStatus = $newStatus === 'paid' ? 'paid' : 'cancelled';
      $pdo->prepare("UPDATE auction_settlements SET settlement_status=? WHERE auction_id=?")
        ->execute([$settlementStatus, (int)$order['auction_id']]);
    }
    $pdo->commit();
    $order['payment_status'] = $newStatus;
    $order['points_earned'] = $points;
    $order['voucher_awarded'] = $vouchers;
    return $order;
  }catch(Throwable $e){
    if($pdo->inTransaction()) $pdo->rollBack();
    throw $e;
  }
}

function normalizeProductPrice($value){
  if(!is_scalar($value)) throw new RuntimeException('Harga produk tidak valid.');
  $digits = preg_replace('/\D+/', '', (string)$value);
  if($digits === '') throw new RuntimeException('Harga produk wajib diisi.');
  $price = (int)$digits;
  if($price > 2147483647) throw new RuntimeException('Harga maksimal Rp 2.147.483.647.');
  return $price;
}

function addProduct($data, $file){
  ensureProductsSchema();
  ensureSellersSchema();
  $pdo = getPDO();
  $price = normalizeProductPrice($data['price'] ?? '');
  $imagePath = resolveExistingProductImage($data['existing_image_path'] ?? '');
  if(isset($file['image']) && $file['image']['error']===UPLOAD_ERR_OK){
    $imagePath = handleUpload($file['image']);
  }
  if(!$imagePath) throw new RuntimeException('Foto produk wajib diunggah atau dipilih dari foto lama.');
  $sizes = isset($data['sizes']) ? implode(',', $data['sizes']) : 'All Size,S,M,L,XL';
  $sellerIdRaw = $data['seller_id'] ?? '';
  $sellerId = ($sellerIdRaw !== '' && $sellerIdRaw !== null) ? (int)$sellerIdRaw : null;
  $st = $pdo->prepare("INSERT INTO products (name, description, price, image_path, sizes, seller_id, is_best_seller) VALUES (?,?,?,?,?,?,?)");
  $st->execute([$data['name'], $data['description'], $price, $imagePath, $sizes, $sellerId, !empty($data['is_best_seller'])?1:0]);
}
function updateProduct($id, $data, $file){
  ensureProductsSchema();
  ensureSellersSchema();
  $pdo = getPDO();
  $product = getProduct($id);
  if(!$product){ throw new RuntimeException('Produk tidak ditemukan'); }
  $price = normalizeProductPrice($data['price'] ?? '');
  $imagePath = $product['image_path'];
  $selectedImage = trim($data['existing_image_path'] ?? '');
  if($selectedImage !== '') $imagePath = resolveExistingProductImage($selectedImage);
  if(isset($file['image']) && $file['image']['error']===UPLOAD_ERR_OK){
    $imagePath = handleUpload($file['image']);
  }
  $sizes = isset($data['sizes']) ? implode(',', $data['sizes']) : 'All Size,S,M,L,XL';
  $sellerIdRaw = $data['seller_id'] ?? '';
  $sellerId = ($sellerIdRaw !== '' && $sellerIdRaw !== null) ? (int)$sellerIdRaw : null;
  $st = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, image_path=?, sizes=?, seller_id=?, is_best_seller=? WHERE id=?");
  $st->execute([$data['name'], $data['description'], $price, $imagePath, $sizes, $sellerId, !empty($data['is_best_seller'])?1:0, $id]);
}
function getUploadedProductImages(){
  $paths = [];
  foreach(glob(__DIR__.'/uploads/*') ?: [] as $file){
    if(!is_file($file) || !preg_match('/\.(jpe?g|png|webp)$/i', $file)) continue;
    $paths[] = 'uploads/'.basename($file);
  }
  rsort($paths, SORT_NATURAL);
  return $paths;
}
function resolveExistingProductImage($path){
  $path = trim((string)$path);
  if($path === '') return null;
  $filename = basename($path);
  $allowed = getUploadedProductImages();
  $relativePath = 'uploads/'.$filename;
  if(!in_array($relativePath, $allowed, true) || !is_file(__DIR__.'/'.$relativePath)){
    throw new RuntimeException('Foto lama tidak ditemukan. Pilih ulang atau unggah foto baru.');
  }
  return $relativePath;
}
function deleteProduct($id){
  $pdo = getPDO();
  $st = $pdo->prepare("DELETE FROM products WHERE id=?");
  $st->execute([$id]);
}
function handleUpload($f){
  $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
  if(!isset($allowed[$f['type']])){ throw new RuntimeException('Format gambar harus JPG/PNG/WebP'); }
  if($f['size'] > 2*1024*1024){ throw new RuntimeException('Ukuran gambar maks 2MB'); }
  if(!is_dir(__DIR__.'/uploads')){ mkdir(__DIR__.'/uploads', 0777, true); }
  $ext = $allowed[$f['type']];
  $name = uniqid('img_', true).'.'.$ext;
  $dest = __DIR__.'/uploads/'.$name;
  if(!move_uploaded_file($f['tmp_name'], $dest)){ throw new RuntimeException('Gagal upload'); }
  return 'uploads/'.$name;
}
function formatRupiah($n){ return 'Rp '.number_format((int)$n,0,',','.'); }

function calcShipping($courier, $service, $province=null){
  $rates = [
    'JNE' => ['REG'=>20000, 'YES'=>35000],
    'SiCepat' => ['REG'=>18000, 'BEST'=>28000],
    'AnterAja' => ['REG'=>17000, 'NDS'=>27000],
  ];
  $courier = $courier ?: 'JNE';
  if(!isset($rates[$courier])) $courier = 'JNE';
  $service = $service ?: array_key_first($rates[$courier]);
  if(!isset($rates[$courier][$service])) $service = array_key_first($rates[$courier]);
  $base = $rates[$courier][$service];

  if($province){
    $prov = mb_strtolower(trim($province));
    if(strpos($prov, 'jakarta') !== false || strpos($prov, 'dki') !== false){
      $base = max(10000, (int)round($base * 0.8));
    }
  }
  return (int)$base;
}
?>
