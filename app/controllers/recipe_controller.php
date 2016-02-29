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
        $chef = Chef::find($recipe->chef_id);

        View::make('recipe/show.html', array('chef' => $chef, 'recipe' => $recipe, 'comments' => $comments, 'ingredients' => $ingredients, 'keywords' => $keywords));
    }

    public static function create()
    {
            View::make('recipe/new.html');
    }

    public static function store()
    {
        $params = $_POST;
        $chef_id = self::get_user_logged_in()->id;

        $validator = self::validate_params_for_recipe(new Valitron\Validator($params));

        if ($validator->validate()) {

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
            $error = self::combine_errors_to_single_string($validator);
            Redirect::to('/recipe/new', array('error' => $error, 'params' => $params));
        }
    }

    public static function edit($id)
    {

        $recipe = Recipe::find($id);
            $comments = Comment::find_by_recipe_id($id);
            $ingredients = Ingredient::find_by_recipe_id($id);
            $keywords = Keyword::find_by_recipe_id($id);
            View::make('recipe/edit.html', array('recipe' => $recipe, 'comments' => $comments, 'ingredients' => $ingredients, 'keywords' => $keywords));
    }

    public static function update($id)
    {
        $recipe = Recipe::find($id);
        $params = $_POST;

        $validator = new \Valitron\Validator($params);
        $validator = self::validate_params_for_recipe($validator);
        if ($validator->validate()) {
            self::make_changes_to_recipe($id, $params, $recipe);

            $recipe->update();
            Redirect::to('/recipe/' . $id, array('message' => 'Resepti on päivitetty'));
        } else {
            $error = self::combine_errors_to_single_string($validator);
            Redirect::to('/recipe/' . $id . '/edit', array('error' => $error, 'params' => $params));
        }
    }

    public static function destroy($id)
    {
        $recipe = Recipe::find($id);
        $recipe->destroy();
        Redirect::to('/recipe', array('message' => 'Resepti poistettu'));
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
            $error = self::combine_errors_to_single_string($validator);
            Redirect::to('/recipe/' . $id, array('error' => $error));
        }
    }

    public static function delete_comment($id, $chef_id)
    {
        if (self::get_user_logged_in()->id == $chef_id) {
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
        ))->message('');

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
        $validator->rule('required', array(0 => 'name', 1 => 'quantity'))->message('');
        $validator->rule('lengthMin', 'name', 3)->message('Ainesosan nimen pituus tulee olla vähintään 3 merkkiä');
        $validator->rule('lengthMin', 'quantity', 1)->message("Määrän tulee vähintään olla yksi merkki");
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

    /**
     * @param $validator
     * @param $logged
     * @return string
     */
    protected static function combine_errors_to_single_string($validator)
    {
        $error = "Virhe";
        foreach ($validator->errors() as $errors_in_errors) {
            foreach ($errors_in_errors as $err) {
                $error = $error . ".\n" . $err;
            }
        }
        return $error;
    }

    /**
     * @param $id
     * @param $params
     * @param $recipe
     */
    public static function make_changes_to_recipe($id, $params, $recipe)
    {
        $recipe->name = $params['name'];
        $recipe->cooking_time = $params['cooking_time'];
        $recipe->directions = $params['directions'];
        foreach ($params['ingredient'] as $index => $row) {
            $ingredient = Ingredient::find_by_recipe_id_and_ingredient_name($id, $row);
            if ($ingredient) $validator = self::validate_ingredient($ingredient);
            if ($validator->validate()) $ingredient->update();
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
            'comment' => $params['comment']
        ));
    }

}
