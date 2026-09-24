<?php
session_start();
include 'koneksi.php';
$error = '';

if (isset($_POST['login'])) {
    $u = mysqli_real_escape_string($conn, $_POST['username']);
    $p = mysqli_real_escape_string($conn, $_POST['password']);
    $q = mysqli_query($conn, "SELECT * FROM admins WHERE username='$u' AND password='$p'");
    if (mysqli_num_rows($q) > 0) {
        $_SESSION['admin'] = $u;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="login-box">
  <h2>📚 LOGIN ADMIN</h2>
  <form method="post">
    <label>Username</label>
    <input type="text" name="username" required autofocus>
    <label>Password</label>
    <input type="password" name="password" required>
    <button type="submit" name="login">LOGIN</button>
  </form>
</div>

<?php if ($error): ?>
<script>
Swal.fire({
  icon: 'error',
  title: 'Login Gagal',
  text: '<?= $error ?>',
  confirmButtonColor: '#8fa998'
});
</script>
<?php endif; ?>
</body>
</html>