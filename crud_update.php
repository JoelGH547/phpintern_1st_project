<?php
require_once 'auth_guard.php'; // (V.1)
require_once 'db_connect.php';



$id_to_edit = $_GET['id'] ?? null;
$logged_in_user_id = $_SESSION['user_id'];

if (!$id_to_edit) { header("Location: index.php"); exit; }

// [ตรรกะสิทธิ์ V.1]
if (hasPermission('user:update:others')) { /* Master/Admin */ } 
else if (hasPermission('user:update:self') && $id_to_edit == $logged_in_user_id) { /* User แก้ไขตัวเอง */ } 
else { checkPermission('user:update:others'); /* Access Denied */ }

$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$id_to_edit]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) { header("Location: index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $photoName = $user['photo'];
    if (!empty($_POST['cropped_image'])) {
        if ($photoName && file_exists("uploads/$photoName")) { unlink("uploads/$photoName"); }
        $uploadDir = 'uploads/'; $photoName = time() . '.png'; $targetFile = $uploadDir . $photoName;
        $imageData = explode(',', $_POST['cropped_image']); $decoded = base64_decode($imageData[1]); file_put_contents($targetFile, $decoded);
    }

    // --- [ 1. อัปเกรด PHP ] ---
    // เพิ่ม gender_custom=?
    $sql = "UPDATE users SET 
                name=?, lastname=?, email=?, photo=?,
                nickname=?, phone=?, birthday=?, address=?, 
                weight=?, height=?, gender=?, gender_custom=?
            WHERE id=?";
    
    $stmt = $pdo->prepare($sql);

    // [ใหม่] ตรรกะสำหรับ gender_custom
    // ถ้าเพศ ไม่ใช่ 'other' ให้ล้างค่า custom ทิ้ง
    $gender_custom = ($_POST['gender'] === 'other') ? $_POST['gender_custom'] : null;

    $stmt->execute([
        $_POST['name'], $_POST['lastname'], $_POST['email'], $photoName,
        $_POST['nickname'], $_POST['phone'],
        empty($_POST['birthday']) ? null : $_POST['birthday'],
        $_POST['address'],
        empty($_POST['weight']) ? null : $_POST['weight'],
        empty($_POST['height']) ? null : $_POST['height'],
        $_POST['gender'],
        $gender_custom, // <-- เพิ่มตัวแปรนี้
        $_POST['id']
    ]);
    header("Location: index.php"); exit;
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8"><title>แก้ไขข้อมูลผู้ใช้</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css"> 
<style>
body { min-height: 100vh; }
.container { background: #fff; border-radius: 16px; box-shadow: 0 0 25px rgba(0,0,0,0.1); padding: 30px; margin-top: 40px; max-width: 800px; }
img#preview { width: 100%; max-height: 400px; display: none; border-radius: 10px; margin-bottom: 10px; }
.crop-area { max-height: 400px; overflow: hidden; }
.preview-img { display: block; margin: 10px auto 20px; width: 120px; height: 120px; border-radius: 10px; object-fit: cover; border: 3px solid #4facfe; }
.btn-primary { background: #4facfe; border-color: #4facfe; }
.btn-success { background: #00b09b; border-color: #00b09b; }
.btn-secondary { background: #6c757d; border-color: #6c757d; }
</style>
</head>
<body>
<div class="container">
  <h2 class="text-center mb-4">แก้ไขข้อมูลผู้ใช้</h2>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $user['id'] ?>">
    <div class="row">
        <div class="col-md-6 mb-3"><label class="form-label">ชื่อ</label><input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="form-control" required></div>
        <div class="col-md-6 mb-3"><label class="form-label">นามสกุล</label><input type="text" name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>" class="form-control" required></div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3"><label class="form-label">อีเมล</label><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required></div>
        <div class="col-md-6 mb-3"><label class="form-label">ชื่อเล่น</label><input type="text" name="nickname" value="<?= htmlspecialchars($user['nickname'] ?? '') ?>" class="form-control"></div>
    </div>
    <hr class="my-3">
    <div class="row">
        <div class="col-md-6 mb-3"><label class="form-label">เบอร์โทร</label><input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" class="form-control"></div>
        <div class="col-md-6 mb-3"><label class="form-label">วันเกิด</label><input type="date" name="birthday" value="<?= htmlspecialchars($user['birthday'] ?? '') ?>" class="form-control"></div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3"><label class="form-label">น้ำหนัก (กก.)</label><input type="number" name="weight" step="0.01" value="<?= htmlspecialchars($user['weight'] ?? '') ?>" class="form-control" placeholder="e.g., 65.50"></div>
        <div class="col-md-4 mb-3"><label class="form-label">ส่วนสูง (ซม.)</label><input type="number" name="height" step="0.01" value="<?= htmlspecialchars($user['height'] ?? '') ?>" class="form-control" placeholder="e.g., 170.25"></div>
        
        <div class="col-md-4 mb-3">
            <label class="form-label">เพศ</label>
            <select name="gender" id="gender_select" class="form-select">
                <option value="not_specified" <?= ($user['gender'] ?? 'not_specified') == 'not_specified' ? 'selected' : '' ?>>ไม่ระบุ</option>
                <option value="male" <?= ($user['gender'] ?? '') == 'male' ? 'selected' : '' ?>>ชาย</option>
                <option value="female" <?= ($user['gender'] ?? '') == 'female' ? 'selected' : '' ?>>หญิง</option>
                <option value="other" <?= ($user['gender'] ?? '') == 'other' ? 'selected' : '' ?>>อื่นๆ (ระบุ)</option>
            </select>
        </div>
    </div> <div class="mb-3" id="gender_custom_wrapper" style="display: none;">
        <label class="form-label">ระบุเพศ (อื่นๆ)</label>
        <input type="text" name="gender_custom" id="gender_custom_input" 
               value="<?= htmlspecialchars($user['gender_custom'] ?? '') ?>" class="form-control">
    </div>
    <div class="mb-3"><label class="form-label">ที่อยู่</label><textarea name="address" rows="3" class="form-control"><?= htmlspecialchars($user['address'] ?? '') ?></textarea></div>
    <hr class="my-3">
    <div class="mb-3 text-center">
      <label class="form-label">รูปภาพปัจจุบัน:</label><br>
      <?php if ($user['photo']): ?><img src="uploads/<?= htmlspecialchars($user['photo']) ?>" class="preview-img"><?php else: ?><p class="text-muted">ยังไม่มีรูป</p><?php endif; ?>
    </div>
    <div class="mb-3"><label class="form-label">เลือกรูปใหม่:</label><input type="file" id="photoInput" class="form-control" accept="image/*"></div>
    <div class="crop-area"><img id="preview" alt="ตัวอย่างภาพ"></div>
    <input type="hidden" name="cropped_image" id="cropped_image">
    <div class="text-center mt-4">
      <button type="button" id="cropButton" class="btn btn-primary me-2">ครอปภาพ</button>
      <button type="submit" class="btn btn-success">อัปเดตข้อมูล</button>
      <a href="index.php" class="btn btn-secondary">กลับหน้าแรก</a>
    </div>
  </form>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
// --- [ 3. อัปเกรด JavaScript ] ---
// (วาง JS ของ Cropper เดิมไว้ก่อน)
let cropper; const input = document.getElementById('photoInput'); const image = document.getElementById('preview'); const croppedField = document.getElementById('cropped_image'); const cropButton = document.getElementById('cropButton');
input.addEventListener('change', (e) => { const file = e.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = function(event) { image.src = event.target.result; image.style.display = 'block'; if (cropper) cropper.destroy(); cropper = new Cropper(image, { aspectRatio: 1, viewMode: 1 }); }; reader.readAsDataURL(file); } });
cropButton.addEventListener('click', () => { if (cropper) { const canvas = cropper.getCroppedCanvas({ width: 400, height: 400 }); croppedField.value = canvas.toDataURL('image/png'); alert('✅ ครอปสำเร็จ!'); } else { alert('กรุณาเลือกรูปก่อน'); } });


// --- [ใหม่] โค้ดสำหรับซ่อน/แสดง ช่อง "ระบุเพศ" ---
const genderSelect = document.getElementById('gender_select');
const customWrapper = document.getElementById('gender_custom_wrapper');
const customInput = document.getElementById('gender_custom_input');

function toggleCustomGenderField() {
    if (genderSelect.value === 'other') {
        customWrapper.style.display = 'block'; // แสดง
    } else {
        customWrapper.style.display = 'none'; // ซ่อน
        customInput.value = ''; // ล้างค่า
    }
}
// 3.1 เรียกฟังก์ชันเมื่อ "เปลี่ยน" ค่า
genderSelect.addEventListener('change', toggleCustomGenderField);

// 3.2 เรียกฟังก์ชัน 1 ครั้ง "ตอนโหลดหน้า" (สำคัญมากสำหรับหน้า Edit)
// เราใช้ DOMContentLoaded เพื่อให้แน่ใจว่า HTML โหลดเสร็จก่อน
document.addEventListener('DOMContentLoaded', toggleCustomGenderField);
</script>
</body>
</html>