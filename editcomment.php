<?php
session_start();
include 'dbconnect.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commentID = $_GET['id'] ?? null;
    $commentText = $_POST['commentText'] ?? null;
    $userID = $_SESSION['user_id'] ?? null;
    $postID = $_POST['postID'] ?? null;

    // Validate inputs
    if (!filter_var($commentID, FILTER_VALIDATE_INT) || empty($commentText) || !filter_var($userID, FILTER_VALIDATE_INT)) {
        echo 'Invalid input.';
        exit();
    }

    // Prepare and execute the update query
    $stmt = $conn->prepare(
        "UPDATE comments 
         SET commentText = ?, updated = CURRENT_TIMESTAMP 
         WHERE id = ? 
         AND (userID = ? 
              OR EXISTS (SELECT 1 
                          FROM posts 
                          WHERE id = ? 
                          AND userID = ?))"
    );
    if ($stmt === false) {
        echo 'Error preparing statement: ' . htmlspecialchars($conn->error);
        exit();
    }

    // Bind parameters
    $stmt->bind_param("siisi", $commentText, $commentID, $userID, $postID, $userID);
    if (!$stmt->execute()) {
        echo 'Error executing statement: ' . htmlspecialchars($stmt->error);
        exit();
    }

    $stmt->close();

    // Redirect back to the profile page after successful update
    header("Location: profile.php?message=Comment updated successfully");
    exit();
}

// Fetch comment for form
$commentID = $_GET['id'] ?? null;
if (!filter_var($commentID, FILTER_VALIDATE_INT)) {
    echo 'Invalid comment ID.';
    exit();
}

$stmt = $conn->prepare("SELECT commentText, postID FROM comments WHERE id = ?");
$stmt->bind_param("i", $commentID);
$stmt->execute();
$result = $stmt->get_result();
$comment = $result->fetch_assoc();
$stmt->close();

if (!$comment) {
    echo 'Comment not found.';
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./CSS/design.css">
    <title>Edit Comment - UngaBunga</title>
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
            <form action="editcomment.php?id=<?= htmlspecialchars($commentID) ?>" method="post">
                <input type="hidden" name="postID" value="<?= htmlspecialchars($comment['postID']) ?>">
                <label for="commentText">Comment:</label><br>
                <textarea class="contentbox" name="commentText" rows="5" required><?= htmlspecialchars($comment['commentText']) ?></textarea><br>
                <button type="submit" class="btn">Update Comment</button>
            </form>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>