<?php
header('Content-Type: application/json');
include 'dbconnect.php';

$privilege = $_GET['privilege'] ?? null;
$district = $_GET['district'] ?? null;
$sub_district = $_GET['sub_district'] ?? null;

// Base queries (Note: some have WHERE, some don't)
$sql_users = "SELECT COUNT(*) as total FROM tbl_user u WHERE u.privilege = 0";
$sql_welfare_total = "SELECT COUNT(*) as total FROM tbl_welfare w JOIN tbl_user u ON w.user_id = u.user_id";
$sql_welfare_pending = "SELECT COUNT(*) as total FROM tbl_welfare w JOIN tbl_user u ON w.user_id = u.user_id WHERE w.status = 'pending'";
$sql_complaints_pending = "SELECT COUNT(*) as total FROM tbl_complaint c JOIN tbl_user u ON c.user_id = u.user_id WHERE c.status = 'pending'";
$sql_aids_total = "SELECT SUM(amount) as total FROM tbl_aid a JOIN tbl_user u ON a.user_id = u.user_id";

// Filtering Logic
$filter = "";
if ($privilege == 1 || $privilege == 2) {
    $filter = " AND u.sub_district = '$sub_district'";
} elseif ($privilege == 3) {
    $filter = " AND u.district = '$district'";
}

function getMetric($conn, $query, $filter) {
    if (empty($filter)) {
        $finalQuery = $query;
    } else {
        // Check if the base query already has a WHERE clause
        if (stripos($query, 'WHERE') !== false) {
            // Already has WHERE, so just append the AND filter
            $finalQuery = $query . $filter;
        } else {
            // No WHERE yet, add it
            $finalQuery = $query . " WHERE 1=1 " . $filter;
        }
    }
    
    $stmt = $conn->query($finalQuery);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['total'] ?? 0;
}

$response = [
    "total_citizens" => getMetric($conn, $sql_users, ($privilege != 4 ? $filter : "")),
    "total_welfare" => getMetric($conn, $sql_welfare_total, $filter),
    "pending_welfare" => getMetric($conn, $sql_welfare_pending, $filter),
    "pending_complaints" => getMetric($conn, $sql_complaints_pending, $filter),
    "total_aids" => getMetric($conn, $sql_aids_total, $filter)
];

echo json_encode($response);
?>