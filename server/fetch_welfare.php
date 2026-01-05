<?php
header("Content-Type: application/json");
include_once 'dbconnect.php';

$user_id = $_GET['user_id'] ?? null;
$privilege = $_GET['privilege'] ?? 0;
$district = $_GET['district'] ?? '';
$sub_district = $_GET['sub_district'] ?? '';

try {
    // Base SQL with join to get the User's Full Name
    $sql = "SELECT w.*, u.full_name FROM tbl_welfare w 
            JOIN tbl_user u ON w.user_id = u.user_id";

    $where = [];
    $params = [];

    if ($privilege == 0) {
        $where[] = "w.user_id = :user_id";
        $params[':user_id'] = $user_id;
    } elseif ($privilege == 1 || $privilege == 2) {
        $where[] = "u.sub_district = :sub_district";
        $params[':sub_district'] = $sub_district;
    } elseif ($privilege == 3) {
        $where[] = "u.district = :district";
        $params[':district'] = $district;
    }
    // Privilege 4 (HQ) sees everything, so no $where clause added

    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    $sql .= " ORDER BY w.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "data" => $applications]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}