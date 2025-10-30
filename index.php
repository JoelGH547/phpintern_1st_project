<?php
require_once 'auth_guard.php'; // เรียก "ยาม" ตัวใหม่
require_once 'db_connect.php';

// ทุกคนที่มาถึงหน้านี้ได้ อย่างน้อยต้องมีสิทธิ์ 'user:read' 
// (เพราะ Visitor ก็ต้องเข้ามาดูได้)
checkPermission('user:read');

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
    
  <div class="action-bar">
      <?php if (hasPermission('user:create')): ?>
          <a href="crud_create.php" class="btn add">+ เพิ่มข้อมูล</a>
      <?php endif; ?>
      
      <?php if (hasPermission('permission:manage')): ?>
          <a href="manage_permissions.php" class="btn edit">จัดการสิทธิ์ผู้ใช้</a>
      <?php endif; ?>
  </div>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>ชื่อ</th>
        <th>นามสกุล</th>
        <th>ชื่อเล่น</th>
        <th>เพศ</th>
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
        <td><?= htmlspecialchars($u['nickname'] ?? 'N/A') ?></td>
        <td><?= ($u['gender'] == 'not_specified' || empty($u['gender'])) ? 'ไม่ระบุ' : htmlspecialchars($u['gender']) ?></td>
        <td>
          <?php if ($u['photo']): ?>
              <img src="uploads/<?= htmlspecialchars($u['photo']) ?>" class="avatar">
          <?php else: ?>
              <span class="no-photo">ไม่มีรูป</span>
          <?php endif; ?>
        </td>
        <td><?= $u['created_at'] ?></td>
        
        <td class="action-links">
          
          <a href="view_user.php?id=<?= $u['id'] ?>" class="btn view">ดูรายละเอียด</a>

          <?php if (hasPermission('user:update:others') || (hasPermission('user:update:self') && $u['id'] == $_SESSION['user_id'])): ?>
            <a href="crud_update.php?id=<?= $u['id'] ?>" class="btn edit">แก้ไข</a>
          <?php endif; ?>

          <?php if (hasPermission('user:delete')): ?>
            <a href="crud_delete.php?id=<?= $u['id'] ?>" class="btn delete" onclick="return confirm('ลบข้อมูลนี้หรือไม่?')">ลบ</a>
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