<?php

class RecipeController extends BaseController
{

    public static function index()
    {
        $recipes = Recipe::all();

        View::make('recipe/index.html', array('recipes' => $recipes));
    }

    public static function show($id)
    {
        $recipe = Recipe::find($id);
        $comments = Comment::find_by_recipe_id($id);
        $ingredients = Ingredient::find_by_recipe_id($id);
        $keywords = Keyword::find_by_recipe_id($id);

        View::make('recipe/show.html', array('recipe' => $recipe, 'comments' => $comments, 'ingredients' => $ingredients, 'keywords' => $keywords));
    }

    public static function create()
    {
        if (!self::check_logged_in()) {
            View::make('recipe/new.html');
        } else {
            Redirect::to('/login', array('error' => self::check_logged_in()));
        }
    }

    public static function store()
    {
        $params = $_POST;
        $chef_id = $_SESSION['user'];

        $logged = self::check_logged_in();
        $validator = new Valitron\Validator($params);
        $validator = self::validate_params_for_recipe($validator);

        if ($validator->validate() && !$logged) {

            $recipe = self::create_recipe_base($params, $chef_id);
            $recipe_id = $recipe->save();

            /*
            ** Seuraavat tallentavat vain validit komponentit.
            ** Invalidit vain ohitetaan ja resepti tallentuu niistä huolimatta
            */
            self::create_and_store_ingredients($params, $recipe_id);

            self::create_and_store_keywords($params, $recipe_id);

            Redirect::to('/recipe/' . $recipe->id, array('message' => 'Resepti on julkaistu'));
        } else {
            $validator->errors('user', $logged);
            $error = "Virhe";
            foreach ($validator->errors() as $errors_in_errors) {
                foreach ($errors_in_errors as $err) {
                    $error = $error . ".\n" . $err;
                }
            }
            Redirect::to('/recipe/new', array('error' => $error, 'params' => $params));
        }
    }

    public static function edit($id)
    {

        $recipe = Recipe::find($id);
        if (self::get_user_logged_in() == $recipe->chef_id) {
            $comments = Comment::find_by_recipe_id($id);
            $ingredients = Ingredient::find_by_recipe_id($id);
            $keywords = Keyword::find_by_recipe_id($id);
            View::make('recipe/edit.html', array('recipe' => $recipe, 'comments' => $comments, 'ingredients' => $ingredients, 'keywords' => $keywords));
        } else {
            Redirect::to('/login', array('error' => self::check_logged_in()));
        }
    }

    public static function update($id)
    {
        $recipe = Recipe::find($id);
        $params = $_POST;
        $logged = self::check_logged_in();

        $validator = new \Valitron\Validator($params);
        $validator = self::validate_params_for_recipe($validator);
        if ($validator->validate() && !$logged) {
            $recipe->name = $params['name'];
            $recipe->cooking_time = $params['cooking_time'];
            $recipe->directions = $params['directions'];
            foreach ($params['ingredient'] as $index => $row) {
                $ingredient = Ingredient::find_by_recipe_id_and_ingredient_name($id, $row);
                $validator = self::validate_ingredient($ingredient);
                if ($validator->validate()) $ingredient->update();
            }

            $recipe->update();
            Redirect::to('/recipe/' . $id, array('message' => 'Resepti on päivitetty'));
        } else {
            $validator->errors('user', $logged);
            $error = "Virhe.";
            foreach ($validator->errors() as $errors_in_errors) {
                foreach ($errors_in_errors as $err) {
                    $error = $error . ".\n" . $err;
                }
            }
            Redirect::to('/recipe/' . $id . '/edit', array('error' => $error, 'params' => $params));
        }
    }

    public static function destroy($id)
    {
        $recipe = Recipe::find($id);
        $recipe->destroy();
        Redirect::to('/recipe', array('message' => 'Resepti poistettu'));
    }

    public static function newcomment($id)
    {
        $params = $_POST;
        $chef_id = $_SESSION['user'];
        $logged = self::check_logged_in();

        $validator = new Valitron\Validator($params);
        $validator->rule('required', array('rating', 'comment'));
        $validator->rule('lengthMin', 'comment', 2)->message('Kommentin tulee olla vähintään 2 merkkiä');

        if ($validator->validate() && !$logged) {
            $comment = new Comment(array(
                'recipe_id' => $id,
                'chef_id' => $chef_id,
                'rating' => $params['rating'],
                'comment' => $params['comment']
            ));

            $comment->save();

            Redirect::to('/recipe/' . $id);
        } else {
            $validator->errors('user', $logged);
            $error = "Virhe";
            foreach ($validator->errors() as $errors_in_errors) {
                foreach ($errors_in_errors as $err) {
                    $error = $error . ".\n" . $err;
                }
            }
            Redirect::to('/recipe/' . $id, array('error' => $error));
        }
    }

    public static function deletecomment($id, $chef_id)
    {
        if (self::get_user_logged_in() == $chef_id) {
            $comment = Comment::delete_chefs_from_recipe($id, $chef_id);
            Redirect::to('/recipe/' . $id, array('message' => 'Kommentti poistettu!'));
        } else {
            Redirect::to('/recipe/' . $id, array('error' => 'Voit poistaa vain omia kommentteja!'));
        }
    }

    /**
     * @param $params
     * @param $chef_id
     * @return Recipe
     */
    protected static function create_recipe_base($params, $chef_id)
    {
        $recipe = new Recipe(array(
            'name' => $params['name'],
            'chef_id' => $chef_id,
            'cooking_time' => $params['cooking_time'],
            'directions' => $params['directions']));
        return $recipe;
    }

    /**
     * @param $params
     * @param $recipe_id
     */
    protected static function create_and_store_ingredients($params, $recipe_id)
    {
        foreach ($params['ingredient'] as $index => $row) {

            $ingredient = new Ingredient(array(
                'recipe_id' => $recipe_id,
                'name' => $row,
                'quantity' => $params['quantity'][$index]));
//            $validator = self::validate_ingredient($ingredient);

//            if ($validator->validate()) $ingredient->save();
            $ingredient->save();
        }
    }

    /**
     * @param $params
     * @param $recipe_id
     */
    protected static function create_and_store_keywords($params, $recipe_id)
    {
        foreach ($params['keyword'] as $word) {
            $keyword = new Keyword(array(
                'keyword' => $word));
            if (strlen($keyword->keyword) > 1) {
                $keyword->save($recipe_id);
            }
        }
    }

    /**
     * @param $params
     * @return mixed
     */
    protected static function validate_params_for_recipe($validator)
    {

        $validator->rule('required', array(
            'name',
            'cooking_time',
            'directions',
            'ingredient',
            'quantity'
        ));

        $validator->rule('lengthMin', 'name', 2)->message('Nimen tulee olla vähintään 2 merkkiä');
        $validator->rule('lengthMin', 'cooking_time', 2)->message('Valmistusaika tulee olla vähintään 2 merkkiä');
        $validator->rule('lengthMin', 'directions', 2)->message('Ohjeiden tulee olla vähintään 2 merkkiä');
        return $validator;
    }

    /**
     * @param $ingredient
     * @return \Valitron\Validator
     */
    protected static function validate_ingredient($ingredient)
    {
        $validator = new Valitron\Validator(array('name' => $ingredient->name, 'quantity' => $ingredient));
        $validator->rule('required', array(0 => 'name', 1 => 'quantity'));
        $validator->rule('lengthMin', 'name', 3)->message('Ainesosan nimen pituus tulee olla vähintään 3 merkkiä');
        $validator->rule('lengthMin', 'quantity', 1)->message("Määrän tulee vähintään olla yksi merkki");
        return $validator;
    }

}
