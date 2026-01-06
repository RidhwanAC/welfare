<?php
session_start();
header("Content-Type: application/json");
include_once 'dbconnect.php';

// 1. AUTHORIZATION CHECK: Only Admins (1, 2, 3, 4)
$privilege = $_SESSION['privilege'] ?? 0;
if ($privilege < 1 || $privilege > 4) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['welfare_id'], $data['status'])) {
    
    // 2. INPUT VALIDATION
    $welfare_id = filter_var($data['welfare_id'], FILTER_VALIDATE_INT);
    $new_status = trim($data['status']);
    
    // Strict Whitelist
    $allowed_statuses = ['Pending', 'Approved', 'Rejected'];
    if (!in_array($new_status, $allowed_statuses)) {
        echo json_encode(["status" => "error", "message" => "Invalid status value."]);
        exit;
    }

    try {
        // 3. PREPARED STATEMENT
        $stmt = $conn->prepare("UPDATE tbl_welfare SET status = ? WHERE welfare_id = ?");
        $stmt->execute([$new_status, $welfare_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(["status" => "success", "message" => "Welfare updated."]);
        } else {
            echo json_encode(["status" => "error", "message" => "No changes made or record not found."]);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(["status" => "error", "message" => "Internal database error."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Missing welfare_id or status."]);
}