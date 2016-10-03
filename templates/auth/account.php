<?php $user = $Auth->getSessionUser() ?>

<h1 class="page-header"><span class="glyphicon glyphicon-user"></span> Mon compte</h1>
<h2>Informations personnelles</h2>
<dl class="dl-horizontal">
    <dt>Email</dt>
    <dd><?= $user['email'] ?></dd>
    <dt>Prénom</dt>
    <dd><?= $user['first_name'] ?></dd>
    <dt>Nom</dt>
    <dd><?= $user['last_name'] ?></dd>
    <dt>Etat du compte</dt>
    <dd>
        <?php if ($user['online' ]) { ?>
            <span class="label label-success">en ligne</span>
        <?php } else { ?>
            <span class="label label-danger">hors ligne</span>
        <?php } ?>
    </dd>
    <dt>Connexion <abbr title="Service Central d'Authentification">CAS</abbr></dt>
    <dd>
        <?php if ($Auth->isLoggedUsingCas()) { ?>
            <span class="label label-success">Oui</span>
        <?php } else { ?>
            <span class="label label-danger">Non</span>
        <?php } ?>
    </dd>
</dl>

<h2>Permissions</h2>
<dl class="dl-horizontal">
    <dt>Rôles</dt>
    <dd>
        <ul>
            <?php foreach ($user['roles'] as $role): ?>
                <li><?= $role['name'] ?></li>
            <?php endforeach ?>
        </ul>
    </dd>
</dl>

<?php if ($Auth->isSuperAdmin($user)) { ?>
    <p class="alert alert-info">En tant qu'administrateur, vous avez accès à toutes les pages.</p>
<?php } else if (!empty($user['permissions'])) { ?>
    <h4>Pages auxquelles vous avez accès</h4>
    <ul>
        <?php foreach ($user['permissions'] as $ok): ?>
            <li><?= $ok ?></li>
        <?php endforeach ?>
    </ul>
<?php } ?>