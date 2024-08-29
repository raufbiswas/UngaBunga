<?php
session_start();
include 'dbconnect.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Debugging: Print POST data
echo '<pre>';
print_r($_POST);
echo '</pre>';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and validate the postID and commentText from POST request
    $postID = isset($_POST['postID']) ? intval($_POST['postID']) : null;
    $commentText = isset($_POST['commentText']) ? trim($_POST['commentText']) : null;
    $userID = $_SESSION['user_id'];

    // Debugging: Check values
    if ($postID === null) {
        echo 'Post ID is missing or invalid.';
    } elseif (empty($commentText)) {
        echo 'Comment text is empty.';
    } else {
        echo 'Post ID: ' . $postID . '<br>';
        echo 'Comment Text: ' . htmlspecialchars($commentText);
    }

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