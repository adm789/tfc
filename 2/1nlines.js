// ============================================================
//  1nlines.js — 杏夜絮語 v9.0 | 大型成人擴充版 12kB+
//  與 general_lines.txt 完美相容 | 更多露骨模板
// ============================================================

(function(global){

// ════════════════════════════════════════════════════════════
//  【A】詞彙池 — 大幅擴充成人性感詞彙
// ════════════════════════════════════════════════════════════
const POOLS = {
    env: [
        "月光如水的臥室", "潔白床單的柔軟深處", "薄霧繚繞的鏡前", "只有喘息聲的深夜",
        "玫瑰花瓣散落的浴室", "霓虹燈下的落地窗前", "蒸氣瀰漫的溫泉池畔", "絲綢帷幔低垂處",
        "天鵝絨沙發的私密角落", "燭光搖曳的暖閣", "杏花林邊的落地窗", "完全漆黑只剩觸感的房間"
    ],
    body: [
        "豐盈的胸脯", "渾圓挺翹的臀部", "粉嫩硬挺的乳尖", "濕潤粉嫩的私密處",
        "光滑敏感的大腿內側", "完全赤裸的誘人胴體", "汗水淋漓的性感身體",
        "又紅又腫的陰唇", "腫脹敏感的陰蒂", "纖細卻誘人的腰肢", "修長玉腿",
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
        "高高抬起雙腿", "俯臥高高拱起臀部", "對著鏡子瘋狂自慰",
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
//  【B】圖片路徑 — 完整陣列
// ════════════════════════════════════════════════════════════
const FOLDER_IMAGES = {
    '': (function(){
        var a = [];
        for (var i = 1; i <= 99; i++) a.push(i.toString().padStart(3,'0') + '.jpg');
        return a;
    })(),
    'q1': ["000.jpg","001.jpg","002.jpg","003.jpg","004.jpg","005.jpg","006.jpg","007.jpg","008.jpg","009.jpg","010.jpg","011.jpg","012.jpg","013.jpg","014.jpg","015.jpg","016.jpg","017.jpg","018.jpg","019.jpg","020.jpg"],
    'w7': ["000.jpg","001.jpg","002.jpg","003.jpg","004.jpg","005.jpg","006.jpg","007.jpg","008.jpg","009.jpg","010.jpg","011.jpg","012.jpg","013.jpg","014.jpg","015.jpg","016.jpg","017.jpg","018.jpg","019.jpg","020.jpg"],
    'w9': ["000.jpg","001.jpg","002.jpg","003.jpg","004.jpg","005.jpg","006.jpg","007.jpg","008.jpg","009.jpg","010.jpg","011.jpg","012.jpg","013.jpg","014.jpg","015.jpg","016.jpg","017.jpg","018.jpg","019.jpg","020.jpg"]
};

function getImage(folder) {
    var list = FOLDER_IMAGES[folder] || FOLDER_IMAGES[''];
    return list[Math.floor(Math.random() * list.length)];
}

function imgSrc(folder, filename) {
    return folder ? `./img/${folder}/${filename}` : `./img/${filename}`;
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
                    return trimmed.length > 8 && 
                           !trimmed.startsWith('//') && 
                           !trimmed.startsWith('#');
                });
                console.log('[1nlines.js] v9.0 已加載', externalGeneralLines.length, '句成人短句');
            } else {
                console.warn('[1nlines.js] general_lines.txt 加載失敗');
            }
            if (callback) callback();
        }
    };
    xhr.send();
}

// ════════════════════════════════════════════════════════════
//  【D】特殊群組 — 大幅增加露骨模板（每組 25+ 條）
// ════════════════════════════════════════════════════════════
const specialGroupTemplates = {
    q1: [
        "{env}，我完全赤裸枕臂仰躺，讓{body}在潔白床單上誘人舒展。",
        "白蕾絲已被褪下，我赤裸躺在柔軟床上，{action}，{feeling}如潮水般襲來。",
        "{env}，我雙腿大大分開，{body}完全暴露在空氣中。",
        "鏡中映出我濕潤的私處，{feeling}讓我忍不住輕輕呻吟。",
        "{env}，我拱起腰肢，讓{body}呈現最淫蕩的弧度。",
        "我在鏡前輕揉{body}，乳尖迅速硬挺，{feeling}無法抑制。",
        "仰躺時手指沿大腿內側向上，觸碰到已經濕透的私密處。",
        "月光灑在赤裸身體上，我用力{action}，感受{feeling}。",
        "完全敞開的姿態，在{env}中徹底沉淪於慾望。",
        "雙手捧起豐滿胸部，用力揉捏乳尖，{feeling}如電流竄過。",
        "{env}，我用枕頭墊高臀部，方便手指更深入抽插。",
        "鏡中的我眼神迷離，{action}時全身泛起粉紅。",
        "我喜歡看著自己手指被愛液包裹進出的淫靡模樣。",
        "完全赤裸仰躺，雙腿高高抬起，{feeling}越來越強烈。"
    ],
    w7: [
        "{env}，我高高抬起雙腿，完全暴露{body}。",
        "明亮臥室裡，我凝視鏡中自己，{action}時{feeling}無法抑制。",
        "{env}，抬腿姿勢中我用力{action}，快感如潮水湧來。",
        "抬腿時手指輕輕撥開花瓣，{feeling}讓我忍不住大聲呻吟。",
        "鏡中看著自己張開的淫蕩模樣，慾望越來越強烈。",
        "抬腿姿勢讓臀部上翹，我用手指緩緩探索最深處。",
        "在{env}中，我保持抬腿姿勢，感受強烈空虛與渴望。",
        "雙腿大開的瞬間，{feeling}如巨浪般襲來。",
        "{env}，我側躺抬腿，讓{action}更深入敏感地帶。",
        "高抬雙腿架在床邊，我瘋狂{action}直到高潮。"
    ],
    w9: [
        "{env}，我俯臥回首，{body}的柔美弧線完全暴露。",
        "拱起的背脊與散落長髮，{action}讓{feeling}緩緩湧上。",
        "{env}，我高高拱起臀部，呈現後入般極致誘人視角。",
        "俯臥回首時，渾圓臀部完全敞開，我輕輕搖晃。",
        "拱起的腰臀曲線在月光下完美呈現，{feeling}無法抑制。",
        "從後方看去的自己，充滿強烈禁忌的誘惑。",
        "俯臥姿態中，我伸手向後猛烈{action}，{feeling}越來越強。",
        "{env}，我跪趴抬臀，用手指從後方猛烈自慰。",
        "俯臥高拱臀部，我對鏡欣賞自己最淫蕩的模樣。"
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
    
    // 優先使用你的 general_lines.txt
    var linesToUse = externalGeneralLines.length > 0 ? externalGeneralLines : [];
    linesToUse.forEach(function(line) {
        if (line && line.trim()) {
            result.push({ text: line.trim(), folder: '' });
        }
    });
    
    // 特殊長句大幅提高權重（每條重複 4 次）
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
    
    console.log('[1nlines.js] v9.0 getAll完成！總句數:', result.length, '（general_lines 已載入', linesToUse.length, '句）');
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
    console.log('[1nlines.js] v9.0 初始化完成，可使用 LinesLib.getAll()');
});

})(window);