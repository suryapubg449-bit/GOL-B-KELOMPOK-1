<?php
include 'config.php';

if (isset($_POST['register'])) {

    $nama     = $_POST['nama'];
    $email    = $_POST['email'];
    $password = md5($_POST['password']);

    $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Email sudah digunakan!')</script>";
    } else {

        $query = "INSERT INTO users (nama_lengkap, email, password)
                  VALUES ('$nama', '$email', '$password')";

        if (mysqli_query($conn, $query)) {
            echo "<script>
                    alert('Register berhasil!');
                    window.location='loginnew.php';
                  </script>";
        } else {
            echo "Gagal register!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - GYMZONE</title>

  <link rel="stylesheet" href="style.css">
    <style>


    /* ===== NAVBAR (same as gymzone) ===== */
    nav {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 40px;
      height: 64px;
      background: #0d0d0d;
      border-bottom: 2px solid skyblue;
      position: sticky;
      top: 0;
      z-index: 100;
    }
        .logo {
  font-size: 1.6rem;
  font-weight: 900;
  color: #fff;
  letter-spacing: 3px;
  text-transform: uppercase;
}

.logo span {
  color: skyblue;
}
    .nav-links { display: flex; align-items: center; gap: 8px; }
    .nav-links a {
      color: white;
      text-decoration: none;
      font-size: 0.9rem;
        font-weight:750;
      padding: 6px 14px;
      border-radius: 6px;
      transition: color 0.25s, background 0.25s;
    }
    .nav-links a:hover { color: skyblue; background: #1a1a1a; }
    .nav-links a.active { color: skyblue; }
    .logout-btn {
      background: skyblue !important;
      color: #0d0d0d !important;
      font-weight: 700;
      border-radius: 6px;
      padding: 6px 16px;
      transition: background 0.25s !important;
    }
    .logout-btn:hover { background: #0090c8 !important; }
</style>
</head>
<body>

<nav>
  <div class="logo"><span>GYM</span>ZONE</div>

  <div class="nav-links">
    <a href="index.html">Home</a>
    <a href="loginnew.php">Login</a>
  </div>
</nav>

<section>

<div class="form-container">

<h2>Register</h2>

<form method="POST">

<div class="input-group">
<label>Nama Lengkap</label>
<input type="text" name="nama" required>
</div>

<div class="input-group">
<label>Email</label>
<input type="email" name="email" required>
</div>

<div class="input-group">
<label>Password</label>
<input type="password" name="password" required>
</div>

<button type="submit" name="register" class="form-btn">
Register
</button>

</form>

<div class="form-footer">
Sudah punya akun?
<a href="loginnew.php">Login</a>
</div>

</div>

</section>

</body>
</html>