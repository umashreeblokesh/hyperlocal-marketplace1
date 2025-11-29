<?php
// backend/admin/reject_vendor.php

session_start();
require_once "../inc/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../frontend/admin/admin-login.html");
    exit;
}

if (!isset($_GET['vendor_id'])) {
    die("Vendor ID missing.");
}

$vendor_id = (int)$_GET['vendor_id'];

$stmt = $conn->prepare("UPDATE vendors SET status = 'rejected' WHERE id = ?");
$stmt->bind_param("i", $vendor_id);

if ($stmt->execute()) {
    header("Location: dashboard.php");
    exit;
} else {
    echo "Error rejecting vendor: " . $stmt->error;
}
?>
