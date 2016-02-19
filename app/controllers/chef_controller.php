<?php

class ChefController extends BaseController {

    public static function register() {
        View::make('register.html');
    }

    public static function myprofile() {
        View::make('myprofile.html');
    }

    public static function show($id) {
        throw new Exception("Not supported yet");
    }

    public static function store() {
        $params = $_POST;
        $v = new Valitron\Validator($params);
        $v->rule('required', array('username', 'password', 'password_confirm'))->message('');
        $v->rule('lengthMin','username', 4)->message('Käyttäjänimen tulee olla vähintää 4 merkkiä');
        $v->rule('lengthMin','password', 4)->message('Salasanan tulee myös olla vähintään 4 merkkiä');
        $v->rule('equals','password', 'password_confirm')->message('Salasana ei täsmää');
        
        if ($v->validate()) {
            $new_chef = Chef::register($params['username'], $params['password']);

            Redirect::to('/', array('message' => 'Uusi käyttäjä luotu'));
        } else {
            $error = 'Virhe';
            foreach ($v->errors() as $errors_in_errors) {
                foreach ($errors_in_errors as $err) {
                    $error = $error . ".\n" . $err;
                }
            }
            Redirect::to('/register', array('error' => $error, 'username' => $params['username']));
        }
    }

}
