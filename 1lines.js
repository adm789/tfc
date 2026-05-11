// ====
//  lines_compatible.js — 杏夜絮語 · m4 專用修復版 (v9.2)
//  修復: 1. m4 改用純傳統陣列 2. 支援檔名內含子目錄 3. 移除所有尾隨空格
// ====
(function(global){
const POOLS = {
env: [ "月光如水的臥室", "潔白床單的柔軟深處", "薄霧繚繞的鏡前", "杏花林邊的窗前", "燭光輕搖的深夜" ],
body: [ "鎖骨", "腰肢", "豐盈胸脯", "修長玉腿", "渾圓挺翹臀部", "完全赤裸胴體" ],
feeling: [ "細膩的溫熱", "欲語還休的悸動", "強烈快感", "全身如火焚燒的慾望" ],
action: [ "指尖輕輕游移", "掌心緩緩貼緊", "雙腿緩緩張開", "輕咬下唇壓抑呻吟" ]
};

// 🛠️ m4 改用「純傳統陣列」，移除所有尾隨空格，支援子目錄路徑
const FOLDER_IMAGES = {
    'w9': [61, "001.jpg"], //145
    'm4': ["27.mp4", "s3.mp4", "tumblr_ns5tz9PHKW1r9k3bc.mp4","tumblr_nuddsb25eW1tcmyw3.mp4", "bg/uploadMP.mp4"], // 👈 純字串陣列
    'q8': [11, "000.jpg"],
    '66': [66, "001.jpg"],
    '99': [60, "001.jpg"],
    'q1': [99, "000.jpg"],
    'w7': [84, "84/001.jpg"],
    '60': [60, "000.jpg"],
    '':  [55, "001.jpg"]
};

function getImage(folder) {
    var conf = FOLDER_IMAGES[folder];
    if (!conf) conf = FOLDER_IMAGES[''];
    
    // 情況 A: 傳統陣列 (用於 m4 等不連續檔名)
    if (Array.isArray(conf) && typeof conf[0] === 'string') {
        return conf[Math.floor(Math.random() * conf.length)].trim();
    }
    
    // 情況 B: 壓縮格式 [總數, "起始檔名"]
    if (Array.isArray(conf) && conf.length === 2 && typeof conf[0] === 'number') {
        var total = conf[0], startName = conf[1].trim();
        var numbers = startName.match(/\d+(?=\.)/g);
        if (!numbers || numbers.length === 0) return startName;
        
        var fileNum = parseInt(numbers[numbers.length - 1]);
        var padLen = numbers[numbers.length - 1].length;
        var pathPrefix = startName.substring(0, startName.lastIndexOf(numbers[numbers.length - 1]));
        var ext = startName.split('.').pop();
        var randomNum = fileNum + Math.floor(Math.random() * total);
        var numStr = randomNum.toString().padStart(padLen, '0');
        return pathPrefix + numStr + '.' + ext;
    }
    return '001.jpg';
}
function imgSrc(folder, filename) {
  filename = filename.trim();
  // 1. 先嘗試本地路徑
  const local = folder ? `./img/${folder}/${filename}` : `./img/${filename}`;
  // 2. 備援網路路徑
  const remote = `https://cb755c0b.lkk-2eo.pages.dev/x/img/${folder?folder+'/':''}${filename}`;
  // 3. 可加入圖片載入失敗自動切換邏輯（需前端配合）
  return local; // 預設返回本地，前端 onerror 時再換 remote
}
/*️ 核心修復：若檔名已含路徑(如 bg/uploadMP4.mp4)，直接使用；否則拼接 ./img/m4/
function imgSrc(folder, filename) {
    if (!filename) return '';
    filename = filename.trim();
    var isVideo = /\.(mp4|webm|m4v|mov)$/i.test(filename);
    var isWebp = /\.webp$/i.test(filename);
    
    if (isVideo || isWebp) {
        // 🔧 若檔名已含 "/"，代表已有子目錄，直接使用；否則拼接 ./img/m4/
        return filename.includes('/') ? `./img/${filename}` : `./img/m4/${filename}`;
    }
    return folder ? `./img/${folder}/${filename}` : `./img/${filename}`;
} */

var externalGeneralLines = [], fallbackGeneralLines = ["月光落在鎖骨上，涼涼的，像一片薄霜。"];
const specialGroupTemplates = {
    w9: ["{env}，我輕抬一腿，{body}在白蕾絲下若隱若現。"],
    66: [ "{env}，{action}，{feeling}。" ],
    q1: [ "{env}，{feeling}。" ],
    95: [ "{action}，{env}，{feeling}。" ],
    //q1/q8 w7:84
 60: ["{env}，我枕臂仰躺，讓{body}在潔白床單上完全舒展開來。"],
    99: ["{env}，我俯臥回首，{body}的柔美弧線在白紗間若隱若現。"]
};

function getAll() {
    var result = [], linesToUse = (externalGeneralLines.length > 0) ? externalGeneralLines : fallbackGeneralLines;
    linesToUse.forEach(l => { if(l&&l.trim()) result.push({text:l.trim(), folder:''}); });
    ['m4','q1','w7','w9','q8'].forEach(f => {
        if(specialGroupTemplates[f]) specialGroupTemplates[f].forEach(t => result.push({text:buildLine(t), folder:f}));
    });
    return result;
}
function buildLine(template) {
    return template.replace(/{(\w+)}/g, (_,key) => { var p=POOLS[key]; return p?p[Math.floor(Math.random()*p.length)]:''; });
}
global.LinesLib = { getAll, buildLine, getImage, imgSrc, FOLDER_IMAGES, POOLS };
})(window);