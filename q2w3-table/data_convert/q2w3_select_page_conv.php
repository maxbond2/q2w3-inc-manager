<?php

/**
 * @author Max Bond
 * 
 * Special class for converting data for <select> element with multiple="true" option
 *
 */
class q2w3_select_page_conv extends _q2w3_data_conv {



	public function php2db($data) {
	
		if (is_array($data) && !empty($data)) {
			
			return implode(",", $data); // array became a string
		
		} elseif ($data) {
			
			return $data;
		
		} else {
			
			return 0;
			
		}
	
	}
	
	public function db2php($data) {
	
		if (strstr($data, ',')) return explode(",", $data); else return $data;
	
	}
	
	public function db2text($data) {
	
		if (!$data) return '-';
		
		if (strstr($data, ',')) $ids = explode(",", $data); else $ids = (array) $data;
		
		$pages = '';
		
		foreach ($ids as $id) {
			
			$pages .= q2w3_select_page_conv::page_title($id).' // ';
						
		}
		
		$pages = substr_replace($pages, '', -4);
		
		return $pages;
				
	}
	
	
	/**
	 * Return page title
	 * 
	 * @param string $id Page id
	 * @return string Page title or false if title not found
	 */
	public static function page_title($id) {
		
		static $pages = array();
		
		if (!$pages) {
		
			$pages = q2w3_table_func::page_selectors();
		
		}
				
		if (isset($pages[$id]) && !empty($pages[$id])) {
			
			return $pages[$id];
			
		} else {
			
			return false;
			
		}
			
	}
	
}

?>