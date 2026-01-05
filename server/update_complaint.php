<?php
header("Content-Type: application/json");
include_once 'dbconnect.php';

// Get the JSON data from the request body
$data = json_decode(file_get_contents("php://input"), true);

// Check for complaint_id and status (matching the JS keys)
if (isset($data['complaint_id']) && isset($data['status'])) {
    try {
        // Update the status for the specific complaint
        $stmt = $conn->prepare("UPDATE tbl_complaint SET status = ? WHERE complaint_id = ?");
        $success = $stmt->execute([
            $data['status'], 
            $data['complaint_id']
        ]);

        if ($success) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update record"]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "status" => "error", 
            "message" => "Database error: " . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        "status" => "error", 
        "message" => "Missing required fields: complaint_id and status"
    ]);
}
?>