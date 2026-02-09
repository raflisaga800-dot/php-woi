<?php
require '../config/config.php';
cek_login();
cek_role('viewer');

function deadline_color($deadline){
  $today = date('Y-m-d');
  $diff = (strtotime($deadline) - strtotime($today)) / 86400;
  return ($diff <= 2) ? 'danger' : 'safe';
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
  <h1>Dashboard Monitoring</h1>
  <a href="../auth/logout.php" class="logout-btn">Logout</a>
</div>

<hr>

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
<div class="card" style="min-height:340px">

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
      <th align="left">Nama Karyawan</th>
      <th>Status</th>
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
</tr>
<?php } ?>

  </table>

</div>
<?php } ?>

</div>

</div>

<script src="../page-transition.js"></script>
</body>
</html>
