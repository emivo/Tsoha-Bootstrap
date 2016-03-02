<?php

class SearchController extends BaseController {
    public static function find() {
        $params = $_POST;
        $string = $params['search'];
        $recipes = Recipe::search_default($string);
        $comments_for_recipes = array();
        foreach ($recipes as $recipe) {
            $comments_for_recipes[$recipe->id][$recipe->id] = Comment::find_by_recipe_id($recipe->id);
        }
        $chefs = self::find_chefs($recipes);

        View::make('/results.html', array('recipes' => $recipes, 'comments_for_recipes' => $comments_for_recipes, 'chefs' => $chefs));
    }
    
    public static function find_by_keyword($keyword) {
        
        $recipes = Recipe::search_by_keyword($keyword);

        $chefs = self::find_chefs($recipes);
        View::make('/results.html', array('recipes' => $recipes, 'chefs' => $chefs));
    }

    /**
     * @param $recipes
     * @return array
     */
    public static function find_chefs($recipes)
    {
        $chefs = array();
        foreach ($recipes as $recipe) {
            $chefs[$recipe->chef_id] = Chef::find($recipe->chef_id);
        }
        return $chefs;
    }
}