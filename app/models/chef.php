<?php

class Chef extends BaseModel {

    public $id, $name;

    public function __construct($attributes) {
        parent::__construct($attributes);
    }

    public static function all() {
        $query = DB::connection()->prepare('SELECT * FROM Chef');

        $query->execute();

        $rows = $query->fetchAll();

        $chefs = array();

        foreach ($rows as $row) {
            $chefs[] = new Chef(array(
                'id' => $row['id'],
                'name' => $row['name'],
            ));
        }

        return $chefs;
    }

    public static function find($id) {
        $query = DB::connection()->prepare('SELECT * FROM Chef WHERE id = :id LIMIT 1');

        $query->execute(array('id' => $id));

        $row = $query->fetch();

        if ($row) {

            $chef = new Chef(array(
                'id' => $row['id'],
                'name' => $row['name']
            ));
            return $chef;
        }

        return null;
    }

    public static function authenticate($name, $password) {
        $query = DB::connection()
                ->prepare('SELECT * FROM Chef WHERE name = :name AND password = :password LIMIT 1');
        $query->execute(array(
            'name' => $name,
            'password' => crypt($password, '$1$sillysalt$')
        ));
        $row = $query->fetch();
        if ($row) {
            $chef[] = new Chef(array(
                'id' => $row['id'],
                'name' => $row['name']
            ));

            return $chef;
        } else {
            return null;
        }
    }

    public static function register($name, $password) {
        $query = DB::connection()
                ->prepare("INSERT INTO Chef (name, password) VALUES (:name, :password) RETURNING id");

        $password_digest = crypt($password, '$1$sillysalt$');

        $query->execute(array(
            'name' => $name,
            'password' => $password_digest));

        $row = $query->fetch();

        return $row['id'];
    }

}
