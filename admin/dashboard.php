<?php
require '../config/config.php';
cek_login();
cek_role('admin');

function deadline_color($deadline){
  $today = date('Y-m-d');
  $diff = (strtotime($deadline) - strtotime($today)) / 86400;
  return ($diff <= 2) ? 'danger' : 'safe';
}

// =================== TAMBAH UNIT ===================
if(isset($_POST['tambah_unit'])){
  mysqli_query($conn,"INSERT INTO unit VALUES(NULL,'$_POST[nama_unit]','$_POST[deadline]')");
  $id = mysqli_insert_id($conn);
  mysqli_query($conn,"INSERT INTO users VALUES(NULL,'$_POST[username_unit]',MD5('123'),'unit','$id',NULL)");

  // redirect supaya tidak submit ulang saat refresh
  echo '<script>location.href="";</script>';
}

// =================== EDIT DEADLINE UNIT ===================
if(isset($_POST['edit_deadline'])){
  mysqli_query($conn,"UPDATE unit SET deadline='$_POST[deadline_baru]' WHERE id='$_POST[edit_unit_id]'");
  echo '<script>location.href="";</script>';
}

// =================== HAPUS UNIT ===================
if(isset($_POST['hapus_unit'])){
  mysqli_query($conn,"DELETE FROM users WHERE unit_id='$_POST[hapus_unit_id]' AND role='unit'");
  mysqli_query($conn,"DELETE FROM unit WHERE id='$_POST[hapus_unit_id]'");
  echo '<script>location.href="";</script>';
}

// =================== TAMBAH KARYAWAN ===================
if(isset($_POST['tambah_karyawan'])){
  mysqli_query($conn,"INSERT INTO karyawan VALUES(NULL,'$_POST[nama]','$_POST[unit_id]')");
  $kid=mysqli_insert_id($conn);
  mysqli_query($conn,"INSERT INTO users VALUES(NULL,'$_POST[username_karyawan]',MD5('123'),'karyawan','$_POST[unit_id]','$kid')");
  echo '<script>location.href="";</script>';
}

// =================== HAPUS KARYAWAN ===================
if(isset($_POST['hapus_karyawan'])){
  mysqli_query($conn,"DELETE FROM users WHERE karyawan_id='$_POST[hapus_karyawan_id]' AND role='karyawan'");
  mysqli_query($conn,"DELETE FROM karyawan WHERE id='$_POST[hapus_karyawan_id]'");
  echo '<script>location.href="";</script>';
}

// =================== HAPUS PEKERJAAN ===================
if(isset($_POST['hapus_pekerjaan'])){
  mysqli_query($conn,"DELETE FROM pekerjaan WHERE id='$_POST[hapus_pekerjaan_id]'");
  echo '<script>location.href="";</script>';
}

?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="../style.css">
  <style>
    .deadline.safe{color:#2ecc71;font-weight:bold}
    .deadline.danger{color:#e74c3c;font-weight:bold}

    .progress-box{
      background:#eee;
      border-radius:20px;
      overflow:hidden;
      height:14px;
      margin:6px 0 10px
    }
    .progress-bar{
      height:100%;
      background:linear-gradient(90deg,#3498db,#2ecc71);
      width:0%;
      transition:.4s
    }
    table{font-size:14px}
    th,td{padding:4px}
  </style>
</head>
<body>

<div class="page dashboard">

<!-- ================= HEADER ================= -->
<div style="display:flex;justify-content:space-between;align-items:center">
  <h1>Dashboard Admin</h1>
  <a href="../auth/logout.php" class="logout-btn">Logout</a>
</div>

<hr>

<!-- ================= MONITORING UNIT ================= -->
<h2>Monitoring Unit</h2>

<div class="grid">
<?php
$unit = mysqli_query($conn,"SELECT * FROM unit");
while($u = mysqli_fetch_assoc($unit)){

  $total = mysqli_num_rows(
    mysqli_query($conn,"SELECT id FROM karyawan WHERE unit_id='$u[id]'")
  );

  $selesai = mysqli_num_rows(
    mysqli_query($conn,"
      SELECT DISTINCT karyawan_id 
      FROM pekerjaan 
      WHERE unit_id='$u[id]'
    ")
  );

  $progress = $total ? round(($selesai/$total)*100) : 0;
  $warna_deadline = deadline_color($u['deadline']);
?>
<div class="card" style="min-height:360px">

  <h3><?= $u['nama_unit'] ?></h3>

  <p class="deadline <?= $warna_deadline ?>">
    Deadline: <?= $u['deadline'] ?>
  </p>

  <div class="progress-box">
    <div class="progress-bar" style="width:<?= $progress ?>%"></div>
  </div>

  <p><b>Progress:</b> <?= $progress ?>%</p>
  <p><b>Karyawan:</b> <?= $selesai ?> / <?= $total ?> selesai</p>

  <hr>

  <table width="100%">
    <tr>
      <th align="left">Karyawan</th>
      <th>Status</th>
      <th>Aksi</th>
    </tr>

<?php
$k = mysqli_query($conn,"SELECT * FROM karyawan WHERE unit_id='$u[id]'");
while($karyawan = mysqli_fetch_assoc($k)){

  $cek = mysqli_num_rows(
    mysqli_query($conn,"
      SELECT id FROM pekerjaan 
      WHERE karyawan_id='$karyawan[id]'
      LIMIT 1
    ")
  );
?>
<tr>
  <td><?= $karyawan['nama'] ?></td>
  <td><?= $cek ? '✅ Sudah Kirim' : '⏳ Belum Kirim' ?></td>
  <td>
    <form method="POST" style="display:inline">
      <input type="hidden" name="hapus_karyawan_id" value="<?= $karyawan['id'] ?>">
      <button name="hapus_karyawan">Hapus</button>
    </form>
  </td>
</tr>
<?php } ?>
  </table>

</div>
<?php } ?>
</div>

<hr>

<!-- ================= MANAJEMEN UNIT ================= -->
<h2>Manajemen Unit</h2>

<div class="grid">

<div class="card">
<h3>Tambah Unit</h3>
<form method="POST">
  <input name="nama_unit" placeholder="Nama Unit" required>
  <input name="username_unit" placeholder="Username Login Unit" required>
  <input type="date" name="deadline" required>
  <button name="tambah_unit">Tambah</button>
</form>
<small>Password default: <b>123</b></small>
</div>

<?php
$unit = mysqli_query($conn,"
SELECT unit.*, users.username 
FROM unit 
LEFT JOIN users ON users.unit_id=unit.id AND users.role='unit'
");
while($u=mysqli_fetch_assoc($unit)){
?>
<div class="card">
<h3><?= $u['nama_unit'] ?></h3>
<p>Login: <b><?= $u['username'] ?? '-' ?></b></p>

<form method="POST">
  <input type="hidden" name="edit_unit_id" value="<?= $u['id'] ?>">
  <input type="date" name="deadline_baru" required>
  <button name="edit_deadline">Ubah Deadline</button>
</form>

<form method="POST">
  <input type="hidden" name="hapus_unit_id" value="<?= $u['id'] ?>">
  <button name="hapus_unit">Hapus</button>
</form>
</div>
<?php } ?>

</div>

<hr>

<!-- ================= TAMBAH KARYAWAN ================= -->
<h2>Tambah Karyawan</h2>

<div class="card">
<form method="POST">
  <input name="nama" placeholder="Nama Karyawan" required>
  <input name="username_karyawan" placeholder="Username Login" required>
  <select name="unit_id" required>
<?php
$u=mysqli_query($conn,"SELECT * FROM unit");
while($d=mysqli_fetch_assoc($u)){
  echo "<option value='$d[id]'>$d[nama_unit]</option>";
}
?>
  </select>
  <button name="tambah_karyawan">Tambah</button>
</form>
<small>Password default: <b>123</b></small>
</div>

<hr>

<!-- ================= DATA PEKERJAAN ================= -->
<h2>Data Pekerjaan</h2>

<table>
<tr>
<th>Karyawan</th><th>Unit</th><th>Nama</th>
<th>Tanggal</th><th>Jam</th><th>File</th><th>Aksi</th>
</tr>

<?php
$p=mysqli_query($conn,"
SELECT pekerjaan.*,karyawan.nama AS nama_karyawan,unit.nama_unit
FROM pekerjaan
JOIN karyawan ON pekerjaan.karyawan_id=karyawan.id
JOIN unit ON pekerjaan.unit_id=unit.id
");
while($d=mysqli_fetch_assoc($p)){
?>
<tr>
<td><?= $d['nama_karyawan'] ?></td>
<td><?= $d['nama_unit'] ?></td>
<td><?= $d['nama_pekerjaan'] ?></td>
<td><?= $d['tanggal'] ?></td>
<td><?= $d['jam'] ?></td>
<td><a href="../upload/<?= $d['file'] ?>">Download</a></td>
<td>
<form method="POST">
  <input type="hidden" name="hapus_pekerjaan_id" value="<?= $d['id'] ?>">
  <button name="hapus_pekerjaan">Hapus</button>
</form>
</td>
</tr>
<?php } ?>
</table>

</div>
</body>
</html>
