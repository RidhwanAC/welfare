<?php
header("Content-Type: application/json");
include_once 'dbconnect.php';

// Get the user_id from the query parameter
$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode([
        "status" => "error",
        "message" => "User ID is required"
    ]);
    exit;
}

try {
    // We select the columns matching your tbl_complaint structure
    // We alias 'complaint_details' as 'description' to match the JS expectation if needed,
    // or you can just use the column name directly.
    $sql = "SELECT 
                complaint_id,
                complaint_type,
                complaint_details,
                status,
                created_at
            FROM tbl_complaint
            WHERE user_id = :user_id
            ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $complaints
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>