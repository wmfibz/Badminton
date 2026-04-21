<?php
// db_connect.php
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = "";     // Default XAMPP password (usually empty)
$dbname = "badminton_booking_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>