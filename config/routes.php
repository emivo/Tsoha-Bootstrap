<?php

$routes->get('/', function() {
    RecipeLibraryController::index();
});

$routes->get('/hiekkalaatikko', function() {
    SandboxController::sandbox();
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

$routes->get('/recipe/new', function() {
    RecipeController::create();
});

$routes->get('/recipe_modify', function() {
    RecipeLibraryController::recipe_modify();
});

$routes->get('/myprofile', function() {
    RecipeLibraryController::myprofile();
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
