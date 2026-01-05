<?php
header("Content-Type: application/json");
include_once 'dbconnect.php';

// Get parameters from the request
$user_id      = $_GET['user_id'] ?? null;
$privilege    = $_GET['privilege'] ?? 0;
$district     = $_GET['district'] ?? '';
$sub_district = $_GET['sub_district'] ?? '';

try {
    // Base SQL: Select complaint details and the user's name
    $sql = "SELECT c.*, u.full_name 
            FROM tbl_complaint c
            JOIN tbl_user u ON c.user_id = u.user_id";

    $where = [];
    $params = [];

    // Privilege logic
    if ($privilege == 0) {
        // Citizen: Only see their own complaints
        $where[] = "c.user_id = :user_id";
        $params[':user_id'] = $user_id;
    } elseif ($privilege == 1 || $privilege == 2) {
        // Sub-district admin: Filter by complainant's sub_district
        $where[] = "u.sub_district = :sub_district";
        $params[':sub_district'] = $sub_district;
    } elseif ($privilege == 3) {
        // District admin: Filter by complainant's district
        $where[] = "u.district = :district";
        $params[':district'] = $district;
    }
    // Privilege 4 (HQ) remains without filters to see all complaints

    // Append WHERE clauses if necessary
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    // Sort by latest
    $sql .= " ORDER BY c.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
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