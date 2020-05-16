<?php

/**
 * @author Max Bond
 * 
 * Q2W3 Table system messages class
 * 
 * Uses sessions
 *
 */
class q2w3_table_sys_msg {
	
	
	
	public function __construct($msg) {
		
		if (!session_id()) @session_start(); // This will generate warning: 'Cannot send session cookie - headers already sent by' 

		if ($msg) $_SESSION['q2w3_table_sys_msg'][] = $msg; 
		
	}
	
	public static function display() {
	
		if (!session_id()) @session_start(); 
	
		if (key_exists('q2w3_table_sys_msg', $_SESSION) && is_array($_SESSION['q2w3_table_sys_msg'])) {
			
			$msgs = array_unique($_SESSION['q2w3_table_sys_msg']);

			unset($_SESSION['q2w3_table_sys_msg']);
			
		}  else {
			
			return false;
		
		}
			
		foreach ($msgs as $msg) {
			
			$res .= '<div class="updated fade"><p>'. $msg .'</p></div>'.PHP_EOL; // Standard WP sys msg style
			
		}
		
		return $res; 
		
	}
	
}

?>