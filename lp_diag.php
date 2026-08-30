<?php
// Emergency restore + diagnostic tool
$action = $_GET['action'] ?? 'info';
$dir = __DIR__ . "/admin/landing_pages";

header('Content-Type: application/json');

if ($action === 'restore_pacinek') {
    // Read the JSON from git-tracked file
    $jsonFile = $dir . "/jiri-pacinek.json";
    if (!file_exists($jsonFile)) {
        echo json_encode(['error' => 'JSON not found at: ' . $jsonFile]);
        exit;
    }
    
    // Include the landing_pages.php to get renderLandingPageHtml function
    // but we need to avoid its output - so we'll buffer
    ob_start();
    $_SERVER['REQUEST_METHOD'] = 'GET'; // prevent POST actions
    $_GET['edit'] = 'jiri-pacinek';
    // We'll just trigger the save action by calling the render function directly
    ob_end_clean();
    
    // Simplest approach: POST to ourselves as if saving
    $jsonData = file_get_contents($jsonFile);
    $data = json_decode($jsonData, true);
    
    // Check if renderLandingPageHtml function exists by including the file
    // Actually, let's just write back the HTML from the existing html file if it exists
    $htmlFile = $dir . "/jiri-pacinek.html";
    
    echo json_encode([
        'json_exists' => file_exists($jsonFile),
        'html_exists' => file_exists($htmlFile),
        'json_size' => filesize($jsonFile),
        'html_size' => file_exists($htmlFile) ? filesize($htmlFile) : 0,
        'dir' => $dir,
        'dir_exists' => is_dir($dir),
        'dir_writable' => is_writable($dir),
        '__dir__' => __DIR__,
        'ls_admin' => array_diff(scandir(__DIR__ . "/admin") ?: [], ['.', '..']),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Default info
echo json_encode([
    '__dir__' => __DIR__,
    'lp_dir' => $dir,
    'lp_dir_exists' => is_dir($dir),
    'lp_dir_writable' => is_writable($dir),
    'lp_files' => is_dir($dir) ? array_diff(scandir($dir), ['.', '..']) : [],
    'admin_dir_exists' => is_dir(__DIR__ . "/admin"),
    'admin_contents' => array_diff(scandir(__DIR__ . "/admin") ?: [], ['.', '..']),
    'server_time' => date('Y-m-d H:i:s'),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
