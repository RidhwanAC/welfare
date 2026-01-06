<?php
session_start();
header("Content-Type: application/json");
include_once 'dbconnect.php';

// 1. Session & Privilege Check
$current_user_privilege = $_SESSION['privilege'] ?? null;
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON payload"]);
    exit;
}

// Strict Authorization
if ($current_user_privilege != 4 && (!isset($data['requester_privilege']) || $data['requester_privilege'] != 4)) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access attempt logged."]);
    exit;
}

// 2. DATA SANITIZATION (Prevent XSS)
// We strip tags and convert special characters to HTML entities
function sanitize($input) {
    if (is_array($input)) return $input;
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

$full_name    = sanitize($data['full_name'] ?? '');
$ic_number    = sanitize($data['ic_number'] ?? '');
$email        = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
$phone        = sanitize($data['phone'] ?? '');
$district     = sanitize($data['district'] ?? '');
$sub_district = sanitize($data['sub_district'] ?? '');
$username     = sanitize($data['username'] ?? '');
$password     = $data['password'] ?? ''; // Don't sanitize passwords (it changes the hash)
$privilege    = filter_var($data['privilege'] ?? 0, FILTER_SANITIZE_NUMBER_INT);

// 3. DATA VALIDATION
$errors = [];

// IC Number: Must be exactly 12 digits
if (!preg_match('/^[0-9]{12}$/', $ic_number)) {
    $errors[] = "IC Number must be 12 digits without dashes.";
}

// Email: Standard format check
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}

// Phone: Numbers and '+' sign only, length 10-15
if (!preg_match('/^[0-9+]{10,15}$/', $phone)) {
    $errors[] = "Invalid phone number format.";
}

// Privilege: Must be between 1 and 3
if ($privilege < 1 || $privilege > 3) {
    $errors[] = "Invalid privilege level assigned.";
}

// Length check for long strings (Prevent Buffer issues/excessive storage)
if (strlen($full_name) > 100 || strlen($username) > 50) {
    $errors[] = "Name or Username is too long.";
}

if (!empty($errors)) {
    echo json_encode(["status" => "error", "message" => $errors[0]]);
    exit;
}

try {
    // 4. PREPARED STATEMENTS (SQL Injection Protection)
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
        $errorMsg = $e->getMessage();
        if (strpos($errorMsg, 'username') !== false) $msg = "Username exists.";
        elseif (strpos($errorMsg, 'ic_number') !== false) $msg = "IC exists.";
        elseif (strpos($errorMsg, 'email') !== false) $msg = "Email exists.";
        else $msg = "Duplicate entry.";
        echo json_encode(["status" => "error", "message" => $msg]);
    } else {
        // We do NOT echo $e->getMessage() in production to hide table names/structure
        echo json_encode(["status" => "error", "message" => "Database error occurred."]);
    }
}