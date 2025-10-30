<?php
session_start();
require_once 'db_connect.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        
        // --- [อัปเกรด] ---
        // 1. ดึง "สิทธิ์" (permissions) ทั้งหมดที่ User นี้มี
        $sql_perms = "SELECT p.permission_name 
                      FROM role_permissions rp
                      JOIN permissions p ON rp.permission_id = p.permission_id
                      WHERE rp.role_id = :role_id";
        
        $stmt_perms = $pdo->prepare($sql_perms);
        $stmt_perms->execute(['role_id' => $user['role_id']]);
        
        // $permissions จะเป็น Array เช่น ['user:read', 'user:update:self']
        $permissions = $stmt_perms->fetchAll(PDO::FETCH_COLUMN);

        // 2. เก็บทุกอย่างไว้ใน Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role_id'] = $user['role_id']; // เก็บ role_id ไว้
        $_SESSION['permissions'] = $permissions; // <-- นี่คือหัวใจใหม่!
        
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