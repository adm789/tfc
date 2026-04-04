<?php
ob_start();
header('Content-Type: application/javascript; charset=utf-8');

// 獲取目錄參數，預設為當前目錄
$target = isset($_GET['dir']) ? $_GET['dir'] : '.';
$dir = rtrim($target, '/\\');

// 遞歸掃描指定目錄，支援多層級，避免路徑缺失
function scanMediaFiles($baseDir) {
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'mov'];
    $results = [];

    // 使用 RecursiveDirectoryIterator 遞歸掃描
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $ext = strtolower($file->getExtension());
            if (in_array($ext, $allowedExtensions)) {
                // 獲取相對路徑，並轉換為 URL 安全格式
                $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $file->getPathname());
                // 對每個路徑部分進行編碼，保留目錄結構
                $parts = explode('/', $relativePath);
                $encodedParts = array_map('rawurlencode', $parts);
                $encodedPath = implode('/', $encodedParts);
                $results[] = $encodedPath;
            }
        }
    }

    return $results;
}

// 掃描目錄
$mediaFiles = scanMediaFiles($dir);

// 構建媒體列表對象，自動識別類型
$mediaList = [];
foreach ($mediaFiles as $filePath) {
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $type = in_array($ext, ['mp4', 'webm', 'mov']) ? 'vid' : 'img';
    $mediaList[] = [
        't' => $type,
        's' => $filePath
    ];
}

// 輸出 JavaScript 變量
echo "const mediaList = " . json_encode($mediaList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ";";
ob_end_flush();
?>