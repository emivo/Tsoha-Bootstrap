<?php

class Keyword extends BaseModel {

    public $id, $keyword;

    public function __construct($attributes) {
        parent::__construct($attributes);
    }

    public static function find_by_recipe_id($id) {
        $junction_table_query = DB::connection()
                ->prepare("SELECT * FROM RecipeKeyword WHERE recipe_id = :id");
        $junction_table_query->execute(array('id' => $id));

        $rows = $junction_table_query->fetchAll();

        $keywords = array();

        foreach ($rows as $row) {
            $keyword_query = DB::connection()
                    ->prepare("SELECT * FROM Keyword WHERE id = :keyword_id");
            $keyword_query->execute(array('keyword_id' => $row['keyword_id']));

            $keyword = $keyword_query->fetch();
            $keywords[] = new Keyword(array(
                'id' => $keyword['id'],
                'keyword' => $keyword['keyword']
            ));
        }

        return $keywords;
    }
    
    public function save($recipe_id) {
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

    public static function find_by_name($string) {
        throw Exception("Not supported yet");
    }

}
