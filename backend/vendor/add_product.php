<?php
// backend/vendor/add_product.php
session_start();
require_once "../inc/db.php";

if (!isset($_SESSION['vendor_id'])) {
    header("Location: ../../frontend/vendor/vendor-login.html");
    exit;
}

$vendor_id = $_SESSION['vendor_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name']  ?? '';
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock_status'] ?? 'in_stock';

    if (empty($name) || empty($price)) {
        $message = "All fields are required.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO products (vendor_id, name, price, stock_status)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("isds", $vendor_id, $name, $price, $stock);

        if ($stmt->execute()) {
            $message = "✅ Product added successfully.";
        } else {
            $message = "❌ Error: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add Product | Vendor</title>
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
    <h2 class="mb-3">Add New Product</h2>
    <p class="text-muted mb-3">
      Add items that you want customers to see and order.
    </p>

    <?php if (!empty($message)): ?>
      <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <label class="form-label">Product Name</label>
        <input type="text" name="name" class="form-control" required />
      </div>

      <div class="mb-3">
        <label class="form-label">Price (₹)</label>
        <input type="number" step="0.01" name="price" class="form-control" required />
      </div>

      <div class="mb-3">
        <label class="form-label">Stock Status</label>
        <select name="stock_status" class="form-select">
          <option value="in_stock">In Stock</option>
          <option value="out_of_stock">Out of Stock</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary btn-rounded">
        Save Product
      </button>
      <a href="dashboard.php" class="btn btn-outline-secondary btn-rounded ms-2">
        Back to Dashboard
      </a>
    </form>
  </div>
</body>
</html>
