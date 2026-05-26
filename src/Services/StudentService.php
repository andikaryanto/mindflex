<?php

namespace App\Services;

use App\Database\DatabaseConnection;

class StudentService
{
    public static function getAllStudents()
    {
        $connection = DatabaseConnection::getInstance();
        $connection->setQuery('SELECT * FROM students');
        return $connection->getAll();
    }
}