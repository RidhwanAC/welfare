<?php
session_start();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include_once 'dbconnect.php';

// 1. Authorization Check: Check Session FIRST
// Only Privilege 4 (HQ) can add admins
$current_user_privilege = $_SESSION['privilege'] ?? null;

// Read JSON body
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON payload"]);
    exit;
}

// FALLBACK: If session is lost but frontend provides userData, verify it (Optional but safer)
if ($current_user_privilege != 4 && (!isset($data['requester_privilege']) || $data['requester_privilege'] != 4)) {
    echo json_encode([
        "status" => "error", 
        "message" => "Unauthorized: Only HQ can perform this action. (Privilege detected: $current_user_privilege)"
    ]);
    exit;
}

// 2. Extract fields (This is the NEW ADMIN data)
$full_name    = $data['full_name'] ?? null;
$ic_number    = $data['ic_number'] ?? null;
$phone        = $data['phone'] ?? null;
$district     = $data['district'] ?? null;
$sub_district = $data['sub_district'] ?? null;
$username     = $data['username'] ?? null;
$password     = $data['password'] ?? null;
$privilege    = $data['privilege'] ?? null; // The role assigned to the NEW admin (1-3)

if (!$full_name || !$ic_number || !$username || !$password || !$privilege) {
    echo json_encode(["status" => "error", "message" => "Missing required fields."]);
    exit;
}

try {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO tbl_user (full_name, ic_number, phone, district, sub_district, username, password, privilege) 
            VALUES (:full_name, :ic_number, :phone, :district, :sub_district, :username, :password, :privilege)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':full_name'    => $full_name,
        ':ic_number'    => $ic_number,
        ':phone'        => $phone,
        ':district'     => $district,
        ':sub_district' => $sub_district,
        ':username'     => $username,
        ':password'     => $hashedPassword,
        ':privilege'    => $privilege
    ]);

    echo json_encode(["status" => "success", "message" => "Admin account created successfully for " . $full_name]);

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode(["status" => "error", "message" => "Username or IC Number already exists."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error"]);
    }
}