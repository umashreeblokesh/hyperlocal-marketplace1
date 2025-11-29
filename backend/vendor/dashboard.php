<?php
// backend/vendor/dashboard.php

session_start();
require_once "../inc/db.php";

if (!isset($_SESSION['vendor_id'])) {
    header("Location: ../../frontend/vendor/vendor-login.html");
    exit;
}

$vendor_id = $_SESSION['vendor_id'];

// 1) Fetch vendor details
$stmt = $conn->prepare("SELECT shop_name, shop_address, location, shop_type, vendor_id_code, status 
                        FROM vendors WHERE id = ?");
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$stmt->bind_result($shop_name, $shop_address, $location, $shop_type, $vendor_code, $status);
$stmt->fetch();
$stmt->close();

if (empty($vendor_code)) {
    $vendor_code = "Not assigned yet";
}

// 2) Fetch latest 5 products for this vendor
$stmtProd = $conn->prepare(
    "SELECT id, name, price, stock_status, created_at
     FROM products
     WHERE vendor_id = ?
     ORDER BY created_at DESC
     LIMIT 5"
);
$stmtProd->bind_param("i", $vendor_id);
$stmtProd->execute();
$productsResult = $stmtProd->get_result();

// 3) Fetch latest 5 orders for this vendor
$queryOrders = "
SELECT o.id, o.total_amount, o.status, o.created_at,
       c.name AS customer_name
FROM orders o
JOIN customers c ON o.customer_id = c.id
WHERE o.vendor_id = ?
ORDER BY o.created_at DESC
LIMIT 5
";

$stmtOrders = $conn->prepare($queryOrders);
$stmtOrders->bind_param("i", $vendor_id);
$stmtOrders->execute();
$ordersResult = $stmtOrders->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Vendor Dashboard | Hyperlocal Marketplace</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />

  <!-- Use same CSS as frontend -->
  <link rel="stylesheet" href="../../frontend/css/style.css" />
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg bg-white shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="../../frontend/index.html">
        Hyperlocal <span class="text-primary">Marketplace</span>
      </a>
      <div class="ms-auto">
        <a href="logout.php" class="btn btn-outline-secondary btn-sm btn-rounded">
          Logout
        </a>
      </div>
    </div>
  </nav>

  <!-- Main content -->
  <div class="container py-4">
    <h2 class="mb-3">Vendor Dashboard</h2>
    <p class="text-muted mb-4">
      Manage your shop, products, and orders from this panel.
    </p>

    <div class="row g-3">
      <!-- Left: Shop info + Quick actions -->
      <div class="col-lg-4">
        <div class="card mb-3">
          <div class="card-body">
            <h5 class="card-title mb-1">
              <?= htmlspecialchars($shop_name) ?>
            </h5>
            <p class="small text-muted mb-2">
              <?= nl2br(htmlspecialchars($shop_address)) ?><br />
              <?= htmlspecialchars($location) ?><br />
              Type: <?= htmlspecialchars($shop_type) ?>
            </p>
            <span class="badge bg-success mb-2">
              <?= ($status === 'approved') ? 'Verified Vendor' : ucfirst($status); ?>
            </span>
            <p class="small text-muted mb-0">
              Vendor ID: <strong><?= htmlspecialchars($vendor_code) ?></strong><br />
              Status: <strong><?= htmlspecialchars($status) ?></strong>
            </p>
          </div>
        </div>

        <!-- Quick actions -->
        <div class="card">
          <div class="card-body">
            <h6 class="card-title mb-3">Quick Actions</h6>
            <a href="add_product.php" class="btn btn-primary w-100 btn-rounded mb-2">
              + Add New Product
            </a>
            <a href="manage_products.php" class="btn btn-outline-primary w-100 btn-rounded mb-2">
              Manage Products
            </a>
            <a href="orders.php" class="btn btn-outline-success w-100 btn-rounded mb-2">
              View Orders
            </a>
          </div>
        </div>
      </div>

      <!-- Right: Products & Orders -->
      <div class="col-lg-8">
        <!-- Your Products (dynamic) -->
        <div class="card mb-3">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="card-title mb-0">Your Latest Products</h6>
              <a href="manage_products.php" class="small">View all</a>
            </div>

            <?php if ($productsResult && $productsResult->num_rows > 0): ?>
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Price (₹)</th>
                      <th>Stock</th>
                      <th>Added</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($p = $productsResult->fetch_assoc()): ?>
                      <tr>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['price']) ?></td>
                        <td>
                          <?php if ($p['stock_status'] === 'in_stock'): ?>
                            <span class="badge bg-success">In Stock</span>
                          <?php else: ?>
                            <span class="badge bg-danger">Out of Stock</span>
                          <?php endif; ?>
                        </td>
                        <td class="small text-muted"><?= htmlspecialchars($p['created_at']) ?></td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <p class="text-muted mb-0">
                No products added yet. Start by adding your first product.
              </p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Recent Orders (dynamic) -->
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="card-title mb-0">Recent Orders</h6>
              <a href="orders.php" class="small">View all</a>
            </div>

            <?php if ($ordersResult && $ordersResult->num_rows > 0): ?>
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Order</th>
                      <th>Customer</th>
                      <th>Total (₹)</th>
                      <th>Status</th>
                      <th>Time</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($o = $ordersResult->fetch_assoc()): ?>
                      <tr>
                        <td>#<?= $o['id'] ?></td>
                        <td><?= htmlspecialchars($o['customer_name']) ?></td>
                        <td><?= htmlspecialchars($o['total_amount']) ?></td>
                        <td>
                          <?php
                            $st = $o['status'];
                            $badge = "bg-secondary";
                            $label = "Placed";
                            if ($st === 'accepted') { $badge = "bg-info"; $label = "Accepted"; }
                            if ($st === 'rejected') { $badge = "bg-danger"; $label = "Rejected"; }
                            if ($st === 'delivered') { $badge = "bg-success"; $label = "Delivered"; }
                          ?>
                          <span class="badge <?= $badge ?>"><?= $label ?></span>
                        </td>
                        <td class="small text-muted"><?= htmlspecialchars($o['created_at']) ?></td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <p class="text-muted mb-0">
                No orders received yet.
              </p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
