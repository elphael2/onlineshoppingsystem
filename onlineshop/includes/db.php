<?php
$conn = new mysqli("localhost", "root", "", "onlineshop");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
