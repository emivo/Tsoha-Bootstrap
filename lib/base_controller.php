<?php

class BaseController
{

    public static function get_user_logged_in()
    {
        if (isset($_SESSION['user'])) {
            $user_id = $_SESSION['user'];

            $user = Chef::find($user_id);

            return $user;
        }
        return null;
    }

    public static function check_logged_in()
    {
        if (!isset($_SESSION['user'])) {
            Redirect::to('/login', array('error' => 'Kirjaudu ensin sisään!'));
        }
    }

    protected static function combine_errors_to_single_string($validator, $v = null)
    {
        $error = "Virhe";
        $error = self::loop_through_errors($validator, $error);
        if ($v) {
            $error = self::loop_through_errors($v, $error);
        }
        $error = $error . ".";
        return $error;
    }

    /**
     * @param $validator \Valitron\Validator
     * @param $error
     * @return string
     */
    protected static function loop_through_errors($validator, $error)
    {
        foreach ($validator->errors() as $errors_in_errors) {
            foreach ($errors_in_errors as $err) {
                $error = $error . ".\n" . $err;
            }
        }
        return $error;
    }
}
