<?php
// api/dashboard.php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

// Auth Check (Admin only)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // 1. Total Events
    $stmt = $pdo->query("SELECT COUNT(*) FROM events");
    $totalEvents = $stmt->fetchColumn();

    // 2. Total Attendees
    $stmt = $pdo->query("SELECT COUNT(*) FROM attendees");
    $totalAttendees = $stmt->fetchColumn();

    // 3. Last Service Check-ins (Most recent event)
    // Find the most recent event ID
    $stmt = $pdo->query("SELECT id FROM events ORDER BY event_date DESC LIMIT 1");
    $lastEventId = $stmt->fetchColumn();

    $lastServiceCheckins = 0;
    if ($lastEventId) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance_log WHERE event_id = ?");
        $stmt->execute([$lastEventId]);
        $lastServiceCheckins = $stmt->fetchColumn();
    }

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_events' => $totalEvents,
            'total_attendees' => $totalAttendees,
            'last_service_checkins' => $lastServiceCheckins
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
