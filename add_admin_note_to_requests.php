<?php
// Migration script to add 'admin_note' column to master_requests table
require_once 'db.php';

try {
    // Check if admin_note column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM master_requests LIKE 'admin_note'");
    $columnExists = $stmt->rowCount() > 0;

    if (!$columnExists) {
        // Add admin_note column after note
        $pdo->exec("ALTER TABLE master_requests ADD COLUMN admin_note TEXT AFTER note");
        echo "✓ Column 'admin_note' successfully added to master_requests table.<br>";
    } else {
        echo "✓ Column 'admin_note' already exists in master_requests table.<br>";
    }

    echo "<br>Migration completed successfully!<br>";
    echo "<strong>IMPORTANT:</strong> Please delete this file (add_admin_note_to_requests.php) after running it.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>