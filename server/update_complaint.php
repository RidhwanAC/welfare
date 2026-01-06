<?php
session_start();
header("Content-Type: application/json");
include_once 'dbconnect.php';

// 1. AUTHORIZATION CHECK
$privilege = $_SESSION['privilege'] ?? 0;
if ($privilege < 1 || $privilege > 4) {
    echo json_encode(["status" => "error", "message" => "Unauthorized: Administrative access required."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['complaint_id'], $data['status'])) {
    
    // 2. INPUT VALIDATION
    $complaint_id = filter_var($data['complaint_id'], FILTER_VALIDATE_INT);
    $new_status   = trim($data['status']);
    
    // Strict White-listing of allowed statuses
    $allowed_statuses = ['Pending', 'Viewed', 'Resolved', 'Rejected'];
    if (!in_array($new_status, $allowed_statuses)) {
        echo json_encode(["status" => "error", "message" => "Invalid status value."]);
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE tbl_complaint SET status = ? WHERE complaint_id = ?");
        $success = $stmt->execute([$new_status, $complaint_id]);

        if ($success && $stmt->rowCount() > 0) {
            echo json_encode(["status" => "success", "message" => "Status updated."]);
        } else {
            echo json_encode(["status" => "error", "message" => "No record found or no change made."]);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(["status" => "error", "message" => "Database error."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Missing fields."]);
}
?>