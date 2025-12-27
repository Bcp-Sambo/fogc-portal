<?php
// api/events.php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

// Simple Auth Check
if (!isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Fetch All Events
        $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC, created_at DESC");
        $events = $stmt->fetchAll();
        echo json_encode($events);
    } 
    elseif ($method === 'POST') {
        // Create or Update Event
        // Admin only?
        if ($_SESSION['role'] !== 'admin') {
             throw new Exception("Access Denied", 403);
        }

        $id = $_POST['id'] ?? null;
        $name = $_POST['event_name'] ?? '';
        $date = $_POST['event_date'] ?? '';
        $status = $_POST['is_active'] === 'Active' ? 1 : 0;
        
        if (empty($name) || empty($date)) {
            throw new Exception("Name and Date are required");
        }

        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE events SET event_name = ?, event_date = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $date, $status, $id]);
            echo json_encode(['success' => true, 'message' => 'Event Updated']);
        } else {
            // Create
            $stmt = $pdo->prepare("INSERT INTO events (event_name, event_date, is_active) VALUES (?, ?, ?)");
            $stmt->execute([$name, $date, $status]);
            echo json_encode(['success' => true, 'message' => 'Event Created']);
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
