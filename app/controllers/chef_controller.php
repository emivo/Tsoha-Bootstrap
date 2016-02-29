<?php

class ChefController extends BaseController
{

    public static function register()
    {
        View::make('register.html');
    }

    public static function index()
    {
        $chefs = Chef::all();
        View::make('chef/index.html', array('chefs' => $chefs));
    }

    /**
     * Näytä yksittäinen käyttäjä. Jos katselee omaa profiilia mahdollisuus vaihtaa salasana
     * @param $id
     */
    public static function show($id)
    {

        $chef = Chef::find($id);
        if ($chef) {
            $recipes = Recipe::find_by_chef_id($chef->id);
            $comments = Comment::find_by_chef_id($chef->id);
            View::make('chef/show.html', array('chef' => $chef, 'recipes' => $recipes, 'comments' => $comments));
        } else {
            Redirect::to('/', array('error' => 'Käyttäjää ei löytynyt'));
        }
    }

    public static function store()
    {
        $params = $_POST;
        $validator = self::validate_params(new Valitron\Validator($params));

        if ($validator->validate()) {
            Chef::register($params['username'], $params['password']);
            Redirect::to('/', array('message' => 'Uusi käyttäjä luotu'));
        } else {
            $error = self::collect_errors($validator);
            Redirect::to('/register', array('error' => $error, 'username' => $params['username']));
        }
    }

    public static function update()
    {
        $params = $_POST;
        $validator = self::validate_params(new Valitron\Validator($params));

        if ($validator->validate()) {
            $chef = self::get_user_logged_in();
            $chef->update($params['password']);

            Redirect::to('/', array('message' => 'Käyttäjätiedot päivitetty'));
        } else {
            $error = self::collect_errors($validator);
            Redirect::to('/chef/my_profile', array('error' => $error));
        }
    }

    public static function destroy()
    {
        $chef = self::get_user_logged_in();
        $chef->destroy();
        // TODO varmistus, DONt drink and root
        Redirect::to('/', array('message' => 'Käyttäjä sekä käyttäjän reseptit, että kommentit poistettu'));
    }

    public static function toggle_activity($id)
    {

        $chef = Chef::find($id);
        $chef->toggle_activity();
        if ($chef->active) {
            Redirect::to('/chef/'.$id, array('message' => 'Käyttäjän esto poistettu'));
        } else {
            Redirect::to('/chef/'.$id, array('message' => 'Käyttäjä estetty'));
        }
    }


    protected static function validate_params($validator)
    {
        $validator->rule('required', array('username', 'password', 'password_confirm'))->message('');
        $validator->rule('lengthMin', 'username', 4)->message('Käyttäjänimen tulee olla vähintää 4 merkkiä');
        $validator->rule('lengthMin', 'password', 4)->message('Salasanan tulee myös olla vähintään 4 merkkiä');
        $validator->rule('equals', 'password', 'password_confirm')->message('Salasana ei täsmää');
        return $validator;
    }

    public static function collect_errors($validator)
    {
        $error = 'Virhe';
        foreach ($validator->errors() as $errors_in_errors) {
            foreach ($errors_in_errors as $err) {
                $error = $error . ".\n" . $err;
            }
        }
        return $error;
    }

}
