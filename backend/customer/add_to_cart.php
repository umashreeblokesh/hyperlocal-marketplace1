<?php
// backend/customer/add_to_cart.php

session_start();
require_once "../inc/db.php";

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../../frontend/customer/customer-login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $vendor_id  = (int)($_POST['vendor_id'] ?? 0);
    $quantity   = (int)($_POST['quantity'] ?? 1);

    if ($product_id <= 0 || $vendor_id <= 0 || $quantity <= 0) {
        die("Invalid cart data.");
    }

    // Initialize cart if not exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [
            'vendor_id' => $vendor_id,
            'items'     => []
        ];
    }

    // If cart has a different vendor, reset cart (one shop per order)
    if ($_SESSION['cart']['vendor_id'] !== $vendor_id) {
        $_SESSION['cart'] = [
            'vendor_id' => $vendor_id,
            'items'     => []
        ];
    }

    // Add / update item quantity
    if (!isset($_SESSION['cart']['items'][$product_id])) {
        $_SESSION['cart']['items'][$product_id] = 0;
    }
    $_SESSION['cart']['items'][$product_id] += $quantity;

    // Redirect back to products page
    $redirectVendorId = $vendor_id;
    header("Location: products.php?vendor_id=" . $redirectVendorId);
    exit;
} else {
    echo "Invalid request.";
}
?>
