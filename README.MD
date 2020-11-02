### PDO数据库操作

### 安装
`composer require logoove/db`

~~~
use logoove\DB;

通过文件生成数据表,或者通过数据表生成数据库php文件
连接数据库mysql
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
~~~