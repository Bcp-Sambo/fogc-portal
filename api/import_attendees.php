<?php
// api/import_attendees.php
session_start();
// Disable Error Display to prevent JSON corruption
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/helpers.php';

// Auth Check (Admin only)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File upload failed']);
    exit;
}

$file = $_FILES['csv_file']['tmp_name'];
$handle = fopen($file, 'r');

if ($handle === false) {
    echo json_encode(['success' => false, 'message' => 'Could not open file']);
    exit;
}

$imported = 0;
$skipped = 0;

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT IGNORE INTO attendees (phone_number, first_name, last_name, sex, is_member, email, invited_by) VALUES (?, ?, ?, ?, ?, ?, ?)");

    // Determine if first row is header
    $firstRow = fgetcsv($handle);
    if ($firstRow) {
        $firstCell = strtolower(trim($firstRow[0]));
        // If first cell looks like a phone number (>3 digits), it's probably data
        if (preg_match('/[0-9]{3}/', $firstCell)) {
            rewind($handle);
        }
        // Else assume it is "Phone" or similar header, so we skip it.
    }

    while (($row = fgetcsv($handle)) !== false) {
        // Skip empty rows
        if (empty(array_filter($row))) continue;

        // MAPPING: Phone(0), First(1), Last(2), Sex(3), Member(4), Email(5)
        
        $rawPhone = $row[0] ?? '';
        $phone = clean_phone($rawPhone); // Use Smart Helper
        
        if (strlen($phone) < 10) {
            $skipped++;
            continue;
        }

        $fname = $row[1] ?? 'Unknown';
        $lname = $row[2] ?? '';
        $sex = ucfirst(strtolower(trim($row[3] ?? 'Male'))); // Normalize "male"/"Male"
        
        // Normalize Member Yes/No
        $memberRaw = strtolower(trim($row[4] ?? ''));
        $isMember = ($memberRaw === 'yes' || $memberRaw === 'y') ? 'Yes' : 'No'; 
        
        $email = trim($row[5] ?? '');
        $invitedBy = trim($row[6] ?? '');

        // Convert empty strings to NULL for DB cleanliness
        if ($email === '') $email = null;
        if ($invitedBy === '') $invitedBy = null;

        $stmt->execute([$phone, $fname, $lname, $sex, $isMember, $email, $invitedBy]);
        
        if ($stmt->rowCount() > 0) {
            $imported++;
        } else {
            $skipped++; // Duplicate or ignored by INSERT IGNORE
        }
    }

    $pdo->commit();
    fclose($handle);

    echo json_encode([
        'success' => true, 
        'message' => "Imported $imported attendees. Skipped $skipped duplicates/invalid."
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>
