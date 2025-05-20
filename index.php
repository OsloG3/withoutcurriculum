<?php include 'UBH/includes/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>without curriculum</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
<?php if (!isset($_GET['id'])) {
	echo "<div class='title'>Without Curriculum</div>";
}
?>
        <nav>
             <ul>
                   <li><a href="index.php">ABOUT</a></li>               
                   <li><a href="novels.php">NOVELS</a></li>
                   <li><a href="photo.php">PHOTOS</a></li>
                   <li>
                        <a>TRANSLATIONS</a>
                        <ul>
                            <li><a href="index.php?category_id=rus_ita">russian to italian</a></li>
                            <li><a href="index.php?category_id=spa_ita">spanish to italian</a></li>
                            
                        </ul>
                    </li>
                   <li>
                        <a>STORIES</a>
                        <ul>
                            <li><a href="index.php?category_id=ita_storie">ITALIANO</a></li>
                            <li><a href="index.php?category_id=spa_storie">ESPAÑOL</a></li>
                            <li><a href="index.php?category_id=pol_storie">POLSKI</a></li>
                            <li><a href="index.php?category_id=rus_storie">РУССКИЙ</a></li> 
                            <li><a href="index.php?category_id=port_storie">PORTUGUÊS</a></li>                           
                        </ul>
                    </li>
                    <li><a href='index.php?contact=1'>CONTACT</a></li>
              </ul>
        </nav>
        
    </header>

    <section id="about">
        
        <?php 
            if (isset($_GET['category_id'])) {
                $categoryId = $_GET['category_id'];
                $stmt = $pdo->prepare("SELECT * FROM writen WHERE category = ? ORDER BY date DESC");
                $stmt->execute([$categoryId]);
                echo "<div class='pad'>";
                while ($article = $stmt->fetch()) {
                    echo "<p class='select'><a href='index.php?id={$article['id']}'>{$article['title']}</a><p>";
                    //echo "<p>{$article['content']}</p>";
                    //echo "<p>Author: {$article['author']} | Date: {$article['created_at']}</p>";
                }
                echo "</div>";
           } elseif (isset($_GET['id'])) {
                $id = $_GET['id'];
                $stmt = $pdo->prepare("SELECT * FROM writen WHERE id = ?");
                $stmt->execute([$id]);
                $article = $stmt->fetch();

                if ($article) {
                    echo "<p>{$article['title']}</p>";
                    $content = nl2br($article['content']);
                    echo "<p>{$content}</p>";
                    echo "<p>{$article['author']} </p>";
                } else {
                    echo "<p>Article not found.</p>";
                }
           } elseif (isset($_GET['photo_id'])) {
                  $path = "img";

                  if ($handle = opendir($path)) {
                        echo "<div class='gal_row'>
                                <div class='gal_col'>";
                        $i = 0;
                        while (false !== ($file = readdir($handle))) {
                                if ('.' === $file) continue;
                                if ('..' === $file) continue;
                                if (($i%61) == 0 && $i != 0) {
                                        echo "</div><div class='gal_col'>";
                                }
                                echo " 
                                     <img src='img/{$file}'>";
                                $i++;
                        }
                        echo "</div></div>";
                        closedir($handle);
                }                
               echo "<div class='clearfix'></div>";
           } elseif (isset($_GET['contact'])) {
                echo "<p> My phone number is 333 but I never answer<br>
                        my email is: TTT@ttt.genius but I rarely check it<br>
                        if you want to speak to me in person you are wellcome to fuckoff<br><br>
                        Kind Regards,<br>
                        Tinca Gardenghi
                        </p>";
           } else {
                echo 
                <<<ITEM

                      <div class="row">
                        <div class="col_40">
                        <i>
                        What must you do?<br>
                        You must submit an application<br>
                        and enclose a Curriculum Vitae.<br>
                        Regardless of how long your life is,<br>
                        the Curriculum Vitae should be short.<br>
                        Be concise, select facts.<br>
                        Change landscapes into addresses<br>
                        and vague memories into fixed dates.<br>
                        Of all your loves, mention only the marital,<br>
                        and of the children, only those who were born.<br>
                        It's more important who knows you<br>
                        than whom you know.<br>
                        Travels––only if abroad.<br>
                        Affiliations––to what, not why.<br>
                        Awards––but not for what.<br>
                        Write as if you never talked with yourself,<br>
                        as if you looked at yourself from afar.<br>
                        Omit dogs, cats, and birds,<br>
                        mementos, friends, dreams.<br>
                        State price rather than value,<br>
                        title rather than content.<br>
                        Shoe size, not where one is going,<br>
                        the one you are supposed to be.<br>
                        Enclose a photo with one ear showing.<br>
                        What counts is its shape, not what it hears.<br>
                        What does it hear?<br>
                        The clatter of machinery that shreds paper.<br>
                        Poem by  Wislawa Szymborska <br>
                        </i>  
                        </div>
                        <div class="col_60">
                        <img src="media/t.jpeg" alt="My self">
                        <p>
                        HERE I WILL<br> WRITE<br> SOME VERY BORING<br><br><br> STUFF ABOUT MY SELF <br><br>
                        <br>Our Lord and Saviour Jesus please save us from me<br><br><br>Gosh
                        it's so boring<br><br><br></p>
                        </div>
                     </div>
                      


                ITEM;
           }
        ?>
    
    </section>
<div class="footer">Tinca Gardenghi: an average novelist y no tan buena traductora</div>
</body>
</html>

