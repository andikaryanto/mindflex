<?php

require __DIR__ . '/vendor/autoload.php';
// Legacy Admin Dashboard for Mindflex Matchmaking System

use App\Database\DatabaseConnection;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database Connection
try {
    $db = DatabaseConnection::getInstance();
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . ". Make sure mindflex.db exists and is writeable.");
}

// Action Handlers
$message = "";
$error_message = "";

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // DANGEROUS: Deleting assignment via GET without CSRF token or validation
    if ($action === 'delete') {
        try {
            $id = $_GET['id']; // SQL Injection vulnerability here
            $db->exec("DELETE FROM assignments WHERE id = " . $id);
            header("Location: index.php?msg=Assignment+deleted+successfully");
            exit;
        } catch (Exception $e) {
            $error_message = "Failed to delete: " . $e->getMessage();
        }
    }

    // DANGEROUS: Completing assignment via GET without CSRF token or validation
    if ($action === 'complete') {
        try {
            $id = $_GET['id']; // SQL Injection vulnerability here
            $db->exec("UPDATE assignments SET status = '2' WHERE id = " . $id);
            header("Location: index.php?msg=Assignment+completed");
            exit;
        } catch (Exception $e) {
            $error_message = "Failed to update: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // DANGEROUS: Create assignment without CSRF, budget validation, hourly rate snapshotted, or capacity check
    if ($action === 'create_assignment') {
        try {
            $student_id = $_POST['student_id'];
            $tutor_id = $_POST['tutor_id'];
            $weekly_hours = $_POST['weekly_hours'];

            // Vulnerable to SQL injection in INSERT as well, plus zero validation
            $db->exec("INSERT INTO assignments (student_id, tutor_id, weekly_hours, status, created_at) 
                       VALUES ($student_id, $tutor_id, $weekly_hours, '1', '" . date('Y-m-d H:i:s') . "')");
            header("Location: index.php?msg=Assignment+created");
            exit;
        } catch (Exception $e) {
            $error_message = "Error creating assignment: " . $e->getMessage();
        }
    }

    // Create Tutor - RAW SQL
    if ($action === 'add_tutor') {
        try {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $hourly_rate = $_POST['hourly_rate'];
            $subjects = $_POST['subjects'];
            $rating = $_POST['rating'] ?? 5.0;

            // Vulnerable to SQL Injection
            $query = "INSERT INTO tutors (name, email, hourly_rate, subjects, status, rating) 
                      VALUES ('$name', '$email', $hourly_rate, '$subjects', 'active', $rating)";
            $db->exec($query);
            header("Location: index.php?msg=Tutor+added");
            exit;
        } catch (Exception $e) {
            $error_message = "Error adding tutor: " . $e->getMessage();
        }
    }

    // Create Student - RAW SQL
    if ($action === 'add_student') {
        try {
            $name = $_POST['name'];
            $grade_level = $_POST['grade_level'];
            $budget_limit = $_POST['budget_limit'];

            // Vulnerable to SQL Injection
            $query = "INSERT INTO students (name, grade_level, budget_limit) 
                      VALUES ('$name', '$grade_level', $budget_limit)";
            $db->exec($query);
            header("Location: index.php?msg=Student+added");
            exit;
        } catch (Exception $e) {
            $error_message = "Error adding student: " . $e->getMessage();
        }
    }
}

if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
}

// Fetch stats - UNOPTIMIZED & N+1 Loop
try {
    $tutors_count = $db->query("SELECT COUNT(*) FROM tutors")->fetchColumn();
    $students_count = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $active_assignments_count = $db->query("SELECT COUNT(*) FROM assignments WHERE status = '1'")->fetchColumn();

    // Retroactive Pricing Calculation Flaw & N+1 Query in stats calculation
    $total_weekly_revenue = 0.0;
    $all_active_assignments = $db->query("SELECT * FROM assignments WHERE status = '1'")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($all_active_assignments as $asg) {
        // N+1 database call to fetch tutor's CURRENT hourly rate
        $tutor_data = $db->query("SELECT hourly_rate FROM tutors WHERE id = " . $asg['tutor_id'])->fetch(PDO::FETCH_ASSOC);
        if ($tutor_data) {
            $total_weekly_revenue += (float)$asg['weekly_hours'] * (float)$tutor_data['hourly_rate'];
        }
    }
} catch (Exception $e) {
    // If database tables aren't created yet
    $tutors_count = $students_count = $active_assignments_count = 0;
    $total_weekly_revenue = 0.0;
    $error_message = "Database tables are missing or not configured. Run migration/setup. " . $e->getMessage();
}

// Fetch tutors with Search - SQL INJECTION VULNERABLE
$tutors = [];
try {
    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $search = $_GET['search'];
        // Direct interpolation of user input -> SQL Injection
        $tutors_query = "SELECT * FROM tutors WHERE name LIKE '%" . $search . "%' OR subjects LIKE '%" . $search . "%'";
    } else {
        $tutors_query = "SELECT * FROM tutors";
    }
    $tutors = $db->query($tutors_query)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Suppress or assign empty
}

// Fetch students
$students = [];
try {
    $students = $db->query("SELECT * FROM students")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Suppress
}

// Fetch active assignments - N+1 Loop for displaying details
$assignments_list = [];
try {
    $assignments_list = $db->query("SELECT * FROM assignments")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Suppress
}

include 'src/Views/main.php';
?>