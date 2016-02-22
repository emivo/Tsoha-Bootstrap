<?php

class SearchController extends BaseController {
    public static function find() {
        $params = $_POST;
        $string = $params['search'];
        $recipes = Recipe::search_default($string);

        View::make('/results.html', array('recipes' => $recipes));
    }
    
    public static function find_by_keyword($keyword) {
        
        $recipes = Recipe::search_by_keyword($keyword);
        // TODO muuta redirectiksi
        View::make('/results.html', array('recipes' => $recipes));
    }
}