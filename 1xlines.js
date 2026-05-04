// ====
//  lines_compatible.js — 杏夜絮語 · 與 1auto.htm 完全相容版
//  版本: v7.0 | 外部短句庫 + 大幅增加成人性感內容 + 完整圖片列表
// ====

(function(global){

// ═══
//  【A】詞彙池 — 大幅擴充成人/裸露/性感描述
// ═══
const POOLS = {
    env: [
        "月光如水的臥室", "潔白床單的柔軟深處", "薄霧繚繞的鏡前",
        "杏花林邊的窗前", "燭光輕搖的深夜", "瀟湘館竹影搖曳處",
        "怡紅院暖閣", "梨香院花落時分", "大觀園月下池邊",
        "秦淮河畔燈火闌珊", "絲綢帷幔低垂處", "只有喘息聲的深夜",
        "玫瑰花瓣散落的浴缸邊", "霓虹燈映照的落地窗前", "天鵝絨沙發的私密角落",
        "蒸氣瀰漫的溫泉池畔", "皮革束縛椅的邊緣", "完全漆黑只剩觸感的房間"
    ],
    
    body: [ //-的
        "鎖骨", "腰肢", "肩背", "頸項", "小腿", //"腳踝",
        "豐盈胸脯", "纖細腰線", "修長玉腿", "起伏胸廓",
        "如緞般背脊", "黛玉般纖弱身子", "寶釵般豐腴曲線",
        "渾圓挺翹臀部", "粉嫩敏感乳尖", "濕潤私密處",
        "光滑大腿內側", "微微顫抖腹部", "完全赤裸胴體",
        "被汗水浸濕肌膚", "誘人張開雙腿", "硬挺乳頭"
    ],
    
    feeling: [ //-的
        "細膩的溫熱", "欲語還休的悸動", "似夢似醒的迷離", "溫柔輕顫",
        "安靜的酥麻", "沉靜而深邃的情緒", "如春雨般的甜意", "春宵一夢的溫存",
        "寶黛之間的悸動", "葬花詞般的幽怨", "如潮汐般的感受", "漫過全身的暖意",
        "強烈快感", "下體傳來的濕熱", "無法抑制的喘息", "全身如火焚燒的慾望",
        "乳尖被觸碰的電流", "私處被撫摸的快樂", "高潮即將來臨的顫抖"
    ],
    
    action: [
        "指尖輕輕游移", "掌心緩緩貼緊", "腰肢微微弓起", "呼吸逐漸加深",
        "視線與自己交纏", "輕輕撫過鎖骨", "沿著曲線描繪", "溫柔環抱自己",
        "手指滑向濕潤的下體", "雙腿緩緩張開", "輕咬下唇壓抑呻吟",
        "臀部輕輕扭動", "揉捏豐滿的胸部", "完全敞開私密處"
    ],
    
    light: ["月光", "燭焰", "晨曦", "薄霧中的光", "杏花的影", "霓虹燈的色彩", "燭光映照的肌膚陰影"]
};

// ═══
//  【B】圖片路徑 — 完整列表（無任何省略）
// ═══
const FOLDER_IMAGES = {
    '': (function(){
        var a = [];
        for (var i = 1; i <= 99; i++) {
            a.push(i.toString().padStart(3,'0') + '.jpg');
        }
        return a;
    })(),
    'q1': [
        "000.jpg","001.jpg","002.jpg","003.jpg","004.jpg","005.jpg","006.jpg","007.jpg","008.jpg","009.jpg",
        "010.jpg","011.jpg","012.jpg","013.jpg","014.jpg","015.jpg","016.jpg","017.jpg","018.jpg","019.jpg",
        "020.jpg","021.jpg","022.jpg","023.jpg","024.jpg","025.jpg","026.jpg","027.jpg","028.jpg","029.jpg",
        "030.jpg","031.jpg","032.jpg","033.jpg","034.jpg","035.jpg","036.jpg","037.jpg","038.jpg","039.jpg",
        "040.jpg","041.jpg","042.jpg","043.jpg","044.jpg","045.jpg","046.jpg","047.jpg","048.jpg","049.jpg",
        "050.jpg","051.jpg","052.jpg","053.jpg","054.jpg","055.jpg","056.jpg","057.jpg","058.jpg","059.jpg",
        "060.jpg","061.jpg","062.jpg","063.jpg","064.jpg","065.jpg","066.jpg","067.jpg","068.jpg","069.jpg",
        "070.jpg","071.jpg","072.jpg","073.jpg","074.jpg","075.jpg","076.jpg","077.jpg","078.jpg","079.jpg",
        "080.jpg","081.jpg","082.jpg","083.jpg","084.jpg","085.jpg","086.jpg","087.jpg","088.jpg","089.jpg",
        "090.jpg","091.jpg","092.jpg","093.jpg","094.jpg","095.jpg","096.jpg","097.jpg","098.jpg","099.jpg"
    ],
    'w7': [
        "000.jpg","001.jpg","002.jpg","003.jpg","004.jpg","005.jpg","006.jpg","007.jpg","008.jpg","009.jpg",
        "010.jpg","011.jpg","012.jpg","013.jpg","014.jpg","015.jpg","016.jpg","017.jpg","018.jpg","019.jpg",
        "020.jpg","021.jpg","022.jpg","023.jpg","024.jpg","025.jpg","026.jpg","027.jpg","028.jpg","029.jpg",
        "030.jpg","031.jpg","032.jpg","033.jpg","034.jpg","035.jpg","036.jpg","037.jpg","038.jpg","039.jpg",
        "040.jpg","041.jpg","042.jpg","043.jpg","044.jpg","045.jpg","046.jpg","047.jpg","048.jpg","049.jpg",
        "050.jpg","051.jpg","052.jpg","053.jpg","054.jpg","055.jpg","056.jpg","057.jpg","058.jpg","059.jpg",
        "060.jpg","061.jpg","062.jpg","063.jpg","064.jpg","065.jpg","066.jpg","067.jpg","068.jpg","069.jpg",
        "070.jpg","071.jpg","072.jpg","073.jpg","074.jpg","075.jpg","076.jpg","077.jpg","078.jpg","079.jpg",
        "080.jpg","081.jpg","082.jpg","083.jpg","084.jpg","085.jpg","086.jpg","087.jpg","088.jpg","089.jpg",
        "090.jpg","091.jpg","092.jpg","093.jpg","094.jpg","095.jpg","096.jpg","097.jpg","098.jpg","099.jpg"
    ],
    'w9': [
        "000.jpg","001.jpg","002.jpg","003.jpg","004.jpg","005.jpg","006.jpg","007.jpg","008.jpg","009.jpg",
        "010.jpg","011.jpg","012.jpg","013.jpg","014.jpg","015.jpg","016.jpg","017.jpg","018.jpg","019.jpg",
        "020.jpg","021.jpg","022.jpg","023.jpg","024.jpg","025.jpg","026.jpg","027.jpg","028.jpg","029.jpg",
        "030.jpg","031.jpg","032.jpg","033.jpg","034.jpg","035.jpg","036.jpg","037.jpg","038.jpg","039.jpg",
        "040.jpg","041.jpg","042.jpg","043.jpg","044.jpg","045.jpg","046.jpg","047.jpg","048.jpg","049.jpg",
        "050.jpg","051.jpg","052.jpg","053.jpg","054.jpg","055.jpg","056.jpg","057.jpg","058.jpg","059.jpg",
        "060.jpg","061.jpg","062.jpg","063.jpg","064.jpg","065.jpg","066.jpg","067.jpg","068.jpg","069.jpg",
        "070.jpg","071.jpg","072.jpg","073.jpg","074.jpg","075.jpg","076.jpg","077.jpg","078.jpg","079.jpg",
        "080.jpg","081.jpg","082.jpg","083.jpg","084.jpg","085.jpg","086.jpg","087.jpg","088.jpg","089.jpg",
        "090.jpg","091.jpg","092.jpg","093.jpg","094.jpg","095.jpg","096.jpg","097.jpg","098.jpg","099.jpg"
    ]
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

// ═══
//  【C】外部短句庫
// ═══
var externalGeneralLines = [];

var fallbackGeneralLines = [
    "月光落在鎖骨上，涼涼的，像一片薄霜。",
    "我閉上眼，讓自己回到那片杏花林裡，風從耳邊經過。",
    "指尖輕輕劃過小腹，不是挑逗，只是確認自己還在。",
    "完全赤裸躺在床上，感受空氣拂過肌膚的涼意。"
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
                console.warn('[lines.js] general_lines.txt 加載失敗，使用內置備用庫');
            }
            if (callback) callback();
        }
    };
    xhr.send();
}

// ═══
//  【D】特殊群組 — 大幅增加性感成人模板
// ═══
const specialGroupTemplates = {
    q1: [
        "{env}，我枕臂仰躺，讓{body}在潔白床單上完全舒展開來。",
        "白蕾絲輕覆身體，我躺在柔軟床上，感受{feeling}。",
        "潔白床鋪如雲朵包圍我，{body}自然舒展，夜色寂靜。",
        "鏡中映出柔軟姿態，{env}，{feeling}像細雨輕拂。",
        "白蕾絲與肌膚交織，在{env}的柔光下，我沉浸於{feeling}。",
        "半躺的姿態讓{body}自然舒展，我在{env}裡品味{feeling}。",
        "{env}，我完全赤裸仰躺，{action}，{feeling}讓乳尖硬挺。",
        "雙腿微微張開躺在床上，{body}在月光下泛著誘人光澤。",
        "手指輕輕劃過{body}，感受{feeling}從下體傳來。",
        "鏡前完全敞開自己，{action}帶來強烈的{feeling}。"
    ],
    w7: [
        "{env}，我輕抬一腿，{body}在白蕾絲下若隱若現。",
        "明亮臥室裡，我凝視鏡中自己，{action}時全身泛起{feeling}。",
        "緊貼身體的白蕾絲讓呼吸柔軟，我沉浸在{feeling}中。",
        "抬高的腿部弧線在光影中延伸，我感受到{feeling}。",
        "白蕾絲如第二層肌膚，我讓姿勢自然展開，夜的溫柔一點一點滲入。",
        "潔白床單上，我以抬腿姿態，享受{feeling}在體內流動。",
        "{env}，我將雙腿高高抬起，完全暴露{body}，{feeling}如潮水般湧來。",
        "手指沿著大腿內側向上，觸碰到{body}。" //-濕潤的
    ],
    w9: [
        "{env}，我俯臥回首，{body}的柔美弧線在白紗間若隱若現。",
        "拱起的背脊與散落長髮，{action}，{feeling}緩緩湧上心頭。",
        "潔白床單上，我回望自己的身體，讓{body}在光線下呈現迷人姿態。",
        "從肩頭回眸的眼神，伴隨著{feeling}，讓夜晚變得格外動人。",
        "俯臥的我微微弓起背脊，感受{action}帶來的溫熱與輕顫。",
        "{env}，我高高拱起臀部，{body}完全敞開，等待被觸碰。",//-向後
        "回頭的瞬間，眼神柔軟，渾圓{body}在白蕾絲下輕輕呼吸。"
    ]
};

// ═══
//  【E】autoDetectFolder
// ═══
function autoDetectFolder(text) {
    if (text.indexOf('枕臂仰躺') !== -1 || text.indexOf('赤裸仰躺') !== -1 || text.indexOf('完全敞開') !== -1) return 'q1';
    if (text.indexOf('輕抬一腿') !== -1 || text.indexOf('抬腿') !== -1 || text.indexOf('雙腿張開') !== -1) return 'w7';
    if (text.indexOf('俯臥回首') !== -1 || text.indexOf('拱起的背脊') !== -1 || text.indexOf('高高拱起臀部') !== -1) return 'w9';
    return '';
}

// ═══
//  【F】getAll()
// ═══
function getAll() {
    var result = [];
    
    var linesToUse = (externalGeneralLines.length > 0) ? externalGeneralLines : fallbackGeneralLines;
    for (var i = 0; i < linesToUse.length; i++) {
        if (linesToUse[i] && linesToUse[i].trim()) {
            result.push({ text: linesToUse[i].trim(), folder: '' });
        }
    }
    
    var folders = ['q1', 'w7', 'w9'];
    for (var f = 0; f < folders.length; f++) {
        var folder = folders[f];
        var templates = specialGroupTemplates[folder];
        for (var repeat = 0; repeat < 3; repeat++) {  // 長句權重提高
            for (var t = 0; t < templates.length; t++) {
                var cooked = buildLine(templates[t]);
                result.push({ text: cooked, folder: folder });
            }
        }
    }
    
    console.log('[lines.js] v7.0 getAll 完成，總句數:', result.length);
    return result;
}

function buildLine(template) {
    return template.replace(/{(\w+)}/g, function(_, key) {
        var pool = POOLS[key];
        if (!pool || !pool.length) return '{' + key + '}';
        return pool[Math.floor(Math.random() * pool.length)];
    });
}

// ═══
//  【G】對外暴露
// ═══
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