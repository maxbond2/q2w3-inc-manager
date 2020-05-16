<?php

/**
 * @author Max Bond
 * 
 * Activate selected includes action class
 *
 */
class q2w3_table_activate_selected extends _q2w3_table_action {
	
	
	
	public function html($object = false, $row_data = false) {
		
		return array($this->action_name(), __('Activate', $this->plugin_id));
		
	}
	
	public static function action($plugin_id, $object_name) {
		
		$checked = $_POST['checked'];
	
		if (is_array($checked)) {
			
			$object = new q2w3_include_obj($plugin_id);
	
			foreach ($checked as $id=>$no_value) {
			
				$object->id->val = $id;
		
				$object->change_status(q2w3_include_obj::STATUS_ACTIVE);
					
			}
				
		}
		
	}
	
}

?>