<?php

/**
 * @author Max Bond
 * 
 * Update table record action class
 *
 */
class q2w3_table_search extends _q2w3_table_action {
	

	
	public function html($object = false, $row_data = false) {
		
		return new q2w3_hidden_input(array('name'=>'action', 'value'=>$this->action_name()));
		
	}
	
	public static function action($plugin_id, $object_name) {
		
		session_start();
		
		$_SESSION['search'] = self::clear($_POST['propertie']);
				
	}
	
	protected static function clear($array) {
		
		foreach ($array as $index=>$value) {
			
			if (is_array($value)) {
				
				$sub_array = self::clear($value);
				
				if ($sub_array) $array[$index] = $sub_array; else unset($array[$index]); 
				
			} elseif (trim($value) == '') {
				
				unset($array[$index]);
				
			}
			
		}
		
		return $array;
		
	}
	
}

?>