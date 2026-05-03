<?php
// update_schema.php
// Spusťte tento soubor v prohlížeči pro navýšení kapacity databáze pro fotky a audio.

require_once 'db.php';

try {
    echo "🚀 Startuji aktualizaci databáze...<br>";

    // Změna typů sloupců na LONGTEXT (kapacita až 4GB)
    $pdo->exec("ALTER TABLE masters 
                MODIFY COLUMN description LONGTEXT,
                MODIFY COLUMN gallery LONGTEXT,
                MODIFY COLUMN photo LONGTEXT,
                MODIFY COLUMN audio LONGTEXT");

    // Přidání sloupce socials pokud neexistuje
    try {
        $pdo->exec("ALTER TABLE masters ADD COLUMN socials JSON AFTER photoSettings");
    } catch (Exception $e) {
        // Pravděpodobně už existuje
    }

    // Vytvoření tabulek pro media knihovnu
    $pdo->exec("CREATE TABLE IF NOT EXISTS media_folders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        parentId INT DEFAULT NULL,
        FOREIGN KEY (parentId) REFERENCES media_folders(id) ON DELETE SET NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS media_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        folderId INT DEFAULT NULL,
        name VARCHAR(255) NOT NULL,
        type ENUM('image', 'video', 'audio') NOT NULL,
        path LONGTEXT NOT NULL,
        thumbnail LONGTEXT,
        size INT,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (folderId) REFERENCES media_folders(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS site_content (
        key_name VARCHAR(255) PRIMARY KEY,
        content_value TEXT,
        updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    echo "✅ Hotovo! Sloupce byly rozšířeny a tabulky pro média a obsah vytvořeny.<br>";
    echo "Nyní by mělo ukládání velkých fotek a audia fungovat správně.<br>";
    echo "<strong>Tento soubor můžete smazat.</strong>";

} catch (Exception $e) {
    echo "❌ Chyba při aktualizaci: " . $e->getMessage();
}
?>