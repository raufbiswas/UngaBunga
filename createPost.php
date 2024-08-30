<?php
session_start();
include 'dbconnect.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $userID = $_SESSION['user_id'];

    // Debugging output
    if (empty($title) || empty($content)) {
        die('Title or content cannot be empty.');
    }

    echo "Title: " . htmlspecialchars($title) . "<br>";
    echo "Content: " . htmlspecialchars($content) . "<br>";
    echo "User ID: " . htmlspecialchars($userID) . "<br>";

    // Insert post into the database
    $stmt = $conn->prepare("INSERT INTO posts (userID, title, content) VALUES (?, ?, ?)");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    
    $stmt->bind_param("iss", $userID, $title, $content);
    if (!$stmt->execute()) {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    }

    $stmt->close();

    // Update total_post count in the users table
    $stmt = $conn->prepare("UPDATE users SET total_post = total_post + 1 WHERE id = ?");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    
    $stmt->bind_param("i", $userID);
    if (!$stmt->execute()) {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    }

    $stmt->close();

    // Redirect to the home page after successful submission
    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./CSS/design.css">
    <title>Create Post - UngaBunga</title>
</head>
<body>
    <div class="container">
        <div class="main">
            <span>
                <a href="home.php" class="logo">UngaBunga Blog</a>
            </span>
            <span>
                <a href="profile.php" class="profileicon">Hi, <?= htmlspecialchars($_SESSION['username']) ?>!</a>
            </span>
        </div>

        <div class="header">
            <a href="home.php" class="btn-secondary">Home</a>
            <a href="logout.php" class="btn-secondary">Log Out</a>
        </div>
        
        <div class="post">
            <form action="createpost.php" method="post">
                <label for="title">Title:</label><br>
                <input class="textbox" type="text" name="title" required><br>
                
                <label for="content">Content:</label><br>
                <textarea class="contentbox" id="contentbox" name="content" rows="15" required></textarea><br>

                <button type="submit" class="btn">Publish</button>
            </form>
        </div>
    </div>
</body>
</html>