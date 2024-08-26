<?php
session_start();
include 'dbconnect.php';

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($hashed_password);

if ($stmt->num_rows > 0) {
    $stmt->fetch();

    if (password_verify($password, $hashed_password)) {
        $_SESSION['username'] = $username;
        header('Location: home.php');
    } else {
        header('Location: index.php?error=1');
    }
} else {
    header('Location: index.php?error=1');
}

$stmt->close();
$conn->close();
?>