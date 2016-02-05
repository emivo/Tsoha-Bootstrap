<?php //

class Recipe extends BaseModel {

    public $id, $chef_id, $name, $cooking_time, $directions, $published;

    public function __construct($attributes) {
        parent::__construct($attributes);
    }

    public static function all() {
        $query = DB::connection()->prepare('SELECT * FROM Recipe');

        $query->execute();

        $rows = $query->fetchAll();

        $recipes = array();

        foreach ($rows as $row) {
            $recipes[] = new Recipe(array(
                'id' => $row['id'],
                'chef_id' => $row['chef_id'],
                'name' => $row['name'],
                'cooking_time' => $row['cooking_time'],
                'directions' => $row['directions'],
                'published' => $row['published'],
            ));
        }

        return $recipes;
    }

    public static function find($id) {
        $query = DB::connection()->prepare('SELECT * FROM Recipe WHERE id = :id LIMIT 1');
        $query->execute(array('id' => $id));
        $row = $query->fetch();
        
        if ($row) {
            $recipe = new Recipe(array(
                'id' => $row['id'],
                'chef_id' => $row['chef_id'],
                'name' => $row['name'],
                'cooking_time' => $row['cooking_time'],
                'directions' => $row['directions'],
                'published' => $row['published'],
            ));
            
            return $recipe;
        }
        
        return null;
    }

}
