<?php
// migrate_content.php
require_once 'db.php';

$content = [
    'hero_eyebrow' => 'Svobodné cechy',
    'hero_h1' => 'Učednictví, které je rychlé, reálné a zábavné.',
    'hero_lead' => 'Svobodné cechy propojují mistry a učedníky. Dáváme přednost učení praxí v reálných dílnách před teorií ve škole. Učedník od 12 do 18 let získává dovednosti, portfolio i sebevědomí.',
    'card_h2' => '3 kroky k mistrovi',
    'card_p' => 'Jasná cesta: najdi mistra, napiš mu, začni v dílně. Všechno je postavené na praxi.',
    'step1_num' => '01',
    'step1_label' => 'Vyber si mistra',
    'step2_num' => '02',
    'step2_label' => 'Napiš zprávu',
    'step3_num' => '03',
    'step3_label' => 'Začni v dílně',
    'steps_h2' => 'Proč to funguje',
    'step_col1_label' => 'Učedník',
    'step_col1_text' => 'Rychlé učení, portfolio, reálné výsledky a jasná cesta k práci.',
    'step_col2_label' => 'Mistr',
    'step_col2_text' => 'Vychovává nástupce, rozvíjí dílnu a předává know‑how.',
    'step_col3_label' => 'Komunita',
    'step_col3_text' => 'Získává nové řemeslníky, kteří umí a chtějí tvořit.',
    'cta_h2' => 'Jsi připraven?',
    'cta_text' => 'Nejrychlejší cesta je začít u mistra. Vyber si ho ještě dnes.'
];

try {
    echo "🚀 Startuji migraci obsahu...<br>";

    $stmt = $pdo->prepare("INSERT INTO site_content (key_name, content_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)");

    foreach ($content as $key => $val) {
        $stmt->execute([$key, $val]);
        echo "✅ Klíč '$key' byl migrován.<br>";
    }

    echo "<br><strong>Hotovo! Obsah byl přesunut do databáze.</strong><br>";
    echo "Nyní můžete smazat tento soubor a index.html vyčistit.";

} catch (Exception $e) {
    echo "❌ Chyba: " . $e->getMessage();
}
?>