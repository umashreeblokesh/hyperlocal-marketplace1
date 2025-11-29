<?php
// backend/customer/shops.php

session_start();
require_once "../inc/db.php";

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../../frontend/customer/customer-login.html");
    exit;
}

$customer_id = $_SESSION['customer_id'];

// 1) Get customer's location
$stmt = $conn->prepare("SELECT latitude, longitude FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$stmt->bind_result($custLat, $custLng);
$stmt->fetch();
$stmt->close();

$hasCustomerLocation = !empty($custLat) && !empty($custLng);

// 2) Get all approved vendors with their coordinates
$result = $conn->query(
    "SELECT id, shop_name, shop_address, location, shop_type, latitude, longitude 
     FROM vendors 
     WHERE status = 'approved'"
);

// 3) Function to calculate distance using Haversine formula
function distanceKm($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // km

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);

    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distance = $earthRadius * $c;

    return $distance;
}

// radius in km for filtering (change as you like)
$RADIUS_KM = 3.0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Nearby Shops | Hyperlocal Mart</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <link rel="stylesheet" href="../../frontend/css/style.css" />
</head>
<body>
<div class="page-wrapper page-fade">
  <div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="mb-0">Nearby Verified Shops</h2>
      <div class="d-flex gap-2">
        <a href="orders.php" class="btn btn-outline-primary btn-sm">My Orders</a>
        <a href="logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
      </div>
    </div>

    <?php if ($hasCustomerLocation): ?>
      <p class="text-helper">
        Showing shops within <strong><?php echo $RADIUS_KM; ?> km</strong> of your location.
      </p>
    <?php else: ?>
      <p class="text-helper">
        We could not detect your precise location. Showing all approved shops.
      </p>
    <?php endif; ?>

    <div class="row g-3 mt-3">

      <?php
      $shopsShown = 0;

      if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {

              $distance = null;
              $vendorLat = $row['latitude'];
              $vendorLng = $row['longitude'];

              if ($hasCustomerLocation && !empty($vendorLat) && !empty($vendorLng)) {
                  $distance = distanceKm(
                      (float)$custLat,
                      (float)$custLng,
                      (float)$vendorLat,
                      (float)$vendorLng
                  );

                  // skip vendors beyond radius
                  if ($distance > $RADIUS_KM) {
                      continue;
                  }
              }

              $shopsShown++;
      ?>
        <div class="col-md-4">
          <div class="role-card h-100">
            <h5 class="role-title mb-1">
              <?php echo htmlspecialchars($row['shop_name']); ?>
            </h5>
            <p class="role-description mb-1">
              <?php echo htmlspecialchars($row['shop_address']); ?><br />
              Type: <?php echo htmlspecialchars($row['shop_type']); ?>
            </p>

            <?php if ($distance !== null): ?>
              <p class="small mb-1">
                📍 <strong><?php echo number_format($distance, 1); ?> km away</strong>
              </p>
            <?php endif; ?>

            <span class="badge bg-success mb-2">Open</span><br />

            <a href="products.php?vendor_id=<?php echo $row['id']; ?>"
               class="btn btn-success btn-sm mt-1">
              View Products
            </a>
          </div>
        </div>
      <?php
          } // end while
      }

      if ($shopsShown === 0) {
          echo '<p class="mt-3">No shops found within ' . $RADIUS_KM . ' km from your location.</p>';
      }
      ?>
    </div>

    <p class="auth-note mt-4 mb-0">
      These shops are loaded dynamically from the database where status = <strong>'approved'</strong>
      and filtered by distance from the logged-in customer.
    </p>

  </div>
</div>
</body>
</html>
