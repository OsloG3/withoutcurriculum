<?php 
include 'includes/db.php';
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
    <title>Add New Article</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Add New Article</h1>
    
    <form action="add_article.php" method="post">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required><br>

        <label for="content">Content:</label><br>
        <textarea id="content" name="content" rows="10" required></textarea><br>

        <label for="author">Author:</label>
        <input type="text" id="author" name="author" required><br>

        <label for="category_id">Category:</label>
        <select id="category_id" name="category_id" required>
            <option value="">Select a category</option>
            <?php
            // Fetch categories from the database
            $categoryStmt = $pdo->query("SELECT * FROM categories");
            while ($category = $categoryStmt->fetch()) {
                echo "<option value='{$category['id']}'>{$category['name']}</option>";
            }
            ?>
        </select><br><br>

        <input type="submit" value="Add Article">
    </form>

    <?php
    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $author = $_POST['author'];
        $category_id = $_POST['category_id'];

        // Insert article into the database
        $stmt = $pdo->prepare("INSERT INTO articles (title, content, author, category_id) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$title, $content, $author, $category_id])) {
            echo "<p>Article added successfully!</p>";
        } else {
            echo "<p>Error adding article.</p>";
        }
    }
    ?>
</body>
</html>
