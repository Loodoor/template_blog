<?php
    session_start();
?>

<!DOCTYPE html>
<HTML>
    <?php include('head.php'); ?>
    <body>
    <?php
        if (isset($_GET['id'])){
            $postid = intval($_GET['id']);

            $postManager = new PostManager();
            $lastid = $postManager->findAll()[0]->getId();
            try {
                if (1 <= $postid && $postid <= $lastid)
                    $post = $postManager->findPost($postid);
                else
                    header('Location: index.php');
            } catch (Exception $e) {
                header('Location: index.php');
            }

            if ($post->getId() == 0) {
                http_response_code(404);
                exit();
            }

            if (isset($_POST['cmd']) and $_POST['cmd'] == 'post_comment_add') {
                $commentaire = new Commentaire();
                $pseudo = "*(nullptr)";
                if (!isset($_SESSION) or !isset($_SESSION['pseudo'])) {
                    http_response_code(404);
                    exit();
                }
                else
                    $pseudo = $_SESSION['pseudo'];
                $message = htmlentities($_POST['post_comment_message']);
                $commentaire->handlePostRequest($pseudo, $message);
                $validation = $commentaire->validate();
                if ($validation['valid']) {
                    $post->addCommentaire($commentaire);
                    $postManager->updatePost($post);
                }
                header("Location: post.php?id=" . $_GET['id'] . "#comments");
            }
        } else {
            header("Location: index.php");
        }
        include("header.php");
        $Parsedown = new Parsedown();
    ?>
    <div class="container">
        <br />
        <div class="breadcrumb-container">
            <ol class="breadcrumb">
                <li><a href="index.php">Accueil</a></li>
                <li><a class="nolink"><?php echo $post->getTitre() ?></a></li>
            </ol>
        </div>
        <hr>
        <div class="post">
            <div class="post-header">
                <h1 style="display: inline-block"><?php echo $post->getTitre(); ?></h1>&nbsp;&nbsp;
                <span id="categorie" class="label label-default" onclick="window.location='categorie.php?id=<?php if (is_a($post, 'Post') or is_a($post, 'Project')) {echo $post->getCategorie();} else if (is_a($post, 'Article')) {echo "Article";} ?>';">
                <?php if (is_a($post, 'Post') or is_a($post, 'Project')) {echo $post->getCategorie();} else if (is_a($post, 'Article')) {echo "Article";} ?></span>
                <h4><?php echo $post->getDisplayableDate(); ?> par <?php echo $post->getAuthor(); ?>, <?php echo $post->getDisplayableDate() ?></h4>
                <div class="col-md-4 col-md-offset-4">
                    <hr />
                </div>
                <script type="text/javascript">dce("categorie").style.cursor = "pointer";</script>
            </div>
            <div class="post-content">
                <?php echo $Parsedown->text($post->getContent()); ?>
            </div>
        </div>
        <hr>
        <div>
            <div class="row">
                <div class="commentaire-form-container well col-md-8 col-md-offset-2">
                    <?php
                        $blocked_mgr = new BlockedUsersManager();
                        if (!$blocked_mgr->isBlocked($_SERVER['REMOTE_ADDR'])) {
                    ?>
                    <form class="commentaire-form form-horizontal" method="post">
                        <div class="container-fluid">
                            <?php if (!isset($_SESSION) or !isset($_SESSION['pseudo'])) { ?>
                            <h4>Donnez votre opinion !</h4>
                            Ah mince, vous devez être connecté pour continuer :( <br />
                                <a onclick="load_modal('signup_mod');" class="btn btn-default" style="margin-top: 10px;">Inscription</a>&nbsp;
                                <a onclick="load_modal('login_mod');" class="btn btn-default" style="margin-top: 10px;">Connexion</a>
                            <br />
                            <?php } else {
                                echo '<h4 style="display: inline;">Donnez votre opinion !</h4>';
                                echo '<div class="avatar" style="display: inline; float: right; line-height: 20px;">';
                                echo '<b>' . $_SESSION['pseudo'] . '</b>';
                                echo '<img src="http://identicon.org?t=' . $_SESSION['pseudo'] . '&s=50" class="img-responsive">';
                                echo '<br />';
                                echo '</div>';
                            ?>
                            <div class="form-group">
                                <textarea class="form-control" row="5" placeholder="Votre message..." name="post_comment_message"></textarea>
                            </div>
                            <div class="form-footer">
                                <input type="hidden" name="cmd" value="post_comment_add" />
                                <input type="submit" class="btn btn-primary" value="Poster" />
                            </div>
                            <?php } ?>
                        </div>
                    </form>
                    <?php } else {echo 'Votre adresse IP a été bloquée. Veuillez nous envoyer un mail si vous pensez que c\'est une erreur';} ?>
                </div>
            </div>
            <div class="row">
                <div class="commentaires-container col-md-10 col-md-offset-1">
                    <?php if (count($post->getCommentaires()) < 1): ?>
                            <h3 style="text-align: center">Aucun commentaire</h3>
                    <?php else: ?>
                            <h3 id="comments"><?php echo count($post->getCommentaires()) ?> Commentaire<?php if (count($post->getCommentaires()) > 1): ?>s<?php endif ?> : </h3>
                    <?php endif ?>
                    <?php
                    foreach($post->getCommentairesSorted() as $commentaire){
                        $validation = $commentaire->validate();
                        if (!$validation['valid']) {
                            continue;
                        }
                        $date = $commentaire->getDisplayableDate();
                        $pseudo = $commentaire->getPseudo();
                        $message = $commentaire->getMessage();
                    ?>
                    <div class="commentaire">
                        <div>
                            <div class="avatar">
                                <img src="http://identicon.org?t=<?php echo $pseudo ?>&s=50" class="img-responsive">
                            </div>
                            <div class="body">
                                <div class="header">
                                    <p><b><?php echo $pseudo ?></b>, <?php echo $date ?></p>
                                </div>
                                <div class="message">
                                    <?php echo nl2br($message) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <br />
        <br />
        <?php
            include('footer.php');
        ?>
    </div>
    </body>
</HTML>