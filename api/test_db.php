<?php
// api/test_db.php
// Quick Connection Tester with Timeout
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain');

require_once '../includes/db.php';

echo "Attempting connection to Host: $host ...\n";

try {
    // Determine if we are using the global $pdo from db.php (which might have already tried connecting)
    // or if we need to try manually with timeout options.
    
    // Since db.php connects immediately on include, if we got here, it might have worked?
    // OR db.php died.
    // Let's modify db.php check.
    
    if (isset($pdo)) {
        echo "PDO Object exists.\n";
        echo "Connection Status: " . $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "\n";
        echo "Success! Database is reachable.";
    } else {
        echo "PDO Object NOT found. Connection likely failed inside db.php.";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
