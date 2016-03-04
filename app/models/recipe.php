<?php

class Recipe extends BaseModel
{
    public $id, $chef_id, $name, $cooking_time, $directions, $published;

    public function __construct($attributes)
    {
        parent::__construct($attributes);
    }

    public static function all($option = null)
    {

        $query_string = 'SELECT * FROM Recipe ORDER BY published DESC';
        if ($option && isset($option['limit'])) {
            $query_string .= ' LIMIT ' . $option['limit'];
        } else {
            if ($option && isset($option['page']) && isset($option['page_size'])) {
                $page_size = $option['page_size'];
                $offset = $page_size * ($option['page'] - 1);
                $query_string .= ' LIMIT ' . $page_size . ' OFFSET ' . $offset;
            }
        }
        $query = DB::connection()->prepare($query_string);
        $query->execute();
        $rows = $query->fetchAll();

        return self::recipes_from_rows($rows);
    }

    public static function count()
    {
        $query = DB::connection()->prepare('SELECT COUNT(*) FROM Recipe');
        $query->execute();
        $row = $query->fetch();
        if (is_null($row)) return 0;
        return $row['count'];
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

    public static function find_by_chef_id($chef_id)
    {
        $query = DB::connection()
            ->prepare("SELECT * FROM Recipe WHERE chef_id = :chef_id");
        $query->bindParam(':chef_id', $chef_id, PDO::PARAM_INT);
        $query->execute();
        $rows = $query->fetchAll();

        return self::recipes_from_rows($rows);
    }

    public static function search_default($string)
    {
        $query = DB::connection()
            ->prepare(self::search_query_string());
        $string = "%" . $string . "%";
        $rows = self::bind_param_and_fetch_from_query($query, ':search', $string);
        return self::recipes_from_rows($rows);
    }

    public static function search_by_keyword($keyword)
    {

        $query_string = "SELECT DISTINCT Recipe.* FROM Recipe "
            . "LEFT JOIN RecipeKeyword ON Recipe.id = RecipeKeyword.recipe_id "
            . "JOIN Keyword ON Keyword.id = RecipeKeyword.keyword_id WHERE Keyword.keyword LIKE :keyword";
        $query = DB::connection()
            ->prepare($query_string);
        $keyword = '%' . $keyword . '%';
        $rows = self::bind_param_and_fetch_from_query($query, ':keyword', $keyword);
        $recipes = self::recipes_from_rows($rows);
        return $recipes;
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
            'published' => date("j.n.Y", strtotime($row['published']))
        ));
    }

    /**
     * @param $query
     * @param $param
     * @param
     * @return mixed
     */
    protected static function bind_param_and_fetch_from_query($query, $param, $string)
    {
        $query->bindParam($param, $string, PDO::PARAM_STR);
        $query->execute();

        $rows = $query->fetchAll();
        return $rows;
    }

    /**
     * This so long query.
     * @return string
     */
    protected static function search_query_string()
    {
        return "SELECT DISTINCT Recipe.* FROM Recipe "
            . "LEFT JOIN RecipeKeyword ON Recipe.id = RecipeKeyword.recipe_id "
            . "JOIN Keyword ON Keyword.id = RecipeKeyword.keyword_id "
            . "JOIN RecipeIngredient ON Recipe.id = RecipeIngredient.recipe_id "
            . "JOIN Ingredient ON Ingredient.id = RecipeIngredient.ingredient_id "
            . "WHERE Recipe.name LIKE :search "
            . "OR Keyword.keyword LIKE :search "
            . "OR Ingredient.name LIKE :search "
            . "OR Recipe.directions LIKE :search";
    }

    /**
     * @param $rows
     * @return array
     */
    protected static function recipes_from_rows($rows)
    {
        $recipes = array();
        foreach ($rows as $row) {
            $recipes[] = self::new_recipe_from_row($row);
        }
        return $recipes;
    }

    public function save()
    {
        $query_string = "INSERT INTO Recipe (name, chef_id, cooking_time, directions, published) "
            . "VALUES (:name, :chef_id, :cooking_time, :directions, NOW()) RETURNING id";
        $query = DB::connection()
            ->prepare($query_string);
        $query->execute(array(
            'name' => $this->name,
            'chef_id' => $this->chef_id,
            'cooking_time' => $this->cooking_time,
            'directions' => $this->directions));

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
