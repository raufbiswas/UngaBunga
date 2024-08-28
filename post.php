<?php
// Start session and include database connection
session_start();
require 'dbconnect.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $title = $_POST['title'];
    $content = $_POST['content'];
    $userID = $_SESSION['userID'];  // Assuming the user's ID is stored in the session

    // Initialize file upload variables
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
    $stmt = $conn->prepare("INSERT INTO Posts (userID, title, content, file_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userID, $title, $content, $filePath);
    $stmt->execute();
    $postID = $stmt->insert_id;
    $stmt->close();

    // Handle categories if applicable
    if (isset($_POST['categories'])) {
        foreach ($_POST['categories'] as $categoryID) {
            $catStmt = $conn->prepare("INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)");
            $catStmt->bind_param("ii", $postID, $categoryID);
            $catStmt->execute();
            $catStmt->close();
        }
    }
    // Redirect to the home page or display a success message
    header("Location: home.php");
    exit();
}
?>
<?php
// Debugging: Print the file path value
var_dump($post['file_path']);
?>

<?php if (isset($post['file_path']) && !empty($post['file_path'])): ?>
    <?php
    $fileExtension = pathinfo($post['file_path'], PATHINFO_EXTENSION);
    if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])): ?>
        <img src="<?= htmlspecialchars($post['file_path']) ?>" alt="Post Image" class="post-image">
    <?php elseif (in_array($fileExtension, ['mp4', 'avi', 'mov'])): ?>
        <video controls class="post-video">
            <source src="<?= htmlspecialchars($post['file_path']) ?>" type="video/<?= htmlspecialchars($fileExtension) ?>">
            Your browser does not support the video tag.
        </video>
    <?php else: ?>
        <p>Unsupported file type: <?= htmlspecialchars($fileExtension) ?></p>
    <?php endif; ?>
<?php else: ?>
    <p>No file uploaded or file path is empty.</p>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./CSS/design.css">
    <title>Create Post - UngaBunga</title>
    <!-- Include Emoji CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1.0.0/dist/emoji-picker-element.css">
</head>
<body>
    <div class="container">
        <div class="main">
            <span>
                <a href="home.php" class="logo">UngaBunga Blog</a>
            </span>
            <span>
                <a href="profile.php" class="profileicon">Hi, raufbiswas!</a>
            </span>
        </div>
        <div class="post">
            <form action="createPost.php" method="post" enctype="multipart/form-data">
                <label for="title">Title:</label><br>
                <input class="textbox" type="text" name="title" required><br>
                
                <label for="content">Content:</label><br>
                <textarea class="contentbox" id="contentbox" name="content" rows="15" required></textarea><br>
                
                <!-- Single File Upload Input -->
                <label for="fileUpload">Upload File:</label><br>
                <input type="file" name="fileUpload"><br><br>

                <!-- Emoji Picker -->
                <emoji-picker id="emoji-picker"></emoji-picker><br>

                <button type="submit" class="btn">Publish</button>
            </form>
        </div>
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
    </script>
</body>
</html>