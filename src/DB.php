<?php
/**
 * 通过文件生成数据表,或者通过数据表生成数据库php文件
 * 连接数据库mysql
 $db = new DB('localhost','root','','3306','test','ims_');
 * 添加表字段生成表
$list = $db->name("userxx")->a("id","int",10,"not null",'null',"主表")
->a("name","varchar",200,'null','null',"姓名")
->a("age","smallint",2,'unsigned not null',0,"年龄")
->a('sex','enum',"男,女,未知",'null',"未知","性别")
->a("money","decimal","10,2",'not null',"0.00","金钱")
->a('creat_at','timestamp')
->a("times","int",10,"not null",0,"时间")
->index("name","names")
->index("name,sex","names1")
->key("id")
->c();
 *删除表字段
$list=$db->name("userxx")->df("i,ii");
 * 添加表字段
$list = $db->name("userxx")
    ->a("name1","varchar",200,'null','null',"姓名")
    ->a("age1","smallint",2,'unsigned not null',0,"年龄")
    ->af();
* 生成批量数据
for($i=1;$i<5;$i++){
    $data=[
        'name'=>$db->string(5,'cn'),
        'age'=>$db->string(2,'number')
    ];
    $db->insert('userxx', $data);
}
 * 查询多条数据
 * $list = $db->query("select * from ".$db->table("userxx")." limit 4");
 * 查询一条数据
 * $list = $db->fetch("select * from ".$db->table("userxx")." where id=?",[88]);
 * 返回单个数据
 * $list = $db->column("select count(*) from ".$db->table("userxx")."");
 * 插入修改删除非查询
 * $db->exec($sql,array())
 * 插入id
 * $db->insertid()
 * 运行多条非查询语句
 * $db->run($sql,表前缀);
 * 字段是否存在
 * $db->isfield($tb,$field)
 * 表是否存在
 * $db->istable($tb)
 * 获取表中所有字段
 * $db->getfield($tb)
 * 检测表是否为空
 * $db->emptytable($tb)
 * 删除表
 * $db->deltabel($tb)
 * 清空表
 * $db->emptytable($tb)
 * 重命名表
 * $db->rename($tb,$newtb)
 *
 */
namespace logoove;
class DB
{    protected $host;
    protected $username;
    protected  $password;
    protected  $post;
    protected $database;
    protected $tablepre;
    protected $db;
    public $op=array();
    protected  $errors=array();

    public function __construct($host='localhost',$username='',$password='',$post='3306',$database='',$tablepre='ims_')
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->post = $post;
        $this->database = $database;
        $this->tablepre=$tablepre;
        $this->db = new PDO("mysql:dbname={$database};host={$host};port={$post};charset=utf8", $username, $password);
        $sql = "SET NAMES 'utf8';";
        $this->db->exec($sql);
        $this->db->exec("SET sql_mode='';");
    }

    /**
     * @param $table
     * @return 返回带前缀表名称
     */
    public function table($table){
       return $this->tablepre .$table;

    }

    /**
     * @param $sql
     * @$params 替换参数支持 ? :name两种方式 pdo原生的
     * @return 查询语句返回多条
     */
    public function query($sql,$params=array()) {
        $statement = $this->db->prepare($sql);
        $result = $statement->execute($params);
        if (!$result) {
            return false;
        } else {
                $result = $statement->fetchAll(pdo::FETCH_ASSOC);
            return $result;
        }
    }

    /**
     * @param $sql
     * @param $params 替换参数
     * @return 查询一条数据
     */
    public function fetch($sql, $params = array()) {

        $statement = $this->db->prepare($sql);
        $result = $statement->execute($params);
        if (!$result) {
            return false;
        } else {
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            return $data;
        }
    }

    /**
     * @param $sql
     * @param $params,数组参数
     * @param $column1 第几列默认从0开始
     * @return 返回数据第一条第N列也可以统计数据条数
     */
    public function column($sql, $params = array(), $column1 =0) {

        $statement = $this->db->prepare($sql);
        $result = $statement->execute($params);
        if (!$result) {
            return false;
        } else {
            $data = $statement->fetchColumn((int)$column1);
            return $data;
        }
    }
    /**
     * @param string $sql
     * @return 执行插入删除更新等非查询命令
     */
    public function exec($sql,$params=array()){
        if (empty($params)) {
            $result = $this->db->exec($sql);
            return $result;
        }
        $statement = $this->db->prepare($sql);
        $result = $statement->execute($params);
        if (!$result) {
            return false;
        } else {
            return $statement->rowCount();
        }
    }

    /**
     * @return 插入id
     */
    public function insertid() {
        return $this->db->lastInsertId();
    }

    /**
     * @return 事务开始
     */
    public function begin() {
        $this->db->beginTransaction();
    }
    /**
     * @return 事务提交
     */
    public function commit() {
        $this->db->commit();
    }
    /**
     * @return 事务回滚
     */
    public function rollback() {
        $this->db->rollBack();
    }

    /**
     * @param $sql 语句
     * @param $stuff 表前缀
     * @return 执行多条sql语句,建表常用
     */
    public function run($sql, $stuff = 'ims_') {
        if(!isset($sql) || empty($sql)) return;

        $sql = str_replace("\r", "\n", str_replace(' ' . $stuff, ' ' . $this->tablepre, $sql));
        $sql = str_replace("\r", "\n", str_replace(' `' . $stuff, ' `' . $this->tablepre, $sql));
        $ret = array();
        $num = 0;
        $sql = preg_replace("/\;[ \f\t\v]+/", ';', $sql);
        foreach(explode(";\n", trim($sql)) as $query) {
            $ret[$num] = '';
            $queries = explode("\n", trim($query));
            foreach($queries as $query) {
                $ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0].$query[1] == '--') ? '' : $query;
            }
            $num++;
        }
        unset($sql);
        foreach($ret as $query) {
            $query = trim($query);
            if($query) {
                $this->exec($query, array());
            }
        }
    }
    /**
     *
     * @param $tablename
     * @param $fieldname
     * @return 检测表中是否存在某个字段
     */
    public function isfield($tablename, $fieldname) {
        $isexists = $this->fetch("DESCRIBE " . $this->table($tablename) . " `{$fieldname}`", array());
        return !empty($isexists) ? true : false;
    }
    /**
     *
     * @param $table
     * @return 检测表是否存在
     */
    public function istable($table) {
        if(!empty($table)) {
            $data = $this->fetch("SHOW TABLES LIKE '{$this->tablepre}{$table}'", array());
            if(!empty($data)) {
                $data = array_values($data);
                $tablename = $this->tablepre . $table;
                if(in_array($tablename, $data)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $table
     * @return 获取表中字段
     */
    public function getfield($table)
    {
        $fields = array();
        $recordset = $this->db->query("SHOW COLUMNS FROM ".$this->table($table));
        $recordset->setFetchMode(PDO::FETCH_ASSOC);
        $result = $recordset->fetchAll();
        foreach ($result as $rows) {
            $fields[] = $rows['Field'];
        }
        return $fields;
    }

    /**
     * @param $tablename
     * @return 检测表是否为空,true不为空
     */
    public function emptytable($tablename) {
        $row = $this->fetch("select * from ".$this->table($tablename)." limit 1");
        if (empty($row) || !is_array($row) || count($row) == 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param $table
     * @return 删除表
     */
    public function deltable($table){
        return $this->exec("drop table if exists ".$this->table($table));
    }

    /**
     * @param $table
     * @return 清空表
     */
    public function cleartable($table){
        return $this->exec("truncate  table ".$this->table($table));
    }

    /**
     * @param $table
     * @param $new
     * @return 重命名表
     */
    public function rename($table,$new){
        return $this->exec("alter table ".$this->table($table)." rename ".$this->table($new));
    }

    /**
     * @param $table 表名称
     * @param $en 引擎InnoDB|MyISAM
     * @return 选择要创建的表
     */
 public function name($table,$en="InnoDB"){
        $this->op['table']=$this->table($table);
        $this->op['en']=$en;
        return $this;
 }

    /**
     * @param  $name 字段英文名
     * @param $type 字段类型int|tinyint|varchar|char|decimal|smallint|mediumint|text|mediumtext|timestamp|enum
     * @param  $len 长度enum时候是选项
     * @param  $isnull 可以NULL NOT NULL UNSIGNED NULL
     * @param  $default 默认值NULL
     * @param  $c 注释
     * @return 添加列
     */
    public function a($name='',$type='',$len="0",$isnull='NULL',$default='NULL',$c=''){
        if(empty($name)){
            exit("字段不能为空");
        }
        if(empty($type)){
            $type="varchar";
        }
        if($type=='enum'&& $default=="NULL"){
            exit("默认值必须有选项");
        }
        //$table=$this->op['table'];
        $arrstr="int|tinyint|varchar|char|decimal|smallint|mediumint|text|mediumtext|timestamp|enum";
        $arr=explode("|",$arrstr);
        if(!in_array($type,$arr)){
            exit("类型必须是".$arrstr."一种");
        }
        switch ($type){
            case "int":
            case "tinyint":
            case "smallint":
            case "mediumint":
                $default=($default=='NULL')?0:$default;
                if($len==0){
                    $len=10;
                }
                $this->op['add'][]=[
                    "name"=>$name,
                    "type"=>$type,
                    "len"=>$len,
                    "isnull"=>$isnull,
                    "default"=>$default,
                    "c"=>$c,
                ];break;
            case "char":
            case "varchar":
                if($len==0){
                    $len=20;
                }
                $this->op['add'][]=[
                    "name"=>$name,
                    "type"=>$type,
                    "len"=>$len,
                    "isnull"=>$isnull,
                    "default"=>$default,
                    "c"=>$c,
                ];break;
            case "decimal":
                $this->op['add'][]=[
                    "name"=>$name,
                    "type"=>$type,
                    "len"=>"10,2",
                    "isnull"=>$isnull,
                    "default"=>$default,
                    "c"=>$c,
                ];break;
            case "text":
            case "mediumtext":
            case "timestamp":
            $this->op['add'][]=[
                "name"=>$name,
                "type"=>$type,
                "len"=>"0",
                "isnull"=>$isnull,
                "default"=>$default,
                "c"=>$c,
            ];break;
            case "enum":
                $lenarr=explode(",",$len);
                $l="";
                foreach ($lenarr as $k=>$v){
                    $l.="'$v',";
                }
               $l= rtrim($l,",");
                $this->op['add'][]=[
                    "name"=>$name,
                    "type"=>$type,
                    "len"=>$l,
                    "isnull"=>$isnull,
                    "default"=>"'$default'",
                    "c"=>$c,
                ];break;
        }

        return $this;

    }

    /**
     * @param  $index 要加入的索引,多个,号隔开
     * @param $name 索引名
     * @return 添加索引可以多次添加不同索引
     */
    public function index($index="",$name){
        $this->op["index"][]=[
            'index'=>$index,
            'name'=>$name
        ];
        return $this;
    }

    /**
     * @param $index 添加主键
     * @return 添加主键只有一个
     */
    public function key($index="id"){
        $this->op["key"]=$index;
return $this;
    }

    /**
     * @return 生成数据表
     */
    public function c(){
        $arr=$this->op;
        $str="";
        if(!isset($arr["key"])){
            exit("主键必须设置");
        }
        $str.="DROP TABLE IF EXISTS ".$arr['table'].";\n";
        $s="";
        $s1="";
        foreach ($arr['add'] as $k=>$v){
            if($v['name']==$arr["key"]){
               $s.=$v['name']." ".$v["type"]."(".$v['len'].") UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '".$v['c']."',\n";
               $s1.="PRIMARY KEY (".$v['name'].") USING BTREE,\n";
            }else{
                    $s.=$v['name']." ".$v["type"]."(".$v['len'].") ".$v['isnull']." DEFAULT ".$v["default"]."  COMMENT '".$v['c']."',\n";
            }
        }
        $in="";
        if(isset($arr['index'])) {
            foreach ($arr['index'] as $kk => $vv) {
                $lenarr=explode(",",$vv['index']);
                $l="";
                foreach ($lenarr as $k=>$v){
                    $l.="`$v`,";
                }
                $l= rtrim($l,",");
                $in .= "UNIQUE INDEX " . $vv['name'] . "(" . $l . ") USING BTREE,\n";
            }
            $in=rtrim($in,",\n");
        }
        if(empty($in)){
    $s1=rtrim($s1,",\n");
        }
        $str.="CREATE TABLE ".$arr['table']." (\n".$s.$s1.$in;
        $str.=")ENGINE = ".$arr['en']."  CHARACTER SET = utf8;";
        $this->exec($str);
        $this->op=array();
        return '';
    }

    /**
     * @param $ff 多个字段逗号隔开
     * @return 删除表字段
     */
public function df($ff){
        $tb=$this->op['table'];
        $arr=explode(",",$ff);
        $sql="";
        foreach($arr as $k=>$v){
$sql="alter table ".$tb." drop ".$v;
$this->exec($sql);
        }
    $this->op=array();
        return '';
}

    /**
     * @return 添加字段
     */
    public function af(){
        $arr=$this->op;
        foreach ($arr['add'] as $k=>$v){
            $sql="alter table ".$arr['table']." add  ".$v['name']." ".$v["type"]."(".$v['len'].") ".$v['isnull']." DEFAULT ".$v["default"]."  COMMENT '".$v['c']."'";
               $this->exec($sql);
        }
        $this->op=array();
        return '';
    }
    /**
     * @param  $len 长度
     * @param  $type 类型number|cn|string
     * @param  $addChars 附加字符串
     * @return 生成随机字符串
     */
 public   function string($len = 6, $type = '', $addChars = '')
    {
        $str = '';
        switch ($type) {
            case 'number':
                $chars = str_repeat('0123456789', 3);
                break;
            case 'cn':
                $chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只休借" . $addChars;
                break;
            default :
                $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
                break;
        }
        if ($len > 10) {
            $chars = str_repeat($chars, 5);
        }
        if ($type != 'cn') {
            $chars = str_shuffle($chars);
            $str = substr($chars, 0, $len);
        } else {
            for ($i = 0; $i < $len; $i++) {
                $str .=mb_substr($chars,floor(mt_rand(0, mb_strlen($chars, 'utf-8') - 1)),1);
            }
        }
        return $str;
    }

    /**
     * @param  $min 最小
     * @param  $max 最大
     * @return 生成价格
     */
    public function price($min='0.01',$max='100.00')
    {

        return sprintf("%.2f",$min + mt_rand() / mt_getrandmax() * ($max - $min));

    }

    /**
     * @param  $is now表示现在时间戳
     * @return 生成随即时间戳
     */
    public function timestamp($is=''){
        $begin = strtotime("1970-12-12 12:12:12");
        $end = time();
        if($is=='now'){
            return $end;
        }else{
            return $timestamp = rand($begin, $end);
        }

    }

    /**
     * @return 随机邮箱
     */
    public function email()
    {

        $data = array(
            '@qq.com','@163.com','@126.com','@sina.com.cn','@139.com','@hotmail.com','@gmail.com','@yahoo.com'
        );

        $domain = $data[mt_rand(0, count($data) - 1)];

        if($domain == '@qq.com'){

            return self::number('10000-1000000000') . $domain;

        }
        return self::string(6) . $domain;
    }
    public  function mobile()
    {

        $data = array(
            130,131,132,133,134,135,136,137,138,139,144,147,150,151,152,153,155,156,157,158,159,176,177,178,180,181,182,183,184,185,186,187,188,189,
        );

        $prefix = $data[mt_rand(0, count($data) - 1)];

        return $prefix . self::string(8,"number");

    }

    /**
     * @param  $width
     * @param  $height
     * @return 随机图片
     */
    public function image($width=640,$height=480)
    {
        $baseUrl = "http://temp.im/";
        return $baseUrl.$width."x".$height;
    }

    /**
     * @return 随机地址
     */
    public function address(){
        $city = [ '北京', '上海', '天津', '重庆',
            '哈尔滨', '长春', '沈阳', '呼和浩特',
            '石家庄', '乌鲁木齐', '兰州', '西宁',
            '西安', '银川', '郑州', '济南',
            '太原', '合肥', '武汉', '长沙',
            '南京', '成都', '贵阳', '昆明',
            '南宁', '拉萨', '杭州', '南昌',
            '广州', '福州', '海口',
            '香港', '澳门'];
        $area = [
            '西夏区', '永川区', '秀英区', '高港区',
            '清城区', '兴山区', '锡山区', '清河区',
            '龙潭区', '华龙区', '海陵区', '滨城区',
            '东丽区', '高坪区', '沙湾区', '平山区',
            '城北区', '海港区', '沙市区', '双滦区',
            '长寿区', '山亭区', '南湖区', '浔阳区',
            '南长区', '友好区', '安次区', '翔安区',
            '沈河区', '魏都区', '西峰区', '萧山区',
            '金平区', '沈北新区', '孝南区', '上街区',
            '城东区', '牧野区', '大东区', '白云区',
            '花溪区', '吉利区', '新城区', '怀柔区',
            '六枝特区', '涪城区', '清浦区', '南溪区',
            '淄川区', '高明区', '金水区', '中原区',
            '高新开发区', '经济开发新区', '新区'
        ];
        $key1 = array_rand($city, 1);
        $key2 = array_rand($area, 1);
        return $city[$key1].$area[$key2];
    }

    /**
     * @param  $n 数量
     * @return 随机emoji
     */
    public function emoji($n=1)
    {
        $emoji = array(
            '\uD83D\uDE00', '\uD83D\uDE01', '\uD83D\uDE02', '\uD83D\uDE03',
            '\uD83D\uDE04', '\uD83D\uDE05', '\uD83D\uDE06', '\uD83D\uDE07',
            '\uD83D\uDE08', '\uD83D\uDE09', '\uD83D\uDE0A', '\uD83D\uDE0B',
            '\uD83D\uDE0C', '\uD83D\uDE0D', '\uD83D\uDE0E', '\uD83D\uDE0F',
            '\uD83D\uDE10', '\uD83D\uDE11', '\uD83D\uDE12', '\uD83D\uDE13',
            '\uD83D\uDE14', '\uD83D\uDE15', '\uD83D\uDE16', '\uD83D\uDE17',
            '\uD83D\uDE18', '\uD83D\uDE19', '\uD83D\uDE1A', '\uD83D\uDE1B',
            '\uD83D\uDE1C', '\uD83D\uDE1D', '\uD83D\uDE1E', '\uD83D\uDE1F',
            '\uD83D\uDE20', '\uD83D\uDE21', '\uD83D\uDE22', '\uD83D\uDE23',
            '\uD83D\uDE24', '\uD83D\uDE25', '\uD83D\uDE26', '\uD83D\uDE27',
            '\uD83D\uDE28', '\uD83D\uDE29', '\uD83D\uDE2A', '\uD83D\uDE2B',
            '\uD83D\uDE2C', '\uD83D\uDE2D', '\uD83D\uDE2E', '\uD83D\uDE2F',
            '\uD83D\uDE30', '\uD83D\uDE31', '\uD83D\uDE32', '\uD83D\uDE33',
            '\uD83D\uDE34', '\uD83D\uDE35', '\uD83D\uDE36', '\uD83D\uDE37',
        );
        $s="";
        for($i=1;$i<=$n;$i++){
            $k = array_rand($emoji, 1);
            $s.=  json_decode('"' . $emoji[$k] . '"');
        }

        return $s;
    }

    /**
     * @param 单词长度
     * @return 生成标题
     */
    public function title($n=8){
        $word=function(){
            $arr =  [
                'a', 'b', 'c', 'd', 'e', 'f', 'g',
                'h', 'i', 'j', 'k', 'l', 'm', 'n',
                'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
            ];
            $n=   mt_rand(3,8);
            $word="";
            while ($n--) {
                $index = mt_rand(0, 25);
                $word .= $arr[$index];
            }
            return $word;
        };
        $str="";
        for($i=0;$i<$n;$i++){
            $str.=$word()." ";
        }
        $str = rtrim($str," ");
        $str = ucfirst($str);
        return $str;
    }

    /**
     * @param $tb 表名称
     * @param  $data 生成数据数组
     * @return 插入数据用于批量生成演示数据
     */
    public function insert($tb,$data=array()){
        $sql="";
            $k=array_keys($data);
            $k=implode(',',$k);
            $v=array_values($data);
            $str="";
            foreach($v as $vv){
                $str.="\"$vv\",";
            }
           $str= rtrim($str,",");
            $sql="insert into ".$this->table($tb)."($k) values($str)";
          $t=  $this->exec($sql);
        return $t;
    }
}