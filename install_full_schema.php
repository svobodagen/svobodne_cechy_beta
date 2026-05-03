<?php
// install_full_schema.php
require_once 'db.php';

try {
    echo "🚀 Startuji kompletní instalaci databáze...<br>";

    $sql = file_get_contents('schema.sql');
    
    // Rozdělení SQL na jednotlivé příkazy (podle středníku)
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }

    echo "✅ Úspěch! Všechny tabulky byly vytvořeny (users, masters, messages, media, requests, atd.).<br>";
    echo "Nyní by měl web beta.svobodnecechy.cz fungovat správně.<br>";
    echo "<strong>Tento soubor po spuštění smažte.</strong>";

} catch (Exception $e) {
    echo "❌ Chyba při instalaci: " . $e->getMessage();
}
?>
