<?php

require __DIR__ . '/vendor/autoload.php';
// Legacy Admin Dashboard for Mindflex Matchmaking System

use App\Database\DatabaseConnection;
use App\Services\AssigmentService;
use App\Services\RevenueSevice;
use App\Services\StudentService;
use App\Services\TutorService;

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

function handleCreateAssignment()
{
    $student_id = $_POST['student_id'];
    $tutor_id = $_POST['tutor_id'];
    $weekly_hours = $_POST['weekly_hours'];

    // Vulnerable to SQL injection in INSERT as well, plus zero validation
    AssigmentService::create($student_id, $tutor_id, $weekly_hours);
    header("Location: index.php?msg=Assignment+created");
    exit;
}

function handleAddTutor()
{
    $name = $_POST['name'];
    $email = $_POST['email'];
    $hourly_rate = $_POST['hourly_rate'];
    $subjects = $_POST['subjects'];
    $rating = $_POST['rating'] ?? 5.0;

    // Vulnerable to SQL Injection
    TutorService::create($name, $email, $hourly_rate, $subjects, $rating);
    header("Location: index.php?msg=Tutor+added");
    exit;
}

function handleAddStudent()
{
    $name = $_POST['name'];
    $grade_level = $_POST['grade_level'];
    $budget_limit = $_POST['budget_limit'];

    StudentService::create($name, $grade_level, $budget_limit);
    header("Location: index.php?msg=Student+added");
    exit;
}

function handlePostAction($action)
{
    switch ($action) {
        case 'create_assignment':
            handleCreateAssignment();
            break;

        case 'add_tutor':
            handleAddTutor();
            break;

        case 'add_student':
            handleAddStudent();
            break;

        default:
            throw new InvalidArgumentException('Invalid action.');
    }
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // DANGEROUS: Deleting assignment via GET without CSRF token or validation
    if ($action === 'delete') {
        try {
            $id = $_GET['id']; // SQL Injection vulnerability here
            AssigmentService::deleteById($id);
            header("Location: index.php?msg=Assignment+deleted+successfully");
            exit;
        } catch (Exception $e) {
            $error_message = "Failed to delete: " . $e->getMessage();
        }
    }

    // DANGEROUS: Completing assignment via GET without CSRF token or validation
    if ($action === 'complete') {
        try {
            $id = $_GET['id'];             
            AssigmentService::completeById($id);
            header("Location: index.php?msg=Assignment+completed");
            exit;
        } catch (Exception $e) {
            $error_message = "Failed to update: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        handlePostAction($action);
    } catch (Exception $e) {
        $error_message = "Error processing request: " . $e->getMessage();
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

    $total_weekly_revenue = RevenueSevice::calculateWeeklyRevenue();

} catch (Exception $e) {
    // If database tables aren't created yet
    $tutors_count = $students_count = $active_assignments_count = 0;
    $total_weekly_revenue = 0.0;
    $error_message = "Database tables are missing or not configured. Run migration/setup. " . $e->getMessage();
}

// Fetch tutors with Search - SQL INJECTION VULNERABLE
$tutors = [];
try {
    $search_params = [];
    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $search = $_GET['search'];
        // Change to service and bind param to prevent injection
        $tutors = TutorService::getAllTutorsByNameOrSubject($search);
    } else {
        $tutors = TutorService::getAllTutors();
    }
} catch (Exception $e) {
    // Suppress or assign empty
}

// Fetch students
$students = [];
try {
    $students = StudentService::getAllStudents();
} catch (Exception $e) {
    // Suppress
}

// Fetch active assignments - N+1 Loop for displaying details
$assignments_list = [];
try {
    $assignments_list = AssigmentService::getAllAssignments();
} catch (Exception $e) {
    // Suppress
}

include 'src/Views/main.php';
?>
