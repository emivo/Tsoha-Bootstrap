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
        View::make('recipe/new.html');
    }

    public static function store() {
        $params = $_POST;

        $recipe = new Recipe(array(
            'name' => $params['name'],
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

}
