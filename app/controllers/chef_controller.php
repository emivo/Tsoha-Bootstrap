<?php

class ChefController extends BaseController {

    public static function register() {
        View::make('register.html');
    }

    public static function myprofile() {
        View::make('myprofile.html');
    }

    public static function show($id) {
        throw "Not supported yet";
    }

    public static function store() {
        $params = $_POST;
        if ($params['password'] == $params['password_confirm']) {
            $new_chef = Chef::register($params['username'], $params['password']);

            Redirect::to('/', array('message' => 'Uusi käyttäjä luotu'));
        } else {
            Redirect::to('/register', array('error' => 'Salasana ei täsmää', 'username' => $params['username']));
        }
    }

}
