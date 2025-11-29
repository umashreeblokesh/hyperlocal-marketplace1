<?php
// backend/vendor/manage_products.php

session_start();
require_once "../inc/db.php";

if (!isset($_SESSION['vendor_id'])) {
    header("Location: ../../frontend/vendor/vendor-login.html");
    exit;
}

$vendor_id = $_SESSION['vendor_id'];

// Fetch vendor products
$stmt = $conn->prepare(
    "SELECT id, name, price, stock_status, created_at 
     FROM products
     WHERE vendor_id = ?"
);
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Products | Vendor</title>
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
    <h2 class="mb-3">My Products</h2>
    <p class="text-muted mb-3">
      Edit prices, stock status, or remove products from your shop.
    </p>

    <a href="add_product.php" class="btn btn-primary btn-sm btn-rounded mb-3">
      + Add New Product
    </a>

    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Price (₹)</th>
          <th>Stock</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars($row['price']) ?></td>
              <td><?= htmlspecialchars($row['stock_status']) ?></td>
              <td><?= htmlspecialchars($row['created_at']) ?></td>
              <td>
                <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">
                  Edit
                </a>
                <a href="delete_product.php?id=<?= $row['id'] ?>"
                   class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Are you sure you want to delete this product?');">
                  Delete
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="6">No products added yet.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
