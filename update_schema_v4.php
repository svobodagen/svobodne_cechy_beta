<?php
require_once 'db.php';

try {
    $pdo->exec("ALTER TABLE masters ADD COLUMN IF NOT EXISTS requirements LONGTEXT");
    echo "Databáze úspěšně aktualizována o pole Požadavky.";
} catch (PDOException $e) {
    echo "Chyba při aktualizaci databáze: " . $e->getMessage();
}
