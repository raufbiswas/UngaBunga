<?php
session_start();
include 'dbconnect.php';

if (!isset($_SESSION['userID'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $userID = $_SESSION['userID'];

    $query = "INSERT INTO Posts (userID, title, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $userID, $title, $content);

    if ($stmt->execute()) {
        // Update total_post count for user
        $updateStmt = $conn->prepare("UPDATE users SET total_post = total_post + 1 WHERE id = ?");
        $updateStmt->bind_param("i", $userID);
        $updateStmt->execute();
        $updateStmt->close();

        header('Location: profile.php');
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./CSS/design.css">
    <link rel="stylesheet" href="./CSS/enhanced.css">
    <title>Create Post - UngaBunga</title>
</head>
<body>
    <div class="container">
        <h1 class="logo">Create New Post</h1>
        <form action="createPost.php" method="post">
            <label for="title">Title:</label><br>
            <input class="textbox" type="text" name="title" required><br>
            <label for="content">Content:</label><br>
            <textarea class="textbox" name="content" rows="5" required></textarea><br>
            <button class="btn" type="submit">Create Post</button>
        </form>
    </div>
</body>
</html>