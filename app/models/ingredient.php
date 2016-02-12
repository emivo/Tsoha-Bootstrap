<?php

class Ingredient extends BaseModel {

    public $ingredient_id, $recipe_id, $name, $quantity;

    public function __construct($attributes) {
        parent::__construct($attributes);
    }

    public static function find_by_recipe_id($id) {
        $quantity_query = DB::connection()
                ->prepare("SELECT * FROM RecipeIngredient WHERE recipe_id = :id");
        $quantity_query->execute(array('id' => $id));

        $rows = $quantity_query->fetchAll();

        $ingredients = array();

        foreach ($rows as $row) {
            $ingredient_query = DB::connection()
                    ->prepare("SELECT * FROM Ingredient WHERE id = :id LIMIT 1");
            $ingredient_query->execute(array('id' => $row['ingredient_id']));

            $ingredient = $ingredient_query->fetch();
            $ingredients[] = new Ingredient(array(
                'ingredient_id' => $row['ingredient_id'],
                'recipe_id' => $id,
                'name' => $ingredient['name'],
                'quantity' => $row['quantity']
            ));
        }

        return $ingredients;
    }

    public static function find_by_recipe_id_and_ingredient_id($recipe_id, $ingredient_id) {
        $query = DB::connection()
                ->prepare("SELECT * FROM RecipeIngredient INNER JOIN Ingredient WHERE recipe_id = :recipe_id AND ingredient_id = :ingredient_id");
        $query->execute(array('recipe_id' => $recipe_id, 'ingredient_id' => $ingredient_id));

        $row = $query->fetch();

        if ($row) {
            $ingredient = new Ingredient(array(
                'ingredient_id' => $row['ingredient_id'],
                'recipe_id' => $row['recipe_id'],
                'name' => $row['name'],
                'quantity' => $row['quantity']
            ));

            return $ingredient;
        }

        return null;
    }

    public function save() {
        $query = DB::connection()
                ->prepare("SELECT * FROM Ingredient WHERE name = :name LIMIT 1");
        $query->execute(array('name' => $this->name));
        $ingredient = $query->fetch();
        $insert_query = DB::connection()
                ->prepare("INSERT INTO RecipeIngredient (recipe_id, ingredient_id, quantity) VALUES (:recipe_id, :ingredient_id, :quantity)");

        if (!$ingredient) {
            $new_ingredient = DB::connection()
                    ->prepare("INSERT INTO Ingredient (name) VALUES (:name) RETURNING id");
            $new_ingredient
                    ->execute(array('name' => $this->name));
            $ingredient = $new_ingredient->fetch();
        }

        $this->ingredient_id = $ingredient['id'];

        $insert_query->execute(array('recipe_id' => $this->recipe_id, 'ingredient_id' => $this->ingredient_id, 'quantity' => $this->quantity));
    }
//Kohta valmis
//    public function update() {
//        DB::connection()
//                ->prepare("UPDATE Ingredient SET name = :name WHERE id = :id")
//                ->execute(array('name' => $this->name, 'id' => $this->id));
//
//        DB::connection()
//                ->prepare("UPDATE RecipeIngredient SET quantity = :quantity WHERE ingredient_id = :iid AND recipe_id = :rid")
//                ->execute(array('quantity' => $this->quantity, 'iid' => $this->ingredient_id, 'rid' => $this->recipe_id));
//    }

}
