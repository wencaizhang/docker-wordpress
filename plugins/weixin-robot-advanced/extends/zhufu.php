<?php
/*
Plugin Name: 新春祝福短信
Plugin URI: http://wpjam.net/item/wpjam-weixin-zhufu/
Description: 获取新春祝福短信，发给好友。
Version: 1.0
*/

// 替换规则：2015=>2016 羊年=》猴年，在搜索羊，删除，马年=》羊年

add_filter('weixin_builtin_reply', 'weixin_robot_zhufu_builtin_reply');
function weixin_robot_zhufu_builtin_reply($weixin_builtin_replies){
    $weixin_builtin_replies['祝福'] = array('type'=>'prefix', 'reply'=>'新春祝福语', 'function'=>'weixin_robot_zhufu_reply');
    return $weixin_builtin_replies;
}

add_filter('weixin_response_types','weixin_robot_zhufu_response_types');
function weixin_robot_zhufu_response_types($response_types){
    $response_types['zhufu'] = '新春祝福语';
    return $response_types;
}

function weixin_robot_zhufu_reply($keyword){
    global $weixin_reply;

    $keyword    = str_replace('祝福', '', $keyword);
    $keyword    = ($keyword)?$keyword:'朋友';

    $weixin_reply->textReply(weixin_robot_get_zhufu_results($keyword));
    $weixin_reply->set_response('zhufu');
}

function weixin_robot_get_zhufu_results($keyword=''){

$zhufu['父母'] = array(
"千里之遥，我站在僻静的窗台旁，透过新年的氛围，遥望过去。时间凝固了，而你是这风景上灿烂的亮点，我用心在这幅画上题写祝福！父母新年快乐！",
"春节到，祝福送，愿你开心永无终；春节到，祝福送，祝你生意多兴隆；春节到，祝福送，疾病远离去无踪；春节到，祝福送，家庭美满如意从。祝爸妈春节快乐！",
"有些事并不因时光流逝而褪去，有些人不因不常见面而忘记，记忆里你是我至爱的亲人，在这迎新之际，恭祝事业和健康蒸蒸日上。",
"新春就要到，新春祝福提前到。祝你有人缘，事业顺利不心烦；祝你有情缘，爱情甜蜜心也甜；祝你有财源，腰包鼓鼓金钱花不完。最后预祝爸妈新年快乐！",
"饺子里裹着幸福的味道，音乐里和着快乐的节拍，彩灯里闪烁着明亮的光彩，笑语里回荡着幸福的余味，节日里浓缩着永恒的祝福。真心祝我的爸妈春节快乐！",
"鸿雁送禧祝福到，祝福我的父母：快乐比昨天多一点点，幸福比过去多一点点，好运比福气多一点点，甜蜜比蜜糖多一点点。",
"祝福是份真心意，不是千言万语的表白。一首心曲，愿爸妈你们岁岁平安，事事如意！",
"新年送礼：一斤花生二斤枣、愿你跟着好运跑；三斤桔子四斤蕉、财源滚进你腰包；五斤葡萄六斤橙、愿你心想事就成；八斤芒果十斤瓜、愿你新年天天乐开花。",
"希望这是你收到的第一条祝福，比我早的统统和谐掉，让我抢个沙发坐坐；煽情的话放在心里，要低调；我发的不是短信，是祝福；春节需要快乐，你懂的。",
"张灯结彩闹新春，合家团圆享天伦。喜气洋洋访亲友，笑语盈盈情意久。天地一派幸福景，愿爸妈春节好心情。吉祥如意身旁绕，爱情事业皆欢笑。愿你们春节快乐。",
"一句平淡如水的问候，很轻；一声平常如纸的祝福，很真；摘一颗星，采一朵云，装入平安的信封里送给你，让快乐好运时刻围绕着你！你新年快乐！",
"与快乐击掌，和幸福恰恰，与如意拍手，和顺心探戈，与成功对唱，和美满慢摇，与鞭炮开心大笑，迎接新年，与人中龙凤，偷偷短信，互递祝福！祝爸妈猴年大吉大利，百事顺心，万事如意！",
"星光闪烁的是希望的光芒，白云托载的是梦想的天堂，彩虹架设的是幸福的桥梁，丹霞铺就的是明天的辉煌，短信捎去的是新年的吉祥！祝你春节快乐！",
"一牛吼地，二蛇戏珠，三春不老，四海祥云，五谷丰登，六合之内，七彩缤纷，八祥八瑞，九鼎生光，十面八方，千秋伟业，万福云集。新年快乐！",
"爆竹声声催促着归乡的脚步，美酒杯杯洋溢着团圆的喜悦，对联张张充满着未来的期盼，短信句句表达着真诚地祝愿。春节到了，祝我们一家合家团圆，幸福美满！",
"春节一过继续忙，为了生活苦奔忙。工作辛劳多努力，关切健康不能忘。祝愿爸爸工作顺，万事如意创新高；祝愿妈妈身体好，身体健康不受伤！",
"年之初，信息传；字不多，情无限；兴致好，拜大年；事业兴，家美满；身体好，多挣钱；敬长者，爱少年；朋友多，结善缘；欢笑声，到永远。春节好！"
);
$zhufu['小孩'] = array(
"因为你的可爱，特意给你关怀；晚上被子要盖，免得手脚冷坏；没事刁根骨头，这样可以补钙；不要说我太坏，祝你猴年愉快！",
"时间流逝的是平淡，牵挂酝酿的是情谊，关切流露的是真情，好的朋友我真的好想念。为你许下新年的祝愿：愿你幸福甜蜜无边，快快乐乐每一天！",
"星星落下要三秒，月亮升起要一天，地球公转要一年，想一个人要二十四小时，爱一个人要一辈子，但一句贴心祝福只要一秒：新年快乐！",
"春联春潮扬春意，鞭炮声声除旧气，猴年春节共欢聚，美好祝福送给你。祝春节快乐、猴年吉祥、好运笼罩、合家团圆。",
"乘着健康快车，走过幸福大道，捧着玉如意，揣着金元宝，擎着平安果，背着吉祥树，然后把这些都送给你。不要太感动哟！因为我要你猴年快乐！",
"好想，好想，好想，好想，好想用阳光温暖你，用星光装点你，用美酒陶醉你，用美食满足你，用幸福淹没你。可我不做上帝很久了，只能用短信祝福你。祝新年快乐！",
"来年开心，今生快乐，今世平安，小家和睦，愿你生命中的个个愿望都能得到实现，新年快乐！",
"春节到，多热闹，敲锣打鼓放鞭炮；欢笑声，多响亮，舞龙舞狮踩高跷；祝福语，多精彩，祝你平安又多智！祝你春节快乐，学业进步！",
"猴年到，把时光剪成一束烟花，绽放快乐繁华；把团圆煮成一碗饺子，温暖幸福一家；把祝福编成一条短信，送到你的手里。祝你新春大吉！",
"新年天气预报：你将会遇到金钱雨，幸运风，友情雾，爱情露，健康霞，幸福云，顺利霜，美满爽，安全雹，开心闪，它们将伴你一生！猴年快乐！",
"新年已成过去式，工作正在进行时。工作可以找乐趣，烦恼只能留过去。相逢领导需敬礼，偶遇同事笑嘻嘻。一团和气好身体，学业进步属于你。年后开学，万事如意！",
"新年到到到，祝您好好好，福星照照照，前程妙妙妙，收入高高高，烦恼少少少，喜讯报报报，圆梦早早早，全家笑笑笑。好运就来到！",
"祝愿：一元复始、万象更新；年年如意、岁岁平安；财源广进、富贵吉祥；幸福安康、庆有余；竹报平安、福满门；喜气洋洋！",
"新的一年，愿您抱着平安，拥着健康，揣着幸福，携着快乐，搂着温馨，带着甜蜜，带着财运，拽着吉祥，迈入新年，快乐度过每一天！",
"把握年轻，守护新春，幸福猴年，吉祥欢颜，把握今朝，感受欢喜，合家团圆，热情团聚，吉祥14，告别13，攒起斗志，勇敢崛起，幸福快乐，愿你成就新的传奇。",
"愿你在新的一年里：抱着平安，拥着健康，揣着幸福，携着快乐，搂着温馨，带着甜蜜，牵着财运，拽着吉祥，迈入新年，快乐度过每一天！",
"新年好运到，好事来得早！朋友微微笑，喜庆围你绕！花儿对你开，鸟儿向你叫。生活美满又如意！喜庆！喜庆！一生平安如意！"
);
$zhufu['领导'] = array(
"春节假期要过好，但不要太疲劳，饭要吃好，但不用吃太饱，莫睡懒觉，但休息不能少，朋友联系一定要，千万别忘了，发个短信问声领导好，才能天天没烦恼！",
"贴一副春联，写满如意美满；燃一筒烟花，绽放幸福和谐；挂一盏红灯，点亮温馨安康；送一份祝福，传递吉祥快乐；春节到，愿你大吉大利，领导春节愉快！",
"春节送你一件外套，前面是平安，后面是幸福，吉祥是领子，如意是袖子，快乐是扣子，口袋里满是温暖，穿上吧，让它相伴你的每一天！领导新年快乐！",
"猴年来到，问候祝福串成串，好运吉祥串成串，幸福快乐串成串，财富健康串成串，愿领导你：生活串串香，爱情串串甜，事业串串红，合家串串福。",
"喜鹊报喜枝头叫，和平飞鸽凑热闹。红幅对联映彩虹，大红福字盈门笑。玉盘水饺不能少，发财添福进财宝。新年定能赚钞票。新春愉快阖家圆，财运亨通结财缘。祝领导你春节身体健康，吉祥如意神采飞扬。",
"炮竹惊梦春节到，红日东升吉祥照。三世同堂家宴聚，喜乐欢笑冲云霄。春风杨柳万千条，瑞雪纷飞丰年兆。人寿年丰三代旺，财源滚滚连年高。祝你春节如意笑，长寿健康喜乐道！",
"春节短信到来喜事多，阖家团员幸福多；心情愉快朋友多，身体健康快乐多；一切顺利福气多，新年吉祥生意多；祝愿领导您好事多！多！多！",
"祝你在新的一年里：财源滚滚，发得像肥龙；身体棒棒，壮得像狗熊；爱情甜甜，美得像蜜蜂；财源滚滚，多得像牛毛！领导新年好！",
"美酒香飘万里，遍邀朋友知己，畅叙离后别情，酒醉浓浓情谊。水饺色美味鲜，家人围坐桌前，珍惜团圆时光，享受幸福流年。春节近在眼前，短信表达祝愿，愿领导你年年健康，开心岁岁平安！",
"新年到放鞭炮，拱拱手祝福好，身体棒乐陶陶，事业成薪水高，夫妻间分红包，兄弟间酒不少，敬长辈送补药，会亲友真热闹，送旧符展新貌，春节乐天天笑！",
"衷心的祝愿你在新的一年里，所有的期待都能出现，所有的梦想都能实现，所有的希望都能如愿，所有的付出都能兑现！",
"祝领导你正财、偏财、横财，财源滚滚；亲情、友情、私情，情情如意；官运、财运、桃花运，运运亨通。",
"惜别羊年好运，迎接猴年吉祥，舞出幸福旋律，谱写快乐篇章，送上真诚祝愿，猴年到啦，愿你日子甜蜜依旧，好运总伴左右，如意围在身边，领导新春大吉！",
"今年春节不送礼，送礼只送俏祝福，送你一份健康福，福寿无边心舒畅；送你一份平安福，事业兴旺万事顺；送你一份吉祥福，鸿运当头不可挡；送你一份快乐福，开心自在乐逍遥；再送你一份福满多，万事如意笑开怀。祝你猴年交好运，五福临门幸福长！",
"新年到，我将好运作邮票、真情作邮戳、幸福作信封，写上健康、填上快乐、加上好运、注上平安，附上吉祥，然后把它放进甜蜜的邮筒，祝你猴年春节快乐。",
"猴年祝福不求最早，不求最好，但求最诚！我的祝福不求最美，不求最全，但求最灵！预祝：你及你的家人在新的一年里身体健康，万事如意！",
"猴年到了，大家发了很多短信祝福你，基本能代表我的心意。另外我再补充一点：你要理个圆圆的发型，脸上带着开心笑容，这样才与圆蛋快乐相吻合！猴年快乐！",
"不需要多点拥有，只需要少点计较；不需要想得复杂，只需要过得简单；不需要追名逐利，只需要淡泊随意；不需要阳光明媚，只需要心情晴朗；不需要经常问候，只需要偶尔祝福：猴年到，大吉大利，万事如意，愿你心情姹紫嫣红，喜圆幸福美梦！",
"烟花的一瞬间是快乐；流星的一瞬是祈愿；思念的一瞬是感动。而我只想让你看到短信的一瞬能够明白：无论你天涯海角，都会深深祝福你猴年快乐！",
"新的一年又来到，短信祝福来报道，一福福寿绵长，二福富足尊贵，三福健康安宁，四福仁善宽厚，五福善终无灾、无祸、无病痛、无烦恼，五福临门贺新年。",
"新年的风，吹走你的忧郁；新年的雨，洗掉你的烦恼；新年的阳光，给你无边的温暖；新年的空气，给你无尽的喜悦；新年的祝福，给你无限的问候。",
"寄一句真真的问候，字字句句都祝你新年快乐；送一串深深的祝福，分分秒秒都祈祷你新年平安；传一份浓浓的心意，点点滴滴都愿你猴年如意！",
"新年钟声还未敲响，我的祝福早来到，一祝新年心情好二祝工作高升早，三祝烦恼都吓跑四祝开心直到老，五祝欢聚真美妙六祝猴年快乐乐逍遥。",
"锣鼓喧天猴年到，举国欢庆乐淘淘。家家户户庆团圆，迎来送往多热闹。亲朋相聚祝福多，开开心心把话聊。边防子弟多辛苦，驻守边疆独寂寞。换来平安幸福年，孤独艰苦也心甘。祝子弟兵新年快乐！",
"新的一年，祝好事接二连三，心情四季如春，生活五颜六色，七彩缤纷，偶尔八点小财，烦恼抛到九霄云外！请接受我十全十美的祝福。祝猴年春节快乐！",
"通知：春节了，请把烦恼收藏，把快乐搜索，把健康下载，把财运复制，把如意粘贴，把平安保存，把幸福编辑，把生活刷新，嘿嘿，你就偷着乐吧。猴年快乐！",
"情是组成的文字，爱是输出的电波，用祝福酿杯醇香的美酒，在喜庆的日子里，让我送去文字，输出电波，献上美酒，愿朋友幸福、开心、猴年快乐！",
"新年祝福赶个早：一祝身体好；二祝困难少；三祝烦恼消；四祝不变老；五祝心情好；六祝忧愁抛；七祝幸福绕；八祝收入高；九祝平安罩；十祝乐逍遥！",
"新年到了，将一声声贴心的问候，一串串真挚的祝福，一片片深厚的情意，乘着爱心的短信，穿越千山万水，飘进你的心坎。祝你猴年快乐！"
);
$zhufu['姐妹'] = array(
"向东方采了一些吉祥，问西方要了一丝好运，朝南方借了一份如意，在北方扯了一把平安，将这些全部送给你，新年到了，愿姐妹四面八方的幸福全部涌向你，猴年快乐！",
"给你一份温暖，愿寒冷远离你；给你一份快乐，愿悲伤远离你；给你一份祝福，把所有的幸福送给你。猴年到了，愿姐妹以后的道路一切顺利！",
"红烛摇来团圆的情意，灯笼照亮平安路途，春联盈满美丽的企盼，佳酿散发吉祥的气息，烟花描绘幸福的图画，新春寄托真诚的祝福：姐妹春节快乐。",
"心情的欢悦，元旦的喜悦，思念的愉悦，祝福的短信为你跳跃，温馨的笑容为你取悦，美好的愿望为你腾跃，提前祝你新年天天看好，成功之门等你跃！",
"街街巷巷喜气洋洋，家家户户彩结灯张，老老少少笑声爽朗，处处奏响春的乐章，冬去春来人倍忙，春节临近祝福长：春节愉快，新春吉祥！",
"事业无须惊天地，有成就行。友谊无须说谜语，想着就行。金钱无须取不尽，够用就行。朋友无须有多少，有你就行。姐妹祝你新年快乐！",
"春节到，大拜年：一拜全家好；二拜困难少；三拜烦恼消；四拜不变老；五拜儿女孝；六拜幸福绕；七拜忧愁抛；八拜收入高；九拜平安罩；十拜乐逍遥。",
"新出炉的短信，带着热腾腾的福气，滚烫烫的好运，香喷喷的吉祥，作为你团圆饭上的一道佳肴，吃了身体棒棒的，心里暖暖的。姐妹猴年快乐！",
"新年到，我许下愿望，愿你：寒冷时，有人送暖不畏寒；饥饿时，有人送饭不担忧；困难时，有人伸手来扶持；孤单时，有人陪伴寂寞消。愿姐妹你开心每一天。",
"每一次的真诚，我们不需要寻找，只需要感受；每一份的祝福，我们不需要怀疑，只需要接受。猴年到，愿你感受到我的真诚，接受我的祝福，开心幸福到永远。",
"新的开始新希望，新的一天新阳光，开始新的追求，播下新的梦想，翻开新的一页，写下新的辉煌。新的一年开始，送姐妹你深深的祝福，猴年快乐。",
"衷心的祝愿你在新的一年里，所有的期待都能出现，所有的梦想都能实现，所有的希望都能如愿，所有的付出都能兑现！",
"春节祝愿您，所爱的人幸福，所做的梦实现，所干的事顺利，所要的钱赚到，所见的天湛蓝，所踏的地丰收，所走的路平安，所听的话开怀。姐妹春节幸福快乐！",
"该吃吃，该喝喝，疾病不往你身搁；该哭哭，该乐乐，你想咋着就咋；秋深了，天凉了，心神气爽你开怀；天高了，云飘了，风轻云淡你幸福；读一读，乐一乐，祝福看你笑了。祝姐妹幸福快乐！",
"忧伤杳然去，快乐踏雪来。吉祥伴梅开，无奈随风散。新春已驾到，祝福不能少。今朝合家欢，幸福永绵绵。恭祝姐妹春节快乐！",
"新的一年来到，新的祝愿送到：祝你人缘实现广覆盖、健康突破保基本、好运争取多层次、财运走向可持续，幸福保持稳步涨。姐妹猴年快乐！",
"新年已来到，向你问个好。开心无烦恼，好运跟着跑。家人共团聚，天伦乐逍遥。朋友相扶持，心情不寂寥。事业风水顺，金银撑荷包。祝姐妹猴年生活开心步步高！",
"新年的钟声即将敲响，新年的焰火即将点燃，新年的祝福迎着曙光，喜气洋洋地走进你心房。愿快乐对你过目不忘，愿你的幸福地久天长！猴年快乐！",
"和风细雨润人间，喜气洋洋迎新年，爱情美满享百年，幸福生活乐无边，洪福高照福无限，天降祥瑞体康健，事事顺心好运伴，快乐逍遥赛神仙。姐妹春节快乐！",
"祝你在新年里：一家和和睦睦，一年开开心心；一生快快乐乐，一世平平安安；天天精神百倍，月月喜气洋洋；年年财源广进，岁岁平安祥和！姐妹春节快乐！"
);
$zhufu['兄弟'] = array(
"新春孕育新生命，万象更新新起点，祝您在猴年里，家庭幸福上新台阶，工作晋升有新气象，人际交往具新活力，健康管理有新策略。",
"钟声是我的问候，歌声是我的祝福，雪花是我的贺卡，美酒是我的飞吻，清风是我的拥抱，快乐是我的礼物！统统都送给你，祝你新年快乐！",
"新年到，新年到，君不见烦恼已多年，幸福知尔快乐绕，猴年新春祝福到，亲朋好友不能少，新人新事新气象，团聚猴年闹闹闹!祝，猴年吉祥！",
"新年又至送去大衣一件，用它裹住温情，怀揣浪漫的爱情，兜满幸福的友情，扣紧身体健康，阻隔厄运，每天都有好心情！",
"新年祝福挤满堂，新年短信手机上，健康快乐不忘掉，平安欢笑心情好，天天跟着时尚跑，红包礼品不会少，祝你猴年快乐！",
"繁星点点，夜色宁静，在你回家的路上，送上我真挚的祝福。繁星如我，伴你同行，道一声朋友，祝你新年好运！",
"祝你新年快乐！事业顺心顺意，工作顺顺利利，爱情甜甜蜜蜜！身体有用不完的力气，滚滚财源广进！身体倍儿棒，吃饭倍儿香，牙好胃口就好，事事开心，事事顺利！",
"金钱是一种有用的东西，但是只有在你觉得知足的时候，它才会带给你快乐。所以你应该把多余的钱交给我，这样我们两个都会感到快乐了！",
"祝你财源滚滚，发得像肥猪；身体棒棒，壮得像狗熊；爱情甜甜，美得像蜜蜂；好运连连，多得像牛毛。",
"千里之遥，我站在僻静的窗台旁，透过新年的氛围，遥望过去。时间凝固了，而你是这风景上灿烂的亮点，我用心在这幅画上题写祝福！新年快乐！",
"一丝吉祥祝福，一丝温馨温暖，一丝友情亲情，一丝团聚团圆，一丝真诚祝愿，一丝感悟心田，一丝新年快乐，一丝吉祥相伴，一丝猴年神韵，一起幸福新年，愿新春快乐，一片欢颜。",
"一片绿叶，饱含着它对根的情谊；一句贺词，浓缩了我对你的祝愿。又是一个美好的开始–新年岁首，祝成功和快乐永远伴随着你。",
"新春到来人欢喜，神采奕奕走亲戚，几项叮嘱心头记：出行安全要注意，吃喝玩乐莫心急，搓麻过久伤身体，燃放烟花危险避，健康快乐新春里，祝您佳节玩的舒心，过得畅意！",
"祝福加祝福叠满无数个祝福，祝福减祝福又是祝福的起点，祝福乘祝福是无限美好祝福，祝福除祝福是惟美厚重的祝福，没有什么比祝福更贵重的礼物！祝福你猴年幸福无边。",
"祝你新年正财、偏财、横财，财源滚滚；亲情、友情、私情，情情如意；官运、财运、桃花运，运运亨通。"
);
$zhufu['老公'] = array(
"猴年到，小小短信来问好，新年收获可不小，好运天天交，困难见你跑，事业节节高；心情时时好，烦恼立马消，生活甜如枣。老公猴年快乐！",
"春节问候到，烦恼全感冒，健康来报道，幸福开口笑。春节祝福到，晦气不再扰，财运追着跑，日子乐逍遥。新春佳节，愿老公开开心心，永远幸福。",
"猴年佳节要来到，提前向你问个好，身体倍健康，心情特别好；好运天天交，口味顿顿妙。最后祝您及您的家人：猴年好运挡不住，猴年财源滚滚来！",
"春节到，亲朋聚。看电视，打麻将。沙发坐，一整天。颈椎疼，腰椎痛。过个节，受场罪。君且住，快运动。站起来，动一动。摇摇头，扭扭腰。祝健康，幸福绕。",
"短信传递送祝福，春节拜年我先到，猴年福星紧围绕。祝你猴年在新的一年里，好运已经联通，真情永不移动。",
"春联春潮扬春意，鞭炮声声除旧气，猴年春节共欢聚，美好祝福送给你。祝猴年春节快乐、猴年吉祥、好运笼罩、合家团圆。",
"在新的一年里，我把一千朵鲜花送给你，要你好好爱自己；一千只纸鹤送给你，让烦恼远离你；一千颗幸运星送给你，让好运围绕着你；一千枚开心果送给你，让好心情天天陪伴老公！",
"快乐是自导自演的戏，总希望你多些欢喜，幸福是自弹自普的曲，总希望你生活甜蜜，问候是真心诚意发自心底，总希望温暖你心里，祝老公新年一路顺风，事事如意。",
"春节来到，我把祝福送到：祝你快乐幸福永相随，家庭美满乐常在，身体健康笑开怀，财源滚滚满车栽，事业顺利好运来，最后祝你春节愉快。",
"我的祝福像飘浮在天下的片片白云，无时不在；像连绵不绝的悠悠流水，永不断绝；像寒冷冬日的明媚阳光，恒久温暖。祝老公猴年快乐。",
"春节一到忙返乡，背上行囊满载归，妻儿老小在家盼，游子归心似飞箭，祝坐上幸福快车，平平安安到家，合家团聚甜蜜。老公新年快乐！",
"春节大放假，健康不放假。春节温馨提醒您：饮食适度，均配荤素，合理饮食健康张弛有度；再好美食，吃得合适，胃好疾病就被吓跑。祝你春节吃得开心，吃得健康！",
"春节到了，鞭炮声此起彼伏，欢笑声连绵不断，祝福声不绝于耳，我的短信见逢插针，飞进你的手机，祝你猴年大吉，新春快乐，全家幸福，万事胜意！",
"新年到，新年到，短信早早去报到。祝你新年好，事业顺利工资高；万事如意心情好；吉祥高照好运到；幸福生活常微笑。老公猴年快乐！",
"有家的人是幸福的，幸福在牵挂，有家的人是温馨的，温馨在亲情，有家的人是充盈的，充实在梦想，有家的人是愉快的，愉快在归乡的车上，愿老公新年工作顺利，享受温馨。"
);
$zhufu['客户'] = array(
"客户们新春新年好！我的祝福到!新的一年开始了。在此真诚地祝愿你猴年身体健康，工作顺利，生活美满，笑口常开！",
"人之相惜惜于品，人之相敬敬于德，人之相信信于诚，人之相拥拥于礼，人之相伴伴于爱，人之相交交于情，人之相传传思念：新年新气象，客户猴年快乐！",
"愿我的祝福像高高低低的风铃，给你带去叮叮铛铛的快乐！客户猴年愉快！",
"值此新春，感谢您过去一年对我的支持，给您拜年了！祝您在猴年，位高权重责任轻，钱多事少离家近，每天睡到自然醒，工资领到手抽筋，奖金多到车来运，别人加班你加薪。猴年鸿运！",
"酒越久越醇，朋友相交越久越真；水越流越清，世间沧桑越流越淡。祝新年快乐，时时好心情！",
"新年的钟声就要响起，愿这吉祥的钟声能为您及家人带来平安、幸福、健康和快乐！祝新年快乐，万事如意！一年又比一年强。",
"祝你：生活越来越好，年龄越看越小，经济再往上跑，别墅钻石珠宝，开着宝马炫耀，挣钱如同割草，感觉贼好！哈哈，为你这样的朋友骄傲！",
"加薪买房购小车，娶妻生子成家室！祝您好事连连，好梦圆圆。客户猴年愉快！",
"白云离蓝天最近，百花离春天最近，耕耘离收获最近，追求离梦想最近。猴年你若收到我的短信，快乐离你最近。",
"新春又来到，新年问个好，办事步步高，生活乐陶陶，好运天天交，越长越俊俏，家里出黄金，墙上长钞票。客户新年好！",
"值此春回大地、万象更新之良辰，敬祝您福、禄、寿三星高照，阖府康乐，如意吉祥！祝猴年您万事如意，心想事成！",
"祝你猴年里娶的老婆是小昭，交的朋友是令狐冲，做个男儿像乔峰，出来混得如小宝！",
"新春快乐，我的朋友！愿你年年圆满如意，月月事事顺心，日日喜悦无忧，时时高兴欢喜，刻刻充满朝气，祝福你！",
"欢欢喜喜迎新年，万事如意平安年，扬眉吐气顺心年，梦想成真发财年，事业辉煌成功年，祝客户猴年岁岁有好年！",
"天增岁月人增寿，春满乾坤福满门。祝你：官大、权大、肚子大，口袋更大;手长、舌长、裙带长，好景更长！",
"猴年恭喜你!你获得本公司头奖50万元。请带上安全防备用品到平价收银台凭密码领取奖金，密码是：把钱拿出来！",
"新春好，好事全来了！朋友微微笑，喜气围你绕！欢庆节日里，生活美满又如意！喜气！喜气！一生平安如意！",
"新的1年开始，祝好事接2连3，心情4季如春，生活5颜6色，7彩缤纷，偶尔8点小财，烦恼抛到9霄云外！请接受我10心10意的祝福。祝客户猴年春节新春快乐！",
"新年又到，祝你猴年身体健康，万事如意，想什么有什么，想多少赚多少！！新的一年要努力！事在人为！客户猴年愉快！",
"祝你猴年一而再、再而三、事事如意、五福临门、六六大顺、七彩生活、八面玲珑、久盛不衰、十全十美、百年好合、千禧之初、万贯家财，慢慢享用以上祝福，有效期一生。",
"对你的思念象袅袅的轻烟不绝如缕，对你的祝福是潺潺的小溪叮咚作响。或许岁月将往事褪色，或许空间将彼此隔离。但值得珍惜的依然是你我的良好合作关系。再次对你说声：新年快乐！",
"春节到来，短信送礼：送你一条红鲤鱼，愿你年年有余；送你一盘开心果，愿你时 时开心；送你一杯好运酒，愿你猴年顺心！新年好！",
"送你一件外套，前面是平安，后面是幸福，吉祥是领子，如意是袖子，快乐是扣子，口袋里满是温暖，穿上吧，让它相伴你的每一天！新年快乐！",
"在这节日里，愿我是一枚爆竹，绽裂的快乐为你的脸庞添一抹笑意；愿我是一盏灯笼，曳动的火光映红温暖你的心，愿我的一生美丽你的一世！",
"健康是最佳的礼物，知足是最大的财富，信心是最好的品德，关心是最真挚的问候，牵挂是最无私的思念，祝福是最美好的话语。祝你新年快乐！平安幸福！",
"春节前夕，我顺着祝福的潮水，驾着问候的小舟，满载着吉祥、平安、团圆和喜庆， 乘风破浪疾驰而来，将在新年港湾靠航，请注意收货。预祝猴年大吉！",
"在关爱中让友情更深，在牵挂中让亲情更暖，在老实中让心底更静，在简单中让生活更美，在问候中让祝福更好，在祝福中让春节更快乐！",
"新年祝福天气预报：你将会遇到金钱雨、幸运风、友情雾、爱情露、健康霞、幸福云、顺利霜、美满雷、安全雹、开心闪、它们将伴你整一年。",
"高雅的人，看背影就知道；奋进的人，听脚步就知道；和善的人，看笑脸就知道；自信的人，看眼神就知道；吉祥的人，看您就知道。祝新年快乐！",
"幸福快乐陪着您，走过每一天；温馨和谐陪着您，度过每一时；健康平安陪着您，走过每一分；我的祝福陪着您，度过每一秒。祝您：猴年快乐！",
"空调冷却不了青春的火焰，彩电演绎不了年轻的色彩，MP3播放不了岁月的音色，电影远比不上生命的出色，短信却能寄托我真诚的祝福：猴年快乐！",
"祝你猴年一而再、再而三、事事如意、五福临门、六六大顺、七彩生活、八面玲珑、久盛不衰、十全十美、百年好合、千禧之初、万贯家财…慢慢享用以上祝福，有效期一生。",
"花开花谢，此消彼长，云卷云舒，又是一年。愿时间更替带给你漂亮心情，飘送着我的祝福，萦绕在您的身边。祝你猴年新年快乐！快乐每一天！",
"雪花悄悄的，饺子是热的，你我是闲的，孩子是忙的，烦恼是假的，祝福是真的，窗花凉凉的，心里暖暖的，猴年好运气，注定发财的，春节太喜气，最美是除夕。祝：快乐除夕，猴年大吉！",
"亲们，猴年来了，不送你们别的了，只送你们三样水果：橙子、香蕉和苹果。祝你们在新的一年里，心想事“橙”;永“蕉”好运;另外，要事事“苹”安呦！",
"白雪红梅淡芬芳，农历新年呈吉祥。鞭炮声声成与长，拜年句句安与康。新年喜菜吃不胖，推向高潮是酒香。猴年祝福行好运，恭喜发财福临门。祝：客户新年快乐，猴年吉祥！",
"人生是一道解析题，每个阶段是判断题，每天的生活是选择题。完成了选择题，才有判断的权利，做好了判断题才有解析的意义。羊年即将交卷，愿我们猴年更精彩！",
"【春】来喜气洋，【节】日快乐爽，【祝】愿大吉祥，【福】禄寿禧康；【万】贯财进仓，【事】业更辉煌，【如】日中天强，【意】气风发旺。",
"抖落一年的疲惫，有收获有汗水。掌声和鲜花都虚无，充实的脚步最可贵。淡淡的牵挂总相随，变迁的过往不可追。快新年了，愿客户快乐时时有，来年生活美！",
"除夕了，来包饺，你一个，我一下；过大年，挂灯笼，红彤彤，吉祥照；猴年了，放鞭炮，啪啪啪，真热闹；初一了，起大早，这拜年，那问好：客户新年快乐！",
"过年了，送你一“新”，新年快乐；送你一“兴”，兴旺发达；送你一“心”，心想事成；送你一“欣”，欣欣向荣；送你一“薪”。薪水丰厚；送你一“星”，星光灿烂；送你一“心”，心意无限全在短信中！",
"又是一年春节到，燃放爆竹和花炮，辞去旧岁好运到，穿上新衣戴新帽，见面互道新年好，拜年莫忘送红包，吃了年糕步步高，圆圆的饺子赛元宝。客户春节吉祥！",
"美味佳肴在喜悦中欢快分享，琼浆玉液在欢笑中推杯换盏。亲朋好友欢聚一堂热闹非凡，亲情友情融为一体和谐吉祥。盛世佳节同贺共庆幸福美满，祝愿客户新年愉快！",
"拜年了，拜年了，短信拜年了。新年虽然还未到，短信祝福先送到，吉祥话要趁早，预祝你猴年好。愿你在新的一年里“卯”劲十足，钞票少不了！",
"一杯酒干了，朋友是那些年的相伴，一杯酒干了，朋友是这些年的牵绊，一杯酒干了，朋友是我们还要走的未来好多年，短信送祝愿，愿我们的友谊地久天长，猴年吉祥。",
"我愿把幸福写进饱满的诗行，我愿把日子揉进清亮的时光，当我收藏幸福的岁月时，不会留下遗憾给自己。新的一年我要把最好的留给我的朋友，留给我的亲人，请接受我这新年的贺礼，愿我们事事都如意！",
"你愿，或者不愿意，今年都将过去。你想或者不想经历，新的一年都将到来。你信或者不信，我们的生活将会越来越好。祝客户新年带着好心情过活！",
"空气中弥漫新年的气息，带来无限的喜悦；生活中充满种种乐趣，带来无限的快乐；人们的脸上洋溢着开心的笑容，带来无比的甜蜜。新春到了，一切都充满新的希望，新的憧憬。愿客户新年幸福多多，快乐多多！",
"庭前春未暖，山后雪还寒；城中霜雾重，枝头晓月残。深冬又一日，却已是新年！春节悄然至，乐起平淡间；祝客户你欢乐、猴年新春快乐！（新春短信祝福 www.nzhufu.com/chunjie）",
"新年新气象，愿你来年买个新房子，努力娶个喜娘子；没事听听新曲子，闲来看看新片子；找点致富新路子，多多挣点新票子；愿客户更新了岁月，翻新了心情！",
"春节，是一年的起点；快乐，是人生的重点；烦恼，永远降到冰点；时间，是情谊的钟点；祝福是短信的焦点。新年，愿你占领幸福的制高点，处处春光无限！",
"放了鞭炮，吃好饺子，看会晚会，睡醒一觉，初一来到，开门喜庆，出门快乐，走亲开心，串门高兴，放松身心，亲朋聚餐，幸福美美，客户猴年吉祥。"
);
$zhufu['长辈'] = array(
"新年到问个好，短信祝福报个到，财源滚滚时时进，身体壮健步步高，生活如意事事顺，好运连连天天交，愿你开心又美妙，转发八人显神效！猴年大吉！",
"新年到，新春到；有成绩，别骄傲；失败过，别倒下；齐努力，开大炮；好运气，天上掉；同分享，大家乐。天天好运道，日日福星照。猴年好！",
"新年祝福赶个早：一祝身体好；二祝困难少；三祝烦恼消；四祝不变老；五祝心情好；六祝忧愁抛；七祝幸福绕；八祝收入高；九祝平安罩；十祝乐逍遥！",
"春日融融，春水潺潺。春草如丝，春雨绵绵。春风得意，春意盎然。春华秋实，春风满面。春花秋月，春色满园。春色怡人，春意阑珊。春回大地，春光灿烂。春暖花开，春风送暖。春节快乐，春满人间！",
"提个前，赶个早，先来道声春节好；撞好运，遇好事，道个吉祥和如意。祝你新年拥有新气象，春天拥有春的希望，幸运之星照又照，生活快乐又美妙！",
"眼见家家都团圆，不知不觉又一年；新年来临祝福你，愿你来年喜连连；身体健康家和睦，事业顺风福禄全；天天开心天天乐，准保升职又加钱！猴年愉快！",
"新年将至，送你祝福早一点：旧的一年完美句点，新的一年希望起点；开心微笑多一点，烦恼心事少一点；让日子变得幸福点，快乐不止一点点。春节快乐！",
"猴年春节拜年早，欢欢喜喜过新年；春联抒写福寿禄，平平安安吉祥年；美酒溢满如意杯，红红火火好运年；真挚祝福伴温暖，团团圆圆幸福年。祝新春大吉，幸福无边！",
"新年到来拜年早，短信祝福问声好，好运最先来报到，幸福生活跟你跑，事业大发步步高，日子开心处处妙，快乐心情伴你笑，大把大把赚钞票！",
"新年将至，愿你抱着平安，拥着健康，揣着幸福，携着快乐，搂着温馨，带着甜蜜，牵着财运，拽着吉祥，迈入新年，快乐度过每一天！",
"新的一年，祝好事接二连三，心情四季如春，生活五颜六色，七彩缤纷，偶尔八点小财，烦恼抛到九霄云外！请接受我十全十美的祝福。祝猴年新春愉快！",
"眼见家家都团圆，不知不觉又一年；新年来临祝福你，愿你来年喜连连；身体健康家和睦，事业顺风福禄全；天天开心天天乐，准保健康财运好！猴年快乐！",
"我的祝福，不是最早，但是最诚；我的祝福，不求最美，不求最全，但求最灵。无论已经收到多少问候，我依然献上最诚挚的祝福，猴年大吉！",
"新年祝福不求最早，不求最好，但求最诚！我的祝福不求最美，不求最全，但求最灵！预祝：你及你的家人在新的一年里身体健康，万事如意！",
"人人尽道过年好，神州万里锦绣飘。银蛇御风破苍穹，天上人间猴年到。尽享过年三件宝，烟花鞭炮与水饺。窗花春联与年画，也算过年三件套。无论天涯与海角，年的味道中国造。新年快乐！",
"新年到了，将一声声贴心的问候，一串串真挚的祝福，一片片深厚的情意，乘着爱心的短信，穿越千山万水，飘进你的心坎。祝你猴年快乐！",
"春节来临，欢声不断；电话打搅，多有不便；短信拜年，了我心愿；祝您全家，身体康健；生活幸福，来年多赚；提早拜年，免得占线！猴年快乐！"
);
$zhufu['老师'] = array(
"愿你新的一年里：事业正当午，身体壮如虎，金钱不胜数，干活不辛苦，悠闲像老鼠，浪漫似乐谱，快乐非你莫属！祝老师春节快乐！",
"距离遥远不能见，祝福绵绵永惦念，教师佳节情灿烂，桃李芬芳永开遍，愿短信努力争先，扎进您手机里面，道声快乐连成片。新年快乐！",
"新年的钟声即将响起，深深的思念已经传递，暖暖的问候藏在心底，真心的祝愿全部送给你。预祝你春节快乐，万事如意，财运滚滚，一生平安！",
"也许，您不是最优秀的，但在我心中您是最棒的！节日快乐，老师！阳光普照，园丁心坎春意暖；雨露滋润，桃李蓓蕾红。祝您猴年快乐。",
"老师伟略英才展，率领学生大奋战。年年高考刷新翻，人皆都夸师有方。春节之际齐拜年，家长学生同声赞。老师春节阖家园，下年成绩我们闯。",
"您用黑板擦净化了我心灵，您用粉笔为我增添了智慧，您把知识洒向大地，您把幼苗辛勤培育，您是伟大的园丁，您是天底下最慈祥的妈妈，春节到了，衷心祝福您幸福永远！",
"亲爱的老师：感谢您用心血和汗水为我做的一切，您的美好身影在学生心中永远不会磨灭。春节快乐！",
"美妙的人生，因有朋友而畅快，因有成就而骄傲，因有家庭而温暖，因有爱人而幸福，因有希望而激荡，因有健康而充实。总之，祝你应有尽有，猴年愉快！",
"粉笔一支恩情长，清风两袖园丁忙，讲台三尺勤耕耘，育才四方满天涯，桃李五湖皆芬芳，飞花六月景色好，实心实意献给您，祝敬爱的老师，新春佳节快乐！",
"漫天的星辰，装上我平安夜的祈祷；跳动的烛光，摇曳着平安夜的心愿；悠长的钟声，传送着我新年的祝福，轻轻为你捎去一声问候：祝老师你开心、平安、幸福。",
"您把人生的春天奉献给了芬芳的桃李，却给自己留下了冬的干净、雪的洁白、冰的清纯…祝您猴年节日快乐！",
"愿你把金财银财大财小财，汇成万贯家财；用你的朝气英气正气勇气，换得一团和气；把我最真的祝福祝愿心愿许愿，一并送给你：祝老师春节快乐，富贵连年。",
"您有默默无私的奉献，您有春蚕丝尽的精神，您有桃李满天下的硕果，您有惊天动地的伟业。您就是人类灵魂的工程师，教师节到了，祝我最敬爱的老师身体健康，新年节日快乐！",
"新年的风，吹走你的忧郁；新年的雨，洗掉你的烦恼；新年的阳光，给你无边的温暖；新年的空气，给你无尽的喜悦；新年的祝福，给你无限的问候。",
"愿我的祝福像清茶滋润您干涸的喉咙，像蜡烛照亮您的办公室，像鲜花送给你一片清香！春节快乐！",
"感谢您提供了一个平台，让我们展示自我能力，感激您耐心栽培，让我们不断进步，怀揣一颗感恩的心，为您送去春节的祝福，愿老师您合家欢乐，万事如意。",
"不惜付出，克己复礼；不畏清贫，坚守四季；不惧风霜，宽厚弘毅；不计辛勤，芬芳四溢；桃李天下，无可匹敌。亲爱的老师，衷心的祝愿您猴年快乐。",
"一个个日子升起又降落，一届届学生走来又走过，不变的是您深沉的爱和灿烂的笑容。祝福您，亲爱的老师，春节愉快！",
"小小短信表我心，感谢老师培育恩。小小短信表我心，师恩之情暖我心。小小短信表我心，师情恩情比海深。祝老师新春节日快乐，幸福久久。"
);
$zhufu['妹纸'] = $zhufu['女生'] = array(
"春节至，夜似水，思如月，恋友情更切。短信至，问候寄，关怀无可替。祝福情，有诚意，愿你好运永不缺，幸福永不绝。祝你猴年快乐！",
"翻翻渐薄的日历，拽拽溜走的岁月；理理纷飞的思绪，点点走过的脚步；掸掸身上的烦恼，露露开心的微笑。猴年愉快！",
"春节祝福短信来到，祝你在新年里：事业如日中天，心情阳光灿烂，工资地覆天翻，春节祝福语未来风光无限，爱情浪漫依然，快乐游戏人间。",
"夜静的很深，当我们沉浸在睡梦中的时候；新年已经无声无息地到来，带去了我真诚的祝福，清早当第一缕晨光洒落在新年里，祝所有朋友新年快乐幸福！",
"祝在新的一年里：一家和和睦睦，一年开开心心；一生快快乐乐，一世平平安安；天天精神百倍，月月喜气洋洋；年年财源广进，岁岁平安祥和！春节快乐！",
"冬雪未飘渝已寒，夏日酷暑夜难眠；春光灿烂洒满园，秋高气爽却没闲；矢志不渝坚如磐，同窗共度又一年；春节快乐勿忘玩，新年好运乐团圆。",
"祝你百事可乐，万事芬达，心情雪碧，一周七喜，天天娃哈哈，年年高乐高。猴年快乐！",
"新春之际祝您：东行吉祥，南走顺利，西出平安，北走无虑，中有健康；左逢源，右发达，内积千金，外行好运！祝新年快乐！",
"发一条短信一毛，回一条也一毛，一毛又一毛，加起来好多毛，新年到了，忍着痛再拔一毛，送给你！祝你春节快乐！也就是你，换别人，我一毛不拔！",
"灵蛇迎春到，短信把福报，瑞雪纷飞寒梅俏，枝头喜鹊闹。除夕吉星照，如意祥云绕，桃红柳绿春来早，年丰人欢笑！恭喜发财新年好。",
"赶个早，提个前，先来道声春节好。撞好运，遇好事，道个吉祥和如意。祝你新年拥有新气象，春天拥有春的希望，幸运之星照又照，生活快乐又美妙！",
"缕缕牵挂，让白云替我传达；声声祝福，由短信帮我送出。虽然总是茫茫碌碌不常相聚，心里却从不曾把你忘记。在此问候你：最近还好吧！新年快乐！",
"钟声是我的问候，歌声是我的祝福，雪花是我的贺卡，美酒是我的飞吻，清风是我的拥抱，快乐是我的礼物！统统都送给你，祝你猴年快乐！",
"哥们儿，恭喜发财，大吉大利，祝你全家平安，工作顺利！今年发财了吧，别忘了请客！给你特别的祝福，愿春节带给你无边的幸福、如意，祝春节快乐，新年充满幸福和成功。",
"要过年了，我没有送去漂亮的冬衣；没有浪漫的诗句；没有贵重的礼物；没有玫瑰的欢喜。只有轻轻地祝福请你牢记：祝你在猴年里万事如意！",
"恭喜发财，大吉大利，祝你全家平安，工作顺利！今年发财了吧，别忘了请客！给你特别的祝福，愿春节带给你无边的幸福、如意，祝春节快乐，猴年充满幸福和成功。",
"距离之隔并不意味分离，疏于联系并不意味忘记，不能相见并不意味冷漠，一切只因为我们都活在忙碌交织的岁月里，但我依然记得你，祝你猴年快乐！",
"和风细雨润人间，喜气洋洋迎新年，爱情美满享百年，幸福生活乐无边，洪福高照福无限，天降祥瑞体康健，事事顺心好运伴，快乐逍遥赛神仙。猴年春节快乐！",
"猴年气象新，梦想定成真：出门有车开，回家住豪宅；种地不上税，读书全免费；看病有医保，老来有依靠；朋友不可少，祝福放首要。新年快乐！",
"快乐时我给你祝福，失意时给你安抚，友情是最大的财富，幸福蕴含在知足。新的一年，让我们共同祝福，猴年快乐！快乐永远！ 新的一年梦想实现！",
"猴年到，收到短信心情好；抬头能见喜，出门捡钱包；伸手抓祥云，幸福来拥抱；踏上健康路，登上快乐岛；猴年多福运，年年乐逍遥！妹纸猴年快乐！",
"恭祝新的一年给力增运气，围脖添才气，工作有“帝”气，围观聚人气，“穿越”通财气，一切都顺气。妹纸新春快乐！",
"对你的思念象袅袅的轻烟不绝如缕，对你的祝福是潺潺的小溪叮咚作响。或许岁月将往事褪色，或许空间将彼此隔离。但值得珍惜的依然是你给我的情谊。再次对你说声：妹纸新年快乐！",
"流星是天空的表情，愿望是他的心情，鲜花是春天的表情，美丽是她的心情，幸福是时间的表情，快乐是你的心情，猴年新春，祝妹纸你平安如意。",
"奉天承运，皇帝诏曰：2016新年特殊行动：惊天动地开心笑，谈天说地疯狂侃；呼天唤地纵情唱，欢天喜地疯狂吃；昏天暗地踏实睡，呼风唤雨财源滚！祝妹纸猴年快乐！",
"春节到了，这年头，祝福也不好送了，快乐被人打包了，煽情被人赶早了，万事如意跟身体健康也不知道被谁拐跑了，可我的祝福还是送到了。妹纸春节愉快！",
"说得好不如唱得好，唱得好不如做得好，做得好不如运气好，运气好不如我的祝福好！祝祖国繁荣昌盛，人民给力！祝你新春家财源滚滚，鸿福齐天！",
"雪花飞舞翩翩，迎来2016猴年；打开手机看看，短信送去祝愿；新年到来幸福，快乐健康永远！2016猴年快乐！",
"得得失失平常事，是是非非任由之，恩恩怨怨心不愧，冷冷暖暖我自知，坎坎坷坷人生路，曲曲折折事业梯，凡事不必太在意，愿妹纸你新春一生好运气！",
"春节祝福还没收到吧？小日子过的还安逸吧?短信不是和你玩躲猫猫，只是我要第一个让你春节快乐，千万不要感动流泪呀，我只是想来抢个大沙发。",
"365个日出，付出2013的辛苦；365个祝福，迎接2016的幸福；365个新鲜，给你最美好的祝愿，祝福贵友新的一年里好梦成真，大吉大利，事事顺心，新春快乐哦。",
"鱼在游，鸟在叫，愿你天天哈哈笑；手中书，杯中酒，祝你好运天天有；欢乐多，忧愁少，祝大家新春天天天蓝！",
"我向时间借一些记忆，记住你我之间的情谊，我向岁月借一些歌曲，记住你我之间的旋律，我向猴年借一些话语，祝妹纸你欣欣向荣好运气。",
"人生是一道风景，快乐是一种心境，春看桃，夏见柳，秋观菊，冬赏梅，愿幸福陪伴你；月圆是诗，月缺是画，日上灿烂，日落浪漫，祝新春快乐！",
"健康是最佳的礼物，知足是最大的财富，信心是最好的品德，关心是最真挚的问候，牵挂是最无私的思念，祝福是最美好的话语。祝你新年快乐！平安幸福！",
"如果你是一条鱼，我就变成一个渔夫抛下鱼饵等着你。如果你是一只鸟，我就变成枝头鸟巢。如果你是一只小兔，我就变成兔妈妈，永远的保护你！妹纸猴年快乐！",
"一场瑞雪悄悄降落，愿吉祥将你笼罩。出门不要堵车，上班不要迟到，工作不要浮躁，干活不要抱怨，领钱不要嫌少。面对新年道一声：你好！",
"距离之隔并不意味分离，疏于联系并不意味忘记，不能相见并不意味冷漠，一切只因为我们都活在忙碌交织的岁月里，但我依然记得你，祝妹纸你猴年快乐！"
);
$zhufu['朋友'] = array(
"新年到，开好头：运道旺，有彩头；快乐多，有劲头；生活好，有甜头；事业顺，有奔头；财源广，有盼头；喜事多，有乐头。新年蛇抬头，红红火火领蛇头！",
"要散场了，请放下一年的疲倦，抛弃所有烦恼，摒弃一切不幸，精神抖擞地带着幸福，牵着好运，背上健康，开心快乐地迈向，祝新年快乐！",
"祝在新的一年里：一家和和睦睦，一年开开心心；一生快快乐乐，一世平平安安；天天精神百倍，月月喜气洋洋；年年财源广进，岁岁平安祥和！春节快乐！",
"新年佳节到，向你问个好，身体倍健康，心情特别好；好运天天交，口味顿顿妙。最后祝您：鸡年好运挡不住，鸡年财源滚滚来！",
"新年临近梅花香，一条信息带六香；一香送你摇钱树，二香送你贵人扶，三香送你工作好，四香送你没烦恼，五香送你钱满箱，六香送你永健康！新年快乐！",
"昨天拜年早了点，明天拜年挤了点，后天拜年迟了点！现在拜年是正点。拜年啦！拜年拉！祝新春快乐，财源茂盛，万事如意，新年大吉。",
"“严酷”的大寒里，也有温暖无限：温馨的亲情是棉衣，穿在身上暖在心头，甜蜜的爱情是炉火，幸福火焰热度升温，快乐的友情是手套，知心情谊驱赶寒意，祝你度过一个气温不降反“升”的大寒节气！",
"新春到，拜年早：一拜全家好，二拜没烦恼，三拜不变老，四拜幸福绕，五拜步步高，六拜平安罩，七拜收入高，八拜乐逍遥。",
"一家和和睦睦，一年开开心心，一生快快乐乐，一世平平安安，天天精神百倍，月月喜气洋洋，年年财源广进。",
"新年的祝福数不胜数，新年的愿望好多好多，新年的问候依旧美好，在这个温馨的时刻，送上我最真挚的祝福，愿你新年健康加平安，幸福又快乐！",
"祝你鸡年：新年大吉大利、百無禁忌、五福臨門、富貴吉祥、橫財就手、財運亨通、步步高升、生意興隆、東成西就、恭喜發財！",
"春联红红火火，年画漂漂亮亮，财神笑笑嘻嘻，门神威威严严，爆竹噼噼啪啪，年饭喷喷香香，全家团团圆圆，过年喜喜欢欢，除夕吉吉利利，全年大吉",
"抓着快乐给生活把脉，活出洒脱的姿态；嚼着吉祥为幸福添彩，将烦忧拒之门外；揣着祝福携带着关怀，把顺心顺意满载；新的一年，愿你快乐开怀到永远！",
"新年到，新春到，有成绩，别骄傲，失败过，别死掉，齐努力，开大炮，好运气，天上掉，同分享，大家乐。天天好运道，日日福星照。",
"春节生活忙碌多，身体健康要关注：早睡晚起精神好，作息规律体安康；大鱼大肉要减少，荤素搭配益身心；油腻食物尽量少，多吃蔬果健康来。祝春节快乐！",
"我发短信主要有两个目的：一是锻炼指法，二是联络感情，我很负责任的告诉你，今天除夕，新的一年马上来到了，送句有技术含量的话：春节快乐！",
"新年到，愿你家兴业兴财源兴，人旺体旺精神旺；嘴巴整日乐歪歪，脑袋逍遥美晃晃；日子一日一日强，幸福一年一年长；青春常驻你身上，友谊永在万年长。",
"祝你在新的一年里，快乐多如鸡毛，聪敏胜过鸡脑，进步犹如鸡跑，烦恼好比鸡子扔玉苞！",
"春节至，夜似水，思如月，恋友情更切。短信至，问候寄，关怀无可替。祝福情，有诚意，愿你好运永不缺，幸福永不绝。祝你新年快乐！",
"新年祝福来报道：大财、小财、意外财，财源滚滚；亲情、爱情、朋友情，份份真情；官运、财运、桃花运，运运亨通；爱人、亲人、家里人，人人平安。",
"春风眷恋你，爱情滋润你，财运青睐你，家人关怀你，爱人理解你，朋友信任你，生活眷顾你，祝福跟随你，短信提醒你，春节发信息，一年幸福多甜蜜！",
"如意春风吉祥雨，山川秀丽燕子舞。美满生活迎春到，幸福花开芳蕊吐。平安健康就是福，一帆风顺创五湖。春天到来播希望，精耕细作不怕苦。愿你立春笑开颜，耕耘播种幸福圆！",
"你收到的是信息，我发送的是祝福；你看到的是文字，我发送的是牵挂；你打开的是消息，我发出的是吉祥，新年来临之际，愿小小短息能传达我对你不变的祝福，新年快乐！",
"新年到，祝福到；短信问好，友人可安；祝愿朋友，财源滚滚；吉祥高照，鸿运当头；幸福围绕，健康相伴；一生平安，万事顺心；笑口常开，春节快乐！",
"绿水青山迎宝猴，红梅白雪送灵蛇。猴蹄阵阵催奋进，壮志凌云书人生。艳阳照耀岁华新，骏猴欢腾四海春。声声祝福耳边绕，只愿佳节多欢笑。",
"值此新春，感谢您过去一年对我的支持，给您拜年了！祝您在鸡年，位高权重责任轻，钱多事少离家近，每天睡到自然醒，工资领到手抽筋，奖金多到车来运，别人加班你加薪。鸡年鸿运！",
"飞雪飘，红梅艳，快乐人生惹人羡；放鞭炮，贴春联，事事如意乐无边；月长久，花浪漫，爱情甜蜜情绵绵；唢呐响，锣鼓喧，生活幸福庆新年。愿你新年快乐，幸福一生！",
"你，在我记忆的画屏上增添了许多美好的怀念，似锦如织！请接受我深深的祝愿，愿所有的欢乐都陪伴着你直到永远。特别的春节给你特别的祝福。",
"放一挂喜鞭炮，摆一桌佳肴，共享节日快乐盛宴；捧一簇问候，送一份祝福，传递新年平安吉祥；忘记昨日忧伤，祈祷幸福美好，祝愿你新年开心，好运天天交！",
"鸡年来到笑开颜，一生喜乐未鸡年。要问为啥贼高兴，笑答我本属相鸡。祥瑞鸡年本分年，六十寿辰摆盛宴。邀请亲朋齐来贺，弘扬鸡年正能量。愿你高寿鸡福享，一生都把鸡财发！",
"爱情亲情友情情情如意，好运幸运福运财运运运享通，爱你的人你爱的人想你的人你想的人人人幸福！新年快乐！",
"别动！你已经被祝福包围，马上放下烦恼，向快乐投降，你所有的忧愁，将被全部没收，并判你幸福一百年，流放到开心岛。",
"春节送祝福！祝大家新年快乐。可不可以发几条关于过年的短信息吗?我要发给他们，可以吗?祝你们新春快乐。",
"在新年即将来临时，送你旺旺大礼包：一送你摇钱树，二送你贵人扶，三送你工作好，四送你没烦恼，五送你钱满箱，六送你永安康！",
"捻一片深冬的雪，斟一杯春天的酒，加一滴快乐的水，添一勺幸运的花，摘一轮皎洁的月，洒一缕灿烂的光，酿一句真诚的话：新年快乐！",
"人在天涯，心在咫尺。关注关怀，从未停息。冬日寒冷，问候加温。阳光灿烂，祝福温馨。遥祝朋友：身体康健，工作顺利，家庭和睦，幸福洋溢。",
"健康是最佳的礼物，知足是最大的财富，信心是最好的品德，关心是最真的问候，牵挂是最深的思念，祝福是最美的话语。祝新年快乐！平安幸福！",
"日出东海落西山，愁也一天，喜也一天；遇事不钻牛角尖，人也舒坦，心也舒坦；常与朋友聊聊天，古也谈谈，今也谈谈，不是神仙胜似神仙；愿你快乐一整年。",
"曾几何时，回家已成一种奢望。灿烂的烟花，燃点的只是寂寞。喧闹的爆竹，炸响的只是冷清。变味的年饭，品出的只有苦涩。春节不回家，在外多保重！",
"电影：让子弹飞；生活：让物价飞；交通：让油价飞；楼盘：让房价飞；春节：让祝福飞；祝福：让短信飞；期望：让幸福飞；计划：让工资飞。",
"金蛇穿云去，紫骝踏雪来。新春无限好，奋蹄起龙图。把酒庆丰年，幸福乐无边。常存千里志，立志绘蓝图。锦绣千里路，更上一层楼。",
"把美好的祝福，输在这条短信里，信不长情意重，我的好友愿你新年快乐！好久不见，十分想念。在这温馨的日子里，常常忆起共处的岁月。祝新年快乐，心想事成！给你拜个年！",
"新年里充满快乐的味道，人人脸上洋溢着幸福的欢笑。鞭炮驱走了沉闷的空气，礼花带来了喜庆的气氛。街巷里到处是热闹的场景，轻盈的舞步在歌曲声中更加令人痴迷。新年到了，祝你万事如意，快乐无比！",
"以家庭为圆心，以幸福为半径，画出千千万万个阖家圆满！2017年新年祝福语",
"在新的一年开启新的希望，新的空白承载新的梦想。朋友拂去岁月之尘，让欢笑和泪水，爱与哀愁在心中凝成一颗厚重晶莹的琥珀停留。祝最好的朋友新年快乐！",
"盼了又盼终于等到春节的来临，希望每一个人都能在新的一年有辉煌的成就，出门遇贵人啊！2017年新年祝福语",
"锣鼓敲，欢声笑，福星照，舞狮闹，祝福速速来报到。新的一年，希望你财富贼多，事业贼火，身体贼棒，家庭贼旺，一切贼顺，鸡年贼牛！",
"上班就像子在爬满鸡子的大树上，向上看全是屁股，向下看全是笑脸，左右看全是耳目。鸡年到了，祝你使劲向上多爬两根枝丫，看到更多的笑脸和更少的屁股！",
"祝您新年快乐！事业顺心顺意，工作顺顺利利，爱情甜甜蜜蜜！身体有用不完的力气，滚滚财源广进！身体倍儿棒，吃饭倍儿香，牙好胃口就好，事事开心，事事顺利！",
"茶用情感去品，越品越浓，酒用坦诚去喝，越喝越香，情用真诚去感，越感越深，友用理解去交，越交越好。除夕只此一夜，朋友却是永远！",
"拿起美妙的彩笔，勾画鸡年的美景，绽放灿烂的笑脸，抒发喜悦的心情。画上一群鸡，点彩一堆钱。让你痴迷让你醉，让你鸡年幸福睡。鸡年到了，送你一群鸡，幸福万年长！",
"新年到了，我请求天神帮你置换以下事项：将你的伤心换成开心，将忧心换成舒心，将烦心换成顺心，这可是我真心求来的，你要耐心的收下哟，新年快乐。",
"马儿扬蹄奔鸡年，喜气洋洋绕身边。生活快乐永向前，阳光大道任你选。幸福美满笑开颜，无忧无虑乐无限。祝你鸡年理想现，鸡年大吉身体健！",
"名气是大家给的，地位是兄弟拼的，春节到了，我代表江湖上的朋友祝你节日快乐！你未来的日子里所收到的短信都是我安排他们发的。我为人低调别跟我客气",
"流星是天空的表情，愿望是他的心情，鲜花是春天的表情，美丽是她的心情，幸福是时间的表情，快乐是你的心情，新年，祝你平安如意。",
"新年散发出喜气，烟花绽放了美丽，春联写上了平安，美酒斟满了富贵，颂歌吟唱着快乐，身心享受着甜蜜，问候融入了友谊，祝福充满了真挚。新的一年，祝朋友万事如意，心情舒畅，财源滚滚，合家幸福！",
"鸡年春节祝福语 你是女中豪杰当之无愧，你是我们衣食父母官，你的伟业锦旗挂满墙，你的丰功业绩硕果累。春节祝领导生体健康，扎西德勒，更上一层楼，想着老百姓。",
"十面埋伏是雄心，破釜沉舟是决心，完璧归赵是忠心，程门立雪是虚心，卧薪尝胆是苦心，愚公移山是信心，绳锯木断是专心，精卫填海是恒心，大展宏图需八心，朋友您可别花心。祝新春快乐！",
"对你的思念像袅袅的轻烟不绝如缕，对你的祝福是潺潺的小溪叮咚作响。或许岁月将往事褪色，或许空间将彼此隔离。但值得珍惜的依然是你给我的情谊。再次对你说声：2017新年快乐！",
"新的一年来到，新的祝愿送到：祝你人缘实现广覆盖健康突破保基本好运争取多层次财运走向可持续，幸福保持稳步涨。新年快乐！",
"新年到百花香，一条信息带六香。一香送你摇钱树，二香送你贵人扶，三香送你工作好，四香送你没烦恼，五香送你钱满箱，六香送你永安康！祝春节快乐！",
"趁着悠扬的钟声还未敲响，辞久的爆竹还未燃放，人们的祝福还在路上，在此祝您及家人：新春好，万事顺，合家欢，年运旺。",
"春天的鲜花簇拥你，祝你新年事业顺；夏日的彩虹照亮你，祝你前途更光明；秋天的果实滋润你，祝你新年最健康；冬日的雪花陪伴你，祝你鸡年新年步步高！",
"时间不经意写了结局，还没来得及说再见，时间不经意写了开始，还没猜到要和谁相见，时间不经意写了祝愿，祝你新年一切平平安安。",
"托空气做邮差，把我热腾腾的问候装订成包裹，加印上真心的邮戳，用37度恒温快递赶春节之前送到你手上，提前祝你春节快乐，好运滚滚！",
"祝福你在新的一年：合乐融融，财运滚滚，一帆风顺，二龙腾飞，三鸡开泰，四季平安，五福临门，六六大顺，七星高照，八方来财，九九同心，十全十美。",
"车如梭，人如潮，一年春运如期到。千山横，万水绕，回家之行路迢迢。报平安，佳讯捎，阖家团圆乐陶陶。祝归乡之路一路顺风！",
"这是春的开头，虽然你在那头，我在这头，还是把你记在心头。在这寒冷关头，愿我的祝福温暖你的手头，直达你的心头，鸡年春节假期快乐！",
"新年至，愿你浪漫如诗，温柔似雨，柔情如雾，宽容似海，温馨如月，热情似日。友情传信，爱情传情。保你幸福快乐，鸡年大吉。",
"金鸡走，银鸡到，朋友向你问声好；手机聊，飞信报，传递感情最重要；看烟花，放礼炮，携带家人游港澳；观山水，赏冬景，新年伊始乐淘淘。祝新年快乐！",
"在关爱中让友情更深在牵挂中让亲情更暖在诚实中让心底更静在简单中让生活更美在问候中让祝福更好在祝福中让春节更快乐！祝你新春快乐！",
"装一车幸福，让平安开道，抛弃一切烦恼，让快乐与您环绕，存储所有温暖，将寒冷赶跑，释放一生真情，让幸福永远对您微笑！大年三十吃饺子！春节快乐。",
"在外打工把家念，春节很快到眼前，春运回家买票难，人潮拥挤注意安全，彻夜排队要保暖，一票在手心里甜，祝你平平安安把家返，开开心心过大年！",

"竹报三多，红梅报喜，瑞雪迎春，阳春召我，淑气宜人。",
"天天开心，笑口常开，幸福安康，好运连连，财源滚滚。",
"太平有象，幸福无疆，龙缠启岁，凤纪书元，与山同静。",
"星罗棋布，步步高升，升官发财，财源广进，近水楼台。",
"鼎新革旧，豫立亨通，春为岁首，梅占花魁，梅开五福。",
"升官发财，财源广进，近水楼台，四海增辉，鹏程万里。",
"风舒柳眼，雪润梅腮，北窗梅启，东院柳舒，晴舒柳眼。",
"海屋添寿，松林岁月，庆衍箕畴，篷岛春风，寿城宏开，庆衍萱畴。",
"平安无恙，吉庆有余，百花献瑞，百花齐放。",
"随地有春，唐虞盛世，天地长春，物化天宝，人杰地灵。",
"日月长明，祝无量寿，鹤寿添寿，奉觞上寿。",
"太平有象，大造无私，四时吉庆，八节安康，天开景运。",
"鸿案齐眉，极婺联辉，鹤算同添，寿域同登，椿萱并茂，家中全福，天上双星。",
"暖吐花唇，野云归岫，春舍澄空，白梅吐艳，黄菊傲霜。",
"天福华民，百家有福，六合同春，万事如意，四时平安。",
"如写阳春，造家庭福，抱世界观，蟠桃祝寿，梅柳迎春。",
"岁岁平安，大吉大利，万事如意，恭喜发财。",

);

$zhufu['元宵'] = $zhufu['元宵节'] = $zhufu['元宵节快乐'] = array(

'🎉´*•…¸(*•.🎧¸.•*)🎏¸…•*`
✨🎄.¸.元宵.¸.🎐🏆
🎈🎶🌟.¸.快乐.¸.✨🎵
.¸•*’ 🌀📻(¸.•*´`*🎉•.¸)`*•.¸',


'✨   💢.  💢. 💢✨💢
  .\*  .\*   */.✨ \*  */.  */.
💢.\*   /. */ .  .  \*/  .* /.
  ✨\*   */✨ * *\ \ ∙ /.💢
 🎈  ||元||          ||宵|| ✨
🎉   ||快||          ||乐|| 🎉',


'✨   ✨  ✨  ✨
✨🍸     🎈👑  ✨
       \👸/    \👩
        👙       👗>
 🎈 _/  \_   _/  \_🎈
💃👫👯👫👯👫💃
元宵夜，出来玩吧！',


'☁     🌀 ㊗   🌀
🌀   ☀  /      ☁
〓〓〓 /  〓〓〓〓
◇　   /　　　         ◆
◆.    (　　                 ◇
◇　  \　          ◆
◆　    \                ◇
◇          \                   ◆
◆           ✳
◇              ＼👩/
◆                  👗
◇🏢🏦🏨     | |  🏬
〓〓 元宵快乐 〓〓
希望你可以突破框框
找到你头上的一片天',



'💱
             💹
☁    🚀 ☁☁
     ✨            🎈
☁ ✨
 ✨     ✨         ✨ ✨
 ✨      💰＼ 😁／💰✨
 ✨                👔
🏦✨💨💰💰💰💰💰
㊗你元宵也发大财，
赚个不停！',



'            🎩
             👂👀👂
                  👃    旺！
                   ⭕  🍸
💰💰  💪 👔／ 💰💰
💰💰💰  /    \💰💰💰
💰💰💰👟   👟💰💰
㊗ 你元宵快乐！
🈵🈵🈵🈵🈵🈵🈵🈵',



'🐠    👱
            <👕>🐬
       🐟   JL 💦
            🐬💦
         💦🐬🐬💦
    💦💦🐟🐟💦💦
  💦💦🐠🐠🐠💦💦
💦💦🐳🐳🐳🐳💦💦
💦💦㊗你节日好运💦
 💦💦如鱼得水💦💦
   💦💦💦💦💦💦
         💦💦💦💦',


'.           ✌
.              \👸
.               👙>
.                /  \
.🎉✨    👠👠   🎉
.        ┏💎💎💎┓ ✨
.       🏃🏃🏃🏃🏃
.✨┏━💍💍💍━┓🎉
. 🏃🏃🏃🏃🏃🏃🏃🏃
.㊗越来越漂亮身材好
.      越来越多人追！',


'✌
  \👱
   👔>
 _/  \_
╬👣═╬🌟
╬═👣╬💰
╬👣═╬💰
╬═👣╬💰
╬👣═╬💰
╬═👣╬💰
╬👣═╬💰
祝你元宵快乐',



'😊元宵到了，送你一温馨🎁🈶:
🈵🈵的㊗福！
🉐🉐的🙏LUCKY!
收到这信息📱⚡📱,日后一定💹
🈵🈵🈵,8⃣8⃣8⃣💰💰💰！
♠♥♣♦🀄🐎WIN,WIN,WIN!
👗👕👔👙👠👢🍜🍛🍣🍚🍞🍲🍧🍸🍶🍵🍻永🈚憂😄',


'❤💎💎💰💎💎❤
💎💎💰💰💰💎💎
💎💰💎💰💎💰💎
💰💎💎💰💎💎💰
💰💎💎💰💎💎💎
💎💰💎💰💎💎💎
💎💎💰💰💰💎💎
💎💎💎💰💎💰💎
💎💎💎💰💎💎💰
💰💎💎💰💎💎💰
💎💰💎💰💎💰💎
💎💎💰💰💰💎💎
❤💎💎💰💎💎❤',


'✨🌹🌹    🌹🌹✨
🌹🎁🌹🌹🌹🎁🌹
🌹🎂💄🌹💄🎂🌹
  🌹💋我爱你💋🌹
✨🌹💎💍💎🌹✨
     ✨🌹🌹🌹  ✨
        ✨  🌹 ✨


✨🌹🌹    🌹🌹✨
🌹🎁🌹🌹🌹🎁🌹
🌹🎂💄🌹💄🎂🌹
  🌹💋嫁给我💋🌹
✨🌹💎💍💎🌹✨
     ✨🌹🌹🌹  ✨
        ✨  🌹 ✨',

'🍀🍀🍀    🍀🍀🍀
🍀╔╗╔╗╔╗╦╗✨🍀
🍀║╦║║║║║║👍🍀
🍀╚╝╚╝╚╝╩╝。 🍀
    🍀🍀祝你🍀🍀
  🍀🍀好运气🍀🍀',



'🌟。❤。😉。🍀
。🎁 。🎉。🌟
✨。＼｜／。🌺
   恭祝元宵快乐
💜。／｜＼。💎
。☀。 🌹。🌙。
 🌟。 😍。 🎶',


'┳╮🍺🎶🍻🎶🍺
┣┻╮*╭╮╭╮┣╮
┃✨┃┣┛┣┛┃
┻━╯"╰╯╰╯┻
🎶🍻🎶🍺🎶🍻🎶
   今晚不醉不归',


'🌷🌷㊗元宵快乐🌷🌷
💰✅✅✅💰💰💰💰
💰💰✅💰💰💰💰💰
💰💰✅💰💰✅✅✅
✅✅✅✅✅✅💰✅
💰✅✅✅💰✅💰✅
✅💰✅💰✅✅✅✅
💰💰✅💰💰💰💰💰
💰💰✅💰💰💰💰💰',


'☁☁☁☁☁☁☀
🐔           元宵快乐!
 ( c  ) 🐣 🐣 🐣~🎶
🌺🌺🌺🌺🌺🌺🌺',

'       (  ●ー●  ) 
　／　　　   ＼
  /　　　  ○  　\
/　 /  　　    ヽ   \
|　/　元宵节   \　|
 \Ԏ　送你大白  Ԏ/
　卜−　　   ―イ
　  \　　/\　   /
　　 ︶　   ︶',
);

$zhufu['大白'] = array(
'       (  ●ー●  ) 
　／　　　   ＼
  /　　　  ○  　\
/　 /  　　    ヽ   \
|　/　元宵节   \　|
 \Ԏ　送你大白  Ԏ/
　卜−　　   ―イ
　  \　　/\　   /
　　 ︶　   ︶

点击这里下载<a href="http://a.wpweixin.com/go/492/">大白壁纸</a>。'
);

    if($keyword == '' || empty($zhufu[$keyword])){
        return '请输入下面的关键字来获取新春祝福语：'."\n\n祝福".implode("\n祝福", array_keys($zhufu));
    }else{
        $the_zhufu = $zhufu[$keyword];  
        shuffle($the_zhufu);
        return $the_zhufu[0]; 
    }
}