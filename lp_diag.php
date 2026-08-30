<?php
// Download JSON content of any slug passed as ?slug=xxx
$dir = __DIR__ . "/admin/landing_pages";
$slug = $_GET['slug'] ?? '';
$action = $_GET['action'] ?? 'list';

header('Content-Type: application/json');

if ($action === 'getjson' && $slug) {
    $file = $dir . "/" . $slug . ".json";
    if (file_exists($file)) {
        echo file_get_contents($file);
    } else {
        echo json_encode(['error' => 'not found', 'path' => $file]);
    }
    exit;
}

// List all files with sizes
$files = is_dir($dir) ? array_diff(scandir($dir), ['.', '..']) : [];
$result = [];
foreach ($files as $f) {
    $path = $dir . "/" . $f;
    $result[] = ['name' => $f, 'size' => filesize($path)];
}

// Also check uploads/masters subfolder
$mastersDir = __DIR__ . "/uploads/masters";
$mastersFiles = [];
if (is_dir($mastersDir)) {
    foreach (array_diff(scandir($mastersDir), ['.', '..']) as $f) {
        $mastersFiles[] = $f;
    }
}

echo json_encode([
    'lp_files' => $result,
    'masters_uploads' => $mastersFiles,
    'lp_dir' => $dir,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
