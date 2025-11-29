<?php
require_once "inc/db.php";

$name  = "Test User";
$phone = "9876543210";
$email = "testuser@example.com";
$pass  = "Test@123";

$hash = password_hash($pass, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO customers (name, phone, email, password_hash) VALUES (?, ?, ?, ?)"
);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("ssss", $name, $phone, $email, $hash);

if ($stmt->execute()) {
    echo "✅ Test insert success";
} else {
    echo "❌ Error inserting: " . $stmt->error;
}
