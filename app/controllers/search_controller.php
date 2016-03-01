<?php

class SearchController extends BaseController {
    public static function find() {
        $params = $_POST;
        $string = $params['search'];
        $recipes = Recipe::search_default($string);
        $chefs = self::find_chefs($recipes);

        View::make('/results.html', array('recipes' => $recipes, 'chefs' => $chefs));
    }
    
    public static function find_by_keyword($keyword) {
        
        $recipes = Recipe::search_by_keyword($keyword);

        $chefs = self::find_chefs($recipes);
        // TODO muuta redirectiksi
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