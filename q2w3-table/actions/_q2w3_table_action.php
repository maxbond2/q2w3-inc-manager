<?php

/**
 * @author Max Bond
 * 
 * Q2w3 Table action abstract class
 *
 */
abstract class _q2w3_table_action {
	
	protected $plugin_id;
	
	protected $wp_nonce;
	
	protected $action_page;
	
		
	
	/**
	 * @param string $plugin_id
	 * @param string $action_page GET or POST handler url
	 */
	public function __construct($plugin_id, $action_page = false) {

		$this->plugin_id = $plugin_id;
		
		$this->wp_nonce = wp_create_nonce('q2w3_table'); // create nonce for get actions
		
		$this->action_page = $action_page;
		
	}
	
	/**
	 * Returns action name (object class name)
	 * 
	 * @return string
	 */
	public function action_name() {
		
		return get_class($this);
		
	}
	
	/**
	 * Describes action control elements
	 * 
	 * @param object $object instance of _q2w3_table_obj class
	 * @param array $row_data
	 */
	abstract public function html($object = false, $row_data = false);
	
	/**
	 * Place here form data processing code
	 * 
	 * @param string $plugin_id
	 * @param string $object_name
	 */
	public static function action($plugin_id, $object_name) {
		
		
		
	}

	public static function ajax() {
		
		if ( !check_admin_referer('q2w3_table','wp_nonce') ) wp_die('Security check failed'); 

		$action = $_REQUEST['action'];

		$action::action(q2w3_inc_manager::ID, $_REQUEST['object']);
		
		wp_redirect($_SERVER['HTTP_REFERER']);
				
		exit();

	}
	
}

?>