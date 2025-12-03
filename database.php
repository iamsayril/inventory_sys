<?php
// Enable error reporting for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_sys_quinio"; // Make sure this matches your DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Set charset to avoid encoding issues
$conn->set_charset("utf8");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
