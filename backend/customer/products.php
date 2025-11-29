<?php
// backend/customer/products.php

session_start();
require_once "../inc/db.php";

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../../frontend/customer/customer-login.html");
    exit;
}

if (!isset($_GET['vendor_id'])) {
    die("Vendor not selected.");
}

$vendor_id = (int)$_GET['vendor_id'];

// Get vendor info
$stmtVendor = $conn->prepare(
    "SELECT shop_name, location, shop_type FROM vendors WHERE id = ? AND status = 'approved'"
);
$stmtVendor->bind_param("i", $vendor_id);
$stmtVendor->execute();
$stmtVendor->bind_result($shop_name, $location, $shop_type);
$stmtVendor->fetch();
$stmtVendor->close();

// Get products
$stmtProd = $conn->prepare(
    "SELECT id, name, price, stock_status FROM products WHERE vendor_id = ?"
);
$stmtProd->bind_param("i", $vendor_id);
$stmtProd->execute();
$resultProd = $stmtProd->get_result();

// Cart count (optional)
$cartCount = 0;
if (isset($_SESSION['cart']) && $_SESSION['cart']['vendor_id'] == $vendor_id) {
    $cartCount = array_sum($_SESSION['cart']['items']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Shop Products | Hyperlocal Marketplace</title>
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
          Back to Shops
        </a>
        <a href="cart.php" class="btn btn-success btn-sm btn-rounded">
          Cart <?php if ($cartCount > 0) echo "($cartCount)"; ?>
        </a>
      </div>
    </div>
  </nav>

  <div class="container py-4">
    <!-- Shop header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h3 class="mb-0"><?= htmlspecialchars($shop_name) ?></h3>
        <p class="text-muted mb-0 small">
          <?= htmlspecialchars($location) ?> • <?= htmlspecialchars($shop_type) ?> • Verified Vendor
        </p>
      </div>
      <span class="badge-soft">Delivering within 3–5 km</span>
    </div>

    <!-- Product grid -->
    <div class="row g-3">
      <?php if ($resultProd && $resultProd->num_rows > 0): ?>
        <?php while ($p = $resultProd->fetch_assoc()): ?>
          <div class="col-md-4">
            <div class="card h-100">
              <div class="card-body">
                <h5 class="card-title mb-1">
                  <?= htmlspecialchars($p['name']) ?>
                </h5>
                <p class="mb-1 fw-semibold">
                  ₹<?= htmlspecialchars($p['price']) ?>
                </p>
                <?php if ($p['stock_status'] === 'in_stock'): ?>
                  <span class="badge bg-success mb-2">In Stock</span>
                <?php else: ?>
                  <span class="badge bg-danger mb-2">Out of Stock</span>
                <?php endif; ?>

                <?php if ($p['stock_status'] === 'in_stock'): ?>
                  <form action="add_to_cart.php" method="post" class="d-flex align-items-center mt-2">
                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="vendor_id" value="<?= $vendor_id ?>">
                    <input type="number" name="quantity" min="1" value="1"
                           class="form-control form-control-sm me-2"
                           style="max-width:80px;">
                    <button type="submit" class="btn btn-sm btn-primary btn-rounded">
                      Add to Cart
                    </button>
                  </form>
                <?php else: ?>
                  <p class="small text-muted mb-0 mt-2">
                    Currently unavailable.
                  </p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No products added yet for this shop.</p>
      <?php endif; ?>
    </div>

    <p class="auth-note mt-4 mb-0">
      You can add multiple items from this shop to your cart and place a single order.
    </p>
  </div>
</body>
</html>
