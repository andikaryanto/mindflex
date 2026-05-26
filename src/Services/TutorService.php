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

    public static function create(
        string $name,
        string $email,
        string $hourly_rate,
        string $subjects,
        string $rating
    ) {
        $connection = DatabaseConnection::getInstance();
        $sql = "INSERT INTO tutors (name, email, hourly_rate, subjects, status, rating) 
                   VALUES (:name, :email, :hourly_rate, :subjects, 'active', :rating)";

        return $connection->exec($sql, [
            'name' => $name,
            'subjects' => $subjects,
            'hourly_rate' => $hourly_rate,
            'email' => $email,
            'rating' => $rating
        ]);
    }
}