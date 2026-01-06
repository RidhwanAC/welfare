<?php
session_start();
header("Content-Type: application/json");
include_once 'dbconnect.php';

// 1. IDENTITY CHECK
$session_user_id = $_SESSION['user_id'] ?? null;
$user_privilege  = $_SESSION['privilege'] ?? 0;

if (!$session_user_id) {
    echo json_encode(["status" => "error", "message" => "Session expired."]);
    exit;
}

// 2. LOGIC: Regular users can only see THEIR history. Admins can see ANYONE'S.
$target_user_id = $_GET['user_id'] ?? null;

// If the user is NOT an admin, force them to use their own ID
if ($user_privilege == 0) {
    $target_user_id = $session_user_id;
} 

if (!$target_user_id) {
    echo json_encode(["status" => "error", "message" => "Target User ID required"]);
    exit;
}

try {
    // 3. PREPARED STATEMENT (Mitigates SQL Injection)
    $stmt = $conn->prepare("SELECT aid_id, amount, aid_remark, created_at 
                            FROM tbl_aid 
                            WHERE user_id = ? 
                            ORDER BY created_at DESC");
    $stmt->execute([$target_user_id]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. PREVENT XSS: Sanitize output for safe display
    foreach ($history as &$row) {
        $row['aid_remark'] = htmlspecialchars($row['aid_remark'], ENT_QUOTES, 'UTF-8');
    }

    echo json_encode(["status" => "success", "data" => $history]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(["status" => "error", "message" => "Database error."]);
}
?>