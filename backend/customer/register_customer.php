<?php
// backend/customer/register_customer.php

require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name']  ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $pass  = $_POST['password'] ?? '';

    if (empty($name) || empty($phone) || empty($email) || empty($pass)) {
        die("All fields are required.");
    }

    $hash = password_hash($pass, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO customers (name, phone, email, password_hash)
         VALUES (?, ?, ?, ?)"
    );

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssss", $name, $phone, $email, $hash);

    try {
        if ($stmt->execute()) {
            // ✅ Redirect to login page after successful signup
            header("Location: /hyperlocal_marketplace/frontend/customer/customer-login.html");
            exit;
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            echo "⚠️ This email is already registered. Please login.";
        } else {
            echo "❌ Database error: " . $e->getMessage();
        }
    }
} else {
    echo "Invalid request.";
}
?>
