<?php
// Error checking script for all management pages
require 'db.php';

echo "<h2>Database Structure Check</h2>";

$tables = [
    'RUNNER' => ['runner_id', 'first_name', 'last_name', 'date_of_birth', 'gender', 'citizen_id', 'phone', 'email', 'address', 'is_disabled', 'created_at'],
    'RACE_CATEGORY' => ['category_id', 'name', 'distance_km', 'start_time', 'time_limit', 'giveaway_type', 'created_at'],
    'AGE_GROUP' => ['age_group_id', 'category_id', 'gender', 'min_age', 'max_age', 'label', 'created_at'],
    'PRICE_RATE' => ['price_id', 'category_id', 'runner_type', 'amount', 'created_at'],
    'SHIPPING_OPTION' => ['shipping_id', 'type', 'cost', 'detail', 'created_at'],
    'REGISTRATION' => ['reg_id', 'runner_id', 'category_id', 'price_id', 'shipping_id', 'reg_date', 'shirt_size', 'status', 'bib_number', 'created_at'],
    'PAYMENT' => ['payment_id', 'reg_id', 'total_amount', 'payment_time', 'payment_method', 'status', 'transaction_ref', 'created_at']
];

foreach ($tables as $tableName => $expectedColumns) {
    echo "<h3>Checking table: $tableName</h3>";
    
    try {
        // Check if table exists
        $stmt = $pdo->query("SHOW TABLES LIKE '$tableName'");
        if ($stmt->rowCount() == 0) {
            echo "<p style='color: red;'>❌ Table $tableName does not exist!</p>";
            continue;
        }
        
        // Get actual columns
        $stmt = $pdo->query("SHOW COLUMNS FROM $tableName");
        $actualColumns = [];
        while ($row = $stmt->fetch()) {
            $actualColumns[] = $row['Field'];
        }
        
        // Check each expected column
        foreach ($expectedColumns as $column) {
            if (in_array($column, $actualColumns)) {
                echo "<p style='color: green;'>✓ Column $column exists</p>";
            } else {
                echo "<p style='color: red;'>❌ Column $column is missing!</p>";
            }
        }
        
        // Test a simple query
        $testQuery = "SELECT * FROM $tableName LIMIT 1";
        $stmt = $pdo->query($testQuery);
        $row = $stmt->fetch();
        
        if ($row) {
            echo "<p style='color: blue;'>ℹ Sample data exists in $tableName</p>";
        } else {
            echo "<p style='color: orange;'>⚠ No data in $tableName</p>";
        }
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error checking $tableName: " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
}

echo "<h3>Recommendations:</h3>";
echo "<ul>";
echo "<li>If any columns are missing, run <a href='fix_database.php'>fix_database.php</a></li>";
echo "<li>If tables are missing, import <a href='create_database.sql'>create_database.sql</a></li>";
echo "<li>Check your database connection in db.php</li>";
echo "</ul>";

echo "<p><a href='admin_index.php'>Back to Admin Panel</a></p>";
?>