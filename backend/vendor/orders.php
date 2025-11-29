<?php
// backend/vendor/orders.php

session_start();
require_once "../inc/db.php";

if (!isset($_SESSION['vendor_id'])) {
    header("Location: ../../frontend/vendor/vendor-login.html");
    exit;
}

$vendor_id = $_SESSION['vendor_id'];

// Fetch orders for this vendor with customer name
$query = "
SELECT o.id, o.total_amount, o.status, o.created_at,
       c.name AS customer_name,
       c.phone AS customer_phone
FROM orders o
JOIN customers c ON o.customer_id = c.id
WHERE o.vendor_id = ?
ORDER BY o.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Orders | Vendor</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <link rel="stylesheet" href="../../frontend/css/style.css" />
</head>
<body>
  <nav class="navbar navbar-expand-lg bg-white shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="dashboard.php">
        Vendor <span class="text-primary">Dashboard</span>
      </a>
      <div class="ms-auto">
        <a href="logout.php" class="btn btn-outline-secondary btn-sm btn-rounded">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container py-4">
    <h2 class="mb-3">Customer Orders</h2>
    <p class="text-muted mb-3">
      View and update the status of orders placed for your shop.
    </p>

    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Customer</th>
          <th>Phone</th>
          <th>Total (₹)</th>
          <th>Status</th>
          <th>Placed At</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['customer_name']) ?></td>
              <td><?= htmlspecialchars($row['customer_phone']) ?></td>
              <td><?= htmlspecialchars($row['total_amount']) ?></td>
              <td><?= htmlspecialchars($row['status']) ?></td>
              <td><?= htmlspecialchars($row['created_at']) ?></td>
              <td>
                <a href="update_order_status.php?id=<?= $row['id'] ?>&status=accepted"
                   class="btn btn-sm btn-outline-success">
                  Accept
                </a>
                <a href="update_order_status.php?id=<?= $row['id'] ?>&status=rejected"
                   class="btn btn-sm btn-outline-danger">
                  Reject
                </a>
                <a href="update_order_status.php?id=<?= $row['id'] ?>&status=delivered"
                   class="btn btn-sm btn-outline-primary">
                  Delivered
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="7">No orders received yet.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
