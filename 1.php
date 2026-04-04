<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <title>My Final Manager</title>
    <style>
        body { background: #111; color: #ccc; font-family: "Segoe UI", sans-serif; margin: 0; padding: 15px; }
        h2 { font-size: 1.1em; color: #0ff; border-bottom: 1px solid #333; padding: 10px 0 5px 0; margin: 15px 0; display: flex; justify-content: space-between; align-items: center; }
        .sort-btns a { color: #0ff; text-decoration: none; margin-left: 10px; border: 1px solid #0ff; padding: 2px 5px; border-radius: 3px; font-size: 12px; }
        
        /* 圖片牆 */
        .img-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; }
        .img-item { display: block; aspect-ratio: 1/1; background: #222; border: 1px solid #333; overflow: hidden; border-radius: 8px; }
        .img-item img { width: 100%; height: 100%; object-fit: cover; }

        /* 檔案與資料夾列表 */
        .file-list { width: 100%; border-collapse: collapse; font-size: 13px; }
        .file-list tr { border-bottom: 1px solid #222; }
        .file-list td { padding: 8px; vertical-align: middle; }
        .file-list a { color: #aaa; text-decoration: none; }
        .play-btn { background: #0ff; border: none; cursor: pointer; padding: 3px 10px; border-radius: 2px; font-weight: bold; }
        .delay-input { width: 50px; background: #222; color: #0ff; border: 1px solid #444; margin-right: 5px; }
    </style>
    <script>
        function playMedia(dirName) {
            let delay = document.getElementById('delay-' + dirName).value;
            window.open('play.php?dir=' + encodeURIComponent(dirName) + '&delay=' + delay, '_blank');
        }
    </script>
</head>
<body>

<?php
$sort_mode = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$all = array_diff(scandir('.'), array('.', '..', '1.php', '.htaccess', 'index.php', 'play.php'));

$folders = []; $images = []; $others = [];

foreach ($all as $f) {
    if (is_dir($f)) {
        $folders[] = $f;
    } elseif (@getimagesize($f)) {
        $images[] = $f;
    } else {
        $others[] = [
            'name' => $f,
            'size' => filesize($f),
            'ext' => strtolower(pathinfo($f, PATHINFO_EXTENSION))
        ];
    }
}

// 列表排序邏輯
if ($sort_mode == 'size') {
    usort($others, function($a, $b) { return $b['size'] - $a['size']; });
}
?>

<h2>📁 資料夾播放 (Sub-Folders)</h2>
<table class="file-list">
    <?php foreach ($folders as $dir): ?>
    <tr>
        <td style="width:30px">📁</td>
        <td><b><?php echo $dir; ?></b></td>
        <td style="text-align:right">
            <input type="number" id="delay-<?php echo $dir; ?>" class="delay-input" value="3000">
            <button class="play-btn" onclick="playMedia('<?php echo $dir; ?>')">Play</button>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<h2>🖼️ 圖片預覽 (Images)</h2>
<div class="img-grid">
    <?php foreach ($images as $img) echo '<a href="'.$img.'" target="_blank" class="img-item"><img src="'.$img.'"></a>'; ?>
</div>

<h2>
    📄 其他檔案 (Files)
    <div class="sort-btns">
        <a href="?sort=name">按名稱</a> <a href="?sort=size">按大小</a>
    </div>
</h2>
<table class="file-list">
    <?php foreach ($others as $file): 
        $size_txt = ($file['size'] >= 1048576) ? round($file['size'] / 1048576, 2) . ' MB' : round($file['size'] / 1024, 1) . ' KB';
    ?>
    <tr>
        <td style="width:30px"><?php echo (in_array($file['ext'], ['mp4','webm'])) ? '▶' : '📄'; ?></td>
        <td><a href="<?php echo $file['name']; ?>" target="_blank"><?php echo $file['name']; ?></a></td>
        <td style="text-align:right; color:#666; font-family:monospace;"><?php echo $size_txt; ?></td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>