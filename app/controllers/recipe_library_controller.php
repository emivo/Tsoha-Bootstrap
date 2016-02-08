<?php

class RecipeLibraryController extends BaseController {

    public static function index() {
        $recipes = Recipe::ten_recent();

        View::make('home.html', array('recipes' => $recipes));
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

    public static function myprofile() {
        View::make('myprofile.html');
    }

}
