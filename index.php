<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./CSS/design.css">
    <title>UngaBunga</title>
</head>
<body class="index-page">
    <div class="container">
    <div class="signbox">
        <h1 class="logo">UngaBunga Blog</h1>
        <form action="signin.php" method="post">
            <label for="username">Username:</label><br>
            <input class="textbox" type="text" name="username" required><br>
            <label for="password">Password:</label><br>
            <input class="textbox" type="text" name="password" required><br><br>
            <?php
                if(isset($_GET['error'])) {
                    echo "<div class='message' style='color: Red'>
                    <p>Invalid username or password. Please try again!</p>
                    </div> <br>";
                } elseif(isset($_GET['message'])) {
                    echo "<div class='message' style='color: Green'>
                    <p>Registration Successful!</p>
                    </div> <br>";
                }
            ?>
            <button class="btn">Sign In</button><br><br>
            <a style="color: lavender;" href="signup.php">Don't have an account? Sign Up!</a>
        </form>
    </div>
    </div>
</body>
</html>