<?php

$routes->get('/', function() {
    RecipeLibraryController::index();
});

$routes->get('/hiekkalaatikko', function() {
    RecipeLibraryController::sandbox();
});

$routes->get('/login', function() {
    RecipeLibraryController::login();
});

$routes->get('/register', function() {
    RecipeLibraryController::register();
});

$routes->get('/recipe_show', function() {
    RecipeLibraryController::recipe_show();
});

$routes->get('/new_recipe', function() {
    RecipeLibraryController::new_recipe();
});

$routes->get('/recipe_modify', function() {
    RecipeLibraryController::recipe_modify();
});

$routes->get('/myprofile', function() {
    RecipeLibraryController::myprofile();
});
