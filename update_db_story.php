<?php
require_once 'db.php';
try {
    $pdo->exec("ALTER TABLE masters ADD COLUMN story TEXT AFTER description");
    echo "Sloupec 'story' byl úspěšně přidán do databáze.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Sloupec 'story' již v databázi existuje.";
    } else {
        echo "Chyba při aktualizaci databáze: " . $e->getMessage();
    }
}
?>
