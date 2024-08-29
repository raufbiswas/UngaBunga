<?php
session_start();
include 'dbconnect.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postID = $_GET['postID'];
    $commentID = $_GET['commentID'];
    $replyText = $_POST['reply'];
    $userID = $_SESSION['user_id'];

    // Validate postID and commentID
    if (!filter_var($postID, FILTER_VALIDATE_INT) || !filter_var($commentID, FILTER_VALIDATE_INT)) {
        die('Invalid post or comment ID.');
    }

    // Insert reply into the database
    $stmt = $conn->prepare("INSERT INTO comments (postID, userID, commentText) VALUES (?, ?, ?)");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    
    $stmt->bind_param("iis", $postID, $userID, $replyText);
    if (!$stmt->execute()) {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    }

    $stmt->close();

    // Redirect back to the profile page
    header("Location: profile.php");
    exit();
}
?>