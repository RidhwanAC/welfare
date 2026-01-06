<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

include_once 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. INPUT SANITIZATION
    // Use htmlspecialchars and strip_tags to kill any script injection (XSS)
    $username = isset($_POST['username']) ? htmlspecialchars(strip_tags(trim($_POST['username'])), ENT_QUOTES, 'UTF-8') : null;
    $email    = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : null;
    $password = $_POST['password'] ?? null; // Passwords should NOT be sanitized (it alters the hash)

    // 2. INPUT VALIDATION
    if (!$username || !$email || !$password) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }

    // Email Format Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format."]);
        exit;
    }

    // Username Length/Character Validation (Prevent excessively long strings)
    if (strlen($username) < 3 || strlen($username) > 20 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        echo json_encode(["status" => "error", "message" => "Username must be 3-20 characters (alphanumeric only)."]);
        exit;
    }

    // Password Complexity (Demonstrating security logic)
    if (strlen($password) < 8) {
        echo json_encode(["status" => "error", "message" => "Password must be at least 8 characters long."]);
        exit;
    }

    try {
        // 3. SECURE HASHING
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 4. PREPARED STATEMENTS (SQL Injection Mitigation)
        $sql = "INSERT INTO tbl_user (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);

        if ($stmt->execute()) {
            $userId = $conn->lastInsertId();

            // 5. FETCH DATA FOR RETURN
            $query = "SELECT user_id, username, email, full_name, ic_number, phone, 
                             household_size, household_income, district, 
                             sub_district, privilege, created_at 
                      FROM tbl_user WHERE user_id = :user_id";
            
            $getUser = $conn->prepare($query);
            $getUser->bindParam(':user_id', $userId);
            $getUser->execute();
            $userData = $getUser->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                "status" => "success",
                "message" => "Registration successful",
                "data" => $userData
            ]);
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(["status" => "error", "message" => "Username or Email already exists."]);
        } else {
            // Log the actual error for dev, but show generic message to user
            error_log($e->getMessage());
            echo json_encode(["status" => "error", "message" => "A registration error occurred."]);
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}