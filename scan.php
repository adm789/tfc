<?php
// scan.php — 遞迴掃描圖片目錄，回傳 JSON
// GET ?dir=img/q1   掃指定子目錄（相對於本檔位置）
// GET ?dir=         或不帶參數 → 掃預設 img/ 目錄
// 回傳格式：
// { "base": "img/q1/", "images": ["001.jpg","002.jpg",...],
//   "byFolder": { "img/q1/sub/": ["a.jpg"] } }

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$IMG_EXTS = ['jpg','jpeg','png','gif','webp','svg','bmp'];

// 安全：限制只能在當前目錄下
$root    = realpath(__DIR__);
$reqDir  = isset($_GET['dir']) ? trim($_GET['dir'], '/\\ ') : 'img';
if ($reqDir === '') $reqDir = 'img';

$target = realpath($root . '/' . $reqDir);

// 防止路徑穿越
if ($target === false || strpos($target, $root) !== 0) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid dir']);
    exit;
}

if (!is_dir($target)) {
    http_response_code(404);
    echo json_encode(['error' => 'dir not found', 'tried' => $reqDir]);
    exit;
}

$allImages  = [];   // 全部圖片（相對路徑）
$byFolder   = [];   // 依子目錄分組

$rit = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($rit as $file) {
    if (!$file->isFile()) continue;
    $ext = strtolower($file->getExtension());
    if (!in_array($ext, $IMG_EXTS)) continue;

    // 相對路徑（相對於 __DIR__），用 / 分隔
    $rel     = str_replace('\\', '/', substr($file->getPathname(), strlen($root)+1));
    $folder  = str_replace('\\', '/', substr($file->getPath(),     strlen($root)+1)) . '/';
    $name    = $file->getFilename();

    $allImages[] = $rel;
    if (!isset($byFolder[$folder])) $byFolder[$folder] = [];
    $byFolder[$folder][] = $name;
}

sort($allImages);
foreach ($byFolder as &$arr) sort($arr);
ksort($byFolder);

echo json_encode([
    'base'     => $reqDir . '/',
    'total'    => count($allImages),
    'images'   => $allImages,
    'byFolder' => $byFolder,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
