<?php
session_start();
include 'config.php';

// PHPMailer: use & require harus di luar semua blok if
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ── Konfigurasi Gmail SMTP ─────────────────────────────────────────────────
// Ganti dua baris ini dengan email & App Password Gmail kamu
// Cara dapat App Password: myaccount.google.com → Keamanan → App Passwords
define('SMTP_USER', 'suryagege76@gmail.com');   // ← ganti
define('SMTP_PASS', 'uwmc mhop waaq rmyf');   // ← App Password Gmail (16 karakter)
// ──────────────────────────────────────────────────────────────────────────

// ===== LOGIN =====
if (isset($_POST['login'])) {

    $email    = $_POST['email'];
    $password = md5($_POST['password']);

    $query = mysqli_query($conn,
        "SELECT * FROM users
         WHERE email='$email'
         AND password='$password'"
    );

    if (mysqli_num_rows($query) > 0) {

        $data = mysqli_fetch_assoc($query);

        $_SESSION['id']   = $data['id'];
        $_SESSION['nama'] = $data['nama_lengkap'];

        $nama_js  = addslashes($data['nama_lengkap']);
        $email_js = addslashes($email);
        echo "<script>
                sessionStorage.setItem('gymzone_user', '" . $nama_js . "');
                sessionStorage.setItem('gymzone_email', '" . $email_js . "');
                alert('Login berhasil! Selamat datang, " . $nama_js . "!');
                window.location='gymzone.html';
              </script>";

    } else {

        echo "<script>alert('Email atau password salah!')</script>";
    }
}

// ===== LUPA PASSWORD: kirim link reset =====
if (isset($_POST['forgot'])) {

    $email = mysqli_real_escape_string($conn, $_POST['forgot_email']);
    $cek   = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($cek) > 0) {

        $token   = bin2hex(random_bytes(32));
        $expired = date('Y-m-d H:i:s', strtotime('+1 hour'));

        mysqli_query($conn,
            "INSERT INTO password_resets (email, token, expired_at)
             VALUES ('$email', '$token', '$expired')
             ON DUPLICATE KEY UPDATE token='$token', expired_at='$expired'"
        );

        $link = "http://" . $_SERVER['HTTP_HOST']
              . dirname($_SERVER['PHP_SELF'])
              . "/reset_password.php?token=$token";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom(SMTP_USER, 'GYMZONE');
            $mail->addAddress($email);
            $mail->Subject = 'Reset Password - GYMZONE';
            $mail->Body    = "Klik link berikut untuk reset password Anda (berlaku 1 jam):\n\n$link";

            $mail->send();
            echo "<script>alert('Link reset password telah dikirim ke email Anda.')</script>";

        } catch (Exception $e) {
            echo "<script>alert('Gagal mengirim email. Coba lagi nanti.')</script>";
        }

    } else {
        echo "<script>alert('Email tidak ditemukan!')</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - GYMZONE</title>

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

    /* ===== MODAL LUPA PASSWORD ===== */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.65);
      z-index: 999;
      align-items: center;
      justify-content: center;
    }
    .modal-overlay.active { display: flex; }
    .modal-box {
      background: #1a1a1a;
      border: 1px solid skyblue;
      border-radius: 12px;
      padding: 32px 28px;
      width: 100%;
      max-width: 360px;
    }
    .modal-box h3 {
      color: #fff;
      margin: 0 0 6px;
      font-size: 1.1rem;
    }
    .modal-box p {
      color: #aaa;
      font-size: 0.85rem;
      margin: 0 0 18px;
    }
    .modal-box input[type="email"] {
      width: 100%;
      padding: 10px 14px;
      border-radius: 8px;
      border: 1px solid #333;
      background: #0d0d0d;
      color: #fff;
      font-size: 0.95rem;
      box-sizing: border-box;
      margin-bottom: 14px;
    }
    .modal-box input[type="email"]:focus {
      outline: none;
      border-color: skyblue;
    }
    .modal-actions { display: flex; gap: 10px; }
    .modal-actions button {
      flex: 1;
      padding: 10px;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      font-weight: 700;
      font-size: 0.9rem;
      transition: background 0.2s;
    }
    .btn-kirim { background: skyblue; color: #0d0d0d; }
    .btn-kirim:hover { background: #0090c8; }
    .btn-batal { background: #333; color: #fff; }
    .btn-batal:hover { background: #444; }

    /* Link lupa password */
    .forgot-link {
      display: block;
      text-align: right;
      font-size: 0.82rem;
      color: skyblue;
      margin-top: -4px;
      margin-bottom: 14px;
      cursor: pointer;
      text-decoration: none;
    }
    .forgot-link:hover { text-decoration: underline; }
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

<h2>Login</h2>

<form method="POST">

<div class="input-group">
<label>Email</label>
<input type="email" name="email" required>
</div>

<div class="input-group">
<label>Password</label>
<input type="password" name="password" required>
</div>

<a class="forgot-link" onclick="document.getElementById('modalLupa').classList.add('active')">Lupa Password?</a>

<button type="submit" name="login" class="form-btn">Login</button>

</form>

<div class="form-footer">
Belum punya akun? <a href="registernew.php">Register</a>
</div>

</div>
</section>

<!-- ===== MODAL LUPA PASSWORD ===== -->
<div class="modal-overlay" id="modalLupa">
  <div class="modal-box">
    <h3>Lupa Password?</h3>
    <p>Masukkan email Anda. Kami akan mengirimkan link untuk reset password.</p>
    <form method="POST">
      <input type="email" name="forgot_email" placeholder="contoh@email.com" required>
      <div class="modal-actions">
        <button type="submit" name="forgot" class="btn-kirim">Kirim Link</button>
        <button type="button" class="btn-batal" onclick="document.getElementById('modalLupa').classList.remove('active')">Batal</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>
