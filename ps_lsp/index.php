<?php
//digunakan supaya path gambar tetap benar
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';

/* DATA HARGA PLAYSTATION */
$ps_prices = [
    'ps3' => 10000,
    'ps4' => 15000,
    'ps5' => 25000
];
// menyimpan pesan error dan total bayar dan menampilkan ringkasan
$errors = [];
$total = 0;
$show_summary = false;

$harga_per_jam = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama      = trim($_POST['nama'] ?? '');
    $gender    = $_POST['gender'] ?? '';
    $identitas = trim($_POST['identitas'] ?? '');
    $ps        = $_POST['ps'] ?? '';
    $tanggal   = $_POST['tanggal'] ?? '';
    $durasi    = (int)($_POST['durasi'] ?? 0);
    $snack     = isset($_POST['snack']);

    // ambil harga PS
    if (isset($ps_prices[$ps])) {
        $harga_per_jam = $ps_prices[$ps];
    }

    // validasi
    if ($nama === '') $errors[] = 'Nama wajib diisi';
    if (!in_array($gender, ['L','P'])) $errors[] = 'Pilih jenis kelamin';
    if (!preg_match('/^\d{16}$/', $identitas)) $errors[] = 'Identitas harus 16 digit';
    if (!isset($ps_prices[$ps])) $errors[] = 'Jenis PS tidak valid';
    if ($tanggal === '') $errors[] = 'Tanggal wajib diisi';
    if ($durasi <= 0) $errors[] = 'Durasi minimal 1 jam';

    // =====================
    // HITUNG TOTAL (PHP)
    // =====================
    if (!$errors && (isset($_POST['hitung']) || isset($_POST['sewa']))) {

        $total = $harga_per_jam * $durasi;

        if ($durasi > 5) {
            $total *= 0.9; // diskon 10%
        }

        if ($snack) {
            $total += 20000;
        }

        $total = round($total);
        $show_summary = true;
    }
}

?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Rental PlayStation</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>

  /* spasi supaya navbar tidak menutupi navbar */
body { padding-top:70px }

/* ukuran gambar produk */
.card-img-top { height:220px; object-fit:cover }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
<div class="container">
<a class="navbar-brand" href="#">Rental PS 🎮</a>

<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="#produk">Produk</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#sewa">Sewa PlayStation</a>
        </li>
      </ul>
</div>
</nav>

<div class="container">

<!-- PRODUK -->
<section id="produk" class="mb-5">
<h2 class="mb-4">Produk 🎮</h2>
<div class="row">
<?php foreach (['3','4','5'] as $n): ?>
  <div class="col-md-4">
    <div class="card shadow-sm">
      <img src="<?= $base ?>img/playstation<?= $n ?>.jpg" class="card-img-top">
      <div class="card-body text-center">
        <h5>PlayStation <?= $n ?></h5>
      </div>
    </div>
  </div>
<?php endforeach ?>
</div>
</section>

<!-- SEWA -->
<section id="sewa">
<h2 class="mb-3">Sewa PlayStation 📝</h2>

<!-- tampilkan pesan error -->
<?php if ($errors): ?>
<div class="alert alert-danger">
<ul class="mb-0"><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
</div>
<?php endif ?>

<!-- tampilkan hasil total bayar -->
<?php if ($show_summary): ?>
<div class="alert alert-success">
Total Bayar: <b>Rp <?= number_format($total,0,',','.') ?></b>
</div>
<?php endif ?>

<form method="post" class="row g-3">

<div class="col-md-12">
<label>Nama</label>
<input type="text" name="nama" class="form-control" required>
</div>

<div class="col-md-12">
<label class="d-block">Jenis Kelamin</label>
<div class="form-check form-check-inline">
<input class="form-check-input" type="radio" name="gender" value="L" required> Laki-laki
</div>
<div class="form-check form-check-inline">
<input class="form-check-input" type="radio" name="gender" value="P"> Perempuan
</div>
</div>

<div class="col-md-12">
  <label>Nomor Identitas</label>
  <input type="text" name="identitas" maxlength="16" class="form-control" required>
</div>

<div class="col-md-12">
<label>Jenis PlayStation</label>
    <select name="ps" id="ps" class="form-select" onchange="tampilkanHarga()" required>
        <option value="">-- Pilih --</option>
        <option value="ps3">PS 3</option>
        <option value="ps4">PS 4</option>
        <option value="ps5">PS 5</option>
    </select>
</div>

<div class="col-md-12">
  <label>Harga / Jam</label>
  <input type="text" id="harga_view" class="form-control"
    value="<?= $harga_per_jam ? 'Rp '.number_format($harga_per_jam,0,',','.') : '-' ?>"
    readonly>
</div>



<div class="col-md-12">
    <label>Tanggal Sewa</label>
    <input type="date" name="tanggal" class="form-control" required>
</div>

<div class="col-md-12">
    <label>Durasi (Jam)</label>
    <input type="number" name="durasi" id="durasi"class="form-control" min="1" required>
</div>

<div class="col-md-12">
    <div class="form-check">
        <input type="checkbox" id="snack" name="snack"class="form-check-input">
        <label class="form-check-label">Snack (+20.000)</label>
    </div>
</div>

<div class="col-md-12">
    <label>Total Bayar</label>
      <input type="text" class="form-control fw-bold"
          value="<?= $show_summary ? 'Rp '.number_format($total,0,',','.') : '' ?>"
          readonly>
</div>


<div class="col-md-12 d-flex gap-2">
    <button type="submit" name="hitung" class="btn btn-success">
        Hitung Total
    </button>

    <button type="submit" name="sewa" class="btn btn-primary">
        Sewa Sekarang
    </button>

    <button type="reset" class="btn btn-danger">
        Cancel
    </button>
</div>



</form>
</section>


</div>

<script>
  const psPrices = {
    ps3: 10000,
    ps4: 15000,
    ps5: 25000
  };

  function tampilkanHarga() {
    const ps = document.getElementById('ps').value;
    const hargaInput = document.getElementById('harga_view');

    if (ps && psPrices[ps]) {
      hargaInput.value = 'Rp ' + psPrices[ps].toLocaleString('id-ID');
    } else {
      hargaInput.value = '-';
    }
  }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
