<?php

class ChefController extends BaseController
{

    public static function register()
    {
        View::make('register.html');
    }

    public static function index($find = null)
    {
        if (is_null($find)) {
            $chefs = Chef::all();

        } else {
            $chefs = Chef::all($find);
        }
        View::make('chef/index.html', array('chefs' => $chefs));
    }

    public static function show($id)
    {

        $chef = Chef::find($id);
        if ($chef) {
            $recipes = Recipe::find_by_chef_id($chef->id);
            $comments = Comment::find_by_chef_id($chef->id);
            $recipes_for_comments = array();
            foreach ($comments as $comment) {
                $recipes_for_comments[$comment->recipe_id] = Recipe::find($comment->recipe_id);
            }
            View::make('chef/show.html', array('chef' => $chef, 'recipes' => $recipes, 'comments' => $comments, 'recipes_for_comments' => $recipes_for_comments));
        } else {
            Redirect::to('/', array('error' => 'Käyttäjää ei löytynyt'));
        }
    }

    public static function store()
    {
        $params = $_POST;
        $validator = self::validate_params(new Valitron\Validator($params), false);


        if ($validator->validate()) {
            Chef::register($params['username'], $params['password'], $params['info']);
            Redirect::to('/', array('message' => 'Uusi käyttäjä luotu'));
        } else {
            $error = self::combine_errors_to_single_string($validator);
            Redirect::to('/register', array('error' => $error, 'username' => $params['username']));
        }
    }

    public static function edit()
    {
        $chef = self::get_user_logged_in();
        View::make('chef/edit.html', array('chef' => $chef));
    }

    public static function update()
    {
        $params = $_POST;
        $validator = self::validate_params(new Valitron\Validator($params), true);
        if ($validator->validate()) {
            $chef = self::get_user_logged_in();
            $chef->info = $params['info'];
            $chef->update_info();
            if (strlen($params['password'] > 3)) {
                $chef->update_password($params['password']);
            }

            Redirect::to('/', array('message' => 'Käyttäjätiedot päivitetty'));
        } else {
            $error = self::combine_errors_to_single_string($validator);
            Redirect::to('/my_profile/edit', array('error' => $error));
        }
    }

    public static function destroy()
    {
        $chef = self::get_user_logged_in();
        if ($chef->admin) {
            Redirect::to('/', array('error' => 'Ylläpitäjää ei voi poistaa'));
        } else {
            $chef->destroy();
            Redirect::to('/', array('message' => 'Käyttäjä sekä käyttäjän reseptit, että kommentit poistettu'));
        }
    }

    public static function toggle_activity($id)
    {

        $chef = Chef::find($id);
        if ($chef) {
            if ($chef->admin) {
                Redirect::to('/', array('error' => 'Ylläpitäjää ei voi estää'));
            } else {
                $chef->toggle_activity();
                if ($chef->active) {
                    Redirect::to('/chef/' . $id, array('message' => 'Käyttäjän esto poistettu'));
                } else {
                    Redirect::to('/chef/' . $id, array('message' => 'Käyttäjä estetty'));
                }
            }
        } else {
            Redirect::to('/', array('error' => 'Käyttäjää ei ole'));
        }
    }

    public static function toggle_admin_status($id)
    {
        $chef = Chef::find($id);
        if ($chef) {
            if ($chef->name == 'admin') {
                Redirect::to('/', array('Tältä ylläpitäjältä ei voi poistaa ylläpito-oikeuksia'));
            } else {
                $chef->toggle_admin_status();
                if ($chef->admin) {
                    Redirect::to('/chef/' . $id, array('message' => 'Käyttäjällä on nyt ylläpito-oikeudet'));
                } else {
                    Redirect::to('/chef/' . $id, array('message' => 'Käyttäjän oikeudet poistettu'));
                }
            }
        } else {
            Redirect::to('/', array('error' => 'Käyttäjää ei ole'));
        }
    }


    /**
     * @param $validator
     * @param bool $update
     * @return \Valitron\Validator
     */
    protected static function validate_params($validator, $update = false)
    {
        if (!$update) {
            $validator->rule('required', array('username', 'password', 'password_confirm'))->message('Täytä vaadittavat kentät');
            $validator->rule('slug', 'username')->message('Käyttäjänimeen vain a-z, 0-9, -, _, merkkejä');
            $chefs = Chef::all();
            $chefnames = array();
            foreach ($chefs as $chef) {
                $chefnames[] = $chef->name;
            }
            $validator->rule('notIn', 'username', $chefnames)->message('Käyttäjänimi on jo olemassa');
            $validator->rule('lengthMin', 'username', 4)->message('Käyttäjänimen tulee olla vähintää 4 merkkiä');
            $validator->rule('lengthMax', 'username', 20)->message('Käyttäjänimi enintään 20 merkkiä');
        }
        $validator->rule('lengthMin', 'password', 4)->message('Salasanan tulee olla vähintään 4 merkkiä');
        $validator->rule('lengthMax', 'password', 72)->message('Salasana enintään 72 merkkiä');
        $validator->rule('equals', 'password', 'password_confirm')->message('Salasana ei täsmää');

        $validator->rule('required', 'info')->message('Täytä tietoja kenttä');
        $validator->rule('lengthMin', 'info', 4)->message('Tietojen tulee olla vähintään 4 merkkiä');
        $validator->rule('lengthMax', 'info', 200)->message('Tietojen tulee olla enintään 200 merkkiä');

        return $validator;
    }
}
