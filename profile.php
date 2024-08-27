<?php
session_start();
include 'dbconnect.php';

$userID = $_SESSION['userID'];

// Fetch the user's profile details
$userStmt = $conn->prepare("SELECT username, first_name, last_name, email, phone, date_of_birth, total_post FROM users WHERE id = ?");
$userStmt->bind_param("i", $userID);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userResult->num_rows === 0) {
    header('Location: home.php');
    exit();
}

$user = $userResult->fetch_assoc();

// Fetch all posts by the user
$postsStmt = $conn->prepare("SELECT id, title, content, created FROM Posts WHERE userID = ? ORDER BY created DESC");
$postsStmt->bind_param("i", $userID);
$postsStmt->execute();
$postsResult = $postsStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="User profile page for UngaBunga with options to update profile, create new posts, search posts, and view all posts.">
    <meta name="keywords" content="profile, posts, UngaBunga, user">
    <link rel="stylesheet" href="./CSS/design.css">
    <link rel="stylesheet" href="./CSS/enhanced.css">
    <title>My Profile - UngaBunga</title>
</head>
<body>
    <!-- Profile Header with Links -->
    <div class="profile-header">
        <div class="profile-link">
            <a href="profile.php?username=<?= htmlspecialchars($_SESSION['username']) ?>">
                <?= htmlspecialchars($_SESSION['username']) ?>'s Profile
            </a>
        </div>
        <div class="logout-link">
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <!-- Profile Update Section -->
        <section class="profile-update">
            <h1 class="section-title">My Profile</h1>
            <p>Welcome, <?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name']) ?>!</p>
            <p>Email: <?= htmlspecialchars($user['email']) ?></p>
            <p>Phone: <?= htmlspecialchars($user['phone']) ?></p>
            <p>Date of Birth: <?= htmlspecialchars($user['date_of_birth']) ?></p>
            <p>Total Posts: <?= htmlspecialchars($user['total_post']) ?></p>
            <a href="update_profile.php" class="btn btn-primary">Update Profile</a>
        </section>

        <!-- Create New Post Section -->
        <section class="create-post">
            <h2 class="section-title">Create New Post</h2>
            <a href="create_post.php" class="btn btn-secondary">Create Post</a>
        </section>

        <!-- Search Posts Section -->
        <section class="search-posts">
            <h2 class="section-title">Search Your Posts</h2>
            <form action="profile.php" method="get">
                <input class="textbox search-input" type="text" name="search" placeholder="Search posts..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button class="btn btn-primary" type="submit">Search</button>
            </form>
        </section>

        <!-- Display All Posts by the User -->
        <section class="user-posts">
            <h2 class="section-title">Your Posts</h2>
            <?php if ($postsResult->num_rows > 0): ?>
                <?php while ($post = $postsResult->fetch_assoc()): ?>
                    <div class="post">
                        <h3 class="post-title"><?= htmlspecialchars($post['title']) ?></h3>
                        <p class="post-content"><?= htmlspecialchars($post['content']) ?></p>
                        <p class="post-meta">Posted on <?= htmlspecialchars($post['created']) ?></p>
                        <div class="post-actions">
                            <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn btn-edit">Edit</a>
                            <a href="delete_post.php?id=<?= $post['id'] ?>" class="btn btn-delete">Delete</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No posts found.</p>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>

<?php
$userStmt->close();
$postsStmt->close();
$conn->close();
?>