<?php

namespace App\Database;

use PDO;

class DatabaseConnection
{
    protected PDO $db;
    private static ?DatabaseConnection $instance = null;

    public string $query = '';
    public array $params = [];

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

    public function exec(string $query, $params = [])
    {
        $this->setQuery($query, $params);
        $statement = $this->db->prepare($this->query);
        return $statement->execute($this->params);
    }

    /**
     * set query tobe executed
     */
    public function setQuery(string $query, array $params = [])
    {
        $this->query = $query;
        $this->params = $params;
        return $this;
    }

    /**
     * get single row
     */
    public function get()
    {
        try {
            $statement = $this->db->prepare($this->query);

            $statement->execute($this->params);
            $result = $statement->fetch();
            $this->query = '';
            $this->params = [];

            return $result;
        } finally {
            $this->query = '';
            $this->params = [];
        }
    }

    /**
     * get rows
     */
    public function getAll()
    {
        try {
            $statement = $this->db->prepare($this->query);

            $statement->execute($this->params);
            $result = $statement->fetchAll();
            $this->query = '';
            $this->params = [];

            return $result;
        } finally {
            $this->query = '';
            $this->params = [];
        }
    }
}
