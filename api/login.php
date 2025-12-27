<?php
// api/login.php
session_start();
header('Content-Type: application/json');

require_once '../includes/db.php';

try {
    // 1. Get Input
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        throw new Exception("Username and password required.", 400);
    }

    // 2. Query DB
    $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    // 3. Verify Password
    // Note: For initial setup, we might need a plain text check if hashes aren't generated yet, 
    // BUT the SQL script uses bcrypt hashes for the default users.
    if (!$user || !password_verify($password, $user['password_hash'])) {
        throw new Exception("Invalid credentials.", 401);
    }

    // 4. Set Session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    // 5. Response
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'role' => $user['role'],
        'redirect' => ($user['role'] === 'admin') ? 'admin/index.php' : 'attendance.php'
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
