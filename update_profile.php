<?php
session_start();
include 'dbconnect.php';

$userID = $_SESSION['userID'];

// Validate and sanitize input
$fname = filter_var(trim($_POST['fname']), FILTER_SANITIZE_STRING);
$lname = filter_var(trim($_POST['lname']), FILTER_SANITIZE_STRING);
$username = filter_var(trim($_POST['username']), FILTER_SANITIZE_STRING);
$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$phone = filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING);
$password = $_POST['password'];

// Simple validation for email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: profile.php?error=invalid_email');
    exit();
}

// Check if username already exists
$checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
$checkStmt->bind_param("si", $username, $userID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    header('Location: profile.php?error=username_taken');
    exit();
}

// Prepare and execute the update query
if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $query = "UPDATE users SET first_name = ?, last_name = ?, username = ?, email = ?, phone = ?, password = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssi", $fname, $lname, $username, $email, $phone, $hashed_password, $userID);
} else {
    $query = "UPDATE users SET first_name = ?, last_name = ?, username = ?, email = ?, phone = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssi", $fname, $lname, $username, $email, $phone, $userID);
}

if ($stmt->execute()) {
    $_SESSION['username'] = $username; // Update session username if changed
    $_SESSION['user_email'] = $email;  // Optionally update session email
    header('Location: profile.php?success=update');
} else {
    header('Location: profile.php?error=update_failed');
}

$stmt->close();
$conn->close();
?>