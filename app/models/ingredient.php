<?php

class Ingredient extends BaseModel
{

    public $ingredient_id, $recipe_id, $name, $quantity;

    public function __construct($attributes)
    {
        parent::__construct($attributes);
    }

    public static function find_by_recipe_id($id)
    {
        $quantity_query = DB::connection()
            ->prepare("SELECT * FROM RecipeIngredient WHERE recipe_id = :id");
        $quantity_query->execute(array('id' => $id));

        $rows = $quantity_query->fetchAll();

        $ingredients = array();

        foreach ($rows as $row) {
            $ingredient = self::find_ingredient_by_id($row['ingredient_id']);

            $ingredients[] = self::new_ingredient_from_row($id, $row, $ingredient);
        }

        return $ingredients;
    }

    public static function find_by_recipe_id_and_ingredient_name($recipe_id, $name)
    {
        $ingredient = self::find_ingredient_by_name($name);


        $query = DB::connection()
            ->prepare("SELECT * FROM RecipeIngredient WHERE recipe_id = :recipe_id AND ingredient_id = :id");
        $query->execute(array('recipe_id' => $recipe_id, 'id' => $ingredient['id']));

        $row = $query->fetch();

        if ($row) {
            $ingredient = new Ingredient(array(
                'ingredient_id' => $row['ingredient_id'],
                'recipe_id' => $row['recipe_id'],
                'name' => $name,
                'quantity' => $row['quantity']
            ));

            return $ingredient;
        }

        return null;
    }


    /**
     * @return mixed Palauttaa SQL kyselyn tuloksen
     */
    protected static function find_ingredient_by_id($id)
    {
        $ingredient_query = DB::connection()
            ->prepare("SELECT * FROM Ingredient WHERE id = :id LIMIT 1");
        $ingredient_query->execute(array('id' => $id));

        $ingredient = $ingredient_query->fetch();
        return $ingredient;
    }

    /**
     * @param $id
     * @param $row
     * @param $ingredient
     * @return Ingredient
     */
    public static function new_ingredient_from_row($id, $row, $ingredient)
    {
        return new Ingredient(array(
            'ingredient_id' => $row['ingredient_id'],
            'recipe_id' => $id,
            'name' => $ingredient['name'],
            'quantity' => $row['quantity']
        ));
    }

    public function save()
    {
        $ingredient = $this->find_ingredient();
        $insert_query = DB::connection()
            ->prepare("INSERT INTO RecipeIngredient (recipe_id, ingredient_id, quantity) VALUES (:recipe_id, :ingredient_id, :quantity)");

        if (!$ingredient) {
            $ingredient = $this->new_ingredient_store();
        }

        $this->ingredient_id = $ingredient['id'];

        $insert_query->execute(array('recipe_id' => $this->recipe_id, 'ingredient_id' => $this->ingredient_id, 'quantity' => $this->quantity));
    }

    /**
     * @return mixed palauttaa SQL kyselyn tuloksen
     */
    protected function find_ingredient()
    {
        return self::find_ingredient_by_name($this->name);
    }

    /**
     * @param $name
     * @return mixed palauttaa sql kyselyn tuloksen
     */
    public static function find_ingredient_by_name($name)
    {
        $query = DB::connection()
            ->prepare("SELECT * FROM Ingredient WHERE name = :name LIMIT 1");
        $query->execute(array('name' => $name));
        $ingredient = $query->fetch();
        return $ingredient;
    }
// Ainesosien päivitys ei ole niin mielekästä joten ratkaisua muutettu
//    public function update()
//    {
//        // jos ainesosa vain yhdessä, niin vaihda nimi, muuten luo uusi
//        $query = DB::connection()
//            ->prepare("SELECT COUNT(*) FROM RecipeIngredient WHERE ingredient_id  = :id");
//        $query->execute(array('id' => $this->ingredient_id));
//        $count = $query->fetch();
//
//        if ($count['count'] == 1) {
//            DB::connection()
//                ->prepare("UPDATE Ingredient SET name = :name WHERE id = :id")
//                ->execute(array('name' => $this->name, 'id' => $this->ingredient_id));
//        } else {
//            $ingredient = $this->new_ingredient_store();
//            $this->ingredient_id = $ingredient['id'];
//        }
//
//        DB::connection()
//            ->prepare("UPDATE RecipeIngredient SET quantity = :quantity WHERE ingredient_id = :iid AND recipe_id = :rid")
//            ->execute(array('quantity' => $this->quantity, 'iid' => $this->ingredient_id, 'rid' => $this->recipe_id));
//    }

    public static function delete_unused()
    {
        $query = DB::connection()
            ->prepare("SELECT * FROM Ingredient");
        $query->execute();

        $rows = $query->fetchAll();

        foreach ($rows as $row) {
            $ingredient = new Ingredient(array(
                'ingredient_id' => $row['id'],
                'name' => $row['name']
            ));

            if (is_null($ingredient->find_recipes_for_ingredient())) $ingredient->delete();
        }
    }

    public function find_recipes_for_ingredient()
    {
        $query = DB::connection()
            ->prepare("SELECT * FROM RecipeIngredient WHERE ingredient_id = :id");
        $query->execute(array('id' => $this->ingredient_id));
        $rows = $query->fetchAll();

        $array_of_recipes = array();
        foreach ($rows as $row) {
            // muuta tähän jotta lisää reseptit listaan
            $array_of_recipes[] = $row['recipe_id'];
        }

        if (count($array_of_recipes) > 0) {
            return $array_of_recipes;
        }
        return null;
    }

    public static function delete_from_recipe($id)
    {
        $query_delete_quantities = DB::connection()
            ->prepare("DELETE FROM RecipeIngredient WHERE recipe_id = :id");
        $query_delete_quantities->execute(array('id' => $id));
    }
    public function delete()
    {
        $query_delete_quantities = DB::connection()
            ->prepare("DELETE FROM Ingredient WHERE id = :id");
        $query_delete_quantities->execute(array('id' => $this->ingredient_id));
    }
    public function delete_only_from_this_recipe()
    {
        $query_delete_quantities = DB::connection()
            ->prepare("DELETE FROM RecipeIngredient WHERE recipe_id = :rid AND ingredient_id = :iid");
        $query_delete_quantities->execute(array('rid' => $this->recipe_id,'iid' => $this->ingredient_id));
        self::delete_unused();
    }


    /**
     * @return mixed
     */
    protected function new_ingredient_store()
    {
        $new_ingredient = DB::connection()
            ->prepare("INSERT INTO Ingredient (name) VALUES (:name) RETURNING id");
        $new_ingredient
            ->execute(array('name' => $this->name));
        $ingredient = $new_ingredient->fetch();
        return $ingredient;
    }

}
