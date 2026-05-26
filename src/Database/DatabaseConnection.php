<?php

namespace App\Database;

class DatabaseConnection
{
    public static function connect()
    {
        $db = new \PDO('sqlite:mindflex.db');
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $db;
    }
}