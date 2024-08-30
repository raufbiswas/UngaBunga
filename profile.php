<?php
session_start();
include 'dbconnect.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Retrieve user ID from session
$userID = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

// Determine if we need to display another user's profile
$viewingUserID = isset($_GET['userID']) ? intval($_GET['userID']) : $userID;

// If user ID is not set in session or viewing user ID is invalid, redirect to login
if (!$userID) {
    header('Location: index.php');
    exit();
}

// Fetch user information
$stmt = $conn->prepare("SELECT first_name, last_name, date_of_birth, email, phone, total_post, followers FROM users WHERE id = ?");
$stmt->bind_param("i", $viewingUserID);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    $user = null; // Set to null if no user found
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./CSS/design.css">
    <title><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?> - UngaBunga</title>
</head>
<body>
    <div class="container">
        <div class="main">
            <span>
                <a href="home.php" class="logo">UngaBunga Blog</a>
            </span>
            <span>
                <a href="profile.php" class="profileicon">Hi, <?= htmlspecialchars($username) ?>!</a>
            </span>
        </div>

        <div class="header">
            <a href="home.php" class="btn-secondary">Home</a>
            <a href="followers.php" class="btn-secondary">Followers</a>
            <a href="createpost.php" class="btn-secondary">Create Post</a>
            <a href="logout.php" class="btn-secondary">Log Out</a>
        </div>

        <!-- User Information -->
        <section class="profileinfo">
            <h1 class="section-title">Profile Information</h1>
            <?php if ($user): ?>
                <p>
                    Name : <b><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?></b><br>
                    Email : <b><?= htmlspecialchars($user['email']) ?></b><br>
                    Phone : <b><?= htmlspecialchars($user['phone']) ?></b><br>
                    Date Of Birth : <b><?= htmlspecialchars($user['date_of_birth']) ?></b><br>
                    Total Posts : <b><?= htmlspecialchars($user['total_post']) ?></b><br>
                    Followers : <b><?= htmlspecialchars($user['followers']) ?></b>
                </p>
                <?php if ($viewingUserID == $userID): ?>
                    <a href="updateprofile.php" class="btn-primary">Update Profile</a>
                <?php endif; ?>
            <?php else: ?>
                <p>User not found.</p>
            <?php endif; ?>
        </section>
        
        <!-- Display Posts -->
        <section style="margin-bottom: 2rem">
            <h2 class="section-title">Posts</h2>
            <?php
            // Fetch posts by the user
            $stmt = $conn->prepare("SELECT id, title, content, created, updated, userID FROM posts WHERE userID = ? ORDER BY created DESC");
            $stmt->bind_param("i", $viewingUserID);
            $stmt->execute();
            $postsResult = $stmt->get_result();
            ?>
            <?php if ($postsResult->num_rows > 0): ?>
                <?php while ($post = $postsResult->fetch_assoc()): ?>
                    <div class="post" style='margin-top: 1rem; margin-bottom:1rem'>
                        <h3>Title: <?= htmlspecialchars($post['title']) ?></h3>
                        <p>Content: <?= htmlspecialchars(substr($post['content'], 0, 100)) ?>...</p>
                        <p>Created: <?= htmlspecialchars($post['created']) ?> | Updated: <?= htmlspecialchars($post['updated']) ?></p>
                        <?php if ($viewingUserID == $userID): ?>
                            <a href="editpost.php?id=<?= htmlspecialchars($post['id']) ?>" class="btn-primary">Edit</a>
                            <a href="deletepost.php?id=<?= htmlspecialchars($post['id']) ?>" class="btn-delete">Delete</a>
                        <?php endif; ?>

                        <!-- Display Comments -->
                        <section class="comments" style='margin-top: 1rem; margin-bottom:1rem'>
                            <h4>Comments:</h4>
                            <?php
                            // Fetch comments for this post
                            $commentsStmt = $conn->prepare("SELECT id, commentText, created, userID FROM comments WHERE postID = ? ORDER BY created DESC");
                            $commentsStmt->bind_param("i", $post['id']);
                            $commentsStmt->execute();
                            $commentsResult = $commentsStmt->get_result();
                            ?>
                            <?php if ($commentsResult->num_rows > 0): ?>
                                <?php while ($comment = $commentsResult->fetch_assoc()): ?>
                                    <div class="comment">
                                        <p><b>Comment:</b> <?= htmlspecialchars($comment['commentText']) ?></p>
                                        <p>Created: <?= htmlspecialchars($comment['created']) ?></p>
                                        <?php if ($comment['userID'] == $userID || $post['userID'] == $userID): ?>
                                            <a href="editcomment.php?id=<?= htmlspecialchars($comment['id']) ?>" class="btn-primary">Edit</a>
                                            <a href="deletecomment.php?id=<?= htmlspecialchars($comment['id']) ?>" class="btn-delete">Delete</a>
                                        <?php endif; ?>
                                    </div>
                                    <hr>

                                    <!-- Reply Form for Post Owner -->
                                    <?php if ($post['userID'] == $userID): ?>
                                        <form action="replycomment.php" method="post">
                                            <input type="hidden" name="postID" value="<?= htmlspecialchars($post['id']) ?>">
                                            <input type="hidden" name="commentID" value="<?= htmlspecialchars($comment['id']) ?>">
                                            <textarea name="reply" rows="3" cols="50" placeholder="Reply to this comment..."></textarea><br>
                                            <button type="submit" class="btn-primary">Reply</button>
                                        </form>
                                    <?php endif; ?>

                                <?php endwhile; ?>
                            <?php else: ?>
                                <p>No comments yet.</p>
                            <?php endif; ?>
                            <?php
                            $commentsStmt->close();
                            ?>
                        </section>

                        <!-- Comment Form for Post Owner -->
                        <?php if ($post['userID'] == $userID): ?>
                            <section class="comment-form" style='margin-top: 1rem;'>
                                <h4>Add a Comment:</h4>
                                <form action="addcomment.php" method="post">
                                    <input type="hidden" name="postID" value="<?= htmlspecialchars($post['id']) ?>">
                                    <textarea name="commentText" rows="3" cols="50" placeholder="Add a comment..." required></textarea><br>
                                    <button type="submit" class="btn-primary">Add Comment</button>
                                </form>
                            </section>
                        <?php endif; ?>

                    </div>
                    <hr>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No posts found.</p>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>

<?php
$conn->close();
?>