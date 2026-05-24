<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'rental_db';

// First connect without database
$conn = new mysqli($host, $user, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    // Database created or already exists
} else {
    die("Error creating database: " . $conn->error);
}

// Now select the database
$conn->select_db($database);

// Create tables if they don't exist
$tables = [
    "CREATE TABLE IF NOT EXISTS Client (
        client_id CHAR(6) PRIMARY KEY,
        first_name VARCHAR(25) NOT NULL,
        last_name VARCHAR(25) NOT NULL,
        contact VARCHAR(30) NOT NULL,
        location VARCHAR(50)
    )",
    
    "CREATE TABLE IF NOT EXISTS Employee (
        employee_id CHAR(6) PRIMARY KEY,
        first_name VARCHAR(25) NOT NULL,
        last_name VARCHAR(25) NOT NULL,
        wage INTEGER
    )",
    
    "CREATE TABLE IF NOT EXISTS Item_Type (
        item_type_id CHAR(3) PRIMARY KEY,
        type_name VARCHAR(20)
    )",
    
    "CREATE TABLE IF NOT EXISTS Rental_Item (
        item_id CHAR(6) PRIMARY KEY,
        item_name VARCHAR(25),
        item_type_id CHAR(3),
        individual_cost INTEGER,
        total_stock INTEGER,
        FOREIGN KEY (item_type_id) REFERENCES Item_Type(item_type_id)
    )"
];

foreach ($tables as $table) {
    if ($conn->query($table) !== TRUE) {
        // Table might already exist, continue
    }
}

// Insert default item types if empty
$check = $conn->query("SELECT COUNT(*) as count FROM Item_Type");
$row = $check->fetch_assoc();
if ($row['count'] == 0) {
    $conn->query("INSERT INTO Item_Type VALUES 
        ('001', 'Electronics'),
        ('002', 'Furniture'),
        ('003', 'Tools'),
        ('004', 'Vehicles'),
        ('005', 'Appliances')");
}

// Set charset
$conn->set_charset("utf8");

// Function for prepared statements
function executeQuery($sql, $types = "", ...$params) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}

// Function to generate IDs
function generateId($prefix, $table, $column) {
    global $conn;
    $result = $conn->query("SELECT MAX($column) as max_id FROM $table");
    $row = $result->fetch_assoc();
    $lastId = $row['max_id'];
    if ($lastId) {
        $num = intval(substr($lastId, 1)) + 1;
        return $prefix . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
    return $prefix . '00001';
}
?>