<?php

/**
 * @author Max Bond
 * 
 * Describes basic method for table filters
 *
 */
abstract class _q2w3_table_filter {
	
	protected $plugin_id;
	

	
	public function __construct($plugin_id) {
		
		$this->plugin_id = $plugin_id;
		
	}
	
	abstract public function controls(); // filter controls // html
	
	abstract public function sql(); // filter action // sql 
	
}

?>