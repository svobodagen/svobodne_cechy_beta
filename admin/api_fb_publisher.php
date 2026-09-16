<?php
// admin/api_fb_publisher.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';

// Auto-create database tables & columns if not exist
try {
    // 1. fb_groups table
    $pdo->exec("CREATE TABLE IF NOT EXISTS fb_groups (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        group_url VARCHAR(500) NOT NULL,
        category VARCHAR(100) DEFAULT NULL,
        notes TEXT DEFAULT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (is_active),
        INDEX (category)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 2. fb_post_templates table
    $pdo->exec("CREATE TABLE IF NOT EXISTS fb_post_templates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        landing_slug VARCHAR(100) DEFAULT NULL,
        target_url VARCHAR(500) DEFAULT NULL,
        post_text TEXT DEFAULT NULL,
        image_url VARCHAR(500) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (landing_slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 3. fb_schedule_tasks table
    $pdo->exec("CREATE TABLE IF NOT EXISTS fb_schedule_tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        group_id INT NOT NULL,
        template_id INT DEFAULT NULL,
        custom_text TEXT DEFAULT NULL,
        custom_image_url VARCHAR(500) DEFAULT NULL,
        target_url VARCHAR(500) DEFAULT NULL,
        scheduled_at DATETIME NOT NULL,
        status VARCHAR(50) DEFAULT 'naplanovano',
        published_at DATETIME DEFAULT NULL,
        log_message TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (scheduled_at),
        INDEX (status),
        INDEX (group_id),
        INDEX (template_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

} catch (\PDOException $e) {
    // Database handled gracefully
}

$action = $_GET['action'] ?? '';
$inputRaw = file_get_contents('php://input');
$input = json_decode($inputRaw, true) ?? $_POST;

// -------------------------------------------------------------
// Helper: Get Base Web URL
// -------------------------------------------------------------
function getBaseWebUrl() {
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'svobodnecechy.cz';
    return $proto . '://' . $host;
}

// -------------------------------------------------------------
// 1. ACTION: get_all_data (Groups, Templates, Tasks, Landing Pages)
// -------------------------------------------------------------
if ($action === 'get_all_data') {
    try {
        // Groups
        $groupsStmt = $pdo->query("SELECT * FROM fb_groups ORDER BY name ASC");
        $groups = $groupsStmt ? $groupsStmt->fetchAll() : [];

        // Templates
        $tplStmt = $pdo->query("SELECT * FROM fb_post_templates ORDER BY created_at DESC");
        $templates = $tplStmt ? $tplStmt->fetchAll() : [];

        // Tasks (join group and template)
        $tasksStmt = $pdo->query("
            SELECT t.*, 
                   g.name as group_name, g.group_url, g.category as group_category,
                   pt.title as template_title, pt.landing_slug, pt.image_url as template_image_url
            FROM fb_schedule_tasks t
            LEFT JOIN fb_groups g ON t.group_id = g.id
            LEFT JOIN fb_post_templates pt ON t.template_id = pt.id
            ORDER BY t.scheduled_at DESC
            LIMIT 300
        ");
        $tasks = $tasksStmt ? $tasksStmt->fetchAll() : [];

        // Landing pages list
        $lpStmt = $pdo->query("SELECT slug, master_name FROM landing_pages ORDER BY master_name ASC");
        $landingPages = $lpStmt ? $lpStmt->fetchAll() : [];

        echo json_encode([
            'success' => true,
            'groups' => $groups,
            'templates' => $templates,
            'tasks' => $tasks,
            'landing_pages' => $landingPages
        ]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------------------------
// 2. GROUPS: save_group
// -------------------------------------------------------------
if ($action === 'save_group') {
    $id = !empty($input['id']) ? intval($input['id']) : null;
    $name = trim($input['name'] ?? '');
    $groupUrl = trim($input['group_url'] ?? '');
    $category = trim($input['category'] ?? '');
    $notes = trim($input['notes'] ?? '');
    $isActive = isset($input['is_active']) ? intval($input['is_active']) : 1;

    if (empty($name) || empty($groupUrl)) {
        echo json_encode(['success' => false, 'message' => 'Vyplňte prosím název a URL skupiny.']);
        exit;
    }

    try {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE fb_groups SET name = ?, group_url = ?, category = ?, notes = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $groupUrl, $category, $notes, $isActive, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO fb_groups (name, group_url, category, notes, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $groupUrl, $category, $notes, $isActive]);
            $id = $pdo->lastInsertId();
        }
        echo json_encode(['success' => true, 'id' => $id]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------------------------
// 3. GROUPS: delete_group
// -------------------------------------------------------------
if ($action === 'delete_group') {
    $id = intval($_GET['id'] ?? $input['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Chybí ID skupiny']);
        exit;
    }
    try {
        $pdo->prepare("DELETE FROM fb_groups WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------------------------
// 4. TEMPLATES: save_template
// -------------------------------------------------------------
if ($action === 'save_template') {
    $id = !empty($input['id']) ? intval($input['id']) : null;
    $title = trim($input['title'] ?? '');
    $landingSlug = trim($input['landing_slug'] ?? '');
    $targetUrl = trim($input['target_url'] ?? '');
    $postText = trim($input['post_text'] ?? '');
    $imageUrl = trim($input['image_url'] ?? '');

    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Zadejte prosím název šablony.']);
        exit;
    }

    try {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE fb_post_templates SET title = ?, landing_slug = ?, target_url = ?, post_text = ?, image_url = ? WHERE id = ?");
            $stmt->execute([$title, $landingSlug, $targetUrl, $postText, $imageUrl, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO fb_post_templates (title, landing_slug, target_url, post_text, image_url) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $landingSlug, $targetUrl, $postText, $imageUrl]);
            $id = $pdo->lastInsertId();
        }
        echo json_encode(['success' => true, 'id' => $id]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------------------------
// 5. TEMPLATES: delete_template
// -------------------------------------------------------------
if ($action === 'delete_template') {
    $id = intval($_GET['id'] ?? $input['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Chybí ID šablony']);
        exit;
    }
    try {
        $pdo->prepare("DELETE FROM fb_post_templates WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------------------------
// 6. TASKS: save_task (Schedule new post)
// -------------------------------------------------------------
if ($action === 'save_task') {
    $id = !empty($input['id']) ? intval($input['id']) : null;
    $groupId = intval($input['group_id'] ?? 0);
    $templateId = !empty($input['template_id']) ? intval($input['template_id']) : null;
    $scheduledAt = trim($input['scheduled_at'] ?? '');
    $customText = trim($input['custom_text'] ?? '');
    $customImageUrl = trim($input['custom_image_url'] ?? '');
    $targetUrl = trim($input['target_url'] ?? '');
    $status = trim($input['status'] ?? 'naplanovano');

    if (!$groupId || empty($scheduledAt)) {
        echo json_encode(['success' => false, 'message' => 'Vyberte skupinu a termín vložení.']);
        exit;
    }

    try {
        if ($id) {
            $stmt = $pdo->prepare("
                UPDATE fb_schedule_tasks 
                SET group_id = ?, template_id = ?, custom_text = ?, custom_image_url = ?, target_url = ?, scheduled_at = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([$groupId, $templateId, $customText, $customImageUrl, $targetUrl, $scheduledAt, $status, $id]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO fb_schedule_tasks (group_id, template_id, custom_text, custom_image_url, target_url, scheduled_at, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$groupId, $templateId, $customText, $customImageUrl, $targetUrl, $scheduledAt, $status]);
            $id = $pdo->lastInsertId();
        }
        echo json_encode(['success' => true, 'id' => $id]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------------------------
// 7. TASKS: delete_task
// -------------------------------------------------------------
if ($action === 'delete_task') {
    $id = intval($_GET['id'] ?? $input['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Chybí ID úkolu']);
        exit;
    }
    try {
        $pdo->prepare("DELETE FROM fb_schedule_tasks WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------------------------
// 8. TASKS: update_task_status
// -------------------------------------------------------------
if ($action === 'update_task_status') {
    $id = intval($input['id'] ?? $_GET['id'] ?? 0);
    $status = trim($input['status'] ?? $_GET['status'] ?? '');
    $logMessage = trim($input['log_message'] ?? '');

    $allowedStatuses = ['naplanovano', 'naplanovano_rucne', 'zpracovava_se', 'publikovano', 'chyba', 'zruseno'];
    if (!$id || !in_array($status, $allowedStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Neplatné parametry stavu']);
        exit;
    }

    try {
        $publishedAt = ($status === 'publikovano') ? date('Y-m-d H:i:s') : null;
        $stmt = $pdo->prepare("
            UPDATE fb_schedule_tasks 
            SET status = ?, 
                published_at = COALESCE(?, published_at),
                log_message = CASE WHEN ? != '' THEN ? ELSE log_message END,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$status, $publishedAt, $logMessage, $logMessage, $id]);
        echo json_encode(['success' => true]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// =============================================================
// AGENT SPECIFIC ENDPOINTS
// =============================================================

// -------------------------------------------------------------
// 9. AGENT: get_pending_tasks
// Returns tasks scheduled <= NOW() with status 'naplanovano'
// Fully resolves group URL, final post text, image and UTM tracking link
// -------------------------------------------------------------
if ($action === 'get_pending_tasks') {
    try {
        $stmt = $pdo->prepare("
            SELECT t.id as task_id, t.scheduled_at, t.status,
                   g.id as group_id, g.name as group_name, g.group_url, g.category as group_category, g.notes as group_notes,
                   pt.id as template_id, pt.title as template_title, pt.landing_slug,
                   COALESCE(NULLIF(t.custom_text, ''), pt.post_text) as post_text,
                   COALESCE(NULLIF(t.custom_image_url, ''), pt.image_url) as image_url,
                   COALESCE(NULLIF(t.target_url, ''), pt.target_url) as base_target_url
            FROM fb_schedule_tasks t
            JOIN fb_groups g ON t.group_id = g.id
            LEFT JOIN fb_post_templates pt ON t.template_id = pt.id
            WHERE t.status = 'naplanovano' 
              AND t.scheduled_at <= NOW()
              AND g.is_active = 1
            ORDER BY t.scheduled_at ASC
            LIMIT 20
        ");
        $stmt->execute();
        $tasks = $stmt->fetchAll();

        $baseUrl = getBaseWebUrl();

        // Build resolved URLs with UTM parameters
        $result = [];
        foreach ($tasks as $row) {
            $targetUrl = $row['base_target_url'];
            if (empty($targetUrl) && !empty($row['landing_slug'])) {
                $targetUrl = $baseUrl . '/landing_pages/' . $row['landing_slug'] . '.html';
            }

            // Append UTM params
            if (!empty($targetUrl)) {
                $sep = (strpos($targetUrl, '?') !== false) ? '&' : '?';
                $groupTag = 'fb_gr_' . preg_replace('/[^a-z0-9]/', '', strtolower($row['group_name']));
                $targetUrl .= $sep . 'zdroj=' . urlencode($groupTag) . '&utm_source=facebook&utm_medium=group&utm_campaign=' . urlencode($row['landing_slug'] ?: 'post');
            }

            $result[] = [
                'task_id' => intval($row['task_id']),
                'scheduled_at' => $row['scheduled_at'],
                'group' => [
                    'id' => intval($row['group_id']),
                    'name' => $row['group_name'],
                    'url' => $row['group_url'],
                    'category' => $row['group_category'],
                    'notes' => $row['group_notes'],
                ],
                'post' => [
                    'template_title' => $row['template_title'],
                    'landing_slug' => $row['landing_slug'],
                    'text' => $row['post_text'],
                    'target_url' => $targetUrl,
                    'image_url' => $row['image_url'],
                ]
            ];
        }

        echo json_encode([
            'success' => true,
            'count' => count($result),
            'tasks' => $result
        ]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------------------------
// 10. AGENT: complete_task
// -------------------------------------------------------------
if ($action === 'complete_task') {
    $taskId = intval($input['task_id'] ?? 0);
    $status = trim($input['status'] ?? 'publikovano');
    $log = trim($input['log_message'] ?? 'Publikováno přes Antigravity Browser Agent');

    if (!$taskId) {
        echo json_encode(['success' => false, 'message' => 'Chybí task_id']);
        exit;
    }

    try {
        $publishedAt = ($status === 'publikovano') ? date('Y-m-d H:i:s') : null;
        $stmt = $pdo->prepare("
            UPDATE fb_schedule_tasks 
            SET status = ?, 
                published_at = ?,
                log_message = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$status, $publishedAt, $log, $taskId]);
        echo json_encode(['success' => true]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Neznámá akce']);
