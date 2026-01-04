<?php
header("Content-Type: application/json");
include_once 'dbconnect.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode([
        "status" => "error",
        "message" => "No data received"
    ]);
    exit;
}

try {
    $sql = "INSERT INTO tbl_complaint (user_id, complaint_type, complaint_details, status)
            VALUES (:user_id, :complaint_type, :complaint_details, :status)";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':user_id'        => $data['user_id'],
        ':complaint_type' => $data['complaint_type'],
        ':complaint_details'        => $data['complaint_details'],
        ':status'         => $data['status']
    ]);

    echo json_encode([
        "status" => "success",
        "message" => "Complaint submitted successfully"
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>
