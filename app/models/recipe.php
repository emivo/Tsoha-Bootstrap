<?php

class Recipe extends BaseModel
{
// TODO Refactor
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
            $recipes[] = self::new_recipe_from_row($row);
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
            $recipes[] = self::new_recipe_from_row($row);
        }
        return $recipes;
    }

    public static function find($id)
    {
        $query = DB::connection()->prepare('SELECT * FROM Recipe WHERE id = :id LIMIT 1');
        $query->execute(array('id' => $id));
        $row = $query->fetch();

        if ($row) {
            $recipe = self::new_recipe_from_row($row);
            return $recipe;
        }
        return null;
    }

    public static function find_by_chef_id($chef_id) {
        $query  = DB::connection()
            ->prepare("SELECT * FROM Recipe WHERE chef_id = :chef_id");
        $query->execute(array('chef_id' => $chef_id));
        $rows = $query->fetchAll();

        $recipes = array();
        foreach ($rows as $row) {
            $recipes[] = self::new_recipe_from_row($row);
        }
        return $recipes;
    }

    public static function search_default($string)
    {
        $query = DB::connection()
            ->prepare("SELECT Recipe.* FROM Recipe "
                . "LEFT JOIN RecipeKeyword ON Recipe.id = RecipeKeyword.recipe_id "
                . "JOIN Keyword ON Keyword.id = RecipeKeyword.keyword_id "
                . "JOIN RecipeIngredient ON Recipe.id = RecipeIngredient.recipe_id JOIN Ingredient ON Ingredient.id = RecipeIngredient.ingredient_id "
                . "WHERE Recipe.name LIKE :search OR Keyword.keyword LIKE :search OR Ingredient.name LIKE :search");
        // TODO VALIDATE STRING
        $string = "%" . $string . "%";
//        $validator = new Valitron\Validator($string);
//        $validator->addRule($string, $validator);
        $query->bindParam(':search', $string, PDO::PARAM_STR);
        $query->execute();
        $rows = $query->fetchAll();

        $results = array();
        foreach ($rows as $row) {
            $results[] = self::new_recipe_from_row($row);
        }
        return $results;
    }

    public static function search_by_keyword($keyword)
    {

        $query = DB::connection()
            ->prepare("SELECT Recipe.* FROM Recipe "
                . "LEFT JOIN RecipeKeyword ON Recipe.id = RecipeKeyword.recipe_id "
                . "JOIN Keyword ON Keyword.id = RecipeKeyword.keyword_id WHERE Keyword.keyword LIKE :keyword");
        $keyword = '%' . $keyword . '%';
        $query->bindParam(':keyword', $keyword, PDO::PARAM_STR);
        $query->execute();

        $rows = $query->fetchAll();

        $results = array();
        foreach ($rows as $row) {
            $results[] = self::new_recipe_from_row($row);
        }

        return $results;
    }

    /**
     * @param $row
     * @return Recipe
     */
    protected static function new_recipe_from_row($row)
    {
        return new Recipe(array(
            'id' => $row['id'],
            'chef_id' => $row['chef_id'],
            'name' => $row['name'],
            'cooking_time' => $row['cooking_time'],
            'directions' => $row['directions'],
            'published' => $row['published'],
        ));
    }

    public function save()
    {
        $query = DB::connection()->prepare("INSERT INTO Recipe (name, chef_id, cooking_time, directions, published) VALUES (:name, :chef_id, :cooking_time, :directions, NOW()) RETURNING id");

        $query->execute(array('name' => $this->name, 'chef_id' => $this->chef_id, 'cooking_time' => $this->cooking_time, 'directions' => $this->directions));

        $row = $query->fetch();

        $this->id = $row['id'];

        return $this->id;
    }

    public function destroy()
    {
        // poista kommentit
        Comment::delete_all_from_recipe($this->id);
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
        $query->execute(array('name' => $this->name, 'ctime' => $this->cooking_time, 'directions' => $this->directions, 'id' => $this->id));
    }

}
