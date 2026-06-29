<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = 'localhost:3307'; // Change to 'localhost:3307' if you changed the MySQL port in XAMPP
$db_user = 'root';
$db_pass = '';
$db_name = 'gs_coffee';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>