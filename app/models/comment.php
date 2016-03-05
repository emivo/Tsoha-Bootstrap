<?php

class Comment extends BaseModel
{

    public $recipe_id, $chef_id, $rating, $comment;

    public function __construct($attributes)
    {
        parent::__construct($attributes);
    }

    public static function find_by_recipe_id($recipe_id)
    {
        $query = DB::connection()->prepare("SELECT * FROM Comment WHERE recipe_id = :id");
        $query->execute(array('id' => $recipe_id));

        return self::fetch_comments_from_query_result($query);

    }

    public static function find_by_chef_id($chef_id)
    {
        $query = DB::connection()
            ->prepare("SELECT * FROM Comment WHERE chef_id = :chef_id");
        $query->execute(array('chef_id' => $chef_id));

        return self::fetch_comments_from_query_result($query);
    }

    public static function delete_chefs_from_recipe($id, $chef_id)
    {
        $comment = new Comment(array(
            'recipe_id' => $id,
            'chef_id' => $chef_id
        ));
        $comment->delete();
    }

    public static function delete_all_from_recipe($recipe_id)
    {
        $query_delete_comments = DB::connection()
            ->prepare("DELETE FROM Comment WHERE recipe_id = :id");
        $query_delete_comments->execute(array('id' => $recipe_id));
    }

    public function delete()
    {
        $query_delete_comments = DB::connection()
            ->prepare("DELETE FROM Comment WHERE recipe_id = :recipe_id AND chef_id = :chef_id");
        $query_delete_comments->execute(array('recipe_id' => $this->recipe_id, 'chef_id' => $this->chef_id));
    }

    /**
     * @param $row
     * @param $comments
     * @return array
     */
    public static function new_comment_from_row($row, $comments)
    {
        $comments[] = new Comment(array(
            'recipe_id' => $row['recipe_id'],
            'chef_id' => $row['chef_id'],
            'rating' => $row['rating'],
            'comment' => $row['comment']
        ));
        return $comments;
    }

    /**
     * @param $query
     * @return array
     */
    public static function fetch_comments_from_query_result($query)
    {
        $rows = $query->fetchAll();
        $comments = array();
        foreach ($rows as $row) {
            $comments = self::new_comment_from_row($row, $comments);
        }
        return $comments;
    }

    public function save()
    {
        $query = DB::connection()
            ->prepare("INSERT INTO Comment (recipe_id, chef_id, rating, comment) VALUES (:recipe, :chef, :rating, :comment)");
        $query->execute(array('recipe' => $this->recipe_id, 'chef' => $this->chef_id, 'rating' => $this->rating, 'comment' => $this->comment));
    }
}
