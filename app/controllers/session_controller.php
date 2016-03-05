<?php

class SessionController extends BaseController
{

    public static function login()
    {
        View::make('/login.html');
    }

    public static function handle_login()
    {
        $params = $_POST;
        $user = Chef::authenticate($params['username'], $params['password']);


        if (!$user) {
            Redirect::to('/login', array('error' => 'Väärä käyttäjätunnus tai salasana!'));
        } elseif ($user->active == false) {
            Redirect::to('/login', array('error' => 'Käyttäjä estetty. Ota yhteyttä ylläpitoon'));
        } else {
            $_SESSION['user'] = $user->id;
            Redirect::to('/', array('message' => 'Tervetuloa ' . $user->name));
        }
    }

    public static function handle_logout()
    {
        unset($_SESSION['user']);

        Redirect::to('/', array('message' => 'Uloskirjautuminen onnistui. Näkemiin!'));
    }
}
