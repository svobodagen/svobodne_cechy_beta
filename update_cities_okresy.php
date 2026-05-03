<?php
// update_cities_okresy.php
require_once 'db.php';

try {
    echo "🚀 Startuji aktualizaci měst a okresů...<br>";

    // 1. Vytvoření tabulky okresů
    $pdo->exec("CREATE TABLE IF NOT EXISTS districts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL UNIQUE
    )");

    // 2. Modifikace tabulky měst (přidání district_id)
    // Nejdřív zjistíme, jestli sloupec district_id už existuje
    $cols = $pdo->query("DESCRIBE cities")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('district_id', $cols)) {
        $pdo->exec("ALTER TABLE cities ADD COLUMN district_id INT AFTER name");
        $pdo->exec("ALTER TABLE cities ADD FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE SET NULL");
    }

    // 3. Načtení dat z CSV
    $csvFile = 'mesta_okresy.csv';
    if (!file_exists($csvFile)) {
        throw new Exception("Soubor $csvFile nebyl nalezen.");
    }

    $handle = fopen($csvFile, "r");
    if ($handle === FALSE) {
        throw new Exception("Nelze otevřít soubor $csvFile.");
    }

    // Přeskočit hlavičku
    fgetcsv($handle, 1000, ";");

    $pdo->beginTransaction();

    // Vyčistit stará data pro jistotu (nepovinné, ale pro čistý import)
    // $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    // $pdo->exec("TRUNCATE TABLE cities;");
    // $pdo->exec("TRUNCATE TABLE districts;");
    // $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    $count = 0;
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        if (count($data) < 3)
            continue;

        $cityName = trim($data[1]);
        $districtName = trim($data[2]);

        // Uložení/Získání ID okresu
        $stmt = $pdo->prepare("INSERT IGNORE INTO districts (name) VALUES (?)");
        $stmt->execute([$districtName]);

        $stmt = $pdo->prepare("SELECT id FROM districts WHERE name = ?");
        $stmt->execute([$districtName]);
        $districtId = $stmt->fetchColumn();

        // Uložení města s vazbou na okres
        // Použijeme INSERT ... ON DUPLICATE KEY UPDATE pro aktualizaci existujících měst
        $stmt = $pdo->prepare("INSERT INTO cities (name, district_id) VALUES (?, ?) 
                               ON DUPLICATE KEY UPDATE district_id = VALUES(district_id)");
        $stmt->execute([$cityName, $districtId]);

        $count++;
    }

    fclose($handle);
    $pdo->commit();

    echo "✅ Hotovo! Importováno/aktualizováno $count měst a jejich příslušných okresů.<br>";

} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo "❌ Chyba: " . $e->getMessage();
}
?>