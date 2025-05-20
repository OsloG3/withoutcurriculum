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
    <title>Edit Article</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Edit Article</h1>
    <?php if (!($_SERVER["REQUEST_METHOD"] == "POST")): ?>
        <form action="edit_article.php" method="post">
            <label for="article_id">Articles:</label>
            <select id="article_id" name="article_id" required>
                <option value="">Select article</option>
                <?php
                // Fetch categories from the database
                $articleStmt = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC");
                while ($article = $articleStmt->fetch()) {
                    echo "<option value='{$article['id']}'>{$article['title']}</option>";
                }
                ?>
            </select><br><br>
            <input type="submit" value="Edit Article">
        </form>
    <?php else:
        $id = $_POST['article_id'];
        $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
        $stmt->execute([$id]);
        $article = $stmt->fetch();
    ?>
    <form action="edit_article.php" method="post">
        <input type="hidden" value="<?php echo $id; ?>" name="article_id" id="article_id" />
        
        <label for="title">Title:</label>
        <textarea id="title" name="title" rows="1" required><?php echo $article['title']; ?></textarea><br>

        <label for="content">Content:</label><br>
        <textarea id="content" name="content" rows="10" required><?php echo $article['content']; ?></textarea><br>

        <label for="author">Author:</label>
        <textarea id="author" name="author" rows="1" required><?php echo $article['author']; ?></textarea><br>

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

        <input type="submit" value="Edit Article">
    </form>

    <?php
    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['author'])) {

        $id = $_POST['article_id'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $author = $_POST['author'];
        $category_id = $_POST['category_id'];
        // Insert article into the database
        $stmt = $pdo->prepare("UPDATE articles SET title=?, content=?, author=?, category_id=? WHERE id=?");
        echo "HuLL "; echo [$title, $content, $author, $category_id, $id];
        echo $title;
        echo $content;
        echo $author;
        echo $category_id;
        echo $id;
        if ($stmt->execute([$title, $content, $author, $category_id, $id])) {
            echo "<p>Article added successfully!</p>";
            header('Location: admin_dashboard.php');
        } else {
            echo "<p>Error adding article.</p>";
        }
        echo "HHH";
    }
    ?>
    <?php endif; ?>
</body>
</html>
