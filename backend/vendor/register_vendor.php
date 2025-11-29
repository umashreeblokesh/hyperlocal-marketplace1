<?php
// backend/vendor/register_vendor.php

require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // READ FIELDS – names MUST match your HTML form
    $owner_name   = $_POST['owner_name']   ?? '';
    $shop_name    = $_POST['shop_name']    ?? '';
    $shop_address = $_POST['shop_address'] ?? '';
    $location     = $_POST['location']     ?? '';
    $shop_type    = $_POST['shop_type']    ?? '';
    $phone        = $_POST['phone']        ?? '';
    $email        = $_POST['email']        ?? '';
    $password     = $_POST['password']     ?? '';

    // These are optional now (only used if you altered DB for lat/lng)
    $latitude     = $_POST['latitude']     ?? null;
    $longitude    = $_POST['longitude']    ?? null;

    // BASIC VALIDATION
    if (
        empty($owner_name) || empty($shop_name) || empty($shop_address) ||
        empty($location)   || empty($shop_type)  || empty($phone) ||
        empty($email)      || empty($password)
    ) {
        die("All fields are required.");
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // HANDLE LICENSE PDF UPLOAD
    $license_path = null;
    if (isset($_FILES['license_pdf']) && $_FILES['license_pdf']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "../uploads/licenses/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $tmpName = $_FILES['license_pdf']['tmp_name'];
        $originalName = basename($_FILES['license_pdf']['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($ext !== 'pdf') {
            die("Only PDF license files are allowed.");
        }

        $newName = "license_" . time() . "_" . rand(1000, 9999) . ".pdf";
        $destPath = $uploadDir . $newName;

        if (!move_uploaded_file($tmpName, $destPath)) {
            die("Failed to upload license file.");
        }

        $license_path = "uploads/licenses/" . $newName;
    } else {
        die("License PDF is required.");
    }

    // INSERT VENDOR – simple version (without forcing lat/lng)
    $stmt = $conn->prepare(
        "INSERT INTO vendors
         (owner_name, shop_name, shop_address, location, shop_type, phone, email, password_hash, license_pdf_path, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
    );

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "sssssssss",
        $owner_name,
        $shop_name,
        $shop_address,
        $location,
        $shop_type,
        $phone,
        $email,
        $password_hash,
        $license_path
    );

    try {
        if ($stmt->execute()) {
            echo "✅ Vendor registered successfully. Your account is pending admin approval.";
            // Later you can redirect to login:
            // header('Location: ../../frontend/vendor/vendor-login.html');
            // exit;
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            echo "⚠️ This email is already registered as a vendor.";
        } else {
            echo "❌ Database error: " . $e->getMessage();
        }
    }
} else {
    echo "Invalid request.";
}
?>
