<?php

class Chef extends BaseModel
{

    public $id, $name, $active, $admin;

    public function __construct($attributes)
    {
        parent::__construct($attributes);
    }

    public static function all()
    {
        $query = DB::connection()->prepare('SELECT * FROM Chef');

        $query->execute();

        $rows = $query->fetchAll();

        $chefs = array();

        foreach ($rows as $row) {
            $chefs[] = self::new_chef_from_row($row);
        }

        return $chefs;
    }

    public static function find($id)
    {
        $query = DB::connection()->prepare('SELECT * FROM Chef WHERE id = :id LIMIT 1');

        $query->execute(array('id' => $id));

        $row = $query->fetch();

        if ($row) {

            $chef = self::new_chef_from_row($row);
            return $chef;
        }

        return null;
    }

    public static function authenticate($name, $password)
    {
        $query = DB::connection()
            ->prepare('SELECT * FROM Chef WHERE name = :name AND password = :password LIMIT 1');
        $query->execute(array(
            'name' => $name,
            'password' => crypt($password, '$1$sillysalt$')
        ));
        $row = $query->fetch();
        if ($row) {
            $chef[] = self::new_chef_from_row($row);

            return $chef;
        } else {
            return null;
        }
    }

    public static function register($name, $password)
    {
        $query = DB::connection()
            ->prepare("INSERT INTO Chef (name, admin, active, password) VALUES (:name, FALSE, TRUE, :password) RETURNING id");

        $password_digest = crypt($password, '$1$sillysalt$');

        $query->execute(array(
            'name' => $name,
            'password' => $password_digest));

        $row = $query->fetch();

        return $row['id'];
    }

    /**
     * @param $row
     * @return Chef
     */
    protected static function new_chef_from_row($row)
    {
        return new Chef(array(
            'id' => $row['id'],
            'name' => $row['name'],
            'active' => $row['active'],
            'admin' => $row['admin'],
        ));
    }

    public function update($password)
    {
        $query = DB::connection()
            ->prepare("UPDATE Chef SET password = :password WHERE id = :id");
        $password_digest = crypt($password, '$1$sillysalt$');
        $query->execute(array('password' => $password_digest, 'id' => $this->id));
    }

    public function destroy()
    {
        // Poista käyttäjän reseptit
        $chefs_recipes = Recipe::find_by_chef_id($this->id);
        foreach ($chefs_recipes as $recipe) {
            $recipe->destroy();
        }
        // Poista käyttäjän kommentit
        $chefs_comments = Comment::find_by_chef_id($this->id);
        foreach ($chefs_comments as $comment) {
            $comment->destroy();
        }

        $query = DB::connection()
            ->prepare("DELETE FROM Chef WHERE id = :id");
        $query->execute(array('id' => $this->id));
    }

}
