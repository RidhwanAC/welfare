<?php
header('Content-Type: application/json; charset=utf-8');
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
    $input = $_POST;
}

$usernameOrEmail = trim($input['username'] ?? ($input['email'] ?? ''));
$password = $input['password'] ?? '';

if (!$usernameOrEmail || !$password) {
    echo json_encode(['status' => 'fail', 'message' => 'Missing credentials', 'data' => null]);
    exit;
}

try {
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT id, username, email, password, privilege, created_at FROM users WHERE username = :u OR email = :e LIMIT 1');
    $stmt->execute([':u' => $usernameOrEmail, ':e' => $usernameOrEmail]);
    $user = $stmt->fetch();
    if (!$user) {
        echo json_encode(['status' => 'fail', 'message' => 'Invalid credentials', 'data' => null]);
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        echo json_encode(['status' => 'fail', 'message' => 'Invalid credentials', 'data' => null]);
        exit;
    }

    // Remove password before returning data
    unset($user['password']);

    echo json_encode(['status' => 'success', 'message' => 'Authenticated', 'data' => $user]);
    exit;
} catch (Exception $e) {
    // Log and return error message for debugging (remove detail in production)
    error_log('login.php error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'fail', 'message' => 'Server error: ' . $e->getMessage(), 'data' => null]);
    exit;
}
