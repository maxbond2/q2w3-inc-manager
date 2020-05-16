<?php

/**
 * @author Max Bond
 * 
 * Checkbox data convert class
 *
 */
class q2w3_checkbox_conv extends _q2w3_data_conv {

	
	
	public function php2db($data) {
			
		if(!$data) return 0; else return 1; // if no data passed return 0
	
	}
	
	public function db2text($data) {
	
		if($data) return __('Yes', $this->plugin_id); else return __('No', $this->plugin_id);
		
	}

}

?>