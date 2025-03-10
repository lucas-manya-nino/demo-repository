<?php

include 'includes/db.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$action = $_POST['action'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$username = $_POST['username'] ?? '';
$phone = $_POST['phone'] ?? '06-06-06-06-06';

if ($action === 'signup') {
    $userInfos = array(
        'username' => $username,
        'email' => $email,
        'phone_num' => $phone,
        'password' => $password,
    );
    if (registerNewClient($userInfos)){
        header("Location: index.php");
    } else {
        echo("<script>alert('auth failed')</script>");
        header("Location: loginpage.php");
    }
    exit();
} elseif ($action === 'login') {
    if (loginUser($username,$password)){
        header("Location: index.php");
    } else {
        echo("<script>alert('auth failed')</script>");
        header("Location: loginpage.php");
    }
    exit();
}
?>