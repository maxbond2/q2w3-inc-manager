<?php

/**
 * @author Max Bond
 * 
 * Describes basic data type converting functions
 *
 */
abstract class _q2w3_data_conv {

	protected $propertie;
	
	protected $plugin_id;
	
	public $db2text_values;
	


	/**
	 * @param object $propertie instance of _q2w3_table_obj_propertie
	 * @param unknown_type $plugin_id
	 */
	public function __construct($propertie = false, $plugin_id = false, $db2text_values = false) {
	
		if ($propertie) $this->propertie = $propertie;
		
		if ($plugin_id) $this->plugin_id = $plugin_id;
		
		if ($db2text_values) $this->db2text_values = $db2text_values;
	
	}


	/**
	 * Place here code for converting data before saving in to db
	 * If function is not overloaded in child class, no conversion occurs
	 * 
	 */
	public function php2db($data) {
	
		return $data;
	
	}
	
	/**
	 * Place here code for converting data retrived from db. Used for future proccessing in php (unserialize data and etc.)
	 * If function is not overloaded in child class, no conversion occurs
	 * 
	 */
	public function db2php($data) {
	
		return $data;
	
	}
	
	/**
	 * Place here code for converting data retrived from db. Used for future direct output to the screen (converts db data into readable form)
	 * If function is not overloaded in child class, no conversion occurs
	 * 
	 */
	public function db2text($data) {
	
		return $data;
	
	}

} 

?>