<?php
// admin/api_landing_leads.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';

// Auto-create database table if not exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS landing_leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        landing_slug VARCHAR(100) NOT NULL,
        master_name VARCHAR(150) DEFAULT NULL,
        email VARCHAR(255) NOT NULL,
        name VARCHAR(150) DEFAULT NULL,
        phone VARCHAR(50) DEFAULT NULL,
        user_role VARCHAR(50) DEFAULT NULL,
        message TEXT DEFAULT NULL,
        status VARCHAR(50) DEFAULT 'novy',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (landing_slug),
        INDEX (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (\PDOException $e) {
    // Silent fail if table already exists or database handles differently
}

$action = $_GET['action'] ?? '';
$inputRaw = file_get_contents('php://input');
$input = json_decode($inputRaw, true) ?? $_POST;

if ($action === 'capture_email') {
    $email = trim($input['email'] ?? '');
    $slug = trim($input['landing_slug'] ?? 'master');
    $masterName = trim($input['master_name'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Zadejte platný e-mail']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO landing_leads (landing_slug, master_name, email, status, created_at) VALUES (?, ?, ?, 'novy', NOW())");
        $stmt->execute([$slug, $masterName, $email]);
        $leadId = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'lead_id' => $leadId,
            'message' => 'E-mail byl úspěšně uložen.'
        ]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Chyba databáze: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'update_lead') {
    $leadId = intval($input['lead_id'] ?? 0);
    $email = trim($input['email'] ?? '');
    $slug = trim($input['landing_slug'] ?? 'master');
    $name = trim($input['name'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $role = trim($input['user_role'] ?? '');
    $message = trim($input['message'] ?? '');

    try {
        if ($leadId > 0) {
            $stmt = $pdo->prepare("UPDATE landing_leads SET name = ?, phone = ?, user_role = ?, message = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $role, $message, $leadId]);
        } else if (!empty($email)) {
            $stmt = $pdo->prepare("UPDATE landing_leads SET name = ?, phone = ?, user_role = ?, message = ? WHERE email = ? AND landing_slug = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$name, $phone, $role, $message, $email, $slug]);
        }

        echo json_encode(['success' => true, 'message' => 'Údaje byly doplněny.']);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Chyba při aktualizaci: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("DELETE FROM landing_leads WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'toggle_status' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $newStatus = ($_GET['status'] === 'vyreseno') ? 'vyreseno' : 'novy';
    $stmt = $pdo->prepare("UPDATE landing_leads SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $id]);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Neznámá akce']);
