<?php
header('Content-Type: application/json');
include 'dbconnect.php'; // Ensure your DB connection is included

// Get parameters from the request
$privilege = $_GET['privilege'] ?? null;
$district = $_GET['district'] ?? null;
$sub_district = $_GET['sub_district'] ?? null;

// Base queries
$sql_users = "SELECT COUNT(*) as total FROM tbl_user u WHERE u.privilege = 0";
$sql_welfare_total = "SELECT COUNT(*) as total FROM tbl_welfare w JOIN tbl_user u ON w.user_id = u.user_id";
$sql_welfare_pending = "SELECT COUNT(*) as total FROM tbl_welfare w JOIN tbl_user u ON w.user_id = u.user_id WHERE w.status = 'pending'";
$sql_complaints_pending = "SELECT COUNT(*) as total FROM tbl_complaint c JOIN tbl_user u ON c.user_id = u.user_id WHERE c.status = 'pending'";

// Apply Filters based on Admin Level
$filter = "";
if ($privilege == 1 || $privilege == 2) {
    $filter = " AND u.sub_district = '$sub_district'";
} elseif ($privilege == 3) {
    $filter = " AND u.district = '$district'";
}
// Privilege 4 (HQ) gets no filter (sees everything)

function getCount($conn, $query, $filter) {
    // If using PDO (based on your error message)
    $stmt = $conn->query($query . $filter);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['total'] ?? 0;
}

$response = [
    "total_citizens" => getCount($conn, $sql_users, ($privilege != 4 ? $filter : "")),
    "total_welfare" => getCount($conn, $sql_welfare_total, $filter),
    "pending_welfare" => getCount($conn, $sql_welfare_pending, $filter),
    "pending_complaints" => getCount($conn, $sql_complaints_pending, $filter)
];

echo json_encode($response);
?>