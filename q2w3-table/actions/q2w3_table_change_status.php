<?php

/**
 * @author Max Bond
 * 
 * Change include status action class
 *
 */
class q2w3_table_change_status extends _q2w3_table_action {
	

	
	public function html($object = false, $row_data = false) {
		
		if ($row_data[$object->status->col_name] == q2w3_include_obj::STATUS_ACTIVE) {
		
			$status_change = '<span class="0"><a href="'. $this->action_page .'?action='. $this->action_name() .'&amp;set='. q2w3_include_obj::STATUS_DISABLED .'&amp;object='. get_class($object) .'&amp;id='. $object->id->val .'&amp;wp_nonce='. $this->wp_nonce .'" >'. __('Deactivate', $this->plugin_id) .'</a>'.PHP_EOL;
		
		} elseif ($row_data[$object->status->col_name] == q2w3_include_obj::STATUS_DISABLED) {
		
			$status_change = '<span class="0"><a href="'. $this->action_page .'?action='. $this->action_name() .'&amp;set='. q2w3_include_obj::STATUS_ACTIVE .'&amp;object='. get_class($object) .'&amp;id='. $object->id->val .'&amp;wp_nonce='. $this->wp_nonce .'" >'. __('Activate', $this->plugin_id) .'</a>'.PHP_EOL;
		
		}
		
		return $status_change;
				
	}
	
	public static function action($plugin_id, $object_name) {

		$id = $_GET['id'];
		
		$set = $_GET['set'];
		
		if ($id && $set) {
		
			$object = new $object_name($plugin_id);
		
			$object->id->val = $id;
		
			$object->change_status($set);
		
		}
		
	}
	
}

?>