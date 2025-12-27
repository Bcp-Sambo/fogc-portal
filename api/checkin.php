<?php
// api/checkin.php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

// Auth Check (Usher or Admin)
if (!isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // 1. Get Input
    $data = $_POST;
    $phone = $data['phone_number'] ?? $data['phone'] ?? ''; // Accept both for robustness
    $firstName = $data['first_name'] ?? '';
    // Handle optional last name if not provided (split name?) or frontend ensures it. 
    // Assuming separated in DB but maybe frontend sends full 'name'.
    // Let's assume frontend sends 'name' and we split, OR we update frontend to send first/last.
    // The previous frontend mock used 'name'. We should adjust to match DB or split here.
    // Actually, DB has first_name, last_name. Frontend has 'name'.
    // SPLIT NAME LOGIC:
    $fullName = $data['name'] ?? '';
    if (!empty($fullName) && empty($firstName)) {
        $parts = explode(' ', trim($fullName), 2);
        $firstName = $parts[0];
        $lastName = $parts[1] ?? '-';
    } else {
        $lastName = $data['last_name'] ?? '-';
    }

    $sex = $data['sex'] ?? 'Male';
    $isMember = $data['is_member'] ?? 'No'; // 'Yes' or 'No'
    $email = $data['email'] ?? null;
    $eventId = $data['event_id'] ?? null;

    if (empty($phone)) throw new Exception("Phone number required");
    if (!$eventId) throw new Exception("No active event selected");

    $pdo->beginTransaction();

    // 2. Check/Create Attendee
    $stmt = $pdo->prepare("SELECT id FROM attendees WHERE phone_number = ?");
    $stmt->execute([$phone]);
    $attendee = $stmt->fetch();

    if ($attendee) {
        $attendeeId = $attendee['id'];
        // Optional: Update fields if changed? For speed, we might skip or do it later.
    } else {
        // Create New
        $stmt = $pdo->prepare("INSERT INTO attendees (phone_number, first_name, last_name, sex, is_member, email) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$phone, $firstName, $lastName, $sex, $isMember, $email]);
        $attendeeId = $pdo->lastInsertId();
    }

    // 3. Mark Attendance (Check Duplicates)
    $stmt = $pdo->prepare("SELECT id FROM attendance_log WHERE event_id = ? AND attendee_id = ?");
    $stmt->execute([$eventId, $attendeeId]);
    if ($stmt->fetch()) {
        // Already checked in
        $pdo->commit();
         echo json_encode([
            'success' => true, 
            'message' => 'Already checked in!', 
            'type' => 'warning',
            'attendee_name' => $firstName
        ]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO attendance_log (event_id, attendee_id) VALUES (?, ?)");
    $stmt->execute([$eventId, $attendeeId]);

    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Check-in Successful!',
        'attendee_name' => $firstName
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
