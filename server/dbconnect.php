<?php
// Database Configuration
$host     = "localhost";
$db_name  = "mywelfare_db";
$username = "root";
$password = "";

try {
    // Create a new PDO instance
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);

    // Set error mode to Exception to catch connection issues
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optional: Set default fetch mode to associative array
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // If you need to test the connection, you can uncomment the line below:
    // echo "Connected successfully"; 

} catch (PDOException $e) {
    // If connection fails, stop script and show error
    die("Database Connection Failed: " . $e->getMessage());
}