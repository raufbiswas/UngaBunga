<?php
session_start();
include 'dbconnect.php';

// Check if the post ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo 'Post ID is missing.';
    exit();
}

$postID = intval($_GET['id']);

// Fetch the existing post data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("SELECT title, content FROM posts WHERE id = ?");
    if ($stmt === false) {
        echo 'Error preparing statement: ' . htmlspecialchars($conn->error);
        exit();
    }
    
    $stmt->bind_param("i", $postID);
    if (!$stmt->execute()) {
        echo 'Error executing statement: ' . htmlspecialchars($stmt->error);
        exit();
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo 'Post not found.';
        exit();
    }

    $post = $result->fetch_assoc();
    $stmt->close();
}

// Handle form submission for updating the post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    // Validate inputs
    if (empty($title) || empty($content)) {
        echo 'Title and content cannot be empty.';
        exit();
    }

    // Update the post in the database
    $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
    if ($stmt === false) {
        echo 'Error preparing statement: ' . htmlspecialchars($conn->error);
        exit();
    }
    
    $stmt->bind_param("ssi", $title, $content, $postID);
    if (!$stmt->execute()) {
        echo 'Error executing statement: ' . htmlspecialchars($stmt->error);
        exit();
    }

    $stmt->close();

    // Redirect to the home page after successful update
    header("Location: home.php?message=Post updated successfully");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./CSS/design.css">
    <title>Update Post - UngaBunga</title>
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
            <form action="updatepost.php?id=<?= htmlspecialchars($postID) ?>" method="post">
                <label for="title">Title:</label><br>
                <input class="textbox" type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required><br>
                
                <label for="content">Content:</label><br>
                <textarea class="contentbox" id="contentbox" name="content" rows="15" required><?= htmlspecialchars($post['content']) ?></textarea><br>

                <button type="submit" class="btn">Update</button>
            </form>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>