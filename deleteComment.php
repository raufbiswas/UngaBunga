<?php
session_start();
include 'dbconnect.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$commentID = $_GET['id'] ?? null;
$userID = $_SESSION['user_id'] ?? null;

// Validate commentID
if (!filter_var($commentID, FILTER_VALIDATE_INT) || !filter_var($userID, FILTER_VALIDATE_INT)) {
    header('Location: profile.php?error=invalid_comment_id');
    exit();
}

// Prepare the SQL query to delete the comment
$stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND (userID = ? OR EXISTS (SELECT 1 FROM posts WHERE id = (SELECT postID FROM comments WHERE id = ?) AND userID = ?))");
if ($stmt === false) {
    header('Location: profile.php?error=' . urlencode('Prepare failed: ' . $conn->error));
    exit();
}

$stmt->bind_param("iiii", $commentID, $userID, $commentID, $userID);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        header("Location: profile.php?success=comment_deleted");
    } else {
        header('Location: profile.php?error=no_comment_found');
    }
} else {
    header('Location: profile.php?error=' . urlencode('Execute failed: ' . $stmt->error));
}

$stmt->close();
$conn->close();
exit();
?>