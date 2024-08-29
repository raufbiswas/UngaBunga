<?php
session_start();
include "dbconnect.php";

// Retrieve user ID from session
$userID = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

// If user ID is not set in session, redirect to login
if (!$userID) {
    header('Location: index.php');
    exit();
}

// Fetch user information
$stmt = $conn->prepare("SELECT first_name, last_name, date_of_birth, email, phone, username, password FROM users WHERE id=?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (isset($_POST['submit'])) {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $dob = $_POST['dob'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verify if the email or username is used by another user
    $verify_email = mysqli_query($conn, "SELECT email FROM users WHERE email='$email' AND id != '$userID'");
    $verify_username = mysqli_query($conn, "SELECT username FROM users WHERE username='$username' AND id != '$userID'");

    if (mysqli_num_rows($verify_email) != 0) {
        echo "<div class='message'>
        <p>Email already used. Please, try again.</p>
        </div> <br>";
    } elseif (mysqli_num_rows($verify_username) != 0) {
        echo "<div class='message'>
        <p>Username already used. Please, try again.</p>
        </div> <br>";
    } else {
        // If no new password is provided, use the current password
        $new_password = $user['password'];

        // Check if the user entered a new password
        if (!empty($password)) {
            // If a new password is provided, hash it and use it
            $new_password = password_hash($password, PASSWORD_DEFAULT);
        }

        // Now you can update the user record
        $update_query = "UPDATE users SET first_name = '$fname', last_name = '$lname', date_of_birth = '$dob', email='$email', phone = '$phone', username='$username', password='$new_password' WHERE id='$userID'";
        $update_result = mysqli_query($conn, $update_query);

        if ($update_result) {
            header('Location: profile.php');
        } else {
            echo "Error updating profile: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile - UngaBunga</title>
    <link rel="stylesheet" href="./CSS/design.css">
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

        <div class="header">
            <a href="home.php" class="btn-secondary">Home</a>
            <a href="logout.php" class="btn-secondary">Log Out</a>
        </div>

        <section class="profileinfo">
            <form action="updateprofile.php" method="post">
                <label for="fname">First Name:</label><br>
                <input class="textbox" type="text" name="fname" value="<?= htmlspecialchars($user['first_name']) ?>" required><br>
                <label for="lname">Last Name:</label><br>
                <input class="textbox" type="text" name="lname" value="<?= htmlspecialchars($user['last_name']) ?>" required><br>
                <label for="dob">Date of Birth:</label><br>
                <input class="textbox" type="date" id="dob" name="dob" value="<?= htmlspecialchars($user['date_of_birth']) ?>" required><br>
                <label for="email">Email:</label><br>
                <input class="textbox" type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br>
                <label for="phone">Phone Number:</label><br>
                <input class="textbox" type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required><br><br>
                <label for="username">Username:</label><br>
                <input class="textbox" type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required><br>
                <label for="password">Password:</label><br>
                <input class="textbox" type="password" name="password"><br>
                <button class="btn" name="submit" type="submit">Update Info</button><br><br>
            </form>
        </section>
</div>    
</body>
</html>
<?php
$conn->close();
?>