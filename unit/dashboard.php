<?php
require '../config/config.php';
cek_login();
cek_role('unit');

$unit_id = $_SESSION['unit_id'];

function deadline_color($deadline){
  $today = date('Y-m-d');
  $diff = (strtotime($deadline) - strtotime($today)) / 86400;
  return ($diff <= 2) ? 'danger' : 'safe';
}

// =================== GANTI PASSWORD ===================
if(isset($_POST['ganti_password'])){
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    $user = mysqli_fetch_assoc(
        mysqli_query($conn,"SELECT * FROM users WHERE unit_id='$unit_id' AND role='unit' LIMIT 1")
    );

    if(md5($old_pass) == $user['password'] && $new_pass === $confirm_pass && strlen($new_pass) >= 6){
        mysqli_query($conn,"
            UPDATE users SET password=MD5('$new_pass')
            WHERE id='{$user['id']}'
        ");
    }
}

// =================== UBAH DEADLINE ===================
if(isset($_POST['ubah_deadline'])){
  mysqli_query($conn,"
    UPDATE unit SET deadline='$_POST[deadline_baru]'
    WHERE id='$unit_id'
  ");
  echo '<script>location.href="";</script>';
}

// =================== TAMBAH KARYAWAN ===================
if(isset($_POST['tambah_karyawan'])){
  mysqli_query($conn,"
    INSERT INTO karyawan VALUES(NULL,'$_POST[nama]','$unit_id')
  ");
  $kid = mysqli_insert_id($conn);

  mysqli_query($conn,"
    INSERT INTO users VALUES(
      NULL,'$_POST[username]',MD5('123'),
      'karyawan','$unit_id','$kid'
    )
  ");
  echo '<script>location.href="";</script>';
}

// =================== HAPUS KARYAWAN ===================
if(isset($_POST['hapus_karyawan'])){
  mysqli_query($conn,"
    DELETE FROM users 
    WHERE karyawan_id='$_POST[hapus_karyawan_id]' 
    AND role='karyawan'
  ");
  mysqli_query($conn,"
    DELETE FROM karyawan WHERE id='$_POST[hapus_karyawan_id]'
  ");
  echo '<script>location.href="";</script>';
}

// =================== HAPUS PEKERJAAN ===================
if(isset($_POST['hapus_pekerjaan'])){
  mysqli_query($conn,"
    DELETE FROM pekerjaan WHERE id='$_POST[hapus_pekerjaan_id]'
  ");
  echo '<script>location.href="";</script>';
}

// =================== EDIT PEKERJAAN ===================
if(isset($_POST['edit_pekerjaan'])){
    $pid = $_POST['edit_pekerjaan_id'];
    $nama_pekerjaan = $_POST['edit_nama'];
    $tanggal = $_POST['edit_tanggal'];
    $jam = $_POST['edit_jam'];

    if(isset($_FILES['edit_file']) && $_FILES['edit_file']['name'] != ''){
        $file_tmp = $_FILES['edit_file']['tmp_name'];
        $file_name = time().'_'.basename($_FILES['edit_file']['name']);
        move_uploaded_file($file_tmp, "../upload/".$file_name);

        mysqli_query($conn,"
            UPDATE pekerjaan SET 
                nama_pekerjaan='$nama_pekerjaan',
                tanggal='$tanggal',
                jam='$jam',
                file='$file_name'
            WHERE id='$pid'
        ");
    } else {
        mysqli_query($conn,"
            UPDATE pekerjaan SET 
                nama_pekerjaan='$nama_pekerjaan',
                tanggal='$tanggal',
                jam='$jam'
            WHERE id='$pid'
        ");
    }

    echo '<script>location.href="";</script>';
}

?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="../style.css">
  <style>
    body{font-family:Arial, sans-serif; font-size:14px; background:#121212; color:#eee;}
    .deadline.safe{color:#2ecc71;font-weight:bold;}
    .deadline.danger{color:#e74c3c;font-weight:bold;}

    .progress-box{
      background:#333;
      border-radius:20px;
      overflow:hidden;
      height:14px;
      margin:8px 0;
    }
    .progress-bar{
      height:100%;
      background:linear-gradient(90deg,#3498db,#2ecc71);
      transition:.4s;
    }

    .card{
      padding:15px;
      margin-bottom:20px;
      border:1px solid #444;
      border-radius:8px;
      background:#1e1e1e;
    }

    input, button{
      padding:4px 6px;
      margin:2px 0;
      font-size:13px;
      background: #2b2b2b;
      border:1px solid #555;
      color:#eee;
    }
    button{cursor:pointer;}

    table{
      width:100%;
      border-collapse:collapse;
      margin-top:10px;
    }
    th, td{
      padding:6px;
      border:1px solid #555;
      text-align:left;
      font-size:13px;
      color:#eee;
      background:#1e1e1e;
    }
    th{
      background:#2b2b2b;
    }

    a{
      color:#3498db;
      text-decoration:none;
    }

    .footer-actions{
      margin-top:20px;
      text-align:center;
    }
    .footer-actions a{
      display:inline-block;
      margin:5px;
      padding:6px 12px;
      background:#2b2b2b;
      border:1px solid #555;
      color:#eee;
      text-decoration:none;
      border-radius:4px;
    }
    .footer-actions a:hover{
      background:#444;
    }
  </style>
</head>
<body>

<div class="page dashboard">

<!-- ================= HEADER ================= -->
<div>
  <h1>Dashboard Unit</h1>
</div>

<hr>

<?php
$u = mysqli_fetch_assoc(
  mysqli_query($conn,"SELECT * FROM unit WHERE id='$unit_id'")
);

$total = mysqli_num_rows(
  mysqli_query($conn,"SELECT id FROM karyawan WHERE unit_id='$unit_id'")
);

$selesai = mysqli_num_rows(
  mysqli_query($conn,"
    SELECT DISTINCT karyawan_id 
    FROM pekerjaan 
    WHERE unit_id='$unit_id'
  ")
);

$progress = $total ? round(($selesai/$total)*100) : 0;
$warna_deadline = deadline_color($u['deadline']);
?>

<!-- ================= RINGKASAN UNIT ================= -->
<div class="card">
<h2><?= $u['nama_unit'] ?></h2>
<p class="deadline <?= $warna_deadline ?>">Deadline: <?= $u['deadline'] ?></p>

<div class="progress-box">
  <div class="progress-bar" style="width:<?= $progress ?>%"></div>
</div>

<p><b>Progress:</b> <?= $progress ?>%</p>
<p><b>Karyawan:</b> <?= $selesai ?> / <?= $total ?> selesai</p>

<form method="POST" style="margin-top:10px">
  <input type="date" name="deadline_baru" required>
  <button name="ubah_deadline">Ubah Deadline</button>
</form>
</div>

<hr>

<!-- ================= TAMBAH KARYAWAN ================= -->
<h2>Tambah Karyawan</h2>
<div class="card">
<form method="POST">
  <input name="nama" placeholder="Nama Karyawan" required>
  <input name="username" placeholder="Username Login" required>
  <button name="tambah_karyawan">Tambah</button>
</form>
<small>Password default: <b>123</b></small>
</div>

<hr>

<!-- ================= STATUS KARYAWAN ================= -->
<h2>Status Karyawan</h2>
<table>
<tr>
  <th>Nama</th>
  <th>Status</th>
  <th>Aksi</th>
</tr>
<?php
$q = mysqli_query($conn,"SELECT * FROM karyawan WHERE unit_id='$unit_id'");
while($k=mysqli_fetch_assoc($q)){
  $cek = mysqli_num_rows(
    mysqli_query($conn,"SELECT id FROM pekerjaan WHERE karyawan_id='$k[id]' LIMIT 1")
  );
?>
<tr>
<td><?= $k['nama'] ?></td>
<td><?= $cek ? '✅ Sudah Kirim' : '⏳ Belum Kirim' ?></td>
<td>
<form method="POST" style="display:inline;">
  <input type="hidden" name="hapus_karyawan_id" value="<?= $k['id'] ?>">
  <button name="hapus_karyawan">Hapus</button>
</form>
</td>
</tr>
<?php } ?>
</table>

<hr>

<!-- ================= DATA PEKERJAAN ================= -->
<h2>Data Pekerjaan</h2>
<table>
<tr>
<th>Karyawan</th>
<th>Nama Pekerjaan</th>
<th>Tanggal</th>
<th>Jam</th>
<th>File</th>
<th>Aksi</th>
</tr>

<?php
$p = mysqli_query($conn,"
SELECT pekerjaan.*, karyawan.nama AS nama_karyawan
FROM pekerjaan
JOIN karyawan ON pekerjaan.karyawan_id = karyawan.id
WHERE pekerjaan.unit_id='$unit_id'
");
while($d=mysqli_fetch_assoc($p)){
?>
<tr>
<td><?= $d['nama_karyawan'] ?></td>
<td>
<form method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:2px;">
  <input type="hidden" name="edit_pekerjaan_id" value="<?= $d['id'] ?>">
  <input type="text" name="edit_nama" value="<?= $d['nama_pekerjaan'] ?>" required>
</td>
<td><input type="date" name="edit_tanggal" value="<?= $d['tanggal'] ?>" required></td>
<td><input type="time" name="edit_jam" value="<?= $d['jam'] ?>" required></td>
<td>
  <a href="../upload/<?= $d['file'] ?>" target="_blank"><?= $d['file'] ?></a>
  <input type="file" name="edit_file">
</td>
<td>
  <button name="edit_pekerjaan" style="padding:4px 6px;">Simpan</button>
  <form method="POST" style="display:inline;">
    <input type="hidden" name="hapus_pekerjaan_id" value="<?= $d['id'] ?>">
    <button name="hapus_pekerjaan">Hapus</button>
  </form>
</td>
</form>
</tr>
<?php } ?>
</table>

<hr>

<!-- ================= GANTI PASSWORD ================= -->
<h2>Ganti Password</h2>
<div class="card" style="max-width:300px;margin:auto;padding:10px;">
<form method="POST" style="display:flex; flex-direction:column; gap:6px;">
  <input type="password" name="old_password" placeholder="Password Lama" required>
  <input type="password" name="new_password" placeholder="Password Baru" required>
  <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required>
  <button name="ganti_password">Ubah</button>
</form>
</div>

<!-- ================= FOOTER LOGOUT ================= -->
<div class="footer-actions">
  <a href="../auth/logout.php">Logout</a>
</div>

</div>
<script src="../page-transition.js"></script>
</body>
</html>
