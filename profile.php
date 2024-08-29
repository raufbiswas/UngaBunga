<?php
session_start();
include 'dbconnect.php';

// Retrieve user ID from session
$userID = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

// If user ID is not set in session, redirect to login
if (!$userID) {
    header('Location: index.php');
    exit();
}

// Fetch user information
$stmt = $conn->prepare("SELECT first_name, last_name, date_of_birth, email, phone, total_post, followers FROM users WHERE id = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle searching for posts by title
$searchTerm = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : '';

// Fetch all posts by the user, optionally filtered by the search term
if ($searchTerm) {
    $stmt = $conn->prepare("SELECT id, title, content, created, updated FROM posts WHERE userID = ? AND title LIKE ? ORDER BY created DESC");
    $stmt->bind_param("is", $userID, $searchTerm);
} else {
    $stmt = $conn->prepare("SELECT id, title, content, created, updated FROM posts WHERE userID = ? ORDER BY created DESC");
    $stmt->bind_param("i", $userID);
}
$stmt->execute();
$postsResult = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./CSS/design.css">
    <title>My Profile - UngaBunga</title>
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
            <a href="logout.php" class="btn-secondary">Log Out</a>
        </div>

        <!-- User Information -->
        <section class="profileinfo">
            <h1 class="section-title">Profile Information</h1>
            <p>
                Name : <b><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?></b><br>
                Email : <b><?= htmlspecialchars($user['email']) ?></b><br>
                Phone : <b><?= htmlspecialchars($user['phone']) ?></b><br>
                Date Of Birth : <b><?= htmlspecialchars($user['date_of_birth']) ?></b><br>
                Total Posts : <b><?= htmlspecialchars($user['total_post']) ?></b><br>
                Followers : <b><?= htmlspecialchars($user['followers']) ?></b>
            </p>
            <a href="updateprofile.php" class="btn btn-primary">Update Profile</a>
        </section>

        <!-- Create New Post -->
        <section style="margin-bottom: 2rem">
            <h2 class="section-title">Create New Post</h2>
            <a href="createPost.php" class="btn-secondary">Create Post</a>
        </section>

        <!-- Searching Posts -->
        <section style="margin-bottom: 2rem">
            <h2 class="section-title">Search My Posts</h2>
            <form action="profile.php" method="get">
                <input class="textbox search-input" type="text" name="search" placeholder="Search posts..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button class="btn btn-primary" type="submit">Search</button>
            </form>
        </section>

        <!-- Displaying Posts -->
        <section style="margin-bottom: 2rem">
            <h2 class="section-title">My Posts</h2>
            <?php if ($postsResult->num_rows > 0): ?>
                <?php while ($post = $postsResult->fetch_assoc()): ?>
                    <div class="post">
                        <h3>Title: <?= htmlspecialchars($post['title']) ?></h3>
                        <p>Content: <?= htmlspecialchars(substr($post['content'], 0, 100)) ?>...</p>
                        <p>Created: <?= htmlspecialchars($post['created']) ?> | Updated: <?= htmlspecialchars($post['updated']) ?></p>
                        <a href="editpost.php?post_id=<?= $post['id'] ?>" class="btn btn-primary">Edit</a>
                        <a href="deletepost.php?post_id=<?= $post['id'] ?>" class="btn btn-danger">Delete</a>
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