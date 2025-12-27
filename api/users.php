<?php
// api/users.php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

// Auth Check: Only Admins can manage users
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // List Users (hide passwords)
        $stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll());
    } 
    elseif ($method === 'POST') {
        $action = $_POST['action'] ?? 'create';

        if ($action === 'create') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'usher';

            if (empty($username) || empty($password)) {
                throw new Exception("Username and Password are required");
            }

            // Hash Password
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hash, $role]);

            echo json_encode(['success' => true, 'message' => 'User created successfully']);
        }
        elseif ($action === 'reset_password') {
            $username = $_POST['username'] ?? '';
            $newPassword = $_POST['password'] ?? '';

            if (empty($username) || empty($newPassword)) {
                 throw new Exception("Username and New Password required");
            }

            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
            $stmt->execute([$hash, $username]);

            echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
