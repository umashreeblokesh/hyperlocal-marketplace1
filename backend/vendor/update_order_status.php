<?php
// backend/vendor/update_order_status.php

session_start();
require_once "../inc/db.php";

if (!isset($_SESSION['vendor_id'])) {
    header("Location: ../../frontend/vendor/vendor-login.html");
    exit;
}

$vendor_id = $_SESSION['vendor_id'];

if (!isset($_GET['id']) || !isset($_GET['status'])) {
    die("Missing parameters.");
}

$order_id = (int)$_GET['id'];
$status   = $_GET['status'];

$allowed = ['placed', 'accepted', 'rejected', 'delivered'];
if (!in_array($status, $allowed, true)) {
    die("Invalid status.");
}

// Update order only if it belongs to this vendor
$stmt = $conn->prepare(
    "UPDATE orders SET status = ? WHERE id = ? AND vendor_id = ?"
);
$stmt->bind_param("sii", $status, $order_id, $vendor_id);

if ($stmt->execute()) {
    header("Location: orders.php");
    exit;
} else {
    echo "Error updating status: " . $stmt->error;
}
?>
