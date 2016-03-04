<?php

class SearchController extends BaseController
{
    // TODO LISÄÄ OPTIONI ETTÄ VOI HAKEA MYÖS KÄYTTÄJÄÄ
    public static function find()
    {
        $params = $_POST;
        // TODO HAKUSANAN PITUUS tms validointi
        if (strlen($params['search']) > 3) {
            Redirect::to('/results?search='.$params['search'].'&option='.$params['option']);
        } else {
            Redirect::to('/', array('error' => 'Kirjoita hakusanaan ainakin 4 merkkiä'));
        }
    }

    public static function show()
    {
        $params = $_GET;
        $search_word = $params['search'];
        if ($params['option'] == 'resepti') {
            $recipes = Recipe::search_default($search_word);
            $comments_for_recipes = array();
            foreach ($recipes as $recipe) {
                $comments_for_recipes[$recipe->id][$recipe->id] = Comment::find_by_recipe_id($recipe->id);
            }
            $chefs = self::find_chefs($recipes);

            View::make('/results.html', array('recipes' => $recipes, 'comments_for_recipes' => $comments_for_recipes, 'chefs' => $chefs));
        } else {
            ChefController::index($search_word);
        }
    }

    public static function find_by_keyword($keyword)
    {

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