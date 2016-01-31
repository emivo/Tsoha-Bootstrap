<?php

$routes->get('/', function() {
    HelloWorldController::index();
});

$routes->get('/hiekkalaatikko', function() {
    HelloWorldController::sandbox();
});

$routes->get('/login', function() {
    HelloWorldController::login();
});

$routes->get('/register', function() {
    HelloWorldController::register();
});

$routes->get('/recipe_show', function() {
    HelloWorldController::recipe_show();
});

$routes->get('/new_recipe', function() {
    HelloWorldController::new_recipe();
});
