<?php

/**
 * @author Max Bond
 * 
 * Special class for converting data for <select> element with multiple="true" option
 *
 */
class q2w3_checkbox_multi_conv extends _q2w3_data_conv {



	public function php2db($data) {
	
		if (is_array($data) && !empty($data)) {
			
			return implode(",", array_keys($data)); // array became a string. array_keys function needed because selected checkbox ids in array keys
		
		} elseif ($data) {
			
			return $data;
		
		} else {
			
			return 0;
			
		}
	
	}
	
	public function db2php($data) {
		
		if (strpos($data, ',') !== false) $data = explode(',', $data); 
		
		if (!empty($data)) return (array)$data; else return false;
	
	}
	
	public function db2text($data) {
	
		if (!$data) return '-';
		
		if (!$this->db2text_values) return 'missing db2text_values';
		
		if (strstr($data, ',')) $ids = explode(",", $data); else $ids = (array)$data;
		
		foreach ($ids as $id) {
			
			$res[] = $this->db2text_values[$id];
						
		}
		
		$res = implode(', ', $res);
		
		return $res;
				
	}
		
}

?>