<?php

/**
 * @author Max Bond
 * 
 * Q2w3 Table support functions class
 *
 */
class q2w3_table_func {
	
	
	
	/**
	 * Returns full path to wp-load.php file   
	 * 
	 */
	public static function wp_load() {
	
		$wp_root = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
		
		if (file_exists($wp_root.'/wp-load.php')) {
	
			return $wp_root.'/wp-load.php';
	
		} else {
		
			exit('Can not find wp-load.php!');
		
		}
			
	}

	/**
	 * Returns q2w3 table installation folder url
	 * 
	 * @return string
	 */
	public static function folder_url() {
		
		return WP_PLUGIN_URL.'/q2w3-inc-manager/q2w3-table/';
		
	}
	
	/**
	 * Loads css file for current db table object
	 * 
	 * This css used to define styles for object properties window
	 * 
	 */
	public static function css_js_load() {
		
		$folder = plugins_url().'/q2w3-inc-manager/q2w3-table';
							
		wp_enqueue_style('q2w3_table_css', $folder .'/_css/q2w3_table.css');
		
		wp_enqueue_style('q2w3_obj_css', $folder . '/_css/q2w3_obj.css');
		
		if (key_exists('id', $_GET) && $_GET['id']) {
			
			wp_enqueue_style('q2w3_table_tooltip_css', $folder .'/_js/tooltip/tooltip.css');
	
			wp_enqueue_script('q2w3_table_tooltip_js', $folder .'/_js/tooltip/tooltip.js');
			
		}
	
	}
			
	/**
	 * Seachs for $old_value in $_SERVER['HTTP_REFERER'] and replaces it with $new_value
	 * 
	 * @param string $old_value
	 * @param string $new_value
	 */
	public static function change_referer($old_value, $new_value = '') {

		$_SERVER["HTTP_REFERER"] = str_replace($old_value, $new_value, $_SERVER["HTTP_REFERER"]);

	}

	
	/**
	 * Changes query string
	 * 
	 * @param string $get_var $_GET variable name
	 * @param mixed $value Variable value to be set
	 * @param string $qstring Query string to be changed if not specified takes $_SERVER['QUERY_STRING']
	 * @param string $delete_after_var $_GET variable name. If specified all data afte this variable will be deleted 
	 * @return string
	 */
	public static function change_qstring($get_var, $value=false, $qstring=false, $delete_after_var=false) {
	
		if (!$qstring) {
		
			$qstring = $_SERVER['QUERY_STRING'];
		
		} 
	
		if(!$qstring) {
			
			if ($value) {
			
				return $get_var.'='.$value;
				
			} else {
				
				return NULL;
				
			}
			
		}
			
		parse_str(str_replace('&amp;', '&', $qstring), $vars);

		if (!array_key_exists($get_var, $vars)) {
			
			if ($value) $val = "&amp;$get_var=$value"; else $val = false;
			
			return $qstring.$val;
			
		} else {
			
			$vars[$get_var] = $value;
			
		}
		
		foreach ($vars as $key=>$value) {
			
			if ($delete_after_var) {
				
				$value = false;
				
			}
			
			if (!$value) unset($vars[$key]);
			
			if ($delete_after_var && $key == $get_var) {
				
				$delete_after = true;
				
			}
			
		}
		
		return http_build_query($vars,'','&amp;');
		
	}
	
	public static function change_ref_after_del($id_col_name, $id) {
		
		q2w3_table_func::change_referer('&'. $id_col_name .'='.$id, ''); // remove deleted id from query string
	
		if (!(isset($_GET['last_record']) || isset($_POST['last_record']))) return; // continue if deleted record is last

		$page = strstr($_SERVER['HTTP_REFERER'], q2w3_table_page_filter::VAR_NAME); 

		if ($page) { // if page var is set

			$next_var = strpos($page, '&');
			
			if ($next_var) $page = substr($page, 0, $next_var); // remove other vars from string
			
			$page_num = explode('=', $page);
		
			$page_num = intval($page_num[1]); // page number
			
		} else { // there is only one page
			
			$page_num = 1;
			
		}
		
		if ($page_num > 1) { // if this is not first page reset page number to 1

			q2w3_table_func::change_referer('&'.$page, '');
					
		} else { // if this is first page reset location filter var
				
			$location = strstr($_SERVER['HTTP_REFERER'], q2w3_table_location_filter::VAR_NAME);
		
			$next_var = strpos($location, '&');
			
			if ($next_var) $location = substr($location, 0, $next_var);
			
			if ($location) {
			
				q2w3_table_func::change_referer('&'.$location, '');
					
			}
		
		}
		
	}
	
	/**
	 * Returns plugin ID without digits
	 *  
	 * @param string $plugin_id
	 */
	public static function safe_plugin_id($plugin_id) {
		
		return preg_replace('/\d/', '', $plugin_id);
		
	}
			
	public static function page_selectors() {
		
		global $wp_post_types, $wp_taxonomies;
		
		$res = q2w3_include_obj::page_selectors();
		
		foreach ($wp_post_types as $post_type) {
			
			if (!in_array($post_type->name, q2w3_inc_manager::$restricted_post_types)) {
				
				$res += array($post_type->name.'_all' => __('All', q2w3_inc_manager::ID).' '.$post_type->labels->name);
				
				$res += array('post_type_archive_'.$post_type->name =>  __('Archive', q2w3_inc_manager::ID).': '.$post_type->labels->name);

				$selectors = self::select_post_type($post_type->name);
				
				if (!empty($selectors))	$res += $selectors;
								
			}
			
		}
		
		foreach ($wp_taxonomies as $taxonomy) {
			
			if (!in_array($taxonomy->name, q2w3_inc_manager::$restricted_taxonomies)) {
				
				$res += array($taxonomy->name.'_all' => __('All', q2w3_inc_manager::ID).' '.$taxonomy->labels->name);

				$selectors = self::select_taxonomy($taxonomy->name);
				
				if (!empty($selectors))	$res += $selectors;
				
			}
			
		}
		
		$formats_orig = get_post_format_strings();
		
		foreach ($formats_orig as $fkey=>$fname) {
			
			$res['post_format_'.$fkey] = __('PF', $plugin_id).': '.$fname;
			
		}
		
		return $res;
		
	} 
	
	public static function select_post_type($post_type) {

		global $wpdb;
		
		$array_key_prefix = $post_type.'_';
	
		$posts = $wpdb->get_results('SELECT ID, post_title FROM '. $wpdb->posts ." WHERE post_type = '$post_type' AND post_status = 'publish' ORDER BY post_date DESC", ARRAY_N);
			
		if (is_array($posts) && !empty($posts)) {

			foreach ($posts as $post) {

				$output_array[$array_key_prefix.$post[0]] = $post[1];

			}
				
		}
		
		return $output_array;

	}
	
	public static function select_taxonomy($taxonomy) {
		
		global $wpdb;
		
		$array_key_prefix = $taxonomy.'_';
	
		$tags = $wpdb->get_results('SELECT t.term_id, t.name FROM '. $wpdb->terms .' t, '. $wpdb->term_taxonomy ." tt WHERE tt.taxonomy = '$taxonomy' AND tt.term_id = t.term_id AND tt.count > 0 ORDER BY tt.count DESC", ARRAY_N);
			
		if (is_array($tags) && !empty($tags)) {

			foreach ($tags as $tag) {

				$output_array[$array_key_prefix.$tag[0]] = $tag[1];

			}

		}
		
		return $output_array;
		
	}
	
	public static function wp_user_roles() {
		
		global $wp_roles;
		
		if ( is_object($wp_roles) ) {

			$all_roles = $wp_roles->roles;
			
			$editable_roles = apply_filters('editable_roles', $all_roles);
			
			if (is_array($editable_roles)) {
			
				foreach( $editable_roles as $role => $details ) {
				
					$name = translate_user_role($details['name'] );
			
					$roles[$role] = $name;
				
				}
			
			}
		
		}
		
		$roles['q2w3_visitor'] = __('Visitor', q2w3_inc_manager::ID);

		return $roles;
			
	}
	
}

?>