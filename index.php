<?php

require __DIR__ . '/vendor/autoload.php';
// Legacy Admin Dashboard for Mindflex Matchmaking System

session_start();

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

function getCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validateCsrfToken(array $input)
{
    $token = $input['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals(getCsrfToken(), $token)) {
        throw new RuntimeException('Invalid CSRF token.');
    }
}

function isMissingInput(array $input, string $key)
{
    return !array_key_exists($key, $input) || trim((string)$input[$key]) === '';
}

function requireString(array $input, string $key, int $maxLength)
{
    if (isMissingInput($input, $key)) {
        throw new InvalidArgumentException($key . ' is required.');
    }

    $value = trim((string)$input[$key]);

    if (strlen($value) > $maxLength) {
        throw new InvalidArgumentException($key . ' is too long.');
    }

    return $value;
}

function requireEmail(array $input, string $key)
{
    $value = requireString($input, $key, 255);

    if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
        throw new InvalidArgumentException('Invalid email address.');
    }

    return $value;
}

function requirePositiveInt(array $input, string $key)
{
    if (isMissingInput($input, $key)) {
        throw new InvalidArgumentException($key . ' is required.');
    }

    $value = filter_var($input[$key], FILTER_VALIDATE_INT);

    if ($value === false) {
        throw new InvalidArgumentException($key . ' must be an integer.');
    }

    return $value;
}

function requireFloat(array $input, string $key)
{
    if (isMissingInput($input, $key)) {
        throw new InvalidArgumentException($key . ' is required.');
    }

    return validateFloat($input[$key], $key);
}

function optionalFloat(array $input, string $key, float $default)
{
    if (isMissingInput($input, $key)) {
        return $default;
    }

    return validateFloat($input[$key], $key);
}

function validateFloat($rawValue, string $key)
{
    if (!is_numeric($rawValue)) {
        throw new InvalidArgumentException($key . ' must be numeric.');
    }

    return (float)$rawValue;
}

function redirectWithMessage(string $message)
{
    header('Location: index.php?msg=' . urlencode($message));
    exit;
}

function handleCreateAssignment(array $input)
{
    $student_id = requirePositiveInt($input, 'student_id');
    $tutor_id = requirePositiveInt($input, 'tutor_id');
    $weekly_hours = requirePositiveInt($input, 'weekly_hours');

    AssigmentService::create($student_id, $tutor_id, $weekly_hours);
    redirectWithMessage('Assignment created');
}

function handleAddTutor(array $input)
{
    $name = requireString($input, 'name', 100);
    $email = requireEmail($input, 'email');
    $hourly_rate = requireFloat($input, 'hourly_rate');
    $subjects = requireString($input, 'subjects', 255);
    $rating = optionalFloat($input, 'rating', 5.0);

    TutorService::create($name, $email, $hourly_rate, $subjects, $rating);
    redirectWithMessage('Tutor added');
}

function handleAddStudent(array $input)
{
    $name = requireString($input, 'name', 100);
    $grade_level = requireString($input, 'grade_level', 50);
    $budget_limit = requireFloat($input, 'budget_limit');

    StudentService::create($name, $grade_level, $budget_limit);
    redirectWithMessage('Student added');
}

function handlePostAction(string $action, array $input)
{
    validateCsrfToken($input);

    switch ($action) {
        case 'create_assignment':
            handleCreateAssignment($input);
            break;

        case 'add_tutor':
            handleAddTutor($input);
            break;

        case 'add_student':
            handleAddStudent($input);
            break;

        case 'delete':
            handleDeleteAssignment($input);
            break;

        case 'complete':
            handleCompleteAssignment($input);
            break;

        default:
            throw new InvalidArgumentException('Invalid action.');
    }
}

function handleDeleteAssignment(array $input)
{
    $id = requirePositiveInt($input, 'id');
    AssigmentService::deleteById($id);
    redirectWithMessage('Assignment deleted successfully');
}

function handleCompleteAssignment(array $input)
{
    $id = requirePositiveInt($input, 'id');
    AssigmentService::completeById($id);
    redirectWithMessage('Assignment completed');
}

function loadDashboardData()
{
    $tutors = [];

    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $search = $_GET['search'];
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        handlePostAction($action, $_POST);
    } catch (Exception $e) {
        $error_message = "Error processing request: " . $e->getMessage();
    }
}

if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
}

$csrf_token = getCsrfToken();
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
