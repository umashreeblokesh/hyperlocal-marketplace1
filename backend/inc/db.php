<?php
// backend/inc/db.php

$host = "localhost";
$user = "root";        // default in XAMPP
$pass = "";            // default: empty
$dbname = "hyperlocal_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
