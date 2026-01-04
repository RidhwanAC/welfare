<?php
header("Content-Type: application/json");
include_once 'dbconnect.php';

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode([
        "status" => "error",
        "message" => "User ID is required"
    ]);
    exit;
}

try {
    $sql = "SELECT 
                welfare_id,
                aid_type,
                welfare_category,
                remarks,
                status,
                created_at
            FROM tbl_welfare
            WHERE user_id = :user_id
            ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $applications
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
