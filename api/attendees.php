<?php
// api/attendees.php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

// Auth Check
if (!isset($_SESSION['role'])) {
    http_response_code(401);
    exit;
}

$eventId = $_GET['event_id'] ?? null;

try {
    if ($eventId) {
        // Get Attendees for Specific Event (Event Details View)
        // We join attendance_log with attendees
        $stmt = $pdo->prepare("
            SELECT a.first_name, a.last_name, a.sex, a.is_member, a.phone_number, log.check_in_time 
            FROM attendance_log log
            JOIN attendees a ON log.attendee_id = a.id
            WHERE log.event_id = ?
            ORDER BY log.check_in_time DESC
        ");
        $stmt->execute([$eventId]);
        $attendees = $stmt->fetchAll();

        // Calculate Stats
        $stats = [
            'total' => count($attendees),
            'men' => 0,
            'women' => 0,
            'first_timers' => 0, // Assuming is_member='No' means visitor/first timer for now
             // Or if asking for "New" specifically, we might track created_at relative to event?
             // For simplicity: Visitors = First Timers/New
        ];

        foreach ($attendees as $p) {
            if ($p['sex'] === 'Male') $stats['men']++;
            else $stats['women']++;
            
            if ($p['is_member'] === 'No') $stats['first_timers']++;
        }

        echo json_encode(['stats' => $stats, 'list' => $attendees]);

    } else {
        // Get All Attendees (Master Database View)
        $stmt = $pdo->query("SELECT * FROM attendees ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll());
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
