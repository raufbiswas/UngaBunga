<?php
session_start();
include 'dbconnect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Check if the post ID is provided via the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: home.php?error=missing_post_id');
    exit();
}

$postID = $_GET['id'];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the title and content from the POST request
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    // Validate the input
    if (empty($title) || empty($content)) {
        header('Location: editpost.php?id=' . htmlspecialchars($postID) . '&error=empty_fields');
        exit();
    }

    // Prepare the SQL query to update the post
    $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, updated = NOW() WHERE id = ? AND userID = ?");
    if ($stmt === false) {
        header('Location: editpost.php?id=' . htmlspecialchars($postID) . '&error=' . urlencode('Prepare failed: ' . $conn->error));
        exit();
    }
    
    $userID = $_SESSION['user_id']; // Get the logged-in user's ID
    $stmt->bind_param("ssii", $title, $content, $postID, $userID);

    // Execute the query and check for success
    if ($stmt->execute()) {
        // Redirect to the profile page after successful update
        header("Location: profile.php?success=post_updated");
        exit();
    } else {
        // Handle the error if the execution fails
        header('Location: editpost.php?id=' . htmlspecialchars($postID) . '&error=' . urlencode('Execute failed: ' . $stmt->error));
        exit();
    }

    // Close the statement
    $stmt->close();
} else {
    // Redirect if the form was not submitted correctly
    header('Location: home.php');
    exit();
}

// Close the database connection
$conn->close();
?>