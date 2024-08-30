<?php
session_start();
include 'dbconnect.php';

$username = $_POST['username'];
$password = $_POST['password'];

// Prepare a statement to get both userID and hashed password
$stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($userID, $hashed_password);

if ($stmt->num_rows > 0) {
    $stmt->fetch();

    if (password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $userID; // Corrected session variable name
        $_SESSION['username'] = $username;
        header('Location: home.php');
        exit();
    } else {
        header('Location: index.php?error=1'); // General error message
        exit();
    }
} else {
    header('Location: index.php?error=1'); // General error message
    exit();
}

$stmt->close();
$conn->close();
?>