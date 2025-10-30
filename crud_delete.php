<?php
require_once 'auth_guard.php'; // 1. เรียก "ยาม" (ตัวใหม่ที่มี showErrorPage)
require_once 'db_connect.php';

// 2. ถ้าไม่มีสิทธิ์ 'user:delete' จะถูกเด้งไปหน้า Error สวยๆ อัตโนมัติ
checkPermission('user:delete');

$id_to_delete = $_GET['id'] ?? null;
$current_user_id = $_SESSION['user_id'];

if (!$id_to_delete) {
    header("Location: index.php");
    exit;
}

// 3. ป้องกันการลบตัวเอง (เรียกใช้ showErrorPage)
if ($id_to_delete == $current_user_id) {
    http_response_code(403);
    showErrorPage("Access Denied: ไม่สามารถลบตัวเองได้"); // <-- อัปเกรดแล้ว
}

// 4. ป้องกันการลบ Master Admin (Role ID 1)
$stmt_check = $pdo->prepare("SELECT role_id FROM users WHERE id = ?");
$stmt_check->execute([$id_to_delete]);
$user_to_delete = $stmt_check->fetch(PDO::FETCH_ASSOC);

if ($user_to_delete && $user_to_delete['role_id'] == 1) {
    http_response_code(403);
    showErrorPage("Access Denied: ไม่สามารถลบบัญชี Master Admin  ได้"); // <-- อัปเกรดแล้ว
}

// 5. ถ้าผ่านมาหมด ก็ลบได้
$stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
$stmt->execute([$id_to_delete]);

// (โค้ดเรียง ID ใหม่ เหมือนเดิม)
$pdo->exec("SET @count = 0;");
$pdo->exec("UPDATE users SET id = @count:=@count+1;");
$pdo->exec("ALTER TABLE users AUTO_INCREMENT = 1;");

header("Location: index.php");
exit;
?>