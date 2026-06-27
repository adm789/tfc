<?php
error_reporting(0);
date_default_timezone_set('Asia/Taipei');

$dataFile = 'some.htm';
$oldFile  = 'guest_data.txt';

function linkify($text) {
    $imgExts = 'jpe?g|png|gif|webp|svg|bmp';
    $parts    = preg_split('/(<[^>]+>)/s', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    $result   = '';
    $inAnchor = false;
    foreach ($parts as $part) {
        if (preg_match('/^<[^>]+>$/s', $part)) {
            if (preg_match('/^<a[\s>]/i',  $part)) $inAnchor = true;
            if (preg_match('/^<\/a\s*>/i', $part)) $inAnchor = false;
            $result .= $part;
        } elseif ($inAnchor) {
            $result .= $part;
        } else {
            $result .= preg_replace_callback(
                '/(https?:\/\/[^\s<>"\']+)/i',
                function($m) use ($imgExts) {
                    $url      = $m[1];
                    $cleanUrl = preg_replace('/\?.*$/', '', $url);
                    if (preg_match('/\.(' . $imgExts . ')$/i', $cleanUrl)) {
                        return '<img src="' . htmlspecialchars($url, ENT_QUOTES)
                             . '" style="max-width:100%;display:block;margin:4px 0" alt="">';
                    }
                    return '<a href="' . htmlspecialchars($url, ENT_QUOTES)
                         . '" target="_blank" rel="noopener">'
                         . htmlspecialchars($url, ENT_QUOTES) . '</a>';
                },
                $part
            );
        }
    }
    return $result;
}

function processMsg($msg) {
    // Step 1: 補引號 + 清理 src/href + 修正 <img> 亂用 target/rel
    $msg = preg_replace_callback(
        '/<(\w+)((?:\s[^>]*)?)>/s',
        function($tm) {
            $tag   = $tm[1];
            $attrs = isset($tm[2]) ? $tm[2] : '';
            $attrs = preg_replace('/(\w[\w-]*)=(?!["\'])([^\s>]+)/', '$1="$2"', $attrs);
            $attrs = preg_replace_callback(
                '/((?:src|href)=")([^"]*?)(")/i',
                function($am) {
                    $val = $am[2];
                    if (preg_match('/^file:\/\//i', $val))         return $am[1].'#local'.$am[3];
                    if (preg_match('/^[a-zA-Z]:[\\\\\/]/', $val))  return $am[1].'#local'.$am[3];
                    $val = preg_replace('/^[a-z]{1,6}(https?:\/\/)/i', '$1', $val);
                    $val = preg_replace('/@\w+$/', '', $val);
                    return $am[1].$val.$am[3];
                },
                $attrs
            );
            if (strtolower($tag) === 'img') {
                $attrs = preg_replace('/\s+(?:target|rel)="[^"]*"/', '', $attrs);
            }
            return '<'.$tag.$attrs.'>';
        },
        $msg
    );
    // Step 2: 白名單
    $allowed = '<a><b><i><u><s><em><strong><br><p><img><ul><ol><li>'
             . '<h1><h2><h3><h4><blockquote><code><pre><span>'
             . '<table><tr><td><th>';
    $msg = strip_tags($msg, $allowed);
    // Step 3: 修正 <img ...> URL </a> 錯誤結構
    $msg = preg_replace('/<img([^>]*)>\s*https?:\/\/[^\s<]*\s*<\/a>/i', '<img$1>', $msg);
    // Step 4: 本機佔位 → 提示文字
    $msg = preg_replace(
        '/<img\s[^>]*src="#local"[^>]*>/i',
        '<span style="color:#999;font-size:12px;font-style:italic">[本機圖片，網頁無法顯示]</span>',
        $msg
    );
    // Step 5: 文字節點裸 URL → 連結/圖片
    $msg = linkify($msg);
    return $msg;
}

function importOldTxt($path) {
    if (!file_exists($path)) return [];
    $raw    = file_get_contents($path);
    $blocks = array_filter(explode("[[BEGIN]]\n", $raw));
    $entries = [];
    foreach ($blocks as $block) {
        $block = str_replace("[[END]]\n", '', $block);
        $block = trim($block);
        if ($block === '') continue;
        $lines   = explode("\n", $block, 2);
        $header  = isset($lines[0]) ? $lines[0] : '';
        $msgBody = isset($lines[1]) ? trim($lines[1]) : '';
        $msgBody = html_entity_decode($msgBody, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $msgBody = processMsg($msgBody);
        $entries[] = '<div class="msg-box">'.$header."\n".$msgBody.'</div><!--MSG-->';
    }
    return $entries;
}

function readHtm($path) {
    if (!file_exists($path)) return [];
    $raw = file_get_contents($path);
    preg_match_all('/<div class="msg-box">(.*?)<\/div><!--MSG-->/s', $raw, $m);
    $entries = [];
    foreach ($m[1] as $inner) {
        $entries[] = '<div class="msg-box">'.$inner.'</div><!--MSG-->';
    }
    return $entries;
}

function writeHtm($path, array $entries) {
    $body = implode("\n", $entries);
    $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Guestbook Messages</title>
<style>
  body{font-family:sans-serif;max-width:620px;margin:auto;padding:20px;line-height:1.8;}
  .msg-box{border-bottom:2px solid #eee;padding:14px 0;white-space:pre-wrap;word-wrap:break-word;}
  b{color:#e44;}a{color:#07c;}img{max-width:100%;}
</style>
</head>
<body>
{$body}
</body>
</html>
HTML;
    file_put_contents($path, $html, LOCK_EX);
}

if (!file_exists($dataFile) && file_exists($oldFile)) {
    writeHtm($dataFile, importOldTxt($oldFile));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name'])    ? trim($_POST['name'])    : '訪客';
    $msg  = isset($_POST['message']) ? trim($_POST['message']) : '';
    if ($msg !== '') {
        $time     = date('Y-m-d H:i:s');
        $safeName = strip_tags($name);
        $safeMsg  = processMsg($msg);
        $header   = "[{$time}] <b>{$safeName}</b>:";
        $entry    = '<div class="msg-box">'.$header."\n".$safeMsg."\n".'</div><!--MSG-->';
        $existing = readHtm($dataFile);
        array_unshift($existing, $entry);
        writeHtm($dataFile, $existing);
    }
    header("Location: guestbook.php"); exit;
}

$entries = readHtm($dataFile);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Guestbook</title>
    <style>
        body      {font-family:sans-serif;max-width:600px;margin:auto;padding:20px;line-height:1.8;}
        .msg-box  {border-bottom:2px solid #eee;padding:14px 0;white-space:pre-wrap;word-wrap:break-word;}
        .form-area{background:#f9f9f9;padding:15px;border-radius:8px;margin-bottom:30px;}
        b{color:#e44;}a{color:#07c;}img{max-width:100%;}
        textarea{font-family:monospace;font-size:13px;}
        .hint{font-size:11px;color:#999;margin-top:4px;}
    </style>
</head>
<body>
    <div class="form-area">
        <form method="POST">
            Name: <input type="text" name="name" required style="width:80%"><br><br>
            Msg：<br>
            <textarea name="message" style="width:100%;height:110px" required></textarea>
            <div class="hint">
                支援 HTML 標籤（&lt;a href=...&gt; &lt;img src=...&gt; &lt;b&gt; 等）。<br>
                裸露網址自動轉連結；圖片網址（.jpg/.png 等）自動顯示圖片。<br>
                本機路徑（file:/// 或 X:\...）會顯示提示，無法在網頁顯示。
            </div><br>
            <button type="submit" style="padding:5px 20px;">送出留言</button>
        </form>
    </div>
    <h3>最新留言：</h3>
    <?php foreach ($entries as $e): echo $e; endforeach; ?>
</body>
</html>
