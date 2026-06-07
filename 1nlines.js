// ============================================================
//  1nlines_fixed.js — 杏夜絮語 v9.1 | 語法修訂版
//   基於 v9.0 僅修正語法問題，不改原意、不涉道德評價
//   修訂內容：介詞框架、主語一致性、標點統一、長句節奏
// ============================================================

(function(global){

// ════════════════════════════════════════════════════════════
//  【A】詞彙池 — 保持原意，僅微調語法
// ════════════════════════════════════════════════════════════
const POOLS = {
    env: [
        "月光如水的臥室。", "潔白床單的柔軟深處。", "薄霧繚繞的鏡前。", "只有喘息聲的深夜。",
        "玫瑰花瓣散落的浴室。", "霓虹燈下的落地窗前。", "蒸氣瀰漫的溫泉池畔。", "絲綢帷幔低垂處。",
        "天鵝絨沙發的私密角落。", "燭光搖曳的暖閣。", "杏花林邊的落地窗。", "完全漆黑只剩觸感的房間。"
    ],
    body: [
        "豐盈的胸脯", "渾圓挺翹的臀部", "粉嫩硬挺的乳尖", "濕潤粉嫩的私密處",
        "光滑敏感的大腿內側", "完全赤裸的誘人胴體", "汗水淋漓的性感身體",
        "又紅又腫的陰唇", "腫脹敏感的陰蒂", "纖細卻誘人的腰肢", "修長的玉腿",
        "被慾望染紅的肌膚", "微微顫抖的小腹"
    ],
    feeling: [
        "強烈到無法抑制的快感", "下體濕熱的空虛", "全身如火焚燒的慾火",
        "高潮即將來臨的顫抖", "被徹底填滿的滿足", "如潮水般湧來的極致快感",
        "餘韻久久不散的酥麻", "乳尖被刺激的電流", "愛液不斷流出的快感",
        "全身抽搐的極致高潮", "無法思考的迷亂"
    ],
    action: [
        "手指滑向濕潤私處", "雙腿緩緩張開", "輕揉自己豐滿胸部",
        "臀部輕輕扭動", "手指快速抽插", "完全敞開最隱私部位",
        "高高抬起雙腿", "俯臥高高拱起臀部", "對著鏡子自慰",
        "用枕頭激烈磨蹭陰蒂", "用力揉捏乳房", "輕咬下唇壓抑呻吟"
    ]
};

function buildLine(template) {
    return template.replace(/{(\w+)}/g, function(_, key) {
        var pool = POOLS[key];
        if (!pool || !pool.length) return '{' + key + '}';
        return pool[Math.floor(Math.random() * pool.length)];
    });
}

// ════════════════════════════════════════════════════════════
//  【B】圖片路徑 — 改為變數便于修改
// ════════════════════════════════════════════════════════════
const IMG_BASE_URL = 'https://lkk-2eo.pages.dev/x/img/';

const FOLDER_IMAGES = {
    /* '': (function(){
        var a = [];
        for (var i = 1; i <= 99; i++) a.push(i.toString().padStart(3,'0') + '.jpg');
        return a;
    })(),*/
    'm4':["x:/x/3/1038525004548342244.mp4","x:/x/3/2.mp4",
"https://adm.gamer.gd/2/js/x/1038525004548342244.mp4","https://adm.gamer.gd/2/js/x2\/1035978409722212426.mp4"],
    'n1':[6,"https://adm789.github.io/tfc/1/001.jpg"],
    '42':[12,"https://adm.gamer.gd/3/1/42/001.jpg"],
   'q1':        ["000.jpg","001.jpg","002.jpg"], /*,"003.jpg","004.jpg","005.jpg","006.jpg","007.jpg","008.jpg","009.jpg","010.jpg","011.jpg","012.jpg","013.jpg","014.jpg","015.jpg","016.jpg","017.jpg","018.jpg","019.jpg","020.jpg"],*/
    'w7': ["000.jpg","001.jpg"], /*,"002.jpg","003.jpg","004.jpg","005.jpg","006.jpg","007.jpg","008.jpg","009.jpg","010.jpg","011.jpg","012.jpg","013.jpg","014.jpg","015.jpg","016.jpg","017.jpg","018.jpg","019.jpg","020.jpg"],*/
    'w9': ["000.jpg","001.jpg","002.jpg","003.jpg"], /* "004.jpg","005.jpg","006.jpg","007.jpg","008.jpg","009.jpg","010.jpg","011.jpg","012.jpg","013.jpg","014.jpg","015.jpg","016.jpg","017.jpg","018.jpg","019.jpg","020.jpg"] */
     '16':[12,"https://adm.gamer.gd/3/1/img/60/001.jpg"]
};

function getImage(folder) {
    var list = FOLDER_IMAGES[folder] || FOLDER_IMAGES[''];
    return list[Math.floor(Math.random() * list.length)];
}

function imgSrc(folder, filename) {
    if (folder && filename) {
        return IMG_BASE_URL + folder + '/' + filename;
    } else if (filename) {
        return IMG_BASE_URL + filename;
    }
    return '';
}

// ════════════════════════════════════════════════════════════
//  【C】外部短句庫
// ════════════════════════════════════════════════════════════
var externalGeneralLines = [];

function loadExternalLines(callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'general_lines.txt', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                var lines = xhr.responseText.split(/\r?\n/);
                externalGeneralLines = lines.filter(function(line) {
                    var trimmed = line.trim();
                    // 過濾空行、註釋、以及誤入的數字行
                    return trimmed.length > 0 && 
                           !trimmed.startsWith('//') && 
                           !trimmed.startsWith('#') &&
                           !/^\d+$/.test(trimmed);
                });
                console.log('[1nlines_fixed.js] 已加載', externalGeneralLines.length, '句');
            } else {
                console.warn('[1nlines_fixed.js] general_lines.txt 加載失敗');
            }
            if (callback) callback();
        }
    };
    xhr.send();
}

// ════════════════════════════════════════════════════════════
//  【D】特殊群組模板 — 語法修訂版
//  修改重點：拆分長句、統一標點、補齊主語、減少「讓」字濫用
// ════════════════════════════════════════════════════════════
const specialGroupTemplates = {
    q1: [
        "{env}我赤裸枕臂仰躺，{body}在潔白床單上舒展。",
        "白蕾絲已褪下。我赤裸躺在床上，{action}。{feeling}如潮水般襲來。",
        "{env}我雙腿張開，{body}完全暴露。",
        "鏡中映出濕潤的私處。{feeling}讓我忍不住輕輕呻吟。",
        "{env}我拱起腰肢，{body}呈現誘人的弧度。",
        "鏡前我輕揉{body}。乳尖迅速硬挺，{feeling}無法抑制。",
        "仰躺時，手指沿大腿內側向上，觸及濕潤的花瓣。",
        "月光灑在赤裸身體上。我用力{action}，感受{feeling}。",
        "完全敞開的姿態。在{env}，我徹底沉淪於慾望。",
        "雙手捧起豐滿胸部，用力揉捏乳尖。{feeling}如電流竄過。",
        "{env}我用枕頭墊高臀部，讓手指更深入。",
        "鏡中的我眼神迷離。{action}時，全身泛起粉紅。",
        "我看著自己手指被愛液包裹進出的模樣。",
        "赤裸仰躺，雙腿高高抬起。{feeling}越來越強烈。"
    ],
    w7: [
        "{env}我高高抬起雙腿，完全暴露{body}。",
        "明亮臥室裡，我凝視鏡中自己。{action}時，{feeling}無法抑制。",
        "抬腿姿勢中，我用力{action}。快感如潮水湧來。",
        "抬腿時，手指輕輕撥開花瓣。{feeling}讓我忍不住大聲呻吟。",
        "鏡中看著自己張開的模樣。慾望越來越強烈。",
        "抬腿時臀部上翹，我用手指緩緩探索最深處。",
        "{env}我保持抬腿姿勢，感受強烈的空虛與渴望。",
        "雙腿大開的瞬間，{feeling}如巨浪般襲來。",
        "{env}我側躺抬腿，讓手指更深入敏感地帶。",
        "高抬雙腿架在床邊。我瘋狂{action}直到高潮。"
    ],
    w9: [
        "{env}我俯臥回首，{body}的柔美弧線完全暴露。",
        "拱起的背脊與散落長髮。{action}，{feeling}緩緩湧上。",
        "{env}我高高拱起臀部，呈現後入般的誘人視角。",
        "俯臥回首時，渾圓臀部完全敞開。我輕輕搖晃。",
        "拱起的腰臀曲線在月光下完美呈現。{feeling}無法抑制。",
        "從後方看見的自己，充滿強烈的禁忌誘惑。",
        "俯臥姿態中，我伸手向後猛烈{action}。{feeling}越來越強。",
        "{env}我跪趴抬臀，用手指從後方猛烈自慰。",
        "俯臥、高拱臀部。我對鏡欣賞自己最誘人的模樣。"
    ]
};

// ════════════════════════════════════════════════════════════
//  【E】autoDetectFolder
// ════════════════════════════════════════════════════════════
function autoDetectFolder(text) {
    if (text.indexOf('枕臂') !== -1 || text.indexOf('仰躺') !== -1 || text.indexOf('輕揉') !== -1) return 'q1';
    if (text.indexOf('抬腿') !== -1 || text.indexOf('張開') !== -1 || text.indexOf('高抬') !== -1) return 'w7';
    if (text.indexOf('俯臥') !== -1 || text.indexOf('拱起') !== -1 || text.indexOf('跪趴') !== -1) return 'w9';
    return '';
}

// ════════════════════════════════════════════════════════════
//  【F】getAll() — 高權重長句版
// ════════════════════════════════════════════════════════════
function getAll() {
    var result = [];

    // 外部短句庫
    var linesToUse = externalGeneralLines.length > 0 ? externalGeneralLines : [];
    for (var i = 0; i < linesToUse.length; i++) {
        var line = linesToUse[i];
        if (line && line.trim()) {
            result.push({ text: line.trim(), folder: '' });
        }
    }

    // 特殊長句（每條重複 4 次）
    var folders = ['q1', 'w7', 'w9'];
    for (var f = 0; f < folders.length; f++) {
        var folder = folders[f];
        var templates = specialGroupTemplates[folder] || [];
        for (var repeat = 0; repeat < 4; repeat++) {
            for (var t = 0; t < templates.length; t++) {
                var cooked = buildLine(templates[t]);
                result.push({ text: cooked, folder: folder });
            }
        }
    }

    console.log('[1nlines_fixed.js] 總句數:', result.length);
    return result;
}

// ════════════════════════════════════════════════════════════
//  對外暴露
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

loadExternalLines(function() {
    console.log('[1nlines_fixed.js] 初始化完成');
});

})(window);
