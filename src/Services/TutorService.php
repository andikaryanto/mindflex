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

    public static function getActiveTutors(string $subject = '')
    {
        $connection = DatabaseConnection::getInstance();
        $params = [];
        $sql = "SELECT id, name, email, hourly_rate, subjects, rating, status
                FROM tutors
                WHERE status = 'active'";

        if ($subject !== '') {
            $sql .= ' AND subjects LIKE :subject';
            $params['subject'] = '%' . $subject . '%';
        }

        $connection->setQuery($sql, $params);
        return $connection->getAll();
    }

    public static function findActiveTutorBySubject(string $subject)
    {
        $connection = DatabaseConnection::getInstance();
        $connection->setQuery(
            "SELECT *
            FROM tutors
            WHERE status = 'active'
                AND subjects LIKE :subject
            LIMIT 1",
            ['subject' => '%' . $subject . '%']
        );
        return $connection->get();
    }

    public static function updateRate(int $id, float $hourly_rate)
    {
        $connection = DatabaseConnection::getInstance();
        return $connection->exec(
            'UPDATE tutors SET hourly_rate = :hourly_rate WHERE id = :id',
            [
                'hourly_rate' => $hourly_rate,
                'id' => $id
            ]
        );
    }

    public static function countAll()
    {
        $connection = DatabaseConnection::getInstance();
        $connection->setQuery('SELECT COUNT(*) AS total FROM tutors');
        return (int)$connection->get()['total'];
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
