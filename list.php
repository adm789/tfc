<?php
// 錯誤回報開啟，方便在 WinPE 除錯
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/javascript; charset=utf-8'); 

// 優先順序：1. 命令列參數 > 2. 網頁 GET 參數 > 3. 當前檔案目錄
$targetDir = __DIR__; // 這會讓腳本永遠掃描它自己所在的那個資料夾 ok via php.exe

if (php_sapi_name() == "cli" && isset($argv[1])) {
    // 處理來自 CMD 的第一個參數
    $targetDir = $argv[1];
} elseif (isset($_GET['dir'])) {
    $targetDir = $_GET['dir'];
}

$realTarget = realpath($targetDir);
// ... 後續掃描邏輯相同 ...

// 設定掃描起點，預設為當前目錄 Must@browser tho..
//$targetDir = isset($_GET['dir']) ? $_GET['dir'] : '.';
//$targetDir = __DIR__; 
//$realTarget = realpath($targetDir);

$mediaList = [];

if ($realTarget && is_dir($realTarget)) {
    // 使用 RecursiveIterator 進行深度掃描
    $directory = new RecursiveDirectoryIterator($realTarget, RecursiveDirectoryIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($directory);

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $filename = $file->getFilename();
            $extension = strtolower($file->getExtension());
            
            // 定義支援的副檔名
            $videoExts = ['mp4', 'webm', 'mov', 'avi'];
            $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            $type = '';
            if (in_array($extension, $videoExts)) {
                $type = 'vid';
            } elseif (in_array($extension, $imageExts)) {
                $type = 'img';
            }

            // 如果是支援的媒體類型才加入清單
            if ($type !== '') {
                // 取得相對路徑
                $relativePath = str_replace($realTarget . DIRECTORY_SEPARATOR, '', $file->getPathname());
                
                // 統一將 Windows 反斜線轉為正斜線 /
                $normalizedPath = str_replace('\\', '/', $relativePath);
                
                // 對路徑中的各段進行 URL 編碼，但保留斜線 /
                $parts = explode('/', $normalizedPath);
                $encodedParts = array_map('rawurlencode', $parts);
                $finalPath = implode('/', $encodedParts);

                $mediaList[] = [
                    "t" => $type,
                    "s" => $finalPath
                ];
            }
        }
    }
}

// 輸出為 list.js 格式
echo "const mediaList = " . json_encode($mediaList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ";";
?>