<?php

///////////////////////////////
// Routes pour les dispatchs //
///////////////////////////////

// Page ouverte à tous
$app->get('/operations/vue_operations', function ($request, $response, $args) {
    global $Auth;

    $flash = $this->flash;
    $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, 'vue opérations');

    $this->renderer->render($response, 'header.php', compact('Auth', 'flash', 'RouteHelper', $args));
    $this->renderer->render($response, 'operations/vue_operations.php', compact('Auth', $args));
    return $this->renderer->render($response, 'footer.php', compact('Auth', 'RouteHelper', $args));
})->setName('operations/vue_operations');

/////////////////
// Espace Icam //
/////////////////

$app->get('/operations/vuePersoOperations', function ($request, $response, $args) {
    global $Auth, $DB;

    $flash = $this->flash;
    $RouteHelper = new \CoreHelpers\RouteHelper($this, $request, 'vue perso opérations');

    // Sample log message
    // $this->logger->info("Slim-Skeleton '/' index");

    // Render index view
    $this->renderer->render($response, 'header.php', compact('flash', 'RouteHelper', 'Auth', $args));
    $this->renderer->render($response, 'operations/vuePersoOperations.php', compact('RouteHelper', 'Auth', $args));
    return $this->renderer->render($response, 'footer.php', compact('RouteHelper', 'Auth', $args));
})->setName('operations/vuePersoOperations');