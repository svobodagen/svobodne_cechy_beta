<?php
// Migration script to add 'age' column to master_requests table
require_once 'db.php';

try {
    // Check if age column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM master_requests LIKE 'age'");
    $columnExists = $stmt->rowCount() > 0;

    if (!$columnExists) {
        // Add age column after email
        $pdo->exec("ALTER TABLE master_requests ADD COLUMN age INT AFTER email");
        echo "✓ Column 'age' successfully added to master_requests table.<br>";
    } else {
        echo "✓ Column 'age' already exists in master_requests table.<br>";
    }

    echo "<br>Migration completed successfully!<br>";
    echo "<strong>IMPORTANT:</strong> Please delete this file (add_age_to_requests.php) after running it.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>