<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/home.css">
    <title>Posts List</title>
</head>
<body>
<?php
include 'dbconnect.php';

$sql = "SELECT * FROM posts ORDER BY created DESC";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    echo "<h1>Blog Posts</h1>";
    while($row = mysqli_fetch_assoc($result)) {
        echo "<div class='post'>";
        echo "<h2>" . htmlspecialchars($row["title"]) . "</h2>";
        echo "<p><em>Posted on " . htmlspecialchars($row["created"]) . "</em></p>";
        echo "<p>" . htmlspecialchars($row["content"]) . "</p>";
        echo "</div>";
    }
} else {
    echo "No posts found";
}
$conn->close();
?>   
</body>
</html>