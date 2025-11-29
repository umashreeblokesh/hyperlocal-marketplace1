<?php
// backend/vendor/delete_product.php

session_start();
require_once "../inc/db.php";

if (!isset($_SESSION['vendor_id'])) {
    header("Location: ../../frontend/vendor/vendor-login.html");
    exit;
}

$vendor_id = $_SESSION['vendor_id'];

if (!isset($_GET['id'])) {
    die("Product ID missing.");
}

$product_id = (int)$_GET['id'];

// Delete product only if it belongs to this vendor
$stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND vendor_id = ?");
$stmt->bind_param("ii", $product_id, $vendor_id);

if ($stmt->execute()) {
    header("Location: manage_products.php");
    exit;
} else {
    echo "Error deleting product: " . $stmt->error;
}
?>
