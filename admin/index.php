﻿<?php
    session_start();
?>

<!DOCTYPE html>

<HTML>
    <head>
        <title>titre</title>
        <meta charset="utf-8" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="Content-Language" content="fr-FR" />
        <meta name="robots" content="all" />
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no" />
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
        <!-- Website Style -->
        <link rel="stylesheet" href="../css/style.css">
        <?php
            include(__DIR__ . '/../private/post_storage.php');
        ?>
    </head>
    <body>
        <?php
            include(__DIR__ . '/../header.php');
        ?>
        <div class="container">
            <?php
                if (!isset($_POST['user']) and !isset($_POST['pwd'])){
                    include('connect.php');
                }else if (true){
                    // connexion réussie
                    // pas de session créée ici pour le moment
                    echo "Interface d'administration, bonjour {$_POST['user']}<br /><br />";
                    
                    echo "<a href=\"writing.php\">Ecrire un article</a> <br />";
                    echo "<br /><br />";
                    
                    // les news
                    echo "Liste des news : <br />";
                    echo "<ul>";
                    $pm = PostManager();
                    foreach ($pm->findAll() as $post) {
                        echo "<li>";
                        echo "{$post->getId()} - {$post->getTitre()}  {$post->getDisplayableDate()}";
                        if ($post->getEdited()){
                            echo " - a été édité";
                        }
                        echo "</li>";
                    }
                    echo "</ul>";
                    
                    echo "<hr />";
                    
                    // les commentaires (tous)
                    echo "<br />";
                    echo "Liste des commentaires de tous les articles : <br />";
                    echo "<ul>";
                    $count = 0;
                    $max = 100;
                    foreach ($pm->findAll() as $post) {
                        foreach($post->getCommentairesSorted() as $commentaire) {
                            echo "<li>";
                            echo "Par {$commentaire->getPseudo()} ({$commentaire->getIp()}) - {$commentaire->getDisplayableDate()} <br />";
                            echo "<div class=\"spoiler\">{$commentaire->getMessage()}</div>";
                            echo "</li>";
                            
                            ++$count;
                        }
                    }
                    echo "</ul>";
                }
            ?>
            
            <?php
                include(__DIR__ . '/../footer.php');
            ?>
        </div>
    </body>
</HTML>