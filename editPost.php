<?php
session_start();
include 'dbconnect.php';

if (!isset($_SESSION['userID']) || !isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$postID = $_GET['id'];
$userID = $_SESSION['userID'];

// Fetch post details
$postStmt = $conn->prepare("SELECT title, content FROM Posts WHERE id = ? AND userID = ?");
$postStmt->bind_param("ii", $postID, $userID);
$postStmt->execute();
$postResult = $postStmt->get_result();

if ($postResult->num_rows === 0) {
    header('Location: profile.php');
    exit();
}

$post = $postResult->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    $updateStmt = $conn->prepare("UPDATE Posts SET title = ?, content = ? WHERE id = ? AND userID = ?");
    $updateStmt->bind_param("ssii", $title, $content, $postID, $userID);

    if ($updateStmt->execute()) {
        header('Location: profile.php');
    } else {
        echo "Error: " . $updateStmt->error;
    }

    $updateStmt->close();
}

$postStmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./CSS/design.css">
    <link rel="stylesheet" href="./CSS/enhanced.css">
    <title>Edit Post - UngaBunga</title>
</head>
<body>
    <div class="container">
        <h1 class="logo">Edit Post</h1>
        <form action="editPost.php?id=<?= $postID ?>" method="post">
            <label for="title">Title:</label><br>
            <input class="textbox" type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required><br>
            <label for="content">Content:</label><br>
            <textarea class="textbox" name="content" rows="5" required><?= htmlspecialchars($post['content']) ?></textarea><br>
            <button class="btn" type="submit">Update Post</button>
        </form>
    </div>
</body>
</html>