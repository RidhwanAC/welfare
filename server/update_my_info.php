<?php
session_start();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include_once 'dbconnect.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

// Read JSON body
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON payload"]);
    exit;
}

// SECURITY: Prefer session user_id over client input
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

try {
    $sql = "
        UPDATE tbl_user
        SET
            full_name = :full_name,
            ic_number = :ic_number,
            phone = :phone,
            household_size = :household_size,
            household_income = :household_income,
            district = :district,
            sub_district = :sub_district
        WHERE user_id = :user_id
    ";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':full_name' => $data['full_name'],
        ':ic_number' => $data['ic_number'],
        ':phone' => $data['phone'],
        ':household_size' => $data['household_size'],
        ':household_income' => $data['household_income'],
        ':district' => $data['district'],
        ':sub_district' => $data['sub_district'],
        ':user_id' => $user_id
    ]);

    echo json_encode(["status" => "success", "message" => "Profile updated"]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error"
    ]);
}
