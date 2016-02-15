<?php

// juuripolku
$routes->get('/', function() {
    RecipeController::index();
});

/**
 * Hiekkalaatikko
  $routes->get('/hiekkalaatikko', function() {
  SandboxController::sandbox();
  });
 */
$routes->get('/register', function() {
    ChefController::register();
});

$routes->post('/register', function() {
    ChefController::store();
});

$routes->get('/recipe/new', function() {
    RecipeController::create();
});

$routes->get('/myprofile', function() {
    ChefController::myprofile();
});

$routes->get('/recipe', function() {
    RecipeController::index();
});

$routes->post('/recipe', function() {
    RecipeController::store();
});

$routes->get('/recipe/:id', function($id) {
    RecipeController::show($id);
});

$routes->get('/recipe/:id/edit', function($id) {
    RecipeController::edit($id);
});

$routes->post('/recipe/:id/edit', function($id) {
    RecipeController::update($id);
});

$routes->post('/recipe/:id/destroy', function($id) {
    RecipeController::destroy($id);
});

$routes->post('/recipe/:id/newcomment', function($id) {
    RecipeController::newcomment($id);
});

$routes->post('/recipe/:id/comment/:chef_id/delete', function($id, $chef_id) {
    RecipeController::deletecomment($id, $chef_id);
});

$routes->get('/login', function() {
    SessionController::login();
});

$routes->post('/login', function() {
    SessionController::handle_login();
});

// uloskirjautuminen gettinä, sillä en vielä tiedä kuinka saisin sen linkin näköisenä postiksi
$routes->get('/logout', function() {
    SessionController::handle_logout();
});
