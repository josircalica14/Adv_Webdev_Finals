<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $results = [];
    $files = $_FILES['files'];

    $fileCount = is_array($files['name']) ? count($files['name']) : 1;

    for ($i = 0; $i < $fileCount; $i++) {
        $name     = is_array($files['name'])  ? $files['name'][$i]     : $files['name'];
        $tmpName  = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
        $size     = is_array($files['size'])  ? $files['size'][$i]     : $files['size'];
        $error    = is_array($files['error']) ? $files['error'][$i]    : $files['error'];

        if ($error !== UPLOAD_ERR_OK) {
            $results[] = ['success' => false, 'name' => $name, 'message' => 'Upload error'];
            continue;
        }

        $safeName = basename($name);
        $dest     = $uploadDir . time() . '_' . $safeName;

        if (move_uploaded_file($tmpName, $dest)) {
            $ext = strtolower(pathinfo($safeName, PATHINFO_EXTENSION));
            $results[] = [
                'success' => true,
                'name'    => $safeName,
                'size'    => $size,
                'ext'     => strtoupper($ext),
                'path'    => $dest,
            ];
        } else {
            $results[] = ['success' => false, 'name' => $name, 'message' => 'Failed to save file'];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}