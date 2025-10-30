<?php
require_once 'db_connect.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $photoName = $user['photo'];

    // ถ้ามีการครอปรูปใหม่ (จาก cropper.js)
    if (!empty($_POST['cropped_image'])) {
        // ลบไฟล์เก่าก่อน
        if ($photoName && file_exists("uploads/$photoName")) {
            unlink("uploads/$photoName");
        }

        $uploadDir = 'uploads/';
        $photoName = time() . '.png';
        $targetFile = $uploadDir . $photoName;

        // แปลง base64 → รูปจริง
        $imageData = explode(',', $_POST['cropped_image']);
        $decoded = base64_decode($imageData[1]);
        file_put_contents($targetFile, $decoded);
    }

    $stmt = $pdo->prepare("UPDATE users SET name=?, lastname=?, email=?, photo=? WHERE id=?");
    $stmt->execute([$_POST['name'], $_POST['lastname'], $_POST['email'], $photoName, $_POST['id']]);

    header("Location: index.php");
    exit;
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>แก้ไขข้อมูลผู้ใช้</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<style>
body {
  background: linear-gradient(135deg, #74EBD5, #9FACE6);
  min-height: 100vh;
}
.container {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 0 25px rgba(0,0,0,0.1);
  padding: 30px;
  margin-top: 40px;
  max-width: 650px;
}
img#preview {
  width: 100%;
  max-height: 400px;
  display: none;
  border-radius: 10px;
  margin-bottom: 10px;
}
.crop-area {
  max-height: 400px;
  overflow: hidden;
}
.preview-img {
  display: block;
  margin: 10px auto 20px;
  width: 120px;
  height: 120px;
  border-radius: 10px;
  object-fit: cover;
  border: 3px solid #4facfe;
}
</style>
</head>
<body>
<div class="container">
  <h2 class="text-center mb-4">แก้ไขข้อมูลผู้ใช้</h2>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $user['id'] ?>">

    <div class="mb-3">
      <label class="form-label">ชื่อ</label>
      <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">นามสกุล</label>
      <input type="text" name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">อีเมล</label>
      <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required>
    </div>

    <div class="mb-3 text-center">
      <label class="form-label">รูปภาพปัจจุบัน:</label><br>
      <?php if ($user['photo']): ?>
          <img src="uploads/<?= htmlspecialchars($user['photo']) ?>" class="preview-img">
      <?php else: ?>
          <p class="text-muted">ยังไม่มีรูป</p>
      <?php endif; ?>
    </div>

    <div class="mb-3">
      <label class="form-label">เลือกรูปใหม่:</label>
      <input type="file" id="photoInput" class="form-control" accept="image/*">
    </div>

    <div class="crop-area">
      <img id="preview" alt="ตัวอย่างภาพ">
    </div>

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
let cropper;
const input = document.getElementById('photoInput');
const image = document.getElementById('preview');
const croppedField = document.getElementById('cropped_image');
const cropButton = document.getElementById('cropButton');

input.addEventListener('change', (e) => {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(event) {
      image.src = event.target.result;
      image.style.display = 'block';
      if (cropper) cropper.destroy();
      cropper = new Cropper(image, {
        aspectRatio: 1,
        viewMode: 1,
        background: false,
        zoomable: true,
        movable: true,
        autoCropArea: 1,
      });
    };
    reader.readAsDataURL(file);
  }
});

cropButton.addEventListener('click', () => {
  if (cropper) {
    const canvas = cropper.getCroppedCanvas({
      width: 400,
      height: 400,
    });
    croppedField.value = canvas.toDataURL('image/png');
    alert('✅ ครอปสำเร็จ! กด "อัปเดตข้อมูล" เพื่อบันทึก');
  } else {
    alert('กรุณาเลือกรูปก่อน');
  }
});
</script>
</body>
</html>
