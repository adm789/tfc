<?php
$target = isset($_GET['dir']) ? $_GET['dir'] : 'cn';
$files = array();
if (is_dir($target)) {
    $dir_content = scandir($target);
    foreach ($dir_content as $f) {
        if ($f == '.' || $f == '..') continue;
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        $type = '';
        if ($ext == 'mp4' || $ext == 'webm') {
            $type = 'vid';
        } elseif (in_array($ext, array('jpg', 'jpeg', 'png', 'webp', 'avif'))) {
            $type = 'img';
        }
        if ($type != '') {
            $files[] = array("t" => $type, "s" => $target . "/" . $f);
        }
    }
}
$json = json_encode($files);
file_put_contents('list.js', 'const mediaList = ' . $json . ';');
echo "Done: " . count($files) . " items.";
?>