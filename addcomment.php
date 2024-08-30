<?php
session_start();
include 'dbconnect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and validate the postID and commentText from POST request
    $postID = isset($_POST['postID']) ? intval($_POST['postID']) : null;
    $commentText = isset($_POST['commentText']) ? trim($_POST['commentText']) : null;
    $userID = $_SESSION['user_id'];

    // Validate postID
    if (!filter_var($postID, FILTER_VALIDATE_INT)) {
        die('Invalid post ID.');
    }

    // Validate commentText
    if (empty($commentText)) {
        die('Comment text cannot be empty.');
    }

    // Insert comment into the database
    $stmt = $conn->prepare("INSERT INTO comments (postID, userID, commentText) VALUES (?, ?, ?)");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("iis", $postID, $userID, $commentText);
    if (!$stmt->execute()) {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    }

    $stmt->close();

    // Redirect back to the profile page
    header("Location: profile.php?userID=$userID");
    exit();
}
?>