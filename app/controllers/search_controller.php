<?php

class SearchController extends BaseController
{
    public static function search()
    {
        $params = $_POST;
        if (strlen($params['search']) > 3) {
            Redirect::to('/results?search=' . $params['search'] . '&option=' . $params['option']);
        } else {
            Redirect::to('/', array('error' => 'Kirjoita hakusanaan ainakin 4 merkkiä'));
        }
    }

    public static function show_search_results()
    {
        $params = $_GET;
        $search_word = $params['search'];
        if ($params['option'] == 'resepti') {
            $recipes = Recipe::search_default($search_word);
            $path = 'results?search=' . $search_word . '&option=resepti&';
            self::make_paged_view($recipes, $path, 'results.html');
        } else {
            ChefController::index($search_word);
        }
    }

    public static function find_by_keyword($keyword)
    {
        $recipes = Recipe::search_by_keyword($keyword);
        $path = 'keyword/' . $keyword . '?';
        self::make_paged_view($recipes, $path, 'keyword/index.html');
    }

    /**
     * @param $recipes
     * @param $path
     * @param $template
     */
    public static function make_paged_view($recipes, $path, $template)
    {
        $pages = ceil(count($recipes) / 10);
        if ($pages == 0) $pages = 1;
        $page = 1;
        if (isset($_GET['page'])) $page = $_GET['page'];
        if ($page < 1 || $page > $pages) {
            Redirect::to('/', array('error' => 'Sivua ei ole olemassa'));
        } else {
            //
            if (count($recipes) > 0) {
                $recipes = array_chunk($recipes, 10);
            } else {
                $recipes[0] = array();
            }
            $content = RecipeController::make_paged_content($pages, $recipes[$page - 1], $page);
            $content['path'] = $path;
            View::make($template, $content);
        }
    }
}