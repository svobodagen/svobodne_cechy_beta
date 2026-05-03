<?php
// migrate_from_live.php
require_once 'db.php';

function fetchData($action) {
    $url = "https://www.bezskoly.cz/api.php?action=" . $action;
    $json = file_get_contents($url);
    return json_decode($json, true);
}

try {
    echo "🚀 Startuji migraci dat z live webu...<br>";

    // 1. MISTŘI
    $masters = fetchData('load_masters');
    if ($masters) {
        $pdo->exec("DELETE FROM masters");
        $stmt = $pdo->prepare("INSERT INTO masters (id, name, craft, location, rank, aura, description, stats, tags, badges, gallery, photo, audio, photoSettings, socials, education, accommodation, compensation, recommendations, requirements) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($masters as $m) {
            $stmt->execute([
                $m['id'], $m['name'], $m['craft'] ?? '', $m['location'] ?? '', $m['rank'] ?? '', $m['aura'] ?? '',
                $m['description'] ?? $m['desc'] ?? '',
                json_encode($m['stats'] ?? []),
                json_encode($m['tags'] ?? []),
                json_encode($m['badges'] ?? []),
                is_string($m['gallery'] ?? '') ? $m['gallery'] : json_encode($m['gallery'] ?? []),
                $m['photo'] ?? null, $m['audio'] ?? null,
                json_encode($m['photoSettings'] ?? []),
                json_encode($m['socials'] ?? []),
                $m['education'] ?? null, $m['accommodation'] ?? null, $m['compensation'] ?? null, $m['recommendations'] ?? null, $m['requirements'] ?? null
            ]);
        }
        echo "✅ " . count($masters) . " mistrů přeneseno.<br>";
    }

    // 2. UŽIVATELÉ
    $users = fetchData('load_users');
    if ($users) {
        $pdo->exec("DELETE FROM users");
        $stmt = $pdo->prepare("INSERT INTO users (email, role, password, tempPassword, name, phone, socials, createdAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($users as $u) {
            $stmt->execute([
                $u['email'], $u['role'], $u['password'] ?? null, $u['tempPassword'] ?? null,
                $u['name'] ?? '', $u['phone'] ?? '',
                json_encode($u['socials'] ?? []),
                $u['createdAt'] ?? date('Y-m-d H:i:s')
            ]);
        }
        echo "✅ " . count($users) . " uživatelů přeneseno.<br>";
    }

    // 3. ZPRÁVY
    $messages = fetchData('load_messages');
    if ($messages) {
        $pdo->exec("DELETE FROM messages");
        $stmt = $pdo->prepare("INSERT INTO messages (id, toMaster, fromEmail, text, createdAt, deletedByAdmin, deletedByUser, userName, userPhone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($messages as $msg) {
            $stmt->execute([
                $msg['id'], $msg['toMaster'], $msg['fromEmail'] ?? $msg['from'] ?? null,
                $msg['text'], $msg['createdAt'],
                (int)($msg['deletedByAdmin'] ?? 0), (int)($msg['deletedByUser'] ?? 0),
                $msg['userName'] ?? '', $msg['userPhone'] ?? ''
            ]);
        }
        echo "✅ " . count($messages) . " zpráv přeneseno.<br>";
    }

    echo "<br><strong>Hotovo! Data byla úspěšně zkopírována z bezskoly.cz do beta.svobodnecechy.cz.</strong>";

} catch (Exception $e) {
    echo "❌ Chyba: " . $e->getMessage();
}
?>
