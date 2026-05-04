<?php
require_once 'db.php';
try {
    $pdo->exec("ALTER TABLE masters ADD COLUMN reference_sc TEXT AFTER requirements");
    echo "Sloupec 'reference_sc' byl úspěšně přidán do databáze.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Sloupec 'reference_sc' již v databázi existuje.";
    } else {
        echo "Chyba při aktualizaci databáze: " . $e->getMessage();
    }
}
?>
