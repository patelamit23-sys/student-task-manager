<?php
$host = "localhost";       // Always localhost for XAMPP
$user = "root";            // XAMPP default username
$pass = "";                // XAMPP default password (leave empty)
$dbname = "practicaldb";   // Your database name

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
