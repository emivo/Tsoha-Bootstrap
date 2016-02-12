<?php

class Comment extends BaseModel {

    public $recipe_id, $chef_id, $rating, $comment;

    public function __construct($attributes) {
        parent::__construct($attributes);
    }

    public static function find_by_recipe_id($id) {
        $query = DB::connection()->prepare("SELECT * FROM Comment WHERE recipe_id = :id");
        $query->execute(array('id' => $id));

        $rows = $query->fetchAll();

        $comments = array();
        
        foreach ($rows as $row) {
            $comments[] = new Comment(array(
                'recipe_id' => $id,
                'chef_id' => $row['chef_id'],
                'rating' => $row['rating'],
                'comment' => $row['comment']
            ));
        }
        
        return $comments;
    }

    public function save() {
        $query = DB::connection()
            ->prepare("INSERT INTO Comment (recipe_id, chef_id, rating, comment) VALUES (:recipe, :chef, :rating, :comment)");
        $query->execute(array('recipe' => $this->recipe_id, 'chef' => $this->chef_id, 'rating' => $this->rating, 'comment' => $this->comment));
    }

}
