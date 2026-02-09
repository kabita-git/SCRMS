<?php

// Create connection using MySQLi (Object-Oriented)
$conn = new mysqli('localhost', 'root', '', 'scrms');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for proper character encoding
$conn->set_charset("utf8mb4");

