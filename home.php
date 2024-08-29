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

// Get the logged-in user's ID
$userID = $_SESSION['user_id'];

// Fetch posts from the database
$query = "SELECT p.id, p.title, p.content, p.created, p.updated, u.username, u.id as userID 
          FROM Posts p 
          JOIN users u ON p.userID = u.id 
          ORDER BY p.created DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./CSS/design.css">
    <title>Home - UngaBunga</title>
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
            <a href="profile.php" class="btn-secondary">Profile</a>
            <a href="createpost.php" class="btn-secondary"> Create Post</a>
            <a href="logout.php" class="btn-secondary">Log Out</a>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="post">
                    <h2><?= htmlspecialchars($row['title']) ?></h2>
                    <p><?= htmlspecialchars($row['content']) ?></p>
                    <p>Posted by 
                        <a href="profile.php?userID=<?= htmlspecialchars($row['userID']) ?>">
                            <?= htmlspecialchars($row['username']) ?>
                        </a>
                        on <?= htmlspecialchars($row['created']) ?>
                    </p>

                    <!-- Comment form -->
                    <form action="addcomment.php" method="post">
                        <textarea class="textbox" name="commentText" placeholder="Add a comment..." required></textarea><br>
                        <input type="hidden" name="postID" value="<?= htmlspecialchars($row['id']) ?>">
                        <button class="btn" type="submit">Post Comment</button>
                    </form>

                    <!-- Display comments -->
                    <?php
                    $postID = $row['id'];
                    $commentQuery = $conn->prepare("SELECT c.id, c.commentText, c.created, u.username 
                                                    FROM comments c 
                                                    JOIN users u ON c.userID = u.id 
                                                    WHERE c.postID = ? 
                                                    ORDER BY c.created ASC");
                    $commentQuery->bind_param("i", $postID);
                    $commentQuery->execute();
                    $comments = $commentQuery->get_result();
                    ?>
                    <?php if ($comments->num_rows > 0): ?>
                        <div class="comments">
                            <?php while ($comment = $comments->fetch_assoc()): ?>
                                <div class="comment">
                                    <p><strong><?= htmlspecialchars($comment['username']) ?>:</strong> <?= htmlspecialchars($comment['commentText']) ?></p>
                                    <p>Posted on <?= htmlspecialchars($comment['created']) ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No posts available.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>