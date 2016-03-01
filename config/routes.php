<?php

function check_logged_in()
{
    BaseController::check_logged_in();
}

function check_if_admin()
{
    $user = BaseController::get_user_logged_in();
    if (!$user->admin) {
        Redirect::to('/', array('error' => 'Toiminto on tarkoitettu vain sivuston ylläpitäjälle'));
    }
}

// juuripolku
$routes->get('/', function () {
    RecipeController::index();
});

/**
 * Hiekkalaatikko
 * $routes->get('/hiekkalaatikko', function() {
 * SandboxController::sandbox();
 * });
 */
$routes->get('/register', function () {
    ChefController::register();
});

$routes->post('/register', function () {
    ChefController::store();
});

$routes->get('/recipe/new', 'check_logged_in', function () {
    RecipeController::create();
});

$routes->get('/chef/:id', function ($id) {
    ChefController::show($id);
});

$routes->get('/my_profile', 'check_logged_in', function () {
    ChefController::show(BaseController::get_user_logged_in()->id);
});

$routes->post('/my_profile', 'check_logged_in', function () {
    ChefController::update();
});
$routes->post('/my_profile/destroy', 'check_logged_in', function () {
    ChefController::destroy();
});

$routes->get('/chefs/index', function () {
    ChefController::index();
});

$routes->post('/admin/change_account_activity/:id', 'check_if_admin', function ($id) {
    ChefController::toggle_activity($id);
});

$routes->post('/search', function () {
    SearchController::find();
});

// TODO ehkä muuta hakunäkymä getiksi. Mutta ei ole haittaa että on post sillä vain haku
//$routes->get('/results', function() {
//    SearchController::show();
//});

$routes->get('/keyword/:key', function ($keyword) {
    SearchController::find_by_keyword($keyword);
});

$routes->get('/recipe', function () {
    RecipeController::index();
});

$routes->post('/recipe', 'check_logged_in', function () {
    RecipeController::store();
});

$routes->get('/recipe/:id', function ($id) {
    RecipeController::show($id);
});

$routes->get('/recipe/:id/edit', 'check_logged_in', function ($id) {
    RecipeController::edit($id);
});

$routes->post('/recipe/:id/edit', 'check_logged_in', function ($id) {
    RecipeController::update($id);
});

$routes->post('/recipe/:id/destroy', 'check_logged_in', function ($id) {
    RecipeController::destroy($id);
});

$routes->post('/recipe/:id/newcomment', 'check_logged_in', function ($id) {
    RecipeController::new_comment($id);
});
$routes->post('/recipe/:id/comment/:chef_id/delete', 'check_logged_in', function ($id, $chef_id) {
    RecipeController::delete_comment($id, $chef_id);
});
$routes->get('/login', function () {
    SessionController::login();
});

$routes->post('/login', function () {
    SessionController::handle_login();
});

// uloskirjautuminen gettinä, sillä en vielä tiedä kuinka saisin sen linkin näköisenä postiksi
$routes->post('/logout', 'check_logged_in', function () {
    SessionController::handle_logout();
});
