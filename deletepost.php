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
    header('Location: profile.php?error=missing_post_id');
    exit();
}

$postID = $_GET['id'];
$userID = $_SESSION['user_id']; // Get the logged-in user's ID

// Validate postID
if (!filter_var($postID, FILTER_VALIDATE_INT)) {
    header('Location: profile.php?error=invalid_post_id');
    exit();
}

// Prepare the SQL query to delete the post
$stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND userID = ?");
if ($stmt === false) {
    header('Location: profile.php?error=' . urlencode('Prepare failed: ' . $conn->error));
    exit();
}

$stmt->bind_param("ii", $postID, $userID);

// Execute the query and check for success
if ($stmt->execute()) {
    // Check if any rows were affected (i.e., if a post was deleted)
    if ($stmt->affected_rows > 0) {
        // Get the current total_post count
        $stmt = $conn->prepare("SELECT total_post FROM users WHERE id = ?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $stmt->bind_result($total_post);
        $stmt->fetch();
        $stmt->close();

        // Ensure total_post does not go below 0
        $new_total_post = max(0, $total_post - 1);

        // Update total_post count in the users table
        $stmt = $conn->prepare("UPDATE users SET total_post = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_total_post, $userID);
        $stmt->execute();
        $stmt->close();

        // Redirect to the profile page after successful deletion
        header("Location: profile.php?success=post_deleted");
    } else {
        // No post was deleted, either because it didn't exist or didn't belong to the user
        header('Location: profile.php?error=no_post_found');
    }
} else {
    // Handle the error if the execution fails
    header('Location: profile.php?error=' . urlencode('Execute failed: ' . $stmt->error));
}

// Close the statement
$stmt->close();

// Close the database connection
$conn->close();

exit();
?>