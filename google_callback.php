<?php
require_once 'vendor/autoload.php';
require_once 'db_connect.php';
session_start();

$client = new Google_Client();
$client->setClientId('YOUR_GOOGLE_CLIENT_ID');
$client->setClientSecret('YOUR_GOOGLE_CLIENT_SECRET');
$client->setRedirectUri('http://localhost/google_callback.php');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);
        $google_service = new Google_Service_Oauth2($client);
        $data = $google_service->userinfo->get();

        $email = $data['email'];
        $name = $data['name'];
        $photo = $data['picture'];

        // ตรวจสอบว่ามี user อยู่แล้วหรือยัง
        $check = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $check->execute([$email]);
        $user = $check->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // สร้าง user ใหม่
            $stmt = $pdo->prepare("INSERT INTO users (name, email, photo, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $photo, null]);
            $user_id = $pdo->lastInsertId();
        } else {
            $user_id = $user['id'];
        }

        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_photo'] = $photo;

        header("Location: index.php");
        exit;
    }
}
