<?php
session_start();
header("Content-Type: application/json");
include_once 'dbconnect.php';

$current_user_privilege = $_SESSION['privilege'] ?? null;
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON payload"]);
    exit;
}

if ($current_user_privilege != 4 && (!isset($data['requester_privilege']) || $data['requester_privilege'] != 4)) {
    echo json_encode(["status" => "error", "message" => "Unauthorized action."]);
    exit;
}

// Extract fields
$full_name    = $data['full_name'] ?? null;
$ic_number    = $data['ic_number'] ?? null;
$email        = $data['email'] ?? null; // Added
$phone        = $data['phone'] ?? null;
$district     = $data['district'] ?? null;
$sub_district = $data['sub_district'] ?? null;
$username     = $data['username'] ?? null;
$password     = $data['password'] ?? null;
$privilege    = $data['privilege'] ?? null;

if (!$full_name || !$ic_number || !$email || !$username || !$password || !$privilege) {
    echo json_encode(["status" => "error", "message" => "All fields including email are required."]);
    exit;
}

try {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO tbl_user (full_name, ic_number, email, phone, district, sub_district, username, password, privilege) 
            VALUES (:full_name, :ic_number, :email, :phone, :district, :sub_district, :username, :password, :privilege)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':full_name'    => $full_name,
        ':ic_number'    => $ic_number,
        ':email'        => $email,
        ':phone'        => $phone,
        ':district'     => $district,
        ':sub_district' => $sub_district,
        ':username'     => $username,
        ':password'     => $hashedPassword,
        ':privilege'    => $privilege
    ]);

    echo json_encode(["status" => "success", "message" => "Admin account created successfully."]);

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        // More descriptive error message
        $errorMsg = $e->getMessage();
        if (strpos($errorMsg, 'username') !== false) {
            $msg = "Username already exists.";
        } elseif (strpos($errorMsg, 'ic_number') !== false) {
            $msg = "IC Number already exists.";
        } elseif (strpos($errorMsg, 'email') !== false) {
            $msg = "Email address already exists.";
        } else {
            $msg = "Duplicate entry found (Username, IC, or Email).";
        }
        echo json_encode(["status" => "error", "message" => $msg]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
}