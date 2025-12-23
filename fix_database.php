<?php
require 'db.php';

echo "<h2>Database Fix Script</h2>";
echo "<p>Adding missing created_at columns...</p>";

$tables = [
    'PRICE_RATE',
    'SHIPPING_OPTION', 
    'AGE_GROUP',
    'RUNNER',
    'RACE_CATEGORY',
    'REGISTRATION',
    'PAYMENT'
];

try {
    foreach ($tables as $table) {
        // Check if created_at column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM $table LIKE 'created_at'");
        $columnExists = $stmt->rowCount() > 0;
        
        if (!$columnExists) {
            // Add created_at column
            $pdo->exec("ALTER TABLE $table ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
            echo "<p style='color: green;'>✓ Added created_at column to $table</p>";
        } else {
            echo "<p style='color: blue;'>ℹ created_at column already exists in $table</p>";
        }
        
        // Update NULL values
        $pdo->exec("UPDATE $table SET created_at = CURRENT_TIMESTAMP WHERE created_at IS NULL");
    }
    
    echo "<h3 style='color: green;'>Database update completed successfully!</h3>";
    echo "<p><a href='admin_index.php'>Go back to Admin Panel</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>