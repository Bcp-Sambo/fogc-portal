<?php
// api/import_attendees.php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

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

    // Skip Header Row?
    // Let's assume user provides header, so we skip first line if it looks like a header
    $firstRow = fgetcsv($handle);
    // Simple heuristic: if 'Phone' is in first col, skip. Else rewind.
    if (stripos($firstRow[0], 'phone') === false && is_numeric(str_replace(['+',' '], '', $firstRow[0]))) {
         // Doesn't look like header, rewind
         rewind($handle);
    }

    while (($row = fgetcsv($handle)) !== false) {
        // Expected Format: Phone, First Name, Last Name, Sex, Is Member (Yes/No), Email, Invited By
        // Robustness: Only Phone is strictly required unique key.
        
        $phone = $row[0] ?? '';
        // Sanitize Phone
        $phoneClean = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phoneClean) < 10) {
            $skipped++;
            continue;
        }

        $fname = $row[1] ?? 'Unknown';
        $lname = $row[2] ?? '';
        $sex = $row[3] ?? 'Male'; // Default
        $isMember = isset($row[4]) && strtolower($row[4]) === 'no' ? 'No' : 'Yes'; // Default Yes if ambiguous
        $email = $row[5] ?? null;
        $invitedBy = $row[6] ?? null;

        $stmt->execute([$phone, $fname, $lname, $sex, $isMember, $email, $invitedBy]);
        
        if ($stmt->rowCount() > 0) {
            $imported++;
        } else {
            $skipped++; // Duplicate
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
