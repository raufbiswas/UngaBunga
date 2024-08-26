<?php
session_start();
include 'dbconnect.php';

$commentID = $_GET['id'];
$postID = $_GET['postID'];
$userID = $_SESSION['userID'];

// Fetch comment data
$query = "SELECT * FROM comments WHERE id = ? AND userID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $commentID, $userID);
$stmt->execute();
$result = $stmt->get_result();
$comment = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commentText = $_POST['commentText'];

    $stmt = $conn->prepare("UPDATE comments SET commentText = ? WHERE id = ? AND userID = ?");
    $stmt->bind_param("sii", $commentText, $commentID, $userID);

    if ($stmt->execute()) {
        header("Location: viewPost.php?id=$postID");
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./CSS/style.css">
    <title>Edit Comment - UngaBunga</title>
</head>
<body>
<div class="container">
    <h1 class="logo">Edit Comment</h1>
    <form action="editComment.php?id=<?= $commentID ?>&postID=<?= $postID ?>" method="post">
        <textarea class="textbox" name="commentText" rows="5" required><?= htmlspecialchars($comment['commentText']) ?></textarea><br>
        <button class="btn" type="submit">Update Comment</button>
    </form>
</div>
</body>
</html>