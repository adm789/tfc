<?php
error_reporting(0);
date_default_timezone_set('Asia/Taipei');

$dataFile = 'some.htm';
$oldFile  = 'guest_data.txt';

// 網址太長（例如帶了一長串 JWT query string）整段沒有空白，
// 顯示出來會撐爆版面。這裡只縮短「顯示文字」，href 仍是完整原始網址，
// 點擊行為不變，只是畫面上看起來是 domain/...xyz 這種簡短樣式。
function shortUrlLabel($url) {
    $threshold = 60;
    if (mb_strlen($url) <= $threshold) {
        return htmlspecialchars($url, ENT_QUOTES);
    }
    $host = parse_url($url, PHP_URL_HOST);
    $tail = mb_substr($url, -3);
    $label = ($host ?: '連結') . '/...' . $tail;
    return htmlspecialchars($label, ENT_QUOTES);
}

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
                '/(https?:\/\/[^\s<>"\'\`，、《》。！？“”]+)/i',
                function($m) use ($imgExts) {
                    $url      = $m[1];
                    $cleanUrl = preg_replace('/\?.*$/', '', $url);
                    if (preg_match('/\.(' . $imgExts . ')$/i', $cleanUrl)) {
                        $esc = htmlspecialchars($url, ENT_QUOTES);
                        return '<a target="_blank" href="'.$esc.'">'
                             . '<img src="'.$esc.'" style="max-height:80px;max-width:160px;'
                             . 'display:inline-block;margin:2px 4px 2px 0;'
                             . 'border:1px solid #ddd;border-radius:3px;cursor:pointer;" alt="img">'
                             . '</a>';
                    }
                    $esc = htmlspecialchars($url, ENT_QUOTES);
                    return '<a target="_blank" href="' . $esc . '">'
                         . shortUrlLabel($url) . '</a>';
                },
                $part
            );
        }
    }
    return $result;
}

// 如果貼進來的內容裡已經包含本站自己產生的長文包裝／複製按鈕標記
// （例如把舊留言的渲染結果複製貼回文字框重新送出），先攤平回乾淨的純文字，
// 讓後面的流程可以重新正常打包、收合，而不是整段跳過或巢狀重複包裝。
function unwrapPackedArtifacts($msg) {
    if (strpos($msg, 'long-text-wrapper') === false
        && strpos($msg, 'btn-cc') === false
        && strpos($msg, 'bottom-del') === false) {
        return $msg; // 沒有偵測到任何本站標記，維持原樣
    }

    // 長文包裝：preview（可能沒有）+ content 兩段皆為 htmlspecialchars 過的純文字，
    // 沒有真正的巢狀標籤，可以安全用非貪婪比對還原成一段純文字。
    $msg = preg_replace_callback(
        '/<div class="long-text-wrapper">'
        . '(?:<div class="long-text-preview">(.*?)<\/div>)?'
        . '<button class="btn-expand"[^>]*>.*?<\/button>'
        . '(?:<div id="[^"]*" class="long-text-content"[^>]*>(.*?)<\/div>)?'
        . '<\/div>/s',
        function($m) {
            $preview = isset($m[1]) ? $m[1] : '';
            $rest    = isset($m[2]) ? $m[2] : '';
            $full = $preview;
            if ($rest !== '') {
                $full .= ($full !== '' ? "\n" : '') . $rest;
            }
            return html_entity_decode($full, ENT_QUOTES, 'UTF-8');
        },
        $msg
    );

    // 複製按鈕、底部刪除按鈕都是操作用 UI，不是留言內容，直接移除
    $msg = preg_replace('/<button class="btn-cc"[^>]*>.*?<\/button>/is', '', $msg);
    $msg = preg_replace('/<div class="bottom-del">.*?<\/form>\s*<\/div>/is', '', $msg);

    return trim($msg);
}

function processMsg($msg, $packLongText = true) {
    $msg = unwrapPackedArtifacts($msg);

    $btnBlocks = [];
    $msg = preg_replace_callback(
        '/<button\b([^>]*)>(.*?)<\/button>/is',
        function($bm) use (&$btnBlocks) {
            $key = '%%BTN'.count($btnBlocks).'%%';
            $html = $bm[0];
            $html = preg_replace('/\(\)=(?!>)/', '()=>', $html);
            $btnBlocks[$key] = $html;
            return $key;
        },
        $msg
    );
    $msg = preg_replace('/<\/button>/i', '', $msg);

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
            
            if (strtolower($tag) === 'button') {
                return '<' . $tag . $tm[2] . '>';
            }
            
            if (strtolower($tag) === 'img') {
                $attrs = preg_replace('/\s+(?:target|rel)="[^"]*"/', '', $attrs);
            } else {
                $attrs = preg_replace('/\s+rel="[^"]*"/', '', $attrs);
                if (preg_match('/(\s+href="[^"]*")(.*?)(\s+target="[^"]*")/s', $attrs, $tm)) {
                    $attrs = str_replace($tm[0], $tm[3].$tm[2].$tm[1], $attrs);
                }
            }
            return '<'.$tag.$attrs.'>';
        },
        $msg
    );
    
    // ===== 長文本打包（新增） =====
    if ($packLongText) {
        $msg = preg_replace_callback(
            '/((?:[^\n]+\n?){8,})/s',  // 8 行以上觸發打包
            function($m) use ($btnBlocks) {
                $longText = trim($m[1]);

                // 若這段長文裡剛好卡著按鈕佔位符（%%BTN..%%），代表使用者貼的內容
                // 本來就含有 <button> 標籤（不論是自己打的，還是貼上舊留言殘留的）。
                // 這裡先把佔位符換回「原始按鈕原始碼」再一起跳脫成純文字顯示，
                // 這樣後面統一還原按鈕的迴圈就找不到這個佔位符了，
                // 不會把真正可執行的 <button> 標籤注入到已跳脫的文字裡，
                // 同時這段文字仍然會正常收合成 2 行預覽（不會整段跳過不處理）。
                if (!empty($btnBlocks) && strpos($longText, '%%BTN') !== false) {
                    foreach ($btnBlocks as $key => $btnHtml) {
                        $longText = str_replace($key, $btnHtml, $longText);
                    }
                }

                // id 不能只用內容的 md5：若兩則留言貼了一模一樣的長文，
                // md5 會相同，document.getElementById 只會抓到第一個，
                // 導致點到第二則的按鈕卻展開/收合到第一則的內容，版面跟著跑掉。
                // 改成每次都附加唯一序號，確保同頁面不會有重複 id。
                $id = 'lt_' . md5($longText) . '_' . substr(str_replace('.', '', uniqid('', true)), -10);

                $allLines = explode("\n", $longText);
                $lineCount = count($allLines);

                // 預設可見前 2 行當預覽，其餘收在展開區塊裡
                $previewLineCount = min(2, $lineCount);
                $previewLines = array_slice($allLines, 0, $previewLineCount);
                $restLines = array_slice($allLines, $previewLineCount);

                $escapedPreview = htmlspecialchars(implode("\n", $previewLines), ENT_QUOTES, 'UTF-8');
                $escapedRest = htmlspecialchars(implode("\n", $restLines), ENT_QUOTES, 'UTF-8');
                $restCount = count($restLines);

                $labelCollapsed = '📄 展開剩餘 ' . $restCount . ' 行（共 ' . $lineCount . ' 行）';
                $labelExpanded  = '📄 收起長文';

                return '<div class="long-text-wrapper">'
                     . '<div class="long-text-preview">' . $escapedPreview . '</div>'
                     . '<button class="btn-expand" onclick="toggleLongText(this)" data-target="' . $id . '"'
                     . ' data-label-collapsed="' . htmlspecialchars($labelCollapsed, ENT_QUOTES, 'UTF-8') . '"'
                     . ' data-label-expanded="' . htmlspecialchars($labelExpanded, ENT_QUOTES, 'UTF-8') . '">'
                     . $labelCollapsed
                     . '</button>'
                     . '<div id="' . $id . '" class="long-text-content" style="display:none;margin-top:6px;padding:8px 12px;background:#f8f8f8;border-radius:4px;white-space:pre-wrap;border-left:3px solid #667eea;font-size:13px;line-height:1.6;">'
                     . $escapedRest
                     . '</div>'
                     . '</div>';
            },
            $msg
        );
    }
    
    $allowed = '<a><b><i><u><s><em><strong><br><p><img><ul><ol><li>'
             . '<h1><h2><h3><h4><blockquote><code><pre><span><div>'
             . '<table><tr><td><th><button>';
    $msg = strip_tags($msg, $allowed);
    
    $msg = preg_replace('/<img([^>]*)>\s*https?:\/\/[^\s<]*\s*<\/a>/i', '<img$1>', $msg);
    $msg = preg_replace(
        '/<img\s[^>]*src="#local"[^>]*>/i',
        '<span style="color:#999;font-size:12px;font-style:italic">[本機圖片，網頁無法顯示]</span>',
        $msg
    );
    
    $msg = linkify($msg);
    
    foreach ($btnBlocks as $key => $btnHtml) {
        $msg = str_replace($key, $btnHtml, $msg);
    }
    if (!empty($btnBlocks)) {
        foreach (array_keys($btnBlocks) as $key) {
            $msg = preg_replace('/^[^<%%]*(?='.preg_quote($key,'/').')/s', '', $msg);
        }
        $msg = trim($msg);
    }
    return $msg;
}

// 判斷這則留言是否需要另存 data-raw（原始輸入）。
// 純文字（無網址、無成對的 < > 區段）不論多長，複製時都能靠「預覽 2 行 + 展開內容」
// 原樣接回去，結果保證跟原始輸入一致，不需要多存一份。
// 只要符合以下任一條件，才需要 data-raw：
//   1. 內含網址 → linkify() 會轉成 <a>/<img>，textContent 抓不到網址本身
//   2. 內含「成對」的 < ... > 區段 → strip_tags() 只有在抓到一個 < 配對到後面
//      最近的 > 時，才會把中間整段當成標籤吃掉；單獨一個 < 或 >（例如指令列裡
//      常見的 2>&1、run.bat > out.log、5 < 10）不會配對，strip_tags 不會動它，
//      所以不需要因為單一符號就觸發。
// 注意：& " ' 也不需要觸發——顯示時是 htmlspecialchars 過的實體字元，
// 瀏覽器讀 .textContent／複製時的手動解碼會自動還原回原字元，結果本來就正確。
function needsRawStorage($originalMsg) {
    if (preg_match('/https?:\/\//i', $originalMsg)) return true;
    if (preg_match('/<[^<>]*>/', $originalMsg)) return true;
    return false;
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
    // 捕獲 data-raw 屬性（原本此處只是非捕獲群組，讀回後會把使用者原始輸入直接丟掉）
    preg_match_all('/<div class="msg-box"( data-raw="[^"]*")?>(.*?)<\/div><!--MSG-->/s', $raw, $m, PREG_SET_ORDER);
    $entries = [];
    foreach ($m as $match) {
        $rawAttr = isset($match[1]) ? $match[1] : '';
        $inner   = $match[2];
        $entries[] = '<div class="msg-box"' . $rawAttr . '>' . $inner . '</div><!--MSG-->';
    }
    return $entries;
}

function writeHtm($path, array $entries) {
    $body = implode("\n", $entries);
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Guestbook Messages</title><style>'
          . 'body{font-family:sans-serif;max-width:620px;margin:auto;padding:20px;line-height:1.8;}'
          . '.msg-box{border-bottom:2px solid #eee;padding:12px 14px;white-space:pre-wrap;word-wrap:break-word;position:relative;}'
          . '.msg-box h1,.msg-box h2,.msg-box h3,.msg-box h4{font-size:1em;color:inherit;margin:0;}'
          . '.msg-box *{max-width:100%;box-sizing:border-box;}'
          . 'b{color:#e44;}a{color:#07c;}img{max-width:100%;}'
          . '.long-text-wrapper{margin:4px 0;}'
          . '.long-text-preview{white-space:pre-wrap;word-wrap:break-word;color:#333;margin-bottom:4px;}'
          . '.btn-expand{font-size:12px;padding:3px 12px;background:#f0f0f0;color:#333;border:1px solid #ddd;border-radius:4px;cursor:pointer;transition:all 0.2s;display:inline-block;}'
          . '.btn-expand:hover{background:#e8e8e8;border-color:#667eea;}'
          . '.btn-expand.active{background:#667eea;color:#fff;border-color:#667eea;}'
          . '.long-text-content{max-height:400px;overflow-y:auto;}'
          . '.btn-cc{display:inline-block;margin:6px 0 2px;padding:4px 14px;background:#2d2d2d;color:#fff;border:none;border-radius:5px;cursor:pointer;font-size:12px;transition:background 0.2s;}'
          . '.btn-cc:hover{background:#444;}'
          . '.btn-cc.ok{background:#4CAF50;}'
          . '</style></head><body>' . "\n" . $body . "\n"
          // ===== 加入必要的 JavaScript，讓 some.htm 獨立開啟時展開/複製按鈕也能動作 =====
          . '<script>'
          . 'function toggleLongText(btn){'
          . 'var targetId=btn.getAttribute("data-target");'
          . 'var content=document.getElementById(targetId);'
          . 'if(!content)return;'
          . 'var collapsedLabel=btn.getAttribute("data-label-collapsed")||"📄 展開長文";'
          . 'var expandedLabel=btn.getAttribute("data-label-expanded")||"📄 收起長文";'
          . 'if(content.style.display==="none"||content.style.display===""){'
          . 'content.style.display="block";'
          . 'btn.textContent=expandedLabel;'
          . 'btn.classList.add("active");'
          . '}else{'
          . 'content.style.display="none";'
          . 'btn.textContent=collapsedLabel;'
          . 'btn.classList.remove("active");'
          . '}'
          . '}'
          . 'function copyPlainText(text,callback){'
          . 'if(navigator.clipboard){'
          . 'navigator.clipboard.writeText(text).then(callback).catch(function(){fallbackCopy(text);if(callback)callback();});'
          . '}else{fallbackCopy(text);if(callback)callback();}'
          . '}'
          . 'function fallbackCopy(text){'
          . 'var ta=document.createElement("textarea");'
          . 'ta.value=text;ta.style.position="fixed";ta.style.opacity="0";'
          . 'document.body.appendChild(ta);ta.focus();ta.select();'
          . 'try{document.execCommand("copy");}catch(e){}'
          . 'document.body.removeChild(ta);'
          . '}'
          . 'function copyContent(btn){'
          . 'var box=btn.closest(".msg-box");'
          . 'var rawContent=box.getAttribute("data-raw");'
          . 'if(rawContent){'
          . 'copyPlainText(rawContent,function(){'
          . 'btn.textContent="✅ 已複製！";btn.classList.add("ok");'
          . 'setTimeout(function(){btn.textContent="📋 複製內容";btn.classList.remove("ok");},2000);'
          . '});return;'
          . '}'
          . 'var clone=box.cloneNode(true);'
          . 'clone.querySelectorAll(".btn-cc,.bottom-del").forEach(function(el){el.remove();});'
          . 'clone.querySelectorAll(".long-text-wrapper").forEach(function(wrapper){'
          . 'var preview=wrapper.querySelector(".long-text-preview");'
          . 'var content=wrapper.querySelector(".long-text-content");'
          . 'var previewText=preview?preview.textContent:"";'
          . 'var restText=content?content.textContent:"";'
          . 'var full=restText?(previewText+"\\n"+restText):previewText;'
          . 'var textNode=document.createTextNode(full);'
          . 'wrapper.replaceWith(textNode);'
          . '});'
          . 'var html=clone.innerHTML;'
          . 'html=html.replace(/<br\\s*\\/?>/gi,"\\n");'
          . 'html=html.replace(/<[^>]+>/g,"");'
          . 'html=html.replace(/&amp;/g,"&").replace(/&lt;/g,"<").replace(/&gt;/g,">").replace(/&nbsp;/g," ").replace(/&#39;/g,"\'").replace(/&quot;/g,\'"\');'
          . 'var lines=html.trim().split("\\n");'
          . 'if(lines.length>0&&/^\\[\\d{4}-\\d{2}-\\d{2}/.test(lines[0].trim())){lines.shift();}'
          . 'var text=lines.join("\\n").replace(/\\n{3,}/g,"\\n\\n").trim();'
          . 'copyPlainText(text,function(){'
          . 'btn.textContent="✅ 已複製！";btn.classList.add("ok");'
          . 'setTimeout(function(){btn.textContent="📋 複製內容";btn.classList.remove("ok");},2000);'
          . '});'
          . '}'
          . 'document.addEventListener("DOMContentLoaded",function(){'
          . 'document.querySelectorAll(".long-text-wrapper").forEach(function(wrapper){'
          . 'var content=wrapper.querySelector(".long-text-content");'
          . 'var btn=wrapper.querySelector(".btn-expand");'
          . 'if(!content||!btn)return;'
          . 'var isCollapsed=(content.style.display==="none");'
          . 'if(isCollapsed){'
          . 'btn.textContent=btn.getAttribute("data-label-collapsed")||btn.textContent;'
          . 'btn.classList.remove("active");'
          . '}else{'
          . 'btn.textContent=btn.getAttribute("data-label-expanded")||"📄 收起長文";'
          . 'btn.classList.add("active");'
          . '}'
          . '});'
          . '});'
          . '</script>'
          . '</body></html>';
    file_put_contents($path, $html, LOCK_EX);
}

if (!file_exists($dataFile) && file_exists($oldFile)) {
    writeHtm($dataFile, importOldTxt($oldFile));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $idx      = isset($_POST['idx']) ? intval($_POST['idx']) : -1;
        $existing = readHtm($dataFile);
        if ($idx >= 0 && $idx < count($existing)) {
            array_splice($existing, $idx, 1);
            writeHtm($dataFile, $existing);
        }
        header("Location: 1gbk.php"); exit;
    }

    // 重建舊留言的長文收合格式：用目前最新的 processMsg() 邏輯，
    // 把還留有 data-raw（原始輸入）的舊留言重新產生一次 HTML，
    // 修正它們在舊版程式碼下產生的收合/展開顯示問題。
    // 不影響已經沒有 data-raw 的更早期匯入留言（保持原樣）。
    if (isset($_POST['action']) && $_POST['action'] === 'rebuild') {
        $existing = readHtm($dataFile);
        $rebuilt = [];
        foreach ($existing as $e) {
            if (preg_match('/^<div class="msg-box" data-raw="([^"]*)">(.*)<\/div><!--MSG-->$/s', $e, $m)) {
                $originalMsg = htmlspecialchars_decode($m[1], ENT_QUOTES);
                $inner = $m[2];

                $header = '';
                if (preg_match('/^(\[[^\]]+\]\s*<b>.*?<\/b>:)\n/s', $inner, $hm)) {
                    $header = $hm[1];
                }
                $hadCopyBtn = strpos($inner, 'btn-cc') !== false;

                $safeMsg = processMsg($originalMsg, true);
                if ($hadCopyBtn) {
                    $safeMsg .= "\n" . '<button class="btn-cc" onclick="copyContent(this)">📋 複製內容</button>';
                }

                $rawAttr = needsRawStorage($originalMsg)
                    ? ' data-raw="' . htmlspecialchars($originalMsg, ENT_QUOTES, 'UTF-8') . '"'
                    : '';
                $rebuilt[] = '<div class="msg-box"' . $rawAttr . '>'
                           . $header . "\n" . $safeMsg . "\n"
                           . '</div><!--MSG-->';
            } else {
                // 沒有 data-raw 的留言（例如舊匯入資料），無法安全重建，保持原樣
                $rebuilt[] = $e;
            }
        }
        writeHtm($dataFile, $rebuilt);
        header("Location: 1gbk.php?rebuilt=1"); exit;
    }

    $name = isset($_POST['name'])    ? trim($_POST['name'])    : '訪客';
    $msg  = isset($_POST['message']) ? trim($_POST['message']) : '';
    if ($msg !== '') {
        $time     = date('Y-m-d H:i:s');
        $safeName   = strip_tags($name);
        $hasCopyBtn = isset($_POST['has_copy_btn']) && $_POST['has_copy_btn'] === '1';
        $packLongText = isset($_POST['pack_long_text']) && $_POST['pack_long_text'] === '1';
        
        $originalMsg = $msg;  // 儲存原始內容
        $safeMsg    = processMsg($msg, $packLongText);
        
        if ($hasCopyBtn) {
            $safeMsg .= "\n" . '<button class="btn-cc" onclick="copyContent(this)">📋 複製內容</button>';
        }
        $header = "[{$time}] <b>{$safeName}</b>:";
        
        // 選擇性儲存 data-raw：單純短文字不需要另存一份，節省檔案空間；
        // 有網址／HTML 特殊字元／長文的留言才存，確保複製功能仍然準確。
        $rawAttr = needsRawStorage($originalMsg)
            ? ' data-raw="' . htmlspecialchars($originalMsg, ENT_QUOTES, 'UTF-8') . '"'
            : '';
        $entry = '<div class="msg-box"' . $rawAttr . '>'
               . $header . "\n" . $safeMsg . "\n"
               . '</div><!--MSG-->';
        
        $existing = readHtm($dataFile);
        array_unshift($existing, $entry);
        writeHtm($dataFile, $existing);
    }
    header("Location: 1gbk.php"); exit;
}

$entries = readHtm($dataFile);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Guestbook</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 640px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.8;
            background: #fafafa;
        }
        
        .form-area {
            background: white;
            padding: 16px 20px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid #e8e8e8;
        }
        .form-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }
        .form-row label {
            font-weight: 500;
            font-size: 14px;
            color: #333;
            min-width: 50px;
        }
        .form-row input[type="text"] {
            flex: 1;
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        .form-row input[type="text"]:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .form-area textarea {
            width: 100%;
            height: 80px;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 13px;
            resize: vertical;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }
        .form-area textarea:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .form-bottom {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 8px;
            flex-wrap: wrap;
        }
        .form-bottom label {
            font-size: 13px;
            color: #555;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .form-bottom label input[type="checkbox"] {
            width: 15px;
            height: 15px;
            cursor: pointer;
        }
        .btn-submit {
            padding: 6px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.4);
        }
        .btn-submit:active {
            transform: translateY(0);
        }
        
        .list-title{font-size:14px;font-weight:600;margin:4px 0 2px;color:#555;}
        .list-title span{background:#667eea;color:#fff;border-radius:10px;padding:1px 7px;font-size:12px;margin-left:5px;}
        
        .msg-box {
            background: white;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            border: 1px solid #f0f0f0;
            white-space: pre-wrap;
            word-wrap: break-word;
            position: relative;
            transition: box-shadow 0.2s;
        }
        .msg-box:hover {
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        .msg-box h1,.msg-box h2,.msg-box h3,.msg-box h4 {
            font-size: 1em;
            color: inherit;
            margin: 0;
        }
        .msg-box * {
            max-width: 100%;
            box-sizing: border-box;
        }
        .msg-box b {
            color: #e44;
        }
        .msg-box a {
            color: #07c;
            text-decoration: none;
        }
        .msg-box a:hover {
            text-decoration: underline;
        }
        .msg-box img {
            max-width: 100%;
            border-radius: 4px;
        }
        
        .msg-actions {
            position: absolute;
            top: 6px;
            right: 6px;
            display: flex;
            gap: 2px;
            opacity: 0;
            transition: opacity 0.2s;
            background: rgba(255,255,255,0.9);
            padding: 2px;
            border-radius: 6px;
        }
        .msg-box:hover .msg-actions {
            opacity: 1;
        }
        .msg-actions button {
            border: none;
            background: none;
            cursor: pointer;
            font-size: 14px;
            padding: 3px 5px;
            border-radius: 4px;
            line-height: 1;
            transition: background 0.15s;
        }
        .msg-actions button:hover {
            background: #f0f0f0;
        }
        .btn-copy { color: #07c; }
        .btn-del { color: #c44; }
        
        .btn-cc {
            display: inline-block;
            margin: 6px 0 2px;
            padding: 4px 14px;
            background: #2d2d2d;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.2s;
        }
        .btn-cc:hover { background: #444; }
        .btn-cc.ok { background: #4CAF50; }
        
        .bottom-del {
            margin-top: 4px;
            padding-top: 4px;
            border-top: 1px dashed #eee;
        }
        .bottom-del button {
            font-size: 12px;
            color: #c44;
            background: none;
            border: none;
            cursor: pointer;
            padding: 2px 0;
        }
        .bottom-del button:hover {
            text-decoration: underline;
        }
        
        .long-text-wrapper {
            margin: 4px 0;
        }
        .long-text-preview {
            white-space: pre-wrap;
            word-wrap: break-word;
            color: #333;
            margin-bottom: 4px;
        }
        .btn-expand {
            font-size: 12px;
            padding: 3px 12px;
            background: #f0f0f0;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-block;
        }
        .btn-expand:hover {
            background: #e8e8e8;
            border-color: #667eea;
        }
        .btn-expand.active {
            background: #667eea;
            color: #fff;
            border-color: #667eea;
        }
        .long-text-content {
            max-height: 400px;
            overflow-y: auto;
        }
        
        #toast {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(16px);
            background: rgba(34,34,34,0.92);
            color: #fff;
            padding: 8px 24px;
            border-radius: 24px;
            font-size: 14px;
            opacity: 0;
            transition: opacity 0.3s, transform 0.3s;
            pointer-events: none;
            z-index: 9999;
            backdrop-filter: blur(4px);
        }
        
        .empty-state{color:#999;font-size:13px;padding:4px 0;}
    </style>
</head>
<body>
<div class="form-area">
    <form method="POST" id="guestForm">
        <div class="form-row">
            <label for="fName">Name</label>
            <input type="text" name="name" id="fName" required>
        </div>
        <textarea name="message" id="fMsg" required placeholder="請輸入留言內容..."></textarea>
        <div class="form-bottom">
            <label><input type="checkbox" name="has_copy_btn" value="1"> 📋 複製按鈕</label>
            <label><input type="checkbox" name="pack_long_text" value="1" checked> 📄 自動打包長文</label>
            <button type="submit" class="btn-submit">送出留言</button>
        </div>
    </form>
</div>
<div class="list-title">所有留言 <span><?php echo count($entries); ?></span>
    <form method="POST" style="display:inline" onsubmit="return confirm('將重新整理所有還保留原始內容的舊留言格式，確定繼續？')">
        <input type="hidden" name="action" value="rebuild">
        <button type="submit" style="margin-left:8px;font-size:12px;padding:3px 10px;background:#f0f0f0;color:#333;border:1px solid #ddd;border-radius:4px;cursor:pointer;">🔄 重建長文格式</button>
    </form>
</div>
<?php if (empty($entries)): ?>
    <div class="empty-state">（尚無留言）</div>
<?php else: ?>
    <?php foreach ($entries as $i => $e):
        // 提取 data-raw 屬性（如果存在），並拿掉最外層的 <div ...>...</div><!--MSG-->
        $rawAttr = '';
        if (preg_match('/^<div class="msg-box"(?: data-raw="([^"]*)")?>/', $e, $matches)) {
            if (!empty($matches[1])) {
                $rawAttr = ' data-raw="' . $matches[1] . '"';
            }
        }
        $inner = preg_replace('/^<div class="msg-box"(?: data-raw="[^"]*")?>/', '', $e, 1);
        $inner = preg_replace('/<\/div><!--MSG-->$/', '', $inner, 1);

        $delForm = '<form method="POST" style="display:inline" onsubmit="return confirmDel(this)">'
            . '<input type="hidden" name="action" value="delete">'
            . '<input type="hidden" name="idx" value="'.$i.'">';
        $actions = '<div class="msg-actions">'
            . '<button class="btn-copy" onclick="copyMsg(this)" title="複製留言">📋</button>'
            . $delForm
            . '<button class="btn-del" type="submit" title="刪除留言">🗑</button>'
            . '</form></div>';

        // 計算行數（包含換行和 <br>）
        $lineCount = substr_count($inner, "\n") + substr_count($inner, '<br');
        $bottomDel = ($lineCount > 10)
            ? '<div class="bottom-del">' . $delForm . '<button type="submit">🗑 刪除此則留言</button></form></div>'
            : '';

        // 重新組裝：開頭 tag（保留 data-raw）+ 動作按鈕 + 原內容 + （可選）底部刪除
        $out = '<div class="msg-box"' . $rawAttr . '>' . $actions . $inner;
        if ($bottomDel) {
            $out .= $bottomDel;
        }
        $out .= '</div><!--MSG-->';
        echo $out;
    endforeach; ?>
<?php endif; ?>

<div id="toast"></div>

<script>
// ===== Name + Msg 自動儲存到 localStorage =====
(function(){
    var n = document.getElementById('fName');
    var m = document.getElementById('fMsg');
    if (!n || !m) return;

    // 還原儲存的內容
    var savedName = localStorage.getItem('gb_name');
    var savedMsg  = localStorage.getItem('gb_msg');
    if (savedName) n.value = savedName;
    if (savedMsg)  m.value = savedMsg;

    // 即時儲存
    n.addEventListener('input', function(){ localStorage.setItem('gb_name', n.value); });
    m.addEventListener('input', function(){ localStorage.setItem('gb_msg',  m.value); });

    // 送出留言表單：成功後只清 Msg，Name 保留
    var form = document.getElementById('guestForm');
    if (form) {
        form.addEventListener('submit', function(){
            setTimeout(function(){ localStorage.removeItem('gb_msg'); }, 100);
        });
    }
})();

// ===== Toast 提示 =====
var _toastTimer = null;
function showToast(msg) {
    var t = document.getElementById('toast');
    if (!t) return;
    t.textContent = msg;
    t.style.opacity = '1';
    t.style.transform = 'translateX(-50%) translateY(0)';
    if (_toastTimer) clearTimeout(_toastTimer);
    _toastTimer = setTimeout(function(){
        t.style.opacity = '0';
        t.style.transform = 'translateX(-50%) translateY(20px)';
    }, 2000);
}

// ===== 刪除確認 =====
function confirmDel(form) {
    if (!confirm('確定要刪除這則留言嗎？\n（此操作無法復原）')) {
        return false;
    }
    return true;
}

// ===== 長文本展開/收合 =====
function toggleLongText(btn) {
    var targetId = btn.getAttribute('data-target');
    var content = document.getElementById(targetId);
    if (!content) return;

    var collapsedLabel = btn.getAttribute('data-label-collapsed') || '📄 展開長文（點擊展開）';
    var expandedLabel  = btn.getAttribute('data-label-expanded')  || '📄 收起長文';

    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        btn.textContent = expandedLabel;
        btn.classList.add('active');
    } else {
        content.style.display = 'none';
        btn.textContent = collapsedLabel;
        btn.classList.remove('active');
    }
}

// ===== 複製功能 =====
function copyPlainText(text, callback) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(callback).catch(function(){ 
            fallbackCopy(text); 
            if(callback) callback();
        });
    } else { 
        fallbackCopy(text); 
        if(callback) callback(); 
    }
}

function fallbackCopy(text) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    try { document.execCommand('copy'); } catch(e){}
    document.body.removeChild(ta);
}

function getCleanMessageFromBox(boxElement) {
    var clone = boxElement.cloneNode(true);
    clone.querySelectorAll('.msg-actions, .btn-cc, .bottom-del').forEach(function(el){ el.remove(); });
    // 長文包裝：把「預覽行」跟「展開的其餘內容」接回去，還原完整原文（不論目前是否收合）
    clone.querySelectorAll('.long-text-wrapper').forEach(function(wrapper){
        var preview = wrapper.querySelector('.long-text-preview');
        var content = wrapper.querySelector('.long-text-content');
        var previewText = preview ? preview.textContent : '';
        var restText = content ? content.textContent : '';
        var full = restText ? (previewText + '\n' + restText) : previewText;
        var textNode = document.createTextNode(full);
        wrapper.replaceWith(textNode);
    });
    var html = clone.innerHTML;
    html = html.replace(/<b[^>]*>(.*?)<\/b>/gi, '$1');
    html = html.replace(/<br\s*\/?>/gi, '\n');
    html = html.replace(/<a[^>]*href="([^"]*)"[^>]*>\s*<img[^>]*>\s*<\/a>/gi, '$1');
    html = html.replace(/<img[^>]*src="([^"]*)"[^>]*>/gi, '$1');
    html = html.replace(/<a[^>]*>(.*?)<\/a>/gi, '$1');
    html = html.replace(/<[^>]+>/g, '');
    html = html.replace(/&amp;/g, '&').replace(/&lt;/g, '<')
               .replace(/&gt;/g, '>').replace(/&nbsp;/g, ' ')
               .replace(/&#39;/g, "'").replace(/&quot;/g, '"');
    // 移除時間戳行（第一行），和 copyContent 保持一致
    var lines = html.split('\n');
    if (lines.length > 0 && /^\[\d{4}-\d{2}-\d{2}/.test(lines[0].trim())) {
        lines.shift();
    }
    return lines.join('\n').replace(/\n{3,}/g, '\n\n').trim();
}

function copyMsg(btn) {
    var box = btn.closest('.msg-box');
    
    // 嘗試使用 data-raw（原始內容）
    var rawContent = box.getAttribute('data-raw');
    if (rawContent) {
        copyPlainText(rawContent, function(){
            btn.textContent = '✅';
            showToast('已複製原始內容！');
            setTimeout(function(){ btn.textContent = '📋'; }, 1500);
        });
        return;
    }
    
    // 降級方案：從 HTML 解析
    var text = getCleanMessageFromBox(box);
    copyPlainText(text, function(){
        btn.textContent = '✅';
        showToast('已複製留言內容！');
        setTimeout(function(){ btn.textContent = '📋'; }, 1500);
    });
}

function copyContent(btn) {
    var box = btn.closest('.msg-box');
    
    // 優先使用 data-raw（原始內容）
    var rawContent = box.getAttribute('data-raw');
    if (rawContent) {
        copyPlainText(rawContent, function(){
            btn.textContent = '✅ 已複製原始內容！';
            btn.classList.add('ok');
            showToast('已複製原始內容！');
            setTimeout(function(){
                btn.textContent = '📋 複製內容';
                btn.classList.remove('ok');
            }, 2000);
        });
        return;
    }
    
    // 降級方案：從 HTML 解析
    var clone = box.cloneNode(true);
    clone.querySelectorAll('.msg-actions, .btn-cc, .bottom-del').forEach(function(el){ el.remove(); });
    clone.querySelectorAll('.long-text-wrapper').forEach(function(wrapper){
        var preview = wrapper.querySelector('.long-text-preview');
        var content = wrapper.querySelector('.long-text-content');
        var previewText = preview ? preview.textContent : '';
        var restText = content ? content.textContent : '';
        var full = restText ? (previewText + '\n' + restText) : previewText;
        var textNode = document.createTextNode(full);
        wrapper.replaceWith(textNode);
    });
    var html = clone.innerHTML;
    html = html.replace(/<b[^>]*>(.*?)<\/b>/gi, '$1');
    html = html.replace(/<br\s*\/?>/gi, '\n');
    html = html.replace(/<a[^>]*href="([^"]*)"[^>]*>\s*<img[^>]*>\s*<\/a>/gi, '$1');
    html = html.replace(/<img[^>]*src="([^"]*)"[^>]*>/gi, '$1');
    html = html.replace(/<a[^>]*>(.*?)<\/a>/gi, '$1');
    html = html.replace(/<[^>]+>/g, '');
    html = html.replace(/&amp;/g,'&').replace(/&lt;/g,'<').replace(/&gt;/g,'>').replace(/&nbsp;/g,' ').replace(/&#39;/g,"'").replace(/&quot;/g,'"');
    var lines = html.trim().split('\n');
    if (/^\[\d{4}-\d{2}-\d{2}/.test(lines[0].trim())) {
        lines.shift();
    }
    var text = lines.join('\n').replace(/\n{3,}/g,'\n\n').trim();
    copyPlainText(text, function(){
        btn.textContent = '✅ 已複製！';
        btn.classList.add('ok');
        showToast('已複製內容！');
        setTimeout(function(){
            btn.textContent = '📋 複製內容';
            btn.classList.remove('ok');
        }, 2000);
    });
}

// ===== URL 縮短顯示 =====
(function(){
    document.querySelectorAll('.msg-box a').forEach(function(a){
        var txt = a.textContent.trim();
        if (/^https?:\/\//i.test(txt)) {
            a.title = txt;
            try {
                var u = new URL(txt);
                var base = u.pathname.split('/').filter(Boolean).pop() || u.hostname;
                a.textContent = base + u.search;
            } catch(e){}
        }
    });
})();

// ===== 初始化：確保長文按鈕文字與內容的收合狀態一致 =====
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.long-text-wrapper').forEach(function(wrapper){
        var content = wrapper.querySelector('.long-text-content');
        var btn = wrapper.querySelector('.btn-expand');
        if (!content || !btn) return;

        var isCollapsed = (content.style.display === 'none');
        if (isCollapsed) {
            btn.textContent = btn.getAttribute('data-label-collapsed') || btn.textContent;
            btn.classList.remove('active');
        } else {
            btn.textContent = btn.getAttribute('data-label-expanded') || '📄 收起長文';
            btn.classList.add('active');
        }
    });
});
</script>

</body>
</html>