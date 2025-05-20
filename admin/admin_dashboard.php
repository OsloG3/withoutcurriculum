<?php
session_start();

$timeout_duration = 600; // 15 minutes

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect if not logged in
    exit;
} else {
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Welcome to the Admin Dashboard</h1>
    <a href="logout.php">Logout</a>
    <br>
    <a href="add_article.php">Add New Article</a>
    <br>
    <a href="edit_article.php">Edit Existing Article</a>
</body>
</html>
