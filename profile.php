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
$postsStmt = $conn->prepare("SELECT id, title, content, created, status, file_path, view_count, like_count FROM Posts WHERE userID = ? ORDER BY created DESC");
$postsStmt->bind_param("i", $userID);
$postsStmt->execute();
$postsResult = $postsStmt->get_result();

// Handle post creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $status = $_POST['status'] ?? 'draft'; // Default to 'draft' if not set
    $filePath = null;

    // Handle file upload
    if (isset($_FILES['fileUpload']) && $_FILES['fileUpload']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['fileUpload']['tmp_name'];
        $fileName = $_FILES['fileUpload']['name'];
        $fileSize = $_FILES['fileUpload']['size'];
        $fileType = $_FILES['fileUpload']['type'];

        // Define upload directory
        $uploadDir = 'uploads/';

        // Ensure the upload directory exists, if not create it
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Create a unique file name to avoid overwriting
        $uniqueFileName = time() . '-' . $fileName;
        $filePath = $uploadDir . $uniqueFileName;

        // Move the uploaded file to the upload directory
        move_uploaded_file($fileTmpPath, $filePath);
    }

    // Insert post into the database
    $stmt = $conn->prepare("INSERT INTO Posts (userID, title, content, status, file_path, view_count, like_count) VALUES (?, ?, ?, ?, ?, 0, 0)");
    $stmt->bind_param("isss", $userID, $title, $content, $status, $filePath);
    $stmt->execute();
    $stmt->close();

    // Redirect to the profile page or handle success
    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="User profile page for UngaBunga with options to update profile, create new posts, search posts, and view all posts.">
    <meta name="keywords" content="profile, posts, UngaBunga, user">
    <link rel="stylesheet" href="./CSS/design.css">
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
            <form action="profile.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="create_post" value="1">
                <label for="title">Title:</label><br>
                <input class="textbox" type="text" name="title" required><br>
                
                <label for="content">Content:</label><br>
                <textarea class="contentbox" id="contentbox" name="content" rows="15" required></textarea><br>
                
                <!-- Single File Upload Input -->
                <label for="fileUpload">Upload File:</label><br>
                <input type="file" name="fileUpload"><br><br>

                <!-- Post Status Selection -->
                <label for="status">Post Status:</label><br>
                <select name="status">
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select><br><br>

                <!-- Emoji Picker -->
                <emoji-picker id="emoji-picker"></emoji-picker><br>

                <button type="submit" class="btn btn-primary">Publish</button>
            </form>
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
                        <p class="post-status">Status: <?= htmlspecialchars($post['status']) ?></p>
                        <?php if ($post['file_path']): ?>
                            <?php
                            $fileExtension = pathinfo($post['file_path'], PATHINFO_EXTENSION);
                            if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                <img src="<?= htmlspecialchars($post['file_path']) ?>" alt="Post Image" class="post-image">
                            <?php elseif (in_array($fileExtension, ['mp4', 'avi', 'mov'])): ?>
                                <video controls class="post-video">
                                    <source src="<?= htmlspecialchars($post['file_path']) ?>" type="video/<?= htmlspecialchars($fileExtension) ?>">
                                    Your browser does not support the video tag.
                                </video>
                            <?php endif; ?>
                        <?php endif; ?>
                        <div class="post-content">
                            <?php
                            $maxLength = 200; // Maximum length for the preview
                            if (strlen($post['content']) > $maxLength):
                                $preview = substr($post['content'], 0, $maxLength) . '...';
                                $fullContent = htmlspecialchars($post['content']);
                                echo "<p>$preview <a href='#' class='read-more' data-full-content=\"$fullContent\">Read more</a></p>";
                            else:
                                echo "<p>" . htmlspecialchars($post['content']) . "</p>";
                            endif;
                            ?>
                        </div>
                        <p class="post-meta">Posted on <?= htmlspecialchars($post['created']) ?> | Views: <?= htmlspecialchars($post['view_count']) ?> | Likes: <?= htmlspecialchars($post['like_count']) ?></p>
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

    <!-- Include Emoji Picker Script -->
    <script src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1.0.0/dist/emoji-picker-element.js"></script>
    <script>
        const emojiPicker = document.querySelector('#emoji-picker');
        const contentBox = document.querySelector('#contentbox');

        emojiPicker.addEventListener('emoji-click', event => {
            const emoji = event.detail.unicode;
            contentBox.value += emoji;
        });

        document.querySelectorAll('.read-more').forEach(link => {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                const fullContent = this.getAttribute('data-full-content');
                this.parentElement.innerHTML = `<p>${fullContent}</p>`;
            });
        });
    </script>
</body>
</html>