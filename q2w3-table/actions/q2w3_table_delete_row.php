<?php

/**
 * @author Max Bond
 *
 * Delete table record action class
 *
 */
class q2w3_table_delete_row extends _q2w3_table_action {
	
	
	
	public function html($object = false, $row_data = false, $last_record = false) {
				
		if ($last_record) $last_record = '&amp;last_record=1';
		
		return '<span class="0"><a href="'. $this->action_page .'?action='. $this->action_name() .'&amp;object='. get_class($object) .'&amp;id='. $object->id->val.$last_record .'&amp;wp_nonce='. $this->wp_nonce .'" class="delete_row">'. __('Delete', $this->plugin_id) .'</a></span>'.PHP_EOL;
		
	}

	public static function action($plugin_id, $object_name) {
		
		$id = intval($_GET['id']);
		
		if ($id) {
		
			$object = new $object_name($plugin_id);
		
			$object->id->val = $id;
		
			if ($object->delete()) {
				
				q2w3_table_func::change_ref_after_del($object->id->col_name, $id);
						
			}
		
		}
		
	}
	
}

?>