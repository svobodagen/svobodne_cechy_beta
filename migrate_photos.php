<?php
require_once 'db.php';

echo "Sstarting migration...\n";

function save_base64_as_file($base64, $subfolder = 'masters/')
{
    if (!$base64 || !is_string($base64))
        return $base64;
    if (strpos($base64, 'uploads/') === 0)
        return $base64;
    if (strpos($base64, 'data:') !== 0)
        return $base64;

    try {
        if (!strpos($base64, ';'))
            return $base64;
        list($type, $data) = explode(';', $base64);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);

        $uploadDir = 'uploads/' . $subfolder;
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0777, true);

        $ext = 'jpg';
        if (strpos($type, 'png') !== false)
            $ext = 'png';
        if (strpos($type, 'gif') !== false)
            $ext = 'gif';
        if (strpos($type, 'webp') !== false)
            $ext = 'webp';

        $filename = 'file_' . uniqid() . '.' . $ext;
        $filepath = $uploadDir . $filename;

        file_put_contents($filepath, $data);
        return $filepath;
    } catch (Exception $e) {
        return $base64;
    }
}

$stmt = $pdo->query("SELECT * FROM masters");
$masters = $stmt->fetchAll();

foreach ($masters as $m) {
    echo "Processing master: " . $m['name'] . "\n";
    $updated = false;

    // Process main photo
    if ($m['photo'] && strpos($m['photo'], 'data:') === 0) {
        $m['photo'] = save_base64_as_file($m['photo'], 'masters/');
        $updated = true;
    }

    // Process gallery
    if ($m['gallery']) {
        $gallery = json_decode($m['gallery'], true);
        if (is_array($gallery)) {
            $galleryChanged = false;
            foreach ($gallery as &$item) {
                if (isset($item['path']) && strpos($item['path'], 'data:') === 0) {
                    $item['path'] = save_base64_as_file($item['path'], 'gallery/');
                    $galleryChanged = true;
                }
            }
            if ($galleryChanged) {
                $m['gallery'] = json_encode($gallery);
                $updated = true;
            }
        }
    }

    if ($updated) {
        $updateStmt = $pdo->prepare("UPDATE masters SET photo = ?, gallery = ? WHERE id = ?");
        $updateStmt->execute([$m['photo'], $m['gallery'], $m['id']]);
        echo " - Updated!\n";
    }
}

echo "Migration finished.\n";
