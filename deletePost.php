<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['userID']) || !isset($_GET['id'])) {
    header('Location: home.php');
    exit();
}

$userID = $_SESSION['userID'];
$postID = intval($_GET['id']);

// Check if post belongs to user
$stmt = $conn->prepare("SELECT userID FROM Posts WHERE id = ?");
$stmt->bind_param("i", $postID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: home.php');
    exit();
}

$post = $result->fetch_assoc();

if ($post['userID'] !== $userID) {
    header('Location: home.php');
    exit();
}

// Delete the post
$deleteStmt = $conn->prepare("DELETE FROM Posts WHERE id = ?");
$deleteStmt->bind_param("i", $postID);
if ($deleteStmt->execute()) {
    header('Location: profile.php');
} else {
    die('Delete failed: ' . $conn->error);
}

$deleteStmt->close();
$conn->close();
?>