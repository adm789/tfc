<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的文件夹列表 - 云盘 & InfinityFree</title>
    <style>
        body {
            font-family: "Microsoft YaHei", Arial, sans-serif;
            background: #f4f7fa;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #1a73e8;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #1a73e8;
            color: white;
        }
        tr:hover {
            background: #f1f1f1;
        }
        a {
            color: #1a73e8;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .folder { color: #e67e22; font-weight: bold; }
        .file { color: #2e7d32; }
        .info {
            text-align: center;
            color: #666;
            margin: 15px 0;
        }
        .back {
            display: inline-block;
            margin-bottom: 15px;
            padding: 8px 16px;
            background: #1a73e8;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📁 我的文件夹与文件列表</h1>
        <p class="info">支持 InfinityFree、各类云盘中转、个人网盘等平台使用</p>

        <?php
        // 当前目录路径
        $current_dir = isset($_GET['dir']) ? realpath($_GET['dir']) : getcwd();
        if (!$current_dir || !is_dir($current_dir)) {
            $current_dir = getcwd();
        }

        // 安全限制：只能访问当前脚本所在目录及其子目录
        $base_dir = getcwd();
        if (strpos($current_dir, $base_dir) !== 0) {
            $current_dir = $base_dir;
        }

        // 返回上级目录链接
        $parent_dir = dirname($current_dir);
        if ($parent_dir !== $current_dir && strpos($parent_dir, $base_dir) === 0) {
            echo '<a href="?dir=' . urlencode($parent_dir) . '" class="back">← 返回上级目录</a>';
        } else {
            echo '<a href="?" class="back">← 返回根目录</a>';
        }

        echo '<h2>当前路径：' . htmlspecialchars(str_replace($base_dir, '', $current_dir) ?: '根目录') . '</h2>';

        // 读取目录内容
        $items = scandir($current_dir);
        $folders = [];
        $files = [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $full_path = $current_dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($full_path)) {
                $folders[] = $item;
            } else {
                $files[] = $item;
            }
        }

        // 排序
        sort($folders);
        sort($files);

        echo '<table>';
        echo '<tr><th>类型</th><th>名称</th><th>大小</th><th>修改时间</th></tr>';

        // 显示文件夹
        foreach ($folders as $folder) {
            $full_path = $current_dir . DIRECTORY_SEPARATOR . $folder;
            $mod_time = date('Y-m-d H:i', filemtime($full_path));
            $relative = urlencode(str_replace($base_dir . DIRECTORY_SEPARATOR, '', $full_path));
            echo '<tr>';
            echo '<td>📁</td>';
            echo '<td><a href="?dir=' . $relative . '" class="folder">' . htmlspecialchars($folder) . '</a></td>';
            echo '<td>—</td>';
            echo '<td>' . $mod_time . '</td>';
            echo '</tr>';
        }

        // 显示文件
        foreach ($files as $file) {
            $full_path = $current_dir . DIRECTORY_SEPARATOR . $file;
            $size = filesize($full_path);
            $mod_time = date('Y-m-d H:i', filemtime($full_path));
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            // 根据文件类型显示图标
            $icon = '📄';
            if (in_array($ext, ['jpg','png','gif','webp','jpeg'])) $icon = '🖼️';
            elseif (in_array($ext, ['mp4','webm','mov'])) $icon = '🎥';
            elseif (in_array($ext, ['mp3','wav'])) $icon = '🎵';
            elseif ($ext === 'pdf') $icon = '📕';

            $download_link = str_replace($base_dir . DIRECTORY_SEPARATOR, '', $full_path);
            echo '<tr>';
            echo '<td>' . $icon . '</td>';
            echo '<td><a href="' . htmlspecialchars($download_link) . '" class="file" target="_blank">' . htmlspecialchars($file) . '</a></td>';
            echo '<td>' . number_format($size / 1024, 2) . ' KB</td>';
            echo '<td>' . $mod_time . '</td>';
            echo '</tr>';
        }

        echo '</table>';

        if (empty($folders) && empty($files)) {
            echo '<p style="text-align:center; color:#999;">此目录为空</p>';
        }
        ?>

        <div class="info">
            <small>提示：点击文件夹名称可进入子目录，点击文件可直接查看/下载。<br>
            此页面仅用于个人查看文件夹列表，请勿用于非法用途。</small>
        </div>
    </div>
</body>
</html>