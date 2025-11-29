<?php
// backend/vendor/edit_product.php

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

// First, fetch product and ensure it belongs to this vendor
$stmt = $conn->prepare(
    "SELECT name, price, stock_status FROM products WHERE id = ? AND vendor_id = ?"
);
$stmt->bind_param("ii", $product_id, $vendor_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die("Product not found or not yours.");
}

$stmt->bind_result($name, $price, $stock_status);
$stmt->fetch();
$stmt->close();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name  = $_POST['name']  ?? '';
    $new_price = $_POST['price'] ?? '';
    $new_stock = $_POST['stock_status'] ?? 'in_stock';

    if (empty($new_name) || empty($new_price)) {
        $message = "All fields are required.";
    } else {
        $stmtUpdate = $conn->prepare(
            "UPDATE products
             SET name = ?, price = ?, stock_status = ?
             WHERE id = ? AND vendor_id = ?"
        );
        $stmtUpdate->bind_param("sdsii", $new_name, $new_price, $new_stock, $product_id, $vendor_id);

        if ($stmtUpdate->execute()) {
            $message = "✅ Product updated successfully.";
            // Update variables so form shows new values
            $name = $new_name;
            $price = $new_price;
            $stock_status = $new_stock;
        } else {
            $message = "❌ Error: " . $stmtUpdate->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Product | Vendor</title>
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
    <h2 class="mb-3">Edit Product</h2>

    <?php if (!empty($message)): ?>
      <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <label class="form-label">Product Name</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required />
      </div>

      <div class="mb-3">
        <label class="form-label">Price (₹)</label>
        <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($price) ?>" required />
      </div>

      <div class="mb-3">
        <label class="form-label">Stock Status</label>
        <select name="stock_status" class="form-select">
          <option value="in_stock" <?= $stock_status === 'in_stock' ? 'selected' : '' ?>>In Stock</option>
          <option value="out_of_stock" <?= $stock_status === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary btn-rounded">
        Save Changes
      </button>
      <a href="manage_products.php" class="btn btn-outline-secondary btn-rounded ms-2">
        Back to Products
      </a>
    </form>
  </div>
</body>
</html>
