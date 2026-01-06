<?php
session_start();
header("Content-Type: application/json");
include_once 'dbconnect.php';

// Check Session
$session_user_id = $_SESSION['user_id'] ?? null;
if (!$session_user_id) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['amount'], $data['aid_remark'])) {
    
    // 1. SANITIZATION & VALIDATION
    $amount = filter_var($data['amount'], FILTER_VALIDATE_FLOAT);
    $remark = htmlspecialchars(strip_tags(trim($data['aid_remark'])), ENT_QUOTES, 'UTF-8');

    if ($amount === false || $amount <= 0) {
        echo json_encode(["status" => "error", "message" => "Invalid aid amount."]);
        exit;
    }

    try {
        // 2. PREPARED STATEMENT (Mitigates SQL Injection)
        // We use $session_user_id to ensure the record belongs to the logged-in user
        $stmt = $conn->prepare("INSERT INTO tbl_aid (user_id, amount, aid_remark) VALUES (?, ?, ?)");
        $stmt->execute([$session_user_id, $amount, $remark]);
        
        echo json_encode(["status" => "success", "message" => "Aid application submitted."]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(["status" => "error", "message" => "Internal system error."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Required fields missing."]);
}