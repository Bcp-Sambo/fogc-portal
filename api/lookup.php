<?php
// api/lookup.php
header('Content-Type: application/json');
require_once '../includes/db.php';

$phone = $_GET['phone'] ?? '';

if (strlen($phone) < 10) {
    echo json_encode(['found' => false]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM attendees WHERE phone_number = ? LIMIT 1");
    $stmt->execute([$phone]);
    $attendee = $stmt->fetch();

    if ($attendee) {
        echo json_encode(['found' => true, 'data' => $attendee]);
    } else {
        echo json_encode(['found' => false]);
    }
} catch (Exception $e) {
    echo json_encode(['found' => false, 'error' => $e->getMessage()]);
}
?>
