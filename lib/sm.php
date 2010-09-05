<?php
/**  {{{ file information
 * vim: set fdm=marker:
 * @author xurenlu <helloasp@hotmail.com>
 * @version 1.0.0
 * @last_modified 2010-08-12 17:44:37
 * @link http://www.162cm.com
 * @copyright All wroten myself.You can use it free,But don't remove the copyright.
 * }}} */
/*** {{{  sm_log * 日志记录; */ 
function sm_log($msg){
    error_log($msg); 
}
/** }}} */
/** {{{ smPhpEvent 	一些跟事件触发相关的全局函数.
*/
global $PE_EVENTS;
global $PE_FILTERS;
$PE_FILTERS=array();
$PE_EVENTS=array();
/**
*	执行某一事件.
*@param string $event event name;
*/
function smDoEvent($event,$args=null){
	global $PE_EVENTS;
	if(is_array($PE_EVENTS[$event]))
	foreach($PE_EVENTS[$event] as $handle)
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
	global $PE_EVENTS;
	$PE_EVENTS[$event][]=$handle;
}
/**
*	给数据加一个过滤器.
*@name	smAddFilter
*@param	$dataName	string	数据名
*@param	$filterName	string 过滤器名
*@return nothing
*/
function smAddFilter($dataName,$filterName){
	global $PE_FILTERS;
	$PE_FILTERS[$dataName][]=$filterName;
}
/**
*	给数据应用过滤器.
*@name	smApplyEvent
*@param	$data	string	数据
*@param	$dataName	string	数据名
*@return nothing
*/
function  smApplyEvent(&$data,$dataName) {
	global $PE_FILTERS;
	if(is_array($PE_FILTERS[$dataName]))
	foreach($PE_FILTERS[$dataName] as $filter)
	{
		if(function_exists($filter))
		$filter(  $data);
	}
}
/*** }}} */
/*** {{{  sm_gen_url 拼凑URL时用到;*/ 
function sm_gen_url($string,$url_pattern,$get_args=array()){
    $targetURL=$url_pattern;
    foreach($get_args as $k=>$v){
        $targetURL=str_replace("{".$k."}",$v,$targetURL);
    }
    if(strlen($targetURL)>0)
        return  $targetURL;
    else 
        return $string;
}
/** }}} */
/** {{{ sm_test_urlencode * 探测一个变量是否已经被urlencode过了。 */
function sm_test_urlencode($var){
    return    (urldecode($var)==$var)?false:true;
}
/** * }}} */
/** {{{ sm_pagenav_default 分页函数 ，
 * 
 * @param int $total 总记录个数
 * @param int $pagesize 每页记录数
 * @param string $pagestr 其他分页的链接模板
 * @param array $get	一般情况下就是GET数组
 * @param string $page_var_name 一般是page
 * @param int $l 	当前页链接的左边保留多少个链接
 * @param int $r 	当前页链接的右边保留多少个链接
 * @param int $jump 是否加跳转表单。但是当前只有一页时，不显示此跳转表单。
 * @example
 * echo sm_pagenav_default(18332,20);
 * echo sm_pagenav_default(18244,25,"index.php?page={page}",array("key"=>1),"page",3,3);
 * echo sm_pagenav_default(18244,25,null,array("key"=>1),"page",3,3);
 */
function sm_pagenav_default($total,$pagesize=null,$pagestr=null,$get_args=null,$page_var_name="page",$l=4,$r=4,$jump=false){
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
    $str="<form  onsubmit='javascript:return false;'>";
    //$str.=一共".$pagecount."页，".$total."个记录。当前为第".$pagenow."页。";
    if ($pagenow>1){
        $str=$str."<span><a href='".sm_gen_url(str_replace("{page}","1",$pagestr),str_replace("{page}",1,$url_pattern),$get_args)."'>首页</a></span>";
        $str =$str."<span> <a href='".sm_gen_url(str_replace("{page}",($pagenow-1),$pagestr),str_replace("{page}",($pagenow-1),$url_pattern),$get_args)."'>上一页</a></span>";
    }else{
        $str=$str."<span>首页</span><span>&lt;&lt;上一页</span>";
    }
    $startpage=$pagenow-$l;
    $endpage=$pagenow+$r;
    if($startpage<2) $startpage=2;
    if($endpage>=$pagecount) $endpage=$pagecount;
    for($jj=$startpage;$jj<=$endpage;$jj++){
        if($jj==$pagenow)
            $str=$str."<span class='cur'>".$jj."</span>";
        else
            $str=$str."<span ><a href='".sm_gen_url(str_replace("{page}",$jj,$pagestr),str_replace("{page}",$jj,$url_pattern),$get_args)."'>".$jj."</a></span>";
    }
    if($pagenow<$pagecount){
        $str=$str."<span><a href='".sm_gen_url(str_replace("{page}",$pagenow+1,$pagestr),str_replace("{page}",$pagenow+1,$url_pattern),$get_args)."'>下一页</a></span>";
        $str=$str."<span><a href='".sm_gen_url(str_replace("{page}",$pagecount,$pagestr),str_replace("{page}",$pagecount,$url_pattern),$get_args)."'>末页</a></span>";
    }else{
        $str=$str."<span>下一页</span><span>&gt;&gt;尾页</span>";
    }
    if($pagecount>1)
        if($jump){
            $str=$str."跳到<input type=\"text\" name=\"txtpage\" id='input_".$sn."' size=\"3\" class=\"tinput\" / >页";
            $str=$str."<input type=\"button\" value=\"GO\" class=\"tinput\"
                onclick=\"javascript:if((document.getElementById('input_".$sn."').value>=1) &&(document.getElementById('input_".$sn."').value<=".$pagecount.") &&(document.getElementById('input_".$sn."').value!=".$pagenow.")) window.location='".sm_gen_url($pagestr,$url_pattern,$get_args)."'.replace('{page}',document.getElementById('input_".$sn."').value);\"/></form>";
        }
    return $str;
}
/*** }}} */
/** * {{{ class smCache 调用memcache 取缓存;*/
class smCache { 
    private $_group_id;
    private $_servers;
    private $_memcache;
    private $_flag=0;
    public $expire=7200;
    /*** {{{  __construct */ 
    public function __construct($group_id){
        global $sm_config;
        $this->_group_id=$group_id;
        $this->_servers=$sm_config["memcache"][$group_id];
        $mem=new memcache();
        foreach($this->_servers as $server){
            $mem->addServer($server["host"],$server["port"]);
        }
    }
    /** }}} */
    /** * {{{ get_data 读缓存值 */
    function get_data($key){
        return $this->mem->get($key);
    }
    /** * }}} */
    /*** {{{ set_data 设置缓存值;*/
    function set_data($key,$val,$expire=7200){
        return  $this->mem->set($key,$val,$this->_flag,$expire);
    }
    /*** }}} */
    /*** {{{ set_flag */
    function set_flag($flag){
        $this->_flag = $flag;
    }
    /*** }}} */
    function __get($name){
        return $this->get_data($name);
    }
    function __set($name,$value){
        return $this->set_data($name,$value,$this->expire);
    }
}
/** * }}}  */
/** {{{ static class sm_sql  帮助构造SQL语句的小工具类;
@example  
sm_sql::update( "users", array( "id"=>"111", "name"=>"uxferwe'fdsf", "pass"=>"fdsfdsfu2323\\fsdfdsf/'fsdfsdf\""), "id=9999");
sm_sql::insert( "users", array( "id"=>"111", "name"=>"uxferwe'fdsf", "pass"=>"fdsfdsfu2323\\fsdfdsf/'fsdfsdf\""));
sm_sql::select( "users", "*", "id>9999", "id desc", "limit 100", "age");
 */
class sm_sql{
	var $pagesize=20;
	static function escape_string($v){
		return mysql_escape_string($v);
    }
	/** {{{ update  构造更新SQL
	*@param string $table 要更新的数据表名
	*@param array $array 要更新的数据
	*@param string condition 更新条件
	*/
	static function update($table, $array, $condition,$limit=1){   
		if(is_array($array)){
			$sql = "UPDATE `".$table."` SET ";
			$comma = ""; 
			foreach ($array AS $_key => $_val)
			{
				$sql .= $comma."`".$_key."` = '".self::escape_string($_val)."'";
				$comma = ", ";
			}

			if ($condition)
			{
				$sql .= " WHERE ".$condition;
            }
            $sql .= " LIMIT $limit";
			return $sql;
		}
		else{
			return false;
		}
    }
    /** }}} */
	/** {{{ select 构造查询SQL
     *
     * @param string $table 表名字
	 * @param string $columns
	 * @param string $conditions
	 * @param string $order
	 * @param string $limit
	 * @param string $group
	 */
	static function select($table,$columns="*",$conditions=null,$order=null,$limit=null,$group=null){
		global $config;
		if(is_array($columns))
			$cols=join(",",$columns);
		else
			$cols=$columns;
		$sql="	SELECT ".$columns." From ".$table;
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
    /** }}} */
    /*** {{{  count 构造count类语句,注意构造出的是count(*) as c ;*/ 
	static function count($table,$conditions=null,$order=null,$limit=null,$group=null){
        return self::SELECT($table,"count(*) as c",$conditions,$order,$limit,$group); 
    }
    /** }}} */
	/** {{{ insert 得到插入语句的SQL
	* @name insert
	* @param strint $table 数据表名字
	* @param array array 要插入的一行数据
	*/
	static function insert($table,$array){
		if (is_array($array)){	
			$comma = $key = $value = "";
			foreach ($array AS $_key => $_val){	
				$key .= $comma."`".$_key."` ";
				$value .= $comma."'".self::escape_string($_val)."'";	
				$comma = ", ";
			}
			$sql = "INSERT INTO ".$table.  "(".$key.") VALUES (".$value.")";
			return $sql;
		}
    }
    /*** }}} */
	/** {{{ delete  得到删除语句的SQL
	* @param string $table 数据表名字
	* @param string $condition 条件
	* @param integer $limit limit字段
	*/
	static function delete($table,$condition,$limit="1"){
		$sql="DELETE FROM `".mysql_escape_string($table)."` WHERE ".$condition." LIMIT ".$limit;
		return $sql;
    }
    /** }}} */
}
/*** }}} */
/*** {{{  _sm_mysql  连接Mysql的实际函数 */ 
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
/** }}} */
/*** {{{  sm_dbo 返回一个连接对象
 * @param integer $id 在sm_config里的mysql相关配置索引;*/ 
function sm_dbo($id=0){
    global $sm_config,$sm_temp;
    return is_resource($sm_temp["connections"][$id])?$sm_temp["connections"][$id]:_sm_mysql($id);
}
/** }}} */
/*** {{{  sm_query 执行一条sql查询并返回结果 */ 
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
            throw new smException("mysql error:sql:$sql,error descrption:".mysql_error());
        }
        else{
            smDoEvent("query_fail",array("sql"=>$sql,"error_no"=>mysql_error($conn)));
            throw new smException("mysql error:sql:$sql,error descrption:".mysql_error($conn));
        }
    }else{
        smDoEvent("query_succeed",array("sql"=>$sql,"resource"=>$ret));
    }
    smDoEvent("after_query",$sql);
    return $ret;
}
/** }}} */
/** {{{ sm_fetch_row 取出sql查询的一条结果 */
function sm_fetch_row($sql,$conn=null){
    $rs=sm_query($sql,$conn);
    return empty($rs)? null:mysql_fetch_assoc($rs);
}
/** }}} */
/*** {{{  sm_fetch_rows 取出sql查询的多条结果 */ 
function sm_fetch_rows($sql,$conn=null,$type=MYSQL_ASSOC){
    global $sm_config;
    $rs=sm_query($sql,$conn);
    if(!empty($rs)){
        $rows=array();
        while($row=mysql_fetch_array($rs,$type)){
            $rows[]=$row;
        }
        return $rows;
    }
}
/** }}} */
class smObject {
    /*** {{{  __get 
     * the main magic method
     */ 
    public $default_primary_key = "id";
    public function __get($name)
    {
        global $sm_config,$sm_temp,$sm_data;
        if(!empty($sm_config[$name]))
            return $sm_config[$name];
        if(!empty($sm_temp[$name]))
            return $sm_temp[$name];
        if(!empty($sm_data[$name]))
            return $sm_data[$name];
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
        if(preg_match("/^table_(.*)$/",$name)){
            $table_name = substr($name,6,strlen($name));
            $table = new smTable($table_name,$this->default_primary_key,$this->dbo_0,$this->dbo_1);
            $sm_temp[$name]=$table;
            return $table;
        }
        if(preg_match("/^cache_(.*)$/",$name)){
            $cache_group = substr($name,6,strlen($name));
            $cache = new smCache($cache_group);
            $sm_temp[$name]=$cache;
            return $cache;
        }
    }
    /** }}} */
}
class smException  extends Exception{}
/** {{{ class smTable **/
class smTable{
    private $_rconn=null;
    private $_wconn=null;
    private $_table=null;
    private $_pagesize=null;
    private $_page_var = "page";
    private $_extra_args = null;
    function __construct($table,$primary_key="id",$rconn=null,$wconn=null){
        global $sm_config;
        $this->_table=$table;
        if(is_null($rconn))
            $rconn=sm_dbo(0);
        $this->_rconn=$rconn;
        if(is_null($wconn))
            $wconn=$rconn;
        $this->_wconn=$wconn;
        $this->_extra_args=$_GET;
        $this->_pagesize=($sm_config["pagesize"]>0)?$sm_config["pagesize"]:20;
    }
    /*** {{{ set variables */ 
    public function __set($varname,$value){
        $this->$varname=$value;
    }
    /** }}} */
    /*** {{{  get_select_conditions */ 
    function get_select_conditions($columns,$values){
            $conditions=array();
            foreach($columns as $k=>$v){
                $conditions[]="`".$v."` = '".mysql_escape_string($values[$k])."'";
            }
            return $conditions; 
    }
    /** }}} */
    function desc(){
        $rows=sm_fetch_rows("desc ".$this->_table);
        return $rows;
    }
    /*** {{{  find_by 根据指定的条件来查询*/ 
    public function find_by($conditions=null,$wanted="*",$order_by=null,$limit=null,$group_by=null){
        $sql=sm_sql::select($this->_table,$wanted,$conditions,$order_by,$limit,$group_by);
        $rows=sm_fetch_rows($sql,$this->_rconn);
        return $rows;
    }
    /** }}} */
    /*** {{{ find_row_by 根据条件列查找数据,直接调用find_by并返回第一条数据;*/ 
    public function find_row_by($conditions=null,$wanted="*",$order_by=null,$limit=1,$group_by=null){
        return array_shift($this->find_by($conditions,$wanted,$order_by,$limit,$group_by));
    }
    /** }}} */
     /*** {{{ page_by 根据条件查询数据,同时自带分页;*/ 
    public function page_by($conditions=null,$wanted="*",$order_by=null,$limit=null,$group_by=null){
        if(! ($_GET[$this->_page_var]>0))
            $pagenow=1;
        else
            $pagenow=$_GET[$this->_page_var];
        $limit=($pagenow-1)*$this->_pagesize.",".$this->_pagesize;
        $rows=$this->find_by($conditions,$wanted,$order_by,$limit,$group_by); 
        $count_sql=sm_sql::count($this->_table,$conditions,$order_by,1,$group_by);
        $row=sm_fetch_row($count_sql,$this->_rconn);
        $total=$row["c"];
        $pagestr = sm_pagenav_default($total,$this->_pagesize,$this->_pagestr,$this->_extra_args,$this->_page_var,3,3);
        return array("total"=>$total,"entries"=>$rows,"page"=>$pagestr);
     }
     /** }}} */
     /*** {{{ update_by 根据条件更新数据;*/ 
     public function update_by($conditions,$values,$limit=1){
		$sql=sm_sql::update($this->_table,$values,$conditions,$limit);
        return sm_query($sql,$this->_wconn); 
     }
     /** }}} */
     /*** {{{ delete_by */ 
     public function delete_by($conditions,$limit=1){
		    $sql=sm_sql::delete($this->_table,$conditions);
		    return sm_query($sql,$this->_wconn);
     }
     /** }}} */
     /*** {{{  create */ 
     public function create($row){
         $sql=sm_sql::insert($this->_table,$row);
         return sm_query($sql,$this->_wconn); 
     }
     /** }}} */
}
/*** }}} */

/** {{{  smApplication Mvc 功能主要在这里实现;**/
class smApplication{
    private $_app="smapplication";
    private $_name="smapplication";
    private $_last_action="index";
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
    /*** {{{  _before_filter */ 
    function _before_filter($action){
        $var_name = "before_filters_$action";
        foreach($this->$var_name as $method){
            $this->$method();
        }
        foreach($this->before_filters as $method){
            $this->$method();
        }
    }
    /** }}} */
     /*** {{{ _after_filter      */ 
    function _after_filter($action){
        $var_name = "after_filters_$action";
        foreach($this->$var_name as $method){
            $this->$method();
        }
        foreach($this->after_filters as $method){
            $this->$method();
        }
     }
     /** }}} */
    /*** {{{  method_miss */ 
    private function _method_missing($method) {
        throw new smException("method  missing:".$this->_name."->".$method);
    }
    /** }}} */
    /*** {{{ v include the view files;*/
    function v($action=null){
        global $sm_config;
        if(is_null($action))
            $action=$this->_last_action;
        $mod=$this->_name;
        if($sm_config["use_layout"]){
            //如果使用布局并且布局文件存在...
                if(!include($sm_config["app_root"]."/app/layouts/$mod.php") )
                    return include($sm_config["app_root"]."/app/views/$mod/$action.php");
                else return true;
        }
        else{
               return include($sm_config["app_root"]."/app/views/$mod/$action.php");
        }
    }
    /*** }}} */
    /*** {{{  dispatch run the filters and real action method;*/ 
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
        /* 如果views 文件也不存在,那就调用method_missing方法; */
        $this->_app->_method_missing($action);
        return false;
    }
    /** }}} */
    public function yield(){
        global $sm_config;
        return include($sm_config["app_root"]."/app/views/".$this->_name."/".$this->_last_action.".php");
    }
    /*** {{{ establish_connect 建立默认连接,默认情况下读写用同一个链接; */
    public function establish_connect(){
        global $sm;
        $this->_rconn=$sm->dbo_1;
        $this->_wconn=$sm->dbo_0;
    }
    /*** }}} */
    /*** {{{ __get magic method;*/
    public function __get($var){
		return array();
    }
    /*** }}} */
}
/*** }}} */
function sm_tag($tagname,$html_attrs=array(),$inner_html=""){
    $str="<$tagname ";
    if(is_array($html_attrs)){
    foreach($html_attrs as $k=>$v){
        $str.= "$k='".htmlspecialchars($v)."' ";
    }
    }
    else{
        $str .= $html_attrs;
    }
    if(!empty($inner_html))
        $str .= ">".$inner_html."</$tagname>";
    else
        $str .= "/>";
    return $str;
}
function image_tag($src,$html_attrs=array()){
    global $sm_config;
    $src = $sm_config["image_path"]."$src";
    if(is_array($html_attrs))
        return sm_tag("img",array_merge($html_attrs,array("src"=>$src)));
    else
        return sm_tag("img",$html_attrs." src='".htmlspecialchars($src)."'");
}
/** {{{ class Form **/
class smForm
{
    var $_values=array();
    var $_name="";
    /**
     * 如果$arg是一维数据,则表示数据表中的一列数据;
     * 如果$arg是对象,则视为smTable对象;
     * 如果$arg是字符串,则视为mysql数据表名字;
     * */
    function __construct($name,$values=null){
        $this->_name=$name;
        $this->_values=$values;
    }
    function begin($action,$html_attrs=array("method"=>"POST")){
        $str="<form  action='$action' ";
        foreach($html_attrs as $k=>$v){
            $str.=" $k='".$v."' ";
        }
        $str.= ">";
        return $str;
    }
    function end(){
        return "</form>";
    }
    function text_area($field_name,$html_attrs=array()){
        $value=$this->_get_value($field_name,$html_attrs);
        $html= $this->_left("textarea",$field_name,$html_attrs);
        $html.=">".htmlspecialchars($value)."</textarea>";
        return $html;
    }
    function text_field($field_name,$html_attrs=array()){
        $value=$this->_get_value($field_name,$html_attrs);
        $html_attrs["type"]="text";
        $html_attrs["value"]=$value;
        return $this->input($field_name,$html_attrs);
    }
    function check_box($field_name,$html_attrs=array("type"=>"check_box")){
        if(!isset($html_attrs["value"]))
            throw new smException("check_box must specific a value");
        $checked_value=$this->_get_value($field_name,array());
        if($checked_value==$html_attrs["value"])
            $html_attrs["checked"]="checked";
        return $this->input($field_name,$html_attrs);
    }
    function submit($value="提交",$html_attrs=array()){
        $html_attrs["type"]="submit";
        return $this->button("",$html_attrs,$value);
    }
    function select($field_name,$values,$html_attrs=array()){
        $value=$this->_get_value($field_name,$html_attrs);
        $select_html = $this->_left("select",$field_name,$html_attrs);
        foreach($values as $v){
            if(sizeof($v)!=2) throw new smException("you assign a bad value for select ,file:".__FILE__.",line:".__LINE__);
            if(!empty($value) && $v[0]==$value ){
                $strs[]=$this->option("",array("value"=>$v[0],"selected"=>"selected"),$v[1]);
            }else{
                $strs[]=$this->option("",array("value"=>$v[0]),$v[1]);
            }
        }
        return "$select_html".join("",$strs)."</select>";
    }
    function __call($name,$args){
        $tag_name = $name;
        $field_name= array_shift($args);
        $html_attrs=array_shift($args);
        $inner_html=array_shift($args);
        $left_htmls = $this->_left($tag_name,$field_name,$html_attrs);
        $str.=$left_htmls.">".$inner_html."</$tag_name>";
        return $str;
    }
    function _get_value($field_name,$html_attrs){
        if(!empty($this->_values)){
            $value=$this->_values[$field_name];
        }
        if(!empty($html_attrs["value"]))
            $value=$html_attrs["value"];
        return $value;
    }
    function _left($tag_name,$field_name,$html_attrs){
        if($field_name){
            $str="<".$tag_name." id='".$this->_name."_".$field_name."' name='".$this->_name."[".$field_name."]' ";
        }
        else{
            $str="<".$tag_name." ";
        }
        foreach($html_attrs as $k=>$v){
            $str .=$k."='".$v."' ";
        }
        return $str;
    }
    function fetch($source=null){
        if(is_null($source)) $source=$_POST;
        return $source;
    }
}
/*** }}} */
/*** {{{  run_sm */ 
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

    if(!class_exists($sm_temp["controller"]))
        include_once($sm_config["app_root"]."/app/".strtolower($sm_temp["controller"]).".php");
    $app=new $sm_temp["controller"]($sm_temp["controller"]);
    return $app->dispatch($sm_temp["action"]); 
}
/** }}} */
$sm= new smObject();
