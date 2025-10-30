<?php
require_once 'auth_check.php'; // 1. เรียกยาม
require_once 'db_connect.php';

// 2. ตรวจสอบสิทธิ์ (Admin เท่านั้น)
if (!isAdmin()) {
    http_response_code(403);
    die("Access Denied. You do not have permission to create users.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $photoName = null;

    // ตรวจสอบว่ามีข้อมูลภาพ base64 จาก cropper.js
    if (!empty($_POST['cropped_image'])) {
        $uploadDir = 'uploads/';
        $photoName = time() . '.png';
        $targetFile = $uploadDir . $photoName;

        // แปลง base64 → รูปจริง
        $imageData = explode(',', $_POST['cropped_image']);
        $decoded = base64_decode($imageData[1]);
        file_put_contents($targetFile, $decoded);
    }

    $stmt = $pdo->prepare("INSERT INTO users (name, lastname, email, photo) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['name'], $_POST['lastname'], $_POST['email'], $photoName]);

    header("Location: index.php");
    exit;
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>เพิ่มข้อมูลผู้ใช้</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<style>
/* ... (style เหมือนเดิม) ... */
body {
  background: linear-gradient(135deg, #74EBD5, #9FACE6);
  color: #333;
  min-height: 100vh;
}
.container {
  background: white;
  border-radius: 16px;
  box-shadow: 0 0 25px rgba(0,0,0,0.1);
  padding: 30px;
  margin-top: 40px;
  max-width: 600px;
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
</style>
</head>
<body>
<div class="container">
  <h2 class="text-center mb-4">เพิ่มข้อมูลผู้ใช้</h2>
  <form method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">ชื่อ</label>
      <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">นามสกุล</label>
      <input type="text" name="lastname" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">อีเมล</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">เลือกรูปภาพ</label>
      <input type="file" id="photoInput" class="form-control" accept="image/*">
    </div>

    <div class="crop-area">
      <img id="preview" alt="ตัวอย่างภาพ">
    </div>

    <input type="hidden" name="cropped_image" id="cropped_image">

    <div class="text-center mt-3">
      <button type="button" id="cropButton" class="btn btn-primary me-2">ครอปภาพ</button>
      <button type="submit" class="btn btn-success">บันทึกข้อมูล</button>
      <a href="index.php" class="btn btn-secondary">กลับหน้าแรก</a>
    </div>
  </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
/* ... (javascript เหมือนเดิม) ... */
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
    alert('✅ ครอปสำเร็จ! กด "บันทึกข้อมูล" เพื่ออัปโหลด');
  } else {
    alert('กรุณาเลือกรูปก่อน');
  }
});
</script>
</body>
</html>