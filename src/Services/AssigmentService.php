<?php

namespace App\Services;

use App\Database\DatabaseConnection;

class AssigmentService
{
    public static function getAllAssignments()
    {
        $connection = DatabaseConnection::getInstance();
        $connection->setQuery(
            'SELECT 
                a.*,
                s.name AS student_name,
                t.name AS tutor_name,
                t.hourly_rate AS tutor_hourly_rate,
                t.subjects AS tutor_subjects
            FROM assignments a
            LEFT JOIN students s ON s.id = a.student_id
            LEFT JOIN tutors t ON t.id = a.tutor_id'
        );
        return $connection->getAll();
    }

    public static function countActive()
    {
        $connection = DatabaseConnection::getInstance();
        $connection->setQuery("SELECT COUNT(*) AS total FROM assignments WHERE status = '1'");
        return (int)$connection->get()['total'];
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
