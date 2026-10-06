# Panduan Fitur Terra Kala

Panduan ini menjelaskan cara menggunakan fitur toko dan komunitas Terra Kala. Ganti `http://localhost/onlinestore` dengan alamat project yang digunakan.

## 1. Masuk dan peran pengguna

Dari halaman Beranda, buka menu **Masuk** di toolbar untuk memilih **Login pelanggan**, **Login seller**, atau **Login admin**. Satu sesi hanya menggunakan satu peran pada satu waktu. Untuk berganti peran, keluar terlebih dahulu, lalu masuk dengan peran yang dituju.

- **Pelanggan:** masuk memakai nomor HP/WhatsApp yang sudah terdaftar. Pendaftaran meminta nama dan nomor HP/WhatsApp.
- **Seller:** masuk menggunakan username dan password seller. Akun demo untuk pengujian tersedia di README.
- **Admin:** masuk melalui halaman login admin. Kredensial default hanya untuk pengujian lokal dan harus diganti sebelum aplikasi dipakai sungguhan.

## 2. Produk, keranjang, dan checkout

1. Buka **Beranda**, cari produk atau pilih produk dari katalog.
2. Pilih ukuran yang tersedia, tentukan jumlah, lalu tekan **Tambah ke Keranjang**.
3. Buka ikon keranjang di toolbar. Ubah jumlah atau hapus barang bila perlu.
4. Tekan **Checkout**, lengkapi alamat dan pilihan kurir, lalu buat pesanan.

Checkout menampilkan subtotal barang, biaya layanan, ongkir, dan total. Reward yang diklaim melalui voucher ditambahkan ke keranjang dengan harga gratis dan tidak menambah subtotal.

**Biaya layanan:** saat ini sebesar 5% dari subtotal barang, tidak termasuk ongkir. Nilainya ditampilkan terpisah di checkout dan invoice.

## 3. Pembayaran dan poin pelanggan

Pesanan baru berstatus **Menunggu pembayaran**. Pembayaran diperiksa secara manual oleh admin; aplikasi belum terhubung ke payment gateway.

- Setelah membayar sesuai instruksi admin, pelanggan menunggu verifikasi.
- Admin membuka **Admin → Verifikasi Pesanan**, mencocokkan pembayaran yang diterima, lalu memilih **Tandai lunas**. Jika pesanan memang harus dibatalkan, admin dapat memilih **Batalkan**.
- Poin dan voucher transaksi diberikan hanya setelah pesanan ditandai lunas. Perubahan status diproses satu kali.
- Riwayat status dan poin tersedia di **Akun → Riwayat Pesanan**.
- Dasar poin transaksi: setiap Rp10.000 subtotal pada pesanan lunas menghasilkan 1 poin. Setiap 5 poin menjadi 1 voucher. Katalog hadiah dan stok dapat berubah.

Jangan menandai pesanan lunas sebelum pembayaran benar-benar diterima. Status pesanan yang telah diproses tidak dapat diubah melalui halaman admin biasa.

## 4. Panduan DIY dan kiriman karya

Buka **Komunitas → Panduan DIY** untuk melihat panduan video dan buku digital. Pilih **Buka panduan** untuk membaca atau menonton materi.

Untuk mengirim karya sendiri:

1. Masuk sebagai pelanggan.
2. Isi judul karya, cerita proses, dan asal sekolah bila relevan.
3. Pilih video MP4, WebM, atau MOV sesuai batas ukuran yang ditampilkan pada form.
4. Kirim karya. Status awalnya menunggu moderasi.
5. Admin memeriksa karya melalui **Admin → Kelola Komunitas**. Poin kreator diberikan bila karya disetujui; jumlahnya ditetapkan admin saat moderasi.

Gunakan video milik sendiri atau yang memiliki izin. Hindari mengunggah data pribadi. Ikuti petunjuk keamanan jika proyek memakai alat tajam, panas, lem, atau cat.

## 5. Lelang preloved dan upcycle

Untuk ikut lelang:

1. Buka **Komunitas → Lelang** dan periksa status serta jadwal barang.
2. Masuk sebagai pelanggan. Tawaran hanya dapat dikirim setelah waktu mulai dan sebelum waktu penutupan.
3. Masukkan nilai tawaran. Tawaran pertama harus minimal sebesar harga awal; setiap tawaran berikutnya harus sekurangnya penawaran tertinggi ditambah kenaikan minimum.
4. Periksa lagi nilai sebelum menekan **Tawar**. Tawaran yang sudah masuk tidak memiliki fitur penarikan otomatis.
5. Setelah waktu lelang selesai, admin menutup lelang. Tawaran tertinggi ditetapkan sebagai pemenang. Jika tidak ada tawaran, lelang ditutup tanpa pemenang.
6. Pemenang melihat order lelang di bagian **Lelang yang kamu menangkan** pada halaman Komunitas. Tekan **Instruksi pembayaran** untuk menghubungi admin melalui WhatsApp.
7. Setelah dana diterima, admin memverifikasi order melalui **Admin → Verifikasi Pesanan**. Order lelang juga berstatus menunggu pembayaran dan dikenakan biaya layanan yang ditampilkan di order.

Perhatikan keterangan barang, kondisi, harga awal, kenaikan minimum, waktu, dan instruksi admin sebelum menawar. Listing dengan kata **DEMO** hanya contoh pengujian, bukan konfirmasi bahwa barang tersedia atau siap dijual. Pastikan admin mengonfirmasi stok dan kondisi barang sebelum mengikuti listing demo.

## 6. Kompetisi antar-sekolah

1. Buka **Komunitas → Kompetisi antar-sekolah** dan pilih event yang ingin diikuti.
2. Saat event berlangsung, masuk sebagai pelanggan, isi nama sekolah, nama tim, dan uraian misi/karya yang diselesaikan.
3. Kirim satu laporan untuk misi yang dikerjakan. Skor belum bertambah sampai admin memeriksa laporan.
4. Admin menyetujui atau menolak misi dan memberikan skor melalui **Admin → Kelola Komunitas**.
5. Skor tim tampil di papan peringkat. Setelah periode berakhir dan semua kiriman dimoderasi, admin menutup event untuk mengunci tiga besar.
6. Status hadiah podium dapat dilihat di halaman Komunitas. Admin mencatat hadiah sebagai diserahkan setelah benar-benar diberikan.

Tanggal, misi, dan hadiah mengikuti informasi pada masing-masing event. Nama sekolah dan tim perlu ditulis konsisten agar skor masuk ke tim yang sama.

## 7. Peringkat pembeli dan seller

- **Pembeli:** peringkat dihitung dari poin pada pesanan yang berstatus lunas. Jumlah transaksi dan subtotal belanja juga ditampilkan sebagai informasi.
- **Seller:** peringkat triwulanan memperhitungkan produk baru dan nilai penjualan dari order lunas. Tiga posisi teratas mendapat bonus 500, 300, dan 150 poin setelah periode ditutup.
- Admin menutup bonus periode yang sudah berakhir melalui **Admin → Kelola Komunitas → Settlement bonus seller**. Settlement hanya dapat dilakukan sekali per triwulan dan akan ditolak jika masih ada order seller yang menunggu pembayaran.

Saldo bonus seller ditampilkan pada tabel ranking. Bonus tersebut bukan saldo uang.

## 8. Menu admin

Masuk sebagai admin, lalu gunakan menu akun atau tautan dari panel admin:

- **Kelola produk:** tambah, ubah, hapus produk dan memilih seller.
- **Kelola seller:** membuat atau mengelola akun seller.
- **Reward voucher:** mengelola hadiah, biaya voucher, stok, dan status aktif.
- **Verifikasi pesanan:** menandai order lunas setelah pembayaran diterima atau membatalkan order yang belum diproses.
- **Kelola komunitas:** menerbitkan panduan, membuat lelang dan event sekolah, memoderasi video DIY/misi, menutup lelang/event, mencatat penyerahan hadiah, mengatur skor, dan menyelesaikan bonus seller.

## 9. Catatan pengelola

- Pastikan jam dan tanggal server sesuai zona waktu operasional sebelum membuat jadwal lelang/event.
- Sebelum lelang dipublikasikan, konfirmasi barang, foto, kondisi, harga awal, kenaikan minimum, jadwal, dan cara penyerahan.
- Atur batas upload PHP agar sekurangnya sebesar batas video yang ditampilkan pada form. Untuk PHP built-in server, gunakan pengaturan pada README dan mulai ulang server.
- Ganti kredensial default sebelum deployment, gunakan HTTPS, dan siapkan backup database serta file di folder `uploads/`.
