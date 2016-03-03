<?php

class RecipeController extends BaseController
{

    public static function index()
    {
        $recipes = Recipe::all();
        $chefs = self::find_chefs($recipes);
        $comments_for_recipes = array();
        foreach ($recipes as $recipe) {
            $comments_for_recipes[$recipe->id][$recipe->id] = Comment::find_by_recipe_id($recipe->id);
        }

        View::make('recipe/index.html', array('recipes' => $recipes, 'comments_for_recipes' => $comments_for_recipes, 'chefs' => $chefs));
    }

    public static function find_chefs($recipes)
    {
        $chefs = array();
        foreach ($recipes as $recipe) {
            $chefs[$recipe->chef_id] = Chef::find($recipe->chef_id);
        }
        return $chefs;
    }

    public static function show($id)
    {
        $recipe = Recipe::find($id);
        $comments = Comment::find_by_recipe_id($id);
        $commentators = array();
        foreach ($comments as $comment) {
            $commentators[$comment->chef_id] = Chef::find($comment->chef_id);
        }
        $ingredients = Ingredient::find_by_recipe_id($id);
        $keywords = Keyword::find_by_recipe_id($id);
        $chef = Chef::find($recipe->chef_id);

        View::make('recipe/show.html', array('chef' => $chef, 'recipe' => $recipe, 'comments' => $comments, 'commentators' => $commentators, 'ingredients' => $ingredients, 'keywords' => $keywords));
    }

    public static function create()
    {
        View::make('recipe/new.html');
    }

    public static function store()
    {
        $params = $_POST;
        $chef_id = self::get_user_logged_in()->id;

        $ingredients = self::create_ingredients($params);
        $v = self::validate_ingredients($ingredients);
        $validator = self::validate_params_for_recipe(new Valitron\Validator($params));

        if ($validator->validate() && $v->validate()) {

            $recipe = self::create_recipe_base($params, $chef_id);
            $recipe_id = $recipe->save();
            self::save_ingredients_for_recipe($ingredients, $recipe_id);

            // Invalid keywords will be skipped
            self::create_and_store_keywords($params, $recipe_id);

            Redirect::to('/recipe/' . $recipe->id, array('message' => 'Resepti on julkaistu'));
        } else {
            $error = self::combine_errors_to_single_string($validator, $v);
            Redirect::to('/recipe/new', array('error' => $error, 'params' => $params));
        }
    }

    public static function edit($id)
    {

        $recipe = Recipe::find($id);
        $comments = Comment::find_by_recipe_id($id);
        $ingredients = Ingredient::find_by_recipe_id($id);
        $keywords = Keyword::find_by_recipe_id($id);
        if ($recipe->chef_id == self::get_user_logged_in()->id) {
            View::make('recipe/edit.html', array('recipe' => $recipe, 'comments' => $comments, 'ingredients' => $ingredients, 'keywords' => $keywords));
        } else {
            Redirect::to('recipe/' . $id, array('error' => 'Vain reseptin tekijä voi muokata reseptiä'));
        }
    }

    //TODO MUIST MUUTTAA TÄÄLLÄKIN
    public static function update($id)
    {
        $recipe = Recipe::find($id);
        $params = $_POST;

        $ingredients = self::create_ingredients($params);
        $v = self::validate_ingredients($ingredients);
        $validator = self::validate_params_for_recipe(new \Valitron\Validator($params));

        if ($validator->validate() && $v->validate() && self::get_user_logged_in()->id == $recipe->chef_id) {
            self::make_changes_to_recipe($id, $params, $recipe);

            $recipe->update();
            Redirect::to('/recipe/' . $id, array('message' => 'Resepti on päivitetty'));
        } else {
            $error = self::combine_errors_to_single_string($validator, $v);
            Redirect::to('/recipe/' . $id . '/edit', array('error' => $error, 'params' => $params));
        }
    }

    public static function destroy($id)
    {
        $recipe = Recipe::find($id);
        if ($_SESSION['user'] == $recipe->chef_id || self::get_user_logged_in()->admin) {
            $recipe->destroy();
            Redirect::to('/recipe', array('message' => 'Resepti poistettu'));
        } else {
            Redirect::to('/recipe/' . $id, array('error' => 'Reseptin voi poistaa vain reseptin luoja'));
        }
    }

    public static function delete_keyword($id, $keyword)
    {
        if ($_SESSION['user'] == Recipe::find($id)->chef_id) {
            $keyword = Keyword::find_by_name($keyword);
            $keyword->delete_from_recipe($id);

            Redirect::to('/recipe/' . $id . '/edit', array('message' => 'Hakusana poistettu'));
        } else {
            Redirect::to('/recipe/' . $id, array('error' => 'Reseptin hakusanan voi poistaa vain reseptin luoja'));
        }
    }

    public static function new_comment($id)
    {
        $params = $_POST;
        $chef_id = $_SESSION['user'];
        $validator = self::validate_comment($params);

        if ($validator->validate()) {
            $comment = self::new_comment_object($id, $chef_id, $params);
            $comment->save();

            Redirect::to('/recipe/' . $id);
        } else {
            $error = self::combine_errors_to_single_string($validator, new \Valitron\Validator(array()));
            Redirect::to('/recipe/' . $id, array('error' => $error));
        }
    }

    public static function delete_comment($id, $chef_id)
    {
        if (self::get_user_logged_in()->id == $chef_id || self::get_user_logged_in()->admin) {
            Comment::delete_chefs_from_recipe($id, $chef_id);
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
            'name' => trim($params['name']),
            'chef_id' => $chef_id,
            'cooking_time' => trim($params['cooking_time']),
            'directions' => trim($params['directions'])));
        return $recipe;
    }

    protected static function create_ingredients($params)
    {
        $ingredients = array();
        foreach ($params['ingredient'] as $index => $row) {
            if (strlen(trim($row) . '' . trim($params['quantity'][$index])) > 0) {

                $ingredient = new Ingredient(array(
                    'name' => trim($row),
                    'quantity' => trim($params['quantity'][$index])
                ));

                $ingredients[] = $ingredient;
            }
        }
        return $ingredients;
    }

    /**
     * @param $params
     * @param $recipe_id
     */
    protected static function create_and_store_keywords($params, $recipe_id)
    {
        foreach ($params['keyword'] as $word) {
            self::save_new_keyword($recipe_id, $word);
        }
    }


    protected static function validate_params_for_recipe($validator)
    {

        $validator->rule('required', array(
            'name',
            'cooking_time',
            'directions',
            'ingredient',
            'quantity'
        ))->message('Täytä vaadittavat kentät');

        $validator->rule('lengthMin', 'name', 2)->message('Nimen tulee olla vähintään 2 merkkiä');
        $validator->rule('lengthMin', 'cooking_time', 2)->message('Valmistusaika tulee olla vähintään 2 merkkiä');
        $validator->rule('lengthMin', 'directions', 4)->message('Ohjeiden tulee olla vähintään 4 merkkiä');

        return $validator;
    }


    private static function validate_ingredients($ingredients)
    {
        $array = array();
        foreach ($ingredients as $key => $ingredient) {
            $array['name' . $key] = $ingredient->name;
            $array['quantity' . $key] = $ingredient->quantity;
        }

        $validator = new Valitron\Validator($array);
        foreach ($ingredients as $key => $ingredient) {
            $validator->rule('required', array('name' . $key, 'quantity' . $key))->message('Ainesosille vaaditaan määrä ja ainesosan nimi');
            $validator->rule('lengthMin', 'name' . $key, 3)->message('Ainesosan nimen pituus tulee olla vähintään 3 merkkiä');
            $validator->rule('lengthMin', 'quantity' . $key, 1)->message("Määrän tulee vähintään olla yksi merkki");
        }
        if (count($ingredients) == 0) {
            $validator->error('name', 'Yhtään ainesosaa ei ole annettu');
        }
        return $validator;
    }

    /**
     * @param $params
     * @return \Valitron\Validator
     */
    protected static function validate_comment($params)
    {
        $validator = new Valitron\Validator($params);
        $validator->rule('required', array('rating', 'comment'))->message('');
        $validator->rule('lengthMin', 'comment', 2)->message('Kommentin tulee olla vähintään 2 merkkiä');
        return $validator;
    }

    protected static function combine_errors_to_single_string($validator, $v)
    {
        $error = "Virhe";
        $error = self::loop_through_errors($validator, $error);
        $error = self::loop_through_errors($v, $error);
        $error = $error . ".";
        return $error;
    }

    /**
     * @param $id
     * @param $params
     * @param $recipe
     */
    public static function make_changes_to_recipe($id, $params, $recipe)
    {
        $recipe->name = trim($params['name']);
        $recipe->cooking_time = trim($params['cooking_time']);
        $recipe->directions = trim($params['directions']);
        foreach ($params['ingredient'] as $index => $row) {
            $ingredient = Ingredient::find_by_recipe_id_and_ingredient_name($id, trim($row));
            $ingredient->update();
        }
        foreach ($params['keywordNew'] as $word) {
            // Invalids won't be saved
            self::save_new_keyword($recipe->id, $word);
        }
    }

    /**
     * @param $id
     * @param $chef_id
     * @param $params
     * @return Comment
     */
    public static function new_comment_object($id, $chef_id, $params)
    {
        return $comment = new Comment(array(
            'recipe_id' => $id,
            'chef_id' => $chef_id,
            'rating' => $params['rating'],
            'comment' => trim($params['comment'])
        ));
    }

    public static function save_new_keyword($recipe_id, $word)
    {
        $keyword = new Keyword(array(
            'keyword' => trim($word)));
        if (strlen($keyword->keyword) > 1) {
            $keyword->save($recipe_id);
        }
    }


    protected static function loop_through_errors($validator, $error)
    {
        foreach ($validator->errors() as $errors_in_errors) {
            foreach ($errors_in_errors as $err) {
                $error = $error . ".\n" . $err;
            }
        }
        return $error;
    }

    private static function save_ingredients_for_recipe($ingredients, $recipe_id)
    {
        foreach ($ingredients as $ingredient) {
            $ingredient->recipe_id = $recipe_id;
            $ingredient->save();
        }
    }


}
