<?php
session_start();

// Set timeout duration (in seconds)
$timeout_duration = 600; // 15 minutes

$notPassword = password_hash("waky", PASSWORD_DEFAULT);
$userN = "waky";


// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // Check if the session is timed out
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout_duration)) {
        // Last request was more than 15 minutes ago
        session_unset(); // Unset $_SESSION variables
        session_destroy(); // Destroy session data
        header('Location: login.php?error=timeout'); // Redirect to login with timeout error
        exit;
    }
    // Update last activity time
    $_SESSION['LAST_ACTIVITY'] = time();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];


    if (($userN == $username) && password_verify($password, $notPassword)) {
        $_SESSION['user_id'] = 1;
        $_SESSION['LAST_ACTIVITY'] = time(); // Set last activity time
        header('Location: admin_dashboard.php'); // Redirect to the admin dashboard
        exit;
    } else {
        header('Location: login.php?error=1'); // Redirect back with error
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Optional: Link to your CSS file -->
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <form action="login.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
        <?php
                echo $notPassword;
            if (isset($_GET['error'])) {
                if ($_GET['error'] == 'timeout') {
                  echo '<p class="error">Your session has timed out due to inactivity. Please log in again.</p>';
                } else {
                    echo '<p class="error">Invalid username or password.</p>';
                }
            }
        ?>
    </div>
</body>
</html>

