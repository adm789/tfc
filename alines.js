// ============================================================
//  lines_compatible.js — 杏夜絮語 · 與 1auto.htm 完全相容版
//  版本: v6.0 | 外部短句庫 + 語法修復 + 長句權重提高
// ============================================================

(function(global){

// ════════════════════════════════════════════════════════════
//  【A】詞彙池 — 純名詞短語（無主謂結構）
// ════════════════════════════════════════════════════════════
const POOLS = {
    env: [
        "月光如水的臥室", "潔白床單的柔軟深處", "薄霧繚繞的鏡前",
        "杏花林邊的窗前", "燭光輕搖的深夜", "瀟湘館竹影搖曳處",
        "怡紅院暖閣", "梨香院花落時分", "大觀園月下池邊",
        "秦淮河畔燈火闌珊", "絲綢帷幔低垂處", "只有呼吸聲的深夜"
    ],
    
    body: [
        "鎖骨", "腰肢", "肩背", "頸項", "小腿", "腳踝",
        "豐盈的胸脯", "纖細的腰線", "修長的玉腿", "起伏的胸廓",
        "如緞般的背脊", "黛玉般纖弱的身子", "寶釵般豐腴的曲線"
    ],
    
    feeling: [
        "細膩的溫熱", "欲語還休的悸動", "似夢似醒的迷離", "溫柔的輕顫",
        "安靜的酥麻", "沉靜而深邃的情緒", "如春雨般的甜意", "春宵一夢的溫存",
        "寶黛之間的悸動", "葬花詞般的幽怨", "如潮汐般的感受", "漫過全身的暖意"
    ],
    
    action: [
        "指尖輕輕游移", "掌心緩緩貼緊", "腰肢微微弓起", "呼吸逐漸加深",
        "視線與自己交纏", "輕輕撫過鎖骨", "沿著曲線描繪", "溫柔環抱自己"
    ],
    
    light: ["月光", "燭焰", "晨曦", "薄霧中的光", "杏花的影", "瀟湘館的燈影"]
};

function buildLine(template) {
    return template.replace(/{(\w+)}/g, function(_, key) {
        var pool = POOLS[key];
        if (!pool || !pool.length) return '{' + key + '}';
        return pool[Math.floor(Math.random() * pool.length)];
    });
}

// ════════════════════════════════════════════════════════════
//  【B】圖片路徑（保留原結構）
// ════════════════════════════════════════════════════════════
const FOLDER_IMAGES = {
    '': (function(){
        var a = [];
        for (var i = 1; i <= 99; i++) a.push(i.toString().padStart(3,'0') + '.jpg');
        return a;
    })(),
    'q1': ["000.jpg","001.jpg","002.jpg","003.jpg","004.jpg","005.jpg","006.jpg","007.jpg","008.jpg","009.jpg","010.jpg","011.jpg","012.jpg","013.jpg","014.jpg","015.jpg","016.jpg","017.jpg","018.jpg"],
    'w7': ["000.jpg","001.jpg","002.jpg","003.jpg","004.jpg","005.jpg","006.jpg","007.jpg","008.jpg","009.jpg","010.jpg","011.jpg","012.jpg","013.jpg","014.jpg","015.jpg","016.jpg","017.jpg","018.jpg"],
    'w9': ["000.jpg","001.jpg","002.jpg","003.jpg","004.jpg","005.jpg","006.jpg","007.jpg","008.jpg","009.jpg","010.jpg","011.jpg","012.jpg","013.jpg","014.jpg","015.jpg","016.jpg","017.jpg","018.jpg"]
};

function getImage(folder) {
    var list = FOLDER_IMAGES[folder] || FOLDER_IMAGES[''];
    if (!list.length) return '001.jpg';
    return list[Math.floor(Math.random() * list.length)];
}

function imgSrc(folder, filename) {
    return folder ? `https://cb755c0b.lkk-2eo.pages.dev/x/img/${folder}/${filename}` : `https://cb755c0b.lkk-2eo.pages.dev/x/img/${filename}`
   /* if (folder && filename) return folder + '/img/' + filename;
    if (filename) return filename;
    return '';*/
}//  .->https://cb755c0b.lkk-2eo.pages.dev/x or go

// ════════════════════════════════════════════════════════════
//  【C】外部短句庫 — 從 general_lines.txt 加載
// ════════════════════════════════════════════════════════════
var externalGeneralLines = [];

// 內置 fallback（萬一外部文件加載失敗）
var fallbackGeneralLines = [
    "月光落在鎖骨上，涼涼的，像一片薄霜。",
    "我閉上眼，讓自己回到那片杏花林裡，風從耳邊經過。",
    "指尖輕輕劃過小腹，不是挑逗，只是確認自己還在。"
];

function loadExternalLines(callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'general_lines.txt', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                var lines = xhr.responseText.split(/\r?\n/);
                externalGeneralLines = lines.filter(function(line) {
                    var trimmed = line.trim();
                    return trimmed.length > 0 && !trimmed.startsWith('//') && !trimmed.startsWith('#');
                });
                if (typeof console !== 'undefined') {
                    console.log('[lines.js] 已加載外部短句庫:', externalGeneralLines.length, '句');
                }
            } else {
                if (typeof console !== 'undefined') {
                    console.warn('[lines.js] general_lines.txt 加載失敗，使用內置備用庫');
                }
            }
            if (callback) callback();
        }
    };
    xhr.send();
}

// ════════════════════════════════════════════════════════════
//  【D】特殊群組 — 語法完全修復版
//  修正：去掉「我的XX的XX」雙「的」結構
//  提高長句權重：每條特殊句子重複 3 次，增加出現率
// ════════════════════════════════════════════════════════════
const specialGroupTemplates = {
    q1: [
        "{env}，我枕臂仰躺，讓{body}在潔白床單上舒展開來。",
        "白蕾絲輕覆身體，我躺在柔軟床上，感受{feeling}。",
        "潔白床鋪如雲朵包圍我，{body}自然舒展，夜色寂靜。",
        "鏡中映出柔軟姿態，{env}，{feeling}如細雨輕拂。",
        "白蕾絲與肌膚交織，在{env}的柔光下，我沉浸於{feeling}。",
        "半躺的姿態讓{body}自然舒展，我在{env}裡品味{feeling}。",
        "想像黛玉回眸時的幽怨，我俯臥回首，{body}在月光下柔軟如詩。",
        "寶釵的端莊與渴望交織，今夜只想讓{body}在{env}自然舒展。",
        "瀟湘館竹影搖曳，我閉上眼，{feeling}如春雨般滲入心底。"
    ],
    w7: [
        "{env}，我輕抬一腿，{body}在白蕾絲下若隱若現。",
        "明亮臥室裡，我凝視鏡中自己，{action}時全身泛起{feeling}。",
        "緊貼身體的白蕾絲讓呼吸柔軟，我沉浸在{feeling}中。",
        "抬高的腿部弧線在光影中延伸，我感受到{feeling}。",
        "白蕾絲如第二層肌膚，我讓姿勢自然展開，夜的溫柔一點一點滲入。",
        "潔白床單上，我以抬腿姿態，享受{feeling}在體內流動。",
        "眼神與身體一同訴說，{action}帶來的酥麻化作安靜的等待。",
        "梨香院花落無聲，我讓{body}在白蕾絲下輕柔舒展。",
        "今夜我輕抬一腿，在夜色中完全敞開自己，感受{feeling}。"
    ],
    w9: [
        "{env}，我俯臥回首，{body}的柔美弧線在白紗間若隱若現。",
        "拱起的背脊與散落長髮，{action}，{feeling}緩緩湧上心頭。",
        "潔白床單上，我回望自己的身體，讓{body}在光線下呈現迷人姿態。",
        "從肩頭回眸的眼神，伴隨著{feeling}，讓夜晚變得格外動人。",
        "俯臥的我微微弓起背脊，感受{action}帶來的溫熱與輕顫。",
        "回頭的瞬間，眼神柔軟，{body}在白蕾絲下輕輕呼吸。",
        "黛玉般纖弱的身子微微拱起，{feeling}如落花般幽怨動人。",
        "拱起的背脊在白紗間形成優美弧度，{feeling}如月光輕柔覆蓋。",
        "從肩頭回望，像探春的堅韌與溫柔交織，讓夜色更加迷人。",
        "俯臥姿態中，{body}自然舒展，沉浸在{feeling}裡。"
    ]
};

// ════════════════════════════════════════════════════════════
//  【E】autoDetectFolder — 供 1auto.htm 調用
// ════════════════════════════════════════════════════════════
function autoDetectFolder(text) {
    if (text.indexOf('枕臂仰躺') !== -1 || text.indexOf('白蕾絲輕覆') !== -1) return 'q1';
    if (text.indexOf('輕抬一腿') !== -1 || text.indexOf('抬腿') !== -1) return 'w7';
    if (text.indexOf('俯臥回首') !== -1 || text.indexOf('拱起的背脊') !== -1) return 'w9';
    return '';
}

// ════════════════════════════════════════════════════════════
//  【F】getAll() — 回傳格式與 1auto.htm 完全相容
//  長句權重提高：每個特殊句子重複 3 次
// ════════════════════════════════════════════════════════════
function getAll() {
    var result = [];
    
    // 使用外部加載的句子（如果有），否則用內置備用
    var linesToUse = (externalGeneralLines.length > 0) ? externalGeneralLines : fallbackGeneralLines;
    for (var i = 0; i < linesToUse.length; i++) {
        if (linesToUse[i] && linesToUse[i].trim()) {
            result.push({ text: linesToUse[i].trim(), folder: '' });
        }
    }
    
    // 特殊群組句 — 每條重複 3 次，提高長句出現率
    var folders = ['q1', 'w7', 'w9'];
    for (var f = 0; f < folders.length; f++) {
        var folder = folders[f];
        var templates = specialGroupTemplates[folder];
        // 每條模板重複 3 次
        for (var repeat = 0; repeat < 3; repeat++) {
            for (var t = 0; t < templates.length; t++) {
                var cooked = buildLine(templates[t]);
                result.push({ text: cooked, folder: folder });
            }
        }
    }
    
    if (typeof console !== 'undefined') {
        console.log('[lines.js] getAll 完成，總句數:', result.length, '(短句:', linesToUse.length, ', 長句:', (result.length - linesToUse.length), ')');
    }
    
    return result;
}

// ════════════════════════════════════════════════════════════
//  【G】對外暴露
// ════════════════════════════════════════════════════════════
global.LinesLib = {
    getAll: getAll,
    buildLine: buildLine,
    getImage: getImage,
    imgSrc: imgSrc,
    autoDetectFolder: autoDetectFolder,
    FOLDER_IMAGES: FOLDER_IMAGES,
    POOLS: POOLS
};

// 啟動外部加載（非同步，但 getAll 會在加載完成後被調用）
loadExternalLines(function() {
    if (typeof console !== 'undefined') {
        console.log('[lines.js] 初始化完成，可使用 LinesLib.getAll()');
    }
});

})(window);