<?php
require_once 'db_connect.php';

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

// หลังจากลบข้อมูลเสร็จ
$pdo->exec("SET @count = 0;");
$pdo->exec("UPDATE users SET id = @count:=@count+1;");
$pdo->exec("ALTER TABLE users AUTO_INCREMENT = 1;");
