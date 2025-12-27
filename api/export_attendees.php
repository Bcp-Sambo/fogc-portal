<?php
// api/export_attendees.php
session_start();
require_once '../includes/db.php';

// Auth Check
if (!isset($_SESSION['role'])) {
    http_response_code(401);
    die('Unauthorized');
}

// Set Headers for Download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendees_export_' . date('Y-m-d') . '.csv"');

// Open Output Stream
$output = fopen('php://output', 'w');

// Add Header Row
fputcsv($output, ['Phone Number', 'First Name', 'Last Name', 'Sex', 'Is Member', 'Email', 'Invited By', 'Date Added']);

// Fetch Data
try {
    $stmt = $pdo->query("SELECT phone_number, first_name, last_name, sex, is_member, email, invited_by, created_at FROM attendees ORDER BY created_at DESC");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
} catch (Exception $e) {
    // In a CSV download, echoing an error breaks the CSV structure, but it's better than silence
    echo "Error: " . $e->getMessage();
}

fclose($output);
?>
