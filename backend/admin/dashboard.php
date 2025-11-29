<?php
// backend/admin/dashboard.php

session_start();
require_once "../inc/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../frontend/admin/admin-login.html");
    exit;
}

// Fetch pending vendors
$pendingResult = $conn->query(
    "SELECT id, owner_name, shop_name, location, shop_type, phone, email, license_pdf_path, status
     FROM vendors
     WHERE status = 'pending'"
);

// Fetch approved vendors (to show Vendor ID)
$approvedResult = $conn->query(
    "SELECT id, owner_name, shop_name, location, shop_type, phone, email, vendor_id_code, status
     FROM vendors
     WHERE status = 'approved'"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - Vendors</title>
</head>
<body>
  <h2>Admin Dashboard</h2>

  <!-- Pending Vendors Section -->
  <h3>Pending Vendor Applications</h3>

  <table border="1" cellpadding="6" cellspacing="0">
    <tr>
      <th>Owner</th>
      <th>Shop</th>
      <th>Location</th>
      <th>Type</th>
      <th>Phone</th>
      <th>Email</th>
      <th>License</th>
      <th>Status</th>
      <th>Action</th>
    </tr>

    <?php if ($pendingResult && $pendingResult->num_rows > 0): ?>
      <?php while ($row = $pendingResult->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['owner_name']) ?></td>
          <td><?= htmlspecialchars($row['shop_name']) ?></td>
          <td><?= htmlspecialchars($row['location']) ?></td>
          <td><?= htmlspecialchars($row['shop_type']) ?></td>
          <td><?= htmlspecialchars($row['phone']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td>
            <?php if (!empty($row['license_pdf_path'])): ?>
              <a href="../<?= htmlspecialchars($row['license_pdf_path']) ?>" target="_blank">View PDF</a>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($row['status']) ?></td>
          <td>
            <a href="approve_vendor.php?vendor_id=<?= $row['id'] ?>">Approve</a> |
            <a href="reject_vendor.php?vendor_id=<?= $row['id'] ?>">Reject</a>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr>
        <td colspan="9">No pending vendor applications.</td>
      </tr>
    <?php endif; ?>
  </table>

  <br><hr><br>

  <!-- Approved Vendors Section -->
  <h3>Approved Vendors (with Vendor ID)</h3>

  <table border="1" cellpadding="6" cellspacing="0">
    <tr>
      <th>Owner</th>
      <th>Shop</th>
      <th>Location</th>
      <th>Type</th>
      <th>Phone</th>
      <th>Email</th>
      <th>Vendor ID</th>
      <th>Status</th>
    </tr>

    <?php if ($approvedResult && $approvedResult->num_rows > 0): ?>
      <?php while ($row = $approvedResult->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['owner_name']) ?></td>
          <td><?= htmlspecialchars($row['shop_name']) ?></td>
          <td><?= htmlspecialchars($row['location']) ?></td>
          <td><?= htmlspecialchars($row['shop_type']) ?></td>
          <td><?= htmlspecialchars($row['phone']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td><?= htmlspecialchars($row['vendor_id_code']) ?></td>
          <td><?= htmlspecialchars($row['status']) ?></td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr>
        <td colspan="8">No approved vendors yet.</td>
      </tr>
    <?php endif; ?>
  </table>

  <p><a href="logout.php">Logout</a></p>
</body>
</html>
