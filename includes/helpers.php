<?php
// includes/helpers.php

// Normalize Phone Number to Nigerian 11-digit format (080...)
function clean_phone($phone) {
    // 1. Remove non-digits
    $cleaned = preg_replace('/[^0-9]/', '', $phone);

    // 2. Handle International Format (234...)
    if (strlen($cleaned) == 13 && substr($cleaned, 0, 3) == '234') {
        $cleaned = '0' . substr($cleaned, 3);
    }
    
    // 3. Handle 10-digit format (90...)
    if (strlen($cleaned) == 10) {
        $cleaned = '0' . $cleaned;
    }

    return $cleaned;
}
?>
