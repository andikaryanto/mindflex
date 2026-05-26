<?php

namespace App\Services;

use App\Database\DatabaseConnection;

class TutorService
{
    /**
     * Get all tutors by name or subject
     */
    public static function getAllTutorsByNameOrSubject(string $value)
    {
        $connection = DatabaseConnection::getInstance();
        $search_params['search'] = '%' . $value . '%';
        // use parameter to prevent SQl injection
        $connection->setQuery('SELECT * FROM tutors WHERE name LIKE :search OR subjects LIKE :search', $search_params);
        
        return $connection->getAll();
    }

    /**
     * Get all tutors
     */
    public static function getAllTutors()
    {
        $connection = DatabaseConnection::getInstance();
        $connection->setQuery('SELECT * FROM tutors');
        return $connection->getAll();
    }
}