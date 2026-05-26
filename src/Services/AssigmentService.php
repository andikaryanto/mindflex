<?php

namespace App\Services;

use App\Database\DatabaseConnection;

class AssigmentService
{
    public static function getAllAssignments()
    {
        $connection = DatabaseConnection::getInstance();
        $connection->setQuery('SELECT * FROM assignments');
        return $connection->getAll();
    }

    public static function deleteById(int $id)
    {
        $connection = DatabaseConnection::getInstance();
        return $connection->exec('DELETE FROM assignments WHERE id = :id', ['id' => $id]);
    }

    public static function completeById(int $id)
    {
        $connection = DatabaseConnection::getInstance();
        return $connection->exec('UPDATE assignments SET status = 2 WHERE id = :id', ['id' => $id]);
    }

    public static function create(
        int $student_id,
        int $tutor_id,
        int $weekly_hours
    ) {
        $connection = DatabaseConnection::getInstance();
        $sql = "INSERT INTO assignments (student_id, tutor_id, weekly_hours, status, created_at) 
                       VALUES (:student_id, :tutor_id, :weekly_hours, :status, :created_at)";

        return $connection->exec($sql, [
            'student_id' => $student_id,
            'tutor_id' => $tutor_id,
            'weekly_hours' => $weekly_hours,
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
