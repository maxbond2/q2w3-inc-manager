<?php
/*
Plugin Name: Code Insert Manager (Q2W3 Inc Manager)
Plugin URI: http://www.q2w3.ru/code-insert-manager-wordpress-plugin/
Description: This plugin allows you to insert html, css, javascript and PHP code to public wordpress pages.
Version: 2.3.3
Author: Max Bond
Author URI: http://www.q2w3.ru/
*/

// TODO shortcodes execution without php

if (defined('ABSPATH')) { // makes shure that the following functions will be run inside WordPress only

	require_once 'q2w3-table/q2w3_table_func.php';
		
	require_once 'q2w3-table/q2w3_table_load.php'; // loads q2w3_table library
	
	require_once 'q2w3_inc_manager_widget.php'; // loads inc manager widget class
	
	// Hooks 
	
	register_activation_hook(__FILE__, array( 'q2w3_inc_manager', 'activate' )); // registers activation function

	add_action('widgets_init', array( 'q2w3_inc_manager', 'widget_incs' )); // widgets initialization both for admin and public pages
	
	add_shortcode('include', array( 'q2w3_inc_manager', 'shortcode_incs' )); // shortcode init
	
	add_shortcode('INCLUDE', array( 'q2w3_inc_manager', 'shortcode_incs' ));	
	
	if (is_admin()) { // admin hooks
		
		q2w3_inc_manager::load_language(); 
		
		add_action('admin_menu', array( 'q2w3_inc_manager', 'reg_menu' ));
		
		add_filter('plugin_action_links_'.plugin_basename(__FILE__), array( 'q2w3_inc_manager','reg_control_links' ));  
		
		add_filter('set-screen-option', array( 'q2w3_inc_manager', 'screen_options_save' ), 10, 3);
					
	} else { // public hooks
		
		add_action('wp_head', array( 'q2w3_inc_manager', 'cur_page' ));
		
		add_action('wp_head', array( 'q2w3_inc_manager', 'header_incs' ));
	
		add_action('wp_footer', array( 'q2w3_inc_manager', 'footer_incs' ));
		
		add_action('loop_start', array( 'q2w3_inc_manager', 'b_page_content_incs' ));
	
		add_action('loop_end', array( 'q2w3_inc_manager', 'a_page_content_incs' ));
	
		add_filter('the_content', array( 'q2w3_inc_manager', 'b_post_content_incs' ));
		
		add_filter('the_content', array( 'q2w3_inc_manager', 'a_post_excerpt_incs' ));
	
		add_filter('the_content', array( 'q2w3_inc_manager', 'a_post_content_incs' ));
		
		add_filter( 'comment_text', array( 'q2w3_inc_manager', 'parse_shortcodes_comment' ));
		
	}
	
}



if (class_exists('q2w3_inc_manager', false)) return; // if class allready loaded returns control to the main script

/**
 * @author Max Bond
 *
 * Main plugin class. All functions are static. Used PHP 5 OOP.
 * 
 */
class q2w3_inc_manager {

	const ID = 'q2w3_inc_manager'; // Plugin ID, also used as a Text Domain name  
		
	const NAME = 'Code Insert Manager'; // Plugin name
		
	const LANG_DIR = 'languages'; // Plugin languages folder
		
		
	const PHP_VER = '5.2.0'; // Minimum PHP version
	
	const WP_VER = '3.1'; // Minimum WordPress version
	
	
	public static $default_options = array(
			'post_types' => array(
				'post' => array('enable' => 'on', 'expand' => 'on'),
				'page' => array('enable' => 'on', 'expand' => 'on')
				),
			'taxonomies' => array(
				'post_format' => array('enable' => 'on', 'expand' => 'on'),
				'category' => array('enable' => 'on', 'expand' => 'on'),
				'post_tag' => array('enable' => 'on', 'expand' => 'on')
				)
			);
			
	public static $default_post_types = array('post', 'page');
			
	public static $restricted_post_types = array('attachment', 'revision', 'nav_menu_item');
	
	public static $default_taxonomies = array('category', 'post_tag');
		
	public static $restricted_taxonomies = array('link_category', 'nav_menu', 'post_format');
  			

	protected static $plugin_page;
	
	protected static $table; // @var self::$table q2w3_table
	
	protected static $object; // @var self::$object q2w3_include_obj
	
		
	
	/**
	 * Returns db table object
	 * 
	 * @return object instance of _q2w3_table_obj
	 */
	public static function object() {
		
		if (!self::$object) self::$object = new q2w3_include_obj(self::ID);
		
		return self::$object;
		
	}
	
	/**
	 * Returns table object
	 * 
	 * @return object instance q2w3_table
	 */
	public static function table() {
		
		if (!self::$table) {
			
			$inc_obj = self::object();
			
			if (MULTISITE == true) $inc_obj->create_table(); // create tables for wp network sites
	
			$table = new q2w3_table(self::ID, $inc_obj);
			
			$table->get_handler = WP_PLUGIN_URL.'/q2w3-inc-manager/q2w3-table/q2w3_get.php';
	
			$table->post_handler = WP_PLUGIN_URL.'/q2w3-inc-manager/q2w3-table/q2w3_post.php';
	
			$table->reg_filter(new q2w3_table_status_filter(self::ID)); // register status filter
			
			$table->reg_filter(new q2w3_table_location_filter(self::ID)); // register location filter

			$table->reg_filter(new q2w3_table_search_title_filter(self::ID, $inc_obj)); // register search filter
			
			$table->reg_bulk_action(new q2w3_table_activate_selected(self::ID)); // register bulk activate table action
		
			$table->reg_bulk_action(new q2w3_table_disable_selected(self::ID)); // register bulk deactivate table action
		
			$table->reg_row_action(new q2w3_table_change_status(self::ID, $table->get_handler)); // register single row action
		
			$table->set_order(array($inc_obj->location->col_name=>'ASC', $inc_obj->priority->col_name=>'ASC', $inc_obj->id->col_name=>'ASC')); // set table sort order
				
			self::$table = $table;
			
		}
		
		return self::$table; 
		
	}
	
	
	/**
	 * Activate plugin.
	 * Checks PHP and WP versions. Creates include table
	 * 
	 */
	public static function activate() {
	
		if (self::php_version_check() && self::wp_version_check()) { // check php and wp version
	
			$object = self::object();
			
			$object->create_table(); // create db table
			
		}
	
	}
	
	/**
	 * PHP version check
	 * 
	 * @return bool True if check passed
	 */
	public static function php_version_check() {
	
		if (version_compare(phpversion(), self::PHP_VER, '<')) {
    
			deactivate_plugins(plugin_basename(__FILE__)); // deactivates plugin if incompatible php version used
    
			wp_die(__('PHP version', self::ID) . ' ('. PHP_VERSION .') ' . __('is incompatible with this plugin. You need at least version', self::ID) . ' ' . self::PHP_VER);

		} else {
		
			return true;
		
		}
	
	}
	
	/**
	 * WP version check
	 * 
	 * @return bool True if check passed
	 */
	public static function wp_version_check() {
	
		global $wp_version;
		
		if (version_compare($wp_version, self::WP_VER, '<')) { // used php version_compare function because wp version numbers structure very similar to php
    
			deactivate_plugins(plugin_basename(__FILE__)); // deactivates plugin if incompatible wp version used
    
			wp_die(__('Wordpress version', self::ID) . ' ('. $wp_version .') ' . __('is incompatible with this plugin. You need at least version', self::ID) . ' ' . self::WP_VER);
		
		} else {
		
			return true;
		
		}
	
	}
	
	/**
	 * Creates link to plugin settings page in the main menu. 
	 * 
	 */
	public static function reg_menu() {
		
		$access_level = 'activate_plugins'; // admins and superadmins only
	
		add_menu_page('Code Insert', __('Code Insert', self::ID), $access_level, 'q2w3-inc-manager', array(__CLASS__,'main_page'), WP_PLUGIN_URL.'/q2w3-inc-manager/q2w3-table/menu-icon.gif');
		
		self::$plugin_page = add_submenu_page('q2w3-inc-manager', self::NAME, __('Inserts', self::ID), $access_level, 'q2w3-inc-manager', array(__CLASS__,'main_page'));
		
		add_submenu_page('q2w3-inc-manager', self::NAME, __('Add New', self::ID), $access_level, 'q2w3-inc-manager&amp;id=_new_', array(__CLASS__,'main_page'));
		
		add_submenu_page('q2w3-inc-manager', self::NAME, __('Settings', self::ID), $access_level, 'q2w3-inc-manager-settings', array(__CLASS__,'settings_page'));
		
		add_action('manage_'. self::$plugin_page .'_columns', array(__CLASS__, 'screen_options'));
		
		add_action('contextual_help_list', array(__CLASS__, 'help')); // get_current_screen()->add_help_tab()
		
		if (isset($_GET["page"]) && $_GET["page"] == 'q2w3-inc-manager') {
			
			q2w3_table_func::css_js_load(); // css and js for settings page

			if (key_exists('id', $_GET) && $_GET['id']) {
				
				wp_enqueue_style('thickbox'); 
			  
				wp_enqueue_script('thickbox');
				
			}
			
		}
				
	}
	
	public static function screen_options($columns) {
		
		self::table();
		
		return self::$table->user_settings_form();
		
	} 
	
	public static function screen_options_save($value, $option_name, $new_settings) {
		
		self::table();
		
		return self::$table->user_settings_save($new_settings);
				
	}
	
	/**
	 * Adds help for plugin settings page
	 * 
	 * @param array $help_content
	 * @return array
	 */
	public static function help($help_content) {
		
		$help_content[self::$plugin_page] = '<a href="http://www.q2w3.ru/code-insert-manager-wordpress-plugin/">'. __('Q2W3 Inc Manager Homepage', self::ID) .'</a>';
			
		return $help_content;
		
	}
	
	/**
	 * Modifies plugin control links
	 * 
	 * @param $links
	 * @return links array
	 */
	public static function reg_control_links($links) {
	
		if (array_key_exists('deactivate',$links)) {
			
			$index = 'deactivate'; // compatibility with WP 3.0
			
		} else {
			
			$index = 0;
			
		}
		
		$links[$index] = '<a href="admin.php?page=q2w3-inc-manager&amp;deactivate=true">'. __('Deactivate') .'</a>'; // changes default plugin deactivation link // now it points to my custom plugin deactivation page
		
		//$settings_link = '<a href="options-general.php?page='.plugin_basename(__FILE__).'">'. __('Settings') .'</a>'; // Direct link to plugin settings page
		
		//array_unshift($links,$settings_link); // adds settings link before other links
		
		return $links;
	
	}
	
	/**
	 * Loads plugin language file
	 * 
	 */
	public static function load_language() {
	
		$currentLocale = get_locale();
	
		if (!empty($currentLocale)) {
				
			$moFile = dirname(__FILE__).'/'.self::LANG_DIR.'/'.$currentLocale.".mo";
		
			if (@file_exists($moFile) && is_readable($moFile)) load_textdomain(self::ID, $moFile);
			
		}
	
	}
	
	
	
	/**
	 * Prints includes table page
	 *  
	 */
	public static function main_page() {
	
		if (key_exists('deactivate', $_GET) && $_GET['deactivate'] == 'true') {
			
			self::deactivate_page(); // load deactivation page
			
		} else {
			
			self::table();
			
			$res = '<div class="wrap">'.PHP_EOL;
			
			$res .= '<h2>'. self::NAME .'</h2>'.PHP_EOL;
					
			$res .= self::$table->html();
			
			$res .= '<ul class="subsubsub"><li>'. __('Need help? Visit', self::ID) .' <a href="http://www.q2w3.ru/code-insert-manager-wordpress-plugin/">'. __('Plugin Homepage', self::ID) .'</a></li></ul>'.PHP_EOL;
			
			$res .= '</div><!--wrap-->'.PHP_EOL;
			
			echo $res; // output
			
		}
		
	}
	
	/**
	 * Prints plugin settings page
	 *  
	 */
	public static function settings_page() {
	
		$options = get_option(self::ID);	
		
		if (!$options) $options = self::$default_options;
		
		$res = '<div class="wrap">'.PHP_EOL;
			
		$res .= '<h2>'. self::NAME .' &raquo; '. __('Settings', self::ID) .'</h2>'.PHP_EOL;
		
		
		
		$res .= __('Here you can select post types and taxonomies. Selected items will be available for "Insert" and "Exclude" filters. "Expand" option allows you to select individual posts/pages for each Post Type or Taxonomy group.', self::ID);

		$res .= '<form method="post" action="options.php">'.PHP_EOL;
		
		$res .= wp_nonce_field('update-options', '_wpnonce', true, false).PHP_EOL;
		
		$res .= '<br/>'.PHP_EOL;
		
		// Shortcodes in comments option
		
		$res .= '<input type="checkbox" name="'. self::ID.'[shortcodes_in_comments]" '. checked($options['shortcodes_in_comments'], 'on', false) .' /> '. __('Enable shortcodes in comments. This option affects not all shortcodes, only [include] shortcode used by Code Insert Manager', self::ID).PHP_EOL;
		
		// Post types
						
		$res .= '<h3>'. __('Post Types', self::ID) .'</h3>'.PHP_EOL;
		
		$post_types = get_post_types('','objects');

		foreach ($post_types  as $post_type ) {
    
			if (!in_array($post_type->name, self::$restricted_post_types)) {
				
				if (in_array($post_type->name, self::$default_post_types)) {
					
					$disabled = 'disabled="disabled"';

					$res .= '<input type="hidden" name="'. self::ID.'[post_types]['. $post_type->name .'][enable]" value="on"/>'.PHP_EOL;
				
				} else {
					
					$disabled = false;
					
				}
				
				$res .= '<div style="margin: 5px 0px 0px 10px">'.PHP_EOL;
				
				$res .= '<div class="parent_checkbox">';
				
				$res .= '<input type="checkbox" name="'. self::ID.'[post_types]['. $post_type->name .'][enable]" '. checked($options['post_types'][$post_type->name]['enable'], 'on', false) . $disabled .' /> <strong>'. $post_type->labels->name .'</strong> ('. $post_type->name .') <br/>';
				
				$res .= '</div>'.PHP_EOL;
				
				$res .= '<div class="child_checkbox" style="margin-left: 20px">';
				
				$res .= '<input type="checkbox" name="'. self::ID.'[post_types]['. $post_type->name .'][expand]" '. checked($options['post_types'][$post_type->name]['expand'], 'on', false) .' /> '. __('Expand', self::ID);

				$res .= '</div>'.PHP_EOL;
				
				$res .= '</div>'.PHP_EOL;
			
			}
  
		}
		
		// Taxonomies

		$res .= '<h3>'. __('Taxonomies', self::ID) .'</h3>'.PHP_EOL;
		
		// Post Formats
		
		$res .= '<div style="margin: 5px 0px 0px 10px">'.PHP_EOL;
				
		$res .= '<div class="parent_checkbox">';
				
		$res .= '<input type="checkbox" name="'. self::ID.'[taxonomies][post_format][enable]" '. checked($options['taxonomies']['post_format']['enable'], 'on', false) .' /> <strong>'. __('Post Formats') .'</strong> (post_format)';
				
		$res .= '</div>'.PHP_EOL;
				
		$res .= '<div class="child_checkbox" style="margin-left: 20px">';
				
		$res .= '<input type="checkbox" name="'. self::ID .'[taxonomies][post_format][expand]" checked="checked" disabled="disabled" /> '. __('Expand', self::ID);

		$res .= '</div>'.PHP_EOL;
				
		$res .= '</div>'.PHP_EOL;
		
		$taxonomies = get_taxonomies('', 'objects');
		
		foreach ($taxonomies  as $taxonomy ) {
    
			if (!in_array($taxonomy->name, self::$restricted_taxonomies)) {
				
				if (in_array($taxonomy->name, self::$default_taxonomies)) {
					
					$disabled = 'disabled="disabled"';

					$res .= '<input type="hidden" name="'. self::ID.'[taxonomies]['. $taxonomy->name .'][enable]" value="on"/>'.PHP_EOL;
				
				} else {
					
					$disabled = false;
					
				}
				
				$res .= '<div style="margin: 5px 0px 0px 10px">'.PHP_EOL;
				
				$res .= '<div class="parent_checkbox">';
				
				$res .= '<input type="checkbox" name="'. self::ID .'[taxonomies]['. $taxonomy->name .'][enable]" '. checked($options['taxonomies'][$taxonomy->name]['enable'], 'on', false) . $disabled .' /> <strong>'. $taxonomy->labels->name .'</strong> ('. $taxonomy->name .')';
				
				$res .= '</div>'.PHP_EOL;
				
				$res .= '<div class="child_checkbox" style="margin-left: 20px">';
				
				$res .= '<input type="checkbox" name="'. self::ID .'[taxonomies]['. $taxonomy->name .'][expand]" '. checked($options['taxonomies'][$taxonomy->name]['expand'], 'on', false) .' /> '. __('Expand', self::ID);

				$res .= '</div>'.PHP_EOL;
				
				$res .= '</div>'.PHP_EOL;
			
			}
  
		}
		
		$res .= '<input type="hidden" name="action" value="update" />'.PHP_EOL;
		
		$res .= '<input type="hidden" name="page_options" value="'. self::ID .'" />'.PHP_EOL;
		
		$res .= '<p class="submit"><input type="submit" class="button-primary" value="'. __('Save Changes') .'" /></p>'.PHP_EOL;
		
		$res .= '</form>'.PHP_EOL;
		
		//$res .= '<ul class="subsubsub"><li>'. __('Need help? Visit', self::ID) .' <a href="http://www.q2w3.ru/2009/12/06/824/">'. __('Plugin Homepage', self::ID) .'</a></li></ul>'.PHP_EOL;

		$res .= '<h3>'. __('If you like this plugin - help me to promote it! You can:', self::ID) .'</h3>'.PHP_EOL;

		$res .= '<ol>';
		
		$res .= '<li>'. __('Translate it to unsupported language', self::ID) .'</li>'.PHP_EOL;
		
		$res .= '<li>'. __('Rate it on the official Plugin Directory', self::ID) .': <a href="http://wordpress.org/extend/plugins/q2w3-inc-manager/" target="_blank">http://wordpress.org/extend/plugins/q2w3-inc-manager/</a></li>'.PHP_EOL;
		
		$res .= '<li>'. __('Write a review or an article', self::ID) .'</li>'.PHP_EOL;
		
		$res .= '<li>'. __('Or just let your friends know about this plugin', self::ID) .'</li>'.PHP_EOL;
		
		$res .= '</ol>';
		
		$res .= '<p>'. __('Thank you!', self::ID) .'</p>'.PHP_EOL;
		
		$res .= '</div><!--wrap-->'.PHP_EOL;
		
		$res .= '<script type="text/javascript">'.PHP_EOL;
		
		$res .= 'jQuery(".parent_checkbox :checkbox").click(function(){
					if(jQuery(this).attr("checked") == false) {
						jQuery(this).parent("div").next().children(":checkbox").attr("checked", false);
					}
				});'.PHP_EOL;
		
		$res .= 'jQuery(".child_checkbox :checkbox").click(function(){
					if(jQuery(this).attr("checked") == true) {
						jQuery(this).parent("div").prev().children(":checkbox").attr("checked", true);
					}
				});'.PHP_EOL;
		
		$res .= '</script>'.PHP_EOL;
			
		echo $res; // output
		
	}
	
	/**
	 * Output html of the plugin deactivation page
	 * 
	 */
	protected static function deactivate_page() {
		
		//$action_link = get_option('siteurl').'/wp-admin/admin.php';
		
		$action_link = plugins_url().'/q2w3-inc-manager/q2w3-table/q2w3_post.php';
		
		$res = '<div class="wrap">'.PHP_EOL;
		
		$res .= '<h2>'. self::NAME .' &raquo; '. __('Deactivation', self::ID) .'</h2>'.PHP_EOL;
		
		$res .= '<br/><form method="post" action="'. $action_link .'">'.PHP_EOL;
			
		$res .= '<input type="hidden" name="page" value="'. $_GET['page'] .'"/>'.PHP_EOL;
	
		$res .= '<input type="hidden" name="deactivate" value="deactivate"/>'.PHP_EOL;
					
		$res .= '<input type="hidden" name="wp_nonce" value="'. wp_create_nonce('q2w3_table_post') .'"/>'.PHP_EOL;
						
		$res .= '<input type="submit" value="'. __('Deactivate plugin', self::ID) .'" class="button-secondary" /><br/><br/>';
			
		$res .= '</form>'.PHP_EOL;
			
			
		$res .= '<form method="post" action="'. $action_link .'" id="deactivate_and_clean">'.PHP_EOL;
			
		$res .= '<input type="hidden" name="page" value="'. $_GET['page'] .'"/>'.PHP_EOL;
	
		$res .= '<input type="hidden" name="deactivate" value="deactivate_and_clean"/>'.PHP_EOL;
					
		$res .= '<input type="hidden" name="wp_nonce" value="'. wp_create_nonce('q2w3_table_post') .'"/>'.PHP_EOL;
			
		$res .= '<input type="submit" value="'. __('Deactivate plugin and delete all settings from database', self::ID) .'" class="button-secondary" />';
			
		$res .= '</form>'.PHP_EOL;
		
		$res .= '<script type="text/javascript">jQuery("#deactivate_and_clean").submit(function(){return confirm("'. __('Deactivate plugin and delete all settings from database', self::ID) .'?")})</script>'.PHP_EOL;
		
		$res .= '</div><!--wrap-->'.PHP_EOL;
		
		echo $res;
		
	}
	
	public static function deactivation() {
		
		if (isset($_POST['deactivate']) && $_POST['deactivate'] == 'deactivate' || $_POST['deactivate'] == 'deactivate_and_clean') { // process deactivation options
				
			global $wpdb;
			
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
			
			$redirect_url = get_option('siteurl').'/wp-admin/plugins.php?deactivate=true';
			
			if ($_POST['deactivate'] == 'deactivate') { // simple deactivation
	
				deactivate_plugins(plugin_basename(__FILE__)); 
				
				wp_redirect($redirect_url);
								
			} elseif ($_POST['deactivate'] == 'deactivate_and_clean') { // advanced deactivation (delete tables and settings)
				
				self::object();
				
				deactivate_plugins(plugin_basename(__FILE__)); // deactivate plugin
	
				$wpdb->query('DELETE FROM '. $wpdb->options ." WHERE option_name LIKE '%q2w3_inc_manager%'"); // delete all plugin entries in options table
				
				$wpdb->query('DELETE FROM '. $wpdb->usermeta ." WHERE meta_key = '". q2w3_table_func::safe_plugin_id(self::ID)."_table_settings'"); // delete all plugin entries in usermeta table
				
				$wpdb->query('DROP TABLE IF EXISTS '.self::$object->table()); // delete includes table
				
				wp_redirect($redirect_url);
								
			}
							
		} 
		
	}
	
	/**
	 * Prints code of all active header includes 
	 * 
	 */
	public static function header_incs() {
		
		echo self::display_incs(q2w3_include_obj::LOC_HEADER);
		
	}
	
	/**
	 * Prints code of all active footer includes
	 * 
	 */
	public static function footer_incs() {
		
		echo self::display_incs(q2w3_include_obj::LOC_FOOTER);
		
	}
	
	/**
	 * Registers all active widgets
	 * 
	 */
	public static function widget_incs() {

		global $wp_widget_factory;
		
		self::object();
		
		$incs = self::select_incs(q2w3_include_obj::LOC_WIDGET);
						
		if (is_array($incs) && !empty($incs)) {
						
			foreach ($incs as $inc) {
			
				$plugin_name = plugin_basename(__FILE__);
				
				$object_id = $inc[self::$object->id->col_name];
				
				$widget_id = 'q2w3_inc_manager_widget_'.$object_id;

				$widget_admin_title = $inc[self::$object->title->col_name];
				
				$widget_public_title = $inc[self::$object->widget_title->col_name];
				
				$inc_pages = (array) $inc[self::$object->inc_pages->col_name];
				
				$exc_pages = (array) $inc[self::$object->exc_pages->col_name];
				
				$hide_from = $inc[self::$object->hide_from->col_name];
				
				$code_align = $inc[self::$object->code_align->col_name];
				
				$code = $inc[self::$object->code->col_name];
				
				$wp_widget_factory->widgets[$widget_id] = new q2w3_inc_manager_widget(self::ID, $plugin_name, $object_id, $widget_id, $widget_admin_title, $widget_public_title, $inc_pages, $exc_pages, $hide_from, $code_align, $code);
						
			}
		
		}
					
	}
	
	/**
	 * Prints code of all active 'before page content' includes
	 * 
	 */
	public static function b_page_content_incs() {
		
		static $i = 0;
		
		if ($i == 0) echo self::display_incs(q2w3_include_obj::LOC_B_PAGE_CONTENT);
		
		$i++;
		
	}
	
	/**
	 * Prints code of all active 'after page content' includes
	 * 
	 */
	public static function a_page_content_incs() {
		
		static $i = 0;
		
		if ($i == 0) echo self::display_incs(q2w3_include_obj::LOC_A_PAGE_CONTENT);
		
		$i++;
				
	}
	
	/**
	 * Adds active 'before post content' includes code to post content 
	 * 
	 * @param $content
	 * @return string $content
	 */
	public static function b_post_content_incs($content) {
		
		return self::display_incs(q2w3_include_obj::LOC_B_POST_CONTENT).$content; 
		
	}
	
	/**
	 * Adds active 'after post excerpt' includes code to post content 
	 * 
	 * @param $content
	 * @return string $content
	 */
	public static function a_post_excerpt_incs($content) {
		
		global $post;
		
		return str_replace('<span id="more-'.$post->ID.'"></span>', '<span id="more-'.$post->ID.'"></span>'.self::display_incs(q2w3_include_obj::LOC_A_POST_EXCERPT), $content);
		
	}
	
	/**
	 * Adds active 'after post content' includes code to post content 
	 * 
	 * @param $content
	 * @return string $content
	 */
	public static function a_post_content_incs($content) {
		
		return $content.self::display_incs(q2w3_include_obj::LOC_A_POST_CONTENT);
		
	}
	
	/**
	 * Prints code of the include with specified ID
	 * This function must be manually inserted in to one of the theme page
	 * 
	 * @param int $id
	 */
	public static function manual_inc($id, $echo = true) {
		
		if (!$echo) return self::display_inc($id, q2w3_include_obj::LOC_MANUAL);
		
		echo self::display_inc($id, q2w3_include_obj::LOC_MANUAL);
		
	}
	
	/**
	 * Shortcode include function
	 * This function trigger shortcode must be manually inserted in to one post or page content - [include id="%id%"], where %id% is the integer id of the include.
	 * 
	 */
	public static function shortcode_incs($atts, $content = null) {
		
		$id = NULL; // Removes "!" in Zend Studio
		
		extract( shortcode_atts( array( 'id' => null ), $atts ) );
		
		return self::display_inc($id, q2w3_include_obj::LOC_SHORTCODE);
		
	}
	
	/**
	 * Returns single Include
	 * 
	 * @param $id ID of the Include
	 * @param $location One of location constants stored in q2w3_include_obj class
	 * @return string or NULL if no include code exists for current location
	 */
	protected static function display_inc($id, $location) {
		
		$id = intval($id);
		
		self::object();
		
		self::$object->load_values_from_db($id, 'db2php');
		
		if (self::$object->id->val && self::$object->location->val == $location) {
		
			$inc_pages = (array) self::$object->inc_pages->val;
			
			$exc_pages = (array) self::$object->exc_pages->val;
				
			$hide_from = self::$object->hide_from->val;
				
			$code_align = self::$object->code_align->val;
				
			if (self::check_visibility($inc_pages, $exc_pages, $hide_from)) {
					
				if (self::$object->status->val == q2w3_include_obj::STATUS_ACTIVE) {
						
					return self::code_align(self::php_eval(htmlspecialchars_decode(self::$object->code->val, ENT_QUOTES)), $code_align);
					
				}
								
			} 
			
			self::$object->clean_values();
		
		}
		
	}	
	
	/**
	 * Get includes code for selected location
	 * 
	 * @param $location One of location constants stored in q2w3_include_obj class
	 * @return string or NULL if no include code exists for current location
	 */
	protected static function display_incs($location) {
		
		if (!$location) return false;
		
		self::object();
		
		$incs = self::select_incs($location);
		
		$res = '';
		
		if (is_array($incs) && !empty($incs)) {
		
			foreach ($incs as $inc) {
			
				$inc_pages = (array) $inc[self::$object->inc_pages->col_name];
				
				$exc_pages = (array) $inc[self::$object->exc_pages->col_name];
				
				$hide_from = $inc[self::$object->hide_from->col_name];
				
				$code_align = $inc[self::$object->code_align->col_name];
				
				if (self::check_visibility($inc_pages, $exc_pages, $hide_from)) {
					
					$res .= self::code_align(self::php_eval(htmlspecialchars_decode($inc[self::$object->code->col_name], ENT_QUOTES)), $code_align); // htmlspecialchars_decode - php 5.1 function
								
				}
		
			}
		
		}
		
		return $res;
		
	}
		
	/**
	 * Get a list of includes for selected location
	 * 
	 * @param $location One of location constants stored in q2w3_include_obj class
	 * @return array of objects or FALSE if query returned empty result
	 */
	protected static function select_incs($location) {
		
		global $wpdb;
		
		$res = $wpdb->get_results('SELECT * FROM '. self::$object->table() .'  WHERE '. self::$object->status->col_name .' = '. q2w3_include_obj::STATUS_ACTIVE .' AND ' . self::$object->location->col_name .' = '. $location .' ORDER BY '. self::$object->priority->col_name .','. self::$object->id->col_name, ARRAY_A);
		
		if (is_array($res)) {
						
			$output = array();
			
			foreach ($res as $inc_data) {
				
				self::$object->load_values_from_array($inc_data, 'db2php');
				
				$output[] = self::$object->values_array(); 
				
			}
			
			self::$object->clean_values();
			
			return $output;
			
		} else {
			
			return false;
			
		}
						
	}
		
	/**
	 * Check visibility parameters
	 * 
	 * Method must be public!
	 * 
	 * @param array $inc_pages Array of pages where code can be shown
	 * @param array $exc_pages Array of pages where code cannot be shown
	 * @param string $hide_from_admin Hide from admin value
	 * @return bool True if test is positive
	 */
	public static function check_visibility($inc_pages, $exc_pages, $hide_from_role) {

		$user = wp_get_current_user();
		
		if ($hide_from_role) {
			
			if ($user->ID === 0 && in_array('q2w3_visitor', $hide_from_role)) return false; // check visitor role

			if ($user->ID) {
				
				if ( is_multisite() && is_super_admin( $user->ID ) ) { // hide code for multisite superadmin only if administrator group selected
					
					foreach ($hide_from_role as $role) {
					
						if ($role == 'administrator') return false;
					
					}
					
				} else {
				
					foreach($hide_from_role as $role) {
					
						if ($user->has_cap($role)) return false;
					
					}
				
				}
				
			}
			
		}
		
		if (self::check_page($inc_pages) && !self::check_page($exc_pages)) return true; else return false; // check pages for include and exclude code
		
	}
	
	/**
	 * Checks if current page id exists in input array
	 * 
	 * @param array $pages
	 * @return bool True if current page id found in input array
	 */
	protected static function check_page($pages) {
		
		if (is_array($pages)) {
			
			$cur_page = self::cur_page(); // get current page ids
			
			foreach ($pages as $page) {
				
				if (in_array($page, $cur_page)) return true; 
				
			}
			
		}
		
		return false;
		
	}
	
	/**
	 * Returns array of current page ids 
	 * 
	 * @return array
	 */
	public static function cur_page() {
		
		static $page_id = array();
		
		if (is_feed()) return $page_id; // feeds are not affected
		
		if (!$page_id) {
		
			if (is_front_page() || is_home() && !$GLOBALS['wp_query']->is_posts_page) { // front page
				
				$page_id = array(q2w3_include_obj::ALL, q2w3_include_obj::FRONT_PAGE);
				
			} elseif (is_attachment()) { // attachment page
				
				$page_id = array(q2w3_include_obj::ALL, q2w3_include_obj::ATTACHMENT_PAGES);
				
			} elseif (is_single()) { // post page + custom post type page
				
				$post_type = get_post_type($GLOBALS['post']);
				
				$page_id = array(q2w3_include_obj::ALL, q2w3_include_obj::POST_TYPES_PAGES, $post_type.'_all', $post_type.'_'.$GLOBALS['post']->ID);

				if (function_exists('get_post_format')) { // If WP ver < 3.1
				
					$format = get_post_format();
			
					if ($format === false) $format = 'post_format_standard'; else $format = 'post_format_'.$format;
			
					array_push($page_id, $format);
					
				}			
				
			} elseif (is_page()) { // wp page
				
				$page_id = array(q2w3_include_obj::ALL, q2w3_include_obj::POST_TYPES_PAGES, 'page_all', 'page_'.$GLOBALS['post']->ID);
				
			} elseif (is_category() || is_tag() || is_tax()) { // taxonomy page
				
				$tax_obj = $GLOBALS['wp_query']->get_queried_object();
				
				$taxonomy = $tax_obj->taxonomy;
				
				$page_id = array(q2w3_include_obj::ALL, q2w3_include_obj::TAX_PAGES, $taxonomy.'_all', $taxonomy.'_'.$tax_obj->term_id);
				
			} elseif (function_exists('is_post_type_archive') && is_post_type_archive()) { // post type archive page
				
				$post_type = get_post_type($GLOBALS['post']);
				
				$page_id = array(q2w3_include_obj::ALL, q2w3_include_obj::POST_TYPE_ARCHIVE_PAGES, 'post_type_archive_'.$post_type);
				
			} elseif ($GLOBALS['wp_query']->is_posts_page) { // post type archive page for 'post' post type
				
				$page_id = array(q2w3_include_obj::ALL, q2w3_include_obj::POST_TYPE_ARCHIVE_PAGES, 'post_type_archive_post');				
				
			} elseif (is_date()) { // date archive page
				
				$page_id = array(q2w3_include_obj::ALL, q2w3_include_obj::DATE_PAGES);
				
			} elseif (is_author()) { // author page
				
				$page_id = array(q2w3_include_obj::ALL, q2w3_include_obj::AUTHOR_PAGES);
				
			} elseif (is_search()) { // search page
				
				$page_id = array(q2w3_include_obj::ALL, q2w3_include_obj::SEARCH_PAGE);
				
			} elseif (is_404()) { // error 404 page
				
				$page_id = array(q2w3_include_obj::ALL, q2w3_include_obj::PAGE_404);
				
			}
			
			if (is_preview()) { // preview page
	
				array_push($page_id, q2w3_include_obj::PREVIEW_PAGE);
				
			}
	
			if (is_paged()) { // paged page
				
				array_push($page_id, q2w3_include_obj::PAGED_PAGES);
				
			}
			
			/*if (is_feed()) {
				
				array_push($page_id, 'feed');
				
			}*/
			
		}
		
		return $page_id;
		
	}
	
	/**
	 * Wrap code in a div with selected text-align propertie
	 * 
	 * @param string $code_str Code string
	 * @param string $code_align Code align value
	 * @return string   
	 */
	public static function code_align($code_str, $code_align) {
		
		if ($code_align) {
						
			switch ($code_align) {
				
				case q2w3_include_obj::ALIGN_LEFT:
					
					$align = 'left';
					
					break;
					
				case q2w3_include_obj::ALIGN_CENTER:
					
					$align = 'center';
					
					break;
					
				case q2w3_include_obj::ALIGN_RIGHT:
					
					$align = 'right';
					
					break;
				
			}

			$code_str = "<div style=\"text-align: $align\">$code_str</div>".PHP_EOL;
						
		}
		
		return $code_str;
				
	}
	
	/**
	 * Evaluates string as php code
	 * 
	 * @param string Code string
	 * @return string   
	 */
	protected static function php_eval($code_str) {
		
		ob_start(); // strat output buffering to capture eval output in a string
		
		eval('?>'.$code_str); // ? > before $code_str is for correct output of non php code
		
		return ob_get_clean();
		
	}
	
	public static function parse_shortcodes_comment( $content ) {
		
		static $options = '';
		
		if (!$options) $options = get_option(self::ID);
		
		if ($options['shortcodes_in_comments']) {
		
			return self::shortcode_hack( $content, array( __CLASS__, 'shortcode_incs' ) );
		
		} else {
			
			return $content;
			
		}	
			
	}
	
	/**
	 * Returns URL of the plugin directory
	 * 
	 * @return string   
	 */
	public static function plugin_url() {
	
		return WP_PLUGIN_URL.'/'.dirname(plugin_basename(__FILE__));
	
	}
	
	// Shortcodes hack (to use shortcodes in comments)
	
	// A filter function that runs do_shortcode() but only with this plugin's shortcodes
	public static function shortcode_hack( $content, $callback ) {
		global $shortcode_tags;

		$shortcodes = array( 'include', 'INCLUDE');
		
		// Backup current registered shortcodes and clear them all out
		$orig_shortcode_tags = $shortcode_tags;
		remove_all_shortcodes();

		// Register all of this plugin's shortcodes
		foreach ( $shortcodes as $shortcode )
			add_shortcode( $shortcode, $callback );

		// Do the shortcodes (only this plugins's are registered)
		$content = self::do_shortcode_keep_escaped_tags( $content );

		// Put the original shortcodes back
		$shortcode_tags = $orig_shortcode_tags;

		return $content;
	}


	// This is a clone of do_shortcode() that uses a different callback function
	// The new callback function will keep escaped tags escaped, i.e. [[foo]]
	// Up to date as of r18324 (3.2)
	public static function do_shortcode_keep_escaped_tags( $content ) {
		global $shortcode_tags;

		if (empty($shortcode_tags) || !is_array($shortcode_tags))
			return $content;

		$pattern = get_shortcode_regex();
		return preg_replace_callback('/'.$pattern.'/s', array( __CLASS__, 'do_shortcode_tag_keep_escaped_tags' ), $content);
	}


	// Callback for above do_shortcode_keep_escaped_tags() function
	// It's a clone of core's do_shortcode_tag() function with a modification to the escaped shortcode return
	// Up to date as of r18324 (3.2)
	public static function do_shortcode_tag_keep_escaped_tags( $m ) {
		global $shortcode_tags;

		// allow [[foo]] syntax for escaping a tag
		if ( $m[1] == '[' && $m[6] == ']' ) {
			return $m[0]; // This line was modified for this plugin (no substr call)
		}

		$tag = $m[2];
		$attr = shortcode_parse_atts( $m[3] );

		if ( isset( $m[5] ) ) {
			// enclosing tag - extra parameter
			return $m[1] . call_user_func( $shortcode_tags[$tag], $attr, $m[5], $tag ) . $m[6];
		} else {
			// self-closing tag
			return $m[1] . call_user_func( $shortcode_tags[$tag], $attr, NULL,  $tag ) . $m[6];
		}
	}
		
}

?>
