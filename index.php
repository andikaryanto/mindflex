<?php

require __DIR__ . '/vendor/autoload.php';
// Legacy Admin Dashboard for Mindflex Matchmaking System

use App\Services\AssigmentService;
use App\Services\RevenueSevice;
use App\Services\StudentService;
use App\Services\TutorService;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

function handleDeleteAssignment()
{
    $id = $_GET['id']; // SQL Injection vulnerability here
    AssigmentService::deleteById($id);
    header("Location: index.php?msg=Assignment+deleted+successfully");
    exit;
}

function handleCompleteAssignment()
{
    $id = $_GET['id'];
    AssigmentService::completeById($id);
    header("Location: index.php?msg=Assignment+completed");
    exit;
}

function handleGetAction($action)
{
    switch ($action) {
        case 'delete':
            handleDeleteAssignment();
            break;

        case 'complete':
            handleCompleteAssignment();
            break;
    }
}

function loadDashboardData()
{
    $tutors = [];

    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $search = $_GET['search'];
        // Change to service and bind param to prevent injection
        $tutors = TutorService::getAllTutorsByNameOrSubject($search);
    } else {
        $tutors = TutorService::getAllTutors();
    }

    return [
        'tutors_count' => TutorService::countAll(),
        'students_count' => StudentService::countAll(),
        'active_assignments_count' => AssigmentService::countActive(),
        'total_weekly_revenue' => RevenueSevice::calculateWeeklyRevenue(),
        'tutors' => $tutors,
        'students' => StudentService::getAllStudents(),
        'assignments_list' => AssigmentService::getAllAssignments(),
    ];
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    try {
        handleGetAction($action);
    } catch (Exception $e) {
        $error_message = "Failed to process request: " . $e->getMessage();
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

$tutors_count = $students_count = $active_assignments_count = 0;
$total_weekly_revenue = 0.0;
$tutors = [];
$students = [];
$assignments_list = [];

try {
    $dashboard_data = loadDashboardData();

    $tutors_count = $dashboard_data['tutors_count'];
    $students_count = $dashboard_data['students_count'];
    $active_assignments_count = $dashboard_data['active_assignments_count'];
    $total_weekly_revenue = $dashboard_data['total_weekly_revenue'];
    $tutors = $dashboard_data['tutors'];
    $students = $dashboard_data['students'];
    $assignments_list = $dashboard_data['assignments_list'];
} catch (Exception $e) {
    $error_message = "Database tables are missing or not configured. Run migration/setup. " . $e->getMessage();
}

include 'src/Views/main.php';
?>
