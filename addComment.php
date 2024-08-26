<?php
session_start();
include 'dbconnect.php';

if (!isset($_SESSION['userID']) || !isset($_POST['postID']) || !isset($_POST['commentText'])) {
    header('Location: home.php');
    exit();
}

$userID = $_SESSION['userID'];
$postID = $_POST['postID'];
$commentText = $_POST['commentText'];

// Insert the comment into the database
$stmt = $conn->prepare("INSERT INTO comments (postID, userID, commentText) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $postID, $userID, $commentText);
if ($stmt->execute()) {
    header('Location: profile.php?username=' . htmlspecialchars($_GET['username']));
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>