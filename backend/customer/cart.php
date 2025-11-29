<?php
// backend/customer/cart.php

session_start();
require_once "../inc/db.php";

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../../frontend/customer/customer-login.html");
    exit;
}

$cart = $_SESSION['cart'] ?? null;

$items = [];
$total_amount = 0.0;
$vendor_info = null;

if ($cart && !empty($cart['items'])) {
    $vendor_id = (int)$cart['vendor_id'];
    $product_ids = array_keys($cart['items']);

    // Get vendor info
    $stmtVendor = $conn->prepare("SELECT shop_name, location FROM vendors WHERE id = ?");
    $stmtVendor->bind_param("i", $vendor_id);
    $stmtVendor->execute();
    $stmtVendor->bind_result($shop_name, $location);
    $stmtVendor->fetch();
    $stmtVendor->close();

    $vendor_info = [
        'id' => $vendor_id,
        'shop_name' => $shop_name,
        'location' => $location
    ];

    // Build IN clause for products
    $ids_str = implode(',', array_map('intval', $product_ids));
    $query = "SELECT id, name, price FROM products WHERE id IN ($ids_str)";
    $result = $conn->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $pid = (int)$row['id'];
            $qty = $cart['items'][$pid];
            $line_total = $row['price'] * $qty;
            $total_amount += $line_total;

            $items[] = [
                'id'    => $pid,
                'name'  => $row['name'],
                'price' => $row['price'],
                'qty'   => $qty,
                'line_total' => $line_total
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Cart | Hyperlocal Marketplace</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../../frontend/css/style.css" />
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg bg-white shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="shops.php">
        Hyperlocal <span class="text-primary">Marketplace</span>
      </a>
      <div class="ms-auto">
        <a href="shops.php" class="btn btn-outline-primary btn-sm btn-rounded me-2">
          Browse Shops
        </a>
        <a href="orders.php" class="btn btn-success btn-sm btn-rounded me-2">
          My Orders
        </a>
        <a href="logout.php" class="btn btn-outline-secondary btn-sm btn-rounded">
          Logout
        </a>
      </div>
    </div>
  </nav>

  <div class="container py-4">
    <h2 class="mb-3">My Cart</h2>

    <?php if (!$cart || empty($items)): ?>
      <p>Your cart is empty.</p>
      <a href="shops.php" class="btn btn-primary btn-rounded">Browse Shops</a>
    <?php else: ?>
      <p class="text-muted mb-2">
        Shop: <strong><?= htmlspecialchars($vendor_info['shop_name']) ?></strong> –
        <?= htmlspecialchars($vendor_info['location']) ?>
      </p>

      <table class="table table-bordered table-striped align-middle">
        <thead>
          <tr>
            <th>Product</th>
            <th>Price (₹)</th>
            <th>Qty</th>
            <th>Total (₹)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td><?= htmlspecialchars($item['name']) ?></td>
              <td><?= htmlspecialchars($item['price']) ?></td>
              <td><?= htmlspecialchars($item['qty']) ?></td>
              <td><?= htmlspecialchars($item['line_total']) ?></td>
            </tr>
          <?php endforeach; ?>
          <tr>
            <td colspan="3" class="text-end fw-bold">Grand Total</td>
            <td class="fw-bold">₹<?= $total_amount ?></td>
          </tr>
        </tbody>
      </table>

      <form action="place_order_cart.php" method="post">
        <button type="submit" class="btn btn-success btn-rounded">
          Place Order
        </button>
        <a href="shops.php" class="btn btn-outline-secondary btn-rounded ms-2">
          Continue Shopping
        </a>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
