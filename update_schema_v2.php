<?php
// update_schema_v2.php
require_once 'db.php';

try {
    echo "🚀 Startuji aktualizaci databáze v2...<br>";

    // Tabulka pro obory (řemesla)
    $pdo->exec("CREATE TABLE IF NOT EXISTS crafts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL UNIQUE
    )");

    // Tabulka pro města
    $pdo->exec("CREATE TABLE IF NOT EXISTS cities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL UNIQUE
    )");

    // Tabulka pro žádosti o hledání mistra
    $pdo->exec("CREATE TABLE IF NOT EXISTS master_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        email VARCHAR(255),
        crafts JSON,
        cities JSON,
        max_distance INT,
        note TEXT,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Seed crafts if empty
    $count = $pdo->query("SELECT COUNT(*) FROM crafts")->fetchColumn();
    if ($count == 0) {
        $initialCrafts = [
            'Truhlář', 'Kovář', 'Elektrikář', 'Instalatér', 'Zedník', 
            'Švadlena', 'Keramik', 'Zahradník', 'Kuchař', 'Cukrář', 
            'IT specialista', 'Grafik', 'Malíř', 'Restaurátor', 'Vazač knih'
        ];
        $stmt = $pdo->prepare("INSERT IGNORE INTO crafts (name) VALUES (?)");
        foreach ($initialCrafts as $c) {
            $stmt->execute([$c]);
        }
    }

    // Seed cities if empty
    $count = $pdo->query("SELECT COUNT(*) FROM cities")->fetchColumn();
    if ($count == 0) {
        $initialCities = [
            'Praha', 'Brno', 'Ostrava', 'Plzeň', 'Liberec', 'Olomouc', 
            'České Budějovice', 'Hradec Králové', 'Ústí nad Labem', 
            'Pardubice', 'Zlín', 'Havířov', 'Kladno', 'Most', 'Opava'
        ];
        $stmt = $pdo->prepare("INSERT IGNORE INTO cities (name) VALUES (?)");
        foreach ($initialCities as $c) {
            $stmt->execute([$c]);
        }
    }

    echo "✅ Hotovo! Tabulky pro obory, města a žádosti byly vytvořeny.<br>";

} catch (Exception $e) {
    echo "❌ Chyba při aktualizaci: " . $e->getMessage();
}
?>
