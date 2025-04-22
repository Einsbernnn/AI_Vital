<?php
session_start();

$users = [
    'admin' => 'Passw0rd!', // Admin credentials
    'clerk1' => 'Passw0rd!', // Clerk 1 credentials
    'clerk2' => 'Passw0rd!' // Clerk 2 credentials
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (isset($users[$username]) && $users[$username] === $password) {
        if ($username === 'admin') {
            $_SESSION['admin_logged_in'] = true;
        } elseif ($username === 'clerk1') {
            $_SESSION['clerk1_logged_in'] = true;
        } elseif ($username === 'clerk2') {
            $_SESSION['clerk2_logged_in'] = true;
        }
        header('Location: index.php');
    } else {
        echo "<script>alert('Invalid username or password'); window.location.href='admin_login.php';</script>";
    }
}
?>
