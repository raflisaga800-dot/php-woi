<?php
session_start();

$conn = mysqli_connect("localhost","root","","monitoring");
if(!$conn){
  die("Koneksi database gagal");
}

function cek_login(){
  if(!isset($_SESSION['role'])){
    header("Location: ../auth/login.php");
    exit;
  }
}

function cek_role($role){
  if($_SESSION['role'] != $role){
    header("Location: ../auth/login.php");
    exit;
  }
}

/*
LOGIKA PROGRESS:
5 karyawan → 1 input = 20%
4 karyawan → 1 input = 25%
DLL
*/
function progress_unit($unit_id){
  global $conn;

  $total_karyawan = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) AS total FROM karyawan WHERE unit_id='$unit_id'")
  )['total'];

  $sudah_input = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(DISTINCT karyawan_id) AS total FROM pekerjaan WHERE unit_id='$unit_id'")
  )['total'];

  if($total_karyawan == 0) return 0;

  return round(($sudah_input / $total_karyawan) * 100);
}
