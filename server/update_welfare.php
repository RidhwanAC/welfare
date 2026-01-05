<?php
header("Content-Type: application/json");
include_once 'dbconnect.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['welfare_id']) && isset($data['status'])) {
    try {
        $stmt = $conn->prepare("UPDATE tbl_welfare SET status = ? WHERE welfare_id = ?");
        $stmt->execute([$data['status'], $data['welfare_id']]);
        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}