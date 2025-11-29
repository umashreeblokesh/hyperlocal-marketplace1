<?php
// backend/customer/orders.php

session_start();
require_once "../inc/db.php";

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../../frontend/customer/customer-login.html");
    exit;
}

$customer_id = $_SESSION['customer_id'];

// Fetch orders for this customer
$query = "
SELECT o.id, o.total_amount, o.status, o.created_at,
       v.shop_name, v.location
FROM orders o
JOIN vendors v ON o.vendor_id = v.id
WHERE o.customer_id = ?
ORDER BY o.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Orders | Hyperlocal Marketplace</title>
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
    <h2 class="mb-3">My Orders</h2>
    <p class="text-muted mb-3">
      Track the status of your orders from nearby shops.
    </p>

    <table class="table table-bordered table-striped align-middle">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Shop</th>
          <th>Location</th>
          <th>Total (₹)</th>
          <th>Status</th>
          <th>Placed At</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td>#<?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['shop_name']) ?></td>
              <td><?= htmlspecialchars($row['location']) ?></td>
              <td><?= htmlspecialchars($row['total_amount']) ?></td>
              <td>
                <?php
                  $status = $row['status'];
                  $badgeClass = "bg-secondary";
                  $label = "Placed";
                  if ($status === 'accepted') { $badgeClass = "bg-info"; $label = "Accepted"; }
                  if ($status === 'rejected') { $badgeClass = "bg-danger"; $label = "Rejected"; }
                  if ($status === 'delivered') { $badgeClass = "bg-success"; $label = "Delivered"; }
                ?>
                <span class="badge <?= $badgeClass ?>"><?= $label ?></span>
              </td>
              <td><?= htmlspecialchars($row['created_at']) ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="6">You have not placed any orders yet.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <p class="auth-note mt-3 mb-0">
      Order status is updated by vendors in real time (Accepted / Rejected / Delivered).
    </p>
  </div>
</body>
</html>
