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
}