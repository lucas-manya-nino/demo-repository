<?php

include 'includes/db.php';

//$conn = new mysqli("localhost", "root", "", "database_name");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$action = $_POST['action'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$username = $_POST['username'] ?? '';
$phone = $_POST['phone'] ?? '';

if ($action === 'signup') {
    $userInfos = array(
        'username' => $username,
        'email' => $email,
        'phone' => $phone,
        'password' => $password,
    );
    registerNewClient($userInfos);
    exit;
} elseif ($action === 'login') {
    loginUser($username,$password);
    exit;
}
?>