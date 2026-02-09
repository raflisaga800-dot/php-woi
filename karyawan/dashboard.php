<?php
require '../config/config.php';
cek_login();
cek_role('karyawan');

$kid = $_SESSION['karyawan_id'];
$uid = $_SESSION['unit_id'];

// cek sudah submit pekerjaan
$cek = mysqli_query($conn,"SELECT * FROM pekerjaan WHERE karyawan_id='$kid'");
$sudah = mysqli_num_rows($cek) > 0;

// ambil data user
$user = mysqli_fetch_assoc(
  mysqli_query($conn,"
    SELECT * FROM users 
    WHERE karyawan_id='$kid' AND role='karyawan'
  ")
);
?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="../style.css">
  <style>
    .card{margin-bottom:15px}
    .msg-ok{color:#2ecc71;font-weight:bold}
    .msg-err{color:#e74c3c;font-weight:bold}

    /* === FORM KECIL GANTI PASSWORD === */
    .mini-form{
      max-width:320px;
    }
    .mini-form input{
      padding:8px;
      font-size:13px;
    }
    .mini-form button{
      padding:8px;
      font-size:13px;
    }
  </style>
</head>
<body>

<div class="page dashboard">

<!-- ================= HEADER ================= -->
<div style="display:flex;justify-content:space-between;align-items:center">
  <h1>Dashboard Karyawan</h1>
  <a href="../auth/logout.php" class="logout-btn">Logout</a>
</div>

<hr>

<!-- ================= INFO ================= -->
<div class="card">
  <p><b>Username:</b> <?= $user['username'] ?></p>
  <p><b>Status:</b> <?= $sudah ? '✅ Sudah Mengirim' : '⏳ Belum Mengirim' ?></p>
</div>

<hr>

<!-- ================= INPUT PEKERJAAN ================= -->
<h2>Input Pekerjaan</h2>

<?php if($sudah): ?>
<p class="msg-ok">Anda sudah menginput pekerjaan.</p>
<?php else: ?>

<div class="card">
<form method="POST" enctype="multipart/form-data">
  <input name="nama_pekerjaan" placeholder="Nama Pekerjaan" required>
  <input type="date" name="tanggal" required>
  <input type="time" name="jam" required>
  <input type="file" name="file" required>
  <button name="kirim">Kirim</button>
</form>
</div>

<?php
if(isset($_POST['kirim'])){
  $file = time().'_'.$_FILES['file']['name'];
  move_uploaded_file($_FILES['file']['tmp_name'], "../upload/".$file);

  mysqli_query($conn,"
    INSERT INTO pekerjaan VALUES(
      NULL,'$kid','$uid',
      '$_POST[nama_pekerjaan]',
      '$_POST[tanggal]',
      '$_POST[jam]',
      '$file'
    )
  ");

  echo "<p class='msg-ok'>Pekerjaan berhasil dikirim.</p>";
}
?>

<?php endif; ?>

<hr>

<!-- ================= GANTI PASSWORD ================= -->
<h2>Ganti Password</h2>

<div class="card mini-form">
<form method="POST">
  <input type="password" name="lama" placeholder="Password Lama" required>
  <input type="password" name="baru" placeholder="Password Baru" required>
  <input type="password" name="konfirmasi" placeholder="Konfirmasi Password" required>
  <button name="ganti_pw">Simpan</button>
</form>
</div>

<?php
if(isset($_POST['ganti_pw'])){

  $lama = md5($_POST['lama']);
  $baru = $_POST['baru'];
  $konf = $_POST['konfirmasi'];

  $cek = mysqli_query($conn,"
    SELECT id FROM users 
    WHERE karyawan_id='$kid'
    AND role='karyawan'
    AND password='$lama'
  ");

  if(mysqli_num_rows($cek) == 0){
    echo "<p class='msg-err'>Password lama salah.</p>";
  }elseif($baru != $konf){
    echo "<p class='msg-err'>Konfirmasi password tidak sama.</p>";
  }else{
    mysqli_query($conn,"
      UPDATE users SET password='".md5($baru)."'
      WHERE karyawan_id='$kid' AND role='karyawan'
    ");
    echo "<p class='msg-ok'>Password berhasil diganti.</p>";
  }
}
?>

</div>

<script src="../page-transition.js"></script>
</body>
</html>
