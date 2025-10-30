<?php
require_once 'auth_check.php'; // 1. เรียกยาม (เช็ค Login + โหลดฟังก์ชัน isAdmin)
require_once 'db_connect.php';

// ( auth_check.php จัดการเช็ค user_id แล้ว ไม่ต้องเช็คซ้ำ)

$users = $pdo->query("SELECT * FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>รายการผู้ใช้</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header class="navbar">
  <div class="logo">ระบบจัดการผู้ใช้</div>
  <div class="user-info">
    <span>สวัสดี, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
    <a href="logout.php" class="logout-btn">ออกจากระบบ</a>
  </div>
</header>

<main>

  <?php if (isAdmin()): ?>
  <div class="action-bar">
      <a href="crud_create.php" class="btn add">+ เพิ่มข้อมูล</a>
  </div>
  <?php endif; ?>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>ชื่อ</th>
        <th>นามสกุล</th>
        <th>อีเมล</th>
        <th>รูปภาพ</th>
        <th>วันที่สร้าง</th>
        <th>จัดการ</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?= $u['id'] ?></td>
        <td><?= htmlspecialchars($u['name']) ?></td>
        <td><?= htmlspecialchars($u['lastname']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td>
          <?php if ($u['photo']): ?>
              <img src="uploads/<?= htmlspecialchars($u['photo']) ?>" class="avatar">
          <?php else: ?>
              <span class="no-photo">ไม่มีรูป</span>
          <?php endif; ?>
        </td>
        <td><?= $u['created_at'] ?></td>
        
        <td class="action-links">
          <?php if (isAdmin()): ?>
            <a href="crud_update.php?id=<?= $u['id'] ?>" class="btn edit">แก้ไข</a>
            <a href="crud_delete.php?id=<?= $u['id'] ?>" class="btn delete" onclick="return confirm('ลบข้อมูลนี้หรือไม่?')">ลบ</a>
          
          <?php elseif (isUser() && $u['id'] == $_SESSION['user_id']): ?>
            <a href="crud_update.php?id=<?= $u['id'] ?>" class="btn edit">แก้ไข</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</main>

<footer>
  <p>© <?= date('Y') ?> ระบบจัดการผู้ใช้ | Designed by JoEl</p>
</footer>

</body>
</html>