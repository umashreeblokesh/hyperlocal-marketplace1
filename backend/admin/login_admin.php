<?php
// backend/admin/login_admin.php

session_start();
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email']    ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        die("Both email and password are required.");
    }

    $stmt = $conn->prepare("SELECT id, password_hash FROM admins WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($admin_id, $hash);
        $stmt->fetch();

        if (password_verify($password, $hash)) {
            // Login success
            $_SESSION['admin_id'] = $admin_id;
            header("Location: dashboard.php");
            exit;
        }
    }

    echo "Invalid admin credentials.";
} else {
    echo "Invalid request.";
}
?>
