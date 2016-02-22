<?php

class Keyword extends BaseModel
{
// TODO Refactor lisää
    public $id, $keyword;

    public function __construct($attributes)
    {
        parent::__construct($attributes);
    }

    public static function find_by_recipe_id($id)
    {
        $junction_table_query = DB::connection()
            ->prepare("SELECT * FROM RecipeKeyword WHERE recipe_id = :id");
        $junction_table_query->execute(array('id' => $id));

        $rows = $junction_table_query->fetchAll();

        $keywords = array();

        foreach ($rows as $row) {
            $keywords = self::find_keyword_for_juction_and_to_array($row, $keywords);
        }

        return $keywords;
    }

    /**
     * return reseptien id:t listana
     */
    public function find_recipes()
    {
        $query = DB::connection()
            ->prepare("SELECT * FROM RecipeKeyword WHERE keyword_id = :id");
        $query->execute(array('id' => $this->id));
        $rows = $query->fetchAll();
        $array_of_recipes = array();
        foreach ($rows as $row) {
            $array_of_recipes[] = $row['recipe_id'];
        }
        if (count($array_of_recipes) > 0) {
            return $array_of_recipes;
        }
        return null;
    }

    /**
     * @param $row
     * @param $keywords
     * @return array
     */
    public static function find_keyword_for_juction_and_to_array($row, $keywords)
    {
        $keyword_query = DB::connection()
            ->prepare("SELECT * FROM Keyword WHERE id = :keyword_id");
        $keyword_query->execute(array('keyword_id' => $row['keyword_id']));

        $keyword = $keyword_query->fetch();
        $keywords[] = new Keyword(array(
            'id' => $keyword['id'],
            'keyword' => $keyword['keyword']
        ));
        return $keywords;
    }

    public function save($recipe_id)
    {
        $query = DB::connection()
            ->prepare("SELECT * FROM Keyword WHERE keyword = :keyword LIMIT 1");
        $query->execute(array('keyword' => $this->keyword));
        $keyword = $query->fetch();

        $insert_query = DB::connection()
            ->prepare("INSERT INTO RecipeKeyword (recipe_id, keyword_id) VALUES (:recipe_id, :keyword_id)");
        if (!$keyword) {
            $new_keyword = DB::connection()
                ->prepare("INSERT INTO Keyword (keyword) VALUES (:keyword) RETURNING id");
            $new_keyword
                ->execute(array('keyword' => $this->keyword));
            $keyword = $new_keyword->fetch();
        }

        $this->id = $keyword['id'];
        $insert_query->execute(array('recipe_id' => $recipe_id, 'keyword_id' => $this->id));
    }

    public static function find_by_name($string)
    {
        throw Exception("Not supported yet");
    }

    public static function delete_unused()
    {
        $query = DB::connection()
            ->prepare("SELECT * FROM Keyword");
        $query->execute();
        $rows = $query->fetchAll();

        foreach ($rows as $row) {
            $keyword = new Keyword(array(
                'id' => $row['id'],
                'keyword' => $row['keyword']
            ));
            if (is_null($keyword->find_recipes())) $keyword->delete();
        }
    }

    public static function delete_junctions($recipe_id)
    {
        $query_delete_keywords = DB::connection()
            ->prepare("DELETE FROM RecipeKeyword WHERE recipe_id = :id");
        $query_delete_keywords->execute(array('id' => $recipe_id));
    }

    public function delete()
    {
        $query = DB::connection()
            ->prepare("DELETE FROM Keyword WHERE id = :id");
        $query->execute(array('id' => $this->id));
    }
}
