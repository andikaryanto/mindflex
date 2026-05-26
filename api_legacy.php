<?php
// Legacy Backend API Endpoint for Mindflex Matchmaking
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database Connection
try {
    $db = new PDO('sqlite:mindflex.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Fails to output proper JSON headers in error state
    echo "DB Connection Error: " . $e->getMessage();
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_tutors':
        // DANGEROUS: Content type set to json, but could be overridden by errors/warnings
        header('Content-Type: application/json');
        try {
            $subject = $_GET['subject'] ?? '';
            // SQL Injection: Direct variable interpolation
            $query = "SELECT id, name, email, hourly_rate, subjects, rating, status FROM tutors WHERE status = 'active'";
            if ($subject !== '') {
                $query .= " AND subjects LIKE '%" . $subject . "%'";
            }
            $stmt = $db->query($query);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($results);
        } catch (Exception $e) {
            // Poor error handling: outputs raw exception text instead of valid JSON error response
            echo "Error executing query: " . $e->getMessage();
        }
        break;

    case 'update_rate':
        // DANGEROUS: Allows any client to update rates without authentication/authorization (IDOR)
        header('Content-Type: application/json');
        
        $tutor_id = $_POST['tutor_id'] ?? null;
        $hourly_rate = $_POST['hourly_rate'] ?? null;

        // No input validation (accepts strings, negative numbers, etc.)
        if ($tutor_id === null || $hourly_rate === null) {
            echo json_encode(["error" => "Missing parameters"]);
            exit;
        }

        try {
            // SQL Injection: No prepared statement
            $sql = "UPDATE tutors SET hourly_rate = " . $hourly_rate . " WHERE id = " . $tutor_id;
            $db->exec($sql);

            // Flawed return statement, echoing success
            echo json_encode([
                "status" => "success",
                "message" => "Tutor rate updated successfully to " . $hourly_rate
            ]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
        break;

    case 'match_student':
        header('Content-Type: application/json');
        
        $student_id = $_GET['student_id'] ?? '';
        $subject = $_GET['subject'] ?? '';

        if ($student_id === '' || $subject === '') {
            echo json_encode(["error" => "Missing student_id or subject parameter"]);
            exit;
        }

        try {
            // Fetch student
            $student_stmt = $db->query("SELECT * FROM students WHERE id = " . (int)$student_id);
            $student = $student_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                echo json_encode(["error" => "Student not found"]);
                exit;
            }

            // Naive Matchmaking Algorithm (First active tutor matching the subject)
            // SQL Injection vulnerability
            $tutor_query = "SELECT * FROM tutors WHERE status = 'active' AND subjects LIKE '%" . $subject . "%' LIMIT 1";
            $tutor = $db->query($tutor_query)->fetch(PDO::FETCH_ASSOC);

            if (!$tutor) {
                echo json_encode([
                    "match_found" => false,
                    "message" => "No active tutor found for subject: " . htmlspecialchars($subject)
                ]);
                exit;
            }

            // Calculate weekly hours & estimated weekly cost (Default to 2 hours per week)
            $hours = 2;
            $tutor_rate = (float)$tutor['hourly_rate'];
            $weekly_cost = $hours * $tutor_rate;

            // BUSINESS FLAW: Recommends the tutor even if weekly cost exceeds student's budget limit
            $exceeds_budget = false;
            if ($weekly_cost > (float)$student['budget_limit']) {
                $exceeds_budget = true; // Still recommends, just flags it or ignores it!
            }

            echo json_encode([
                "match_found" => true,
                "student" => [
                    "id" => $student['id'],
                    "name" => $student['name'],
                    "budget_limit" => (float)$student['budget_limit']
                ],
                "tutor" => [
                    "id" => $tutor['id'],
                    "name" => $tutor['name'],
                    "hourly_rate" => $tutor_rate,
                    "rating" => (float)$tutor['rating'],
                    "subjects" => $tutor['subjects']
                ],
                "proposed_hours" => $hours,
                "weekly_cost" => $weekly_cost,
                "exceeds_budget" => $exceeds_budget,
                "match_score" => 1.0 // hardcoded rating score
            ]);

        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    default:
        // Returns plain text warning on invalid action
        header('HTTP/1.1 404 Not Found');
        echo "Action not recognized.";
        break;
}
