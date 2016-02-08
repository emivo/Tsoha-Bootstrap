<?php

class Ingredient extends BaseModel {

    public $id, $recipe_id, $name, $quantity;

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

            $ing = $ingredient_query->fetch();
            $ingredients[] = new Ingredient(array(
                'id' => $row['ingredient_id'],
                'recipe_id' => $id,
                'name' => $ing['name'],
                'quantity' => $row['quantity']
            ));
        }

        return $ingredients;
    }

    public function save() {
        // tarkista onko ainesosa jo jossain toisessa reseptissä
        $query = DB::connection()
                ->prepare("SELECT * FROM Ingredient WHERE name = :name LIMIT 1"); // limit 1 turha
        $query->execute(array('name' => $this->name));
        $ingredient = $query->fetch();
        $insert_query = DB::connection()
                ->prepare("INSERT INTO RecipeIngredient (recipe_id, ingredient_id, quantity) VALUES (:recipe_id, :ingredient_id, :quantity)");
        
        if ($ingredient) {
        $insert_query->execute(array('recipe_id' => $this->recipe_id, 'ingredien_id' => $ingredient->id, 'quantity' => $this->quantity));
        } else {
            
        }
    }

}
