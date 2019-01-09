<?php

//////////////////////////////
// Routes pour la connexion //
//////////////////////////////
$app->get('/login', function ($request, $response, $args) {
    $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, $response, 'Login');
    $service = $RouteHelper->curPageBaseUrl. '/login';
    // Connexion via le CAS
    if (!empty($request->getParam('ticket'))) {
        if ($this->Auth->loginUsingCas($request->getParam('ticket'), $service)) {
            return $RouteHelper->returnWithFlash('', "Vous avez bien été authentifié avec le serveur CAS", 'success');
        } else return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('login'));
    }
    if ($this->Auth->isLogged())
        $this->flash->addMessage('info', 'Vous êtes déjà authentifé ! <a class="btn btn-sm btn-primary" href="'.$this->router->pathFor('home').'">Retour à l\'accueil</a>');

    $token = $this->Auth->getTokenSlimCsrf($this, $request);

    if (!empty($this->get('settings')['Auth']['casUrl']))
        $casUrl = $this->get('settings')['Auth']['casUrl']."login?service=".urlencode($service);
    else $casUrl = "";
    return $this->renderer->render($response, 'auth/connexion.php', compact('RouteHelper', 'token', 'casUrl', $args));
})->add($container->get('csrf'))->setName('login');

$app->post('/login', function ($request, $response, $args) {
    $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, $response, 'Login');
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        if ($this->Auth->login($_POST))
            return $RouteHelper->returnWithFlash('', "Vous êtes maintenant connecté", 'success');
        else return $RouteHelper->returnWithFlash('login'.'?errorLogin=1', "Identifiants incorrects", 'danger');
    } else if (!empty($_POST))
        return $RouteHelper->returnWithFlash('login'.'?errorLogin=1', "Veuillez remplir tous vos champs", 'danger');
    else return $response->withStatus(303)->withHeader('Location', $this->router->pathFor('home'));
})->add($container->get('csrf'));

$app->get('/logout', function ($request, $response, $args) {
    $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, $response, 'logout');
    if ($this->Auth->isLoggedUsingCas())
        $url = $this->get('settings')['Auth']['casUrl']."logout?url=".urlencode($RouteHelper->curPageBaseUrl. '/login');
    else $url = $this->router->pathFor('home');
    session_destroy();
    return $response->withStatus(303)->withHeader('Location', $url);
})->setName('logout');

$app->get('/account', function ($request, $response, $args) {
    $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, $response, 'Vue compte');
    if (!$this->Auth->fetchUserAuthLastDbVal())
        return $RouteHelper->returnWithFlash('login'.'?errorLogin=1', "Erreur authentification", 'danger');

    $this->renderer->render($response, 'header.php', compact('RouteHelper', $args));
    $this->renderer->render($response, 'auth/account.php', compact('RouteHelper', $args));
    return $this->renderer->render($response, 'footer.php', compact('RouteHelper', $args));
})->setName('account');


$app->group('/auth', function () {
    $this->get('/list_droits', function ($request, $response, $args) {
        $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, $response, 'Liste des droits');

        $users = $this->Auth->getUsers();
        $this->Auth->setSlimRoutes($this);

        $this->renderer->render($response, 'header.php', compact('RouteHelper', $args));
        $this->renderer->render($response, 'auth/list_droits.php', compact('RouteHelper', 'users', $args));
        return $this->renderer->render($response, 'footer.php', compact('RouteHelper', $args));
    })->setName('auth/list_droits');
    $app->get('/cron', function ($request, $response, $args) {
        $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, $response, 'Vue compte');
        $cron = file_exists('/etc/crontab') ? file_get_contents('/etc/crontab') : '';

        $this->renderer->render($response, 'header.php', compact('RouteHelper', $args));
        $this->renderer->render($response, 'auth/cron.php', compact('RouteHelper', 'cron', $args));
        return $this->renderer->render($response, 'footer.php', compact('RouteHelper', $args));
    })->setName('auth/cron');

  $this->group('/users', function () {
    $this->get('/list', function ($request, $response, $args) {
        $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, $response, 'Liste des utilisateurs');

        $users = $this->Auth->getUsers();
        $token = $this->Auth->getTokenSlimCsrf($this, $request);

        $this->renderer->render($response, 'header.php', compact('RouteHelper', $args));
        $this->renderer->render($response, 'auth/users/list.php', compact('RouteHelper', 'token', 'users', $args));
        return $this->renderer->render($response, 'footer.php', compact('RouteHelper', $args));
    })->setName('auth/users/list');

    $this->get('/export', function ($request, $response, $args) {
        $users = $this->Auth->getUsers();
        $response->getBody()->write('is_active;email;prenom;nom;roles'."\n");
        foreach ($users as $usr) {
            $response->getBody()->write(
                $usr['is_active'].';'.
                $usr['email'].';'.
                $usr['first_name'].';'.
                $usr['last_name'].';'.
                '['.implode(', ', $usr['roles']).']'.
                "\n"
            );
        }
        return $response->withHeader('Content-Type', 'text/csv; charset=utf-8')
                    ->withHeader('Content-Disposition', 'text/csv; attachment; filename=export_users.csv');
    })->setName('auth/users/export');

    $this->get('/delete/{id}/{token_name}/{token_value}', function ($request, $response, $args) {
        $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, $response, 'Suppression');
        if ($this->Auth->sourceConfig != 'database')
            return $this->Auth->forbidden($response, $this->router, 'auth/users/list', "Il n'est pas possible d'éditer les membres avec une configuration fichier. Migrez vers une configuration base de données.", 'danger');
        $id = (int)$args['id'];
        $token_name = $args['token_name'];
        $token_value = $args['token_value'];

        if (!$this->csrf->validateToken($token_name, $token_value))
            return $RouteHelper->returnWithFlash('auth/users/list', "<strong>Attention !</strong> Mauvais ticket pour supprimer cet utilisateur.", 'danger');
        else {
            $userMail = \CoreHelpers\User::getMailFromId($id);
            if (empty($userMail))
                return $RouteHelper->returnWithFlash('auth/users/list', "Utilisateur #$id inconnu.", 'warning');
            else {
                \CoreHelpers\User::deleteUser($id);
                $msg = "Suppression utilisateur #".$id." : ".$userMail;
                $this->logger->addInfo($msg);
                return $RouteHelper->returnWithFlash('auth/users/list', $msg, 'success');
            }
        }
    })->setName('auth/users/delete');

    $this->get('/edit[/[{id}]]', function ($request, $response, $args) {
        if ($this->Auth->sourceConfig != 'database')
            return $this->Auth->forbidden($response, $this->router, 'auth/users/list', "Il n'est pas possible d'éditer les membres avec une configuration fichier. Migrez vers une configuration base de données.", 'danger');

        $id = !empty($args['id'])? (int)$args['id'] : null;
        $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, $response, (empty($id) ? 'Ajouter un utilisateur' : 'Editer l\'utilisateur #'.$id));
        if (empty($id))
            $user = \CoreHelpers\User::getBlankFields();
        else {
            $userMail = \CoreHelpers\User::getMailFromId($id);
            if (empty($userMail))
                return $RouteHelper->returnWithFlash('auth/users/list', "Utilisateur #$id inconnu.", 'warning');
            $user = \CoreHelpers\User::getUser($this->Auth, $userMail, null, true);
        }
        $token = $this->Auth->getTokenSlimCsrf($this, $request);

        $ErrorsCtrl = new \CoreHelpers\ErrorsController([]);
        if (!empty($_SESSION['errorForm'])) {
            $ErrorsCtrl = $_SESSION['errorForm']['Errors'];
            $user = array_merge($user, $_SESSION['errorForm']['curForm']);
            unset($_SESSION['errorForm']);
        }

        $this->renderer->render($response, 'header.php', compact('RouteHelper', $args));
        $this->renderer->render($response, 'auth/users/edit.php', compact('RouteHelper', 'token', 'ErrorsCtrl', 'user', $args));
        return $this->renderer->render($response, 'footer.php', compact('RouteHelper', $args));
    })->setName('auth/users/edit');

    $this->post('/edit', function ($request, $response, $args) {
        $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, $response, 'Edition utilisateur');

        $post = $request->getParsedBody();
        // var_dump($post);

        $userMail = (empty($post['id'])) ? '' : \CoreHelpers\User::getMailFromId($post['id']);
        $userNewIdMail = (empty($post['newId'])) ? '' : \CoreHelpers\User::getMailFromId($post['newId']);
        if (!empty($post['id']) && empty($userMail))
            return $RouteHelper->returnWithFlash('auth/users/list', "Utilisateur #".$post['id']." inconnu.", 'warning');
        if (empty($RouteHelper->conf['Auth']['canEditUserId']) || empty($post['newId']))
            unset($post['newId']);
        else if (empty($post['id']) && !empty($post['newId']) && !empty($userNewIdMail) || !empty($post['id']) && !empty($post['newId']) && $post['newId'] != $post['id'] && !empty($userNewIdMail))
            return $RouteHelper->returnWithFlash('auth/users/list', "Utilisateur #".$post['newId']." déjà existant.", 'warning');

        // Validation formulaire (ajoute ou mise à jour)
        // $userFields = array_keys(\CoreHelpers\User::getBlankFields());
        if (!empty($userMail)) {
            $curUser = \CoreHelpers\User::getUser($this->Auth, $userMail, null, true, false);
            $pswdAncienValidate = ['field'=>null, 'hash'=>$curUser['password']];
        } else {
            $curUser = null;
            $pswdAncienValidate = null;
        }
        $canBeSkiped = !empty($post['id']);
        if (!empty($post['cas_only'])) {
            $post['password'] = $post['password_confirm'] = 'cas_only';
            $canBeSkiped = true;
        }
        if (!empty($post['ldap_only'])) {
            $post['password'] = $post['password_confirm'] = 'ldap_only';
            $canBeSkiped = true;
        }

        $validate = [
            'first_name'=> array('rule'=>'notEmpty', 'msg' => 'Entrez votre prénom'),
            'last_name' => array('rule'=>'notEmpty', 'msg' => 'Entrez votre nom'),
            'email'     => array('rule'=>'email',    'msg' => 'Entrez un email valide'),
            'password'  => array('rule'=>'password', 'msg' => 'Les mots de passe ne concordent pas !',
                                 'fields'=>['nouveau'=>'password', 'confirmation'=>'password_confirm', 'ancien'=>$pswdAncienValidate],
                                 'canBeSkiped'=>$canBeSkiped)
        ];

        $ErrorsCtrl = new \CoreHelpers\ErrorsController($validate);
        if ($ErrorsCtrl->validate($post)) {
            if ($this->Auth->sourceConfig != 'database')
                // On a laissé faire les autres vérifications, mais si jamais on a pas accès à la bdd, on coupe !!
                return $this->Auth->forbidden($response, $this->router, 'auth/users/list', "Il n'est pas possible d'éditer les membres avec une configuration fichier. Migrez vers une configuration base de données.", 'danger');
            if (!empty($userMail) && $userMail != $post['email'] || empty($userMail)) {
                // Si jamais on a un ajout de user: on check le mail
                // Si jamais on une MAJ de user, il faut checker si le mail est modifié que ce dernier n'existe pas déjà
                if (\CoreHelpers\User::emailExist($post['email']))
                    $ErrorsCtrl->addError('email', 'Email déjà existant');
            }
            $post['roles'] = $this->Auth->getRoles($post['roles']);
        }

        if ($ErrorsCtrl->hasError) { // On a trouvé une erreur, on redirige !
            $_SESSION['errorForm'] = ['Errors' => $ErrorsCtrl, 'curForm' => $post];
            $msg = $ErrorsCtrl->hasError." erreur".($ErrorsCtrl->hasError==1?'':'s')." dans le formlaire";
            if (empty($post['id'])) // Si ajout une personne
                return $RouteHelper->returnWithFlash('auth/users/edit', $msg, 'warning');
            else
                return $RouteHelper->returnWithFlash('auth/users/edit/'.$post['id'], $msg, 'warning');
        } else { // On a effectué toutes les vérifications
            if (empty($post['password']))
                unset($post['password']);
            else if (!in_array($post['password'], ['cas_only', 'ldap_only']))
                $post['password'] = password_hash($post['password'], PASSWORD_BCRYPT);
            // var_dump($post);
            $usrId = $post['id'];
            $rolesSlug = array_keys($post['roles']);
            if (empty($usrId)) {
                unset($post['id'], $post['password_confirm'], $post['csrf_name'], $post['csrf_value'], $post['cas_only'], $post['ldap_only']);
                $usrId = \CoreHelpers\User::insert($post);
                $msg = "Ajout d'un utilisateur #".$usrId." : ".$post['email'].' - '.json_encode($rolesSlug);
                $this->logger->addInfo($msg);
                $this->flash->addMessage('success', $msg);
                echo $msg;
            } else {
                $msg = "MAJ utilisateur #".$usrId." : ".$post['email'].' - '.json_encode($rolesSlug);
                if ((empty($RouteHelper->conf['Auth']['ldapUrl']) && empty($post['password']) && empty($curUser['cas_only']) && $curUser['password']=='ldap_only')
                    || (empty($RouteHelper->conf['Auth']['casUrl']) && empty($post['password']) && empty($curUser['ldap_only']) && $curUser['password']=='cas_only')
                    || (!empty($RouteHelper->conf['Auth']['ldapUrl']) && empty($post['password']) && empty($post['ldap_only']) && !empty($curUser['ldap_only']))
                    || (!empty($RouteHelper->conf['Auth']['casUrl']) && empty($post['password']) && empty($post['cas_only']) && !empty($curUser['cas_only']))) {
                    $ErrorsCtrl->addError('password', 'Veuillez mettre à jour la configuration du mot de passe.');
                    $_SESSION['errorForm'] = ['Errors' => $ErrorsCtrl, 'curForm' => $post];
                    return $RouteHelper->returnWithFlash('auth/users/edit'.'/'.$usrId, 'Veuillez mettre à jour la configuration du mot de passe.', 'warning');
                }
                unset($post['id'], $post['password_confirm'], $post['csrf_name'], $post['csrf_value'], $post['cas_only'], $post['ldap_only']);
                \CoreHelpers\User::update($usrId, $post, $curUser);
                $this->logger->addInfo($msg);
                $this->flash->addMessage('success', $msg);
                echo $msg;

                if ($_SESSION[$this->Auth->AuthId]['email'] == $userMail)
                    $this->Auth->fetchUserAuthLastDbVal($post['email']);
            }
        }
        return $response->withHeader('Location', $this->router->pathFor('auth/users/list'));
    })->setName('auth/users/commit');
  }); // end group auth/user
})->add($container->get('csrf'));