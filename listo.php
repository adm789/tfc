<?php
// 设置搜索的根目录
$dir = "."; 
$result = [];

// 递归遍历文件夹
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($iterator as $file) {
    if ($file->isFile()) {
        $path = $file->getPathname();
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        // 匹配媒体类型
        $type = "";
        if (in_array($ext, ['mp4', 'webm', 'ogg', 'mov'])) $type = "vid";
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) $type = "img";

        if ($type !== "") {
            // 格式化路径，移除前面的 "./" 并统一斜杠
            $cleanPath = str_replace('\\', '/', substr($path, 2));
            $result[] = ["t" => $type, "s" => $cleanPath];
        }
    }
}

// 输出为 JavaScript 文件内容
$jsContent = "const mediaList = " . json_encode($result, JSON_UNESCAPED_UNICODE) . ";";
file_put_contents("list.js", $jsContent);

echo "list.js 已成功生成，包含 " . count($result) . " 个媒体文件。";
?>