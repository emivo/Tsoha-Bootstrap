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


$routes->get('/chef/:id', function ($id) {
    ChefController::show($id);
});
$routes->group('/my_profile', function () use ($routes) {
    $routes->get('/', 'check_logged_in', function () {
        ChefController::show(BaseController::get_user_logged_in()->id);
    });

    $routes->get('/edit', 'check_logged_in', function () {
        ChefController::edit();
    });
    $routes->post('/', 'check_logged_in', function () {
        ChefController::update();
    });
    $routes->post('/destroy', 'check_logged_in', function () {
        ChefController::destroy();
    });
});


$routes->get('/chefs/index', function () {
    ChefController::index();
});
$routes->group('/admin', function () use ($routes) {

    $routes->post('/change_account_activity/:id', 'check_if_admin', function ($id) {
        ChefController::toggle_activity($id);
    });

    $routes->post('/change_account_admin_status/:id', 'check_if_admin', function ($id) {
        ChefController::toggle_admin_status($id);
    });
});

$routes->post('/search', function () {
    SearchController::find();
});

$routes->get('/results', function() {
    SearchController::show();
});

$routes->get('/keyword/:key', function ($keyword) {
    SearchController::find_by_keyword($keyword);
});


$routes->group('/recipe', function () use ($routes) {
    $routes->get('s', function () {
        RecipeController::index();
    });

    $routes->post('/', 'check_logged_in', function () {
        RecipeController::store();
    });

    $routes->get('/new', 'check_logged_in', function () {
        RecipeController::create();
    });

    $routes->get('/:id', function ($id) {
        RecipeController::show($id);
    });

    $routes->get('/:id/edit', 'check_logged_in', function ($id) {
        RecipeController::edit($id);
    });

    $routes->post('/:id/edit', 'check_logged_in', function ($id) {
        RecipeController::update($id);
    });

    $routes->post('/:id/delete_keyword/:keyword', 'check_logged_in', function ($id, $keyword) {
        RecipeController::delete_keyword($id, $keyword);
    });
    $routes->post('/:id/delete_ingredient/:ingredient_name', 'check_logged_in', function ($id, $ingredient_name) {
        RecipeController::delete_ingredient($id, $ingredient_name);
    });

    $routes->post('/:id/destroy', 'check_logged_in', function ($id) {
        RecipeController::destroy($id);
    });

    $routes->post('/:id/newcomment', 'check_logged_in', function ($id) {
        RecipeController::new_comment($id);
    });
    $routes->post('/:id/comment/:chef_id/delete', 'check_logged_in', function ($id, $chef_id) {
        RecipeController::delete_comment($id, $chef_id);
    });
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
