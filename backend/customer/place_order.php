<?php
// backend/customer/place_order.php

session_start();
require_once "../inc/db.php";

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../../frontend/customer/customer-login.html");
    exit;
}

$customer_id = $_SESSION['customer_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $vendor_id  = (int)($_POST['vendor_id'] ?? 0);
    $quantity   = (int)($_POST['quantity'] ?? 1);

    if ($product_id <= 0 || $vendor_id <= 0 || $quantity <= 0) {
        die("Invalid order details.");
    }

    // Get product price
    $stmtProd = $conn->prepare("SELECT price FROM products WHERE id = ? AND vendor_id = ?");
    $stmtProd->bind_param("ii", $product_id, $vendor_id);
    $stmtProd->execute();
    $stmtProd->bind_result($price_each);
    if (!$stmtProd->fetch()) {
        die("Product not found.");
    }
    $stmtProd->close();

    $total_amount = $price_each * $quantity;

    // Insert into orders
    $stmtOrder = $conn->prepare(
        "INSERT INTO orders (customer_id, vendor_id, total_amount, status)
         VALUES (?, ?, ?, 'placed')"
    );
    $stmtOrder->bind_param("iid", $customer_id, $vendor_id, $total_amount);

    if ($stmtOrder->execute()) {
        $order_id = $stmtOrder->insert_id;

        // Insert order item
        $stmtItem = $conn->prepare(
            "INSERT INTO order_items (order_id, product_id, quantity, price_each)
             VALUES (?, ?, ?, ?)"
        );
        $stmtItem->bind_param("iiid", $order_id, $product_id, $quantity, $price_each);
        $stmtItem->execute();
        $stmtItem->close();

        // Simple confirmation message
                // Redirect to My Orders page after success
        header("Location: orders.php");
        exit;

    } else {
        echo "❌ Failed to place order: " . $stmtOrder->error;
    }
} else {
    echo "Invalid request.";
}
?>
