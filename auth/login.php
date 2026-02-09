<?php require '../config/config.php'; ?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="page">
  <div class="login-container">
    <h1 class="welcome-text">Monitoring Pekerjaan</h1>
    <p class="sub-text">Silakan Login</p>

    <form method="POST">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button name="login">Login</button>
    </form>

<?php
if(isset($_POST['login'])){
  $u = $_POST['username'];
  $p = md5($_POST['password']);

  $q = mysqli_query($conn,"SELECT * FROM users WHERE username='$u' AND password='$p'");
  if(mysqli_num_rows($q) > 0){
    $d = mysqli_fetch_assoc($q);

    $_SESSION['id'] = $d['id'];
    $_SESSION['role'] = $d['role'];
    $_SESSION['unit_id'] = $d['unit_id'];
    $_SESSION['karyawan_id'] = $d['karyawan_id'];

    switch($d['role']){
      case 'admin': header("Location: ../admin/dashboard.php"); break;
      case 'unit': header("Location: ../unit/dashboard.php"); break;
      case 'karyawan': header("Location: ../karyawan/dashboard.php"); break;
      case 'viewer': header("Location: ../viewer/dashboard.php"); break;
    }
  } else {
    echo "<p>Login gagal</p>";
  }
}
?>

  </div>
</div>

<script src="../page-transition.js"></script>
</body>
</html>
