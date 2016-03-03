<?php

class Chef extends BaseModel
{

    public $id, $name, $active, $admin, $info;

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
            $chef = self::new_chef_from_row($row);

            return $chef;
        } else {
            return null;
        }
    }

    public static function register($name, $password, $info)
    {
        $query = DB::connection()
            ->prepare("INSERT INTO Chef (name, admin, active, info, password) "
                . "VALUES (:name, FALSE, TRUE, :info, :password) "
                . "RETURNING id");

        $password_digest = crypt($password, '$1$sillysalt$');

        $query->execute(array(
            'name' => $name,
            'password' => $password_digest,
            'info' => $info
        ));

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
            'info' => $row['info']
        ));
    }

    public function update_info()
    {
        $query = DB::connection()
            ->prepare("UPDATE Chef SET info = :info WHERE id = :id");
        $query->execute(array('info' => $this->info, 'id' => $this->id));
    }

    public function update_password($password)
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

    public function toggle_activity()
    {
        $this->active = !$this->active;
        $query = DB::connection()
            ->prepare("UPDATE Chef SET active = :active WHERE id = :id");
        $query->bindParam(':active', $this->active, PDO::PARAM_BOOL);
        $this->bind_id_param_and_execute($query);
    }

    public function toggle_admin_status()
    {
        $this->admin = !$this->admin;
        $query = DB::connection()
            ->prepare("UPDATE Chef SET admin = :admin WHERE id = :id");
        $query->bindParam(':admin', $this->admin, PDO::PARAM_BOOL);
        $this->bind_id_param_and_execute($query);
    }

    /**
     * @param $query
     */
    protected function bind_id_param_and_execute($query)
    {
        $query->bindParam(':id', $this->id, PDO::PARAM_INT);
        $query->execute();
    }

}
