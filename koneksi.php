<?php
// file koneksi.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tracer_study";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
