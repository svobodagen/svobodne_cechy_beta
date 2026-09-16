<?php
// admin/api_landing_leads.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../notification_helper.php';

// Auto-create database tables & columns if not exist
try {
    // 1. landing_leads table
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

    // Ensure extra columns exist on landing_leads
    $alters = [
        "ALTER TABLE landing_leads ADD COLUMN newsletter TINYINT(1) DEFAULT 0",
        "ALTER TABLE landing_leads ADD COLUMN session_id VARCHAR(64) DEFAULT NULL",
        "ALTER TABLE landing_leads ADD COLUMN source VARCHAR(100) DEFAULT NULL",
        "ALTER TABLE landing_sessions ADD COLUMN utm_content VARCHAR(100) DEFAULT NULL AFTER utm_campaign"
    ];
    foreach ($alters as $sql) {
        try { $pdo->exec($sql); } catch(\PDOException $e) {}
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
        utm_content VARCHAR(100) DEFAULT NULL,
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

    // 4. landing_links table (saved campaign links)
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

} catch (\PDOException $e) {
    // Database handled gracefully
}

$action = $_GET['action'] ?? '';
$inputRaw = file_get_contents('php://input');
$input = json_decode($inputRaw, true) ?? $_POST;

/**
 * Helper to build HTML analytics block for email notifications
 */
function build_analytics_email_block($sessionId, $slug) {
    global $pdo;
    if (empty($sessionId)) return '';

    try {
        $stmt = $pdo->prepare("SELECT * FROM landing_sessions WHERE session_id = ? LIMIT 1");
        $stmt->execute([$sessionId]);
        $session = $stmt->fetch();

        if (!$session) return '';

        $evStmt = $pdo->prepare("SELECT event_type, event_label, event_section, created_at FROM landing_events WHERE session_id = ? ORDER BY created_at ASC LIMIT 25");
        $evStmt->execute([$sessionId]);
        $events = $evStmt->fetchAll();

        $sourceLabel = htmlspecialchars($session['source'] ?: 'Přímá návštěva');
        $device = ($session['device_type'] === 'mobile') ? '📱 Mobil' : (($session['device_type'] === 'tablet') ? '📱 Tablet' : '💻 Počítač');
        $maxSec = htmlspecialchars($session['max_section'] ?: 'Hero');
        $btnLabel = htmlspecialchars($session['clicked_button'] ?: 'Nezaznamenáno');
        $btnSec = htmlspecialchars($session['clicked_section'] ?: '-');

        $durSec = (int)$session['duration_seconds'];
        $durText = ($durSec >= 60) ? (floor($durSec / 60) . ' min ' . ($durSec % 60) . ' s') : ($durSec . ' s');

        $timelineRows = "";
        foreach ($events as $ev) {
            $time = date('H:i:s', strtotime($ev['created_at']));
            $type = $ev['event_type'];
            $label = htmlspecialchars($ev['event_label'] ?: '');
            $sec = htmlspecialchars($ev['event_section'] ?: '');

            $desc = $label;
            if ($type === 'page_view') $desc = "Příchod na stránku (zdroj: {$sourceLabel})";
            else if ($type === 'scroll_section') $desc = "Doscrolloval do sekce: [{$sec}]";
            else if ($type === 'btn_click') $desc = "Stiskl tlačítko: \"{$label}\" v sekci [{$sec}]";
            else if ($type === 'modal_open') $desc = "Otevřel formulář";
            else if ($type === 'form_step1') $desc = "✅ Dokončil Krok 1 (zadal e-mail)";
            else if ($type === 'form_step2') $desc = "✅ Dokončil Krok 2 (kontaktní údaje)";
            else if ($type === 'form_whatsapp') $desc = "💬 Přešel na WhatsApp";
            else if ($type === 'form_step3') $desc = "🎉 Zobrazena děkovná obrazovka";
            else if ($type === 'step3_web_click') $desc = "🌐 Kliknul na web dílny v poděkování ({$label})";

            $timelineRows .= "<tr><td style='padding:4px 8px; color:#94a3b8; font-family:monospace; font-size:12px; width:65px; border-bottom:1px solid #334155;'>{$time}</td><td style='padding:4px 8px; color:#e2e8f0; font-size:13px; border-bottom:1px solid #334155;'>{$desc}</td></tr>";
        }

        $adminStatsUrl = get_base_url() . '/admin/landing_stats.php?slug=' . urlencode($slug) . '&session=' . urlencode($sessionId);

        $html = "
          <div style='margin-top:25px; padding:18px; background:#1e293b; border-radius:10px; border:1px solid #3b82f6;'>
            <h4 style='color:#60a5fa; margin-top:0; margin-bottom:12px; font-size:15px; text-transform:uppercase; letter-spacing:0.5px;'>
              📊 Analytika návštěvníka a zdroj zájmu
            </h4>
            <table style='width:100%; border-collapse:collapse; color:#cbd5e1; font-size:13px; margin-bottom:14px;'>
              <tr><td style='padding:5px 0; width:150px; color:#94a3b8;'><strong>Zdroj (Kampaň):</strong></td><td style='padding:5px 0;'><span style='background:#0f172a; padding:2px 8px; border-radius:4px; border:1px solid #475569; color:#f59e0b; font-weight:700;'>{$sourceLabel}</span></td></tr>
              <tr><td style='padding:5px 0; color:#94a3b8;'><strong>Zaujat tlačítkem:</strong></td><td style='padding:5px 0;'><strong style='color:#38bdf8;'>{$btnLabel}</strong> (v sekci {$btnSec})</td></tr>
              <tr><td style='padding:5px 0; color:#94a3b8;'><strong>Hloubka scrollování:</strong></td><td style='padding:5px 0;'>Doscrolloval do sekce: <strong>{$maxSec}</strong></td></tr>
              <tr><td style='padding:5px 0; color:#94a3b8;'><strong>Čas na stránce:</strong></td><td style='padding:5px 0;'>{$durText}</td></tr>
              <tr><td style='padding:5px 0; color:#94a3b8;'><strong>Zařízení:</strong></td><td style='padding:5px 0;'>{$device}</td></tr>
            </table>

            " . ($timelineRows ? "
            <div style='margin-top:10px;'>
              <div style='font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:6px;'>Časová osa kroků návštěvníka:</div>
              <table style='width:100%; border-collapse:collapse; background:#0f172a; border-radius:6px; overflow:hidden;'>
                {$timelineRows}
              </table>
            </div>" : "") . "

            <div style='margin-top:14px; text-align:right;'>
              <a href='{$adminStatsUrl}' style='color:#60a5fa; font-size:13px; text-decoration:none; font-weight:600;'>Otevřít detail návštěvníka v analytice →</a>
            </div>
          </div>
        ";
        return $html;
    } catch (\Exception $e) {
        return '';
    }
}

function slugifyCz(string $str): string {
    $trans = [
        'á'=>'a','č'=>'c','ď'=>'d','é'=>'e','ě'=>'e','í'=>'i','ň'=>'n','ó'=>'o','ř'=>'r','š'=>'s','ť'=>'t','ú'=>'u','ů'=>'u','ý'=>'y','ž'=>'z',
        'Á'=>'a','Č'=>'c','Ď'=>'d','É'=>'e','Ě'=>'e','Í'=>'i','Ň'=>'n','Ó'=>'o','Ř'=>'r','Š'=>'s','Ť'=>'t','Ú'=>'u','Ů'=>'u','Ý'=>'y','Ž'=>'z'
    ];
    $str = strtr($str, $trans);
    $str = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $str));
    return $str;
}

function makePostCode(string $scheduledAt, string $groupName): string {
    $dt = new DateTime($scheduledAt);
    $datePart = $dt->format('Ymd');
    $timePart = $dt->format('Hi');
    $groupSlug = substr(slugifyCz($groupName) ?: 'sk', 0, 12);
    return 'KAL-' . $datePart . '-' . $timePart . '-' . $groupSlug;
}

// Helper: Resolve post details (group, time, template, code) for a given session / source / utm_content
function resolvePostDetails($pdo, $utmContent, $source, $createdAt) {
    if (!empty($utmContent) && strpos($utmContent, 'KAL-') === 0) {
        // Code format: KAL-YYYYMMDD-HHmm-groupslug
        $parts = explode('-', $utmContent);
        if (count($parts) >= 4) {
            $datePart = $parts[1]; // YYYYMMDD
            $timePart = $parts[2]; // HHmm
            $groupSlug = strtolower($parts[3]);
            
            // Try to find matching task
            try {
                $stmt = $pdo->prepare("
                    SELECT t.id, t.scheduled_at, g.name as group_name, pt.title as template_title
                    FROM fb_schedule_tasks t
                    JOIN fb_groups g ON t.group_id = g.id
                    LEFT JOIN fb_post_templates pt ON t.template_id = pt.id
                    WHERE t.target_url LIKE ? OR (DATE_FORMAT(t.scheduled_at, '%Y%m%d%H%i') = ? AND LOWER(REPLACE(g.name, ' ', '')) LIKE ?)
                    ORDER BY t.id DESC
                    LIMIT 1
                ");
                $stmt->execute(['%' . $utmContent . '%', $datePart . $timePart, '%' . $groupSlug . '%']);
                $task = $stmt->fetch();
                if ($task) {
                    return [
                        'post_code' => $utmContent,
                        'group_name' => $task['group_name'],
                        'scheduled_at' => $task['scheduled_at'],
                        'template_title' => $task['template_title'] ?: 'Výchozí šablona',
                        'matched' => true
                    ];
                }
            } catch (\Exception $e) {}
            
            // Fallback: parse date/time from code directly
            $formattedDate = substr($datePart, 6, 2) . '. ' . substr($datePart, 4, 2) . '. ' . substr($datePart, 0, 4);
            $formattedTime = substr($timePart, 0, 2) . ':' . substr($timePart, 2, 2);
            return [
                'post_code' => $utmContent,
                'group_name' => 'Skupina (' . $groupSlug . ')',
                'scheduled_at' => $formattedDate . ' ' . $formattedTime,
                'template_title' => 'Příspěvek z kalendáře',
                'matched' => false
            ];
        }
    }

    // Fallback: If utm_content is empty, but source is fb_gr_... or FB_GR_...
    if (!empty($source) && stripos($source, 'fb_gr_') === 0) {
        $slug = preg_replace('/^fb_gr_/i', '', $source);
        $cleanSlug = strtolower(preg_replace('/[^a-z0-9]/i', '', $slug));
        
        try {
            // Find group by slug
            $groups = $pdo->query("SELECT id, name FROM fb_groups")->fetchAll();
            $matchedGroup = null;
            foreach ($groups as $g) {
                $gClean = strtolower(preg_replace('/[^a-z0-9]/i', '', slugifyCz($g['name'])));
                if (!empty($gClean) && (strpos($cleanSlug, $gClean) !== false || strpos($gClean, $cleanSlug) !== false)) {
                    $matchedGroup = $g;
                    break;
                }
            }
            
            if ($matchedGroup) {
                // Find task for this group closest to createdAt
                $stmt = $pdo->prepare("
                    SELECT t.id, t.scheduled_at, pt.title as template_title
                    FROM fb_schedule_tasks t
                    LEFT JOIN fb_post_templates pt ON t.template_id = pt.id
                    WHERE t.group_id = ?
                    ORDER BY ABS(TIMESTAMPDIFF(MINUTE, t.scheduled_at, ?)) ASC
                    LIMIT 1
                ");
                $stmt->execute([$matchedGroup['id'], $createdAt]);
                $task = $stmt->fetch();
                if ($task) {
                    $code = makePostCode($task['scheduled_at'], $matchedGroup['name']);
                    return [
                        'post_code' => $code,
                        'group_name' => $matchedGroup['name'],
                        'scheduled_at' => $task['scheduled_at'],
                        'template_title' => $task['template_title'] ?: 'Výchozí šablona',
                        'matched' => true
                    ];
                }
            }
        } catch (\Exception $e) {}
    }

    return null;
}

// -------------------------------------------------------------
// 1. ACTION: track_session (Initial Pageview / Session init)
// -------------------------------------------------------------
if ($action === 'track_session') {
    $sessionId = trim($input['session_id'] ?? '');
    $slug = trim($input['landing_slug'] ?? 'default');
    $source = trim($input['source'] ?? 'direct');
    $referrer = trim($input['referrer'] ?? '');
    $utmSource = trim($input['utm_source'] ?? '');
    $utmMedium = trim($input['utm_medium'] ?? '');
    $utmCampaign = trim($input['utm_campaign'] ?? '');
    $utmContent = trim($input['utm_content'] ?? '');
    $deviceType = trim($input['device_type'] ?? 'desktop');

    if (empty($sessionId)) {
        $sessionId = bin2hex(random_bytes(16));
    }

    if (empty($utmContent) && !empty($source) && stripos($source, 'fb_gr_') === 0) {
        $pInfo = resolvePostDetails($pdo, '', $source, date('Y-m-d H:i:s'));
        if ($pInfo && !empty($pInfo['post_code'])) {
            $utmContent = $pInfo['post_code'];
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO landing_sessions 
            (session_id, landing_slug, source, referrer, utm_source, utm_medium, utm_campaign, utm_content, device_type, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE 
                updated_at = NOW(),
                utm_content = COALESCE(NULLIF(VALUES(utm_content), ''), utm_content),
                utm_source = COALESCE(NULLIF(VALUES(utm_source), ''), utm_source),
                utm_medium = COALESCE(NULLIF(VALUES(utm_medium), ''), utm_medium),
                utm_campaign = COALESCE(NULLIF(VALUES(utm_campaign), ''), utm_campaign),
                source = COALESCE(NULLIF(VALUES(source), ''), source)");
        $stmt->execute([$sessionId, $slug, $source, $referrer, $utmSource, $utmMedium, $utmCampaign, $utmContent, $deviceType]);

        // Insert initial pageview event if not recorded
        $chk = $pdo->prepare("SELECT id FROM landing_events WHERE session_id = ? AND event_type = 'page_view' LIMIT 1");
        $chk->execute([$sessionId]);
        if (!$chk->fetch()) {
            $ev = $pdo->prepare("INSERT INTO landing_events (session_id, landing_slug, event_type, event_label, event_section, created_at) VALUES (?, ?, 'page_view', ?, 'hero', NOW())");
            $ev->execute([$sessionId, $slug, $source]);
        }

        echo json_encode(['success' => true, 'session_id' => $sessionId]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------------------------
// 2. ACTION: track_event (Scroll, Button Click, WhatsApp, Web Click)
// -------------------------------------------------------------
if ($action === 'track_event') {
    $sessionId = trim($input['session_id'] ?? '');
    $slug = trim($input['landing_slug'] ?? 'default');
    $eventType = trim($input['event_type'] ?? '');
    $eventLabel = trim($input['event_label'] ?? '');
    $eventSection = trim($input['event_section'] ?? '');
    $eventData = trim($input['event_data'] ?? '');
    $duration = intval($input['duration_seconds'] ?? 0);

    if (empty($sessionId) || empty($eventType)) {
        echo json_encode(['success' => false, 'message' => 'Missing session_id or event_type']);
        exit;
    }

    try {
        // Record event
        $stmt = $pdo->prepare("INSERT INTO landing_events (session_id, landing_slug, event_type, event_label, event_section, event_data, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$sessionId, $slug, $eventType, $eventLabel, $eventSection, $eventData]);

        // Update session summary attributes
        $updateParts = ["updated_at = NOW()"];
        $params = [];

        if ($eventType === 'scroll_section' && !empty($eventSection)) {
            $updateParts[] = "max_section = ?";
            $params[] = $eventSection;
        } else if ($eventType === 'btn_click' && !empty($eventLabel)) {
            $updateParts[] = "clicked_button = ?";
            $params[] = $eventLabel;
            if (!empty($eventSection)) {
                $updateParts[] = "clicked_section = ?";
                $params[] = $eventSection;
            }
        } else if ($eventType === 'step3_web_click') {
            $updateParts[] = "form_status = 'step3_web_clicked'";
        } else if ($eventType === 'form_step3') {
            $updateParts[] = "form_status = 'step3_viewed'";
        }

        if ($duration > 0) {
            $updateParts[] = "duration_seconds = GREATEST(duration_seconds, ?)";
            $params[] = $duration;
        }

        $params[] = $sessionId;
        $upSql = "UPDATE landing_sessions SET " . implode(", ", $updateParts) . " WHERE session_id = ?";
        $upStmt = $pdo->prepare($upSql);
        $upStmt->execute($params);

        echo json_encode(['success' => true]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------------------------
// 3. ACTION: capture_email (Step 1 of Lead Modal)
// -------------------------------------------------------------
if ($action === 'capture_email') {
    $email = trim($input['email'] ?? '');
    $slug = trim($input['landing_slug'] ?? 'master');
    $masterName = trim($input['master_name'] ?? '');
    $sessionId = trim($input['session_id'] ?? '');
    $source = trim($input['source'] ?? 'direct');
    $btnLabel = trim($input['button_name'] ?? '');
    $btnSec = trim($input['button_section'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Zadejte platný e-mail']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO landing_leads (landing_slug, master_name, email, session_id, source, status, created_at) VALUES (?, ?, ?, ?, ?, 'novy', NOW())");
        $stmt->execute([$slug, $masterName, $email, $sessionId ?: null, $source ?: 'direct']);
        $leadId = $pdo->lastInsertId();

        // Update session
        if (!empty($sessionId)) {
            $up = $pdo->prepare("UPDATE landing_sessions SET lead_id = ?, form_status = 'step1_email', updated_at = NOW() WHERE session_id = ?");
            $up->execute([$leadId, $sessionId]);

            // Log event
            $ev = $pdo->prepare("INSERT INTO landing_events (session_id, landing_slug, event_type, event_label, event_section, created_at) VALUES (?, ?, 'form_step1', ?, ?, NOW())");
            $ev->execute([$sessionId, $slug, $email, $btnSec ?: 'modal']);
        }

        // Send Admin Email Notification with analytics
        $adminUrl = get_base_url() . '/admin/landing_leads.php';
        $safeEmail = htmlspecialchars($email);
        $safeMaster = htmlspecialchars($masterName ?: $slug);
        $safeSource = htmlspecialchars($source ?: 'přímý odkaz');

        $analyticsBlock = build_analytics_email_block($sessionId, $slug);

        $subject = "🔔 Nový zájemce z Landing Page: " . $safeEmail . " (" . $safeSource . ")";
        $bodyHtml = "
          <h3 style='color:#f8fafc; margin-top:0;'>Byl zachycen nový e-mail na Landing Page!</h3>
          <table style='width:100%; border-collapse:collapse; color:#cbd5e1; font-size:14px; margin-bottom:15px;'>
            <tr><td style='padding:8px 0; border-bottom:1px solid #334155; width:140px; color:#94a3b8;'><strong>E-mail:</strong></td><td style='padding:8px 0; border-bottom:1px solid #334155;'><a href='mailto:{$safeEmail}' style='color:#60a5fa; font-weight:700;'>{$safeEmail}</a></td></tr>
            <tr><td style='padding:8px 0; border-bottom:1px solid #334155; color:#94a3b8;'><strong>Mistr / Kampaň:</strong></td><td style='padding:8px 0; border-bottom:1px solid #334155;'>{$safeMaster} ({$slug})</td></tr>
            <tr><td style='padding:8px 0; border-bottom:1px solid #334155; color:#94a3b8;'><strong>Zdroj (Odkaz):</strong></td><td style='padding:8px 0; border-bottom:1px solid #334155;'><span style='background:#f59e0b; color:#000; padding:2px 6px; border-radius:4px; font-weight:700;'>{$safeSource}</span></td></tr>
            <tr><td style='padding:8px 0; color:#94a3b8;'><strong>Fáze formuláře:</strong></td><td style='padding:8px 0;'>Fáze 1 (Zatím zadaný e-mail)</td></tr>
          </table>

          {$analyticsBlock}

          <div style='margin-top:20px;'>
            <a href='{$adminUrl}' style='display:inline-block; background:#ff7b1c; color:#ffffff; padding:12px 20px; border-radius:8px; text-decoration:none; font-weight:700; font-size:14px;'>Zobrazit kontakty v administraci →</a>
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

// -------------------------------------------------------------
// 4. ACTION: update_lead (Step 2 of Lead Modal & WhatsApp)
// -------------------------------------------------------------
if ($action === 'update_lead') {
    $leadId = intval($input['lead_id'] ?? 0);
    $email = trim($input['email'] ?? '');
    $slug = trim($input['landing_slug'] ?? 'master');
    $name = trim($input['name'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $role = trim($input['user_role'] ?? '');
    $message = trim($input['message'] ?? '');
    $newsletter = !empty($input['newsletter']) ? 1 : 0;
    $sessionId = trim($input['session_id'] ?? '');
    $isWhatsApp = !empty($input['is_whatsapp']);

    try {
        if ($leadId > 0) {
            $stmt = $pdo->prepare("UPDATE landing_leads SET name = ?, phone = ?, user_role = ?, message = ?, newsletter = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $role, $message, $newsletter, $leadId]);
        } else if (!empty($email)) {
            $stmt = $pdo->prepare("UPDATE landing_leads SET name = ?, phone = ?, user_role = ?, message = ?, newsletter = ? WHERE email = ? AND landing_slug = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$name, $phone, $role, $message, $newsletter, $email, $slug]);
        }

        // Update session and log event
        if (!empty($sessionId)) {
            $formStatus = $isWhatsApp ? 'whatsapp_sent' : 'step2_completed';
            $up = $pdo->prepare("UPDATE landing_sessions SET form_status = ?, updated_at = NOW() WHERE session_id = ?");
            $up->execute([$formStatus, $sessionId]);

            $evType = $isWhatsApp ? 'form_whatsapp' : 'form_step2';
            $evLabel = ($isWhatsApp ? 'WhatsApp: ' : 'Formulář: ') . ($name ?: $email);
            $ev = $pdo->prepare("INSERT INTO landing_events (session_id, landing_slug, event_type, event_label, event_section, created_at) VALUES (?, ?, ?, ?, 'modal', NOW())");
            $ev->execute([$sessionId, $slug, $evType, $evLabel]);
        }

        // Send Notification with complete analytics block
        $adminUrl = get_base_url() . '/admin/landing_leads.php';
        $safeName = htmlspecialchars($name ?: 'Nezadané');
        $safeEmail = htmlspecialchars($email ?: '-');
        $safePhone = htmlspecialchars($phone ?: '-');
        $safeRole = htmlspecialchars($role ?: '-');
        $safeMsg = htmlspecialchars($message ?: '-');
        $typeTag = $isWhatsApp ? ' [Odesláno + WhatsApp]' : '';

        $analyticsBlock = build_analytics_email_block($sessionId, $slug);

        $subject = "⭐ Kompletní zájemce: " . ($safeName !== 'Nezadané' ? $safeName : $safeEmail) . $typeTag;
        $bodyHtml = "
          <h3 style='color:#f8fafc; margin-top:0;'>Zájemce doplnil své kontaktní údaje (Fáze 2){$typeTag}!</h3>
          <table style='width:100%; border-collapse:collapse; color:#cbd5e1; font-size:14px; margin-bottom:15px;'>
            <tr><td style='padding:8px 0; border-bottom:1px solid #334155; width:140px; color:#94a3b8;'><strong>Jméno:</strong></td><td style='padding:8px 0; border-bottom:1px solid #334155; font-weight:700; color:#fff;'>{$safeName}</td></tr>
            <tr><td style='padding:8px 0; border-bottom:1px solid #334155; color:#94a3b8;'><strong>Telefon:</strong></td><td style='padding:8px 0; border-bottom:1px solid #334155;'><a href='tel:{$safePhone}' style='color:#22c55e; font-weight:700;'>{$safePhone}</a></td></tr>
            <tr><td style='padding:8px 0; border-bottom:1px solid #334155; color:#94a3b8;'><strong>E-mail:</strong></td><td style='padding:8px 0; border-bottom:1px solid #334155;'><a href='mailto:{$safeEmail}' style='color:#60a5fa;'>{$safeEmail}</a></td></tr>
            <tr><td style='padding:8px 0; border-bottom:1px solid #334155; color:#94a3b8;'><strong>Role:</strong></td><td style='padding:8px 0; border-bottom:1px solid #334155;'>{$safeRole}</td></tr>
            <tr><td style='padding:8px 0; border-bottom:1px solid #334155; color:#94a3b8;'><strong>Zpráva:</strong></td><td style='padding:8px 0; border-bottom:1px solid #334155; white-space:pre-wrap;'>{$safeMsg}</td></tr>
            <tr><td style='padding:8px 0; color:#94a3b8;'><strong>Kampaň:</strong></td><td style='padding:8px 0;'>{$slug}</td></tr>
          </table>

          {$analyticsBlock}

          <div style='margin-top:20px;'>
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

// -------------------------------------------------------------
// 5. ACTION: get_stats (Comprehensive analytics dashboard data)
// -------------------------------------------------------------
if ($action === 'get_stats') {
    $slug = trim($_GET['slug'] ?? '');
    $days = intval($_GET['days'] ?? 30);

    try {
        $whereSql = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        $params = [$days];

        if (!empty($slug)) {
            $whereSql .= " AND landing_slug = ?";
            $params[] = $slug;
        }

        // 1. Overview counts
        $stmtTot = $pdo->prepare("SELECT 
            COUNT(*) as total_sessions,
            SUM(CASE WHEN device_type = 'mobile' THEN 1 ELSE 0 END) as mobile_sessions,
            SUM(CASE WHEN device_type = 'desktop' THEN 1 ELSE 0 END) as desktop_sessions,
            SUM(CASE WHEN clicked_button IS NOT NULL THEN 1 ELSE 0 END) as cta_clicked_count,
            SUM(CASE WHEN form_status IN ('step1_email', 'step2_completed', 'whatsapp_sent', 'step3_viewed', 'step3_web_clicked') THEN 1 ELSE 0 END) as step1_count,
            SUM(CASE WHEN form_status IN ('step2_completed', 'whatsapp_sent', 'step3_viewed', 'step3_web_clicked') THEN 1 ELSE 0 END) as step2_count,
            SUM(CASE WHEN form_status = 'whatsapp_sent' THEN 1 ELSE 0 END) as whatsapp_count,
            SUM(CASE WHEN form_status = 'step3_web_clicked' THEN 1 ELSE 0 END) as web_click_count,
            AVG(duration_seconds) as avg_duration
            FROM landing_sessions {$whereSql}");
        $stmtTot->execute($params);
        $overview = $stmtTot->fetch();

        // 2. Sources breakdown
        $stmtSrc = $pdo->prepare("SELECT 
            source,
            COUNT(*) as visits,
            SUM(CASE WHEN clicked_button IS NOT NULL THEN 1 ELSE 0 END) as cta_clicks,
            SUM(CASE WHEN form_status IN ('step1_email', 'step2_completed', 'whatsapp_sent', 'step3_viewed', 'step3_web_clicked') THEN 1 ELSE 0 END) as step1_count,
            SUM(CASE WHEN form_status IN ('step2_completed', 'whatsapp_sent', 'step3_viewed', 'step3_web_clicked') THEN 1 ELSE 0 END) as leads_count,
            SUM(CASE WHEN form_status = 'whatsapp_sent' THEN 1 ELSE 0 END) as whatsapp_count
            FROM landing_sessions {$whereSql}
            GROUP BY source
            ORDER BY visits DESC");
        $stmtSrc->execute($params);
        $sources = $stmtSrc->fetchAll();

        // 3. Sections breakdown (events)
        $secWhere = "WHERE event_type = 'scroll_section' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        $secParams = [$days];
        if (!empty($slug)) {
            $secWhere .= " AND landing_slug = ?";
            $secParams[] = $slug;
        }
        $stmtSec = $pdo->prepare("SELECT 
            event_section as section_name,
            COUNT(DISTINCT session_id) as visitors_reached
            FROM landing_events {$secWhere}
            GROUP BY event_section
            ORDER BY visitors_reached DESC");
        $stmtSec->execute($secParams);
        $sections = $stmtSec->fetchAll();

        // 4. Buttons breakdown (which button in which section)
        $btnWhere = "WHERE event_type = 'btn_click' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        $btnParams = [$days];
        if (!empty($slug)) {
            $btnWhere .= " AND landing_slug = ?";
            $btnParams[] = $slug;
        }
        $stmtBtn = $pdo->prepare("SELECT 
            event_label as button_name,
            event_section as section_name,
            COUNT(*) as clicks
            FROM landing_events {$btnWhere}
            GROUP BY event_label, event_section
            ORDER BY clicks DESC");
        $stmtBtn->execute($btnParams);
        $buttons = $stmtBtn->fetchAll();

        // 5. Available Slugs
        $slugsStmt = $pdo->query("SELECT DISTINCT landing_slug FROM landing_sessions ORDER BY landing_slug ASC");
        $availableSlugs = $slugsStmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode([
            'success' => true,
            'overview' => $overview,
            'sources' => $sources,
            'sections' => $sections,
            'buttons' => $buttons,
            'available_slugs' => $availableSlugs
        ]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------------------------
// 6. ACTION: get_visitors (Individual sessions list & filtering)
// -------------------------------------------------------------
if ($action === 'get_visitors') {
    $slug = trim($_GET['slug'] ?? '');
    $filter = trim($_GET['filter'] ?? 'all');
    $search = trim($_GET['search'] ?? '');
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(100, max(10, intval($_GET['limit'] ?? 25)));
    $offset = ($page - 1) * $limit;

    try {
        $where = "WHERE 1=1";
        $params = [];

        if (!empty($slug)) {
            $where .= " AND s.landing_slug = ?";
            $params[] = $slug;
        }

        if ($filter === 'cta_clicked') {
            $where .= " AND s.clicked_button IS NOT NULL";
        } else if ($filter === 'leads') {
            $where .= " AND s.form_status IN ('step2_completed', 'whatsapp_sent', 'step3_viewed', 'step3_web_clicked')";
        } else if ($filter === 'abandoned_step1') {
            $where .= " AND s.form_status = 'step1_email'";
        }

        if (!empty($search)) {
            $where .= " AND (s.source LIKE ? OR s.utm_content LIKE ? OR s.session_id LIKE ? OR l.email LIKE ? OR l.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // Count
        $countSql = "SELECT COUNT(*) FROM landing_sessions s LEFT JOIN landing_leads l ON s.lead_id = l.id {$where}";
        $stmtC = $pdo->prepare($countSql);
        $stmtC->execute($params);
        $totalCount = $stmtC->fetchColumn();

        // Fetch
        $sql = "SELECT s.*, l.email as lead_email, l.name as lead_name, l.phone as lead_phone, l.status as lead_status
            FROM landing_sessions s
            LEFT JOIN landing_leads l ON s.lead_id = l.id
            {$where}
            ORDER BY s.created_at DESC
            LIMIT {$limit} OFFSET {$offset}";
        $stmtF = $pdo->prepare($sql);
        $stmtF->execute($params);
        $visitors = $stmtF->fetchAll();

        // Enrich visitors with post_details (group, scheduled time, template, post_code)
        foreach ($visitors as &$v) {
            $postInfo = resolvePostDetails($pdo, $v['utm_content'] ?? '', $v['source'] ?? '', $v['created_at']);
            if ($postInfo) {
                $v['post_details'] = $postInfo;
                if (empty($v['utm_content'])) {
                    $v['utm_content'] = $postInfo['post_code'];
                    try {
                        $pdo->prepare("UPDATE landing_sessions SET utm_content = ? WHERE session_id = ?")->execute([$postInfo['post_code'], $v['session_id']]);
                    } catch (\Exception $e) {}
                }
            } else {
                $v['post_details'] = null;
            }
        }
        unset($v);

        echo json_encode([
            'success' => true,
            'total' => (int)$totalCount,
            'page' => $page,
            'limit' => $limit,
            'visitors' => $visitors
        ]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------------------------
// 7. ACTION: get_timeline (Detail timeline for single session)
// -------------------------------------------------------------
if ($action === 'get_timeline') {
    $sessionId = trim($_GET['session_id'] ?? '');

    if (empty($sessionId)) {
        echo json_encode(['success' => false, 'message' => 'Missing session_id']);
        exit;
    }

    try {
        $stmtS = $pdo->prepare("SELECT s.*, l.email, l.name, l.phone, l.user_role, l.message FROM landing_sessions s LEFT JOIN landing_leads l ON s.lead_id = l.id WHERE s.session_id = ? LIMIT 1");
        $stmtS->execute([$sessionId]);
        $session = $stmtS->fetch();

        if ($session) {
            $session['post_details'] = resolvePostDetails($pdo, $session['utm_content'] ?? '', $session['source'] ?? '', $session['created_at']);
            if ($session['post_details'] && empty($session['utm_content'])) {
                $session['utm_content'] = $session['post_details']['post_code'];
            }
        }

        $stmtE = $pdo->prepare("SELECT * FROM landing_events WHERE session_id = ? ORDER BY created_at ASC");
        $stmtE->execute([$sessionId]);
        $events = $stmtE->fetchAll();

        echo json_encode([
            'success' => true,
            'session' => $session,
            'events' => $events
        ]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------------------------
// 8. ACTION: save_link, get_links, delete_link (Link Builder)
// -------------------------------------------------------------
if ($action === 'save_link') {
    $slug = trim($input['landing_slug'] ?? '');
    $tag = trim($input['source_tag'] ?? '');
    $label = trim($input['label'] ?? '');
    $channel = trim($input['channel'] ?? 'facebook');
    $fullUrl = trim($input['full_url'] ?? '');

    if (empty($slug) || empty($tag) || empty($fullUrl)) {
        echo json_encode(['success' => false, 'message' => 'Vyplňte kód zdroje a landing page']);
        exit;
    }

    // Clean tag: only lowercase alphanumeric, dash and underscore
    $cleanTag = preg_replace('/[^a-z0-9_-]/', '', strtolower($tag));

    try {
        $stmt = $pdo->prepare("INSERT INTO landing_links (landing_slug, source_tag, label, channel, full_url, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE label = VALUES(label), channel = VALUES(channel), full_url = VALUES(full_url)");
        $stmt->execute([$slug, $cleanTag, $label ?: $cleanTag, $channel, $fullUrl]);

        echo json_encode(['success' => true, 'clean_tag' => $cleanTag]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'get_links') {
    $slug = trim($_GET['slug'] ?? '');

    try {
        $where = "WHERE 1=1";
        $params = [];
        if (!empty($slug)) {
            $where .= " AND l.landing_slug = ?";
            $params[] = $slug;
        }

        // Return links joined with stats from landing_sessions
        $sql = "SELECT l.*, 
            COUNT(s.session_id) as visits_count,
            SUM(CASE WHEN s.clicked_button IS NOT NULL THEN 1 ELSE 0 END) as cta_clicks_count,
            SUM(CASE WHEN s.form_status IN ('step2_completed', 'whatsapp_sent', 'step3_viewed', 'step3_web_clicked') THEN 1 ELSE 0 END) as leads_count
            FROM landing_links l
            LEFT JOIN landing_sessions s ON l.landing_slug = s.landing_slug AND l.source_tag = s.source
            {$where}
            GROUP BY l.id
            ORDER BY l.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $links = $stmt->fetchAll();

        echo json_encode(['success' => true, 'links' => $links]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'delete_link') {
    $id = intval($_GET['id'] ?? $input['id'] ?? 0);
    try {
        $stmt = $pdo->prepare("DELETE FROM landing_links WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------------------------
// 9. ACTION: delete_visitor (Delete single session + events)
// -------------------------------------------------------------
if ($action === 'delete_visitor') {
    $sessionId = trim($_GET['session_id'] ?? $input['session_id'] ?? '');
    if (empty($sessionId)) {
        echo json_encode(['success' => false, 'message' => 'Chybí session_id']);
        exit;
    }
    try {
        $pdo->prepare("DELETE FROM landing_events WHERE session_id = ?")->execute([$sessionId]);
        $pdo->prepare("DELETE FROM landing_sessions WHERE session_id = ?")->execute([$sessionId]);
        echo json_encode(['success' => true]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------------------------
// 10. ACTION: bulk_delete_visitors (Delete multiple sessions)
// -------------------------------------------------------------
if ($action === 'bulk_delete_visitors') {
    $ids = $input['session_ids'] ?? [];
    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => false, 'message' => 'Žádné session_id nebyly odeslány']);
        exit;
    }
    // Sanitize: allow valid session IDs (alphanumeric, underscore, hyphen, max 64 chars)
    $ids = array_values(array_filter(
        array_map('trim', $ids),
        fn($id) => is_string($id) && strlen($id) > 0 && strlen($id) <= 64 && preg_match('/^[a-zA-Z0-9_\-]+$/', $id)
    ));
    if (empty($ids)) {
        echo json_encode(['success' => false, 'message' => 'Neplatné session_id']);
        exit;
    }
    try {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $pdo->prepare("DELETE FROM landing_events WHERE session_id IN ({$placeholders})")->execute($ids);
        $pdo->prepare("DELETE FROM landing_sessions WHERE session_id IN ({$placeholders})")->execute($ids);
        echo json_encode(['success' => true, 'deleted' => count($ids)]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------------------------
// Legacy Actions for Leads list compatibility
// -------------------------------------------------------------
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
