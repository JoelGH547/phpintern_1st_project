<?php
require_once 'auth_guard.php'; // เรียก "ยาม" ตัวใหม่
require_once 'db_connect.php';

// เช็คว่ามีสิทธิ์ "อ่าน" หรือไม่ (ทุกคนที่ Login ควรมี)
checkPermission('user:read');

$id_to_view = $_GET['id'] ?? null;
if (!$id_to_view) { header("Location: index.php"); exit; }

$stmt = $pdo->prepare("SELECT u.*, r.role_name 
                       FROM users u
                       LEFT JOIN roles r ON u.role_id = r.role_id
                       WHERE u.id=?");
$stmt->execute([$id_to_view]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) { header("Location: index.php"); exit; }

// ฟังก์ชันเล็กๆ เพื่อแสดงผล "N/A" ถ้าข้อมูลว่าง
function display($value) {
    return !empty($value) ? htmlspecialchars($value) : '<span class="text-muted">N/A</span>';
}

// ฟังก์ชันคำนวณอายุ
function calculateAge($birthday) {
    if (empty($birthday)) {
        return 'N/A';
    }
    try {
        $bday = new DateTime($birthday);
        $today = new DateTime();
        $diff = $today->diff($bday);
        return $diff->y;
    } catch (Exception $e) {
        return 'N/A'; // ในกรณีที่ format วันที่ผิด
    }
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>รายละเอียดผู้ใช้: <?= display($user['name']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
    /* สไตล์เฉพาะหน้านี้ */
    body {
        /* ไม่ต้องใส่ background ที่นี่ เพราะ style.css คุมอยู่ */
        min-height: 100vh;
    }
    .profile-card {
        max-width: 800px;
        margin: 40px auto;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        animation: fadeUp 1s ease;
    }
    .profile-header {
        background: linear-gradient(90deg, #667eea, #764ba2);
        color: white;
        padding: 30px;
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 4px solid #fff;
        object-fit: cover;
    }
    .profile-name h2 { margin: 0; font-weight: 600; }
    .profile-name p { margin: 0; font-size: 1.1rem; opacity: 0.9; }
    .profile-body { padding: 30px; }
    .detail-group { margin-bottom: 25px; }
    .detail-group h5 {
        font-weight: 600;
        color: #764ba2;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 8px;
        margin-bottom: 15px;
    }
    .detail-item { margin-bottom: 10px; }
    .detail-item strong { color: #333; min-width: 120px; display: inline-block; }
    .detail-item span { color: #555; }
    .text-muted { color: #999 !important; font-style: italic; }
</style>
</head>
<body>

<div class="container">
    <div class="profile-card">
        <div class="profile-header">
            <img src="uploads/<?= $user['photo'] ? htmlspecialchars($user['photo']) : 'default_avatar.png' ?>" 
                 alt="Avatar" class="profile-avatar">
            <div class="profile-name">
                <h2><?= display($user['name']) ?> <?= display($user['lastname']) ?></h2>
                <p><?= $user['nickname'] ? '(@' . display($user['nickname']) . ')' : '' ?></p>
            </div>
        </div>
        
        <div class="profile-body">
            
            <div class="detail-group">
                <h5>ข้อมูลติดต่อ</h5>
                <div class="row">
                    <div class="col-md-6 detail-item">
                        <strong>อีเมล:</strong> <span><?= display($user['email']) ?></span>
                    </div>
                    <div class="col-md-6 detail-item">
                        <strong>เบอร์โทร:</strong> <span><?= display($user['phone']) ?></span>
                    </div>
                </div>
            </div>

            <div class="detail-group">
                <h5>ข้อมูลส่วนตัว</h5>
                <div class="row">
                    <div class="col-md-6 detail-item">
                        <strong>วันเกิด:</strong> <span><?= display($user['birthday']) ?></span>
                    </div>
                    <div class="col-md-6 detail-item">
                        <strong>อายุ:</strong> <span><?= calculateAge($user['birthday']) ?> ปี</span>
                    </div>
                    <div class="col-md-6 detail-item">
                        <strong>เพศ:</strong> <span><?= display($user['gender']) ?></span>
                    </div>
                    <div class="col-md-6 detail-item">
                        <strong>บทบาท:</strong> <span><?= display($user['role_name']) ?></span>
                    </div>
                    <div class="col-md-6 detail-item">
                        <strong>ส่วนสูง:</strong> <span><?= display($user['height']) ?> ซม.</span>
                    </div>
                    <div class="col-md-6 detail-item">
                        <strong>น้ำหนัก:</strong> <span><?= display($user['weight']) ?> กก.</span>
                    </div>
                </div>
            </div>

            <div class="detail-group">
                <h5>ที่อยู่</h5>
                <div class="row">
                    <div class="col-12 detail-item">
                        <span><?= $user['address'] ? nl2br(htmlspecialchars($user['address'])) : '<span class="text-muted">N/A</span>' ?></span>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-secondary" style="background: #6c757d;">กลับหน้าแรก</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>