<?php
require_once 'auth_guard.php';
require_once 'db_connect.php';

// หน้านี้ต้องมีสิทธิ์ 'permission:manage' เท่านั้น
checkPermission('permission:manage');

// ดึงรายชื่อผู้ใช้ทั้งหมด
$stmt_users = $pdo->query("SELECT u.id, u.name, u.lastname, u.email, r.role_name 
                           FROM users u 
                           LEFT JOIN roles r ON u.role_id = r.role_id
                           ORDER BY u.id ASC");
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// ดึงรายชื่อบทบาททั้งหมด
$roles = $pdo->query("SELECT * FROM roles WHERE role_id != 1")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>จัดการสิทธิ์ผู้ใช้</title>
<link rel="stylesheet" href="style.css">
<style>
    main { padding: 20px; }
    .btn.save { background: linear-gradient(45deg, #00b09b, #96c93d); }
    .current-role { font-weight: bold; }
</style>
</head>
<body>

<header class="navbar">
  <div class="logo">ระบบจัดการผู้ใช้ (Master Admin)</div>
  <div class="user-info">
    <span>สวัสดี, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
    <a href="index.php" class="logout-btn" style="background: #2196F3;">กลับหน้าหลัก</a>
  </div>
</header>

<main>
  <h2>จัดการสิทธิ์ผู้ใช้</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>ชื่อ-นามสกุล</th>
        <th>Email</th>
        <th>บทบาทปัจจุบัน</th>
        <th>เปลี่ยนบทบาท</th>
        <th>บันทึก</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <form action="update_role_script.php" method="POST">
          <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
          <td><?= $u['id'] ?></td>
          <td><?= htmlspecialchars($u['name'] . ' ' . $u['lastname']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td class="current-role"><?= htmlspecialchars($u['role_name'] ?? 'N/A') ?></td>
          <td>
            <?php if ($u['id'] == $_SESSION['user_id']): ?>
                <span style="color: red;">(ไม่สามารถเปลี่ยนสิทธิ์ตัวเองได้)</span>
            <?php else: ?>
                <select name="new_role_id" style="padding: 5px;">
                  <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['role_id'] ?>">
                      <?= htmlspecialchars($role['role_name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                <button type="submit" class="btn save">บันทึก</button>
            <?php endif; ?>
          </td>
        </form>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>

</body>
</html>