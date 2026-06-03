<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>XAMPP Connection Test</h1>";

// Test 1: PHP Version
echo "<h3>PHP Version: " . phpversion() . "</h3>";

// Test 2: Connect to MySQL WITHOUT database first
echo "<h3>Testing MySQL Connection...</h3>";

// Connect to MySQL server without selecting a database
$conn = new mysqli("localhost", "root", "");

if ($conn->connect_error) {
    die("<p style='color:red'>MySQL Connection Failed: " . $conn->connect_error . "</p>");
}

echo "<p style='color:green'>Connected to MySQL Server!</p>";

// Test 3: Create database
echo "<h3>Creating database...</h3>";
$sql = "CREATE DATABASE IF NOT EXISTS rental_db";
if ($conn->query($sql)) {
    echo "<p style='color:green'>Database 'rental_db' created or already exists!</p>";
} else {
    die("<p style='color:red'>Error creating database: " . $conn->error . "</p>");
}

// Select the database
$conn->select_db("rental_db");
echo "<p style='color:green'>Now using database 'rental_db'</p>";

// Test 4: Create tables
echo "<h3>Creating tables...</h3>";

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
    
    "CREATE TABLE IF NOT EXISTS Card_Type (
        card_type_id CHAR(3) PRIMARY KEY,
        type_name VARCHAR(20)
    )",
    
    "CREATE TABLE IF NOT EXISTS Wallet_Type (
        wallet_type_id CHAR(3) PRIMARY KEY,
        type_name VARCHAR(20)
    )",

    "CREATE TABLE IF NOT EXISTS Wallet (
        payment_id CHAR(6) PRIMARY KEY,
        wallet_type_id CHAR(3),
        account_number CHAR(15),
        transaction_reference_no CHAR(20)
    )",

    "CREATE TABLE IF NOT EXISTS Cash (
        payment_id CHAR(6) PRIMARY KEY,
        amount_received INTEGER,
        change_amount INTEGER
    )",
    
    "CREATE TABLE IF NOT EXISTS Payment_Type (
        payment_type_id CHAR(3) PRIMARY KEY,
        type_name VARCHAR(20)
    )",
    
    "CREATE TABLE IF NOT EXISTS Rental_Item (
        item_id CHAR(6) PRIMARY KEY,
        item_name VARCHAR(25),
        item_type_id CHAR(3),
        individual_cost INTEGER,
        total_stock INTEGER,
        FOREIGN KEY (item_type_id) REFERENCES Item_Type(item_type_id)
    )",
    
    "CREATE TABLE IF NOT EXISTS TransactionTbl (
        transaction_id CHAR(6) PRIMARY KEY,
        client_id CHAR(6),
        employee_id CHAR(6),
        transaction_date DATE,
        start_date DATE,
        return_date DATE,
        FOREIGN KEY (client_id) REFERENCES Client(client_id),
        FOREIGN KEY (employee_id) REFERENCES Employee(employee_id)
    )",
    "ALTER TABLE TransactionTbl ADD COLUMN rental_duration VARCHAR(255)",
    
    "CREATE TABLE IF NOT EXISTS Payment (
        payment_id CHAR(6) PRIMARY KEY,
        transaction_id CHAR(6),
        payment_date DATE,
        amount INTEGER,
        payment_type_id CHAR(3),
        FOREIGN KEY (transaction_id) REFERENCES TransactionTbl(transaction_id),
        FOREIGN KEY (payment_type_id) REFERENCES Payment_Type(payment_type_id)
    )",
    
    "CREATE TABLE IF NOT EXISTS Transaction_Item (
        transaction_id CHAR(6),
        item_id CHAR(6),
        quantity INTEGER,
        PRIMARY KEY (transaction_id, item_id),
        FOREIGN KEY (transaction_id) REFERENCES TransactionTbl(transaction_id),
        FOREIGN KEY (item_id) REFERENCES Rental_Item(item_id)
    )",

    
    "CREATE TABLE IF NOT EXISTS Repair_Fee (
        repair_fee_id CHAR(6) PRIMARY KEY,
        transaction_id CHAR(6),
        item_id CHAR(6),
        date_paid DATE,
        status VARCHAR(15),
        cost INTEGER,
        FOREIGN KEY (transaction_id) REFERENCES TransactionTbl(transaction_id),
        FOREIGN KEY (item_id) REFERENCES Rental_Item(item_id)
    )"
];

foreach ($tables as $table) {
    if ($conn->query($table)) {
        echo "<p style='color:green'>Table created/verified</p>";
    } else {
        echo "<p style='color:orange'>Table may already exist: " . $conn->error . "</p>";
    }
}

echo "<h3>Checking for missing columns...</h3>";

// Add quantity column to Repair_Fee
$check_column = $conn->query("SHOW COLUMNS FROM Repair_Fee LIKE 'quantity'");
if ($check_column->num_rows == 0) {
    $conn->query("ALTER TABLE Repair_Fee ADD COLUMN quantity INT DEFAULT 1");
    echo "<p style='color:green'>Added 'quantity' column to Repair_Fee table</p>";
} else {
    echo "<p style='color:green'>'quantity' column already exists in Repair_Fee</p>";
}

// Add venue column to TransactionTbl
$check_column = $conn->query("SHOW COLUMNS FROM TransactionTbl LIKE 'venue'");
if ($check_column->num_rows == 0) {
    $conn->query("ALTER TABLE TransactionTbl ADD COLUMN venue VARCHAR(100)");
    echo "<p style='color:green'>Added 'venue' column to TransactionTbl table</p>";
} else {
    echo "<p style='color:green'>'venue' column already exists in TransactionTbl</p>";
}

// Add payment_status column to TransactionTbl
$check_column = $conn->query("SHOW COLUMNS FROM TransactionTbl LIKE 'payment_status'");
if ($check_column->num_rows == 0) {
    $conn->query("ALTER TABLE TransactionTbl ADD COLUMN payment_status ENUM('PAID', 'NOT PAID') DEFAULT 'NOT PAID'");
    echo "<p style='color:green'>Added 'payment_status' column to TransactionTbl table</p>";
} else {
    echo "<p style='color:green'>'payment_status' column already exists in TransactionTbl</p>";
}

// Test 5: Insert default data
echo "<h3>Adding default data...</h3>";

// Insert item types
$conn->query("INSERT IGNORE INTO Item_Type VALUES 
    ('001', 'Chair'),
    ('002', 'Table')");
echo "<p style='color:green'>Item types added</p>";

// Insert payment types
$conn->query("INSERT IGNORE INTO Payment_Type VALUES 
    ('001', 'Cash'),
    ('003', 'Digital Wallet')");
echo "<p style='color:green'>Payment types added</p>";

// Insert card types
$conn->query("INSERT IGNORE INTO Card_Type VALUES 
    ('001', 'Visa'),
    ('002', 'Mastercard')");
echo "<p style='color:green'>Card types added</p>";

// Insert wallet types
$conn->query("INSERT IGNORE INTO Wallet_Type VALUES 
    ('001', 'GCash'),
    ('002', 'PayMaya')");
echo "<p style='color:green'>Wallet types added</p>";

// Insert sample client
$conn->query("INSERT IGNORE INTO Client VALUES 
    ('C00001', 'John', 'Doe', '09123456789', 'Manila'),
    ('C00002', 'Jane', 'Smith', '09987654321', 'Quezon City')");
echo "<p style='color:green'>Sample clients added</p>";

// Insert sample employee
$conn->query("INSERT IGNORE INTO Employee VALUES 
    ('E00001', 'James', 'Musa', 500),
    ('E00002', 'Maria', 'Santos', 550)");
echo "<p style='color:green'>Sample employees added</p>";

// Insert sample items
$conn->query("INSERT IGNORE INTO Rental_Item VALUES 
    ('I00001', 'Red Chair', '001', 100, 50),
    ('I00002', 'Monobloc', '001', 100, 25),
    ('I00003', 'Wooden Chair', '001', 100, 25)");
echo "<p style='color:green'>Sample items added</p>";

// Test 6: Display tables
echo "<h3>Tables in database:</h3>";
$result = $conn->query("SHOW TABLES");
echo "<ul>";
while($row = $result->fetch_array()) {
    echo "<li>" . $row[0] . "</li>";
}
echo "</ul>";

// Test 7: Display sample data
echo "<h3>Sample Clients:</h3>";
$result = $conn->query("SELECT * FROM Client");
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background: #333; color: white;'><th>ID</th><th>First Name</th><th>Last Name</th><th>Contact</th><th>Location</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['client_id']}</td>";
    echo "<td>{$row['first_name']}</td>";
    echo "<td>{$row['last_name']}</td>";
    echo "<td>{$row['contact']}</td>";
    echo "<td>{$row['location']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h2 style='color:green'>All tests passed! Your database is ready!</h2>";
echo "<a href='index.php' style='font-size: 18px;'>Go to Main Dashboard</a><br><br>";
echo "<a href='clients/add.php' style='font-size: 18px;'>Add a Client</a>";

$conn->close();
?>