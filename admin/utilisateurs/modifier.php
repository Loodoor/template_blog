<?php
    session_start();
    
    include('../../private/usermanager.php');

    $roles = array('ADMINISTRATEUR', 'AUTEUR', 'MODERATEUR', 'MEMBRE');

    $userManager = new UserManager();
    $user = $userManager->findUser($_GET['id']);

    if (!$user) {
        header('Location: index.php');
        http_response_code(404);
        exit();
    }

    if (isset($_POST['witness'])) {
        if (in_array($_SESSION['role'], array('MODERATEUR', 'ADMINISTRATEUR')) && (!$user->is('ADMINISTRATEUR') || in_array($_SESSION['pseudo'], array('Folaefolc', 'ADMIN')))) {
            $user->handlePostRequest($_POST['user_pseudo'], $_POST['user_password'], $_POST['user_email'], $_POST['user_role']);
            $validation = $user->validate();
            if (intval($_POST['user_on']))
                $user->setActivated();
            else
                $user->setActivated(false);
            if ($validation['valid']) {
                $userManager->editUser($user);
                $userManager->updateUsers();
                header('Location: index.php');
                exit();
            }
        } else {
            header('Location: ../../error.php?error=403');
        }
    }

?>
<!DOCTYPE HTML>

<HTML>
    <?php include('head.php'); ?>
    <body>
        <?php include('header.php'); ?>
        <div class="container">
            <?php if (isset($_SESSION) and ($_SESSION['role'] == 'ADMINISTRATEUR' || $_SESSION['role'] == 'MODERATEUR')) { ?>
            <h2>Modifier : <?php echo $user->getPseudo() ?></h2>
            <p class="text-left">
                <a class="btn btn-default" href="index.php">Retour</a>
            </p>
            <form class="form-horizontal" method="post">
                <div class="form-group">
                    <label for="user_pseudo" class="col-sm-2 control-label">Pseudo</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="user_pseudo" name="user_pseudo" value="<?php echo $user->getPseudo() ?>" placeholder="Pseudo">
                    </div>
                </div>
                <div class="form-group">
                    <label for="user_email" class="col-sm-2 control-label">Email</label>
                    <div class="col-sm-10">
                        <input type="email" class="form-control" id="user_email" name="user_email" value="<?php echo $user->getEmail() ?>" placeholder="Email">
                    </div>
                </div>
                <div class="form-group">
                    <label for="user_role" class="col-sm-2 control-label">Rôle</label>
                    <div class="col-sm-10">
                        <select class="form-control" id="user_role" name="user_role">
                            <?php foreach($roles as $role): ?>
                                <option value="<?php echo $role ?>"<?php if($user->is($role)): ?> selected<?php endif ?>><?php echo $role ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="user_password" class="col-sm-2 control-label">Mot de passe</label>
                    <div class="col-sm-10">
                        <input type="password" class="form-control" id="user_password" name="user_password" placeholder="Mot de passe">
                        <span id="helpBlock" class="help-block">Laissez vide si vous ne souhaiter pas changer le mot de passe</span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="user_on" class="col-sm-2 control-label">Activé</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="user_on" name="user_on" placeholder="1 = activé, 0 = désactivé">
                        <span id="helpBlock" class="help-block">Laissez vide si vous ne souhaiter pas changer l'état d'activation de l'utilisateur</span>
                    </div>
                </div>
                <div class="text-right">
                    <input type="hidden" name="witness" value="X">
                    <?php if (!$user->isRoot()): ?>
                        <a class="btn btn-danger" href="supprimer.php?id=<?php echo $user->getId() ?>">Supprimer</a>
                    <?php endif ?>
                    <input type="submit" class="btn btn-primary" value="Sauvegarder">
                </div>
            </form>
            <?php } else {
                header('Location: ../../error.php?error=403');
            } ?>
            <?php include('../../footer.php'); ?>
        </div>
    </body>
</html>