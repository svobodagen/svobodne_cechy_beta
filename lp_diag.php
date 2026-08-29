<?php
// Temporary diagnostic - lists landing page files on server
$dir = __DIR__ . "/admin/landing_pages";
$files = array_diff(scandir($dir), ['.', '..']);
header('Content-Type: application/json');
$result = [];
foreach ($files as $f) {
    $path = $dir . "/" . $f;
    $result[] = [
        'name' => $f,
        'size' => filesize($path),
        'modified' => date('Y-m-d H:i:s', filemtime($path))
    ];
}
echo json_encode($result, JSON_PRETTY_PRINT);
