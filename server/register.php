<?php
header('Content-Type: application/json; charset=utf-8');
// Allow CORS during development (adjust in production)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/dbconnect.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    // fall back to form-encoded
    $input = $_POST;
}

$username = trim($input['username'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$privilege = isset($input['privilege']) ? (int)$input['privilege'] : 0;

if (!$username || !$email || !$password) {
    echo json_encode(['status' => 'fail', 'message' => 'Missing required fields', 'data' => null]);
    exit;
}

try {
    $pdo = get_db();

    // Check uniqueness
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1');
    $stmt->execute([':u' => $username, ':e' => $email]);
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'fail', 'message' => 'Username or email already exists', 'data' => null]);
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('INSERT INTO users (username, email, password, privilege) VALUES (:u, :e, :p, :pr)');
    $stmt->execute([':u' => $username, ':e' => $email, ':p' => $hash, ':pr' => $privilege]);

    $id = $pdo->lastInsertId();
    $stmt = $pdo->prepare('SELECT id, username, email, privilege, created_at FROM users WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch();

    echo json_encode(['status' => 'success', 'message' => 'Registered', 'data' => $user]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'fail', 'message' => 'Server error', 'data' => null]);
    exit;
}
