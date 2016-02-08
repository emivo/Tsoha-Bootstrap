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

        View::make('recipe/show.html', array('recipe' => $recipe, 'comments' => $comments, 'ingredients' => $ingredients));
    }

    public static function create() {
        View::make('recipe/new.html');
    }

    public static function store() {
        $params = $_POST;
// kokki otetaan sessioista
//        $chef_id = $_SESSION[array()];
        
        $recipe = new Recipe(array(
            'name' => $params['name'],
            'cooking_time' => $params['cooking_time'],
            'directions' => $params['directions'],
            'published' => $params['published'],
            ));
        
    
        $recipe->save();
        
        Redirect::to('/recipe/' . $recipe->id, array('message' => 'Resepti on julkaistu'));
    }

}
