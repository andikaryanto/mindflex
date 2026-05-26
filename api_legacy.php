<?php

require __DIR__ . '/vendor/autoload.php';

use App\Services\StudentService;
use App\Services\TutorService;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function sendJson($data, int $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function handleGetTutors(array $input)
{
    $subject = $input['subject'] ?? '';
    sendJson(TutorService::getActiveTutors($subject));
}

function handleUpdateRate(array $input)
{
    $tutor_id = $input['tutor_id'] ?? null;
    $hourly_rate = $input['hourly_rate'] ?? null;

    if ($tutor_id === null || $hourly_rate === null) {
        sendJson(['error' => 'Missing parameters'], 400);
    }

    TutorService::updateRate((int)$tutor_id, (float)$hourly_rate);

    sendJson([
        'status' => 'success',
        'message' => 'Tutor rate updated successfully to ' . $hourly_rate
    ]);
}

function handleMatchStudent(array $input)
{
    $student_id = $input['student_id'] ?? '';
    $subject = $input['subject'] ?? '';

    if ($student_id === '' || $subject === '') {
        sendJson(['error' => 'Missing student_id or subject parameter'], 400);
    }

    $student = StudentService::findById((int)$student_id);

    if (!$student) {
        sendJson(['error' => 'Student not found'], 404);
    }

    $tutor = TutorService::findActiveTutorBySubject($subject);

    if (!$tutor) {
        sendJson([
            'match_found' => false,
            'message' => 'No active tutor found for subject: ' . $subject
        ]);
    }

    $hours = 2;
    $tutor_rate = (float)$tutor['hourly_rate'];
    $weekly_cost = $hours * $tutor_rate;

    sendJson([
        'match_found' => true,
        'student' => [
            'id' => $student['id'],
            'name' => $student['name'],
            'budget_limit' => (float)$student['budget_limit']
        ],
        'tutor' => [
            'id' => $tutor['id'],
            'name' => $tutor['name'],
            'hourly_rate' => $tutor_rate,
            'rating' => (float)$tutor['rating'],
            'subjects' => $tutor['subjects']
        ],
        'proposed_hours' => $hours,
        'weekly_cost' => $weekly_cost,
        'exceeds_budget' => $weekly_cost > (float)$student['budget_limit'],
        'match_score' => 1.0
    ]);
}

function handleApiAction(string $action)
{
    switch ($action) {
        case 'get_tutors':
            handleGetTutors($_GET);
            break;

        case 'update_rate':
            handleUpdateRate($_POST);
            break;

        case 'match_student':
            handleMatchStudent($_GET);
            break;

        default:
            sendJson(['error' => 'Action not recognized.'], 404);
    }
}

try {
    handleApiAction($_GET['action'] ?? '');
} catch (Exception $e) {
    sendJson(['status' => 'error', 'message' => $e->getMessage()], 500);
}
