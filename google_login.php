<?php
require_once 'vendor/autoload.php';
session_start();

$client = new GoogleClient();
$client->setClientId('725394908853-2ld1gfcd28rakeiqd7jkrm8h8r22sqfg.apps.googleusercontent.com');
$client->setClientSecret('725394908853-2ld1gfcd28rakeiqd7jkrm8h8r22sqfg.apps.googleusercontent.com');
$client->setRedirectUri('http://localhost/google_callback.php');
$client->addScope("email");
$client->addScope("profile");

$login_url = $client->createAuthUrl();
header('Location: ' . filter_var($login_url, FILTER_SANITIZE_URL));
exit;
