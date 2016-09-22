<h1 class="page-header"><span class="glyphicon glyphicon-certificate"></span> Liste des droits</h1>

<table class="table table-condensed table-bordered table-hover table-striped table-nonfluid">
    <thead>
        <tr>
            <th>type</th>
            <th>nom</th>
            <th>pages accessibles</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><span class="label label-success">role</span></td>
            <td>admin</td>
            <td>toutes les pages du site</td>
        </tr>
    <?php foreach($SettingsAuth['permissions']['forRole'] as $k => $routes): ?>
        <tr>
            <td><span class="label <?= $k == 'allUsers' ? 'label-info' : 'label-primary' ?>">role</span></td>
            <td><?= $k ?></td>
            <td><code><?= implode('</code>, <code>', $routes) ?></code></td>
        </tr>
    <?php endforeach ?>
    <?php foreach($SettingsAuth['permissions']['forUser'] as $k => $routes): ?>
        <tr>
            <td><span class="label label-warning">user</span></td>
            <td><?= $k ?></td>
            <td><code><?= implode('</code>, <code>', $routes) ?></code></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>

<h1 class="page-header">Autres éléments</h1>
<h2><span class="glyphicon glyphicon-tower"></span> Liste des roles</h2>
<table class="table table-condensed table-bordered table-hover table-striped table-nonfluid">
    <thead>
        <tr>
            <th>level</th>
            <th>name</th>
            <th>slug</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($SettingsAuth['roles'] as $role): ?>
        <tr>
            <?php foreach ($role as $k => $v): ?>
                <td><?= $v ?></td>
            <?php endforeach ?>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>

<h2><span class="glyphicon glyphicon-user"></span> Liste des utilisateurs</h2>
<table class="table table-condensed table-bordered table-hover table-striped table-nonfluid">
    <thead>
        <tr>
            <th>online</th>
            <th>email</th>
            <th>prenom</th>
            <th>nom</th>
            <th>role</th>
            <th>level</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($SettingsAuth['users'] as $role): ?>
        <tr>
            <td><span class="label <?= ($role['online'])?"label-success":"label-danger" ?>"><?= $role['online'] ?></span></td>
            <td><?= $role['email'] ?></td>
            <td><?= $role['prenom'] ?></td>
            <td><?= $role['nom'] ?></td>
            <td><?= (($role['slug'] == 'admin')?'<span class="glyphicon glyphicon-king"></span>':'') . ' ' .$role['slug'] ?></td>
            <td><?= $role['level'] ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>

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
    <?php foreach($routesSlim as $route): ?>
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