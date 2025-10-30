<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // ตรวจสอบอีเมลซ้ำ
    $check = $pdo->prepare("SELECT * FROM users WHERE email=?");
    $check->execute([$email]);
    if ($check->rowCount() > 0) {
        $error = "อีเมลนี้ถูกใช้แล้ว";
    } else {
        // เพิ่มแค่ข้อมูลหลัก Role จะถูกกำหนดเป็น 'user' อัตโนมัติ
        $stmt = $pdo->prepare("INSERT INTO users (name, lastname, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $lastname, $email, $password]);
        header("Location: login.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>สมัครสมาชิก</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-bg">
<div class="auth-container">
  <h2>สมัครสมาชิก</h2>
  <?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>
  <form method="post">
    <input type="text" name="name" placeholder="ชื่อ" required>
    <input type="text" name="lastname" placeholder="นามสกุล" required>
    <input type="email" name="email" placeholder="อีเมล" required>
    <input type="password" name="password" placeholder="รหัสผ่าน" required>
    <button type="submit">สมัครสมาชิก</button>
  </form>
  <p>มีบัญชีแล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
</div>
</body>
</html>