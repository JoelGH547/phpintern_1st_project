<?php
require_once 'auth_check.php'; // 1. เรียกยาม
require_once 'db_connect.php';

// 2. ตรวจสอบสิทธิ์ (Admin เท่านั้น)
if (!isAdmin()) {
    http_response_code(403);
    die("Access Denied. You do not have permission to delete users.");
}

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
    $stmt->execute([$_GET['id']]);

    // จัดเรียง ID ใหม่
    $pdo->exec("SET @count = 0;");
    $pdo->exec("UPDATE users SET id = @count:=@count+1;");
    $pdo->exec("ALTER TABLE users AUTO_INCREMENT = 1;");
}
header("Location: index.php");
exit;
?>