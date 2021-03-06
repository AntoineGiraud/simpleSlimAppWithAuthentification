<dl class="dl-horizontal">
    <dt>Configuration :</dt>
    <dd><?= $RouteHelper->Auth->sourceConfig == 'file' ? 'fichier' : 'base de données' ?></dd>
    <?php if (!empty($RouteHelper->conf['Auth']['ldapUrl'])): ?>
        <dt>ldap url</dt>
        <dd><em><?= $RouteHelper->conf['Auth']['ldapUrl'] ?></em></dd>
    <?php endif ?>
    <?php if (!empty($RouteHelper->conf['Auth']['casUrl'])): ?>
        <dt>cas url</dt>
        <dd><em><?= $RouteHelper->conf['Auth']['casUrl'] ?></em></dd>
    <?php endif ?>
    <?php if (file_exists('/etc/crontab')): ?>
        <dt>cron</dt>
        <dd><a href="<?= $RouteHelper->getPathFor('auth/cron') ?>" class="btn btn-xs btn-info">Voir</a></dd>
    <?php endif ?>
</dl>

<h1 class="page-header"><span class="glyphicon glyphicon-certificate"></span> Liste des droits</h1>

<table class="table table-condensed table-bordered table-hover table-striped table-nonfluid">
    <thead>
        <tr>
            <th>type</th>
            <th>nom</th>
            <th>pages accessibles</th>
            <th>pages interdites</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><span class="label label-success">role</span></td>
            <td>superadmin</td>
            <td colspan="2">toutes les pages du site</td>
        </tr>
    <?php foreach($RouteHelper->Auth->permissions['forRole'] as $k => $role): ?>
        <tr>
            <td><span class="label <?= $k == $RouteHelper->Auth->allUserRole ? 'label-info' : 'label-primary' ?>">role</span></td>
            <td>
                <?= $k ?>
                <?php if ($k == $RouteHelper->Auth->allUserRole): ?>
                    <small><em>Pages pour tout les utilisateurs</em></small>
                <?php endif ?>
            </td>
            <td><code><?= implode('</code>, <code>', $role['allowed']) ?></code></td>
            <td><code><?= implode('</code>, <code>', $role['not_allowed']) ?></code></td>
        </tr>
    <?php endforeach ?>
    <?php foreach($RouteHelper->Auth->permissions['forUser'] as $k => $user): ?>
        <tr>
            <td><span class="label label-warning">user</span></td>
            <td><?= $k ?></td>
            <td><code><?= implode('</code>, <code>', $user['allowed']) ?></code></td>
            <td><code><?= implode('</code>, <code>', $user['not_allowed']) ?></code></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>

<h1 class="page-header">Autres éléments</h1>
<div class="row">
    <div class="col-md-8">
        <h2>
          <span class="glyphicon glyphicon-user"></span> Liste des utilisateurs
          <div class="pull-right">
            <a href="<?= $RouteHelper->getPathFor('auth/users/list') ?>" class="btn btn-primary" onlick="">Liste</a>
          </div>
        </h2>
        <table class="table table-condensed table-bordered table-hover table-striped">
            <thead>
                <tr>
                    <th>id</th>
                    <th>email</th>
                    <th>prenom</th>
                    <th>nom</th>
                    <th>role</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($users as $user): ?>
                <tr>
                    <td><span class="label <?= ($user['is_active'])?"label-success":"label-danger" ?>"><?= $user['id'] ?></span></td>
                    <td><?= $user['email'] ?></td>
                    <td><?= $user['first_name'] ?></td>
                    <td><?= $user['last_name'] ?></td>
                    <td><?= ((!empty($user['roles']) && in_array('superadmin', $user['roles'])) ? '<span class="glyphicon glyphicon-king"></span>' : '') . ' ' .implode(', ', $user['roles']) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <div class="col-md-4">
        <h2><span class="glyphicon glyphicon-tower"></span> Liste des roles</h2>
        <table class="table table-condensed table-bordered table-hover table-striped">
            <thead>
                <tr>
                    <th>name</th>
                    <th>slug</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($RouteHelper->Auth->getRoles() as $role): ?>
                <tr>
                    <td><?= $role['name'] ?></td>
                    <td><?= $role['slug'] ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<h2><span class="glyphicon glyphicon-road"></span> Liste des routes de l'application web</h2>
<table class="table table-condensed table-bordered table-hover table-striped table-nonfluid">
    <thead>
        <tr>
            <th>id</th>
            <th>nom</th>
            <th>url</th>
            <th>méthodes</th>
            <th>groupes</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($RouteHelper->Auth->routesSlim as $route): ?>
        <tr>
            <td><?= $route['identifier'] ?></td>
            <td><?= $route['name'] ?></td>
            <td><?= $route['pattern'] ?></td>
            <td><?php if (!empty($route['methods'])): ?>
                <code><?= implode('</code>, <code>', $route['methods']) ?></code>
            <?php endif ?></td>
            <td><?php if (!empty($route['groups'])): ?>
                <?php foreach ($route['groups'] as $group): ?>
                    <code><?php echo $group->getPattern() ?></code>
                <?php endforeach ?>
            <?php endif ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>