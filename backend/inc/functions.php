<?php
// backend/inc/functions.php

// Generate a vendor ID like VNDR8XK2 (letters + numbers, no special chars)
function generateVendorId($length = 8) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $id = 'VNDR';
    // VNDR is 4 characters, generate remaining
    for ($i = 0; $i < $length - 4; $i++) {
        $id .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $id;
}

// Simple redirect helper (optional)
function redirect($url) {
    header("Location: $url");
    exit;
}
?>
