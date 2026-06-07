<?php
session_start();
include 'config.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';
$valid = false;
$email = '';

if ($token) {
    $t = mysqli_real_escape_string($conn, $token);
    $query = mysqli_query($conn,
        "SELECT * FROM password_resets WHERE token='$t'"
    );
    if (mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_assoc($query);
        // Cek expired pakai PHP bukan NOW() MySQL agar tidak terpengaruh timezone
        if (strtotime($row['expired_at']) > time()) {
            $valid = true;
            $email = $row['email'];
        }
    }
}

if (isset($_POST['reset'])) {

    $new_pass = md5($_POST['new_password']);
    $em = mysqli_real_escape_string($conn, $email);

    mysqli_query($conn,
        "UPDATE users SET password='$new_pass' WHERE email='$em'"
    );
    mysqli_query($conn,
        "DELETE FROM password_resets WHERE email='$em'"
    );

    echo "<script>
            alert('Password berhasil diubah! Silakan login.');
            window.location='loginnew.php';
          </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password - GYMZONE</title>

  <link rel="stylesheet" href="style.css">
  <style>
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
    .logo span { color: skyblue; }
    .nav-links { display: flex; align-items: center; gap: 8px; }
    .nav-links a {
      color: white;
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: 750;
      padding: 6px 14px;
      border-radius: 6px;
      transition: color 0.25s, background 0.25s;
    }
    .nav-links a:hover { color: skyblue; background: #1a1a1a; }
  </style>
</head>
<body>

<nav>
  <div class="logo"><span>GYM</span>ZONE</div>
  <div class="nav-links">
    <a href="index.html">Home</a>
    <a href="loginnew.php">Login</a>
    <a href="registernew.php">Register</a>
  </div>
</nav>

<section>
<div class="form-container">

<?php if ($valid): ?>

  <h2>Reset Password</h2>

  <form method="POST">

    <div class="input-group">
      <label>Password Baru</label>
      <input type="password" name="new_password" minlength="6" required>
    </div>

    <div class="input-group">
      <label>Konfirmasi Password</label>
      <input type="password" name="confirm_password" minlength="6" required
             oninvalid=""
             oninput="this.setCustomValidity(
               this.value !== document.querySelector('[name=new_password]').value
               ? 'Password tidak cocok!' : '')">
    </div>

    <button type="submit" name="reset" class="form-btn">Simpan Password</button>

  </form>

<?php else: ?>

  <h2>Link Tidak Valid</h2>
  <p style="color:#aaa; text-align:center; margin-top:12px;">
    Link reset password tidak valid atau sudah kadaluarsa.<br>
    Silakan <a href="loginnew.php" style="color:skyblue;">request ulang</a>.
  </p>

<?php endif; ?>

</div>
</section>

</body>
</html>
