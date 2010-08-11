<?php
class sm_sql{
	var $pagesize=20;
	static function escape_string($v){
		return mysql_escape_string($v);
    }
    
	/**
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


	/**
	 * 构造查询SQL
	 *
	 * @param string $columns
	 * @param string $conditions
	 * @param string $order
	 * @param string $limit
	 * @param string $group
	 */
	static function select($table,$columns="*",$conditions=null,$order=null,$limit=null,$group=null)
	{
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
    /*** {{{  count 
    */ 
	static function count($table,$conditions=null,$order=null,$limit=null,$group=null)
    {
        return self::SELECT($table,"count(*) as c",$conditions,$order,$limit,$group); 
    }
    /** }}} */
    
	/**
	* 得到插入语句的SQL
	* @name insert
	* @param strint $table 数据表名字
	* @param array array 要插入的一行数据
	*/
	static function insert($table,$array){
		if (is_array($array))
		{	
			$comma = $key = $value = "";
			foreach ($array AS $_key => $_val)
			{	
				$key .= $comma."`".$_key."` ";
				$value .= $comma."'".self::escape_string($_val)."'";	
				$comma = ", ";
			}
			$sql = "INSERT INTO ".$table.  "(".$key.") VALUES (".$value.")";
			return $sql;
		}
	}
	/**
	* 得到替换语句的SQL
	* @name replace 
	* @param strint $table 数据表名字
	* @param array array 要插入的一行数据
	*/
	static function replace($table,$array){
		if (is_array($array))
		{	
			$comma = $key = $value = "";
			foreach ($array AS $_key => $_val)
			{	
				$key .= $comma."`".$_key."` ";
				$value .= $comma."'".self::escape_string($_val)."'";	
				$comma = ", ";
			}
			$sql = "REPLACE INTO ".$table.  "(".$key.") VALUES (".$value.")";
			return $sql;
		}
	}
	/**
	* 得到删除语句的SQL
	* @param string $table 数据表名字
	* @param string $condition 条件
	* @param integer $limit limit字段
	*/
	static function delete($table,$condition,$limit="1")
	{
		$sql="DELETE FROM `".mysql_escape_string($table)."` WHERE ".$condition." LIMIT ".$limit;
		return $sql;
	}
}
/**
@example  
print_r(sm_sql::update(
"users",
array(
	"id"=>"111",
	"name"=>"uxferwe'fdsf",
	"pass"=>"fdsfdsfu2323\\fsdfdsf/'fsdfsdf\""
),
"id=9999"));
echo "\n";
print_r(sm_sql::insert(
"users",
array(
	"id"=>"111",
	"name"=>"uxferwe'fdsf",
	"pass"=>"fdsfdsfu2323\\fsdfdsf/'fsdfsdf\""
)));
echo "\n";
print_r(sm_sql::select(
"users",
"*",
"id>9999",
"id desc",
"limit 100",
"age"));
echo "\n";
*/
?>
