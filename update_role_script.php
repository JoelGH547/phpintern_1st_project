<?php
require_once 'auth_guard.php'; // 1. เรียก "ยาม" (ตัวใหม่ที่มี showErrorPage)
require_once 'db_connect.php';

checkPermission('permission:manage');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id_to_update = $_POST['user_id'] ?? null;
    $new_role_id = $_POST['new_role_id'] ?? null;
    
    if ($user_id_to_update == $_SESSION['user_id']) {
        showErrorPage("Error: ไม่สามารถเปลี่ยนสิทธิ์ของตัวเองได้"); // <-- อัปเกรดแล้ว
    }

    if ($user_id_to_update && $new_role_id) {
        $stmt = $pdo->prepare("UPDATE users SET role_id = ? WHERE id = ?");
        $stmt->execute([$new_role_id, $user_id_to_update]);
    }
}
header("Location: manage_permissions.php");
exit;
?>