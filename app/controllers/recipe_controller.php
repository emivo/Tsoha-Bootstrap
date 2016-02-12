<?php

class RecipeController extends BaseController {

    public static function index() {
        $recipes = Recipe::all();

        View::make('recipe/index.html', array('recipes' => $recipes));
    }

    public static function show($id) {
        $recipe = Recipe::find($id);
        $comments = Comment::find_by_recipe_id($id);
        $ingredients = Ingredient::find_by_recipe_id($id);
        $keywords = Keyword::find_by_recipe_id($id);

        View::make('recipe/show.html', array('recipe' => $recipe, 'comments' => $comments, 'ingredients' => $ingredients, 'keywords' => $keywords));
    }

    public static function create() {
        if (self::get_user_logged_in()) {
            View::make('recipe/new.html');
        } else {
            self::check_logged_in();
        }
    }

    public static function store() {
        $params = $_POST;
        $chef_id = $_SESSION['user'];
        
        $recipe = new Recipe(array(
            'name' => $params['name'],
            'chef_id' => $chef_id,
            'cooking_time' => $params['cooking_time'],
            'directions' => $params['directions']));

        $recipe_id = $recipe->save();

        foreach ($params['ingredient'] as $index => $row) {
            $ingredient = new Ingredient(array(
                'recipe_id' => $recipe_id,
                'name' => $row,
                'quantity' => $params['quantity'][$index]));

            $ingredient->save();
        }

        foreach ($params['keyword'] as $word) {
            $keyword = new Keyword(array(
                'keyword' => $word));

            $keyword->save($recipe_id);
        }

        Redirect::to('/recipe/' . $recipe->id, array('message' => 'Resepti on julkaistu'));
    }

    public static function edit($id) {
        $recipe = Recipe::find($id);
        $comments = Comment::find_by_recipe_id($id);
        $ingredients = Ingredient::find_by_recipe_id($id);
        $keywords = Keyword::find_by_recipe_id($id);
        View::make('recipe/edit.html', array('recipe' => $recipe, 'comments' => $comments, 'ingredients' => $ingredients, 'keywords' => $keywords));
    }

    public static function update($id) {
        $recipe = Recipe::find($id);
        $params = $_POST;

        $recipe->name = $params['name'];
        $recipe->cooking_time = $params['cooking_time'];
        $recipe->directions = $params['directions'];
// täytynee vielä miettiä
//        foreach ($params['ingredient'] as $row) {
//            $ingredient = Ingredient::find_by_recipe_id_and_ingredient_id($id, $row['']);
//
//            $ingredient->update();
//        }

        $recipe->update();
        Redirect::to('/recipe/' . $id, array('message' => 'Resepti on päivitetty'));
    }

    public static function destroy($id) {
        $recipe = Recipe::find($id);
        $recipe->destroy();
        Redirect::to('/recipe', array('message' => 'Resepti poistettu'));
    }
    
    public static function newcomment($id) {
        $params = $_POST;
        $chef_id = $_SESSION['user'];
        
        $comment = new Comment(array(
            'recipe_id' => $id,
            'chef_id' => $chef_id,
            'rating' => $params['rating'],
            'comment' => $params['comment']
        ));
        
        $comment->save();
        
        Redirect::to('/recipe/' . $id);
    }

}
