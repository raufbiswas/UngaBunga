<?php
session_start();
include 'dbconnect.php';

$userID = $_SESSION['userID'];
$username = $_SESSION['username'];

// Handle searching for posts by a specific user
$searchUsername = isset($_GET['search_user']) ? $_GET['search_user'] : '';

// Fetch all posts, optionally filtered by the searched username
if ($searchUsername) {
    $stmt = $conn->prepare("SELECT p.id, p.title, p.content, p.created, u.username FROM Posts p JOIN users u ON p.userID = u.id WHERE u.username = ? ORDER BY p.created DESC");
    $stmt->bind_param("s", $searchUsername);
} else {
    $stmt = $conn->prepare("SELECT p.id, p.title, p.content, p.created, u.username FROM Posts p JOIN users u ON p.userID = u.id ORDER BY p.created DESC");
}
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./CSS/design.css">
    <link rel="stylesheet" href="./CSS/enhanced.css">
    <title>Home - UngaBunga</title>
</head>
<body>
    <!-- User Profile Link in the Right Upper Corner -->
    <div class="profile-link">
        <a href="profile.php?username=<?= htmlspecialchars($username) ?>">
            <?= htmlspecialchars($username) ?>'s Profile
        </a>
    </div>

    <div class="container">
        <h1 class="logo">UngaBunga Blog</h1>

        <!-- Search for blogs by a specific user -->
        <form action="home.php" method="get">
            <label for="search_user">Search Blogs by User:</label><br>
            <input class="textbox" type="text" name="search_user" value="<?= htmlspecialchars($searchUsername) ?>"><br>
            <button class="btn" type="submit">Search</button>
        </form>

        <!-- Display all posts -->
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="post">
                <h2><?= htmlspecialchars($row['title']) ?></h2>
                <p><?= htmlspecialchars($row['content']) ?></p>
                <p class="post-meta">Posted by <a href="profile.php?username=<?= htmlspecialchars($row['username']) ?>"><?= htmlspecialchars($row['username']) ?></a> on <?= htmlspecialchars($row['created']) ?></p>
                
                <?php if ($userID == $row['userID']): ?>
                    <a href="editpost.php?post_id=<?= $row['id'] ?>" class="btn">Edit</a>
                    <a href="deletepost.php?post_id=<?= $row['id'] ?>" class="btn">Delete</a>
                <?php endif; ?>

                <!-- Comment section -->
                <div class="comments">
                    <h3>Comments</h3>
                    <?php
                    $commentStmt = $conn->prepare("SELECT c.commentText, u.username, c.created FROM comments c JOIN users u ON c.userID = u.id WHERE c.postID = ? ORDER BY c.created ASC");
                    $commentStmt->bind_param("i", $row['id']);
                    $commentStmt->execute();
                    $commentResult = $commentStmt->get_result();
                    while ($commentRow = $commentResult->fetch_assoc()):
                    ?>
                        <div class="comment">
                            <p><strong><?= htmlspecialchars($commentRow['username']) ?>:</strong> <?= htmlspecialchars($commentRow['commentText']) ?></p>
                            <p class="comment-meta"><?= htmlspecialchars($commentRow['created']) ?></p>
                        </div>
                    <?php endwhile; ?>

                    <!-- Add a comment -->
                    <form action="addcomment.php" method="post">
                        <textarea class="textbox" name="commentText" placeholder="Add a comment..." required></textarea><br>
                        <input type="hidden" name="postID" value="<?= $row['id'] ?>">
                        <button class="btn" type="submit">Post Comment</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>