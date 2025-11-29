<?php
// backend/vendor/login_vendor.php

session_start();
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email']    ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        die("Both email and password are required.");
    }

    $stmt = $conn->prepare("SELECT id, password_hash, status FROM vendors WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($vendor_id, $hash, $status);
        $stmt->fetch();

        if ($status !== 'approved') {
            die("⚠️ Your account is not approved yet. Current status: " . $status);
        }

        if (password_verify($password, $hash)) {
            $_SESSION['vendor_id'] = $vendor_id;

            // After successful login, go to vendor dashboard (frontend)
            header("Location: dashboard.php");

            exit;
        }
    }

    echo "Invalid email or password.";
} else {
    echo "Invalid request.";
}
?>
