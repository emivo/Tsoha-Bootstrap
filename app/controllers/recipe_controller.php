<?php
class RecipeController extends BaseController {
    public static function index() {
        $recipes = Recipe::all();
        
        View::make('recipe/index.html', array('recipes' => $recipes));
    }
    
    public static function show($id) {
        $recipe = Recipe::find($id);
        
        View::make('recipe/show.html', array('recipe' => $recipe));
    }
    
    public static function new_recipe() {
        View::make('recipe/new.html');
    }
}