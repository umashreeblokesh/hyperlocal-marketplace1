<?php
// backend/vendor/dashboard.php

session_start();
require_once "../inc/db.php";

if (!isset($_SESSION['vendor_id'])) {
    // Not logged in as vendor
    header("Location: ../../frontend/vendor/vendor-login.html");
    exit;
}

$vendor_id = $_SESSION['vendor_id'];

// Fetch vendor details from DB
$stmt = $conn->prepare("SELECT shop_name, shop_address, location, shop_type, vendor_id_code, status 
                        FROM vendors WHERE id = ?");
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$stmt->bind_result($shop_name, $shop_address, $location, $shop_type, $vendor_code, $status);
$stmt->fetch();
$stmt->close();

// Fallback if no vendor_code yet
if (empty($vendor_code)) {
    $vendor_code = "Not assigned yet";
}
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
      <!-- Left: Shop info -->
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

        <!-- Quick actions (same as before) -->
        <div class="card">
          <div class="card-body">
            <h6 class="card-title mb-3">Quick Actions</h6>
            <button class="btn btn-primary w-100 btn-rounded mb-2">
              + Add New Product
            </button>
            <button class="btn btn-outline-primary w-100 btn-rounded mb-2">
              Manage Categories
            </button>
            <button class="btn btn-outline-secondary w-100 btn-rounded">
              View Profile
            </button>
          </div>
        </div>
      </div>

      <!-- Right: Products & Orders (keep your old static tables here) -->
      <div class="col-lg-8">
        <!-- You can reuse your existing product & orders tables here -->
        <!-- For now just copy from your vendor-dashboard.html -->
        <div class="card mb-3">
          <div class="card-body">
            <h6 class="card-title mb-2">Your Products</h6>
            <p class="text-muted small mb-0">
              (Static demo table here – can be connected to DB later)
            </p>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <h6 class="card-title mb-2">Recent Orders</h6>
            <p class="text-muted small mb-0">
              (Static demo table here – can be connected to orders table later)
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
