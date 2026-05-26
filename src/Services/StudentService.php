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

    public static function create(
        string $name,
        string $grade_level,
        string $budget_limit
    ) {
        $connection = DatabaseConnection::getInstance();
        $sql = 'INSERT INTO students (name, grade_level, budget_limit) 
                        VALUES (:name, :grade_level, :budget_limit)';
        return $connection->exec($sql, [
            'name' => $name,
            'grade_level' => $grade_level,
            'budget_limit' => $budget_limit
        ]);
    }
}
