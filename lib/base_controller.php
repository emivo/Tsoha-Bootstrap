<?php

  class BaseController{

    public static function get_user_logged_in(){
        if (isset($_SESSION['user'])) {
            $user_id = $_SESSION['user'];
            
            $user = Chef::find($user_id);
            
            return $user;
        }
        return null;
    }

    public static function check_logged_in(){
//        find usages ja muuta TODO
//        if(!isset($_SESSION['user'])){
//            Redirect::to('/login', array('message' => 'Kirjaudu ensin sisään!'));
//        }
      if (!isset($_SESSION['user'])) {

          $error = 'Sinun tulee kirjautua ensin sisään';
          return $error;
      }
        return null;
    }

  }
