<?php

function check_logged_in(){
    BaseController::check_logged_in();
}
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

$routes->get('/recipe/new', 'check_logged_in', function() {
    RecipeController::create();
});

$routes->get('/chefs/:id', function($id) {
    ChefController::show($id);
});

$routes->get('/chef/myprofile', 'check_logged_in', function() {
    ChefController::my_profile();
});

$routes->post('/chef/myprofile', 'check_logged_in', function() {
    ChefController::update();
});
$routes->post('/chef/myprofile/destroy', 'check_logged_in', function() {
    ChefController::destroy();
});

$routes->post('/search', function() {
    SearchController::find();
});

// TODO ehkä muuta hakunäkymä getiksi. Mutta ei ole haittaa että on post sillä vain haku
//$routes->get('/results', function() {
//    SearchController::show();
//});

$routes->get('/keyword/:key', function($keyword) {
    SearchController::find_by_keyword($keyword);
});

$routes->get('/recipe', function() {
    RecipeController::index();
});

$routes->post('/recipe', 'check_logged_in', function() {
    RecipeController::store();
});

$routes->get('/recipe/:id', function($id) {
    RecipeController::show($id);
});

$routes->get('/recipe/:id/edit', 'check_logged_in', function($id) {
    RecipeController::edit($id);
});

$routes->post('/recipe/:id/edit', 'check_logged_in', function($id) {
    RecipeController::update($id);
});

$routes->post('/recipe/:id/destroy', 'check_logged_in', function($id) {
    RecipeController::destroy($id);
});

$routes->post('/recipe/:id/newcomment', 'check_logged_in', function($id) {
    RecipeController::newcomment($id);
});

$routes->post('/recipe/:id/comment/:chef_id/delete', 'check_logged_in', function($id, $chef_id) {
    RecipeController::delete_comment($id, $chef_id);
});

$routes->get('/login', function() {
    SessionController::login();
});

$routes->post('/login', function() {
    SessionController::handle_login();
});

// uloskirjautuminen gettinä, sillä en vielä tiedä kuinka saisin sen linkin näköisenä postiksi
$routes->get('/logout', 'check_logged_in', function() {
    SessionController::handle_logout();
});
