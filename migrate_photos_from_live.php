<?php
// migrate_photos_from_live.php
require_once 'db.php';

function downloadFile($path) {
    if (!$path || strpos($path, 'http') === 0) return;

    $remoteUrl = "https://www.bezskoly.cz/" . $path;
    $localPath = __DIR__ . "/" . $path;
    $dir = dirname($localPath);

    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    if (!file_exists($localPath)) {
        $content = @file_get_contents($remoteUrl);
        if ($content) {
            file_put_contents($localPath, $content);
            echo "✅ Staženo: $path<br>";
        } else {
            echo "❌ Selhalo: $path (nenalezeno na live webu)<br>";
        }
    } else {
        echo "⏭️ Přeskočeno: $path (již existuje)<br>";
    }
}

try {
    echo "🚀 Startuji stahování fotek a galerií...<br>";

    $stmt = $pdo->query("SELECT photo, gallery FROM masters");
    $masters = $stmt->fetchAll();

    foreach ($masters as $m) {
        // Hlavní fotka
        if ($m['photo']) {
            downloadFile($m['photo']);
        }

        // Galerie
        if ($m['gallery']) {
            $gallery = json_decode($m['gallery'], true);
            if (is_array($gallery)) {
                foreach ($gallery as $item) {
                    if (isset($item['path'])) {
                        downloadFile($item['path']);
                    }
                }
            }
        }
    }

    echo "<br><strong>Hotovo! Všechny dostupné fotky byly přeneseny.</strong>";

} catch (Exception $e) {
    echo "❌ Chyba: " . $e->getMessage();
}
?>
