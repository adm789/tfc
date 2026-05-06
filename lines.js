// ============================================================
//  lines_compatible.js — 杏夜絮語 · 與 1auto.htm 完全相容版 v7.0
//  大幅增加成人/性感內容 + 長句權重 + 完整圖片陣列
// ============================================================

(function(global){

// ════════════════════════════════════════════════════════════
//  【A】詞彙池 — 擴充大量成人性感詞彙
// ════════════════════════════════════════════════════════════
const POOLS = {
    env: [
        "月光如水的臥室", "潔白床單的柔軟深處", "薄霧繚繞的鏡前",
        "杏花林邊的窗前", "燭光輕搖的深夜", "瀟湘館竹影搖曳處",
        "怡紅院暖閣", "梨香院花落時分", "大觀園月下池邊",
        "秦淮河畔燈火闌珊", "絲綢帷幔低垂處", "只有喘息聲的深夜",
        "玫瑰花瓣散落的浴室", "霓虹燈下的落地窗前", "天鵝絨沙發的私密角落",
        "蒸氣瀰漫的溫泉池畔", "皮革束縛椅邊緣", "完全漆黑只剩觸感的房間"
    ],
    
    body: [
        "鎖骨", "腰肢", "肩背", "頸項", "小腿", "腳踝",
        "豐盈的胸脯", "纖細的腰線", "修長的玉腿", "起伏的胸廓",
        "如緞般的背脊", "黛玉般纖弱的身子", "寶釵般豐腴的曲線",
        "渾圓挺翹的臀部", "粉嫩硬挺的乳尖", "濕潤粉嫩的私密處",
        "光滑敏感的大腿內側", "微微顫抖的小腹", "完全赤裸的誘人胴體",
        "被慾望染紅的肌膚", "汗水淋漓的性感身體"
    ],
    
    feeling: [
        "細膩的溫熱", "欲語還休的悸動", "似夢似醒的迷離", "溫柔的輕顫",
        "安靜的酥麻", "沉靜而深邃的情緒", "如春雨般的甜意", "春宵一夢的溫存",
        "寶黛之間的悸動", "葬花詞般的幽怨", "如潮汐般的感受", "漫過全身的暖意",
        "強烈到無法抑制的快感", "下體濕熱的空虛", "乳尖被刺激的電流",
        "全身如火焚燒的慾火", "高潮即將來臨的顫抖", "被徹底填滿的滿足"
    ],
    
    action: [
        "指尖輕輕游移", "掌心緩緩貼緊", "腰肢微微弓起", "呼吸逐漸加深",
        "視線與自己交纏", "輕輕撫過鎖骨", "沿著曲線描繪", "溫柔環抱自己",
        "手指滑向濕潤私處", "雙腿緩緩張開", "輕揉自己豐滿胸部",
        "臀部輕輕扭動", "輕咬下唇壓抑呻吟", "完全敞開最隱私部位"
    ],
    
    light: ["月光", "燭焰", "晨曦", "薄霧中的光", "杏花的影", "瀟湘館的燈影", "霓虹燈柔光"]
};

// buildLine 函數
function buildLine(template) {
    return template.replace(/{(\w+)}/g, function(_, key) {
        var pool = POOLS[key];
        if (!pool || !pool.length) return '{' + key + '}';
        return pool[Math.floor(Math.random() * pool.length)];
    });
}

// ════════════════════════════════════════════════════════════
//  【B】圖片路徑 — 完整陣列（不省略）
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
    if (!list.length) return '001.jpg';
    return list[Math.floor(Math.random() * list.length)];
}

function imgSrc(folder, filename) {
    return folder ? `./img/${folder}/${filename}` : `./img/${filename}`;
}

// ════════════════════════════════════════════════════════════
//  【C】外部短句庫
// ════════════════════════════════════════════════════════════
var externalGeneralLines = [];

// 內置 fallback
var fallbackGeneralLines = [
    "月光落在鎖骨上，涼涼的，像一片薄霜。",
    "我閉上眼，讓自己回到那片杏花林裡，風從耳邊經過。",
    "指尖輕輕劃過小腹，不是挑逗，只是確認自己還在。",
    "夜晚的寂靜中，我輕輕撫摸自己敏感的部位。",
    "鏡中的自己赤裸而誘人，讓我忍不住繼續探索。"
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
                console.log('[lines.js] 已加載外部短句庫:', externalGeneralLines.length, '句');
            } else {
                console.warn('[lines.js] general_lines.txt 加載失敗，使用內置備用');
            }
            if (callback) callback();
        }
    };
    xhr.send();
}

// ════════════════════════════════════════════════════════════
//  【D】特殊群組 — 大幅增加性感成人模板（每類15+條）
// ════════════════════════════════════════════════════════════
const specialGroupTemplates = {
    q1: [
        "{env}，我枕臂仰躺，讓{body}在潔白床單上完全舒展開來。",
        "白蕾絲輕覆身體，我躺在柔軟床上，感受{feeling}。",
        "潔白床鋪如雲朵包圍我，{body}自然舒展，夜色寂靜。",
        "鏡中映出柔軟姿態，{env}，{feeling}如細雨輕拂。",
        "白蕾絲與肌膚交織，在{env}的柔光下，我沉浸於{feeling}。",
        "半躺的姿態讓{body}自然舒展，我在{env}裡品味{feeling}。",
        "{env}，我完全赤裸仰躺，雙腿微微分開，{feeling}從下體湧起。",
        "我在鏡前輕揉{body}，乳尖迅速硬挺起來。",
        "仰躺時手指沿著大腿內側向上，觸碰到濕潤的私密處。",
        "潔白床單上，我拱起腰肢，讓{body}呈現最誘人的弧度。",
        "月光灑在赤裸身體上，我輕輕撫摸自己，感受{feeling}。",
        "完全敞開的姿態，在{env}中沉淪於慾望。",
        "雙手捧起豐滿胸部，拇指輕揉乳尖，{feeling}如電流竄過。",
        "我躺在床上，慢慢張開雙腿，讓夜風輕拂最敏感的地方。"
    ],
    w7: [
        "{env}，我輕抬一腿，{body}在白蕾絲下若隱若現。",
        "明亮臥室裡，我凝視鏡中自己，{action}時全身泛起{feeling}。",
        "緊貼身體的白蕾絲讓呼吸柔軟，我沉浸在{feeling}中。",
        "抬高的腿部弧線在光影中延伸，我感受到{feeling}。",
        "白蕾絲如第二層肌膚，我讓姿勢自然展開，夜的溫柔一點一點滲入。",
        "潔白床單上，我以抬腿姿態，享受{feeling}在體內流動。",
        "{env}，我高高抬起雙腿，完全暴露濕潤的私處。",
        "抬腿時手指輕輕撥開花瓣，{feeling}讓我忍不住輕吟。",
        "鏡中看著自己張開的模樣，慾望越來越強烈。",
        "抬腿姿勢讓臀部上翹，我用手指緩緩探索內部。",
        "在{env}中，我保持抬腿姿勢，感受空虛與渴望。",
        "雙腿大開的瞬間，{feeling}如潮水般襲來。"
    ],
    w9: [
        "{env}，我俯臥回首，{body}的柔美弧線在白紗間若隱若現。",
        "拱起的背脊與散落長髮，{action}，{feeling}緩緩湧上心頭。",
        "潔白床單上，我回望自己的身體，讓{body}在光線下呈現迷人姿態。",
        "從肩頭回眸的眼神，伴隨著{feeling}，讓夜晚變得格外動人。",
        "俯臥的我微微弓起背脊，感受{action}帶來的溫熱與輕顫。",
        "回頭的瞬間，眼神柔軟，{body}在白蕾絲下輕輕呼吸。",
        "{env}，我高高拱起臀部，呈現後入般誘人視角。",
        "俯臥回首時，渾圓臀部完全暴露，我輕輕搖晃。",
        "拱起的腰臀曲線在月光下完美呈現，{feeling}無法抑制。",
        "從後方看去的自己，充滿禁忌的誘惑。",
        "俯臥姿態中，我伸手向後觸碰自己，{feeling}越來越強。"
    ]
};

// ════════════════════════════════════════════════════════════
//  【E】autoDetectFolder
// ════════════════════════════════════════════════════════════
function autoDetectFolder(text) {
    if (text.indexOf('枕臂仰躺') !== -1 || text.indexOf('赤裸仰躺') !== -1 || text.indexOf('輕揉') !== -1) return 'q1';
    if (text.indexOf('輕抬') !== -1 || text.indexOf('抬腿') !== -1 || text.indexOf('張開') !== -1) return 'w7';
    if (text.indexOf('俯臥') !== -1 || text.indexOf('拱起') !== -1 || text.indexOf('回首') !== -1) return 'w9';
    return '';
}

// ════════════════════════════════════════════════════════════
//  【F】getAll() — 完整版
// ════════════════════════════════════════════════════════════
function getAll() {
    var result = [];
    
    var linesToUse = (externalGeneralLines.length > 0) ? externalGeneralLines : fallbackGeneralLines;
    for (var i = 0; i < linesToUse.length; i++) {
        if (linesToUse[i] && linesToUse[i].trim()) {
            result.push({ text: linesToUse[i].trim(), folder: '' });
        }
    }
    
    // 特殊長句 — 每條重複 3 次（高權重）
    var folders = ['q1', 'w7', 'w9'];
    for (var f = 0; f < folders.length; f++) {
        var folder = folders[f];
        var templates = specialGroupTemplates[folder];
        for (var repeat = 0; repeat < 3; repeat++) {
            for (var t = 0; t < templates.length; t++) {
                var cooked = buildLine(templates[t]);
                result.push({ text: cooked, folder: folder });
            }
        }
    }
    
    console.log('[lines.js] v7.0 getAll完成，總句數:', result.length);
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
    console.log('[lines.js] v7.0 初始化完成');
});

})(window);