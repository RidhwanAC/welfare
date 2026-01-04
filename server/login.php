<?php
// 1. Start session for state persistence
session_start();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

include_once 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;

    if (!$username || !$password) {
        echo json_encode(["status" => "error", "message" => "Username and password are required."]);
        exit;
    }

    try {
        // 2. Fetch all required fields + the password (for verification only)
        $sql = "SELECT user_id, username, email, password, full_name, ic_number, phone, 
                       household_size, household_income, district, 
                       sub_district, privilege, created_at 
                FROM tbl_user 
                WHERE username = :username 
                LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. Verify existence and password match
        if ($user && password_verify($password, $user['password'])) {
            
            // 4. Set Session Variables for the server-side
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];

            // 5. Remove the password from the array before sending to auth.js
            // This ensures the hash never leaves the server
            unset($user['password']);

            echo json_encode([
                "status" => "success",
                "message" => "Login successful",
                "data" => $user // This now contains all your requested fields
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid username or password."]);
        }

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}