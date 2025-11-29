<?php
// backend/customer/login_customer.php
session_start();
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../frontend/customer/customer-login.html");
    exit;
}

$email    = $_POST['email']    ?? '';
$password = $_POST['password'] ?? '';

$email = trim($email);

if ($email === '' || $password === '') {
    echo "Email and password are required.";
    exit;
}

// 1) Fetch customer by email
$stmt = $conn->prepare(
    "SELECT id, name, email, password_hash, latitude, longitude
     FROM customers
     WHERE email = ?
     LIMIT 1"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();
$stmt->close();

if (!$customer) {
    echo "Invalid email or password.";
    exit;
}

// 2) Check password
if (!password_verify($password, $customer['password_hash'])) {
    echo "Invalid email or password.";
    exit;
}

// 3) Successful login -> set session
$_SESSION['customer_id']   = $customer['id'];
$_SESSION['customer_name'] = $customer['name'];
$_SESSION['customer_email'] = $customer['email'];

// 4) OPTIONAL: update location if we got it from login form
$lat  = $_POST['latitude']  ?? null;
$lng  = $_POST['longitude'] ?? null;
$loc  = $_POST['login_location'] ?? null;

if (!empty($lat) && !empty($lng)) {
    $lat = (float)$lat;
    $lng = (float)$lng;

    $update = $conn->prepare(
        "UPDATE customers
         SET latitude = ?, longitude = ?, location = COALESCE(?, location)
         WHERE id = ?"
    );
    $update->bind_param("ddsi", $lat, $lng, $loc, $customer['id']);
    $update->execute();
    $update->close();
}

// 5) Redirect to nearby shops page
header("Location: shops.php");
exit;
