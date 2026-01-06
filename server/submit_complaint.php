<?php
session_start();
header("Content-Type: application/json");
include_once 'dbconnect.php';

$session_user_id = $_SESSION['user_id'] ?? null;
if (!$session_user_id) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["status" => "error", "message" => "No data received"]);
    exit;
}

// 1. SANITIZATION
function sanitize_input($val) {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

$complaint_type = sanitize_input($data['complaint_type'] ?? '');
$details        = sanitize_input($data['complaint_details'] ?? '');
// Forced status 'Pending' to prevent users from submitting 'Approved' complaints
$status         = "Pending"; 

if (empty($complaint_type) || empty($details)) {
    echo json_encode(["status" => "error", "message" => "Complaint type and details are required."]);
    exit;
}

try {
    // 2. PREPARED STATEMENT
    $sql = "INSERT INTO tbl_complaint (user_id, complaint_type, complaint_details, status)
            VALUES (:user_id, :complaint_type, :complaint_details, :status)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':user_id'           => $session_user_id,
        ':complaint_type'    => $complaint_type,
        ':complaint_details' => $details,
        ':status'            => $status
    ]);

    echo json_encode(["status" => "success", "message" => "Complaint submitted successfully"]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(["status" => "error", "message" => "Database error occurred."]);
}
?>