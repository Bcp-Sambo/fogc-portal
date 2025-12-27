<?php
// includes/db.php

// Database Credentials
// UPDATE THESE VALUES TO MATCH YOUR CPANEL DATABASE
$host = '192.250.229.36';
$dbname = 'bubblebotsol_fogc_portal';
$user = 'bubblebotsol_fogc_admin';
$pass = 'DefaultFogcPass@123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    
    // Set Error Mode to Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set Default Fetch Mode to Associative Array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Return JSON error for API compatibility
    http_response_code(500);
    // Log the actual error to a file (optional but recommended)
    error_log("Database Connection Failed: " . $e->getMessage());
    die(json_encode([
        'success' => false, 
        'message' => "Database Connection Failed. Please try again later."
    ]));
}
?>
