<?php
// Secure session settings (Prevent Session Hijacking)
ini_set('session.cookie_httponly', 1); // Prevents JS from accessing session ID
ini_set('session.cookie_secure', 1);   // Only send over HTTPS (if available)
ini_set('session.use_only_cookies', 1);
session_start();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

include_once 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. DATA SANITIZATION
    // Strip tags to prevent XSS and trim whitespace
    $username = isset($_POST['username']) ? htmlspecialchars(strip_tags(trim($_POST['username'])), ENT_QUOTES, 'UTF-8') : null;
    $password = $_POST['password'] ?? null; // Do NOT sanitize passwords

    if (!$username || !$password) {
        echo json_encode(["status" => "error", "message" => "Please provide both credentials."]);
        exit;
    }

    try {
        // 2. PREPARED STATEMENT (SQL Injection Mitigation)
        $sql = "SELECT user_id, username, password, email, full_name, ic_number, phone, 
                       household_size, household_income, district, 
                       sub_district, privilege, created_at 
                FROM tbl_user 
                WHERE username = :username 
                LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. SECURE VERIFICATION
        if ($user && password_verify($password, $user['password'])) {
            
            // Regenerate session ID on login (Prevents Session Fixation)
            session_regenerate_id(true);

            // 4. Set Session Variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['privilege'] = $user['privilege'];
            $_SESSION['last_regen'] = time();

            // 5. DATA LEAKAGE PREVENTION
            unset($user['password']); // Never send the hash back to the client

            echo json_encode([
                "status" => "success",
                "message" => "Access granted",
                "data" => $user 
            ]);
        } else {
            // GENERIC ERROR MESSAGE (Information Disclosure Mitigation)
            echo json_encode(["status" => "error", "message" => "Invalid credentials."]);
        }

    } catch (PDOException $e) {
        // Hide internal database structure
        error_log($e->getMessage()); // Log the actual error for the dev
        echo json_encode(["status" => "error", "message" => "An internal system error occurred."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Method not allowed."]);
}