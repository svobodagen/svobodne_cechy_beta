<?php
// migrate_landing_analytics.php
require_once __DIR__ . '/db.php';

echo "<h1>🚀 Spouštím migraci databáze pro Analytiku Landing Pages...</h1>";

try {
    // 1. landing_leads columns
    $pdo->exec("CREATE TABLE IF NOT EXISTS landing_leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        landing_slug VARCHAR(100) NOT NULL,
        master_name VARCHAR(150) DEFAULT NULL,
        email VARCHAR(255) NOT NULL,
        name VARCHAR(150) DEFAULT NULL,
        phone VARCHAR(50) DEFAULT NULL,
        user_role VARCHAR(50) DEFAULT NULL,
        message TEXT DEFAULT NULL,
        newsletter TINYINT(1) DEFAULT 0,
        status VARCHAR(50) DEFAULT 'novy',
        session_id VARCHAR(64) DEFAULT NULL,
        source VARCHAR(100) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (landing_slug),
        INDEX (email),
        INDEX (session_id),
        INDEX (source)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $alters = [
        "ALTER TABLE landing_leads ADD COLUMN newsletter TINYINT(1) DEFAULT 0",
        "ALTER TABLE landing_leads ADD COLUMN session_id VARCHAR(64) DEFAULT NULL",
        "ALTER TABLE landing_leads ADD COLUMN source VARCHAR(100) DEFAULT NULL"
    ];
    foreach ($alters as $sql) {
        try { 
            $pdo->exec($sql); 
            echo "<p style='color:green;'>✓ Sloupec byl přidán do landing_leads</p>";
        } catch(\PDOException $e) {
            echo "<p style='color:gray;'>ℹ️ Sloupec v landing_leads již existuje</p>";
        }
    }

    // 2. landing_sessions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS landing_sessions (
        session_id VARCHAR(64) PRIMARY KEY,
        landing_slug VARCHAR(100) NOT NULL,
        source VARCHAR(100) DEFAULT 'direct',
        referrer VARCHAR(255) DEFAULT NULL,
        utm_source VARCHAR(100) DEFAULT NULL,
        utm_medium VARCHAR(100) DEFAULT NULL,
        utm_campaign VARCHAR(100) DEFAULT NULL,
        device_type VARCHAR(20) DEFAULT 'desktop',
        max_section VARCHAR(100) DEFAULT 'hero',
        clicked_button VARCHAR(255) DEFAULT NULL,
        clicked_section VARCHAR(100) DEFAULT NULL,
        form_status VARCHAR(50) DEFAULT 'none',
        lead_id INT DEFAULT NULL,
        duration_seconds INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (landing_slug),
        INDEX (source),
        INDEX (created_at),
        INDEX (lead_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p style='color:green;'>✓ Tabulka landing_sessions je připravena</p>";

    // 3. landing_events table
    $pdo->exec("CREATE TABLE IF NOT EXISTS landing_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id VARCHAR(64) NOT NULL,
        landing_slug VARCHAR(100) NOT NULL,
        event_type VARCHAR(50) NOT NULL,
        event_label VARCHAR(255) DEFAULT NULL,
        event_section VARCHAR(100) DEFAULT NULL,
        event_data TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (session_id),
        INDEX (landing_slug),
        INDEX (event_type),
        INDEX (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p style='color:green;'>✓ Tabulka landing_events je připravena</p>";

    // 4. landing_links table
    $pdo->exec("CREATE TABLE IF NOT EXISTS landing_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        landing_slug VARCHAR(100) NOT NULL,
        source_tag VARCHAR(100) NOT NULL,
        label VARCHAR(255) NOT NULL,
        channel VARCHAR(50) DEFAULT 'other',
        full_url TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_slug_source (landing_slug, source_tag),
        INDEX (landing_slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p style='color:green;'>✓ Tabulka landing_links je připravena</p>";

    echo "<h2 style='color:green;'>🎉 Všechny tabulky a sloupce byly úspěšně zkontrolovány a nastaveny!</h2>";
    echo "<p><a href='admin/landing_stats.php'>Přejít do Analytiky a Generátoru odkazů →</a></p>";
} catch (\PDOException $e) {
    echo "<h2 style='color:red;'>❌ Chyba při migraci: " . htmlspecialchars($e->getMessage()) . "</h2>";
}
