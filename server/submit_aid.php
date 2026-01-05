<?php
header("Content-Type: application/json");
include_once 'dbconnect.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['user_id'], $data['amount'], $data['aid_remark'])) {
    try {
        $stmt = $conn->prepare("INSERT INTO tbl_aid (user_id, amount, aid_remark) VALUES (?, ?, ?)");
        $stmt->execute([$data['user_id'], $data['amount'], $data['aid_remark']]);
        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
}