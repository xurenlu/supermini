<?php
/**
 * Mysql 相关函数
 *
 * @author renlu <xurenlu@gmail.com>
 * @version $Id$
 * @copyright renlu <xurenlu@gmail.com>, 11 八月, 2010
 * @package default
 **/
/*** {{{  _sm_mysql 
*/ 
function _sm_mysql($id)
{
    global $sm_config; 
    if(!is_array($sm_config["mysql"][$id])){
        if($sm_config["debug"]) {
            sm_log("MYSQL configuration 'sm_config[\"mysql\"][$id]' not exists");
        }
        return null;
    }
    $config=$sm_config["mysql"][$id];
    $conn=mysql_connect($config["host"],$config["user"],$config["password"]);
    $switch=mysql_select_db($config["database"],$conn);
    if(!is_resource($conn) || !$switch){
        if($sm_config["debug"]){
            sm_log("Mysql error:Can't connect to hosts with : -h ".$config["host"]." -u ".$config["user"]." -p ".$config["password"]." ".$config["database"]);
            return null;
        }
    }
    return $conn;
}
/** }}} */

/*** {{{  sm_dbo 
*/ 
function sm_dbo($id=0)
{
    global $sm_config;
    global $sm_objects;

    if(is_resource($sm_objects[$id])){
        return $sm_objects[$id];
    }else{
        return _sm_mysql($id);
    }
}
/** }}} */

/*** {{{  sm_query 
*/ 
function sm_query($sql,$conn=null)
{
    global $sm_config;
    if($sm_config["sql_debug"]){ error_log($sql); }
    if(is_null($conn)){
        //不指定conn,则由mysql调用默认连接
        $rs=mysql_query($sql);
    }
    else{
        $rs=mysql_query($sql,$conn);
    }
    return $rs;
}
/** }}} */

/** {{{ sm_fetch_row
 * 
 * */
function sm_fetch_row($sql,$conn=null){
    global $sm_config;
    $rs=sm_query($sql,$conn);
    if(!empty($rs)){
        $row=mysql_fetch_assoc($rs); 
        return $row;
    }
    else{
        return null;
    }
}
/** }}} */
/*** {{{  sm_fetch_rows 
*/ 
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
    else{
        return null;
    }
}
/** }}} */
/**
 * class smException
 **/
class smException  extends Exception
{
    
    function __construct($msg,$code=-1)
    {
        // code...
        parent::__construct($msg,$code);
    }
    /*** {{{  __toString 
    */ 
    public function __toString()
    {
        return $msg."";
    }
    /** }}} */
    
}
/**
 * class smTable
 **/
class smTable{
    private $_conn=null;
    private $_table=null;
    private $_pagesize=null;
    private $_page_var = "page";
    private $_extra_args = "null";
    function __construct($table,$primary_key="id",$conn=null){
        $this->_table=$table;
        $this->_conn=$conn;
        $this->_extra_args=$_GET;
    }
    /*** {{{  set_conn 
    */ 
    public function set_conn($conn)
    {
        $this->_conn=$conn;
    }
    /** }}} */
    /*** {{{  get_select_conditions 
    */ 
    function get_select_conditions($columns,$values)
    {
            $conditions=array();
            foreach($columns as $k=>$v){
                $conditions[]="`".$v."` = '".mysql_escape_string($values[$k])."'";
            }
            return $conditions; 
    }
    /** }}} */
    
    
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
            $limit=$group_by=$order_by=$wanted=null;
            if(!empty($args[0])){
                //还有多余的参数;
                if(!empty($args[0]["where"])){
                    $conditions[]=$args[0]["where"];
                }
                if(!empty($args[0]["limit"])){
                    $limit=$args[0]["limit"];
                }else{
                    $limit=null;
                }
                if(!empty($args[0]["group_by"])){
                    $group_by=$args[0]["group_by"];
                }else{
                    $group_by=null;
                }
                if(!empty($args[0]["order_by"])){
                    $order_by=$args[0]["order_by"];
                }else{
                    $order_by=null;
                }
                if(!empty($args[0]["wanted"])){
                    $wanted=$args[0]["wanted"];
                }else{
                    $wanted="*";
                }
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
            $limit=$group_by=$order_by=$wanted=null;
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
    /*** {{{  find_by 
    */ 
    public function find_by($conditions=null,$wanted="*",$order_by=null,$limit=null,$group_by=null)
    {
            $sql=sm_sql::select($this->_table,$wanted,$conditions,$order_by,$limit,$group_by);
            $rows=sm_fetch_rows($sql,$this->_conn);
            return $rows;
         
    }
    /** }}} */
    /*** {{{ find_row_by 
    */ 
    public function find_row_by($conditions=null,$wanted="*",$order_by=null,$limit=1,$group_by=null){

        $rows=$this->find_by($conditions,$wanted,$order_by,$limit,$group_by);
        return $rows[0];
    }
    /** }}} */
     /*** {{{ page_by 
     */ 
    public function page_by($conditions=null,$wanted="*",$order_by=null,$limit=1,$group_by=null)
     {
         $rows=$this->find_by($conditions,$wanted,$order_by,$limit,$group_by); 
         $count_sql=sm_sql::count($this->_table,$conditions,$order_by,1,$group_by);
         $row=sm_fetch_row($count_sql);
         $total=$row["c"];
         $pagestr = sm_pagenav_default($total,$this->_pagesize,$this->_pagestr,$this->_extra_args,$this->_page_var,3,3);
         return array("total"=>$total,"entries"=>$rows,"page"=>$pagestr);
     }
     /** }}} */
     
     /*** {{{ update_by 
     */ 
     public function update_by($conditions,$values,$limit=1)
     {
		$sql=sm_sql::update($this->_table,$values,$conditions,$limit);
        return sm_query($sql); 
     }
     /** }}} */
     /*** {{{ delete_by 
     */ 
     public function delete_by($conditions,$limit=1)
     {
		    $sql=sm_sql::delete($this->_table,$conditions);
     }
     /** }}} */
     /*** {{{  create 
     */ 
     public function create($row)
     {
         $sql=sm_sql::insert($this->_table,$row);
         return sm_query($sql); 
     }
     /** }}} */
}
