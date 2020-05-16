<?php

/**
 * @author Max Bond
 * 
 * Update table record action class
 *
 */
class q2w3_table_update_row extends _q2w3_table_action {
	

	
	public function html($object = false, $row_data = false) {
		
		return new q2w3_hidden_input(array('name'=>'action', 'value'=>$this->action_name()));
		
	}
	
	public static function action($plugin_id, $object_name) {
		
		$object = new $object_name($plugin_id);
		
		$object->load_values_from_array($_POST['propertie'], 'php2db');
		
		$object->save();
				
	}
	
}

?>