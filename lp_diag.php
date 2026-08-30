<?php
// Temporary diagnostic - lists landing page files on server (extended)
$rootDir = __DIR__;
$lpDir = $rootDir . "/admin/landing_pages";
$uploadsDir = $rootDir . "/uploads";

$result = [
    'server_time' => date('Y-m-d H:i:s'),
    'lp_dir_exists' => is_dir($lpDir),
    'lp_files' => [],
    'uploads_dir_exists' => is_dir($uploadsDir),
    'uploads_files' => [],
    'root_files_landing' => [],
];

if (is_dir($lpDir)) {
    foreach (array_diff(scandir($lpDir), ['.', '..']) as $f) {
        $path = $lpDir . "/" . $f;
        $result['lp_files'][] = [
            'name' => $f,
            'size' => filesize($path),
            'modified' => date('Y-m-d H:i:s', filemtime($path))
        ];
    }
}

if (is_dir($uploadsDir)) {
    foreach (array_diff(scandir($uploadsDir), ['.', '..']) as $f) {
        $result['uploads_files'][] = $f;
    }
}

// Also look for any .html files in root or landing_pages subdir
foreach (glob($rootDir . "/*.html") as $f) {
    if (strpos($f, 'mittner') !== false || strpos($f, 'pacinek') !== false) {
        $result['root_files_landing'][] = basename($f);
    }
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
