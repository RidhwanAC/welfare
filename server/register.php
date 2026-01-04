<?php
// Set headers to return JSON response
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

// Include the database connection
include_once 'dbconnect.php';

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Get input data (works for both standard form-data and JSON)
    $username = $_POST['username'] ?? null;
    $email    = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;

    // 2. Basic Validation
    if (!$username || !$email || !$password) {
        echo json_encode(["status" => "error", "message" => "Required fields are missing."]);
        exit;
    }

    try {
        // 3. Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 4. Prepare SQL to insert user
        // Note: full_name is handled by the SQL Trigger we created earlier
        $sql = "INSERT INTO tbl_user (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);

        if ($stmt->execute()) {
            // 5. Get the ID of the newly created user
            $userId = $conn->lastInsertId();

            // 6. Fetch user data (excluding password) to return in response
            $query = "SELECT user_id, username, email, full_name, ic_number, phone, 
                             household_size, household_income, district, 
                             sub_district, privilege, created_at 
                      FROM tbl_user WHERE user_id = :user_id";
            
            $getUser = $conn->prepare($query);
            $getUser->bindParam(':user_id', $userId);
            $getUser->execute();
            $userData = $getUser->fetch();

            // 7. Send Success Response
            echo json_encode([
                "status" => "success",
                "message" => "Registration successful",
                "data" => $userData
            ]);
        }
    } catch (PDOException $e) {
        // Handle Duplicate Entry errors (Username or Email)
        if ($e->getCode() == 23000) {
            echo json_encode(["status" => "error", "message" => "Username or Email already exists."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}