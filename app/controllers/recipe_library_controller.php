<?php

class RecipeLibraryController extends BaseController {

    public static function index() {
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
    
    
    public static function recipe_modify() {
        View::make('recipe_modify.html');
    }
    
    public static function myprofile() {
        View::make('myprofile.html');
    }

}
