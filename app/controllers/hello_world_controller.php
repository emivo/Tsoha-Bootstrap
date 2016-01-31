<?php

class HelloWorldController extends BaseController {

    public static function index() {
        // make-metodi renderöi app/views-kansiossa sijaitsevia tiedostoja
        View::make('home.html');
    }

    public static function sandbox() {
        View::make('helloworld.html');
    }

    public static function login() {
        View::make('login.html');
    }

    public static function register() {
        View::make('register.html');
    }
    
    public static function recipe_show() {
        View::make('recipe_show.html');
    }
    
    public static function new_recipe() {
        View::make('new_recipe.html');
    }

}
