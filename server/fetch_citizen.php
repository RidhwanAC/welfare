<?php
header("Content-Type: application/json");
include_once 'dbconnect.php';

$privilege = $_GET['privilege'] ?? 0;
$district = $_GET['district'] ?? '';
$sub_district = $_GET['sub_district'] ?? '';

try {
    // Only fetch Citizens (privilege 0)
    $sql = "SELECT user_id, full_name, sub_district, district FROM tbl_user WHERE privilege = 0";
    $params = [];

    if ($privilege == 1 || $privilege == 2) {
        $sql .= " AND sub_district = :sub_district";
        $params[':sub_district'] = $sub_district;
    } elseif ($privilege == 3) {
        $sql .= " AND district = :district";
        $params[':district'] = $district;
    }
    // Privilege 4 sees all citizens

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "data" => $data]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}