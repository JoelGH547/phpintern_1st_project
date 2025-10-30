<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}

function hasPermission($permissionName) {
    if (empty($_SESSION['permissions'])) {
        return false;
    }
    return in_array($permissionName, $_SESSION['permissions']);
}

/**
 * -----------------------------------------------------------------
 * ฟังก์ชันใหม่สำหรับแสดงหน้า Error ที่สวยงาม
 * -----------------------------------------------------------------
 * @param string $message ข้อความ Error ที่จะแสดง
 */
function showErrorPage($message) {
    // เราจะยืมสไตล์จาก .auth-bg และ .auth-container มาใช้
    echo '
    <!doctype html>
    <html lang="th">
    <head>
    <meta charset="utf-8">
    <title>เกิดข้อผิดพลาด</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* สไตล์สำหรับกล่องข้อความ Error สีแดง */
        .error-message-box {
            color: #721c24; /* สีตัวอักษรแดงเข้ม */
            background-color: #f8d7da; /* สีพื้นหลังแดงอ่อน */
            border: 1px solid #f5c6cb; /* สีขอบ */
            padding: 20px;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 500;
            text-align: left;
        }
        .auth-container h2 {
            color: #dc3545; /* สีแดงสำหรับหัวข้อ */
        }
        .btn-back {
            background: #6c757d;
            color: white;
            margin-top: 20px;
            text-decoration: none;
            padding: 12px;
            display: inline-block;
            border-radius: 10px;
            font-weight: 600;
        }
    </style>
    </head>
    <body class="auth-bg">
    <div class="auth-container" style="max-width: 600px;">
      <h2><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16" style="margin-right: 8px;">
        <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
      </svg> Access Denied</h2>
      
      <div class="error-message-box">
        ' . htmlspecialchars($message) . '
      </div>
      
      <a href="javascript:history.back()" class="btn-back"> &laquo; ย้อนกลับ</a>
      <a href="index.php" class="btn-back" style="background: #0d6efd;">กลับหน้าหลัก</a>
    </div>
    </body>
    </html>';
    
    exit(); // หยุดการทำงานทันที
}


/**
 * -----------------------------------------------------------------
 * อัปเกรด checkPermission() ให้เรียกใช้ showErrorPage()
 * -----------------------------------------------------------------
 */
function checkPermission($permissionName) {
    if (!hasPermission($permissionName)) {
        http_response_code(403); // 403 Forbidden
        // เรียกใช้ฟังก์ชัน Error ใหม่ แทนการ die() แบบเดิม
        showErrorPage("คุณไม่มีสิทธิ์ [{$permissionName}]");
    }
}
?>