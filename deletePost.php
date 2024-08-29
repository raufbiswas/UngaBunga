<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in and if post ID is provided
if (!isset($_SESSION['userID']) || !isset($_GET['id'])) {
    header('Location: home.php');
    exit();
}

$userID = $_SESSION['userID'];
$postID = intval($_GET['id']);

// Check if post exists and belongs to the user
$stmt = $conn->prepare("SELECT userID FROM Posts WHERE id = ?");
$stmt->bind_param("i", $postID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Post does not exist
    header('Location: home.php');
    exit();
}

$post = $result->fetch_assoc();

if ($post['userID'] !== $userID) {
    // Post does not belong to the user
    header('Location: home.php');
    exit();
}

// Proceed to delete the post
$deleteStmt = $conn->prepare("DELETE FROM Posts WHERE id = ?");
$deleteStmt->bind_param("i", $postID);

if ($deleteStmt->execute()) {
    // Redirect to the profile page after deletion
    header('Location: profile.php');
    exit();
} else {
    die('Delete failed: ' . $conn->error);
}

$deleteStmt->close();
$conn->close();
?>