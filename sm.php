<?php
/**  {{{ file information
 * vim: set fdm=marker:
 * @author xurenlu <helloasp@hotmail.com>
 * @version 1.0.0
 * @last_modified 2010-08-12 17:44:37
 * @link http://www.162cm.com
 * @copyright All wroten myself.You can use it free,But don't remove the copyright.
 * }}} */
/*** {{{  sm_log 
 * 日志记录;
*/ 
function sm_log($msg){
    error_log($msg); 
}
/** }}} */
/*** {{{  pr 
*/ 
function pr($var,$legend="variable"){
    if(php_sapi_name()=="cli"){
        echo "\n$legend:\n";
        print_r($var);
        echo "\n";
        return;
    }
    echo "\n<hr/>$legend<hr/><pre>";
    print_r($var);
    echo "</pre>";
}
/** }}} */
/*** {{{  sm_gen_url 
 */ 
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
/** {{{ sm_test_urlencode
 * 探测一个变量是否已经被urlencode过了。
 */
function sm_test_urlencode($var){
    if(urldecode($var)==$var)
        return false;
    else
        return true;
}
/** * }}} */
/** {{{ sm_pagenav_default
 * Page navgation ，
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
function sm_pagenav_default($total,$pagesize=null,$pagestr=null,$get_args=null,$page_var_name="page",$l=4,$r=4,$jump=true){
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
    $str="<form  onsubmit='javascript:return false;'>一共".$pagecount."页，".$total."个记录。当前为第".$pagenow."页。";
    if ($pagenow>1){
        $str=$str."&nbsp;&nbsp;<a href='".sm_gen_url(str_replace("{page}","1",$pagestr),str_replace("{page}",1,$url_pattern),$get_args)."'>|<<</a>&nbsp;";
        $str =$str." <a href='".sm_gen_url(str_replace("{page}",($pagenow-1),$pagestr),str_replace("{page}",($pagenow-1),$url_pattern),$get_args)."'><</a>&nbsp;";
    }
    $startpage=$pagenow-$l;
    $endpage=$pagenow+$r;
    if($startpage<2) $startpage=2;
    if($endpage>=$pagecount) $endpage=$pagecount;
    for($jj=$startpage;$jj<=$endpage;$jj++){
        if($jj==$pagenow)
            $str=$str."<strong>".$jj."</strong>&nbsp;";
        else
            $str=$str."<a href='".sm_gen_url(str_replace("{page}",$jj,$pagestr),str_replace("{page}",$jj,$url_pattern),$get_args)."'>".$jj."</a>&nbsp;";
    }
    if($pagenow<$pagecount){
        $str=$str."<a href='".sm_gen_url(str_replace("{page}",$pagenow+1,$pagestr),str_replace("{page}",$pagenow+1,$url_pattern),$get_args)."'>&gt;</a>&nbsp;";
        $str=$str."<a href='".sm_gen_url(str_replace("{page}",$pagecount,$pagestr),str_replace("{page}",$pagecount,$url_pattern),$get_args)."'>&gt;&gt;</a>&nbsp;";
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
/** * {{{ class smCache **/
class smCache { 
    private $_group_id;
    private $_servers;
    private $_memcache;
    private $_flag=0;
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
    /**
     * {{{ get_data
     * */
    function get_data($key){
        return $this->mem->get($key);
    }
    /** * }}} */
    /*** {{{ set_data */
    function set_data($key,$val,$expire=7200){
        return  $this->mem->set($key,$val,$this->_flag,$expire);
    }
    /*** }}} */
    /*** {{{ set_flag */
    function set_flag($flag){
        $this->_flag = $flag;
    }
    /*** }}} */
}
/** * }}}  */
/** {{{ static class sm_sql 
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
	/** {{{ update
	* 构造更新SQL
	*
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
	/** {{{ select
	 * 构造查询SQL
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
    /*** {{{  count */ 
	static function count($table,$conditions=null,$order=null,$limit=null,$group=null){
        return self::SELECT($table,"count(*) as c",$conditions,$order,$limit,$group); 
    }
    /** }}} */
	/** {{{ insert
	* 得到插入语句的SQL
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
	/** {{{ delete 
	* 得到删除语句的SQL
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
    if(!is_resource($conn) || !$switch){
            throw new smException("Mysql error:Can't connect to hosts with : -h ".$config["host"]." -u ".$config["user"]." -p ".substr($config["password"],0,2)."*** ".$config["database"]);
    }
    return $conn;
}
/** }}} */
/*** {{{  sm_dbo */ 
function sm_dbo($id=0){
    global $sm_config,$sm_temp;
    return is_resource($sm_temp["connections"][$id])?$sm_temp["connections"][$id]:_sm_mysql($id);
}
/** }}} */
/*** {{{  sm_query 执行一条sql查询并返回结果*/ 
function sm_query($sql,$conn=null){
    global $sm_config;
    if($sm_config["sql_debug"]){
        error_log($sql);
    }
    return is_null($conn)?mysql_query($sql):mysql_query($sql,$conn);//不指定conn时,mysql会调用默认连接
}
/** }}} */
/** {{{ sm_fetch_row 取出sql查询的一条结果 */
function sm_fetch_row($sql,$conn=null){
    $rs=sm_query($sql,$conn);
    return empty($rs)? null:mysql_fetch_assoc($rs);
}
/** }}} */
/*** {{{  sm_fetch_rows 取出sql查询的多条结果 */ 
function sm_fetch_rows($sql,$conn=null){
    global $sm_config;
    $rs=sm_query($sql,$conn);
    if(!empty($rs)){
        $rows=array();
        while($row=mysql_fetch_assoc($rs)){
            $rows[]=$row;
        }
        return $rows;
    }
}
/** }}} */
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
        $this->_rconn=$rconn;
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
     /*** {{{ __call the famous magic method */
    function __call($name,$args){
        if(preg_match("/^find_by_/",$name)){
            //是要根据某些键值来删除数据
            $temp= substr($name,8,strlen($name)-8);
            $columns = explode("_and_",$temp);
            if(sizeof($args)<sizeof($columns)){
                throw new smException("length of columns and columns not match.");
                return null;
            }
            $values = array();
            for($i=0;$i<sizeof($columns);$i++){
                $values[]=array_shift($args);
            }
            $conditions = $this->get_select_conditions($columns,$values);
            $limit=$group_by=$order_by=null;
            $wanted="*";
            if(!empty($args[0])){
                if(!empty($args[0]["where"])){
                    $conditions[]=$args[0]["where"];
                }
                $limit=empty($args[0]["limit"])?null:$args[0]["limit"];
                $group_by=empty($args[0]["order_by"])?null:$args[0]["group_by"];
                $order_by=empty($args[0]["order_by"])?null:$args[0]["order_by"];
                $wanted=empty($args[0]["wanted"])?"*":$args[0]["wanted"];
            }
            $cond=join(" AND ",$conditions);
            return $this->find_by($wanted,$cond,$order_by,$limit,$group_by);
        }
        if(preg_match("/^page_by_/",$name)){
            $temp= substr($name,8,strlen($name)-8);
            $columns = explode("_and_",$temp);
            if(sizeof($args)<sizeof($columns)){
                throw new smException("length of columns and columns not match.");
                return null;
            }
            $values = array();
            for($i=0;$i<sizeof($columns);$i++){
                $values[]=array_shift($args);
            }
            $conditions = $this->get_select_conditions($columns,$values);
            $wanted="*";
            $limit=$group_by=$order_by=null;
            if(!empty($args[0])){
                //还有多余的参数;
                if(!empty($args[0]["where"])){
                    $conditions[]=$args[0]["where"];
                }
                if(!empty($args[0]["limit"])){
                    throw new smException("you should not specific the limit argument when you call a page_by_[some_field] ");
                    return null;
                }
                $group_by=!empty($args[0]["group_by"])? $order_by=$args[0]["group_by"]:null;
                $order_by=!empty($args[0]["order_by"])? $order_by=$args[0]["order_by"]:null;
                $wanted=!empty($args[0]["wanted"])?$args[0]["wanted"]:"*";
                $per_page= $args[0]["per_page"]>0?$args[0]["per_page"]:( $sm_config["pagesize"]>0?$sm_config["pagesize"]:20);
                $page= $args[0]["page"]>0?$args[0]["page"]:1;
            }
            $cond=join(" AND ",$conditions);
            $limit = ($page-1)*$per_page.",$per_page"; 
            return $this->page_by($wanted,$cond,$order_by,$limit,$group_by);
        }
    }
    /*** }}} */
    /*** {{{  find_by */ 
    public function find_by($conditions=null,$wanted="*",$order_by=null,$limit=null,$group_by=null){
        $sql=sm_sql::select($this->_table,$wanted,$conditions,$order_by,$limit,$group_by);
        $rows=sm_fetch_rows($sql,$this->_rconn);
        return $rows;
    }
    /** }}} */
    /*** {{{ find_row_by 根据条件列查找数据;*/ 
    public function find_row_by($conditions=null,$wanted="*",$order_by=null,$limit=1,$group_by=null){
        return array_shift($this->find_by($conditions,$wanted,$order_by,$limit,$group_by));
    }
    /** }}} */
     /*** {{{ page_by 根据条件查询数据,同时自带分页;*/ 
    public function page_by($conditions=null,$wanted="*",$order_by=null,$limit=null,$group_by=null){
        if(! ($_GET[$this->_page_var]>0)){
            $pagenow=1;
        }
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
/** {{{  smApplication **/
class smApplication{
    private $_app="smapplication";
    private $_name="smapplication";
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
    private  function _before_filter($action){
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
     private function _after_filter($action){
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
    function v($action){
        $path= $sm_config["app_root"]."/app/views/".$this->_name."/$action.php";
        if(!include($path))
            echo "view file [$path] not exists";
    }
    /*** }}} */
    /*** {{{  dispatch */ 
    public function dispatch($action){
        global $sm_config;
        $methods=get_class_methods($this->_app);
        if(in_array($action,$methods)){
            $this->_app->_before_filter($action);
            $this->_app->$action();
            $this->_app->_after_filter($action);
            return true;
        }
        if(in_array("action_".$action,$methods)){
            $method="action_".$action;
            $this->_app->_before_filter($action);
            $this->_app->$method();
            $this->_app->_after_filter($action);
            return true;
        }
        /* 如果views 文件也不存在,那就调用method_missing方法; */
        if(!include($sm_config["app_root"]."/app/views/".$this->_name."/$action.php"))
            $this->_app->_method_missing($action);
        return false;
    }
    /** }}} */
    /*** {{{ establish_connect 建立默认连接,默认情况下读用 */
    public function establish_connect(){
       return  $this->_rconn =$this->_wconn=sm_dbo(0);
    }
    /*** {{{ __get */
    public function __get($var){
		return array();
    }
    /*** }}} */
}
/*** }}} */
/*** {{{  run_sm */ 
function run_sm($controller=null,$action=null) {
    global $sm_temp;
    if(is_null($controller)) 
        $sm_temp["controller"]=empty($_GET["controller"])?  "smapplication":strtolower($_GET["controller"]);
    else
        $sm_temp["controller"]=$controller;
    if(is_null($action))
        $sm_temp["action"]=empty($_GET["action"])?  "index":strtolower($_GET["index"]);
    else
        $sm_temp["action"]=$action;
    $app=new smApplication($sm_temp["controller"]);
    return $app->dispatch($sm_temp["action"]); 
}
/** }}} */
