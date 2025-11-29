<?php
// backend/admin/approve_vendor.php

session_start();
require_once "../inc/db.php";
require_once "../inc/functions.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../frontend/admin/admin-login.html");
    exit;
}

if (!isset($_GET['vendor_id'])) {
    die("Vendor ID missing.");
}

$vendor_id = (int)$_GET['vendor_id'];

// Generate a unique Vendor ID code
$vendor_code = generateVendorId(8);  // e.g. VNDR8XK2

$stmt = $conn->prepare(
    "UPDATE vendors
     SET status = 'approved', vendor_id_code = ?
     WHERE id = ?"
);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("si", $vendor_code, $vendor_id);

if ($stmt->execute()) {
    // In future: SMS with $vendor_code can be sent here
    header("Location: dashboard.php");
    exit;
} else {
    echo "Error approving vendor: " . $stmt->error;
}
?>
