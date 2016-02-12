<?php

class SessionController extends BaseController {
    
    public static function login() {
        View::make('/login.html');
    }
    
    public static function handle_login() {
        $params = $_POST;
        $user = Chef::authenticate($params['username'], $params['password']);
        
        if (!$user) {
            View::make('/login.html', array('error' => 'Väärä käyttäjätunnus tai salasana!', 'username' => $params['username']));
        } else {
            Kint::dump($user);
            $_SESSION['user'] = $user[0]->id;
            
            
            Redirect::to('/', array('message' => 'Tervetuloa ' . $user[0]->name));
        }
    }
    
    public static function handle_logout() {
        unset($_SESSION['user']);
        
        Redirect::to('/', array('message' => 'Uloskirjautuminen onnistui. Näkemiin!'));
    }
}
