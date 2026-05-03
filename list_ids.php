<?php
require_once 'db.php';
try {
    $stmt = $pdo->query("SELECT id, name FROM masters");
    $masters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($masters, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
