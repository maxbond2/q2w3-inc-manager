<?php

/**
 * @author Max Bond
 * 
 * Class for input text conversion
 *
 */
class q2w3_text_conv extends _q2w3_data_conv {

	
	
	public function php2db($data) {
	
		if(!$data) return 0; else return $data; 
	
	}
	
	public function db2php($data) {
	
		if(!$data) return ''; else return $data;
	
	}
	
}

?>