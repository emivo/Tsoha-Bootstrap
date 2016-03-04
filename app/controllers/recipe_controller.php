<?php

class RecipeController extends BaseController
{

    public static function index()
    {
        $params = $_GET;
        $recipes_count = Recipe::count();
        $page_size = 10;
        $pages = ceil($recipes_count / $page_size);
        if (isset($params['page'])) {
            $page = $params['page'];
            if ($page <= 0 || $page > $pages) {
                Redirect::to('/', array('error' => 'sivua ei ole olemassa'));
            } else {
                $recipes = Recipe::all(array('page' => $page, 'page_size' => $page_size));
                list($chefs, $comments_for_recipes) = self::get_chefs_and_comments_for_recipes($recipes);

                $content = array('recipes' => $recipes, 'comments_for_recipes' => $comments_for_recipes, 'chefs' => $chefs, 'current_page' => $params['page']);
                if ($page > 1) {
                    $content['prev_page'] = $page - 1;
                }
                if ($page < $pages) {
                    $content['next_page'] = $page - 1;
                }
                View::make('recipe/index.html', $content);
            }
        } else {
            $recipes = Recipe::all(array('limit' => 10));
            list($chefs, $comments_for_recipes) = self::get_chefs_and_comments_for_recipes($recipes);

            View::make('home.html', array('recipes' => $recipes, 'comments_for_recipes' => $comments_for_recipes, 'chefs' => $chefs));
        }
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
        $params = self::trim_double_spaces_from_params($_POST);
        $chef_id = self::get_user_logged_in()->id;

        $ingredients = self::create_ingredients($params, false);
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

    /**
     * @param $id
     */
    public static function update($id)
    {
        $recipe = Recipe::find($id);
        $params = self::trim_double_spaces_from_params($_POST);

        $new_ingredients = array();
        $v = new \Valitron\Validator(array());
        if (array_key_exists('ingredientNew', $params)) {
            $new_ingredients = self::create_ingredients($params, true);
            $v = self::validate_ingredients($new_ingredients);
        }
        $validator = self::validate_params_for_recipe(new Valitron\Validator($params));

        if ($validator->validate() && $v->validate() && self::get_user_logged_in()->id == $recipe->chef_id) {
            self::make_changes_to_recipe($params, $recipe, $new_ingredients);

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
            Redirect::to('/recipes?=1', array('message' => 'Resepti poistettu'));
        } else {
            Redirect::to('/recipe/' . $id, array('error' => 'Reseptin voi poistaa vain reseptin luoja'));
        }
    }

    public static function delete_keyword($id, $keyword)
    {
        $keywords_left = count(Keyword::find_by_recipe_id($id));
        if ($_SESSION['user'] == Recipe::find($id)->chef_id && $keywords_left > 1) {
            $keyword = Keyword::find_by_name($keyword);
            if ($keyword) {
                $keyword->delete_from_recipe($id);
                Redirect::to('/recipe/' . $id . '/edit', array('message' => 'Hakusana poistettu'));
            } else {
                Redirect::to('/recipe/' . $id . '/edit', array('error' => 'Poistettavaa hakusana ei ole reseptissä'));
            }
        } elseif ($keywords_left == 1) {
            Redirect::to('/recipe/' . $id . '/edit', array('error' => 'Reseptillä täytyy olla ainakin yksi hakusana'));
        } else {
            Redirect::to('/recipe/' . $id, array('error' => 'Reseptin hakusanan voi poistaa vain reseptin luoja'));
        }
    }

    public static function delete_ingredient($id, $ingredient_name)
    {
        $ingredients_left = count(Ingredient::find_by_recipe_id($id));
        if ($_SESSION['user'] == Recipe::find($id)->chef_id && $ingredients_left > 1) {
            $ingredient = Ingredient::find_by_recipe_id_and_ingredient_name($id, $ingredient_name);
            if ($ingredient) {
                $ingredient->delete_only_from_this_recipe();
                Redirect::to('/recipe/' . $id . '/edit', array('message' => 'Ainesosa poistettu'));
            } else {
                Redirect::to('/recipe/' . $id . '/edit', array('error' => 'Poistettavaa ainesosaa ei ole reseptissä'));
            }

        } elseif ($ingredients_left == 1) {
            Redirect::to('/recipe/' . $id . '/edit', array('error' => 'Reseptillä täytyy olla ainakin yksi ainesosa'));
        } else {
            Redirect::to('/recipe/' . $id, array('error' => 'Reseptin ainesosan voi poistaa vain reseptin luoja'));
        }
    }

    public static function new_comment($id)
    {
        $params = $_POST;
        $params['comment'] = preg_replace('/\s+/', ' ', $params['comment']);
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
            'name' => $params['name'],
            'chef_id' => $chef_id,
            'cooking_time' => $params['cooking_time'],
            'directions' => $params['directions']));
        return $recipe;
    }

    protected static function trim_double_spaces_from_params($params) {
        $params['name'] = preg_replace('/\s+/', ' ', $params['name']);
        $params['cooking_time'] = preg_replace('/\s+/', ' ', $params['cooking_time']);
        $params['directions'] = preg_replace('/\s+/', ' ', $params['directions']);
        return $params;
    }

    protected static function create_ingredients($params, $edit)
    {
        $ingredients = array();
        $i = 'ingredient';
        $q = 'quantity';
        if ($edit) {
            $i = $i . 'New';
            $q = $q . 'New';
        }
        foreach ($params[$i] as $index => $row) {
            // empty fields are ignored
            if (strlen(trim($row) . '' . trim($params[$q][$index])) > 0) {
                $ingredient = new Ingredient(array(
                    'name' => preg_replace('/\s+/', ' ', $row),
                    'quantity' => preg_replace('/\s+/', ' ', $params[$q][$index])
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


    /**
     * @param $validator
     * @return \Valitron\Validator
     */
    protected static function validate_params_for_recipe($validator)
    {

        $validator->rule('required', array(
            'name',
            'cooking_time',
            'directions',
        ))->message('Täytä vaadittavat kentät');

        $validator->rule('lengthMin', 'name', 2)->message('Nimen tulee olla vähintään 2 merkkiä');
        $validator->rule('lengthMax', 'name', 30)->message('Nimi enintään 30 merkkiä');
        $validator->rule('lengthMin', 'cooking_time', 2)->message('Valmistusaika tulee olla vähintään 2 merkkiä');
        $validator->rule('lengthMax', 'cooking_time', 15)->message('Valmistusaika enintää 15 merkkiä');
        $validator->rule('lengthMin', 'directions', 4)->message('Ohjeiden tulee olla vähintään 4 merkkiä');
        $validator->rule('lengthMax', 'directions', 500)->message('Ohjeiden maksimipituus 500 merkkiä');

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
            $validator->rule('lengthMax', 'name' . $key, 15)->message('Ainesosan nimi enintään 15 merkkiä');
            $validator->rule('lengthMin', 'quantity' . $key, 1)->message("Määrän tulee vähintään olla yksi merkki");
            $validator->rule('lengthMax', 'quantity' . $key, 10)->message("Määrä enintään 10 merkkiä");
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
        $validator->rule('required', array('rating', 'comment'))->message('Arvosana sekä kommentti vaaditaan');
        $validator->rule('lengthMin', 'comment', 2)->message('Kommentin tulee olla vähintään 2 merkkiä');
        $validator->rule('lengthMax', 'comment', 100)->message('Kommentin maksimi pituus 100 merkkiä');
        return $validator;
    }

//    protected static function combine_errors_to_single_string($validator, $v)
//    {
//        $error = "Virhe";
//        $error = self::loop_through_errors($validator, $error);
//        $error = self::loop_through_errors($v, $error);
//        $error = $error . ".";
//        return $error;
//    }

    public static function make_changes_to_recipe($params, $recipe, $new_ingredients)
    {
        $recipe->name = $params['name'];
        $recipe->cooking_time = $params['cooking_time'];
        $recipe->directions = $params['directions'];

        if (count($new_ingredients) > 0) {
            self::save_ingredients_for_recipe($new_ingredients, $recipe->id);
        }

        if (array_key_exists('keywordNew', $params)) {
            foreach ($params['keywordNew'] as $word) {
                // Invalids won't be saved
                self::save_new_keyword($recipe->id, $word);
            }
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

    public static function save_new_keyword($recipe_id, $word)
    {
        $keyword = new Keyword(array(
            'keyword' => preg_replace('/\s+/', ' ', $word)));
        if (strlen($keyword->keyword) > 1) {
            $keyword->save($recipe_id);
        }
    }


    private static function save_ingredients_for_recipe($ingredients, $recipe_id)
    {
        foreach ($ingredients as $ingredient) {
            $ingredient->recipe_id = $recipe_id;
            $ingredient->save();
        }
    }

    /**
     * @param $recipes
     * @return array
     */
    public static function get_chefs_and_comments_for_recipes($recipes)
    {
        $chefs = self::find_chefs($recipes);
        $comments_for_recipes = array();
        foreach ($recipes as $recipe) {
            $comments_for_recipes[$recipe->id][$recipe->id] = Comment::find_by_recipe_id($recipe->id);
        }
        return array($chefs, $comments_for_recipes);
    }


}
