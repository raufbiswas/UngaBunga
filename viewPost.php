<?php
session_start();
include 'dbconnect.php';

$postID = $_GET['id'];

// Fetch post data
$query = "SELECT posts.*, users.username FROM posts JOIN users ON posts.userID = users.id WHERE posts.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $postID);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

// Fetch comments
$comment_query = "SELECT comments.*, users.username FROM comments JOIN users ON comments.userID = users.id WHERE comments.postID = ? ORDER BY comments.created DESC";
$comment_stmt = $conn->prepare($comment_query);
$comment_stmt->bind_param("i", $postID);
$comment_stmt->execute();
$comments = $comment_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./CSS/style.css">
    <title><?= htmlspecialchars($post['title']) ?> - UngaBunga</title>
</head>
<body>
<div class="container">
    <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>
    <p>by <?= htmlspecialchars($post['username']) ?> on <?= $post['created'] ?></p>
    <div class="post-content"><?= nl2br(htmlspecialchars($post['content'])) ?></div>

    <div class="comment-box">
        <h2 class="comment-title">Comments</h2>
        <?php while ($comment = $comments->fetch_assoc()): ?>
            <div class="comment-content">
                <p><?= htmlspecialchars($comment['username']) ?> on <?= $comment['created'] ?></p>
                <p><?= nl2br(htmlspecialchars($comment['commentText'])) ?></p>
                <?php if ($comment['userID'] == $_SESSION['userID']): ?>
                    <div class="action-links">
                        <a href="editComment.php?id=<?= $comment['id'] ?>&postID=<?= $postID ?>">Edit</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="comment-box">
        <h2 class="comment-title">Add a Comment</h2>
        <form action="addComment.php?postID=<?= $postID ?>" method="post">
            <textarea class="textbox" name="commentText" rows="5" placeholder="Write your comment..." required></textarea><br>
            <button class="btn" type="submit">Add Comment</button>
        </form>
    </div>
</div>
</body>
</html>