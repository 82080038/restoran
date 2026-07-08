
---
biasanya ada harga menu yang berbeda; misalnya, apabila hanya daging pangangg tanpa nasi, miaslnya harga Rp 30.000; kemudian apabila hanya nasi, misalnya harga Rp 10.000 per porsi, namun apabila daging panggang pakai nasi, maka harganya menjad Rp 35.000;

harga daging panggang per kilo RP 250.000; 
bagaimana aplikasi mengatasi hal tersebut ?
termasuk melakukan kalkukasi dan saran oleh system ?
---

analisa tentang normalisasi database; banyak yang merupakan tabel master, yang bisa mengurangi redundansi data di database;
namun, perlu diperhatikan, bahwa aplikasi ini adalah aplikasi multi-tenant; sehingga, semua tenant harus bisa dilayani oleh aplikasi;artinya, harus bisa dinamis dan adaptip.
---
bagaimana dengan sistem pembayaran, karena ada saja pelanggan yang sedang melakukan pembayaran, namun butuh kembalian, sehingga lama; dan pelanggan lain mendahului melakukan pembayaran tanpa kembalian, sehingga yang uang pas tersebut harus di dahulukan disimpan sebagai pembayaran lengkap dan selesai; berarti yang harus kembalian tersebut harus disimpan sementara; dan nantinya bisa dilanjutkan;
bagaimana mengatasi hal tersebut ?

---
misalnya, di tenant; ada menjual minuman, namanya badak;
nah, kalau normal, alias tidak dikulkas, harganya 10 ribu,
kalau sudah dikulkas, harganya 12 ribu;
kalau sduah dikulkas dan diminta pelanggan harus pakai es, harganya 15 ribu;

bagaimana menngantisipasi hal tersebut, termasuk terhadap produk tenant lainnya ?
apakah aplikasi sudah mengatasi hal tersebut ?
--
misalnya, ada tenant yang beroperasi  di satu instansi, dan pelanggan ada yang makan minum di kantin tenant tersebut; ada juga yang minta diantar ke ruangan pegawai, ada juga masyarakat yang bukan pegawai yang makan minum di tenant tersebut;
apakah aplikasi sudah bisa mengatasi hal tersebut ?
termasuk lokasi pengantaran dan (mungkin) ada atau tidak biaya antar ke ruangan pegawai?
--

apakah aplikasi, sudah mengantsipasi biaya modal, termasuk biaya modal yang nilainya kecil ?
misalnya biaya untuk bumbu (cabe, garam, kopi bubuk, gula, biaya listrik, air, telepon, gaji pegawai) dan sebagainya ?

----
apakah aplikasi sudah mengantisipasi tentang kerugian yang tidak disengaja ? misalnya ada gelas pecah yang tidak disengaja, apakah modul bagian inventaris sudah otomatis melakukan adjustment?

---

untuk membuat suatu produk, misalnya : ayam pinadar, itu kan butuh bumbu; dan ada jumlahnya, apakah aplikasi sudah mengantisipasi akuntansi dan pengurangan bahan secara otmatis; dan memberikan warning tentang ada/kurang/atau tidak adanya bahan dari bumbu tersebut ?
kemudian menyarankan nilai jual dari HPP + bumbu produk tersebut ?

----
bagaimana dengan piring atau peralatan kotor, apakah diketahui aplikasi, dan memberikan saran kepada pemilik atau petugas, unntuk melakukan pembersihan;
sehingga inventaris harus bisa mengetahui kesediaan untuk operasional tenant.

---

kalau misalnya pemilik tenant tidak di tempat atau bepergian, apakah aplikasi sudah bisa mengantisipasi kinerja karyawan, berlangsungnya usaha, dan membuat laporan kepada pemilik usaha, dan analisa dari laporan tersebut, apakah nilainya wajar atau tidak ?
--
bagaimana dengan cabang tenant ?
misalnya cabangnya butuh bahan dari cabang utama atau sebaliknya, apakah aplikasi sudah mengantisipasi hal tersebut dan sudah ada bagian analisa dan laporan untuk kegiatan tersebut ?

--
misalnya, ada pelanggan yang merupakan keluarga, biasanya diberikan diskon; atau, tidak berbayar, bagaimana aplikasi bisa mengantisipasi hal tersebut ?

---
bagaimana dengan schedulu karyawan, kalau by shift masih gampang, bagaimana jika tenant hanya aktif pada jam tertentu, dan semua karyawan harus hadir?

--

bagaimana dengan tenant, yang tidak memiliki dapur, atau display, atau beberapa bagian yang seharusnya ada di tenant F&B; apakah aplikasi masih tetap bisa melakukan operasional terhadap tenant tersebut ?

--
bagaimana dengan tenant, misalnya rumah makan padang, biasanya seluruh makanan yang hendak dijual sudah ada di display; sehingga hanya kadang butuh sedikit tindakan terhadap peralatan kotor dan dapur, bagaimana aplikasi mengatasipasi hal tersebut ?
---

apakah tampilan menu kepada pelangan, maupun guest, menampilkan seluruh menu yang ada maupun sudah habis di aplikasi ?
dan, sebaiknya bagaimana untuk tampilan menu tersebut ?

---

bagaimana dengan pelanggan yang alergi terhadap sesuatu ?
bagaimana aplikasi mengakomodir hal tersebut ?
--
bagaimana aplikasi mengantisipasi hal ini :
ada pelanggan yang sudah dihidangkan atau diantarkan pesanan sesuai yang awalnya diminta; rupanya minta ganti menu;
bagaimana aplikasi menangani masalah tersebut,
sebagai contoh lain, misalnya pelanggan minta ganti bumbu yang sudah dihidangkan atau diantar.
--
periksa semua integrasi FE ke endpoints (termasuk API routers) dan ke BE; lengkapi dan perbaiki.
---

berarti, masih banyak skenario asli lapangan, yang belum diantisipasi aplikasi ini;
analisa secara mendalam, dari aplikasi d internet, kurasi dan imlementasikan.
