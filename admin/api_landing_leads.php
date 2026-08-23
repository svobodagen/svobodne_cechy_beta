<?php
// admin/api_landing_leads.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../notification_helper.php';

// Auto-create database table if not exists
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
        newsletter TINYINT(1) DEFAULT 0,
        status VARCHAR(50) DEFAULT 'novy',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (landing_slug),
        INDEX (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    // Ensure newsletter column exists if table was created previously
    try { $pdo->exec("ALTER TABLE landing_leads ADD COLUMN newsletter TINYINT(1) DEFAULT 0"); } catch(\PDOException $e) {}
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

        // Send Email Notification
        $adminUrl = get_base_url() . '/admin/landing_leads.php';
        $safeEmail = htmlspecialchars($email);
        $safeMaster = htmlspecialchars($masterName ?: $slug);

        $subject = "Nová zájemce z Landing Page: " . $safeEmail . ($masterName ? " ({$masterName})" : "");
        $bodyHtml = "
          <h3 style='color:#f8fafc; margin-top:0;'>Byl zachycen nový e-mail na Landing Page!</h3>
          <table style='width:100%; border-collapse:collapse; color:#cbd5e1; font-size:14px; margin-bottom:20px;'>
            <tr><td style='padding:8px 0; border-bottom:1px solid #334155; width:140px; color:#94a3b8;'><strong>E-mail:</strong></td><td style='padding:8px 0; border-bottom:1px solid #334155;'><a href='mailto:{$safeEmail}' style='color:#60a5fa;'>{$safeEmail}</a></td></tr>
            <tr><td style='padding:8px 0; border-bottom:1px solid #334155; color:#94a3b8;'><strong>Mistr / Kampaň:</strong></td><td style='padding:8px 0; border-bottom:1px solid #334155;'>{$safeMaster} ({$slug})</td></tr>
            <tr><td style='padding:8px 0; color:#94a3b8;'><strong>Fáze:</strong></td><td style='padding:8px 0;'>Fáze 1 (Zatiaľ zadaný e-mail)</td></tr>
          </table>
          <div>
            <a href='{$adminUrl}' style='display:inline-block; background:#ff7b1c; color:#ffffff; padding:12px 20px; border-radius:8px; text-decoration:none; font-weight:700; font-size:14px;'>Zobrazit zájemce v administraci →</a>
          </div>
        ";
        send_admin_notification($subject, $bodyHtml);

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
    $newsletter = !empty($input['newsletter']) ? 1 : 0;

    try {
        if ($leadId > 0) {
            $stmt = $pdo->prepare("UPDATE landing_leads SET name = ?, phone = ?, user_role = ?, message = ?, newsletter = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $role, $message, $newsletter, $leadId]);
        } else if (!empty($email)) {
            $stmt = $pdo->prepare("UPDATE landing_leads SET name = ?, phone = ?, user_role = ?, message = ?, newsletter = ? WHERE email = ? AND landing_slug = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$name, $phone, $role, $message, $newsletter, $email, $slug]);
        }

        // Send Email Notification for updated info
        $adminUrl = get_base_url() . '/admin/landing_leads.php';
        $safeName = htmlspecialchars($name ?: 'Nezadané');
        $safeEmail = htmlspecialchars($email ?: '-');
        $safePhone = htmlspecialchars($phone ?: '-');
        $safeRole = htmlspecialchars($role ?: '-');
        $safeMsg = htmlspecialchars($message ?: '-');

        $subject = "Doplněné údaje od zájemce: " . ($safeName !== 'Nezadané' ? $safeName : $safeEmail);
        $bodyHtml = "
          <h3 style='color:#f8fafc; margin-top:0;'>Zájemce doplnil své kontaktní údaje (Fáze 2)!</h3>
          <table style='width:100%; border-collapse:collapse; color:#cbd5e1; font-size:14px; margin-bottom:20px;'>
            <tr><td style='padding:8px 0; border-bottom:1px solid #334155; width:140px; color:#94a3b8;'><strong>Jméno:</strong></td><td style='padding:8px 0; border-bottom:1px solid #334155;'>{$safeName}</td></tr>
            <tr><td style='padding:8px 0; border-bottom:1px solid #334155; color:#94a3b8;'><strong>E-mail:</strong></td><td style='padding:8px 0; border-bottom:1px solid #334155;'><a href='mailto:{$safeEmail}' style='color:#60a5fa;'>{$safeEmail}</a></td></tr>
            <tr><td style='padding:8px 0; border-bottom:1px solid #334155; color:#94a3b8;'><strong>Telefon:</strong></td><td style='padding:8px 0; border-bottom:1px solid #334155;'><a href='tel:{$safePhone}' style='color:#60a5fa;'>{$safePhone}</a></td></tr>
            <tr><td style='padding:8px 0; border-bottom:1px solid #334155; color:#94a3b8;'><strong>Role:</strong></td><td style='padding:8px 0; border-bottom:1px solid #334155;'>{$safeRole}</td></tr>
            <tr><td style='padding:8px 0; border-bottom:1px solid #334155; color:#94a3b8;'><strong>Zpráva:</strong></td><td style='padding:8px 0; border-bottom:1px solid #334155; white-space:pre-wrap;'>{$safeMsg}</td></tr>
            <tr><td style='padding:8px 0; color:#94a3b8;'><strong>Kampaň:</strong></td><td style='padding:8px 0;'>{$slug}</td></tr>
          </table>
          <div>
            <a href='{$adminUrl}' style='display:inline-block; background:#ff7b1c; color:#ffffff; padding:12px 20px; border-radius:8px; text-decoration:none; font-weight:700; font-size:14px;'>Zobrazit všechny zprávy v administraci →</a>
          </div>
        ";
        send_admin_notification($subject, $bodyHtml);

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
