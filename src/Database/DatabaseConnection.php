<?php

namespace App\Database;

use PDO;

class DatabaseConnection
{
    protected PDO $db;
    private static ?DatabaseConnection $instance = null;

    public string $query = '';

    private function __construct()
    {
        $this->db = self::connect();
    }

    public static function getInstance(): DatabaseConnection
    {
        if (self::$instance === null) {
            self::$instance = new DatabaseConnection();
        }

        return self::$instance;
    }

    /**
     * Create PDO instance
     */
    private static function connect()
    {
        $db = new \PDO('sqlite:mindflex.db');
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $db;
    }

    public function getConnection(): PDO
    {
        return $this->db;
    }

    public function query(string $query)
    {   
        return $this->db->query($query);
    }

    public function exec(string $query)
    {
        return $this->db->exec($query);
    }

    /**
     * set query tobe executed
     */
    public function setQuery(string $query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * get single row
     */
    public function get()
    {
        return $this->db->query($this->query)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * get rows
     */
    public function getAll()
    {
        return $this->db->query($this->query)->fetchAll(PDO::FETCH_ASSOC);
    }
}