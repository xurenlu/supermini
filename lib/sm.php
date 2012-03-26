<?php
/**   
 *@file sm.php 主框架内容;
 * vim: set fdm=marker:
 *@author xurenlu <helloasp@hotmail.com>
 *@version 1.2.0 
 *\b License:  \b MIT <http://en.wikipedia.org/wiki/MIT_License>
 <pre>
 *@last_modified 2012-03-27 01:44:37
 \b Homepage: http://www.162cm.com/ 
 \b Slide: http://codeany.com/slides.10.play.miniphpkuangjiasuperminijianjie.shtml
 </pre>
 \b 使用之前要理解并同意的几个关键点
 1.Mysql已经相当好用了，所以，这个框架只支持用mysql做数据库。没有设计一大堆的DBDriver;
 2.做cache,Memcache足够好用了，因此,内置的一些cache支持是基于memcache的;
 3.只用最好的，最必需的,精简精简再精简~
</pre>
*/
/**  smPhpEvent 	一些跟事件触发相关的全局函数. */
global $SM_PE_EVENTS,$SM_PE_FILTERS,$sm_config,$sm_temp,$sm_data;
$SM_PE_FILTERS=array();
$SM_PE_EVENTS=array();
/**
 *	执行某一事件.
 * @param $event String 事件的名称
 * @param  $args MISC 要传递给事件的参数;
 */
function smDoEvent($event,$args=null){
    global $SM_PE_EVENTS;
    if(is_array($SM_PE_EVENTS[$event]))
        foreach($SM_PE_EVENTS[$event] as $handle)
        {
            if(function_exists($handle))
                $handle($args);
        }
}
/**
 *	加入一个事件处理器
 *@name		smAddEvent
 *@param	$event	string	"event name"
 *@param	$handle	string	"event handle" must be an exists function name
 *@return nothing
 */
function smAddEvent($event,$handle){
    global $SM_PE_EVENTS;
    $SM_PE_EVENTS[$event][]=$handle;
}
/**
 *	给数据加一个过滤器.
 *@name	smAddFilter
 *@param	$dataName	string	数据名
 *@param	$filterName	string 过滤器名
 *@return nothing
 */
function smAddFilter($dataName,$filterName){
    global $SM_PE_FILTERS;
    $SM_PE_FILTERS[$dataName][]=$filterName;
}
/**
 *	给数据应用过滤器.
 *@name	smApplyEvent
 *@param	$data	string	数据
 *@param	$dataName	string	数据名
 *@return nothing
 */
function  smApplyEvent(&$data,$dataName) {
    global $SM_PE_FILTERS;
    if(is_array($SM_PE_FILTERS[$dataName]))
        foreach($SM_PE_FILTERS[$dataName] as $filter)
        {
            if(function_exists($filter))
                $filter(  $data);
        }
}
/** generate URL by $_GET arguments */
function sm_url($args,$string=""){
    global $sm_temp;
    if(!$sm_temp["use_shorturl"]){
        if(!$args["controller"])
            $args["controller"]=$sm_temp["controller"];
        if(!$args["action"])
            $args["action"]=$sm_temp["action"];
        $arr=array();
        foreach($args as $key=>$val){
            if(!sm_test_urlencode($val)){
                $val=urlencode($val);
            }
            $arr[]=$key."=".$val;
        }
        $pagestr="?".join("&",$arr);
        return $pagestr;
    }
    if(!isset($args["controller"]))
        $args["controller"]=$sm_temp["controller"];
    if(!isset($args["action"]))
        $args["action"]=$sm_temp["action"];
    $keys=array_keys($args);
    sort($keys);
    foreach($sm_temp["compiled_url_routes"] as $k=>$v){
        sort($v["fields"]);
        if($keys==$v["fields"]){
            $pattern=$v["pat"];
            break;
        }
    }
    if(!isset($pattern)){
        $controller = $args["controller"];
        $action = $args["action"];
        unset($args["controller"]);
        unset($args["action"]);
        foreach($args as $key=>$val){
            if(!sm_test_urlencode($val)){
                $val=urlencode($val);
            }
            $arr[]=$key."=".$val;
        }
        $string =sm_url(array("controller"=>$controller,"action"=>$action))."?".join("&",$arr);
        return $string;
    }
    else{
        $string = $pattern;
	    foreach($args as $k=>$v){
	        $string=str_replace("{".$k."}",$v,$string);
        }	
        $string=sm_urlmap($string,2);
        $left2=substr($string,0,2);
        if($left2=="//")
            $string = substr($string ,1);
		return $string;
	}
}
/**  sm_test_urlencode * 探测一个变量是否已经被urlencode过了。 */
function sm_test_urlencode($var){
    return    (urldecode($var)==$var)?false:true;
}
/**  sm_pagenav_default 分页函数 ，
 * 
 * @param int $total 总记录个数
 * @param int $pagesize 每页记录数
 * @param string $pagestr 其他分页的链接模板
 * @param array $get	一般情况下就是GET数组
 * @param string $page_var_name 一般是page
 * @param int $l 	当前页链接的左边保留多少个链接
 * @param int $r 	当前页链接的右边保留多少个链接
 *
 * @code
 * echo sm_pagenav_default(18332,20);
 * echo sm_pagenav_default(18244,25,"index.php?page={page}",array("key"=>1),"page",3,3);
 * echo sm_pagenav_default(18244,25,null,array("key"=>1),"page",3,3);
 * @endcode
 */
function sm_pagenav_default($total,$pagesize=null,$pagestr=null,$get_args=null,$page_var_name="page",$l=4,$r=4){
        global $sm_temp;
        $url_pattern=$sm_temp["url_pattern"];
        if(is_null($pagestr)){
            $arr=array();
            if(is_null($get_args))
                $get_args=$_GET;
            while(list($key,$val)=each($get_args)){
                if(!sm_test_urlencode($val))
                    $val=urlencode($val);
    
                if(strtolower($key)!=$page_var_name)
                    $arr[]=$key."=".$val;
            }
            $arr[]=$page_var_name."={page}";
            $pagestr="?".join("&",$arr);
        }
        $get_args[$page_var_name]="{page}";
        unset($get_args["use_layout"]);
        if(is_null($pagesize)){
            global $sm_config;
            $pagesize=$sm_config["pagesize"]>0?$sm_config["pagesize"]:20;
        }
        $pagecount=$total/$pagesize;
        if(floor($pagecount)<$pagecount)
            $pagecount= floor($pagecount)+1;
        if(! ($_GET[$page_var_name]>0)){
            $_GET[$page_var_name]=1;
            $pagenow=1;
        }
        else
            $pagenow=$_GET[$page_var_name];
    
        $sn="page_".rand(1000,9999);
        if ($pagenow>1){
            $get_args[$page_var_name]=1;
            $str=$str."<a href='".sm_url($get_args)."' class='prev'><span class='pagenum'>首页</span></a>";
            $get_args[$page_var_name]=$pagenow-1;
            $str =$str."<a href='".sm_url($get_args)."' class='prev'><span class='pagenum'> 上一页</span></a>";
        }else{
            $str=$str."<a  class='prev' title='已经是第一页了'><span class='pagenum'>首页</span></a>";
            $str =$str."<a class='prev' title='已经是第一页了'><span class='pagenum'> 上一页</span></a>";
        }
        $startpage=$pagenow-$l;
        $endpage=$pagenow+$r;
        if($startpage<1) $startpage=1;
        if($endpage>=$pagecount) $endpage=$pagecount;
        for($jj=$startpage;$jj<=$endpage;$jj++){
            if($jj==$pagenow)
                $str=$str."<a class='on' href=".sm_url($get_args)."><span class='current pagenum'>".$jj."</span></a>";
            else{
                $get_args[$page_var_name]=$jj;
                $str=$str."<a href='".sm_url($get_args)."'><span>".$jj."</span></a>";
            }
        }
        if($pagenow<$pagecount){
            $get_args[$page_var_name]=$pagenow+1;
            $str=$str."<a href='".sm_url($get_args)."' class='next'><span class=''>下一页</span></a>";
            $get_args[$page_var_name]=$pagecount;
            $str=$str."<a href='".sm_url($get_args)."' class='next'><span class=''>尾页</span></a>";
          
        }else{
            $str=$str."<a  class='next' title='已经是最后一页了'><span class=''>下一页</span></a>";
            $str=$str."<a class='next' title='已经是最后一页了'><span class=''>尾页</span></a>";
        }
        return $str;
    }
    /**  static class smSql  帮助构造SQL语句的小工具类; */
class smSql{
    var $pagesize=20;
    static function escape_string($v){
        return mysql_escape_string($v);
    }
    /**  update  构造更新SQL
     *@param  $table string 要更新的数据表名
     *@param  $array array要更新的数据
     *@param  $condition string更新条件
     *@param $limit integer 更新的条数;
     */
    static function update($table, $array, $condition,$limit=1){   
        if(is_array($array)){
            $sql = "UPDATE `".$table."` SET ";
            $comma = ""; 
            foreach ($array AS $_key => $_val){
                $sql .= $comma."`".$_key."` = '".self::escape_string($_val)."'";
                $comma = ", ";
            }
            if ($condition){
                $sql .= " WHERE ".$condition;
            }
            $sql .= " LIMIT $limit";
            return $sql;
        }
        else{
            return false;
        }
    }
    /**  select 构造查询SQL
     *
     * @param  $table string表名字
     * @param  $columns string 要查找的列，默认是"*"
     * @param  $conditions string ,默认是null,请给出sql语句 where子句where后面的部分
     * @param  $order string 
     * @param  $limit string
     * @param  $group string
     */
    static function select($table,$columns="*",$conditions=null,$order=null,$limit=null,$group=null,$join=null,$on=null){
        if($columns===NULL)
            $columns="*";
        if(is_array($columns))
            $cols=join(",",$columns);
        else
            $cols=$columns;
        $sql="	SELECT ".$columns." FROM ".$table;
        if(!is_null($join)){
            $sql.=" $join ";
        }
        if(!is_null($on)){
            $sql.="on  $on";
        }
        if(!is_null($conditions))
            $sql.="	WHERE ".$conditions;
        if(!is_null($group))
            $sql.="	GROUP BY ".$group;
        if(!is_null($order))
            $sql.="	ORDER BY ".$order;
        if(!is_null($limit))
            $sql.="	LIMIT ".$limit;
        return $sql;
    } 
    /***   count 构造count类语句,注意构造出的是count(*) as c ;*/ 
    static function count($table,$conditions=null,$order=null,$limit=null,$group=null,$join=null,$on){
        return self::SELECT($table,"count(*) as c",$conditions,$order,$limit,$group,$join,$on); 
    }
    /**  insert 得到插入语句的SQL
     *@param  $table string 数据表名字
     *@param  $array array 要插入的一行数据
     *@param $type 要么是INSERT要么是REPLACE,这决定生成的sql语句是insert into 还是replace into 
     **/
    static function insert($table,$array,$type="INSERT"){
        if (is_array($array)){	
            $comma = $key = $value = "";
            foreach ($array AS $_key => $_val){	
                $key .= $comma."`".$_key."` ";
                $value .= $comma."'".self::escape_string($_val)."'";	
                $comma = ", ";
            }
            $sql = "$type INTO ".$table.  "(".$key.") VALUES (".$value.")";
            return $sql;
        }
    }
    /**  delete  得到删除语句的SQL
     * @param  $table string 数据表名字
     * @param  $condition string 条件
     * @param  $limit string limit字段
     */
    static function delete($table,$condition,$limit="1"){
        $sql="DELETE FROM `".mysql_escape_string($table)."` WHERE ".$condition." LIMIT ".$limit;
        return $sql;
    }
}
/***   _sm_mysql  连接Mysql的实际函数 */ 
function _sm_mysql($id){
    global $sm_config; 
    if(!is_array($sm_config["mysql"][$id])){
        throw new smException("MYSQL configuration 'sm_config[\"mysql\"][$id]' not exists");
    }
    $config=$sm_config["mysql"][$id];
    $conn=mysql_connect($config["host"],$config["user"],$config["password"]);
    $switch=mysql_select_db($config["database"],$conn);
    smDoEvent("select_db",$conn);
    if(!is_resource($conn) || !$switch){
        throw new smException("Mysql error:Can't connect to hosts with : -h ".$config["host"]." -u ".$config["user"]." -p ".substr($config["password"],0,2)."*** ".$config["database"]);
    }
    if(!empty($sm_config["prepare_sql"])) sm_query($sm_config["prepare_sql"]."",$conn);
    return $conn;
}
/***   sm_dbo 返回一个连接对象
 * @param integer $id 在sm_config里的mysql相关配置索引;*/ 
function sm_dbo($id=0){
    global $sm_config,$sm_temp;
    return $sm_temp["connections"][$id]=is_resource($sm_temp["connections"][$id])?$sm_temp["connections"][$id]:_sm_mysql($id);
}
/***   sm_query 执行一条sql查询并返回结果 */ 
function sm_query($sql,$conn=null){
    global $sm_config,$sm_temp;
    $sm_temp["sqls"][]=$sql;
    if($sm_config["sql_debug"]){
        error_log($sql);
    }
    smDoEvent("before_query",$sql);
    $ret=is_null($conn)?mysql_query($sql):mysql_query($sql,$conn);//不指定conn时,mysql会调用默认连接
    if(!$ret){
        if(is_null($conn)){
            smDoEvent("query_fail",array("sql"=>$sql,"error_no"=>mysql_error()));
            throw new smException("mysql error:sql:$sql,error descrption:".mysql_errno().":".str_replace("\n","",mysql_error()));
        }
        else{
            smDoEvent("query_fail",array("sql"=>$sql,"error_no"=>mysql_error($conn)));
            throw new smException("mysql error:sql:$sql,error descrption:".mysql_errno($conn).":".str_replace("\n","",mysql_error($conn)));
        }
    }else{
        smDoEvent("query_succeed",array("sql"=>$sql,"resource"=>$ret));
    }
    smDoEvent("after_query",$sql);
    return $ret;
}
/**  sm_fetch_row 取出sql查询的一条结果 */
function sm_fetch_row($sql,$conn=null){
    global $sm_config,$sm_temp;
    $rs=sm_query($sql,$conn);
    $sm_temp["last_rs"]=$rs;
    return empty($rs)? null:mysql_fetch_assoc($rs);
}

/*!   sm_fetch_rows 取出sql查询的多条结果 */ 
function sm_fetch_rows($sql,$conn=null,$type=MYSQL_ASSOC){
    global $sm_config,$sm_temp;
    $rs=sm_query($sql,$conn);
    $sm_temp["last_rs"]=$rs;
    if(!empty($rs)){
        $rows=array();
        while($row=mysql_fetch_array($rs,$type)){
            $rows[]=$row;
        }
        return $rows;
    }
}
function sm_free_result(){
    global $sm_config,$sm_temp;
    if($sm_temp["rs"])
        mysql_free_result($sm_temp["rs"]);
}
/**  smObject class
 * 实现一个比较灵活的功能,调用它的属性时，会自动地创建数据库，创建缓存对象等.
 **/
class smObject {
    /** 数据表的默认主键 */
    public $default_primary_key = "id";
    /**  存放回调钩子 */
    public $callbacks=array();
    /** 设置对某个属性调用时触发的钩子 */
    public function set_callback($name,$callback){
        $this->callbacks[$name]=$callback;
    }
    /***   __get 
     * the main magic method
     */ 
    public function __get($name){
        global $sm_config,$sm_temp,$sm_data;
        if(!empty($sm_config[$name]))
            return $sm_config[$name];
        if(!empty($sm_temp[$name]))
            return $sm_temp[$name];
        if(!empty($sm_data[$name]))
            return $sm_data[$name];
        if($this->callbacks[$name]){
            $sm_temp[$name]=$this->callbacks[$name]();
            return $sm_temp[$name];
        }
        if($name=="db"){
            $sm_temp[$name]=new smDB();  
            $sm_temp[$name]->prepare_dbo();
            return $sm_temp[$name];
        }
        if($name=="form"){
            $sm_temp[$name]=new smForm();  
            return $sm_temp[$name];
        }
        if(preg_match("/^get_(.*)+$/",$name)){
            $id = substr($name,4,strlen($name));
            $sm_temp["get_".$id]=$_GET[$id];
            return $sm_temp["get_$id"];
        }
        if(preg_match("/^env_(.*)+$/",$name)){
            $id = substr($name,4,strlen($name));
            $sm_temp["env_".$id]=$_SERVER[$id];
            return $sm_temp["env_$id"];
        }
        if(preg_match("/^post_(.*)+$/",$name)){
            $id = substr($name,5,strlen($name));
            $sm_temp["post_".$id]=$_POST[$id];
            return $sm_temp["post_$id"];
        }
        if(preg_match("/^dbo_(.*)+$/",$name)){
            $id = substr($name,4,strlen($name));
            $sm_temp["dbo_".$id]=sm_dbo($id);
            return $sm_temp["dbo_$id"];
        }
        if(preg_match("/^cache_(.*)$/",$name)){
            $cache_group = substr($name,6,strlen($name));
            $cache = new smCache($cache_group);
            $sm_temp[$name]=$cache;
            return $cache;
        }
    }
    /**
     * 1. close mysql links;
     * */
    function __destruct(){
        global $sm_temp;
        if($sm_temp["connections"])
        foreach($sm_temp["connections"] as $conn){
            mysql_close($conn);
        }  
    }
}
/** class smException ,就是一个空类，继承了exception */
class smException  extends Exception{}
/** Chainable 是一个比较特别的类,是基本上所有的方法返回的都是对象自己本身;
 * 主要是使用就是设置一个属性;
 * 调用法是:$smChainable->attribute_name(attribute_value);
 **/
class smChainable {
    var $attrs=array();
    function set($name,$value){
        $this->attrs[$name]=$value;
        return $this;
    }
    function __call($name,$args){
        $this->set($name,$args[0]);
        return $this;
    }
    /** 删除所有附加的属性;*/
    function reset(){
        $this->attrs=array();
    }
}
/** class smCache 调用memcache 取缓存;*/
class smCache { 
    private $_servers;
    private $_memcache;
    public $attrs=array("expire"=>7200,"flag"=>0);
    /***   __construct */ 
    function __construct($group_id){
        global $sm_config;
        if(defined("SAE_MYSQL_USER")){
            $mem=memcache_init();
        }else{
            $mem=new memcache();
            foreach($sm_config["memcache"][$group_id] as $server){
                $mem->addServer($server["host"],$server["port"]);
            }
        }
        $this->_memcache=$mem;
    }
    /** *  get 读缓存值 */
    function get($key){
        return $this->_memcache->get($key);
    }
    /***  set 设置缓存值;*/
    function set($key,$val,$expire=7200){
        return  $this->_memcache->set($key,$val,$this->attrs["flag"],$this->attrs["expire"]);
    }
    /** 删除memcache key */
    function delete($key){
        return $this->_memcache->delete($key);
    }
	function __destruct(){
		$this->_memcache->close();
	}
}
/**
 * smDB 是数据操作类
*/
class smDB extends smChainable {
    protected $_rconn=null;
    protected $_wconn=null;
    protected $_pagesize=null;
    protected $_page_var = "page";
    var $_extra_args = null;
    var $_pagestr = null;
    /**
     * 重置属性(主要是重置查询条件)
     * */
    function reset(){
        $this->attrs= array("where"=>null,"group"=>null,"order"=>null,"limit"=>null,"select"=>null);
    	return 1;
	}
    /**
     * 预备数据库连接;
     * */
    function prepare_dbo(){
        if(!$this->_rconn){ 
            if($this->attrs["rconn"])
                $this->_rconn=$this->attrs["rconn"];
            else
                $this->_rconn=sm_dbo(1);
        }
        if(!$this->_wconn){
            if($this->attrs["wconn"])
                $this->_wconn=$this->attrs["wconn"];
            else
                $this->_wconn=sm_dbo(0);
        }
        return $this;
    }
    function __construct(){
        global $sm_config;
        $this->_pagesize=($sm_config["pagesize"]>0)?$sm_config["pagesize"]:20;
        $this->reset();
    }
    /** 返回符合条件的若干行 */
    function rows($clear=true){
	   if($this->attrs["cache_key"]&&($tmp=$sm->cache_group_1->get($this->attrs["cache_key"]))){
			if(!empty($tmp))
				return $tmp;
       }
	   $sql=smSql::select($this->attrs["table"],$this->attrs["select"],$this->attrs["where"],$this->attrs["order_by"],$this->attrs["limit"],$this->attrs["group_by"],$this->attrs["join"],$this->attrs["on"]);
        $rows=sm_fetch_rows($sql,$this->_rconn);
		if($this->attrs["cache_key"])
			$sm->cache_group_1->set($this->attrs["cache_key"],$rows);
        if($clear)
            $this->reset();
        return $rows; 
    }
    /**  查询一条记录 **/
    function row($clear=true){
	   if($this->attrs["cache_key"]&&($tmp=$sm->cache_group_1->get($this->attrs["cache_key"]))){
			if(!empty($tmp))
				return $tmp;
		}
        $sql=smSql::select($this->attrs["table"],$this->attrs["select"],$this->attrs["where"],$this->attrs["order_by"],$this->attrs["limit"],$this->attrs["group_by"],$this->attrs["join"],$this->attrs["on"]);
        $row=sm_fetch_row($sql,$this->_rconn);
		if($this->attrs["cache_key"])
			$sm->cache_group_1->set($this->attrs["cache_key"],$row);
        if($clear)
            $this->reset();
        return $row; 
    }
    /** 查询出一页的数据,并自动处理分页 */
    function page($clear=true){
        if(!($_GET[$this->_page_var]>0))
            $pagenow=1;
        else
            $pagenow=$_GET[$this->_page_var];
        $limit=($pagenow-1)*$this->_pagesize.",".$this->_pagesize;
        $this->limit($limit);
        $rows=$this->rows(false);
        $total=$this->count(false);
        $pagestr = sm_pagenav_default($total,$this->_pagesize,$this->_pagestr,$this->_extra_args,$this->_page_var,3,3);
        $this->reset();
        return array("total"=>$total,"entries"=>$rows,"page"=>$pagestr);
    }
    /** 查询符合条件的记录的行数 */
    function count($clear=true){
        $sql=smSql::select($this->attrs["table"],"count(*) as c",$this->attrs["where"],$this->attrs["order_by"],"1",$this->attrs["group_by"],$this->attrs["join"],$this->attrs["on"]);
        $row=sm_fetch_row($sql,$this->_rconn);
		if($clear)
	    	$this->reset();
        return $row["c"]; 
    }
 	/** get last inserted id */
    function insert_id(){
        return mysql_insert_id($this->_wconn); 
    }
    /** 取得受影响的行数 */
    function affected_rows(){
        return mysql_affected_rows($this->_wconn);
    }
	/***  update_by 根据条件更新数据;*/ 
    function update($clear=true){
        if(!$this->attrs["limit"])
            $this->set("limit",1);
        $sql=smSql::update($this->attrs["table"],$this->attrs["values"],$this->attrs["where"],$this->attrs["limit"]);
		if($clear)
	    	$this->reset();	
        return sm_query($sql,$this->_wconn); 
    }
    /** 删除符合条件的记录 */
    function delete($clear=true){
        if(!$this->attrs["limit"])
            $this->set("limit",1);
        $sql=smSql::delete($this->attrs["table"],$this->attrs["where"],$this->attrs["limit"]);
		  if($clear)
	            $this->reset();
        return sm_query($sql,$this->_wconn);
    }
    /** smDB::delete 的别名 */
    function remove($clear=true){
        return $this->delete($clear);
    }
    /** 插入一条记录 */
	function insert($type="INSERT",$clear=true){
		$sql=smSql::insert($this->attrs["table"],$this->attrs["values"],$type);
		  if($clear)
	            $this->reset();
        return sm_query($sql,$this->_wconn);
    }
    /** smDB::Create的别名 */
    function create($type="INSERT",$clear=true){
        return $this->insert($type,$clear);
    }
    /** 直接执行一条sql语句 */
    function query($sql){
        return sm_query($sql,$this->_wconn);
    }
}
/**
 * @brief	smApplication Mvc 功能主要在这里实现
 * 
 * 这里只实现特别简单的MVC功能,一般建议用户将app目录设置为./app/,controller文件就旅行团在app/***.php里，
 * 而views层的文件则放在./app/views/{controller}/{action}.php里。
 */
class smApplication{
    public $_app="smapplication";
    public $_name="smapplication";
    public $_last_action="index";
    public $before_filters=array();
    public $after_filters=array();
    function __construct($name="smapplication"){
        global $sm_config;
        $this->_name=$name;
        if(!class_exists($name)){
            include_once($sm_config["app_root"]."/app/".strtolower($name).".php");
            $this->_app = new $name($name);
        }else{
            $this->_app=$this;
        }
    }
    /***   _before_filter */ 
    function _before_filter($action){
        $var_name = "before_filters_$action";
        foreach($this->$var_name as $method){
            $this->$method();
        }
        foreach($this->before_filters as $method){
            $this->$method();
        }
    }
    /***  _after_filter      */ 
    function _after_filter($action){
        $var_name = "after_filters_$action";
        foreach($this->$var_name as $method){
            $this->$method();
        }
        foreach($this->after_filters as $method){
            $this->$method();
        }
    }
    /***   method_miss */ 
    private function _method_missing($method) {
        throw new smException("method  missing:".$this->_name."->".$method);
    }
    /***  v include the view files;*/
    function v($action=null,$template_type="php"){
        global $sm_config,$sm_temp;
        $sm_temp["template_type"]=$template_type;
        if(is_null($action))
            $action=$this->_last_action;
        else
            $this->_last_action=$action;
        $mod=$this->_name;
        unset($_GET["use_layout"]);
        if($sm_config["use_layout"]){
            //如果使用布局并且布局文件存在...
            if (is_file($sm_config["app_root"] . "/app/layouts/$mod.php"))
                return include $sm_config["app_root"] . "/app/layouts/$mod.php";
            elseif (is_file($sm_config["app_root"] . "/app/layouts/application.php"))
                return include $sm_config["app_root"] . "/app/layouts/application.php";
            elseif (is_file($sm_config["app_root"] . "/app/views/$mod/$action.php"))
                return include $sm_config["app_root"] . "/app/views/$mod/$action.php";
            else
                return '';
        }
        else{
            return include($sm_config["app_root"]."/app/views/$mod/$action.php");
        }
    }
    /***   dispatch run the filters and real action method;*/ 
    public function dispatch($action){
        global $sm_config;
        $this->_last_action =$this->_app->_last_action= $action;
        $methods=get_class_methods($this->_app);
        if(
            (in_array($action,$methods) && ($method=$action)) ||
            ( in_array("action_".$action,$methods) &&($method="action_".$action))){
                $this->_app->_before_filter($action);
                $this->_app->$method();
                $this->_app->_after_filter($action);
                return true;
            }
        $this->_app->_method_missing($action); // 调用method_missing方法; 
        return false;
    }
    function redirect($args,$action=null,$controller=null){
        if($action === NULL) $action=$this->_last_action;
        if($controller === NULL)  $controller = $_GET["controller"];
        if(!$args["action"])
        $args["action"]=$action;
        if(!$args["controller"])
        $args["controller"]=$controller;
        header("Location:".sm_url($args));
        exit();
    }
    public function yield(){
        global $sm_config,$sm_temp;
        if($sm_temp["template_type"]=="html")
        return include sm_template($sm_config["app_root"]."/app/views/".$this->_name."/".$this->_last_action.".html");
        else 
        return include($sm_config["app_root"]."/app/views/".$this->_name."/".$this->_last_action.".php");
    }
    /***  establish_connect 建立默认连接,默认情况下读写用同一个链接; */
    public function establish_connect(){
        global $sm;
        $this->_rconn=$sm->dbo_1;
        $this->_wconn=$sm->dbo_0;
    }
    /***  __get magic method;*/
    public function __get($var){
        return array();
    }
}
/**  class Form ,旨在减化生成表单的一些操作;**/
class smForm extends smChainable{
    private $_form_values=array();
    private $_form_name="";
    function reset(){
        $this->attrs=array();
        return true;
    }
    function html($tag,$inner=""){
        $str="<$tag ";
        foreach($this->attrs as $k=>$v){
            if($v!==NULL)
                $str .= "$k=\"".htmlspecialchars($v)."\" "; 
        }
        $this->reset();
        if(in_array($tag,array( "input","button","img","link")))
        $html=$str."/>"; 
        else 
            $html=$str.">".$inner."</$tag>"; 
        $this->reset();
        return $html;
    }
    /** 设定form表单的名称和初始值;
     *
     * @param $name String 一般设置为表的名字;
     * @param $values Array 各个域的值;
     **/
    function __construct($name="",$values=null){
        $this->form($name,$values);
    }
    function form($name,$values=null){
        $this->_form_name=$name;
        $this->_form_values=$values;
        return $this;
    }
    /** Form表单的<form action=** method="***">部分
     *
     * @param $action String,Form表单的提交地址。
     * @param $html_attrs Array,Form表单附加的其他属性;
     */
    function openform($action,$html_attrs=array("method"=>"POST"),$upload=false){
        if($upload){
            $html_attrs["enctype"]="multipart/form-data";
        }
        $str="<form  action='$action' ";
        foreach($html_attrs as $k=>$v){
            $str.=" $k='".$v."' ";
        }
        $str.= ">";
        return $str;
    }
    function closeform(){
        return "</form>";
    }
    function caption($field_name,$caption=null){
        if($caption===NULL)
            $caption=$field_name.":";
        $this->set("for", $this->_form_name."_".$field_name);
        $html=$this->build("label","$field_name",$caption);
        $this->reset();
        return $html;
    }
    /** 输出一个textarea 标记;
     *
     * @param $field_name String 域名字;
     */
    function textarea($field_name){
        $value=$this->_get_value($field_name,$this->attrs);
        $html= $this->_left("textarea",$field_name,$this->attrs);
        $html.=">".htmlspecialchars($value)."</textarea>";
        $this->reset();
        return $html;
    }
    /** 输出一个文本输入框 */
    function textbox($field_name){
        $value=$this->_get_value($field_name,$this->attrs);
        $this->set("value",$value);
        $html=$this->build("input",$field_name,$this->attrs);
        $this->reset();
        return $html;
    }
    /** 属出一个checkbox */
    function checkbox($field_name){
        if(!isset($this->attrs["value"]))
            throw new smException("check_box must specific a value");
        $this->set("type","checkbox");
        $checked_value=$this->_get_value($field_name,array());
        if($checked_value==$this->attrs["value"])
            $this->set("checked","checked");
        $html=$this->build("input",$field_name,$this->attrs);
        $this->reset();
        return $html;
    }
    /** 输出一个提交按钮 */
    function submitbox($value="提交"){
        $this->attrs["type"]="submit";
        $html=$this->build("button","",$value,$this->attrs);
        $this->reset();
        return $html;
    }
    /** 输出一个SELECT下拉框
     * @param $field_name String,字段域名字
     * @param $values Array,一个二维数组;比如:array(array("1","属性1"),array("2","属性2"),array(3,"属性3"))
     */
    function selectbox($field_name,$values){
        $value=$this->_get_value($field_name,$this->attrs);
        $select_html = $this->_left("select",$field_name,$this->attrs);
        $strs=array();
        foreach($values as $v){
            if(sizeof($v)!=2) throw new smException("you assign a bad value for select ,file:".__FILE__.",line:".__LINE__);
            if(!empty($value) && $v[0]==$value ){
                $temp=$strs[]=$this->build("option","",$v[1],array("value"=>$v[0],"selected"=>"selected"));
            }else{
                $strs[]=$this->build("option","",$v[1],array("value"=>$v[0]));
            }
        }
        $this->reset();
        return "$select_html>".join("",$strs)."</select>";
    }
    /** smForm类未明确给出的形形色色的其他各种HTML标记 
     *
     * @param $name String HTML tag 的种类，可以是img,marquee,fieldset,iframe等等;
     * @param $args Array,一个有三个项的数组,第一个项是数据域名字,第二个是HTML属性,第三个是包含在标记里的innerhtml。
     */
    function build($tag_name,$field_name,$inner_html="",$html_attrs=NULL){
        $left_htmls = $this->_left($tag_name,$field_name,$html_attrs);
        if(in_array($tag_name,array(
            "input","img","link")))
        {
            $str.=$left_htmls."/>";
        }else{
            $str.=$left_htmls.">".$inner_html."</$tag_name>";
        }
        return $str;
    }
    function _get_value($field_name,$html_attrs){
        if(!empty($this->_form_values)){
            $value=$this->_form_values[$field_name];
        }
        if(!empty($html_attrs["value"]))
            $value=$html_attrs["value"];
        return $value;
    }
    function _left($tag_name,$field_name,$html_attrs=null){
        if($field_name){
            if($tag_name!="label"){
                $str="<".$tag_name." ";
		if(empty($html_attrs["id"])){
			$str.="id=\"".$this->_form_name."_".$field_name."\" ";
		}
		if(empty($html_attrs["name"])){
			$str.="name=\"".$this->_form_name."[".$field_name."]\" ";
		}
            }else{
                $str="<".$tag_name."  ";
            }
        }
        else{
            $str="<".$tag_name." ";
        }
        if($html_attrs===NULL)
            $html_attrs=$this->attrs;
        foreach($html_attrs as $k=>$v){
            if($v!==NULL)
                $str .=$k."=\"".htmlspecialchars($v)."\" ";
        }
        return $str;
    }

}
/**   run_sm 跑MVC流程 
 * @param $controller controller名字;
 * @param $action action名字;
 */ 
function run_sm($controller=null,$action=null) {
    global $sm_temp,$sm_config;
    if(is_null($controller)) 
        $sm_temp["controller"]=empty($_GET["controller"])?  "smapplication":strtolower($_GET["controller"]);
    else
        $sm_temp["controller"]=$controller;
    if(is_null($action))
        $sm_temp["action"]=empty($_GET["action"])?  "index":strtolower($_GET["action"]);
    else
        $sm_temp["action"]=$action;
    smDoEvent("before_run_sm",array("controller"=>$sm_temp["controller"],"action"=>$sm_temp["action"]));
    if(!class_exists($sm_temp["controller"]))
        include_once($sm_config["app_root"]."/app/".strtolower($sm_temp["controller"]).".php");
    $app=new $sm_temp["controller"]($sm_temp["controller"]);
    return $app->dispatch($sm_temp["action"]); 
}
/* url转换器，1为请求转换，就是把类似q-替换为question/view
2为反向转换，就是把类似/question/view/替换为q-
*/
function sm_urlmap($var, $direction=1) {
    global $sm_config;
    $replaces=$sm_config["url_maps"];
    (2 == $direction) && $replaces = array_flip($replaces);
    return str_replace(array_keys($replaces), array_values($replaces), $var);
}
/**
 * URL静态化的处理
 */
function sm_open_shorturl(){
    global $sm_config,$sm_temp;
    $url=$_SERVER["REQUEST_URI"];
    $url=sm_urlmap($url,1);
    $parsed_patterns=(sm_compile_models($sm_config["url_routes"],$sm_config["url_namespace"]));
    $sm_temp["compiled_url_routes"]=$parsed_patterns;
    $url_parsed=sm_handle_url($parsed_patterns,$url);
    if($url_parsed["params"]) foreach($url_parsed["params"] as $k=>$v){
        if(preg_match("/\?/",$v)){
            $_GET[$k]=array_shift(explode("?",$v));

        }else{
            $_GET[$k]=$v;
        }
	}
	$sm_temp["url_pattern"]=$url_parsed["current_template"];
    $sm_temp["use_shorturl"]=true;
}
function sm_get_url_fields($pat){
    preg_match_all("/{([a-zA-Z\_]*)}/i",$pat,$regs);
    return $regs[1];
}
function sm_compile_models($models,$namespace=""){
    foreach($models as $k=>$v){
        if(is_array($v)&&!is_numeric($k)){
            $pat=$k;$field_rules=$v;
        }else{
            $field_rules=array();$pat=$v;
        }
        $fields=sm_get_url_fields($pat);
        foreach($fields as $field){
            if(!isset($field_rules[$field]))
                $field_rules[$field]="([^.^\/]+)";
        }
        $pat = $namespace.$pat;
        $real_pattern=str_replace("/","\/",$pat);
        $real_pattern=str_replace("{","(?<",$real_pattern);
        foreach($field_rules as $f=>$rule){
            $real_pattern=str_replace($f."}",$f.">".$rule.")",$real_pattern);
        }
        $real_pattern="/^".$real_pattern."/";
        $list[]=array("rules"=>$field_rules,"pat"=>$pat,"real_pattern"=>$real_pattern,"fields"=>$fields);
    }
    return $list;
}
function sm_handle_url($patterns,$url){
    foreach($patterns as $pat){
        if(preg_match($pat["real_pattern"],$url,$regs)){
            $current_pattern=$pat["real_pattern"];
            $current_template=$pat["pat"];
            foreach($pat["fields"] as $f){
                $params[$f]=$regs[$f];
            }
            return array("current_pattern"=>$current_pattern,"current_template"=>$current_template,"params"=>$params);
        }
    }
    return false;
}
/** * sm_undo_magic_quotes_array 和sm_fixgpc用于解决有的服务器打开了magic_gpc设置的问题; * */
function sm_undo_magic_quotes_array($array){   
    return is_array($array) ? array_map('undo_magic_quotes_array',$array) : str_replace("\\'", "'", str_replace("\\\"", "\"", str_replace("\\\\", "\\", str_replace("\\\x00", "\x00", $array))));
}   
/** 修正magic_quotes这个愚蠢的烦人的恼火的功能带来的不爽 */
function sm_fixgpc(){
    if(get_magic_quotes_gpc()){   
        $_GET = sm_undo_magic_quotes_array($_GET);
        $_POST = sm_undo_magic_quotes_array($_POST);
        $_COOKIE = sm_undo_magic_quotes_array($_COOKIE);
        $_FILES = sm_undo_magic_quotes_array($_FILES);
        $_REQUEST = sm_undo_magic_quotes_array($_REQUEST);
    }  
}
$sm= new smObject();
sm_fixgpc();
