<?php

namespace App\Services;

use App\Database\DatabaseConnection;

class RevenueSevice
{
    public static function calculateWeeklyRevenue()
    {
       $db = DatabaseConnection::getInstance();

       $sql = "SELECT SUM(a.weekly_hours * t.hourly_rate)  est_revenue_per_week
                FROM assignments a
                inner join tutors t on t.id = a.tutor_id  
                WHERE a.status = '1'";

       return (float)$db->setQuery($sql)->get()['est_revenue_per_week'];

    }
}