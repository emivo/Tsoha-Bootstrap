<?php

//

class Recipe extends BaseModel
{

    public $id, $chef_id, $name, $cooking_time, $directions, $published;

    public function __construct($attributes)
    {
        parent::__construct($attributes);
    }

    public static function all()
    {
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

    public static function ten_recent()
    {
        $query = DB::connection()->prepare('SELECT * FROM Recipe ORDER BY published DESC LIMIT 10');

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

    public static function find($id)
    {
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

    public function save()
    {
        $query = DB::connection()->prepare("INSERT INTO Recipe (name, cooking_time, directions, published) VALUES (:name, :cooking_time, :directions, NOW()) RETURNING id");

        $query->execute(array('name' => $this->name, 'cooking_time' => $this->cooking_time, 'directions' => $this->directions));

        $row = $query->fetch();

        $this->id = $row['id'];

        return $this->id;
    }

    public function destroy()
    {
        // poista kommentit
        Comment::delete_from_recipe($this->id);
        //poista ainesosat
        Ingredient::delete_from_recipe($this->id);
        Ingredient::delete_unused();
        //poista avainsanat
        Keyword::delete_junctions($this->id);
        Keyword::delete_unused();

        $query = DB::connection()
            ->prepare("DELETE FROM Recipe WHERE id = :id");
        $query->execute(array('id' => $this->id));
    }

    public function update()
    {
        $query = DB::connection()
            ->prepare("UPDATE Recipe SET name = :name, cooking_time = :ctime, directions = :directions WHERE id = :id");
        $query->execute(array('name' => $this->name, 'ctime' => $this->cooking_time, 'directions' => $this->directions));
    }

}
