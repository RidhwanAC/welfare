<?php
session_start();
header("Content-Type: application/json");
include_once 'dbconnect.php'; 

// SECURITY: Verify session to prevent unauthorized submissions
$session_user_id = $_SESSION['user_id'] ?? null;
if (!$session_user_id) {
    echo json_encode(["status" => "error", "message" => "Authentication required."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["status" => "error", "message" => "No data received"]);
    exit;
}

// 1. SANITIZATION
function clean($val) {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

$aid_type         = clean($data['aid_type'] ?? '');
$welfare_category = clean($data['welfare_category'] ?? '');
$remarks          = clean($data['remarks'] ?? '');
// Forced initial status 'Pending' so users can't approve their own welfare
$status           = "Pending"; 

if (empty($aid_type) || empty($welfare_category)) {
    echo json_encode(["status" => "error", "message" => "Required fields missing."]);
    exit;
}

try {
    // 2. PREPARED STATEMENT using Session User ID
    $sql = "INSERT INTO tbl_welfare (user_id, aid_type, welfare_category, remarks, status) 
            VALUES (:user_id, :aid_type, :welfare_category, :remarks, :status)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':user_id'          => $session_user_id,
        ':aid_type'         => $aid_type,
        ':welfare_category' => $welfare_category,
        ':remarks'          => $remarks,
        ':status'           => $status
    ]);

    echo json_encode(["status" => "success", "message" => "Application submitted successfully"]);
} catch (PDOException $e) {
    error_log($e->getMessage()); // Log for dev
    echo json_encode(["status" => "error", "message" => "System error occurred."]);
}
?>