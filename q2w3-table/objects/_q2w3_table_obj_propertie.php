<?php

/**
 * @author Max Bond
 * 
 * This class describes properties of _q2w3_table_obj class properties
 *
 */
class _q2w3_table_obj_propertie {

	public $name; // Propertie name // will be displayed to user
	
	public $col_name; // database column name
	
	public $col_type; // database column type
	
	public $col_index; // database column index
	
		const PRIMARY = 1; // ID of PRIMARY KEY index
	
		const UNIQUE = 2; // ID of UNIQUE KEY index
		
		const INDEX = 3; // ID of INDEX index
	
	public $val; // propertie value
	
	public $input_values; // <select> options array is stored here
	
	public $input; // input object // instance of _q2w3_input class
	
	public $conv; // data conver object // instance of _q2w3_data_conv class
	
	public $help; // Propertie help // mat be displayed for user

	public $table_view; // if set to true table column will be displayed
	
	public $table_view_change; // if set to true $table_view propertie can be set by user
	
	public $single_view; // if set to true value input form element will be displayed at the object edit window
	
	public $search; // search this field or not

}

?>