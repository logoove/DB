<?php

if (!function_exists('dump')) {
    /**
     * @param $arr 变量
     * @return 打印变量
     */
    function dump($arr)
    {
        echo '<pre>' . print_r($arr, TRUE) . '</pre>';
    }

}

if (!function_exists('curl')) {
    /**
     * @param $url 地址
     * @param  $data 要提交数组
     * @return Curl获取
     */
    function curl($url, $data = '')
    {
        $ch = curl_init();
        if (class_exists('\CURLFile')) {
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
        } else {
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
            }
        }
        preg_match('/https:\/\//', $url) ? $ssl = TRUE : $ssl = FALSE;
        if ($ssl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $d = curl_exec($ch);
        curl_close($ch);
        return $d;
    }
}

if (!function_exists('getoauth')) {
    /**
     * @param  $type true是所有信息false只有openid
     * @param  $appid 公众号id
     * @param  $apps 公众号密钥
     * @param  $expired 过期时间
     * @return 微信通过oauth2授权获取信息
     */
    function getoauth($type = FALSE, $appid = '', $apps = '', $expired = '600')
    {
        $type = ($type == TRUE) ? 'snsapi_userinfo' : 'snsapi_base';
        $scheme = $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
        $baseUrl = urlencode($scheme . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);

        if (!isset($_GET['code'])) {
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$baseUrl&response_type=code&scope=$type#wechat_redirect";
            header("location:$url");
            exit();
        } else {
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$apps&code=" . $_GET['code'] . "&grant_type=authorization_code";

            $output = (array)json_decode(curl($url));
            if ($type == 'snsapi_base') {
                return $output['openid'];
            } else {
                $url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $output['access_token'] . '&openid=' . $output['openid'] . '&lang=zh_CN';
                $output = (array)json_decode(curl($url));
                return $output;
            }

        }
    }
}

if (!function_exists('setcache')) {
    /**
     * @param $name 缓存名称
     * @param $value 缓存字符串或数组
     * @param  $path 路径
     * @param  $expire 过期时间秒默认7000
     * @return 设置文件缓存
     */
    function setcache($name, $value, $path = "./", $expire = 7000)
    {
        $filename = $path . "cache_{$name}.php";
        $json = json_encode(array($name => $value, "expire" => time() + $expire));
        $result = file_put_contents($filename, $json);
        if ($result) {
            return true;
        }
        return false;
    }
}

if (!function_exists('getcache')) {
    /**
     * @param $name 缓存id
     * @param  $path
     * @return 获取缓存
     */
    function getcache($name, $path = "./")
    {
        $filename = $path . "cache_{$name}.php";
        if (!is_file($filename)) {
            return false;
        }
        $content = file_get_contents($filename);

        $arr = json_decode($content, true);
        if ($arr['expire'] <= time()) {
            return false;
        }
        return $arr["$name"];
    }
}

if (!function_exists('json')) {
    /**
     * @param  $code 代码
     * @param  $message 提示信息
     * @param  $list 数组或字符
     * @param  $total 数据条数
     * @return 格式化数组
     */
    function json($code = 200, $message = '请求成功', $list = [], $total = -1)
    {
        $json = array(
            'code' => $code,
            'msg' => $message
        );
        if (!empty($list)) {
            $json['data'] = $list;
        }
        if ($total >= 0) {
            $json['total'] = $total;
        }
        header('Content-type: application/json');
        exit(json_encode($json, JSON_UNESCAPED_UNICODE));
    }
}
if (!function_exists('tablearr')) {
    /**
     * @param $table
     * @return 表格转换成数组
     */
    function tablearr($table)
    {
        $table = preg_replace("'<table[^>]*?>'si", "", $table);
        $table = preg_replace("'<tr[^>]*?>'si", "", $table);
        $table = preg_replace("'<td[^>]*?>'si", "", $table);
        $table = str_replace("</tr>", "{tr}", $table);
        $table = str_replace("</td>", "{td}", $table);
        //去掉 HTML 标记
        $table = preg_replace("'<[/!]*?[^<>]*?>'si", "", $table);
        //去掉空白字符
        $table = preg_replace("'([rn])[s]+'", "", $table);
        $table = preg_replace('/&nbsp;/', "", $table);
        $table = str_replace(" ", "", $table);
        $table = str_replace(" ", "", $table);
        $table = str_replace("\r", "", $table);
        $table = str_replace("\t", "", $table);
        $table = str_replace("\n", "", $table);
        $table = explode('{tr}', $table);
        array_pop($table);
        foreach ($table as $key => $tr) {
            $td = explode('{td}', $tr);
            array_pop($td);
            $td_array[] = $td;
        }
        return $td_array;
    }
}

if (!function_exists('findstr')) {
    /**
     * @param $string 原字符串
     * @param $find 子字符串
     * @return 查找是否包含子字符串
     */
    function findstr($string, $find)
    {
        return !(strpos($string, $find) === FALSE);
    }
}

if (!function_exists('emoji')) {
    /**
     * @param $str
     * @param  $is en是转换de是还原
     * @return emoji转换成utf8方便存储支持还原
     */
    function emoji($str, $is = 'en')
    {
        if ('en' == $is) {
            if (!is_string($str)) return $str;
            if (!$str || $str == 'undefined') return '';

            $text = json_encode($str);
            $text = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i", function ($str) {
                return addslashes($str[0]);
            }, $text);
            return json_decode($text);
        } else {
            $text = json_encode($str);
            $text = preg_replace_callback('/\\\\\\\\/i', function ($str) {
                return '\\';
            }, $text);
            return json_decode($text);
        }
    }
}

if (!function_exists('emoji_encode')) {
    /**
     * @param $str
     * @return emoji转换成实体直接在网页显示同名js函数
     */
    function emoji_encode($str)
    {
        preg_match_all('/./u', $str, $matches);
        $unicodeStr = "";
        foreach ($matches[0] as $m) {
            $unicodeStr .= (strlen($m) >= 4) ? "&#" . base_convert(bin2hex(iconv('UTF-8', "UCS-4", $m)), 16, 10) . ';' : $m;
        }
        return $unicodeStr;
    }
}

if (!function_exists('timeline')) {
    /**
     * @param $time
     * @return 时间友好显示
     */
    function timeline($time)
    {
        if (time() <= $time) {
            return date("Y-m-d H:i:s", $time);
        } else {
            $t = time() - $time;
            $f = array(
                '31536000' => '年',
                '2592000' => '个月',
                '604800' => '星期',
                '86400' => '天',
                '3600' => '小时',
                '60' => '分钟',
                '1' => '秒'
            );
            foreach ($f as $k => $v) {
                if (0 != $c = floor($t / (int)$k)) {
                    return $c . $v . '前';
                }
            }
        }
    }
}

if (!function_exists('fileext')) {
    /**
     * @param $file
     * @return 获取文件扩展名
     */
    function fileext($file)
    {
        return strtolower(pathinfo($file, 4));
    }
}

if (!function_exists('putcsv')) {
    /**
     * * $arr = array(
     * array('用户名','密码','邮箱'),
     * array(
     * array('A用户','123456','xiaohai1@zhongsou.com'),
     * array('B用户','213456','xiaohai2@zhongsou.com'),
     * array('C用户','123456','xiaohai3@zhongsou.com')
     * ));
     * putcsv("导出文件",$arr);
     * @param $filename 文件名不带扩展
     * @param $arr 数组可以是导出模板
     * @return 导出csv数据
     */
    function putcsv($filename, $arr)
    {
        if (empty($arr)) {
            return false;
        }
        $export_str = implode(',', $arr[0]) . "\n";

        if (!empty($arr[1])) {
            foreach ($arr[1] as $k => $v) {

                $export_str .= implode(',', $v) . "\n";

            }
        }
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=" . $filename . date('Y-m-d-H-i-s') . ".csv");
        ob_start();
        ob_end_clean();
        //echo "\xEF\xBB\xBF" . $export_str;//解决WPS和excel不乱码
        echo mb_convert_encoding($export_str, 'GBK', 'utf-8');
    }
}

if (!function_exists('getcsv')) {
    /**
     * @param $path
     * @return 导入csv编码ANSI
     */
    function getcsv($path)
    {
        $handle = fopen($path, 'r');
        $dataArray = array();
        $row = 0;
        while ($data = fgetcsv($handle)) {
            $num = count($data);

            for ($i = 0; $i < $num; $i++) {
                $dataArray[$row][$i] = mb_convert_encoding($data[$i], "utf-8", "GBK,ANSI");
            }
            $row++;

        }

        return $dataArray;
    }
}
if (!function_exists('getini')) {
    /**
     * @param $file
     * @return 读取ini
     */
    function getini($file)
    {
        if (file_exists($file)) {
            $data = parse_ini_file($file, true);
            if ($data) {
                return $data;
            }
        } else {
            return false;
        }
    }
}
if (!function_exists('putini')) {
    /**
     * @param $arr 数组
     * @param $file 文件名
     * @return 写入ini,只支持两层
     */
    function putini($arr, $file)
    {
        $arr1 = [];
        $arr2 = [];
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $arr2[$k] = $v;
            } else {
                $arr1[$k] = $v;
            }
        }
        $arr = array_merge($arr1, $arr2);
        $s = "";
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $s .= "[$k]\n";
                foreach ($v as $k1 => $v1) {
                    $v1 = ($v1 === false) ? "false" : $v1;
                    $v1 = ($v1 === true) ? "true" : $v1;
                    $s .= "$k1=$v1\n";
                }
            } else {
                $v = ($v === false) ? "false" : $v;
                $v = ($v === true) ? "true" : $v;
                $s .= "$k=$v\n";
            }
        }
        @file_put_contents($file, $s);
        return true;
    }
}
if (!function_exists('isweixin')) {
    /**
     * @return 是否微信浏览器
     */
    function isweixin()
    {
        $agent = $_SERVER ['HTTP_USER_AGENT'];
        if (!strpos($agent, "icroMessenger")) {
            return false;
        }
        return true;
    }
}

if (!function_exists('hidetel')) {
    /**
     * @param $phone
     * @return 隐藏手机中间4位
     */
    function hidetel($phone)
    {
        $IsWhat = preg_match('/(0[0-9]{2,3}[-]?[2-9][0-9]{6,7}[-]?[0-9]?)/i', $phone);
        if ($IsWhat == 1) {
            return preg_replace('/(0[0-9]{2,3}[-]?[2-9])[0-9]{3,4}([0-9]{3}[-]?[0-9]?)/i', '$1****$2', $phone);
        } else {
            return preg_replace('/(1[34578]{1}[0-9])[0-9]{4}([0-9]{4})/i', '$1****$2', $phone);
        }
    }
}

if (!function_exists('randstr')) {
    /**
     * @param  $len 长度
     * @param  $type 类型0大小写1数字2大写3小写4中文默认随机
     * @param  $addChars 要加入字符
     * @return 随机字符串
     */
    function randstr($len = 6, $type = '', $addChars = '')
    {
        $str = '';
        switch ($type) {
            case 0:
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
                break;
            case 1:
                $chars = str_repeat('0123456789', 3);
                break;
            case 2:
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
                break;
            case 3:
                $chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
                break;
            case 4:
                $chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借" . $addChars;
                break;
            default :
                $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
                break;
        }
        if ($len > 10) {
            $chars = $type == 1 ? str_repeat($chars, $len) : str_repeat($chars, 5);
        }
        if ($type != 4) {
            $chars = str_shuffle($chars);
            $str = substr($chars, 0, $len);
        } else {
            for ($i = 0; $i < $len; $i++) {
                $str .= cutstr($chars, 1, floor(mt_rand(0, mb_strlen($chars, 'utf-8') - 1)), 0);
            }
        }
        return $str;
    }
}
if (!function_exists('cutstr')) {
    /**
     * @param $str 字符
     * @param $length 长度
     * @param int $start 开始位置默认0
     * @param bool $suffix 是否显示...
     * @param string $charset 编码
     * @return 截取字符串
     */
    function cutstr($str, $length, $start = 0, $suffix = true, $charset = "utf-8")
    {
        if (function_exists("mb_substr"))
            $slice = mb_substr($str, $start, $length, $charset);
        elseif (function_exists('iconv_substr')) {
            $slice = iconv_substr($str, $start, $length, $charset);
            if (false === $slice) {
                $slice = '';
            }
        } else {
            $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("", array_slice($match[0], $start, $length));
        }
        return $suffix ? $slice . '...' : $slice;
    }
}

if (!function_exists('filecount')) {
    /**
     * @param $size 字节大小
     * @param  $dec 默认2位小数
     * @return 格式化文件大小
     */
    function filecount($size, $dec = 2)
    {
        $a = array("B", "KB", "MB", "GB", "TB", "PB");
        $pos = 0;
        while ($size >= 1024) {
            $size /= 1024;
            $pos++;
        }
        return round($size, $dec) . " " . $a[$pos];
    }
}
if (!function_exists('getprize')) {
    /**
     * @return 演示显示中将概率数据
     */
    function getprize()
    {//获取中奖
        $prize_arr = array(
            array('id' => 1, 'prize' => '平板电脑', 'v' => 10),
            array('id' => 2, 'prize' => '数码相机', 'v' => 10),
            array('id' => 3, 'prize' => '音箱设备', 'v' => 10),
            array('id' => 4, 'prize' => '4G优盘', 'v' => 10),
            array('id' => 5, 'prize' => '10Q币', 'v' => 10),
            array('id' => 6, 'prize' => '下次没准就能中哦', 'v' => 950),
        );
        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val['v'];
        }
        $ridk = getrand($arr); //根据概率获取奖项id

        $res['yes'] = $prize_arr[$ridk - 1]['prize']; //中奖项
        unset($prize_arr[$ridk - 1]); //将中奖项从数组中剔除，剩下未中奖项
        shuffle($prize_arr); //打乱数组顺序
        for ($i = 0; $i < count($prize_arr); $i++) {
            $pr[] = $prize_arr[$i]['prize'];
        }
        $res['no'] = $pr;
        return $res;
    }
}
if (!function_exists('getrand')) {
    /**
     * @param $proArr
     * @return 中奖概率数据
     */
    function getrand($proArr)
    {
        $result = '';
        $proSum = array_sum($proArr);
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }
}

if (!function_exists('trimstr')) {
    /**
     * @param $str
     * @return 去除字符中空格换行
     */
    function trimstr($str)
    {
        $str = trim($str);
        $str = preg_replace("/\t/", "", $str);
        $str = preg_replace("/\r\n/", "", $str);
        $str = preg_replace("/\r/", "", $str);
        $str = preg_replace("/\n/", "", $str);
        $str = preg_replace("/ /", "", $str);
        return trim($str); //返回字符串
    }
}

if (!function_exists('trimarray')) {
    /**
     * @param $Input 数组
     * @return 去除数组中两端空格支持excel
     */
    function trimarray($Input)
    {
        if (!is_array($Input))
            return preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "", $Input);
        return array_map('trimarray', $Input);
    }
}
if (!function_exists('getip')) {
    /**
     * @return 获取本机ip
     */
    function getip()
    {
        static $ip = '';
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_CDN_SRC_IP'])) {
            $ip = $_SERVER['HTTP_CDN_SRC_IP'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] as $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        }
        if (preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $ip)) {
            return $ip;
        } else {
            return '127.0.0.1';
        }
    }
}
if (!function_exists('getavatar')) {
    /**
     * @param  $email 邮箱
     * @param  $s 大小
     * @return 生成头像
     */
    function getavatar($email = '', $s = 40)
    {
        $hash = md5($email);
        $avatar = "https://www.gravatar.com/avatar/$hash?s=$s&d=mm&r=g";
        return $avatar;
    }
}
if (!function_exists('getmemory')) {
    /**
     * @return 获取内存
     */
    function getmemory()
    {
        return round((memory_get_usage() / 1024 / 1024), 3) . "M";
    }
}
/**
 * 加密解密函数
 * ENCODE 加密
 * @param $string
 * @param string $operation
 * @param string $key
 * @param int $expiry
 * @return string
 */
if (!function_exists('authcode')) {
    /**
     * @param $string 字符串
     * @param $operation de解密en加密
     * @param  $key 密钥
     * @param  $expiry 过期时间
     * @return 加密解密字符串
     */
    function authcode($string, $operation = 'de', $key = '', $expiry = 0)
    {
        $ckey_length = 4;
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'de' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);
        $string = $operation == 'de' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'de') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }
}
if (!function_exists('randcolor')) {
    /**
     * @return 生成随机颜色
     */
    function randcolor()
    {
        $char = 'abcdef0123456789';
        $str = '';
        for ($i = 0; $i < 6; $i++) {
            $str .= substr($char, mt_rand(0, 15), 1);
        }
        return '#' . $str;
    }
}
/**
 *
 * @param 数组 $arr
 * @param 层级 $level
 * @param undefined $ptagname
 *
 * 数组转换xml
 */
if (!function_exists('arr2xml')) {
    function arr2xml($arr, $level = 1)
    {
        $s = $level == 1 ? "<xml>\n" : '';
        foreach ($arr as $k => $v) {
            if (!is_array($v)) {
                $s .= "<{$k}>" . (!is_numeric($v) ? '<![CDATA[' : '') . $v . (!is_numeric($v) ? ']]>' : '') . "</{$k}>\n";
            } else {
                $s .= "<{$k}>\n" . arr2xml($v, $level + 1) . "</{$k}>\n";
            }
        }
        $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
        return $level == 1 ? $s . "</xml>" : $s;
    }
}

if (!function_exists('xml2arr')) {
    /**
     * @param $xml
     * @return xml转换成数组
     */
    function xml2arr($xml)
    {
        if (empty($xml)) {
            return array();
        }
        $result = array();
        $xmlobj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xmlobj instanceof \SimpleXMLElement) {
            $result = json_decode(json_encode($xmlobj), true);
            if (is_array($result)) {
                return $result;
            } else {
                return array();
            }
        } else {
            return $result;
        }
    }
}
if (!function_exists('msg')) {
    /**
     * @param  $type 类型help|gopage|goto|info|success|warn
     * @param  $info 提示语
     * @param  $url 跳转地址
     * @return  页面提示
     */
    function msg($type = "help", $info = "", $url = "")
    {
        if ("close" == $type) {//自动关闭
            $strs = empty($info) ? "" : "alert('$info');";
            echo "<script>" . $strs . "document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
WeixinJSBridge.call('closeWindow');});</script>";
            exit;
        } elseif ("gopage" == $type) {//显示跳转中...
            $urls = empty($url) ? "" : 'location.href="' . $url . '";';
            $strs = empty($info) ? "正在跳转中..." : $info;
            $html = <<<EOF
            <meta charset='utf-8'>
<script type="text/javascript">document.write("<meta name='viewport' content='width=device-width,initial-scale=1,user-scalable=0'><div style='font-size:16px;margin:30px auto;text-align:center;'>$strs </div>"); $urls;</script>
EOF;
            exit($html);
        } elseif ("goto" == $type) {//普通弹出,跳转
            $strs = empty($info) ? "" : "alert('$info');";
            $urls = empty($url) ? "" : 'location.href="' . $url . '";';
            exit('<script type="text/javascript">' . $strs . $urls . '</script>');
        } elseif ("info" == $type || "success" == $type || "warn" == $type) {
            $html = <<<EOF
<meta charset='utf-8'>
<script>document.write("<title>提示</title><meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'><link rel='stylesheet'  href='https://res.wx.qq.com/open/libs/weui/0.4.3/weui.min.css'><div class='weui_msg'><div class='weui_icon_area'><i class='weui_icon_$type weui_icon_msg'></i></div><div class='weui_text_area'><h4 class='weui_msg_title'>$info</h4></div></div>");document.addEventListener("WeixinJSBridgeReady", function onBridgeReady() {WeixinJSBridge.call("hideOptionMenu");});</script>
EOF;
            exit($html);
        }
    }
}
if (!function_exists('str2arr')) {
    /**
     * @param $var 数组或字符串
     * @param  $str 分隔符号
     * @return 字符串数组相互自动转换
     */
    function str2arr($var, $str = ',')
    {
        if (is_array($var)) {
            return implode($str, $var);
        } else {
            return explode($str, $var);
        }
    }
}
if (!function_exists('getdistance')) {
    /**
     * @param $lat1 经度1
     * @param $lng1 维度1
     * @param $lat2
     * @param $lng2
     * @param  $len_type 1m2km
     * @param  $decimal 保留小数2位
     * @return 计算两个经纬度距离
     */
    function getdistance($lat1, $lng1, $lat2, $lng2, $len_type = 1, $decimal = 2)
    {
        $pi = 3.1415926000000001;
        $er = 6378.1369999999997;
        $radLat1 = ($lat1 * $pi) / 180;
        $radLat2 = ($lat2 * $pi) / 180;
        $a = $radLat1 - $radLat2;
        $b = (($lng1 * $pi) / 180) - (($lng2 * $pi) / 180);
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + (cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))));
        $s = $s * $er;
        $s = round($s * 1000);
        if (1 < $len_type) {
            $s /= 1000;
        }
        return round($s, $decimal);
    }
}
if (!function_exists('getaddress')) {
    /**
     * @param  $ak 百度或腾讯地图密钥
     * @param  $type 类型默认baidu|qq
     * @return 根据IP返回地址信息
     */
    function getaddress($ak = '', $type = "baidu")
    {

        if ($type == 'baidu') {
            $ak = (empty($ak)) ? "8SlSbHObMgN8HeOwGUQXU5XM" : $ak;
            $url = "https://api.map.baidu.com/location/ip?ak=$ak&coor=bd09ll&ip=" . getip();
            $rs = json_decode(curl($url), 1);
            if ($rs['status'] == 0) {
                return $rs['content'];
            } else {
                return $rs['message'];
            }
        } else {
            $ak = (empty($ak)) ? "ACEBZ-FDXWP-WFRDV-VGS5Q-S2Q5K-HQBNA" : $ak;
            $url = "https://apis.map.qq.com/ws/location/v1/ip?ip=" . getip() . "&key=$ak";
            $rs = json_decode(curl($url), 1);
            if ($rs['status'] == 0) {
                return $rs['result'];
            } else {
                return $rs['message'];
            }
        }


    }
}
if (!function_exists('htmlencode')) {
    /**
     * @param $var
     * @return html转换实体
     */
    function htmlencode($var)
    {
        if (is_array($var)) {
            foreach ($var as $key => $value) {
                $var[htmlspecialchars($key)] = html2($value);
            }
        } else {
            $var = str_replace('&amp;', '&', htmlspecialchars($var, ENT_QUOTES));
        }
        return $var;
    }
}
if (!function_exists('htmldecode')) {
    /**
     * @param $var
     * @return html实体还原
     */
    function htmldecode($var)
    {
        return htmlspecialchars_decode($var);
    }
}
if (!function_exists('id')) {
    /**
     * @return 生成随机字符串id
     */
    function id()
    {
        return md5(uniqid());
    }
}

if (!function_exists('md')) {
    /**
     * @param $path 路径
     * @return 创建多级目录
     */
    function md($path)
    {
        if (!is_dir($path)) {
            md(dirname($path));
            mkdir($path);
        }

        return is_dir($path);
    }
}
if (!function_exists('putfile')) {
    /**
     * @param $filename 文件名含路径
     * @param $data 数据
     * @return 生成文件
     */
    function putfile($filename, $data)
    {
        md(dirname($filename));
        file_put_contents($filename, $data);
        return is_file($filename);
    }
}
if (!function_exists('getfile')) {
    /**
     * @param $filename 文件名含路径
     * @return 读取文件
     */
    function getfile($filename)
    {
        if (!is_file($filename)) {
            return false;
        }
        return file_get_contents($filename);
    }
}
if (!function_exists('base642img')) {
    /**
     * @param $path 路径
     * @param $data base64图片数据
     * @param $root 要上传根目录包含/
     * @return base64生成图片
     */
    function base642img($path, $data,$root)
    {
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $data, $result)) {
            $type = "." . $result[2];
            $id = sha1($data);
            $path1 = $path . "/" . $id . $type;
        }
        $img = base64_decode(str_replace($result[1], '', $data));
        putfile($root.$path1, $img);
        return $path1;
    }
}
if (!function_exists('img2base64')) {
    /**
     * @param $filename 路径包含图片名
     * @return 图片转换base64
     */
    function img2base64($filename)
    {
        $data = file_get_contents($filename);
        $ext = strtolower(pathinfo($filename, 4));
        $base64 = chunk_split(base64_encode($data));
        $base64 = "data:image/$ext;base64,$base64";
        return $base64;
    }
}
if (!function_exists('filecopy')) {
    /**
     * @param $src 要复制的文件夹
     * @param $des 生成的文件夹
     * @return 复制文件夹
     */
    function filecopy($src, $des)
    {
        $dir = opendir($src);
        @mkdir($des);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    filecopy($src . '/' . $file, $des . '/' . $file);
                } elseif (!in_array(substr($file, strrpos($file, '.') + 1), ["./", "/"])) {
                    copy($src . '/' . $file, $des . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}
if (!function_exists('deldir')) {
    /**
     * @param $path 路径文件夹
     * @param  $clean 默认false删除自身,true保留
     * @return 删除多级目录包含文件
     */
    function deldir($path, $clean = false)
    {
        if (!is_dir($path)) {
            return false;
        }
        $files = glob($path . '/*');
        if ($files) {
            foreach ($files as $file) {
                is_dir($file) ? deldir($file) : @unlink($file);
            }
        }

        return $clean ? true : @rmdir($path);
    }
}
if (!function_exists('listdir')) {
    /**
     * @param  $path
     * @param  $type 0兼有1目录2文件
     * @return 列出目录
     */
    function listdir($path = __FILE__, $type = 0)
    {
        $dir = str_replace('\\', '/', $path);
        $dh = scandir($dir);
        foreach ($dh as $f) {
            if ($f !== '.' && $f != '..') {
                if ($type == 0) {
                    $file[] = $f;
                } elseif ($type == 1) {
                    if (is_dir($dir . "/" . $f)) {
                        $file[] = $f;
                    }
                } elseif ($type == 2) {
                    if (is_file($dir . "/" . $f)) {
                        $file[] = $f;
                    }
                }

            }
        }
        return $file;
    }
}
if (!function_exists('winxinqr')) {
    /**
     * @param $name 公众号名称
     * @return 生成微信公众号二维码
     */
    function winxinqr($name = "youbairuanjian")
    {
        return "https://open.weixin.qq.com/qr/code?username=$name";
    }
}
if (!function_exists('qqshare')) {
    /**
     * @param  $qq
     * @param  $type 1标准2小3大
     * @return qq分享的链接和图片
     */
    function qqshare($qq = "280594236", $type = 1)
    {
        $a = [1 => 51, 52, 53];
        $arr = ["href" => "http://wpa.qq.com/msgrd?v=3&uin=$qq&site=qq&menu=yes",
            "img" => "http://wpa.qq.com/pa?p=2:280594236:" . $a[$type]];
        return $arr;
    }
}
if (!function_exists('gettimezone')) {
    /**
     * @return 返回时区数组
     */
    function arr_timezone()
    {
        return array('Pacific/Kwajalein' => '(GMT -12:00) Eniwetok, Kwajalein', 'Pacific/Samoa' => '(GMT -11:00) Midway Island, Samoa', 'US/Hawaii' => '(GMT -10:00) Hawaii', 'US/Alaska' => '(GMT -09:00) Alaska', 'America/Tijuana' => '(GMT -08:00) Pacific Time (US & Canada), Tijuana', 'US/Arizona' => '(GMT -07:00) Mountain Time (US & Canada), Arizona', 'America/Mexico_City' => '(GMT -06:00) Central Time (US & Canada), Mexico City', 'America/Bogota' => '(GMT -05:00) Eastern Time (US & Canada), Bogota, Lima, Quito', 'America/Caracas' => '(GMT -04:00) Atlantic Time (Canada), Caracas, La Paz', 'Canada/Newfoundland' => '(GMT -03:30) Newfoundland', 'America/Buenos_Aires' => '(GMT -03:00) Brassila, Buenos Aires, Georgetown, Falkland Is', 'Atlantic/St_Helena' => '(GMT -02:00) Mid-Atlantic, Ascension Is., St. Helena', 'Atlantic/Azores' => '(GMT -01:00) Azores, Cape Verde Islands', 'Europe/Dublin' => '(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia', 'Europe/Amsterdam' => '(GMT +01:00) Amsterdam, Berlin, Brussels, Madrid, Paris, Rome', 'Africa/Cairo' => '(GMT +02:00) Cairo, Helsinki, Kaliningrad, South Africa', 'Asia/Baghdad' => '(GMT +03:00) Baghdad, Riyadh, Moscow, Nairobi', 'Asia/Tehran' => '(GMT +03:30) Tehran', 'Asia/Baku' => '(GMT +04:00) Abu Dhabi, Baku, Muscat, Tbilisi', 'Asia/Kabul' => '(GMT +04:30) Kabul', 'Asia/Karachi' => '(GMT +05:00) Ekaterinburg, Islamabad, Karachi, Tashkent', 'Asia/Calcutta' => '(GMT +05:30) Bombay, Calcutta, Madras, New Delhi', 'Asia/Katmandu' => '(GMT +05:45) Katmandu', 'Asia/Almaty' => '(GMT +06:00) Almaty, Colombo, Dhaka, Novosibirsk', 'Asia/Rangoon' => '(GMT +06:30) Rangoon', 'Asia/Bangkok' => '(GMT +07:00) Bangkok, Hanoi, Jakarta', 'Asia/Shanghai' => '(GMT +08:00) Beijing, Hong Kong, Perth, Singapore, Taipei', 'Asia/Tokyo' => '(GMT +09:00) Osaka, Sapporo, Seoul, Tokyo, Yakutsk', 'Australia/Adelaide' => '(GMT +09:30) Adelaide, Darwin', 'Australia/Canberra' => '(GMT +10:00) Canberra, Guam, Melbourne, Sydney, Vladivostok', 'Asia/Magadan' => '(GMT +11:00) Magadan, New Caledonia, Solomon Islands', 'Pacific/Auckland' => '(GMT +12:00) Auckland, Wellington, Fiji, Marshall Island');
    }
}
/**
 * @return 天气数组
 */
function arr_weather()
{
    return array("00" => "晴", "01" => "多云", "02" => "阴", "03" => "阵雨", "04" => "雷阵雨", "05" => "雷阵雨伴有冰雹", "06" => "雨夹雪", "07" => "小雨", "08" => "中雨", "09" => "大雨", "10" => "暴雨", "11" => "大暴雨", "12" => "特大暴雨", "13" => "阵雪", "14" => "小雪", "15" => "中雪", "16" => "大雪", "17" => "暴雪", "18" => "雾", "19" => "冻雨", "20" => "沙尘暴", "21" => "小到中雨", "22" => "中到大雨", "23" => "大到暴雨", "24" => "暴雨到大暴雨", "25" => "大暴雨到特大暴雨", "26" => "小到中雪", "27" => "中到大雪", "28" => "大到暴雪", "29" => "浮尘", "30" => "扬沙", "31" => "强沙尘暴", "53" => "霾", "99" => "无", "32" => "浓雾", "49" => "强浓雾", "54" => "中度霾", "55" => "重度霾", "56" => "严重霾", "57" => "大雾", "58" => "特强浓雾", "301" => "雨", "302" => "雪");
}

if (!function_exists('get_weather')) {
    function get_weather($city = "昆山", $ak = "")
    {
        $ak = empty($ak) ? "M4eExM3AxIcxOdnFGciErtK3" : $ak;
        $d = curl("http://api.map.baidu.com/telematics/v3/weather?location=$city&output=json&ak=$ak");
        $d = json_decode($d, 1);
        if ($d['error'] == 0) {
            $d = array(
                "date" => $d['date'],
                "city" => $d['results'][0]['currentCity'],
                "pm25" => $d['results'][0]['pm25'],   //pm
                "index" => $d['results'][0]['index'], //指数
                "weather_data" => $d['results'][0]['weather_data'],  //当天,3天天气
            );
        } else {
            $d = array();
        }

        return $d;
    }
}
/**
 * @param $ext
 * @return 根据扩展名返回文件类型
 */
function getfiletype($ext)
{
    static $mime_types = array('apk' => 'application/vnd.android.package-archive', '3gp' => 'video/3gpp', 'ai' => 'application/postscript', 'aif' => 'audio/x-aiff', 'aifc' => 'audio/x-aiff', 'aiff' => 'audio/x-aiff', 'asc' => 'text/plain', 'atom' => 'application/atom+xml', 'au' => 'audio/basic', 'avi' => 'video/x-msvideo', 'bcpio' => 'application/x-bcpio', 'bin' => 'application/octet-stream', 'bmp' => 'image/bmp', 'cdf' => 'application/x-netcdf', 'cgm' => 'image/cgm', 'class' => 'application/octet-stream', 'cpio' => 'application/x-cpio', 'cpt' => 'application/mac-compactpro', 'csh' => 'application/x-csh', 'css' => 'text/css', 'dcr' => 'application/x-director', 'dif' => 'video/x-dv', 'dir' => 'application/x-director', 'djv' => 'image/vnd.djvu', 'djvu' => 'image/vnd.djvu', 'dll' => 'application/octet-stream', 'dmg' => 'application/octet-stream', 'dms' => 'application/octet-stream', 'doc' => 'application/msword', 'dtd' => 'application/xml-dtd', 'dv' => 'video/x-dv', 'dvi' => 'application/x-dvi', 'dxr' => 'application/x-director', 'eps' => 'application/postscript', 'etx' => 'text/x-setext', 'exe' => 'application/octet-stream', 'ez' => 'application/andrew-inset', 'flv' => 'video/x-flv', 'gif' => 'image/gif', 'gram' => 'application/srgs', 'grxml' => 'application/srgs+xml', 'gtar' => 'application/x-gtar', 'gz' => 'application/x-gzip', 'hdf' => 'application/x-hdf', 'hqx' => 'application/mac-binhex40', 'htm' => 'text/html', 'html' => 'text/html', 'ice' => 'x-conference/x-cooltalk', 'ico' => 'image/x-icon', 'ics' => 'text/calendar', 'ief' => 'image/ief', 'ifb' => 'text/calendar', 'iges' => 'model/iges', 'igs' => 'model/iges', 'jnlp' => 'application/x-java-jnlp-file', 'jp2' => 'image/jp2', 'jpe' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'jpg' => 'image/jpeg', 'js' => 'application/x-javascript', 'kar' => 'audio/midi', 'latex' => 'application/x-latex', 'lha' => 'application/octet-stream', 'lzh' => 'application/octet-stream', 'm3u' => 'audio/x-mpegurl', 'm4a' => 'audio/mp4a-latm', 'm4p' => 'audio/mp4a-latm', 'm4u' => 'video/vnd.mpegurl', 'm4v' => 'video/x-m4v', 'mac' => 'image/x-macpaint', 'man' => 'application/x-troff-man', 'mathml' => 'application/mathml+xml', 'me' => 'application/x-troff-me', 'mesh' => 'model/mesh', 'mid' => 'audio/midi', 'midi' => 'audio/midi', 'mif' => 'application/vnd.mif', 'mov' => 'video/quicktime', 'movie' => 'video/x-sgi-movie', 'mp2' => 'audio/mpeg', 'mp3' => 'audio/mpeg', 'mp4' => 'video/mp4', 'mpe' => 'video/mpeg', 'mpeg' => 'video/mpeg', 'mpg' => 'video/mpeg', 'mpga' => 'audio/mpeg', 'ms' => 'application/x-troff-ms', 'msh' => 'model/mesh', 'mxu' => 'video/vnd.mpegurl', 'nc' => 'application/x-netcdf', 'oda' => 'application/oda', 'ogg' => 'application/ogg', 'ogv' => 'video/ogv', 'pbm' => 'image/x-portable-bitmap', 'pct' => 'image/pict', 'pdb' => 'chemical/x-pdb', 'pdf' => 'application/pdf', 'pgm' => 'image/x-portable-graymap', 'pgn' => 'application/x-chess-pgn', 'pic' => 'image/pict', 'pict' => 'image/pict', 'png' => 'image/png', 'pnm' => 'image/x-portable-anymap', 'pnt' => 'image/x-macpaint', 'pntg' => 'image/x-macpaint', 'ppm' => 'image/x-portable-pixmap', 'ppt' => 'application/vnd.ms-powerpoint', 'ps' => 'application/postscript', 'qt' => 'video/quicktime', 'qti' => 'image/x-quicktime', 'qtif' => 'image/x-quicktime', 'ra' => 'audio/x-pn-realaudio', 'ram' => 'audio/x-pn-realaudio', 'ras' => 'image/x-cmu-raster', 'rdf' => 'application/rdf+xml', 'rgb' => 'image/x-rgb', 'rm' => 'application/vnd.rn-realmedia', 'roff' => 'application/x-troff', 'rtf' => 'text/rtf', 'rtx' => 'text/richtext', 'sgm' => 'text/sgml', 'sgml' => 'text/sgml', 'sh' => 'application/x-sh', 'shar' => 'application/x-shar', 'silo' => 'model/mesh', 'sit' => 'application/x-stuffit', 'skd' => 'application/x-koan', 'skm' => 'application/x-koan', 'skp' => 'application/x-koan', 'skt' => 'application/x-koan', 'smi' => 'application/smil', 'smil' => 'application/smil', 'snd' => 'audio/basic', 'so' => 'application/octet-stream', 'spl' => 'application/x-futuresplash', 'src' => 'application/x-wais-source', 'sv4cpio' => 'application/x-sv4cpio', 'sv4crc' => 'application/x-sv4crc', 'svg' => 'image/svg+xml', 'swf' => 'application/x-shockwave-flash', 't' => 'application/x-troff', 'tar' => 'application/x-tar', 'tcl' => 'application/x-tcl', 'tex' => 'application/x-tex', 'texi' => 'application/x-texinfo', 'texinfo' => 'application/x-texinfo', 'tif' => 'image/tiff', 'tiff' => 'image/tiff', 'tr' => 'application/x-troff', 'tsv' => 'text/tab-separated-values', 'txt' => 'text/plain', 'ustar' => 'application/x-ustar', 'vcd' => 'application/x-cdlink', 'vrml' => 'model/vrml', 'vxml' => 'application/voicexml+xml', 'wav' => 'audio/x-wav', 'wbmp' => 'image/vnd.wap.wbmp', 'wbxml' => 'application/vnd.wap.wbxml', 'webm' => 'video/webm', 'wml' => 'text/vnd.wap.wml', 'wmlc' => 'application/vnd.wap.wmlc', 'wmls' => 'text/vnd.wap.wmlscript', 'wmlsc' => 'application/vnd.wap.wmlscriptc', 'wmv' => 'video/x-ms-wmv', 'wrl' => 'model/vrml', 'xbm' => 'image/x-xbitmap', 'xht' => 'application/xhtml+xml', 'xhtml' => 'application/xhtml+xml', 'xls' => 'application/vnd.ms-excel', 'xml' => 'application/xml', 'xpm' => 'image/x-xpixmap', 'xsl' => 'application/xml', 'xslt' => 'application/xslt+xml', 'xul' => 'application/vnd.mozilla.xul+xml', 'xwd' => 'image/x-xwindowdump', 'xyz' => 'chemical/x-xyz', 'zip' => 'application/zip');
    return isset($mime_types[$ext]) ? $mime_types[$ext] : 'application/octet-stream';
}

/**
 * @param $month
 * @param $day
 * @return 根据月日返回星座
 */
function get_xingzuo($month, $day)
{
    if ($month < 1 || $month > 12 || $day < 1 || $day > 31) return false;
    $constellations = array(
        array("20" => "宝瓶座"),
        array("19" => "双鱼座"),
        array("21" => "白羊座"),
        array("20" => "金牛座"),
        array("21" => "双子座"),
        array("22" => "巨蟹座"),
        array("23" => "狮子座"),
        array("23" => "处女座"),
        array("23" => "天秤座"),
        array("24" => "天蝎座"),
        array("22" => "射手座"),
        array("22" => "摩羯座")
    );
    list($constellation_start, $constellation_name) = each2($constellations[(int)$month - 1]);
    if ($day < $constellation_start) {
        list($constellation_start, $constellation_name) = each2($constellations[($month - 2 < 0) ? $month = 11 : $month -= 2]);
    }
    return $constellation_name;
}

/**
 * @param $array
 * @return 用来替换each函数
 */
function each2(&$array)
{
    $res = array();
    $key = key($array);
    if ($key !== null) {
        next($array);
        $res[1] = $res['value'] = $array[$key];
        $res[0] = $res['key'] = $key;
    } else {
        $res = false;
    }
    return $res;
}

if (!function_exists('uuid')) {
    /**
     * @return 生成UUID
     */
    function uuid()
    {
        $seed = mt_rand(0, 2147483647) . '#' . mt_rand(0, 2147483647);
        $val = md5($seed, true);
        $byte = array_values(unpack('C16', $val));
        $tLo = ($byte[0] << 24) | ($byte[1] << 16) | ($byte[2] << 8) | $byte[3];
        $tMi = ($byte[4] << 8) | $byte[5];
        $tHi = ($byte[6] << 8) | $byte[7];
        $csLo = $byte[9];
        $csHi = $byte[8] & 0x3f | (1 << 7);
        if (pack('L', 0x6162797A) == pack('N', 0x6162797A)) {
            $tLo = (($tLo & 0x000000ff) << 24) | (($tLo & 0x0000ff00) << 8) | (($tLo & 0x00ff0000) >> 8) | (($tLo & 0xff000000) >> 24);
            $tMi = (($tMi & 0x00ff) << 8) | (($tMi & 0xff00) >> 8);
            $tHi = (($tHi & 0x00ff) << 8) | (($tHi & 0xff00) >> 8);
        }
        $tHi &= 0x0fff;
        $tHi |= (3 << 12);
        $uuid = sprintf('%08x-%04x-%04x-%02x%02x-%02x%02x%02x%02x%02x%02x', $tLo, $tMi, $tHi, $csHi, $csLo, $byte[10], $byte[11], $byte[12], $byte[13], $byte[14], $byte[15]);
        return $uuid;
    }
}

class Tree
{

    private $parentid = 'parentid';
    private $id = 'id';
    private $name = 'name';
    protected static $instance = array();

    /**
     * init (add by sasou)
     *
     * @return self
     */
    public static function init()
    {
        $class_name = get_called_class();

        if (isset(self::$instance[$class_name]) && is_object(self::$instance[$class_name])) {
            return self::$instance[$class_name];
        }
        return self::$instance[$class_name] = new static();
    }

    /**
     * 无限级分类树-初始化配置
     * @param array $config array('parentid'=>'', 'id' => '', 'name' =>'name')
     * @return string|array
     */
    public function config($config = array())
    {
        if (!is_array($config))
            return false;
        $this->parentid = (isset($config['parentid'])) ? $config['parentid'] : $this->parentid;
        $this->id = (isset($config['id'])) ? $config['id'] : $this->id;
        $this->name = (isset($config['name'])) ? $config['name'] : $this->name;
        return true;
    }

    /**
     * 无限级分类树-获取树
     * @param array $tree 树的数组
     * @param int $mid 初始化树时候，代表ID下的所有子集
     * @param int $selectid 选中的ID值
     * @param string $code 代码
     * @param string $prefix 前缀
     * @param string $selected 选中
     * @return string|array
     */
    public function getTree($tree, $mid = 0, $selectid = 5, $code = "<option value='\$id' \$selecteds>\$prefix\$name</option>", $prefix = '|-', $selected = 'selected')
    {
        if (!is_array($tree))
            return '';
        $temp = array();
        $string = '';
        $temp_code = '';
        foreach ($tree as $k => $v) {
            if ($v[$this->parentid] == $mid) {
                $id = $v[$this->id];
                $name = $v[$this->name];
                $selecteds = ($id == $selectid) ? $selected : '';
                eval("\$temp_code = \"$code\";"); //转化
                $string .= $temp_code;
                $string .= $this->getTree($tree, $v[$this->id], $selectid, $code, '&nbsp;&nbsp;' . $prefix);
            }
        }
        return $string;
    }

    /**
     * 无限级分类树-获取树
     * @param array $tree 树的数组
     * @param int $mid 初始化树时候，代表ID下的所有子集
     * @param int $selectid 选中的ID值
     * @param string $code 代码
     * @param string $prefix 前缀
     * @param string $selected 选中
     * @return string|array
     */
    public function getTrees($tree, &$data, $mid = 0, $deep = 0, $prefix = '|-')
    {
        if (!is_array($tree))
            return '';
        $deep++;
        foreach ($tree as $k => $v) {
            if ($v[$this->parentid] == $mid) {
                $id = $v[$this->id];
                $name = $v[$this->name];
                $v["deep"] = $deep - 1;
                $v["prefix"] = $prefix;
                $data[] = $v;
                $this->getTrees($tree, $data, $v[$this->id], $deep, str_repeat("&nbsp;", $deep * 4) . $prefix);
            }
        }
    }

    /**
     * 无限级分类树-获取子类
     * @param array $tree 树的数组
     * @param int $id 父类ID
     * @return string|array
     */
    public function getChild($tree, $id)
    {
        if (!is_array($tree))
            return array();
        $temp = array();
        foreach ($tree as $k => $v) {
            if ($v[$this->parentid] == $id) {
                $temp[] = $v;
            }
        }
        return $temp;
    }

    /**
     * 无限级分类树-获取父类
     * @param array $tree 树的数组
     * @param int $id 子类ID
     * @return string|array
     */
    public function getParent($tree, $id)
    {
        if (!is_array($tree))
            return array();
        $temp = array();
        foreach ($tree as $k => $v) {
            $temp[$v[$this->id]] = $v;
        }
        $parentid = $temp[$id][$this->parentid];
        return $temp[$parentid];
    }

}
/**
 * PC版本常用函数
 */
/**
 * @param $tcount 总页数
 * @param $pindex 当前页面
 * @param $psize 每页显示数据
 * @return 移动端分页
 */
function pager($tcount, $pindex, $psize = 15, $url = '', $context = array('before' => 5, 'after' => 4, 'ajaxcallback' => '')) {
    global $_W;
    $pdata = array(
        'tcount' => 0,
        'tpage' => 0,
        'cindex' => 0,
        'findex' => 0,
        'pindex' => 0,
        'nindex' => 0,
        'lindex' => 0,
        'options' => ''
    );
    if($context['ajaxcallback']) {
        $context['isajax'] = true;
    }

    $pdata['tcount'] = $tcount;
    $pdata['tpage'] = ceil($tcount / $psize);
    if($pdata['tpage'] <= 1) {
        return '';
    }
    $cindex = $pindex;
    $cindex = min($cindex, $pdata['tpage']);
    $cindex = max($cindex, 1);
    $pdata['cindex'] = $cindex;
    $pdata['findex'] = 1;
    $pdata['pindex'] = $cindex > 1 ? $cindex - 1 : 1;
    $pdata['nindex'] = $cindex < $pdata['tpage'] ? $cindex + 1 : $pdata['tpage'];
    $pdata['lindex'] = $pdata['tpage'];

    if($context['isajax']) {
        if(!$url) {
            $url = $_W['script_name'] . '?' . http_build_query($_GET);
        }
        $pdata['faa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['findex'] . '\', ' . $context['ajaxcallback'] . ')"';
        $pdata['paa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['pindex'] . '\', ' . $context['ajaxcallback'] . ')"';
        $pdata['naa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['nindex'] . '\', ' . $context['ajaxcallback'] . ')"';
        $pdata['laa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['lindex'] . '\', ' . $context['ajaxcallback'] . ')"';
    } else {
        if($url) {
            $pdata['faa'] = 'href="?' . str_replace('*', $pdata['findex'], $url) . '"';
            $pdata['paa'] = 'href="?' . str_replace('*', $pdata['pindex'], $url) . '"';
            $pdata['naa'] = 'href="?' . str_replace('*', $pdata['nindex'], $url) . '"';
            $pdata['laa'] = 'href="?' . str_replace('*', $pdata['lindex'], $url) . '"';
        } else {
            $_GET['page'] = $pdata['findex'];
            $pdata['faa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['pindex'];
            $pdata['paa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['nindex'];
            $pdata['naa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['lindex'];
            $pdata['laa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
        }
    }

    $html = '	<div class="page-hd bg-gray" style="height:32px;">
	<div class="pager"  id="pager"><div class="pager-left">';

    $html .= "<div class=\"pager-first\"><a {$pdata['faa']} class=\"pager-nav\">首页</a></div>";
    $html .= "<div class=\"pager-pre\"><a {$pdata['paa']} class=\"pager-nav\">上一页</a></div>";
    $html .='</div><div class="pager-cen">
					' .$pindex.'/'.$pdata['tpage'].'
				</div><div class="pager-right">';

    $html .= "<div class=\"pager-next\"><a {$pdata['naa']} class=\"pager-nav\">下一页</a></div>";
    $html .= "<div class=\"pager-end\"><a {$pdata['laa']} class=\"pager-nav\">尾页</a></div>";

    $html .= '</div></div></div>';
    return $html;
}

/**
 * @param $table 表名称
 * @param $field 要生成字段
 * @param  $prefix 前缀
 * @return 生成唯一订单号
 */
function ordersn($table, $field, $prefix=''){
    $billno = date('YmdHis') . randstr(6,1);
    while (1) {
        $count = pdo_fetchcolumn('select count(*) from ' . tablename($table) . ' where ' . $field . '=:billno limit 1', array(':billno' => $billno));
        if ($count <= 0) {
            break;
        }
        $billno = date('YmdHis') .randstr(6,1);
    }
    return $prefix . $billno;
}
/**
 * @param  $openid
 * @return 是否关注1是0否
 */
function isfollow($openid = ''){
    global $_W;
    $openid = (empty($openid))?$_W['openid']:$openid;
    if (!empty($openid))
    {
        $rs = pdo_fetch('select follow from ' . tablename('mc_mapping_fans') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':openid' => $openid, ':uniacid' => $_W['uniacid']));
        $followed = ($rs['follow'] == 1)?1:0;
        return $followed;
    }else{
        return 0;
    }
}
/**
 * @param $str 字符串
 * @return 生成日志
 */
function logging($str){
    load()->func('logging');
    logging_run($str);
}

/**
 * @param $url
 * @return 生成二维码返回URL
 */
function qr($url)
{
    global $_W;
    $path = IA_ROOT . '/attachment/images/' . $_W['uniacid'] . '/qrcode/';
    if (!(is_dir($path)))
    {
        load()->func('file');
        mkdirs($path);
    }
    $file = md5(base64_encode($url)) . '.jpg';
    $qrcode_file = $path . $file;
    if (!(is_file($qrcode_file)))
    {
        require_once IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
        QRcode::png($url, $qrcode_file, QR_ECLEVEL_L, 4);
    }
    return $_W['siteroot'] . 'attachment/images/qrcode/' . $_W['uniacid'] . '/' . $file;
}
/**
 * @param $openid
 * @return openid获取用户信息
 */
function getuserinfo($openid){
    global $_W;
    load()->classs('weixin.account');
    $acc = WeiXinAccount::create($_W['acid']);
    return $acc->fansQueryInfo($openid);
}

/**
 * @param $mid 媒体id
 * @return 根据媒体id返回图片地址不存在为空
 */
function downimg($mid){
    global $_W;
    load()->classs('weixin.account');
    $acc = WeiXinAccount::create($_W['acid']);
    return $acc->downloadMedia($mid);
}
/**
 * Array
(
[type] => image
[media_id] => ctcINuj0UQgV2XbUQ3EJb_6-6adAYzLUlA6Zxwvh0LYFMyAMaOHhZI650yNYZhTh
[created_at] => 1601990187
[item] => []
)
 * @param $filename
 * @return 上传图片到微信服务器
 */
function upimg($filename){
    global $_W;
    load()->classs('weixin.account');
    $acc = WeiXinAccount::create($_W['acid']);
    return $acc->uploadMedia($filename);
}
/**
 * @param $url
 * @return 长网址转换短网址
 */
function shorturl($url){
    global $_W;
    load()->classs('weixin.account');
    $acc = WeiXinAccount::create($_W['acid']);
    return $acc->long2short($url)['short_url'];
}
/**
 * @param $openid
 * @param $str 内容
 * @return 发送客服消息
 */
function sendtxt($openid,$str){
    $message = [
        'msgtype'=>'text',
        'text'=>['content'=>urlencode($str)],
        'touser'=>$openid
    ];
    $acc = WeAccount::create();
    $status = $acc->sendCustomNotice($message);
    if (is_error($status)) {
        message('发送失败，原因为' . $status['message']);
    }
}
/**
 * @param $openid
 * @param $mid 媒体id
 * @return 发送图片客服消息
 */
function sendimg($openid,$mid){
    $message = [
        'touser' => $openid,
        'msgtype' => 'image',
        'image' =>['media_id'=> $mid]
    ];
    $acc = WeAccount::create();
    $status = $acc->sendCustomNotice($message);
    if (is_error($status)) {
        message('发送失败，原因为' . $status['message']);
    }
}
/**
 * @param $openid
 * @param $tplid 模板id
 * @param $tplstr 模板内容
 * @param $content 发送内容数组
 * @param $url 跳转地址
 * @reurn 发送模板消息1成功
 */
function sendtpl($openid,$tplid,$arr,$url){
    $acc = WeAccount::create();
    $status = $acc->sendTplNotice($openid,$tplid, $arr, $url);
    return $status;
}
/**
 * 积分操作
 * @param $uid uid或openid
 * @param $type credit1积分 2余额
 * @param $val 积分支持正负
 * @param  $str 备注
 * @return 积分加减操作
 */
function credit($uid, $type='credit1', $n,$str="积分操作备注"){
    global $_W;
    load()->model('mc');
    if (is_string($uid)) {
        $uid = mc_openid2uid($uid);
    }
    $log = [0,$str];
    mc_credit_update($uid, $type, $n, $log);
    return true;
}
/**
 * @param $uid uid/openid
 * @param  $type 类型数组 credit1积分2余额
 * @return 查询积分返回积分数组
 */
function getcredit($uid,$type=['credit1']){
    global $_W;
    load()->model('mc');
    if (is_string($uid)) {
        $uid = mc_openid2uid($uid);
    }
    return mc_credit_fetch($uid,$type);
}
/**
 * @param $arr
 * @return 拉黑用户传入openid数组
 */
function lahei($arr=[]){
    $token = WeAccount::token();
    $url="https://api.weixin.qq.com/cgi-bin/tags/members/batchblacklist?access_token=$token";
    $arr =  ["openid_list"=>$arr];
    $json = json_encode($arr);
    $data = curl($url,$json);
    $data = json_decode($data,true);
    $re = ($data['errmsg']=="ok")?true:false;
    return $re;
}
/**
 * @param $arr
 * @return 取消拉黑
 */
function unlahei($arr=[]){
    $token = WeAccount::token();
    $url="https://api.weixin.qq.com/cgi-bin/tags/members/batchunblacklist?access_token=$token";
    $arr =  ["openid_list"=>$arr];
    $json = json_encode($arr);
    $data = curl($url,$json);
    $data = json_decode($data,true);
    $re = ($data['errmsg']=="ok")?true:false;
    return $re;
}

/**
 * @param $s 变量
 * @param  $df 默认值
 * @return 判断变量是否存在不存在使用后一个值
 */
function is_set($s,$df=""){
    return isset($s)?$s:$df;
}

/**
 * @param  $src 二维码地址
 * @param  $title 标题
 * @param  $desc 描述
 * @return 生成关注二维码弹出框
 */
function isfollowqr($src="",$title="关注本公众号",$desc="官方账号"){
    $is="<div class=\"model-mask\"></div>";
    $str=<<<EOF
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <link rel='stylesheet'  href='https://res.wx.qq.com/open/libs/weui/0.4.3/weui.min.css'>
    <style type="text/css">
    .weui-model{width:100%;height:100%;position:fixed;z-index:9999;top:0;left:0;display:block;text-align:center;font-size: 20px;line-height: 1.6;}
.model-mask{width:100%;height:100%;background-color:#000;opacity:.7;cursor:pointer}
.model-main{width:80%;min-height:2.5em;background-color:#fff;color:#333;z-index:99999;border-radius:.2em;position:absolute;top:50%;left:50%;-webkit-transform:translate(-50%,-50%);transform:translate(-50%,-50%)}
.model-main .model-head{font-size:20px;padding:.6em 0;background:-webkit-gradient(linear,left top,left bottom,from(#fd7a71),to(#e5484c));background:-webkit-linear-gradient(top,#fd7a71,#e5484c);background:linear-gradient(to bottom,#fd7a71,#e5484c);border-radius:.1em .1em 0 0;position:relative}
.model-main .model-head p{color:#fff}.model-main .model-head p:nth-child(1){font-size:20px;line-height:1.5;font-weight:bold}
.model-main .model-head p:nth-child(2){font-size:16px;line-height:1.5}.model-main .model-body{padding:.5em;-webkit-box-sizing:border-box;box-sizing:border-box;min-height:5em;width:100%}
.model-main .model-body img{margin-top:.1em;width:70%}.model-main .model-body p{color:#333;line-height:1.6;font-size:16px}</style>
<div class="weui-model">{$is}<div class="model-main"><div class="model-head"><div class="m-title"><p>{$title}</p><p>{$desc}</p></div></div><div class="model-body"><div class="follow">
    <img src="{$src}">
    <p>长按识别图中二维码</p>
</div></div></div></div>
EOF;
    return $str;
}
/**
 * 移动端专用组件生成
 */

/**
 * @param  $text 描述
 * @param  $name 名称
 * @param  $val 默认值
 * @param  $req 是否必须
 * @return 号码文本框
 */
function m_number($text="号码",$name="number",$val="",$req=false){
    $req = ($req)?'<span class="f-red">*</span>':'';
    $str=<<<EOF
<div class="weui-cell">
        <div class="weui-cell__hd"><label class="weui-label">$text $req</label></div>
        <div class="weui-cell__bd">
            <input class="weui-input" pattern="[0-9]*" placeholder="$text" type="number" name="$name" id="$name" value="$val">
        </div>
        <i class="weui-icon-clear" onclick="$(this).prev().find('.weui-input').val('');"></i>
    </div>
EOF;
    return $str;
}

/**
 * @param  $text 描述
 * @param  $name 名称
 * @param  $val 默认值
 * @param  $req 是否必须
 * @return 手机号文本框
 */
function m_mobile($text="手机号",$name="mobile",$val="",$req=false){
    $req = ($req)?'<span class="f-red">*</span>':'';
    $str=<<<EOF
<div class="weui-cell">
        <div class="weui-cell__hd"><label class="weui-label">$text $req</label></div>
        <div class="weui-cell__bd">
            <input class="weui-input"  placeholder="$text" type="tel" name="$name" id="$name" value="$val">
        </div>
        <i class="weui-icon-clear" onclick="$(this).prev().find('.weui-input').val('');return false;"></i>
    </div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $val
 * @param  $req
 * @return 普通文本框
 */
function m_text($text="文本框",$name="txt",$val="",$req=false){
    $req = ($req)?'<span class="f-red">*</span>':'';
    $str=<<<EOF
<div class="weui-cell">
        <div class="weui-cell__hd"><label class="weui-label">$text $req</label></div>
        <div class="weui-cell__bd">
            <input class="weui-input"  placeholder="$text" type="text" name="$name" id="$name" value="$val">
        </div>
        <i class="weui-icon-clear" onclick="$(this).prev().find('.weui-input').val('');return false;"></i>
    </div>
EOF;
    return $str;
}

/**
 * @return 生成表单中间横线
 */
function m_hr(){
    return '<div class="weui-cell padding0"></div>';
}

/**
 * @param  $text
 * @param  $name
 * @param  $val 默认值
 * @param  $num 最多文字
 * @param  $req 是否必须
 * @return 生成文本域
 */
function m_texts($text="文本域",$name="texts",$val="",$num=100,$req=false){
    $req = ($req)?'<span class="f-red">*</span>':'';
    $str=<<<EOF
    <div class="weui-cells__title bold f17 f-black">$text $req</div>
<div class="weui-cell">
        <div class="weui-cell__bd">
            <textarea class="weui-textarea" name="$name" id="$name" placeholder="$text" rows="3" onkeyup="textarea(this);">$val</textarea>
            <div class="weui-textarea-counter"><span>0</span>/<i>$num</i></div>
        </div>
        <i class="weui-icon-clear" onclick="$(this).prev().find('.weui-textarea').val('').next().find('span').text(0);return false;"></i>
    </div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $arr 一维数组
 * @param  $val 数组默认值
 * @param  $req
 * @return 选择控件
 */
function m_picker_select($text="选择",$name="ps",$arr=[],$val="",$req=false){
    $req = ($req)?'<span class="f-red">*</span>':'';
    $arr = json_encode($arr);
    $str=<<<EOF
<div class="weui-cell">
                <div class="weui-cell__hd"><label class="weui-label">$text $req</label></div>
                <div class="weui-cell__bd">
                    <input class="weui-input" name="$name" id="$name" type="text" value="$val">
                </div>
                <i class="icon icon-6 f-gray" ></i>
            </div>
<script>
$("#{$name}").picker({
        title: "请选择$text",
        cols: [
            {
                textAlign: 'center',
                values: {$arr}
            }
        ],
        onChange: function(p, v, dv) {
         
        }
    });
</script>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $val 默认年月日
 * @param  $req 是否必须
 * @return 生日控件
 */
function m_birthday($text="生日",$name="bth",$val="2000-10-01",$req=false){
    $req = ($req)?'<span class="f-red">*</span>':'';
    $str=<<<EOF
<div class="weui-cell">
                <div class="weui-cell__hd"><label for="time-format" class="weui-label">$text $req</label></div>
                <div class="weui-cell__bd">
                    <input class="weui-input" name="$name" id="$name" type="text" value="$val">
                </div>
                <i class="icon icon-6 f-gray" ></i>
            </div>
<script>
var yearend=date("Y");
 $("#{$name}").datetimePicker({
        title: "选择{$text}",
        years:range(1930,yearend),
        times:function(){return [];},
		parse:function(str){
			return str.split("-");
		},
        onChange: function (picker, values, displayValues) {
            
        }
    });
</script>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $val 默认年月日时分
 * @param  $req
 * @return 日期时间控件
 */
function m_datetime($text="日期事件",$name="dt",$val="",$req=false){
    $req = ($req)?'<span class="f-red">*</span>':'';
    $val = empty($val)?date("Y-m-d H:i"):$val;
    $str=<<<EOF
<div class="weui-cell">
                <div class="weui-cell__hd"><label for="time-format" class="weui-label">$text $req</label></div>
                <div class="weui-cell__bd">
                    <input class="weui-input" name="$name" id="$name" type="text" value="$val">
                </div>
                <i class="icon icon-6 f-gray" ></i>
            </div>
<script>
 $("#{$name}").datetimePicker({
        title: "选择{$text}",
        min: "1930-01-01",
        max: "2050-12-31",
        onChange: function (picker, values, displayValues) {
        }
    });
</script>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $val 默认值省市县空格隔开
 * @param  $req 必须
 * @param  $iscity 是否包含县
 * @return 城市选择返回城市名或代码
 */
function m_city($text="选择城市",$name="city",$val="",$req=false,$iscity=true){
    $req = ($req)?'<span class="f-red">*</span>':'';
    $str=<<<EOF
<div class="weui-cell">
                <div class="weui-cell__hd"><label class="weui-label">$text $req</label></div>
                <div class="weui-cell__bd">
                    <input class="weui-input" name="$name" id="$name"  type="text" value="$val">
                </div>
                <i class="icon icon-6 f-gray" ></i>
            </div>
<script>
iscity=(empty({$iscity}))?false:true;
$("#{$name}").cityPicker({
                title: "选择{$text}",
                onChange: function (picker, values, displayValues) {
                    console.log(values, displayValues);
                },showDistrict: iscity,
                
            });
</script>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $arr数组[k=>v]
 * @param  $val 默认k
 * @param  $req
 * @return 单选
 */
function m_radio($text="单选",$name="radio",$arr=[],$val=1,$req=false){
    $req = ($req)?'<span class="f-red">*</span>':'';
    $str1='';
    if(!empty($arr)){
        foreach($arr as $k=>$v){
            $chk = ($k==$val)?"checked":"";
            $str1.='<div class="weui-form-li iblock">
            <input class="weui-form-checkbox"  name="'.$name.'"  id="'.$name.'-'.$k.'" value="'.$k.'" type="radio" '.$chk.'>
            <label for="'.$name.'-'.$k.'" >
                <i class="weui-icon-radio"></i>
                <div class="weui-form-text"><p>'.$v.'</p></div>
            </label>
        </div>';
        }
    }
    $str=<<<EOF
<div class="weui-cells__title  bold f17 f-black">$text $req</div>
    <div class="weui-form">$str1</div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $arr
 * @param  $val
 * @param  $req
 * @return 用于单选2个选项
 */
function m_sex($text="性别",$name="sex",$arr=[],$val=1,$req=false){
    $req = ($req)?'<span class="f-red">*</span>':'';
    $str1='';
    if(!empty($arr)){
        foreach($arr as $k=>$v){
            $chk = ($k==$val)?"checked":"";
            $str1.='<div class="weui-form-li iblock">
            <input class="weui-form-checkbox"  name="'.$name.'"  id="'.$name.'-'.$k.'" value="'.$k.'" type="radio" '.$chk.'>
            <label for="'.$name.'-'.$k.'" >
                <i class="weui-icon-radio"></i>
                <div class="weui-form-text"><p>'.$v.'</p></div>
            </label>
        </div>';
        }
    }
    $str=<<<EOF
    <div class="weui-cell">
        <div class="weui-cell__hd"><label class="weui-label">$text $req</div>
        <div class="weui-cell__bd">
    <div class="weui-form">$str1</div>
</div>
</div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $arr 数组
 * @param  $val 默认值用,分开
 * @param  $req
 * @return 复选框
 */
function m_checkbox($text="多选",$name="checkbox",$arr=[],$val="1,2,3",$req=false){
    $req = ($req)?'<span class="f-red">*</span>':'';
    $str1='';
    $arr1 =!empty($val)?str2arr($val):[];
    if(!empty($arr)){
        foreach($arr as $k=>$v){
            $chk = (in_array($k,$arr1))?"checked":"";
            $str1.='<div class="weui-form-li iblock">
            <input class="weui-form-checkbox"  name="'.$name.'[]"  id="'.$name.'-'.$k.'" value="'.$k.'"  type="checkbox" '.$chk.'>
            <label for="'.$name.'-'.$k.'">
                <i class="weui-icon-checkbox"></i>
                <div class="weui-form-text"><p>'.$v.'</p></div>
            </label>
        </div>';
        }
    }
    $str=<<<EOF
<div class="weui-cells__title bold f17 f-black">$text $req</div>
    <div class="weui-form">$str1</div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $val 默认值true/false
 * @param  $req
 * @return 开关
 */
function m_switch($text="开关",$name="switch",$val=true,$req=false){
    $req = ($req)?'<span class="f-red">*</span>':'';
    $chk = ($val==true)?"checked":"";
    $str=<<<EOF
<div class="weui-cell weui-cell_switch">
        <div class="weui-cell__bd bold">$text $req</div>
        <div class="weui-cell__ft">
            <label for="$name" class="weui-switch-cp">
                <input name="$name" class="weui-switch-cp__input" $chk type="checkbox" id="$name">
                <div class="weui-switch-cp__box"></div>
            </label>
        </div>
    </div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $arr 数组
 * @param  $val 默认值
 * @param  $req
 * @return 标准选择
 */
function m_select($text="选择",$name="select",$arr=[],$val=2,$req=false){
    $req = ($req)?'<span class="f-red">*</span>':'';
    $str1='';
    if(!empty($arr)){
        foreach($arr as $k=>$v){
            $chk = ($k==$val)?"selected":"";
            $str1.='<option '.$chk.' value="'.$k.'">'.$v.'</option>';
        }}
    $str=<<<EOF
<div class="weui-cell weui-cell_select weui-cell_select-after">
        <div class="weui-cell__hd">
            <label class="weui-label">$text $req</label>
        </div>
        <div class="weui-cell__bd">
            <select class="weui-select" name="$name" id="$name">
                $str1
            </select>
        </div>
    </div>
EOF;
    return $str;
}

/**
 * @param $text
 * @param  $name
 * @param $key 密钥
 * @param  $v1 默认地址
 * @param  $v2 默认经纬度
 * @param  $v3 默认名称
 * @param  $req 是否必填
 * @param  $isv1 是否显示地址
 * @param  $isv2 是否显示经纬度
 * @param  $isv3 是否显示名称
 * @return 腾讯地图选点
 */
function m_map($text="腾讯地图",$name='',$key,$v1='',$v2='',$v3='',$req=false,$isv1=true,$isv2=true,$isv3=true){
    $req = ($req)?'<span class="f-red">*</span>':'';
    $isv1 = $isv1==true?"":"hide";
    $isv2 = $isv2==true?"":"hide";
    $isv3 = $isv3==true?"":"hide";
    $str=<<<EOF
 <div class="weui-cell">
        <div class="weui-cell__hd bold f17 hand"><a href="javascript:void(0);" class="weui-label -bold f-blue" id="{$name}-map">$text <span class="icon icon-69"></span> $req</a></div>
        <div class="weui-cell__bd">
            <input class="weui-input $isv1" placeholder="地址" value="$v1" type="text" name="{$name}[address]" id="{$name}-address">
            <input class="weui-input $isv2" placeholder="经纬度" value="$v2" type="text" name="{$name}[latlng]" id="{$name}-latlng">
            <input class="weui-input $isv3" placeholder="名称" value="$v3" type="text" name="{$name}[name]" id="{$name}-name">
        </div>
    </div>
<script>
$(function(){
$(document).on("tap","#{$name}-map",function(){
        var str=`<iframe style="position: fixed;z-index:9999;bottom:0;height: 100%;" id="mapPage" width="100%" height="100%" frameborder=0
    src="//apis.map.qq.com/tools/locpicker?search=1&type=1&key={$key}&referer=myapp">
</iframe>`;
        $(str).prependTo('body');

    })
window.addEventListener('message', function(event) {
    var loc = event.data;
    if (loc && loc.module == 'locationPicker') {
        console.log('location', loc);
        $("#{$name}-address").val(loc.poiaddress);
        $("#{$name}-latlng").val(loc.latlng.lat+","+loc.latlng.lng);
        $("#{$name}-name").val(loc.poiname);
        $("#mapPage").remove();
    }
}, false); 
     })
</script>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $val 默认值
 * @param  $url URL
 * @param  $type 类型true带搜索false不带
 * @return 搜索控件
 */
function m_search($text="搜索",$name="",$val="",$url="",$type=true){
    parse_str($url,$arr);
    if(!empty($arr)){
        $str1='';
        foreach($arr as $k=>$v){
            if($k=="_/index_php?i"){
                $k="i";
            }
            $str1.='<input type="hidden" name="'.$k.'" value="'.$v.'" />';
        }
    }
    $str=<<<EOF
<div class="weui-search-bar" id="searchBar" >
    <form class="weui-search-bar__form" action="" method="get">
        <div class="weui-search-bar__box" style="height:2.2em;line-height: 2.2">
            <i class="weui-icon-search"></i>
            <input class="weui-search-bar__input" id="$name" placeholder="$text" type="search" name="$name" value="$val">
           $str1
            <a href="javascript:void(0);" class="weui-icon-clear" id="searchClear"></a>
        </div>
        <label class="weui-search-bar__label" id="searchText" style="transform-origin: 0px 0px 0px; opacity: 1; transform: scale(1, 1);">
            <i class="weui-icon-search"></i>
            <span>$val</span>
        </label>
    </form>
    <a href="javascript:void(0);" class="weui-search-bar__cancel-btn" id="searchCancel">取消</a>
</div>
EOF;
    $str2=<<<EOF
<form class="" action="" method="get">
<div class="weui-search-bar">
<input style="line-height: 2.5;border-color:#07c160;border-right:none;" type="search" class="search-input" id="$name" placeholder="$text" name="$name" value="$val">
$str1
<button style="line-height: 2.7;border-radius: 0;border:none;" class="weui-btn weui-btn_mini weui-btn_primary"><i class="icon icon-4"></i></button>
</div>
</form>
EOF;
    return $type==true?$str2:$str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $val 图片默认值
 * @param  $mod 模块名称
 * @param  $isfirst 是否第一个默认是
 * @param  $req
 * @return 普通方式上传单个图片返回链接
 */
function m_upimg($text="单图",$name="",$val="",$mod="",$isfirst=true,$req=false){
    $req = ($req)?'<span class="f-red">*</span>':'';
    $ajax=murl('entry/site/saveimg',['m'=>$mod]);
    $del=murl('entry/site/delimg',['m'=>$mod]);
    if($isfirst){
        $str1=<<<EOF
<script>
            function removeimg(obj){
                if(confirm('您确定要删除吗?')){
               var img =   $(obj).children().val();
                  $.post("{$del}",{filename:img},()=>{
                      $(obj).remove();
                  });
                }else{
                return false;
                }
            }
            function uploadimg(obj) {
                $.showLoading("正在上传...");
                lrz(obj.files[0],{width:750,fieldName:"file"}).then(function(data) {
                    $.post("{$ajax}",{imgbase64: data.base64},function(rs){
                        $.hideLoading();
                        $(obj).parent().prev().html('<li onclick="removeimg(this)" class="weui-uploader__file" style="background-image:url('+(rs.data.src)+')"><input value="'+rs.data.val+'"  type="hidden"  name="$name" id="$name" /></li>');
                    },'json');
                 
                }).then(function(data) {
                $.hideLoading();
                }).catch(function(err) {
                    $.hideLoading();
                });
            }
        </script>
EOF;

    }
    if(!empty($val)){
        $val='<li onclick="removeimg(this)" class="weui-uploader__file" style="background-image:url('.tomedia($val).')"><input value="'.$val.'"  type="hidden"  name="'.$name.'" id="'.$name.'" /></li>';
    }
    $str=<<<EOF
<div class="weui-uploader page-bd-15">
        <div class="weui-uploader__hd">
            <p class="weui-uploader__title">$text $req</p>
        </div>
        <div class="weui-uploader__bd">
            <ul class="weui-uploader__files">$val
            </ul>
            <div class="weui-uploader__input-box">
                <input class="weui-uploader__input" accept="image/*"  type="file"  onchange="uploadimg(this)">
            </div>
        </div>
</div>
$str1
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $val 默认值json字符串
 * @param  $mod 模块名
 * @param  $isfirst 是否第一个控件默认是
 * @param  $n 最多上传几张
 * @param  $req
 * @return 上传多个图片
 */
function m_upimgs($text="多图",$name="",$val="",$mod="",$isfirst=true,$n=9,$req=false){
    $req = ($req)?'<span class="f-red">*</span>':'';
    $ajax=murl('entry/site/saveimg',['m'=>$mod]);
    $del=murl('entry/site/delimg',['m'=>$mod]);
    if($isfirst){
        $str1=<<<EOF
<script>
            function removeimgs(obj){
                if(confirm('您确定要删除吗?')){
               var img =   $(obj).children().val();
                  $.post("{$del}",{filename:img},()=>{
                      $(obj).remove();
                  });
                }else{
                return false;
                }
            }
   $(document).on("change",".upimgs",function(){
       var obj=$(this)[0];
       var n= {$n};
        var num =$(obj).parent().prev().find("li").length;
         var n1 = n-num;
               if(n1<=0){
                   $.toast("最多只能上传"+n+"张图片","text");return false;
               }
               $.showLoading("正在上传...");
                 var files =  obj.files;
                var len = files.length;
                for (var i=0; i < len; i++) {     
                lrz(files[i],{width:750,fieldName:"file"}).then(function(data) {
                    $.post("{$ajax}",{imgbase64: data.base64},function(rs){
                        $(obj).parent().prev().append('<li onclick="removeimgs(this)" class="weui-uploader__file" style="background-image:url('+(rs.data.src)+')"><input value="'+rs.data.val+'"  type="hidden"  name="{$name}[]" /></li>');
                    },'json');
                 
                }).then(function(data) {
                $.hideLoading();
                }).catch(function(err) {
                    $.hideLoading();
                });
               }
   })             
        </script>
EOF;

    }
    if(!empty($val)){
        $imgs = json_decode($val,true);
        $html='';
        foreach($imgs as $v){
            $html.='<li onclick="removeimgs(this)" class="weui-uploader__file" style="background-image:url('.tomedia($v).')"><input value="'.$v.'"  type="hidden"  name="'.$name.'[]" /></li>';

        }
    }
    $str=<<<EOF
<div class="weui-uploader page-bd-15">
        <div class="weui-uploader__hd">
            <p class="weui-uploader__title">$text $req</p>
        </div>
        <div class="weui-uploader__bd">
            <ul class="weui-uploader__files">$html
            </ul>
            <div class="weui-uploader__input-box">
                <input class="weui-uploader__input upimgs" accept="image/*" multiple="multiple"   type="file" >
            </div>
        </div>
</div>
$str1
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @return 提交按钮
 */
function  m_btn($text="提交",$name="btn"){
    $str=<<<EOF
<div class="weui-btn-area">
    <button class="weui-btn weui-btn_primary"  name="$name" id="$name"  >$text</button>
</div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $url 跳转地址
 * @param  $class 添加类
 * @return 删除按钮
 */
function m_del($text="删除",$name="",$url="",$class=""){
    $name = !empty($name)?" id='$name' ":"";
    $class=!empty($class)?$class:"bg-red f-white";
    $url = empty($url)?"javascript:void(0);":$url;
    return '<a  '.$name.' href="'.$url.'"  class="weui-btn weui-btn_mini '.$class.' " onclick="return confirm(\'确认操作吗？\'); return false;">'.$text.'</a>';
}

/**
 * @param  $text
 * @param  $name
 * @param  $url 跳转地址
 * @param  $class 附加类
 * @return 编辑或一般链接
 */
function m_a($text="编辑",$name="",$url="",$class=""){
    $name = !empty($name)?" id='$name' ":"";
    $class=!empty($class)?$class:"bg-green f-white";
    $url = empty($url)?"javascript:void(0);":$url;
    return 	'<a '.$name.'  href="'.$url.'" class="weui-btn weui-btn_mini '.$class.' " >'.$text.'</a>';
}

/**
 * @param  $text 标签内容
 * @param $class 附加类
 * @return 返回标签
 */
function m_lab($text="",$class=""){
    return '<div class="weui-cells__title '.$class.'">'.$text.'</div>';
}

/**
 * @param  $name
 * @param $v
 * @return 隐藏域
 */
function m_hidden($name='id',$v){
    return '<input id="'.$name.'" type="hidden" name="'.$name.'" value="'.$v.'">';
}

/**
 * @param  $text 字符串
 * @param  $top 距离上边百分比
 * @return 没有数据模板
 */
function m_nodata($text="没有数据",$top="60%"){
    return '<div class="weui-msgbox"><p style="margin-top:'.$top.'"><i class="weui-icon-info-circle f18 f-blue"></i>'.$text.'</p></div>';
}

/**
 * @param  $title 标题
 * @param  $desc 描述
 * @param  $img 分享图片留空使用程序图标
 * @param $url 要跳转地址可以留空
 * @param $isshow 是否显示分享
 * @return jssdk签名和显示jssdk使用必须调用
 */
function m_jssdk($title="标题",$desc="",$img="",$url="",$isshow=true){
    global $_W;
    $url = (empty($url))?$_W['siteroot'] . $_SERVER['REQUEST_URI']:$url;
    $img = empty($img)?MODULE_URL."icon.jpg":$img;
    $config=json_encode($_W['account']['jssdkconfig']);
    $s="";
    if($isshow){
        $s=<<<EOF
wx.showMenuItems({
            menuList: ['menuItem:share:timeline', 'menuItem:share:appMessage', 'menuItem:copyUrl', "menuItem:share:qq", "menuItem:share:QZone", "menuItem:favorite"],
        });
EOF;
    }
    $str=<<<EOF
<script type="text/javascript" src="//res2.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
<script>
    var jssdkconfig =  {$config} || {};
    jssdkconfig.debug=false;
    jssdkconfig.jsApiList =['checkJsApi', 'updateAppMessageShareData', 'updateTimelineShareData', 'hideMenuItems', 'showMenuItems', 'hideAllNonBaseMenuItem', 'showAllNonBaseMenuItem', 'translateVoice', 'startRecord', 'stopRecord', 'onVoiceRecordEnd', 'playVoice', 'onVoicePlayEnd', 'pauseVoice', 'stopVoice', 'uploadVoice', 'downloadVoice', 'chooseImage', 'previewImage', 'uploadImage', 'downloadImage', 'getNetworkType', 'openLocation', 'getLocation', 'hideOptionMenu', 'showOptionMenu', 'closeWindow', 'scanQRCode', 'chooseWXPay', 'openProductSpecificView', 'addCard', 'chooseCard', 'openCard'];
    wx.config(jssdkconfig);
    wx.ready(()=>{
        var shareData = {
            title:"{$title}",
            desc: "{$desc}",
            link:"{$url}",
            imgUrl:"{$img}",
            type:'link',
            dataUrl:'',
        };
        wx.hideOptionMenu();
         {$s}
        wx.updateAppMessageShareData({
            title: shareData.title,
            desc: shareData.desc,
            link: shareData.link,
            imgUrl: shareData.imgUrl,
            success: function (rs) {
            }
        })

        wx.updateTimelineShareData({
            title: shareData.title,
            link: shareData.link,
            imgUrl: shareData.imgUrl,
            success: function (rs) {
            }
        });
    });
</script>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $val
 * @param  $mod
 * @param  $isfirst 是否第一个
 * @param  $req
 * @return jssdk上传单图
 */
function m_jssdk_img($text="单图",$name="",$val="",$mod="",$isfirst=true,$req=false){
    $req = ($req)?'<span class="f-red">*</span>':'';
    $ajax=murl('entry/site/savemid',['m'=>$mod]);
    $del=murl('entry/site/delimg',['m'=>$mod]);
    if($isfirst){
        $str1=<<<EOF
<script>
            function jremoveimg(obj){
                if(confirm('您确定要删除吗?')){
               var img =   $(obj).children().val();
                  $.post("{$del}",{filename:img},()=>{
                      $(obj).remove();
                  });
                }else{
                return false;
                }
            }
         function jssdkimg(obj){
    wx.chooseImage({
        count:1,
        success: function (res) {
            var localIds = res.localIds;
            wx.uploadImage({
                localId:'' + localIds,
                isShowProgressTips: 1,
                success: function (res) {
                    var serverId = res.serverId;
                    console.log(serverId)
                $.post("{$ajax}",{"mid":serverId},function(rs){
                    $(obj).parent().prev().html('<li onclick="jremoveimg(this)" class="weui-uploader__file" style="background-image:url('+(rs.data.src)+')"><input value="'+rs.data.val+'"  type="hidden"  name="$name" id="$name" /></li>');
                    console.log(rs)
                    
                });
                }
            });


        }
    });
}
        </script>
EOF;
    }
    if(!empty($val)){
        $val='<li onclick="jremoveimg(this)" class="weui-uploader__file" style="background-image:url('.tomedia($val).')"><input value="'.$val.'"  type="hidden"  name="'.$name.'" id="'.$name.'" /></li>';
    }
    $str=<<<EOF
<div class="weui-uploader page-bd-15">
        <div class="weui-uploader__hd">
            <p class="weui-uploader__title">$text $req</p>
        </div>
        <div class="weui-uploader__bd">
            <ul class="weui-uploader__files">$val
            </ul>
            <div class="weui-uploader__input-box">
                <span class="weui-uploader__input" onclick="jssdkimg(this)"></span>
            </div>
        </div>
</div>
$str1
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $val 默认json字符串
 * @param  $mod 模块名
 * @param  $isfirst 是否第一个
 * @param  $n 最多上传数字
 * @param  $req
 * @return jssdk上传多图
 */
function m_jssdk_imgs($text="多图",$name="",$val="",$mod="",$isfirst=true,$n=9,$req=false){
    $req = ($req)?'<span class="f-red">*</span>':'';
    $ajax=murl('entry/site/savemid',['m'=>$mod]);
    $del=murl('entry/site/delimg',['m'=>$mod]);
    if($isfirst){
        $str1=<<<EOF
<script>
            function jremoveimgs(obj){
                if(confirm('您确定要删除吗?')){
               var img =   $(obj).children().val();
                  $.post("{$del}",{filename:img},()=>{
                      $(obj).remove();
                  });
                }else{
                return false;
                }
            }
           function jssdkimgs(obj,n=9){
                  var num =$(obj).parent().prev().find("li").length;
               var n1 = n-num;
               if(n1<=0){
                   $.toast("最多只能上传"+n+"张图片","text");return false;
               }
                wx.chooseImage({
                    count:n,
                    success: function (res) {
                        var localIds = res.localIds;
                        syncupload(localIds,obj);
                    }
                });
            }
            function syncupload(localIds,obj){
                var localId = localIds.pop();
                wx.uploadImage({
                    localId:localId,
                    isShowProgressTips: 1,
                    success: function (res) {
                        var serverId = res.serverId; // 返回图片的服务器端ID
                        $.post("{$ajax}",{"mid":serverId},function(rs){
                            $(obj).parent().prev().append('<li onclick="jremoveimgs(this)" class="weui-uploader__file" style="background-image:url('+(rs.data.src)+')"><input value="'+rs.data.val+'"  type="hidden"  name="{$name}[]" /></li>');
                        });
                        if(localIds.length > 0){
                            syncupload(localIds,obj);
                        }
                    }
                });
            }
        </script>
EOF;

    }
    if(!empty($val)){
        $imgs = json_decode($val,true);
        $html='';
        foreach($imgs as $v){
            $html.='<li onclick="jremoveimgs(this)" class="weui-uploader__file" style="background-image:url('.tomedia($v).')"><input value="'.$v.'"  type="hidden"  name="'.$name.'[]" /></li>';

        }
    }
    $str=<<<EOF
<div class="weui-uploader page-bd-15">
        <div class="weui-uploader__hd">
            <p class="weui-uploader__title">$text $req</p>
        </div>
        <div class="weui-uploader__bd">
            <ul class="weui-uploader__files">$html
            </ul>
            <div class="weui-uploader__input-box">
               <span class="weui-uploader__input" onclick="jssdkimgs(this,{$n})"></span>
            </div>
        </div>
</div>
$str1
EOF;
    return $str;
}

/**
 * 生成adcode 城市编码
 * district 县名称
 * location json字符串位置信息
 * @param $key 地图密钥
 * @return 自动定位腾讯地图
 */
function m_location($key){
    $str=<<<EOF
<iframe id="geoPage" frameborder=0 scrolling="no" src="//apis.map.qq.com/tools/geolocation?key=$key&referer=myapp&effect=zoom" allow="geolocation" style="display:none;"></iframe>
<script charset="utf-8" src="//map.qq.com/api/js?v=2.exp&key=$key"></script>
<script>
var lock=false;
window.addEventListener('message', function (event) {
               var loc = event.data;
                var codes = localStorage.getItem('adcode');
                if (loc && loc.module == 'geolocation') {
                    if (codes == null) {
                        localStorage.setItem('adcode', loc.adcode);
                    }
                    console.log(loc);
                    localStorage.setItem('location', JSON.stringify(loc));   
                    localStorage.setItem('district', loc.district);
                   var district = localStorage.getItem('district'); 
  if(district==null){             
var geocoder = new qq.maps.Geocoder({
    complete:function(res){
        console.log(res)
        localStorage.setItem('district', res.detail.addressComponents.district);
        loc.district=res.detail.addressComponents.district;
        loc.town=res.detail.addressComponents.town;
        loc.street=res.detail.addressComponents.street;
    localStorage.setItem('location', JSON.stringify(loc));
    }
});
var coord=new qq.maps.LatLng(loc.lat,loc.lng);
geocoder.getAddress(coord);
}}
    if(lock) {return;}
ock=true;
 }, false);
</script>
EOF;
    return $str;
}

/**
 * @param  $arr 二维数组包含链接标题
 * @return 生成底部版权
 */
function m_footer($arr=[]){
    global $_W;
    $icp = $_W["setting"]['copyright']['icp'];
    $urls="";
    if(!empty($arr)){
        foreach($arr as $k=>$v){
          $urls.='<a href="'.$v['url'].'" class="weui-footer__link">'.$v['title'].'</a>&nbsp;&nbsp;&nbsp;&nbsp;';
        }
    }
    $date=date("Y");
    $str=<<<EOF
<div class="weui-footer" style="margin:1em auto">
    <p class="weui-footer__links">$urls</p>
    <p class="weui-footer__text">Copyright © $date <a rel="nofollow" target="_blank" href="https://beian.miit.gov.cn/">$icp</a></p>
</div>
EOF;
return $str;
}

/**
 * @return 协议控件
 */
function m_xy($text="协议内容",$name="xy"){
    $str=<<<EOF
<div  class="weui-agree f17">
 <label for="$name" class="weui-agree__text">
    <input id="$name" name="$name" class="weui-agree__checkbox" type="checkbox" style="width:17px;height:17px;margin-right:5px;">阅读并同意
</label><a href="javascript:void(0);" onclick="$('#xy1122').toggle();">《相关协议》</a>
</div>
<div id="xy1122" class="weui-cells__title hide" style="margin-top:0;">
 <p class="content f16">$text</p>
</div>
EOF;
return $str;
}
/**
 * PC端常用控件
 */
/**
 * @param $arr 数组k=>v
 * @param  $mod 模块名
 * @return 后台切换菜单
 */
function we7_tab($arr=["rank"=>"粉丝排名","say"=>"关注回复","dev"=>"创建模块","demo"=>"表单演示"],$mod="yoby_test"){
    global $_GPC;
    $str="";
    foreach ($arr as $k=>$v){
        $ac = $_GPC['do']==$k?"active":"";
        $url=wurl('site/entry/'.$k,['m'=>$mod]);
       $str.= '<li class="'.$ac.'"><a href="'.$url.'">'.$v.'</a></li>';
    }
    $str='<ul class="we7-page-tab">'.$str.'</ul>';
    return $str;
}
/**
 * @param  $text
 * @param  $name
 * @param  $value
 * @param  $desc
 * @return 文本框
 */
function we7_text($text="文本框",$name='',$value='',$desc=''){
    $str=<<<EOF
  <div class="form-group">
        <label class="col-sm-2 control-label">$text</label>
        <div class="form-controls col-sm-8">
            <input type="text" name="$name" class="form-control" id="$name" value="$value" />
            <div class="help-block">$desc</div>
        </div>
    </div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @return 提交按钮
 */
function we7_btn($text='提交',$name=""){
    global $_W;
    $str=<<<EOF
<div class="form-group">
<label class="col-sm-2 control-label">&nbsp;</label>
        <div class="form-controls col-sm-1">
        <input type="hidden" name="token" value="{$_W['token']}" />
            <input name="submit" type="submit" value="$text" class="btn btn-primary btn-block" id="$name" />    
        </div>
    </div>
EOF;
    return $str;
}

/**
 * @param  $text 标签
 * @param  $name
 * @param  $value 默认值
 * @param  $desc 描述
 * @return 文本域
 */
function we7_texts($text="文本域",$name='',$value='',$desc='描述说明'){
    $str=<<<EOF
<div class="form-group">
                <label class="col-sm-2 control-label">$text</label>
                <div class="form-controls col-sm-8">
			<textarea class="form-textarea " style="border:1px solid #ccc" id="$name" name="$name" max-length="120" cols="75%" rows="4">$value</textarea>
			<div class="help-block">$desc</div>
                </div>
            </div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $value
 * @param  $desc
 * @return 百度富文本框
 */
function we7_editor($text="富文本",$name='',$value='',$desc='描述'){
    $txt = tpl_ueditor($name, $value);
    $str=<<<EOF
<div class="form-group">
					<label class="col-sm-2 control-label">$text</label>
					<div class="form-controls col-sm-8">{$txt}
					<div class="help-block">$desc</div>
					</div>
				</div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $arr 数组k=>v
 * @param  $value 默认k
 * @param  $desc
 * @param  $default 请选择
 * @return 选择
 */
function we7_select($text="选择",$name="",$arr=[],$value="",$desc='',$default=" 请选择 "){
    if(!empty($arr)){
        $ss='';
        foreach ($arr as $k=>$v){
            $select=($k==$value)?"selected":"";
            $ss.="<option value='$k'  $select>$v</option>";
        }
    }
    $defalut = empty($default)?"":'<option value="">'.$default.'</option>';
    $str=<<<EOF
		<div class="form-group">
			<label class="col-sm-2 control-label">$text</label>
				<div class="form-controls col-sm-8">
					<select name="$name" id="$name" class="form-control">
						$defalut
						{$ss}
					</select>
					<div class="help-block">$desc</div>
				</div>
		</div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $value 默认图片
 * @param  $desc
 * @return 缩略图单图上传
 */
function we7_image($text="缩略图",$name="",$value='',$desc=''){
    $ss = tpl_form_field_image($name,$value);
    $str=<<<EOF
   <div class="form-group">
                <label class="col-sm-2 control-label">$text</label>
                <div class="form-controls col-sm-8">$ss
                <div class="help-block">$desc</div>
                </div>
                
            </div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $arr 数组k=>v
 * @param  $value 默认k
 * @param  $desc
 * @return 单选
 */
function we7_radio($text="单选",$name="rad",$arr=[],$value=1,$desc=""){
    if(!empty($arr)){
        $ss="";
        foreach ($arr as $k=>$v){
            $chk = ($k==$value)?"checked":"";
            $ss.='<input id="'.$name.'['.$k.']"  '.$chk.' type="radio" name="'.$name.'" value="'.$k.'" /><label style="margin-right:30px;" for="'.$name.'['.$k.']">'.$v.'</label>';
        }
    }
    $str=<<<EOF
   <div class="form-group">
                <label class="control-label col-sm-2">$text</label>
                <div class="form-controls form-control-static col-sm-8">$ss
                    <span class="help-block">$desc</span>
                </div>
            </div>
EOF;
    return $str;
}

/**
 * @param $arr[]
 * @param $value 默认值[]和前面对应
 * @return 内链文本框
 */
function we7_text_inline($arr=[["身高","厘米","height","100"]],$value=[]){
    $str="<div class='form-inline'>";
    foreach($arr as $k=>$v){
        $vv = isset($value[$k])?$value[$k]:$v[3];
        $str.='<div class="input-group padding20-r padding10-b">
                        <div class="input-group-addon">'.$v[0].'</div>
                        <input type="text"  class="form-control" value="'.$vv.'" name="'.$v[2].'" >
                        <div class="input-group-addon">'.$v[1].'</div>
                    </div>';
    }
    $str.="</div>";
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param $arr 选项值
 * @param  $value 默认值,隔开
 * @param  $desc
 * @return 复选框
 */
function we7_checkbox($text="复选",$name="chk",$arr=[1,2,3],$value="1,2,3",$desc=""){
    if(!empty($arr)){
        $ss="";
        $arr1 =!empty($value)?str2arr($value,','):[];
        foreach ($arr as $k=>$v){
            $chk = (in_array($k,$arr1))?"checked":"";
            $ss.='<input id="'.$name.'['.$k.']"  '.$chk.' type="checkbox" name="'.$name.'[]" value="'.$k.'" /><label style="margin-right:30px;" for="'.$name.'['.$k.']">'.$v.'</label>';
        }
    }
    $str=<<<EOF
   <div class="form-group">
                <label class="control-label col-sm-2">$text</label>
                <div class="form-controls form-control-static col-sm-8">$ss
                    <span class="help-block">$desc</span>
                </div>
            </div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $value 默认数组
 * @param  $desc
 * @return 多图上传
 */
function we7_images($text="图片上传",$name="", $value = [],$desc='',$options=[]) {
    global $_W;
    $options['multiple'] = true;
    $options['direct'] = false;
    $options['fileSizeLimit'] = intval($GLOBALS['_W']['setting']['upload']['image']['limit']) * 1024;
    if (isset($options['dest_dir']) && !empty($options['dest_dir'])) {
        if (!preg_match('/^\w+([\/]\w+)?$/i', $options['dest_dir'])) {
            exit('图片上传目录错误,只能指定最多两级目录,如: "we7_store","we7_store/d1"');
        }
    }
    $s = '';
    if (!defined('TPL_INIT_MULTI_IMAGE')) {
        $s = '
<script type="text/javascript">
	function uploadMultiImagex(elm) {
		var name = $(elm).next().val();
		util.image( "", function(urls){
			$.each(urls, function(idx, url){
				$(elm).parent().next().next().append(\'<div class="multi-item"><img onerror="this.src=\\\'./resource/images/nopic.jpg\\\'; this.title=\\\'图片未找到.\\\'" src="\'+url.url+\'" class="img-responsive img-thumbnail"><input type="hidden" name="\'+name+\'[]" value="\'+url.attachment+\'"><em class="close" title="删除这张图片" onclick="deleteMultiImagex(this)">×</em></div>\');
			});
		}, ' . json_encode($options) . ');
	}
	function deleteMultiImagex(elm){
		$(elm).parent().remove();
	}
</script>';
        define('TPL_INIT_MULTI_IMAGE', true);
    }
    $s .= <<<EOF
    <div class="form-group">
    <label class="col-sm-2 control-label">$text</label>
<div class="we7-input-img input-more input-img" style="width:50px;height:50px;">
<a href="javascript:;" class="input-addon" onclick="uploadMultiImagex(this);" ><span>+</span></a>
<input type="hidden" value="{$name}" />
</div>
<div class='help-block'>$desc</div>
<div class=" multi-img-details">
EOF;
    if (is_array($value) && count($value) > 0) {
        foreach ($value as $row) {
            $s .= '
<div class="multi-item">
	<img src="' . tomedia($row) . '" onerror="this.src=\'./resource/images/nopic.jpg\'; this.title=\'图片未找到.\'" class="img-responsive img-thumbnail">
	<input type="hidden" name="'.$name.'[]" value="' . $row . '" >
	<em class="close" title="删除这张图片" onclick="deleteMultiImagex(this)">×</em>
</div>';
        }
    }
    $s .= '</div></div>';

    return $s;
}

/**
 * @param  $text
 * @param  $name
 * @param  $value
 * @return 颜色选择
 */
function we7_color($text="选择颜色",$name='',$value=''){
    $ss=tpl_form_field_color($name, $value);
    $str=<<<EOF
  <div class="form-group">
        <label class="col-sm-2 control-label">$text</label>
        <div class="form-controls col-sm-8">$ss</div>
    </div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $value
 * @param  $withtime 是否包含时间默认是
 * @return 日期时间
 */
function we7_date($text="日期",$name='',$value='',$withtime=true){
    $ss=_tpl_form_field_date($name, $value, $withtime);
    $str=<<<EOF
  <div class="form-group">
        <label class="col-sm-2 control-label">$text</label>
        <div class="form-controls col-sm-5">$ss</div>
    </div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $value
 * @return 表情选择
 */
function we7_emoji($text="表情",$name='',$value=''){
    $ss=tpl_form_field_emoji($name, $value);
    $str=<<<EOF
  <div class="form-group">
        <label class="col-sm-2 control-label">$text</label>
        <div class="form-controls col-sm-2">$ss</div>
    </div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $value
 * @return 图标选择
 */
function we7_icon($text="icon",$name='',$value=''){
    $ss=tpl_form_field_icon($name, $value);
    $str=<<<EOF
  <div class="form-group">
        <label class="col-sm-2 control-label">$text</label>
        <div class="form-controls col-sm-8">$ss</div>
    </div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $value
 * @param  $desc
 * @return 音乐选择
 */
function we7_audio($text="音乐上传",$name='',$value='',$desc=''){
    $ss=tpl_form_field_audio($name, $value);
    $str=<<<EOF
  <div class="form-group">
        <label class="col-sm-2 control-label">$text</label>
        <div class="form-controls col-sm-7">$ss<div class='help-block'>$desc</div></div>
        
    </div>
EOF;
    return $str;
}
/**
 * @param  $text
 * @param  $name
 * @param  $value
 * @param  $desc
 * @return 视频选择
 */
function we7_video($text="视频上传",$name='',$value='',$desc=''){
    $ss=tpl_form_field_video($name, $value);
    $str=<<<EOF
  <div class="form-group">
        <label class="col-sm-2 control-label">$text</label>
        <div class="form-controls col-sm-7">$ss<div class='help-block'>$desc</div></div>
       
    </div>
EOF;
    return $str;
}

/**
 * @param  $text 名称
 * @param  $data 要复制数据
 * @return 复制粘贴
 */
function we7_copy($text="复制链接",$data='复制数据'){
    return '<a title="'.$data.'" href="javascript:void(0);" class="js-clip color-default" data-url="'.$data.'">'.$text.'</a>';
}

/**
 * @param  $text
 * @param  $name
 * @param  $value1 开始时间
 * @param  $value2 结束时间
 * @param  $time 是否包含时间
 * @return 选择时间段
 */
function we7_dates($text="选择时间段",$name="",$value1='2018-10-1',$value2='2019-10-1',$time=true){
    $ss=tpl_form_field_daterange($name,["starttime"=>$value1,"endtime"=>$value2],$time);
    $str=<<<EOF
<div class="form-group">
            <label class="col-sm-2 control-label">$text</label>
            <div class="col-sm-9">$ss</div>
        </div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $value
 * @return 生日选择
 */
function we7_birthday($text="出生生日",$name='birthday',$value='19871212'){
    $ss=tpl_form_field_calendar($name, $value);
    $str=<<<EOF
  <div class="form-group">
        <label class="col-sm-2 control-label">$text</label>
        <div class="form-controls col-sm-8">$ss</div>
    </div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param $key 腾讯地图蜜月
 * @param  $v1 经纬度
 * @param  $v2 地址
 * @param  $v3 名称
 * @param  $desc 描述
 * @param  $isv1 是否隐藏经纬度
 * @param  $isv2 是否隐藏地址
 * @param  $isv3 是否隐藏名称
 * @return 腾讯地图选择
 */
function we7_map($text="腾讯地图选择",$name='',$key,$v1='',$v2='',$v3='',$desc='描述说明',$isv1=true,$isv2=true,$isv3=true){
    $isv1 = $isv1==true?"":"hidden";
    $isv2 = $isv2==true?"":"hidden";
    $isv3 = $isv3==true?"":"hidden";
    $str=<<<EOF
<div class="form-group">
                <label class="col-sm-2 control-label"><a id="qqmap{$name}" class="btn btn-primary btn-xs" href="javascript:void(0);">$text</a></label>
                <div class="form-controls col-sm-8">
                <div class="col-xs-4 col-sm-4 $isv1">
				<input type="text" name="{$name}[latlng]" value="$v1" placeholder="经纬度" class="form-control" id="{$name}1">
			</div>
			<div class="col-xs-4 col-sm-4 $isv2">
				<input type="text" name="{$name}[address]" value="$v2" placeholder="地址" class="form-control" id="{$name}2">
			</div>
			<div class="col-xs-4 col-sm-4 $isv3">
				<input type="text" name="{$name}[name]" value="$v3" placeholder="名称" class="form-control" id="{$name}3">
			</div>
			<div class="help-block">$desc</div>
                </div>
            </div>
<script>
            var index;
            $("#qqmap{$name}").click(()=>{
            index = layer.open({
                    type: 2,
                    title: false,
                    area: ['60%', '90%'],
                    shade: 0.8,
                    closeBtn: 1,
                    shadeClose: true,
                    content:"https://apis.map.qq.com/tools/locpicker?search=1&type=1&key=$key&referer=myapp "
                });
            })
            window.addEventListener('message', function(event) {
                var loc = event.data;
                if (loc && loc.module == 'locationPicker') {
                    console.log(loc)
                    $("#{$name}2").val(loc.poiaddress);
                    $("#{$name}1").val(loc.latlng.lat+","+loc.latlng.lng);
                     $("#{$name}3").val(loc.poiname);
                    layer.close(index);
                }
            }, false);
        </script>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $v1 省
 * @param  $v2 市
 * @param  $v3 县
 * @param  $type类型code或null
 * @param  $isv1 是否显示省
 * @param  $isv2 是否显示市
 * @param  $isv3 是否显示县
 * @return 选择省市县
 */
function we7_city($text="省市县选择",$name='ssx',$v1='',$v2='',$v3='',$type="",$isv1=true,$isv2=true,$isv3=true){
    $type = ($type=="code")?'data-value-type="code"':"";
    $isv1 = ($isv1)?"<select name='{$name}[]' data-province='$v1'></select>":"";
    $isv2 = ($isv2)?"<select name='{$name}[]' data-city='$v2'></select>":"";
    $isv3 = ($isv3)?"<select name='{$name}[]' data-district='$v3'></select>":"";
    $str=<<<EOF
  <div class="form-group">
        <label class="col-sm-2 control-label">$text</label>
        <div class="form-controls col-sm-7">
        <div data-toggle="distpicker" $type>
        $isv1 $isv2 $isv3
</div>
        </div>
       
    </div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $val 默认值
 * @param  $url 提交地址
 * @return GET搜索
 */
function we7_search($text="请输入关键字",$name="",$val="",$url=""){
    parse_str($url,$arr);
    if(!empty($arr)){
        $str1='';
        foreach($arr as $k=>$v){
            if($k=="_/index_php?c"){
                $k="c";
            }
            $str1.='<input type="hidden" name="'.$k.'" value="'.$v.'" />';
        }
    }
    $str=<<<EOF
            <form action="./index.php" method="get" class="form-horizontal ng-pristine ng-valid" role="form">
			$str1
			<div class="input-group col-sm-4 pull-left">
				<input name="$name" id="$name" value="$val" class="form-control" placeholder="$text" type="text">
				<span class="input-group-btn"><button class="btn btn-default"><i class="fa fa-search"></i></button></span>
			</div>
		</form>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $v
 * @param  $url
 * @return POST搜索
 */
function we7_post($text="请输入关键字",$name="",$v="",$url=""){
    $str=<<<EOF
<form action="$url" class="search-form ng-pristine ng-valid" method="post">
						<div class="input-group col-sm-4 pull-left">
							<input type="text" name="$name" id="$name"  class="form-control" size="40" value="" placeholder="$v">
							<span class="input-group-btn">
								<button class="btn btn-default"><i class="fa fa-search"></i></button>
							</span>
						</div>
					</form>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $url
 * @param  $class
 * @return 删除按钮
 */
function we7_del($text="删除",$name="",$url="",$class=""){
    $name = !empty($name)?" id='$name' ":"";
    $class=!empty($class)?$class:"bg-red f-white";
    $url = empty($url)?"javascript:;":$url;
    return '<a  '.$name.' href="'.$url.'"  class="btn margin10-r '.$class.' btn-xs" onclick="return confirm(\'此操作不可恢复，确认吗？\'); return false;">'.$text.'</a>';
}

/**
 * @param  $text
 * @param  $name
 * @param  $url
 * @param  $class
 * @return 默认链接
 */
function we7_a($text="编辑",$name="",$url="javascript:;",$class=""){
    $name = !empty($name)?" id='$name' ":"";
    $class=!empty($class)?$class:"btn-primary f-white";
    $url = empty($url)?"javascript:;":$url;
    return 	'<a '.$name.'  href="'.$url.'" class="btn  margin10-r '.$class.' btn-xs" >'.$text.'</a>';
}

/**
 * @param  $text
 * @return 提示内容
 */
function we7_lab($text="提示"){
    return '<div style="text-indent:30px;padding:10px 0;">'.$text.'</div>';
}

/**
 * @param  $name
 * @param $v
 * @return 隐藏域
 */
function we7_hidden($name='id',$v){
    return '<input id="'.$name.'" type="hidden" name="'.$name.'" value="'.$v.'">';
}

/**
 * @param  $id
 * @param  $arr []
 * @return 标签选择
 */
function we7_labs($id=0,$arr=[]){
    $arr=empty($arr)?['否','是']:$arr;
    if($id==0){
        $str = "<span class='label label-danger margin10-r'>$arr[0]</span>";
    }elseif($id==1){
        $str = "<span class='label label-success margin10-r'>$arr[1]</span>";
    }elseif($id==2){
        $str = "<span class='label label-primary margin10-r'>$arr[2]</span>";
    }
    return $str;
}

/**
 * @param $t 时间戳
 * @return 格式化时间
 */
function we7_t($t){
    return date("Y-m-d H:i",$t);
}

/**
 * @param $img 图片留空输出默认
 * @param  $type 类型banner长方形其他圆形
 * @return 输出图片预览
 */
function we7_img($img,$type='banner'){
    $type = ($type=="banner")?"max-width: 150px; max-height: 50px;":"width:50px;height: 50px;border-radius: 50%;";
    return empty($img)?"<svg t=\"1566479495178\" class=\"icon\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"4875\" width=\"32\" height=\"32\"><path d=\"M345 289.3c61.5 0 111.3 49.9 111.3 111.4 0 61.5-49.9 111.3-111.3 111.3-61.5 0-111.3-49.8-111.3-111.3-0.1-61.5 49.8-111.4 111.3-111.4zM901.7 679L777.6 513.6c-21.7-29-65-29.6-87.6-1.4L512 734.7l-135.3-90.2c-19-12.7-44-12-62.3 1.7L122.3 790.4v55.7h779.4V679z\" fill=\"#8a8a8a\" p-id=\"4876\"></path><path d=\"M901.7 178v668H122.3V178h779.4m55.7-55.7H66.6v779.4h890.8V122.3z\" fill=\"#8a8a8a\" p-id=\"4877\"></path></svg>":"<img style='$type' class='bigimg' title='点击看大图' src='".tomedia($img)."' layer-src='".tomedia($img)."'>";
}

/**
 * @param $json json字符串
 * @return 多个图片输出
 */
function we7_imgs($json){
    $arr =  json_decode($json,TRUE);
    $str="";
    if(!empty($arr)){
        foreach($arr as $v){
            $str.=we7_img($v)."&nbsp;&nbsp;";
        }
    }
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $desc
 * @return 文件上传
 */
function we7_file($text="文件上传",$name='',$desc=''){
    $str=<<<EOF
  <div class="form-group">
        <label class="col-sm-2 control-label">$text</label>
        <div class="form-controls col-sm-8">
            <input type="file" name="$name"  id="$name"/>
            <div class="help-block">$desc</div>
        </div>
    </div>
EOF;
    return $str;
}

/**
 * @param  $text 标题
 * @param  $name 模态框名称
 * @param  $data 数据内容
 * @return 模态框生成但不能显示
 */
function we7_modal($text="模态框",$name="modal",$data=""){
    $str=<<<EOF
<div class="modal fade modal-form" id="{$name}-id" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="max-height:90%;">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">$text</h4>
            </div>
            <div class="modal-body"  style="padding:15px;">
                <div id="{$name}-data" class="we7-form">
                $data
</div>
            </div>
        </div></div>
</div>
EOF;
    return $str;
}

/**
 * @param  $text
 * @param  $name
 * @param  $url 可以js函数调用
 * @param  $class
 * @return 调用模态框
 */
function we7_a_modal($text="模态框",$name="",$url="",$class=""){
    $class=!empty($class)?$class:"btn-primary f-white";
    $s = empty($url)?"data-toggle=\"modal\" data-target=\"#$name-id\"":"";
    $url = empty($url)?"javascript:;":$url;
    return 	'<a id="'.$name.'"  href="'.$url.'"  '.$s.' class="btn  margin10-r '.$class.' btn-xs" >'.$text.'</a>';
}

/**
 * @param  $do 动作
 * @param  $mod 模块名
 * @param $arr 附件数组参数
 * @return pc端生成网址
 */
function w_url($do="",$mod="yoby_test",$arr=[]){
    $arr1=['m'=>$mod];
    $arr=array_merge($arr1,$arr);
   return wurl('site/entry/'.$do,$arr);
}

/**
 * @param  $do 动作
 * @param  $mod 模块名
 * @param  $arr 附加数组
 * @param  $pre 是否包含微信前缀默认否
 * @param  $host 是否包含主域名默认是
 * @return 移动端生成网址
 */
function m_url($do="fm",$mod="yoby_test",$arr=[],$pre=true,$host=true){
    $arr1=['m'=>$mod];
    $arr=array_merge($arr1,$arr);
   return murl('entry/site/'.$do,$arr,$pre,$host);
}

/**
 * @param  $title 表头用|隔开
 * @param  $data 输出格式数组
 * @return 输出表格
 */
function we7_table($title="",$data=[]){
    $arr=str2arr($title,"|");
    $str="";
    foreach ($arr as $k=>$v){
       $str.="<th>$v</th>";
    }
    $str1="";
    if(!empty($arr)){
        foreach ($data as $k=>$v){
        $str1.="<tr>";
        foreach($v as $v1){
            $str1.="<td>$v1</td>";
        }
        $str1.="</tr>";
        }
    }
    $str2=<<<EOF
 <table class="table we7-table table-hover article-list vertical-middle ">
        <thead class="navbar-inner">
        <tr>{$str}
        </tr>
        </thead>
        <tbody id="layer-photo">
        {$str1}
        </tbody>
    </table>
EOF;
return $str2;
}
