<?php
include 'header.php';
$sellerFilter = isset($_GET['seller_id']) ? (int)$_GET['seller_id'] : 0;
$searchQuery = trim($_GET['q'] ?? '');
$sellers = getSellers();
$products = getProducts($sellerFilter > 0 ? $sellerFilter : null, $searchQuery);
$now = date('Y-m-d H:i:s');
$homeEvents = array_slice(array_values(array_filter(getSchoolEvents(false), function($event) use ($now){
  return !empty($event['is_active']) && $event['ends_at'] >= $now && strtolower(trim($event['name'])) !== 'testing';
})), 0, 2);
$homeEbooks = array_slice(array_values(array_filter(getDiyResources(), function($resource){
  return $resource['resource_type'] === 'ebook';
})), 0, 2);
$homeAuctions = array_slice(array_values(array_filter(getCommunityAuctions(), function($auction) use ($now){
  return $auction['ends_at'] > $now;
})), 0, 2);
?>

<!-- Terra Kala Hero -->
<section class="mb-5">
  <style>
    .store-hero { padding:1.5rem; border-radius:16px; }
    .store-hero h1 { max-width:24ch; line-height:1; }
    .store-hero .lead { max-width:38rem; font-size:1.1rem; }
    .store-hero .btn { padding:.65rem .9rem; }
    .store-feature-panel { padding:1rem; border-radius:12px; box-shadow:0 8px 22px rgba(17,41,23,.12); }
    .store-feature { display:flex; align-items:center; gap:.8rem; padding:.75rem 0; }
    .store-feature + .store-feature { border-top:1px solid rgba(34,52,38,.12); }
    .store-feature .icon { flex:0 0 40px; width:40px; height:40px; border-radius:10px; }
    .store-feature-copy { min-width:0; }
    .store-feature-copy .small { line-height:1.4; }
    @media (min-width:768px) { .store-hero { padding:2rem; } }
    @media (max-width:767.98px) { .store-hero h1 { max-width:11ch; } .store-hero .btn { flex:1 1 auto; } }
  </style>
  <div class="store-hero rounded-4 hero-gradient shadow-sm">
    <div class="row g-4 align-items-center">
      <div class="col-md-7">
        <h1 class="display-6 fw-bold mb-3">Terra Kala: Pilihan Sirkular untuk Keseharian</h1>
        <p class="lead mb-3">Temukan produk guna ulang dan hasil upcycle, pelajari cara mengurangi sampah, lalu dukung karya komunitas lokal.</p>
        <div class="d-flex gap-2 flex-wrap mb-3">
          <span class="badge pill-badge">Produk Guna Ulang</span>
          <span class="badge pill-badge">Material Daur Ulang</span>
          <span class="badge pill-badge">Kreasi Upcycle</span>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <a href="#shop" class="btn btn-light"><i class="fa fa-cart-plus me-1"></i> Jelajahi Produk</a>
          <!-- <a href="#about" class="btn btn-outline-light btn-lg">Kenapa Kami</a> -->
        </div>
      </div>
      <div class="col-md-5">
        <div class="glass-card store-feature-panel grid-icons">
          <div class="store-feature">
            <div class="icon"><i class="fa-solid fa-bolt"></i></div>
            <div class="store-feature-copy">
              <div class="fw-semibold">Nilai Guna Baru</div>
              <div class="small">Material yang masih layak diberi fungsi dan manfaat baru.</div>
            </div>
          </div>
          <div class="store-feature">
            <div class="icon"><i class="fa-solid fa-star"></i></div>
            <div class="store-feature-copy">
              <div class="fw-semibold">Pilihan Bertanggung Jawab</div>
              <div class="small">Utamakan barang yang awet, dapat digunakan kembali, dan dirawat dengan baik.</div>
            </div>
          </div>
          <div class="store-feature">
            <div class="icon"><i class="fa-solid fa-fire"></i></div>
            <div class="store-feature-copy">
              <div class="fw-semibold">Gerak Bersama</div>
              <div class="small">Belajar, berbagi keterampilan, dan mengurangi barang terbuang bersama komunitas.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="home-community mb-5" aria-label="Komunitas Terra Kala">
  <style>
    .home-community { padding:1.25rem 0; border-top:1px solid var(--border); border-bottom:1px solid var(--border); }
    .home-community-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1.25rem; }
    .home-community-column { min-width:0; }
    .home-community-column + .home-community-column { border-left:1px solid var(--border); padding-left:1.25rem; }
    .home-community-heading { display:flex; align-items:center; gap:.55rem; margin-bottom:.75rem; }
    .home-community-heading i { color:var(--accent); }
    .home-community-entry { padding:.65rem 0; border-top:1px solid rgba(199,217,182,.7); }
    .home-community-entry:first-of-type { border-top:0; }
    .home-community-entry-title { display:block; color:var(--text); font-weight:700; line-height:1.35; text-decoration:none; }
    .home-community-entry-title:hover { color:var(--accent); }
    .home-community-meta { color:var(--muted); font-size:.8rem; }
    @media (max-width:767.98px) { .home-community-grid { grid-template-columns:1fr; gap:0; } .home-community-column + .home-community-column { border-left:0; border-top:1px solid var(--border); padding:1rem 0 0; margin-top:1rem; } }
  </style>
  <div class="home-community-grid">
    <section class="home-community-column" aria-labelledby="home-events-title">
      <h2 class="h5 home-community-heading" id="home-events-title"><i class="fa-solid fa-people-group"></i>Kompetisi sekolah</h2>
      <?php if($homeEvents): foreach($homeEvents as $event): $eventStarted = $event['starts_at'] <= $now; ?>
        <article class="home-community-entry">
          <a class="home-community-entry-title" href="community.php#schools"><?php echo esc($event['name']); ?></a>
          <div class="home-community-meta"><?php echo $eventStarted ? 'Sedang berlangsung' : 'Segera dimulai'; ?> · Berakhir <?php echo esc(date('d M Y', strtotime($event['ends_at']))); ?></div>
        </article>
      <?php endforeach; else: ?><p class="small text-muted mb-2">Belum ada kompetisi aktif.</p><?php endif; ?>
      <a class="small fw-semibold" href="community.php#schools">Lihat kompetisi <i class="fa fa-arrow-right ms-1"></i></a>
    </section>

    <section class="home-community-column" aria-labelledby="home-ebooks-title">
      <h2 class="h5 home-community-heading" id="home-ebooks-title"><i class="fa-solid fa-book-open"></i>Buku DIY</h2>
      <?php if($homeEbooks): foreach($homeEbooks as $book): ?>
        <article class="home-community-entry">
          <a class="home-community-entry-title" href="<?php echo esc($book['resource_url']); ?>" target="_blank" rel="noopener"><?php echo esc($book['title']); ?></a>
          <div class="home-community-meta"><?php echo esc($book['description']); ?></div>
        </article>
      <?php endforeach; else: ?><p class="small text-muted mb-2">Buku digital akan segera hadir.</p><?php endif; ?>
      <a class="small fw-semibold" href="community.php#diy">Lihat semua panduan <i class="fa fa-arrow-right ms-1"></i></a>
    </section>

    <section class="home-community-column" aria-labelledby="home-auctions-title">
      <h2 class="h5 home-community-heading" id="home-auctions-title"><i class="fa-solid fa-gavel"></i>Lelang berlangsung</h2>
      <?php if($homeAuctions): foreach($homeAuctions as $auction): $auctionStarted = $auction['starts_at'] <= $now; ?>
        <article class="home-community-entry">
          <a class="home-community-entry-title" href="community.php#auction"><?php echo esc($auction['title']); ?></a>
          <div class="home-community-meta"><?php echo $auctionStarted ? 'Sedang berlangsung' : 'Segera dimulai'; ?> · Penawaran <?php echo formatRupiah($auction['current_bid']); ?> · Tutup <?php echo esc(date('d M Y H:i', strtotime($auction['ends_at']))); ?></div>
        </article>
      <?php endforeach; else: ?><p class="small text-muted mb-2">Belum ada lelang yang aktif.</p><?php endif; ?>
      <a class="small fw-semibold" href="community.php#auction">Lihat lelang <i class="fa fa-arrow-right ms-1"></i></a>
    </section>
  </div>
</section>

<!-- Filosofi & Komitmen (Accordion) -->
<section class="mb-5">
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-3 p-md-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div>
          <h2 class="mb-1 section-title">Di Balik Setiap Desain</h2>
          <div class="divider"></div>
        </div>
      </div>
      <div class="accordion mt-2" id="accAbout">
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingOne">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true">
              <i class="fa-solid fa-bolt me-2 text-warning"></i> Filosofi Desain
            </button>
          </h2>
          <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accAbout">
            <div class="accordion-body">
              Desain kami menggabungkan estetika modern dengan semangat keberlanjutan. Setiap produk Terra Kala dibuat untuk memberi nilai lebih pada gaya hidup Anda, sambil mengurangi limbah dan mendukung penggunaan material daur ulang secara lebih sadar.
              Kami percaya kebiasaan berkelanjutan tumbuh dari pilihan sederhana: memakai barang lebih lama, menggunakan kembali material, dan mengurangi barang terbuang.
            </div>
          </div>
        </div>
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingTwo">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
              <i class="fa-solid fa-star me-2 text-primary"></i> Komitmen Kualitas
            </button>
          </h2>
          <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accAbout">
            <div class="accordion-body">
              Terra Kala berkomitmen menghadirkan produk berkualitas tinggi dengan bahan yang lebih bertanggung jawab secara lingkungan. Kami memilih material yang tahan lama, nyaman dipakai, dan dibuat dengan pendekatan yang lebih rendah limbah.
              Setiap jahitan, detail, dan finishing dipilih agar produk tidak hanya bagus dipakai, tetapi juga sejalan dengan nilai hidup yang lebih hijau dan sadar konsumsi.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Tentang Kami (Split + Timeline) -->
<section class="mb-5" id="about">
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-3 p-md-4">
      <div class="row g-4">
        <div class="col-lg-7">
          <h2 class="mb-2 section-title">Tentang Kami</h2>
          <div class="divider"></div>
          <p>Terra Kala adalah marketplace dan ruang belajar untuk produk guna ulang, barang preloved, serta karya upcycle. Kami mengajak masyarakat memperpanjang usia pakai barang dan mengurangi sampah melalui pilihan yang lebih bijak.
          Pembeli dapat menemukan produk dari seller lokal, mempelajari cara mengolah material bekas, dan mengikuti kegiatan komunitas. Kami mendorong informasi produk yang jelas serta kebiasaan merawat barang agar dapat digunakan lebih lama.</p>
          <div class="row g-3 mt-1">
            <div class="col-sm-6">
              <div class="p-3 border rounded-3 h-100">
                <div class="fw-semibold mb-1"><i class="fa-solid fa-pen-ruler me-2 text-primary"></i>Kreasi Upcycle</div>
                <div class="small text-muted">Material yang tersisa diolah menjadi barang baru yang berguna.</div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="p-3 border rounded-3 h-100">
                <div class="fw-semibold mb-1"><i class="fa-solid fa-shirt me-2 text-primary"></i>Rawat dan Pakai Kembali</div>
                <div class="small text-muted">Rawat barang dengan baik agar masa gunanya lebih panjang.</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-5">
          <h6 class="text-uppercase text-muted mb-2">Perjalanan Singkat</h6>
          <div class="timeline">
            <div class="t-item">
              <div class="fw-semibold">Berawal dari Depok</div>
              <div class="small text-muted">Tumbuh dari kepedulian pada sampah dan potensi guna ulang.</div>
            </div>
            <div class="t-item">
              <div class="fw-semibold">Belajar dan Berbagi</div>
              <div class="small text-muted">Panduan DIY dan kolaborasi membantu material menemukan fungsi baru.</div>
            </div>
            <div class="t-item">
              <div class="fw-semibold">Bertumbuh Bersama</div>
              <div class="small text-muted">Pembeli, seller, dan pelajar membangun kebiasaan yang lebih sirkular.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Info Toko + Map -->
<section class="mb-5">
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
      <div class="row g-4 align-items-center">
        <div class="col-lg-5">
          <h3 class="fw-bold mb-2 section-title"><i class="fa-solid fa-store me-2"></i>Terra Kala Store</h3>
          <div class="divider"></div>
          <p class="mb-1"><i class="fa-solid fa-location-dot me-2 text-primary"></i>Komplek Cibubur Indah V Blok S2/1</p>
          <p class="mb-1"><i class="fa-solid fa-clock me-2 text-primary"></i>Jam Operasional: <strong>Senin - Sabtu, 10:00 - 21:00 WIB</strong></p>
          <p class="mb-1"><i class="fa-solid fa-phone me-2 text-primary"></i>No. Telepon: <strong><a href="tel:081414002303" class="text-decoration-primary">081414002303</a></strong></p>
          <a class="btn btn-sm btn-outline-success mt-2" href="https://wa.me/6281414002303" target="_blank">
            <i class="fa-brands fa-whatsapp me-1"></i> Chat WhatsApp
          </a>
        </div>
        <div class="col-lg-7">
          <div class="ratio ratio-16x9 rounded-4 overflow-hidden">
            <iframe
              src="https://www.google.com/maps?q=Komplek%20Cibubur%20Indah%20V%20Blok%20S2%2F1&z=15&output=embed"
              width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Carousel Model Baju dengan Caption -->
<!-- <section class="mb-5">
  <h3 class="mb-3">Model Baju</h3>
  <?php $products = getProducts(); ?>
  <?php if(!$products): ?>
    <div class="alert alert-info">Belum ada produk. <a href="admin/products.php">Tambah sekarang</a>.</div>
  <?php else: ?>
    <div id="productCarousel" class="carousel slide shadow-sm rounded-4 overflow-hidden" data-bs-ride="carousel">
      <div class="carousel-indicators">
        <?php foreach($products as $idx=>$p): ?>
          <button type="button" data-bs-target="#productCarousel" data-bs-slide-to="<?php echo $idx; ?>" class="<?php echo $idx===0?'active':''; ?>" aria-current="<?php echo $idx===0?'true':'false'; ?>" aria-label="Slide <?php echo $idx+1; ?>"></button>
        <?php endforeach; ?>
      </div>
      <div class="carousel-inner">
        <?php foreach($products as $i=>$p): ?>
          <div class="carousel-item <?php echo $i===0?'active':''; ?>">
            <?php if($p['image_path']): ?>
              <div class="ratio ratio-21x9">
                <img src="<?php echo esc($p['image_path']); ?>" class="w-90 h-90" style="object-fit:cover;" alt="<?php echo esc($p['name']); ?>">
              </div>
            <?php else: ?>
              <div class="bg-light" style="height:120px; display:flex; align-items:center; justify-content:center;">
                <div class="text-muted">Belum ada gambar</div>
              </div>
            <?php endif; ?>
            <div class="carousel-caption d-none d-md-block text-start bg-white bg-opacity-50 rounded p-3 m-3">
              <h5 class="mb-1"><?php echo esc($p['name']); ?> <span class="fw-normal text-primary"><?php echo formatRupiah($p['price']); ?></span></h5>
              <p class="small mb-2"><?php echo esc($p['description']); ?></p>
              <a class="btn btn-sm btn-primary"
                 onclick="addToCart(<?php echo $p['id']; ?>, '<?php echo esc(trim(explode(',', $p['sizes'])[0] ?? 'M')); ?>', 1)">
                 Tambah ke Keranjang
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
  <?php endif; ?>
</section> -->

<!-- Mengapa Produk Daur Ulang -->
<section class="mb-5">
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-3 p-md-4">
      <h3 class="mb-3">Kenapa Pilih Produk Daur Ulang?</h3>
      <div class="row g-3">
        <div class="col-md-4">
          <div class="p-3 border rounded-3 h-100">
            <div class="fw-semibold mb-1"><i class="fa-solid fa-recycle me-2 text-success"></i>Material Daur Ulang</div>
            <div class="small text-muted">Setiap produk dibuat dengan pendekatan yang lebih sadar sampah dan pemanfaatan kembali material.</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-3 border rounded-3 h-100">
            <div class="fw-semibold mb-1"><i class="fa-solid fa-seedling me-2 text-success"></i>Lebih Ramah Bumi</div>
            <div class="small text-muted">Kami mendukung gaya hidup yang mengurangi jejak karbon dan mendorong konsumsi yang lebih bijak.</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-3 border rounded-3 h-100">
            <div class="fw-semibold mb-1"><i class="fa-solid fa-shirt me-2 text-success"></i>Awet dan Dapat Digunakan Kembali</div>
            <div class="small text-muted">Pilih, rawat, dan gunakan barang lebih lama sebelum mempertimbangkan pengganti.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Pilih Model & Ukuran -->
<section class="mt-4" id="shop">
  <style>
    .product-card > .card { height:100%; overflow:hidden; }
    .catalog-product-image { position:relative; flex:0 0 220px; height:220px; overflow:hidden; background:#e8eee2; display:flex; align-items:center; justify-content:center; color:#65735d; }
    .catalog-product-image img { display:block; width:100%; height:100%; object-fit:cover; object-position:center; }
    .catalog-product-image-fallback { display:grid; place-content:center; width:100%; height:100%; text-align:center; padding:1rem; }
  </style>
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
      <h3 class="mb-1">Pilih Produk Daur Ulang</h3>
      <p class="text-muted mb-0">Temukan item favorit Anda yang dibuat dengan nilai estetika, kualitas, dan tanggung jawab lingkungan.</p>
    </div>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 w-100">
      <form id="product-search-form" method="get" action="index.php" class="d-flex flex-wrap align-items-center gap-2" style="flex:1; min-width:280px;">
        <input type="hidden" name="seller_id" value="<?php echo (int)$sellerFilter; ?>">
        <input type="text" id="product-search-input" name="q" class="form-control form-control-sm" style="min-width:220px; max-width:280px;" placeholder="Cari produk..." value="<?php echo esc($searchQuery); ?>">
        <button type="submit" class="btn btn-sm btn-success">Cari</button>
        <?php if($searchQuery !== ''): ?>
          <a href="index.php<?php echo $sellerFilter > 0 ? '?seller_id=' . (int)$sellerFilter . '#shop' : '#shop'; ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
        <?php endif; ?>
      </form>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <a class="btn btn-sm <?php echo $sellerFilter > 0 ? 'btn-outline-success' : 'btn-success'; ?>" href="index.php#shop">Semua Seller</a>
        <?php foreach($sellers as $seller): ?>
          <!-- <a class="btn btn-sm <?php echo $sellerFilter === (int)$seller['id'] ? 'btn-success' : 'btn-outline-success'; ?>" href="index.php?seller_id=<?php echo (int)$seller['id']; ?>#shop">
            <?php echo esc($seller['name']); ?>
          </a> -->
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="row g-3">
  <?php if(!$products): ?>
    <div class="col-12" id="product-empty-state">
      <div class="alert alert-info d-flex align-items-center gap-2" style="border-radius:14px;">
        <i class="fa-solid fa-magnifying-glass"></i>
        <span>
          <?php if($searchQuery !== ''): ?>
            Tidak ada produk yang cocok dengan pencarian “<?php echo esc($searchQuery); ?>”.
          <?php else: ?>
            Belum ada produk. <a href="admin/products.php">Tambah sekarang</a>.
          <?php endif; ?>
        </span>
      </div>
    </div>
  <?php else: foreach($products as $p): ?>
    <div class="col-12 col-sm-6 col-lg-4 product-card" data-search="<?php echo esc(strtolower((string)($p['name'] ?? '') . ' ' . (string)($p['description'] ?? '') . ' ' . (string)($p['seller_name'] ?? ''))); ?>">
      <div class="card h-100">
        <div class="catalog-product-image">
          <?php if(!empty($p['image_path'])): ?>
            <img src="<?php echo esc($p['image_path']); ?>" alt="<?php echo esc($p['name']); ?>" loading="lazy" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
            <div class="catalog-product-image-fallback" hidden><i class="fa fa-image fs-3 d-block mb-2"></i><span>Foto produk tidak ditemukan</span></div>
          <?php else: ?>
            <div class="catalog-product-image-fallback"><i class="fa fa-image fs-3 d-block mb-2"></i><span>Foto produk belum tersedia</span></div>
          <?php endif; ?>
        </div>
        <div class="card-body d-flex flex-column">
          <?php $isNew = !empty($p['created_at']) && (strtotime($p['created_at']) >= strtotime('-14 days')); ?>
          <div class="mb-1 d-flex gap-2 flex-wrap">
            <?php if(!empty($p['is_best_seller'])): ?><span class="badge bg-warning-subtle text-warning-emphasis">Best Seller</span><?php endif; ?>
            <?php if($isNew): ?><span class="badge bg-success-subtle text-success-emphasis">New</span><?php endif; ?>
          </div>
          <h5 class="card-title"><?php echo esc($p['name']); ?></h5>
          <?php if(!empty($p['seller_name'])): ?>
            <div class="small text-muted mb-2">
              <i class="fa-solid fa-store me-1"></i>
              <a class="text-decoration-none" href="seller.php?id=<?php echo (int)$p['seller_id']; ?>"><?php echo esc($p['seller_name']); ?></a>
            </div>
          <?php endif; ?>
          <p class="text-muted small flex-grow-1"><?php echo esc($p['description']); ?></p>
          <div class="mb-2 fw-semibold"><?php echo formatRupiah($p['price']); ?></div>
          <div class="mb-2">
            <label class="form-label small mb-1">Ukuran</label>
            <div>
            <?php $sizeList = array_map('trim', explode(',', $p['sizes'] ?: 'All Size,S,M,L,XL')); $sizeList = array_values(array_filter($sizeList, function($value){ return $value !== ''; })); if(!in_array('All Size', $sizeList, true)) { array_unshift($sizeList, 'All Size'); } $defaultSize = in_array('All Size', $sizeList, true) ? 'All Size' : ($sizeList[0] ?? 'M'); foreach($sizeList as $s): ?>
              <span class="badge bg-light text-dark border me-1 badge-size"
                onclick="selectSize(<?php echo $p['id']; ?>, this, '<?php echo $s; ?>')">
                <?php echo $s; ?>
              </span>
            <?php endforeach; ?>
            </div>
            <input type="hidden" name="size_<?php echo $p['id']; ?>" value="<?php echo esc($defaultSize); ?>">
          </div>
          <div class="d-flex align-items-center">
            <input type="number" min="1" value="1" class="form-control me-2" style="max-width:100px" id="qty_<?php echo $p['id']; ?>">
            <button class="btn btn-primary flex-grow-1"
              onclick="addToCart(<?php echo $p['id']; ?>, document.querySelector('input[name=size_<?php echo $p['id']; ?>]').value, document.getElementById('qty_<?php echo $p['id']; ?>').value)">
              Tambah ke Keranjang
            </button>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; endif; ?>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('product-search-form');
  const input = document.getElementById('product-search-input');
  const cards = Array.from(document.querySelectorAll('.product-card'));
  const emptyState = document.getElementById('product-empty-state');

  if (!form || !input) return;

  function applyFilter(term) {
    const keyword = (term || '').toLowerCase().trim();
    let visibleCount = 0;

    cards.forEach(function (card) {
      const haystack = (card.getAttribute('data-search') || '').toLowerCase();
      const match = !keyword || haystack.includes(keyword);
      card.style.display = match ? '' : 'none';
      if (match) visibleCount++;
    });

    if (emptyState) {
      emptyState.style.display = visibleCount > 0 ? 'none' : '';
    }
  }

  input.addEventListener('input', function () {
    applyFilter(this.value);
  });

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    const term = (input.value || '').trim();
    const params = new URLSearchParams(window.location.search);
    if (term) {
      params.set('q', term);
    } else {
      params.delete('q');
    }

    const sellerValue = document.querySelector('input[name="seller_id"]')?.value || '';
    if (sellerValue) {
      params.set('seller_id', sellerValue);
    } else {
      params.delete('seller_id');
    }

    window.location.href = window.location.pathname + '?' + params.toString() + '#shop';
  });

  applyFilter(input.value || '');
});
</script>

<?php include 'footer.php'; ?>
