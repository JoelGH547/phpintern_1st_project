<?php
session_start();
require_once 'db_connect.php';

// ถ้ามีการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: index.php");
        exit;
    } else {
        $error = "อีเมลหรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>เข้าสู่ระบบ</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-bg">
<div class="auth-container">
  <h2>เข้าสู่ระบบ</h2>
  <?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>
  <form method="post">
    <input type="email" name="email" placeholder="อีเมล" required>
    <input type="password" name="password" placeholder="รหัสผ่าน" required>
    <button type="submit">เข้าสู่ระบบ</button>
  </form>
  <p>ยังไม่มีบัญชี? <a href="signup.php">สมัครสมาชิก</a></p>
</div>
</body>
</html>
