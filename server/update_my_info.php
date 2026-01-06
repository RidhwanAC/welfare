<?php
session_start();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include_once 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON payload"]);
    exit;
}

// SECURITY: Use session ID to ensure a user can ONLY update their own profile
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(["status" => "error", "message" => "Session expired. Please login again."]);
    exit;
}

// 1. DATA SANITIZATION
function clean($val) {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

$full_name        = clean($data['full_name'] ?? '');
$ic_number        = clean($data['ic_number'] ?? '');
$phone            = clean($data['phone'] ?? '');
$district         = clean($data['district'] ?? '');
$sub_district     = clean($data['sub_district'] ?? '');

// 2. NUMERIC VALIDATION (Explicit Casting)
$household_size   = filter_var($data['household_size'] ?? 0, FILTER_VALIDATE_INT);
$household_income = filter_var($data['household_income'] ?? 0, FILTER_VALIDATE_FLOAT);

// 3. LOGICAL VALIDATION
$errors = [];

if (!preg_match('/^[0-9]{12}$/', $ic_number)) {
    $errors[] = "IC Number must be exactly 12 digits.";
}

if ($household_size < 1 || $household_size > 30) {
    $errors[] = "Invalid household size.";
}

if ($household_income < 0) {
    $errors[] = "Income cannot be negative.";
}

if (!empty($errors)) {
    echo json_encode(["status" => "error", "message" => $errors[0]]);
    exit;
}

try {
    // 4. PREPARED STATEMENT (Mitigates SQL Injection)
    $sql = "UPDATE tbl_user SET
                full_name = :full_name,
                ic_number = :ic_number,
                phone = :phone,
                household_size = :household_size,
                household_income = :household_income,
                district = :district,
                sub_district = :sub_district
            WHERE user_id = :user_id";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':full_name'        => $full_name,
        ':ic_number'        => $ic_number,
        ':phone'            => $phone,
        ':household_size'   => $household_size,
        ':household_income' => $household_income,
        ':district'         => $district,
        ':sub_district'     => $sub_district,
        ':user_id'          => $user_id
    ]);

    echo json_encode(["status" => "success", "message" => "Profile updated successfully"]);

} catch (PDOException $e) {
    // Check for duplicate IC in another account
    if ($e->getCode() == 23000) {
        echo json_encode(["status" => "error", "message" => "This IC Number is already registered to another user."]);
    } else {
        error_log($e->getMessage());
        echo json_encode(["status" => "error", "message" => "An error occurred while updating."]);
    }
}