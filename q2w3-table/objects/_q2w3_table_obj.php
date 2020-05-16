<?php

/**
 * @author Max Bond
 * 
 * Abstract _q2w3_table_obj class. Describes basic methods for all _q2w3_table_class children classes
 *
 */
abstract class _q2w3_table_obj {

	protected static $plugin_id; // Plugin ID  

	
	
	/**
	 * Object init
	 * 
	 * @param string $plugin_id Plugin ID
	 * @param integer $object_id Object ID (db primary key value)
	 * @param string $conv_type 'php2db' | 'db2php' | 'db2text'
	 */
	public function __construct($plugin_id, $object_id = false, $conv_type = false) {
	
		self::$plugin_id = $plugin_id;		
		
		foreach ($this as $pname=>$propertie) {
					
			$this->$pname = new _q2w3_table_obj_propertie(); // create object properties
		
		}
		
		$this->init(); // init of properties
				
		if ($object_id && $conv_type) { // if params specified load object data from db
			
			$this->load_values_from_db($object_id, $conv_type);
		
		} else { // if not check for saved input
			
			$this->get_saved_input();
			
		}
			
	}

	
	
	abstract public function table(); // method for providing db table name
	
	abstract public function table_ver(); // method for providing db table version 
	
	abstract protected function input_name($name); // method for providing value for from <input name=""/> attribute
	
	abstract protected function init(); // object properties init method
	
	
	
	/**
	 * Method for verifing user input. Extend this function in a child class
	 * 
	 */
	protected function check_input_data() {
	
		return true;
	
	}
	
	/**
	 * Method for launching actions after data inserted into db. Extend this function in a child class
	 * 
	 */
	protected function after_insert() {
		
		return false;
		
	}
	
	/**
	 * Method for launching actions after data db data updated. Extend this function in a child class
	 * 
	 */
	protected function after_update() {
		
		return false;
		
	}
	
	/**
	 * Method for launching actions after db data deleted. Extend this function in a child class
	 * 
	 */
	protected function after_delete() {
		
		return false;
		
	}
	
	/**
	 * Method for providing javascript support for properties edit window. Extend this function in a child class
	 * 
	 */
	public function js() {
		
		return false;
		
	}
	
	/**
	 * Saves object data
	 * 
	 */
	public function save($show_sys_msg = true) {
	
		global $wpdb; // use wpdp object
						
		if ($this->check_input_data()) { // valid user input
			
			if ($this->id->val && $this->id->val != q2w3_table::NEW_MARKER) { // update
				
				if ($wpdb->update($this->table(), $this->values_array(), array($this->id->col_name=>$this->id->val)) !== false) { // ok	
		
					if ( $show_sys_msg ) new q2w3_table_sys_msg(__('Record updated', self::$plugin_id)); // message to user
					
					$this->after_update(); // actions after update
										
					return true;
		
				} else { // error
					
					if ( $show_sys_msg ) new q2w3_table_sys_msg(__('DB error:', self::$plugin_id).' '.$wpdb->last_error);
					
					return false;
					
				}	
					
			} elseif ($this->id->val && $this->id->val == q2w3_table::NEW_MARKER) { // insert
								
				$this->id->val = 0; // id value must be 0 when inserting data
				
				if ($wpdb->insert($this->table(), $this->values_array()) !== false) { // ok				
			
					$this->after_insert(); // action after insert
					
					if ( $show_sys_msg ) new q2w3_table_sys_msg(__('Record inserted', self::$plugin_id)); // message to user
					
					return $wpdb->insert_id; // return inserted id
					
				} else { // error
					
					if ( $show_sys_msg ) new q2w3_table_sys_msg(__('DB error:', self::$plugin_id).' '.$wpdb->last_error);
					
					return false;
					
				}
			
			}
		
		} else { // user input had errors
			
			$this->save_input(); // save data for faster input  
			
			return false;
			
		}
	
	}
	
	/**
	 * Deletes object data from db
	 * 
	 */
	public function delete($show_sys_msg = true) {
	
		global $wpdb; // use wpdb object
	
		if ($this->id->col_name && $this->id->val) { // id supplied
	
			if ($wpdb->query('DELETE FROM '. $this->table() .' WHERE '. $this->id->col_name .' = '. $this->id->val) !== false) { // ok
			
				$this->after_delete(); // actions after delete
				
				if ( $show_sys_msg ) new q2w3_table_sys_msg(__('Record(s) deleted', self::$plugin_id)); // msg for user
				
				return true;
			
			} else { // error
				
				if ( $show_sys_msg ) new q2w3_table_sys_msg(__('DB error:', self::$plugin_id).' '.$wpdb->print_error()); // error msg
				
				return false;
				
			}
			
		} else { // no id 
		
			return false;
		
		}
	
	}
	
	/**
	 * Returns array of object properties values
	 * 
	 */
	public function values_array() {
		
		foreach ($this as $pname=>$propertie) {
			
			$array[$propertie->col_name] = $propertie->val;
			
		}
		
		return $array;
	
	}
	
	/**
	 * Sets object properties values to NULL
	 * 
	 */
	public function clean_values() {
		
		foreach ($this as $pname=>$propertie) {
			
			$this->$pname->val = NULL;
			
		}
		
	}
	
	
	/**
	 * Loads object data from db
	 * 
	 * @param integer $object_id
	 * @param string $conv_type 'php2db' | 'db2php' | 'db2text'
	 */
	public function load_values_from_db($object_id, $conv_type) {
	
		global $wpdb;
	
		if ($object_id) {
			
			$data = $wpdb->get_row('SELECT * FROM '. $this->table() .' WHERE id = '.$object_id, ARRAY_A);
			
			if ( !empty($data) ) {
				
				$this->load_values_from_array($data, $conv_type);

			} else {

				return false;

			}
		
		}
	
	}
	
	
	/**
	 * Loads object data from array
	 * 
	 * @param array $data
	 * @param string $conv_type 'php2db' | 'db2php' | 'db2text'
	 */
	public function load_values_from_array($data, $conv_type) {
	
		if ($data) {
			
			foreach ($this as $pname=>$propertie) {
				
				if (key_exists($propertie->col_name, $data)) $value = $data[$propertie->col_name]; else $value = 0;
				
				if ($conv_type == 'php2db') { // if data passed for storing in db
						
					if (is_object($propertie->conv)) $this->$pname->val = $propertie->conv->php2db($value); else $this->$pname->val = $value;
						
					$this->prepare_data_db($pname);
					
				} elseif ($conv_type == 'db2php') { // if data passed from db for future proccess by php (also creating html forms)
						
					$this->prepare_data_php($pname, $value);
						
					if (is_object($propertie->conv)) $this->$pname->val = $propertie->conv->db2php($this->$pname->val); 
					
				} elseif ($conv_type == 'db2text') { // if data passed from db for direct screen output (used for table rows)
						
					$this->prepare_data_php($pname, $value);
						
					if (is_object($propertie->conv)) $this->$pname->val = $propertie->conv->db2text($this->$pname->val);
										
				}
							
			}
								
		}
	
	}
	
	/**
	 * Sanitizes propertie values before saving in db
	 * 
	 * @param string $pname Object propertie name
	 */
	protected function prepare_data_db($pname) {
	
		static $charset = '';
		
		if (!$charset) $charset = get_option('blog_charset');
		
		$this->$pname->val = htmlspecialchars(trim($this->$pname->val), ENT_QUOTES, $charset, false);
		
	}
	
	
	/**
	 * Rmoves slashes from propertie value passed from db
	 * 
	 * @param string $pname Object propertie name
	 * @param string $value Object propertie value
	 */
	protected function prepare_data_php($pname, $value) {
	
		$this->$pname->val = stripslashes($value);
			
	}

	/**
	 * Creates object db table 
	 * Uses dbDelta function for table upgrade tasks
	 * 
	 */
	public function create_table() {
	
		global $wpdb;
   
		$table_name = $this->table();
		
		$table_ver = $this->table_ver();
		
		$cur_table_ver = get_option($table_name.'_table_ver');
		
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name || $cur_table_ver != $table_ver) { // if table not exists or different table version 
      
			$sql = "CREATE TABLE $table_name (". $this->table_structure() .');'; // TYPE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci 
				
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	      
			dbDelta($sql);
	
			if ($cur_table_ver) { // table version option
				
				update_option($table_name.'_table_ver', $table_ver, '', 'no');
				
			} else {
	
				add_option($table_name.'_table_ver', $table_ver, '', 'no');
			
			}
			
						
			if ($cur_table_ver < 1.4) { // Update to db ver 1.4
				
				$sql = "UPDATE $table_name SET hide_from_admin = 'administrator' WHERE hide_from_admin = 1";
				
				$wpdb->query($sql);
				
			}
							
		}
				
	}
	
	
	/**
	 * Returns SQL code to be used in create table method
	 * Code formatted to comply dbDelta function requirements
	 * 
	 * @return string
	 */
	protected function table_structure() {
	
		foreach ($this as $key=>$prop) {
		
			$sql .= $prop->col_name .' '. $prop->col_type .','.PHP_EOL;
			
			if ($prop->col_index == _q2w3_table_obj_propertie::PRIMARY) {
			
				$primaries[] = $prop->col_name;
			
			} elseif ($prop->col_index == _q2w3_table_obj_propertie::UNIQUE) {
			
				$uniques[] = $prop->col_name;
			
			} elseif ($prop->col_index == _q2w3_table_obj_propertie::INDEX) {
			
				$indexes[] = $prop->col_name;
			
			}
		
		}
		
		if (is_array($primaries)) {
		
			$index_keys[0] = 'PRIMARY KEY  (';
		
			$i = 0;
			
			foreach ($primaries as $primary) {
		
				$i++;
		
				$index_keys[0] .= $primary;
		
				if ($i == count($primaries)) $index_keys[0] .= ')'; else $index_keys[0] .= ',';
		
			}
		
		}
		
		if (is_array($uniques)) {
		
			$index_keys[1] = 'UNIQUE KEY '. $uniques[0] .' (';
		
			$i = 0;
		
			foreach ($uniques as $unique) {
		
				$i++;
		
				$index_keys[1] .= $unique;
		
				if ($i == count($uniques)) $index_keys[1] .= ')'; else $index_keys[1] .= ',';
		
			}
		
		}
		
		if (is_array($indexes)) {
		
			$index_keys[2] = 'KEY '. $indexes[0] .' (';
		
			$i = 0;
		
			foreach ($indexes as $index) {
		
				$i++;
		
				$index_keys[2] .= $index;
		
				if ($i == count($indexes)) $index_keys[2] .= ')'; else $index_keys[2] .= ',';
		
			}
			
		}
			
		return PHP_EOL.$sql.implode(','.PHP_EOL, $index_keys).PHP_EOL;
			
	}
	
	/**
	 * Saves user input in a session variable
	 * 
	 */
	protected function save_input() {
		
		$_SESSION['q2w3_table_tmp_data'] = $this->values_array();
		
	}
	
	
	/**
	 * Loads object data from saved input
	 * Performed only once 
	 * 
	 */
	protected function get_saved_input() {
		
		if (key_exists('id', $_GET) && $_GET['id'] == q2w3_table::NEW_MARKER && isset($_SESSION['q2w3_table_tmp_data']) && is_array($_SESSION['q2w3_table_tmp_data']) && !empty($_SESSION['q2w3_table_tmp_data'])) {
		
			$this->load_values_from_array($_SESSION['q2w3_table_tmp_data'], 'db2php');
		
			unset($_SESSION['q2w3_table_tmp_data']);
		
		}
		
	}
	
}

?>