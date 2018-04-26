<?php
 namespace Chat;

 class Util{

 	
 	/**
	* 对查询结果集进行排序
	* @access public
	* @param array $list 查询结果
	* @param string $field 排序的字段名
	* @param array $sortby 排序类型
	* asc正向排序 desc逆向排序 nat自然排序
	* @return array
	*/
	public static function list_sort_by($list,$field, $sortby='desc') {
	   if(is_array($list)){
	       $refer = $resultSet = array();
	       foreach ($list as $i => $data)
	           $refer[$i] = &$data[$field];
	       switch ($sortby) {
	           case 'asc': // 正向排序
	                asort($refer);
	                break;
	           case 'desc':// 逆向排序
	                arsort($refer);
	                break;
	           case 'nat': // 自然排序
	                natcasesort($refer);
	                break;
	       }
	       foreach ( $refer as $key=> $val)
	           $resultSet[] = &$list[$key];
	       return $resultSet;
	   }
	   return false;
	}
	
 }






