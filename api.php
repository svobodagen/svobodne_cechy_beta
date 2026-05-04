<?php
// api.php
header('Content-Type: application/json');
require_once 'db.php';

$action = $_GET['action'] ?? '';
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);
if ($rawData && $data === null && json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Chyba v JSON datech: ' . json_last_error_msg() . ' (Data len: ' . strlen($rawData) . ')']);
    exit;
}


function save_base64_as_file($base64, $subfolder = 'masters/')
{
    if (!$base64 || !is_string($base64))
        return $base64;
    // If it's already a path, don't re-save
    if (strpos($base64, 'uploads/') === 0)
        return $base64;
    // If it's not a data URI, ignore
    if (strpos($base64, 'data:') !== 0)
        return $base64;

    try {
        if (!strpos($base64, ';'))
            return $base64;
        list($type, $data) = explode(';', $base64);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);

        // Ensure folder exists
        $uploadDir = 'uploads/' . $subfolder;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Determine extension
        $ext = 'jpg';
        if (strpos($type, 'png') !== false)
            $ext = 'png';
        if (strpos($type, 'gif') !== false)
            $ext = 'gif';
        if (strpos($type, 'webp') !== false)
            $ext = 'webp';
        if (strpos($type, 'mp4') !== false)
            $ext = 'mp4';
        if (strpos($type, 'mov') !== false)
            $ext = 'mov';
        if (strpos($type, 'avi') !== false)
            $ext = 'avi';
        if (strpos($type, 'mpeg') !== false)
            $ext = 'mpeg';
        if (strpos($type, 'mp3') !== false)
            $ext = 'mp3';
        if (strpos($type, 'wav') !== false)
            $ext = 'wav';
        if (strpos($type, 'ogg') !== false)
            $ext = 'ogg';

        $filename = 'file_' . uniqid() . '.' . $ext;
        $filepath = $uploadDir . $filename;

        file_put_contents($filepath, $data);
        return $filepath;
    } catch (Exception $e) {
        return $base64; // Fallback to original if something fails
    }
}

try {
    switch ($action) {
        case 'load_masters':
            $isLight = isset($_GET['light']) && $_GET['light'] === '1';
            $fields = $isLight ? "id, name, craft, location, rank, aura, photo, photoSettings, badges" : "*";
            $stmt = $pdo->query("SELECT $fields FROM masters");
            $masters = $stmt->fetchAll();
            foreach ($masters as &$m) {
                if (isset($m['stats']))
                    $m['stats'] = json_decode($m['stats'], true);
                if (isset($m['tags']))
                    $m['tags'] = json_decode($m['tags'], true);
                if (isset($m['badges']))
                    $m['badges'] = json_decode($m['badges'], true);
                if (isset($m['gallery']))
                    $m['gallery'] = json_decode($m['gallery'], true);
                if (isset($m['photoSettings']))
                    $m['photoSettings'] = json_decode($m['photoSettings'], true);
                if (isset($m['socials']))
                    $m['socials'] = json_decode($m['socials'], true);
                $m['desc'] = $m['description'] ?? '';
            }
            echo json_encode($masters);
            break;

        case 'load_master':
            $id = $_GET['id'] ?? '';
            $stmt = $pdo->prepare("SELECT * FROM masters WHERE id = ?");
            $stmt->execute([$id]);
            $m = $stmt->fetch();
            if ($m) {
                $m['stats'] = json_decode($m['stats'], true);
                $m['tags'] = json_decode($m['tags'], true);
                $m['badges'] = json_decode($m['badges'], true);
                $m['gallery'] = json_decode($m['gallery'], true);
                $m['photoSettings'] = json_decode($m['photoSettings'], true);
                $m['socials'] = json_decode($m['socials'], true);
                $m['desc'] = $m['description'] ?? '';
                echo json_encode($m);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Master not found']);
            }
            break;

        case 'save_master':
            try {
                $m = $data;

                // Process photo
                if (isset($m['photo'])) {
                    $m['photo'] = save_base64_as_file($m['photo'], 'masters/');
                }

                // Process gallery
                if (isset($m['gallery']) && is_array($m['gallery'])) {
                    foreach ($m['gallery'] as &$item) {
                        if (isset($item['path'])) {
                            $item['path'] = save_base64_as_file($item['path'], 'gallery/');
                        }
                    }
                }

                $stmt = $pdo->prepare("REPLACE INTO masters (id, name, craft, location, rank, aura, description, stats, tags, badges, gallery, photo, audio, photoSettings, socials, education, accommodation, compensation, recommendations, requirements, reference_sc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $m['id'],
                    $m['name'],
                    $m['craft'] ?? '',
                    $m['location'] ?? '',
                    $m['rank'] ?? '',
                    $m['aura'] ?? '',
                    $m['desc'] ?? '',
                    json_encode($m['stats'] ?? []),
                    json_encode($m['tags'] ?? []),
                    json_encode($m['badges'] ?? []),
                    is_string($m['gallery']) ? $m['gallery'] : json_encode($m['gallery'] ?? []),
                    $m['photo'] ?? null,
                    $m['audio'] ?? null,
                    json_encode($m['photoSettings'] ?? []),
                    json_encode($m['socials'] ?? []),
                    $m['education'] ?? null,
                    $m['accommodation'] ?? null,
                    $m['compensation'] ?? null,
                    $m['recommendations'] ?? null,
                    $m['requirements'] ?? null,
                    $m['reference_sc'] ?? null
                ]);
                echo json_encode(['status' => 'success', 'photo' => $m['photo']]);
            } catch (PDOException $pe) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'error' => 'DB Chyba: ' . $pe->getMessage()]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'error' => 'Chyba: ' . $e->getMessage()]);
            }
            break;

        case 'load_users':
            $stmt = $pdo->query("SELECT * FROM users");
            $users = $stmt->fetchAll();
            foreach ($users as &$u) {
                $u['socials'] = json_decode($u['socials'], true);
            }
            echo json_encode($users);
            break;

        case 'save_users':
            $pdo->beginTransaction();
            $pdo->exec("DELETE FROM users");
            $stmt = $pdo->prepare("INSERT INTO users (email, role, password, tempPassword, name, phone, socials, createdAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($data as $u) {
                $stmt->execute([
                    $u['email'],
                    $u['role'],
                    $u['password'],
                    $u['tempPassword'],
                    $u['name'] ?? '',
                    $u['phone'] ?? '',
                    json_encode($u['socials'] ?? []),
                    $u['createdAt'] ?? date('Y-m-d H:i:s')
                ]);
            }
            $pdo->commit();
            echo json_encode(['status' => 'success']);
            break;

        case 'load_messages':
            $stmt = $pdo->query("SELECT * FROM messages");
            $messages = $stmt->fetchAll();
            foreach ($messages as &$msg) {
                $msg['deletedByAdmin'] = (bool) $msg['deletedByAdmin'];
                $msg['deletedByUser'] = (bool) $msg['deletedByUser'];
                // Adapt to app.js field names if different
                $msg['from'] = $msg['fromEmail'];
            }
            echo json_encode($messages);
            break;

        case 'save_messages':
            $pdo->beginTransaction();
            $pdo->exec("DELETE FROM messages");
            $stmt = $pdo->prepare("INSERT INTO messages (id, toMaster, fromEmail, text, createdAt, deletedByAdmin, deletedByUser, userName, userPhone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($data as $msg) {
                $stmt->execute([
                    $msg['id'],
                    $msg['toMaster'],
                    $msg['from'],
                    $msg['text'],
                    $msg['createdAt'],
                    (int) $msg['deletedByAdmin'],
                    (int) $msg['deletedByUser'],
                    $msg['userName'] ?? '',
                    $msg['userPhone'] ?? ''
                ]);
            }
            $pdo->commit();
            echo json_encode(['status' => 'success']);
            break;

        case 'load_media':
            $folders = $pdo->query("SELECT * FROM media_folders")->fetchAll();
            $items = $pdo->query("SELECT * FROM media_items")->fetchAll();
            echo json_encode(['folders' => $folders, 'items' => $items]);
            break;

        case 'save_folder':
            if (isset($data['id'])) {
                $stmt = $pdo->prepare("UPDATE media_folders SET name = ?, parentId = ? WHERE id = ?");
                $stmt->execute([$data['name'], $data['parentId'] ?? null, $data['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO media_folders (name, parentId) VALUES (?, ?)");
                $stmt->execute([$data['name'], $data['parentId'] ?? null]);
            }
            echo json_encode(['status' => 'success', 'id' => $pdo->lastInsertId()]);
            break;

        case 'delete_folder':
            $stmt = $pdo->prepare("DELETE FROM media_folders WHERE id = ?");
            $stmt->execute([$data['id']]);
            echo json_encode(['status' => 'success']);
            break;

        case 'save_media_item':
            if (isset($data['id'])) {
                $stmt = $pdo->prepare("UPDATE media_items SET folderId = ?, name = ?, type = ?, path = ?, thumbnail = ?, size = ? WHERE id = ?");
                $stmt->execute([
                    $data['folderId'] ?? null,
                    $data['name'],
                    $data['type'],
                    $data['path'],
                    $data['thumbnail'] ?? null,
                    $data['size'] ?? 0,
                    $data['id']
                ]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO media_items (folderId, name, type, path, thumbnail, size) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['folderId'] ?? null,
                    $data['name'],
                    $data['type'],
                    $data['path'],
                    $data['thumbnail'] ?? null,
                    $data['size'] ?? 0
                ]);
            }
            echo json_encode(['status' => 'success', 'id' => $pdo->lastInsertId()]);
            break;

        case 'delete_media_item':
            $stmt = $pdo->prepare("DELETE FROM media_items WHERE id = ?");
            $stmt->execute([$data['id']]);
            echo json_encode(['status' => 'success']);
            break;

        case 'load_site_content':
            $stmt = $pdo->query("SELECT key_name, content_value FROM site_content");
            $content = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            echo json_encode($content);
            break;

        case 'save_site_content':
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO site_content (key_name, content_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)");
            foreach ($data as $key => $val) {
                $stmt->execute([$key, $val]);
            }
            $pdo->commit();
            echo json_encode(['status' => 'success']);
            break;

        case 'load_crafts':
            $stmt = $pdo->query("SELECT * FROM crafts ORDER BY name");
            echo json_encode($stmt->fetchAll());
            break;

        case 'save_craft':
            if (isset($data['name'])) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO crafts (name) VALUES (?)");
                $stmt->execute([$data['name']]);
                echo json_encode(['status' => 'success', 'id' => $pdo->lastInsertId()]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Missing craft name']);
            }
            break;

        case 'load_cities':
            $stmt = $pdo->query("SELECT c.*, d.name as district_name FROM cities c LEFT JOIN districts d ON c.district_id = d.id ORDER BY c.name");
            echo json_encode($stmt->fetchAll());
            break;

        case 'save_master_request':
            $stmt = $pdo->prepare("INSERT INTO master_requests (name, phone, email, age, crafts, cities, max_distance, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['name'] ?? '',
                $data['phone'] ?? '',
                $data['email'] ?? '',
                isset($data['age']) && $data['age'] !== '' ? (int) $data['age'] : null,
                json_encode($data['crafts'] ?? []),
                json_encode($data['cities'] ?? []),
                (int) ($data['max_distance'] ?? 0),
                $data['note'] ?? ''
            ]);
            echo json_encode(['status' => 'success', 'id' => $pdo->lastInsertId()]);
            break;

        case 'load_master_requests':
            // Simple check if user is admin (this app seems to rely on session in frontend, but let's be safe if possible)
            // For now, following the pattern of the rest of the file which doesn't have much auth check inside api.php
            $stmt = $pdo->query("SELECT * FROM master_requests ORDER BY createdAt DESC");
            $requests = $stmt->fetchAll();
            foreach ($requests as &$r) {
                $r['crafts'] = json_decode($r['crafts'], true);
                $r['cities'] = json_decode($r['cities'], true);
            }
            echo json_encode($requests);
            break;

        case 'delete_master_request':
            if (isset($data['id'])) {
                $stmt = $pdo->prepare("DELETE FROM master_requests WHERE id = ?");
                $stmt->execute([$data['id']]);
                echo json_encode(['status' => 'success']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Missing request ID']);
            }
            break;

        case 'update_request_admin_note':
            if (isset($data['id'])) {
                $stmt = $pdo->prepare("UPDATE master_requests SET admin_note = ? WHERE id = ?");
                $stmt->execute([$data['admin_note'] ?? '', $data['id']]);
                echo json_encode(['status' => 'success']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Missing request ID']);
            }
            break;

        case 'register_user':
            $email = trim($data['email'] ?? '');
            $password = $data['password'] ?? '';
            $isManual = !empty($data['manual']); // Check if manual registration

            if (!$email || !$password) {
                http_response_code(400);
                echo json_encode(['error' => 'Chybí email nebo heslo']);
                break;
            }

            // Check existence
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $existing = $stmt->fetch();

            if ($existing) {
                http_response_code(409);
                echo json_encode(['error' => 'Uživatel již existuje']);
            } else {
                if ($isManual) {
                    // Manual registration: Store as permanent password
                    $stmt = $pdo->prepare("INSERT INTO users (email, role, password, createdAt) VALUES (?, 'navstevnik', ?, NOW())");
                } else {
                    // Auto-registration: Store as temp password
                    $stmt = $pdo->prepare("INSERT INTO users (email, role, tempPassword, createdAt) VALUES (?, 'navstevnik', ?, NOW())");
                }
                $stmt->execute([$email, $password]);
                echo json_encode(['status' => 'success']);
            }
            break;

        case 'update_user':
            $email = $data['email'] ?? '';
            if (!$email) {
                http_response_code(400);
                echo json_encode(['error' => 'Chybí email']);
                break;
            }

            $fields = [];
            $params = [];

            if (isset($data['name'])) {
                $fields[] = "name = ?";
                $params[] = $data['name'];
            }
            if (isset($data['phone'])) {
                $fields[] = "phone = ?";
                $params[] = $data['phone'];
            }
            // Handle password explicitly (allow empty string/null? usually not, but let frontend handle validation)
            if (isset($data['password'])) {
                $fields[] = "password = ?";
                $params[] = $data['password'];
            }

            // Allow setting tempPassword to null
            if (array_key_exists('tempPassword', $data)) {
                $fields[] = "tempPassword = ?";
                $params[] = $data['tempPassword'];
            }

            if (isset($data['role'])) {
                $fields[] = "role = ?";
                $params[] = $data['role'];
            }
            if (isset($data['socials'])) {
                $fields[] = "socials = ?";
                $params[] = is_string($data['socials']) ? $data['socials'] : json_encode($data['socials']);
            }

            if (empty($fields)) {
                echo json_encode(['status' => 'success', 'message' => 'Žádné změny']);
                break;
            }

            $params[] = $email;
            $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE email = ?";

            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                echo json_encode(['status' => 'success']);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Chyba databáze: ' . $e->getMessage()]);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>