<?php
header("Content-Type: application/json");
include_once 'dbconnect.php'; 

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "No data received"]);
    exit;
}

try {
    $sql = "INSERT INTO tbl_welfare (user_id, aid_type, welfare_category, remarks, status) 
            VALUES (:user_id, :aid_type, :welfare_category, :remarks, :status)";
    
    $stmt = $conn->prepare($sql);
    
    $stmt->execute([
        ':user_id'          => $data['user_id'],
        ':aid_type'         => $data['aid_type'],
        ':welfare_category' => $data['welfare_category'],
        ':remarks'          => $data['remarks'],
        ':status'           => $data['status']
    ]);

    echo json_encode(["status" => "success", "message" => "Application submitted"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>