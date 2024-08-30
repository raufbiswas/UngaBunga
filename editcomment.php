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
    $postID = $_POST['postID'] ?? null; // Ensure postID is obtained from the form

    // Validate inputs
    if (!filter_var($commentID, FILTER_VALIDATE_INT) || empty($commentText) || !filter_var($userID, FILTER_VALIDATE_INT)) {
        die('Invalid input.');
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
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    // Bind parameters
    // Note: There are 5 placeholders in the query, so 5 bind variables are needed
    $stmt->bind_param("siisi", $commentText, $commentID, $userID, $postID, $userID);
    if (!$stmt->execute()) {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    }

    $stmt->close();

    // Redirect back to the profile page
    header("Location: profile.php");
    exit();
}

// Fetch comment for form
$commentID = $_GET['id'] ?? null;
if (!filter_var($commentID, FILTER_VALIDATE_INT)) {
    die('Invalid comment ID.');
}

$stmt = $conn->prepare("SELECT commentText, postID FROM comments WHERE id = ?");
$stmt->bind_param("i", $commentID);
$stmt->execute();
$result = $stmt->get_result();
$comment = $result->fetch_assoc();
$stmt->close();

if (!$comment) {
    die('Comment not found.');
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
                <input type="hidden" name="postID" value="<?= htmlspecialchars($comment['postID']) ?>"> <!-- Hidden input for postID -->
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