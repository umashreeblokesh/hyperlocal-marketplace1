<?php
// backend/customer/place_order_cart.php

session_start();
require_once "../inc/db.php";

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../../frontend/customer/customer-login.html");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$cart = $_SESSION['cart'] ?? null;

if (!$cart || empty($cart['items'])) {
    die("Cart is empty.");
}

$vendor_id = (int)$cart['vendor_id'];
$product_ids = array_keys($cart['items']);

if ($vendor_id <= 0 || empty($product_ids)) {
    die("Invalid cart data.");
}

// Get product prices
$ids_str = implode(',', array_map('intval', $product_ids));
$query = "SELECT id, price FROM products WHERE id IN ($ids_str) AND vendor_id = $vendor_id";
$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    die("Products not found.");
}

$prices = [];
while ($row = $result->fetch_assoc()) {
    $prices[(int)$row['id']] = (float)$row['price'];
}

// Calculate total_amount
$total_amount = 0.0;
foreach ($cart['items'] as $pid => $qty) {
    if (isset($prices[$pid])) {
        $total_amount += $prices[$pid] * $qty;
    }
}

// Insert into orders
$stmtOrder = $conn->prepare(
    "INSERT INTO orders (customer_id, vendor_id, total_amount, status)
     VALUES (?, ?, ?, 'placed')"
);
$stmtOrder->bind_param("iid", $customer_id, $vendor_id, $total_amount);

if ($stmtOrder->execute()) {
    $order_id = $stmtOrder->insert_id;

    // Insert all items
    $stmtItem = $conn->prepare(
        "INSERT INTO order_items (order_id, product_id, quantity, price_each)
         VALUES (?, ?, ?, ?)"
    );

    foreach ($cart['items'] as $pid => $qty) {
        if (isset($prices[$pid])) {
            $price_each = $prices[$pid];
            $stmtItem->bind_param("iiid", $order_id, $pid, $qty, $price_each);
            $stmtItem->execute();
        }
    }

    $stmtItem->close();

    // Clear cart after order
    unset($_SESSION['cart']);

    // Redirect to My Orders
    header("Location: orders.php");
    exit;
} else {
    echo "❌ Failed to place order: " . $stmtOrder->error;
}
?>
