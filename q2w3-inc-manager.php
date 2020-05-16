<?php
/*
Plugin Name: Code Insert Manager (Q2W3 Inc Manager)
Plugin URI: http://www.q2w3.ru/code-insert-manager-wordpress-plugin/
Description: This plugin allows you to insert html, css, javascript and PHP code to public wordpress pages.
Version: 2.5.2
Author: Max Bond
Author URI: http://www.q2w3.ru/
*/

if ( ! defined('Q2W3_PHP_EVAL') ) define ('Q2W3_PHP_EVAL', false);

if (defined('ABSPATH')) { // makes shure that the following functions will be run inside WordPress only

	require_once 'q2w3-table/q2w3_table_func.php';
		
	require_once 'q2w3-table/q2w3_table_load.php'; // loads q2w3_table library
	
	require_once 'q2w3_inc_manager_widget.php'; // loads inc manager widget class
	
	// Hooks 
	
	register_activation_hook(__FILE__, array( 'q2w3_inc_manager', 'activate' )); // registers activation function

	add_action('widgets_init', array( 'q2w3_inc_manager', 'widget_incs' )); // widgets initialization both for admin and public pages
	
	add_shortcode('include', array( 'q2w3_inc_manager', 'shortcode_incs' )); // shortcode init
	
	add_shortcode('INCLUDE', array( 'q2w3_inc_manager', 'shortcode_incs' ));	
	
	if ( is_admin() ) { // admin hooks
		
		q2w3_inc_manager::load_language(); 
		
		add_action('admin_menu', array( 'q2w3_inc_manager', 'reg_menu' ));
				
		add_action('add_meta_boxes', array( 'q2w3_inc_manager', 'add_meta_boxes' ));

		add_action('wp_insert_post', array( 'q2w3_inc_manager', 'save_meta_boxes' ), 10, 3 );
		
		//add_filter('set-screen-option', array( 'q2w3_inc_manager', 'screen_options_save' ), 10, 3);
					
	} else { // public hooks
		
		add_action('wp_head', array( 'q2w3_inc_manager', 'cur_page' ), 1);
		
		add_action('wp_head', array( 'q2w3_inc_manager', 'header_incs' ), 99);
	
		add_action('wp_footer', array( 'q2w3_inc_manager', 'footer_incs' ), 99);
		
		add_action('loop_start', array( 'q2w3_inc_manager', 'b_page_content_incs' ));
	
		add_action('loop_end', array( 'q2w3_inc_manager', 'a_page_content_incs' ));
	
		add_filter('the_content', array( 'q2w3_inc_manager', 'b_post_content_incs' ));
		
		add_filter('the_content', array( 'q2w3_inc_manager', 'a_post_excerpt_incs' ));
	
		add_filter('the_content', array( 'q2w3_inc_manager', 'a_post_content_incs' ));
		
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
		
		
	const PHP_VER = '7.0'; // Minimum PHP version
	
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
			
	public static $restricted_post_types = array('attachment', 'revision', 'nav_menu_item', 'oembed_cache', 'user_request', 'customize_changeset', 'custom_css', 'tablepress_table');
	
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
			
			if ( MULTISITE == true ) $inc_obj->create_table(); // create tables for wp network sites
	
			$table = new q2w3_table(self::ID, $inc_obj);
			
			$table->get_handler = admin_url( 'admin-ajax.php' ); 
	
			$table->post_handler = admin_url( 'admin-ajax.php' ); 
	
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
	 * Returns URL of the plugin directory
	 * 
	 * @return string   
	 */
	public static function plugin_url() {
	
		return WP_PLUGIN_URL.'/'.dirname(plugin_basename(__FILE__));
	
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
	
		add_menu_page('Code Insert', __('Code Insert', self::ID), $access_level, 'q2w3-inc-manager', array(__CLASS__,'main_page'), 'dashicons-welcome-widgets-menus');
		
		self::$plugin_page = add_submenu_page('q2w3-inc-manager', self::NAME, __('Inserts', self::ID), $access_level, 'q2w3-inc-manager', array(__CLASS__,'main_page'));
		
		add_submenu_page('q2w3-inc-manager', self::NAME, __('Add New', self::ID), $access_level, 'q2w3-inc-manager&amp;id=_new_', array(__CLASS__,'main_page'));
		
		add_submenu_page('q2w3-inc-manager', self::NAME, __('Settings', self::ID), $access_level, 'q2w3-inc-manager-settings', array(__CLASS__,'settings_page'));
		
		//add_action('manage_'. self::$plugin_page .'_columns', array(__CLASS__, 'screen_options'));
		
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
	
		self::table();
		
		$res = '<div class="wrap">'.PHP_EOL;
		
		$res .= '<h1 class="wp-heading-inline">'. self::NAME .'</h1><a href="?page=q2w3-inc-manager&amp;id=_new_" class="page-title-action">'. __('Add New', self::ID) .'</a>'.PHP_EOL;
			
		$res .= '<hr class="wp-header-end">';

		$res .= self::$table->html();
		
		//$res .= '<ul class="subsubsub"><li>'. __('Need help? Visit', self::ID) .' <a href="http://www.q2w3.ru/code-insert-manager-wordpress-plugin/">'. __('Plugin Homepage', self::ID) .'</a></li></ul>'.PHP_EOL;
		
		$res .= '</div><!--wrap-->'.PHP_EOL;
		
		echo $res; // output
		
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
				
				$res .= '<input type="checkbox" name="'. self::ID.'[post_types]['. $post_type->name .'][enable]" '. checked(@$options['post_types'][$post_type->name]['enable'], 'on', false) . $disabled .' /> <strong>'. $post_type->labels->name .'</strong> ('. $post_type->name .') <br/>';
				
				$res .= '</div>'.PHP_EOL;
				
				$res .= '<div class="child_checkbox" style="margin-left: 20px">';
				
				$res .= '<input type="checkbox" name="'. self::ID.'[post_types]['. $post_type->name .'][expand]" '. checked(@$options['post_types'][$post_type->name]['expand'], 'on', false) .' /> '. __('Expand', self::ID);

				$res .= '<br/><input type="checkbox" name="'. self::ID.'[post_types]['. $post_type->name .'][metabox-header]" '. checked(@$options['post_types'][$post_type->name]['metabox-header'], 'on', false) .' /> '. __('Add metabox for page head inserts', self::ID);

				$res .= '<br/><input type="checkbox" name="'. self::ID.'[post_types]['. $post_type->name .'][metabox-footer]" '. checked(@$options['post_types'][$post_type->name]['metabox-footer'], 'on', false) .' /> '. __('Add metabox for page footer inserts', self::ID);

				$res .= '</div>'.PHP_EOL;
				
				$res .= '</div>'.PHP_EOL;
			
			}
  
		}
		
		// Taxonomies

		$res .= '<h3>'. __('Taxonomies', self::ID) .'</h3>'.PHP_EOL;
		
		// Post Formats
		
		$res .= '<div style="margin: 5px 0px 0px 10px">'.PHP_EOL;
				
		$res .= '<div class="parent_checkbox">';
				
		$res .= '<input type="checkbox" name="'. self::ID.'[taxonomies][post_format][enable]" '. checked(@$options['taxonomies']['post_format']['enable'], 'on', false) .' /> <strong>'. __('Post Formats') .'</strong> (post_format)';
				
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
				
				$res .= '<input type="checkbox" name="'. self::ID .'[taxonomies]['. $taxonomy->name .'][enable]" '. checked(@$options['taxonomies'][$taxonomy->name]['enable'], 'on', false) . $disabled .' /> <strong>'. $taxonomy->labels->name .'</strong> ('. $taxonomy->name .')';
				
				$res .= '</div>'.PHP_EOL;
				
				$res .= '<div class="child_checkbox" style="margin-left: 20px">';
				
				$res .= '<input type="checkbox" name="'. self::ID .'[taxonomies]['. $taxonomy->name .'][expand]" '. checked(@$options['taxonomies'][$taxonomy->name]['expand'], 'on', false) .' /> '. __('Expand', self::ID);

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
					
					if ( defined('Q2W3_PHP_EVAL') && Q2W3_PHP_EVAL === true ) { // allow PHP eval

						return self::code_align(self::php_eval(htmlspecialchars_decode(self::$object->code->val, ENT_QUOTES)), $code_align); // htmlspecialchars_decode - php 5.1 function
											
					} else {

						return self::code_align(htmlspecialchars_decode(self::$object->code->val, ENT_QUOTES), $code_align); // htmlspecialchars_decode - php 5.1 function

					}

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
					
					if ( defined('Q2W3_PHP_EVAL') && Q2W3_PHP_EVAL === true ) { // allow PHP eval

						$res .= self::code_align(self::php_eval(htmlspecialchars_decode($inc[self::$object->code->col_name], ENT_QUOTES)), $code_align); // htmlspecialchars_decode - php 5.1 function
											
					} else {
						
						$res .= self::code_align(htmlspecialchars_decode($inc[self::$object->code->col_name], ENT_QUOTES), $code_align); // htmlspecialchars_decode - php 5.1 function
						
					}

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
		
		static $res = NULL;
		
		if ( ! $res ) { // get all active includes in one query
		
			$includes = $wpdb->get_results('SELECT * FROM '. self::$object->table() .'  WHERE '. self::$object->status->col_name .' = '. q2w3_include_obj::STATUS_ACTIVE .' ORDER BY '. self::$object->location->col_name .', '. self::$object->priority->col_name .','. self::$object->id->col_name, ARRAY_A);
			
			if ( is_array($includes) ) foreach ( $includes as $inc ) {
					
				$res[$inc[self::$object->location->col_name]][] = $inc;
						
			}
		
		}
				
		if (isset($res[$location]) && is_array($res[$location])) {
						
			$output = array();
			
			foreach ($res[$location] as $inc_data) {
				
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
					
						//$role = $user->translate_level_to_cap($role);

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

	public static function add_meta_boxes() {

		if ( ! current_user_can('edit_theme_options') ) return false;

		$options = get_option(self::ID);	
		
		if ( ! $options ) $options = self::$default_options;

		if ( ! isset($options['post_types']) ) return false;

		if ( is_array($options['post_types']) ) foreach ( $options['post_types'] as $post_type => $post_type_options ) {

			if ( isset($post_type_options['metabox-header']) && $post_type_options['metabox-header'] ) {

				add_meta_box( self::ID.'-metabox-header', __('Code in ', self::ID) .'wp_head', array( __CLASS__, 'meta_box_header' ), $post_type, 'normal', 'low' );

			}

			if ( isset($post_type_options['metabox-footer']) && $post_type_options['metabox-footer'] ) {

				add_meta_box( self::ID.'-metabox-footer', __('Code in ', self::ID) .'wp_footer', array( __CLASS__, 'meta_box_footer' ), $post_type, 'normal', 'low' );

			}

		}

	}

	public static function meta_box_header() {

		self::meta_box(q2w3_include_obj::LOC_HEADER);

	}
	
	public static function meta_box_footer() {

		self::meta_box(q2w3_include_obj::LOC_FOOTER);

	}

	protected static function meta_box($location) {

		global $post;
		
		$include_id = get_post_meta($post->ID, self::ID.'-'.$location, true);
		
		$status = q2w3_include_obj::STATUS_DISABLED;

		$code = null;

		if ( $include_id > 0 ) {

			$object = new q2w3_include_obj(self::ID);

			$res = $object->load_values_from_db($include_id, 'db2php');

			if ( $res === false ) {

				delete_post_meta($post->ID, self::ID.'-'.$location);

				$include_id = null;

			} else {

				$status = $object->status->val;

				$code = $object->code->val;

			}

		}

		if ( ! $include_id ) $include_id = q2w3_table::NEW_MARKER;


		echo '<input type="hidden" name="'. self::ID .'['. $location .'][id]" value="'. $include_id .'"/>';
		
		echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary button-large" value="Обновить"></p>';

		echo '<p><select name="'. self::ID .'['. $location .'][status]">';
				
			echo '<option value="'. q2w3_include_obj::STATUS_ACTIVE .'" '. selected(q2w3_include_obj::STATUS_ACTIVE, $status, false) .'>'. __('Active', self::ID) .'</option>';
			
			echo '<option value="'. q2w3_include_obj::STATUS_DISABLED .'" '. selected(q2w3_include_obj::STATUS_DISABLED, $status, false) .'>'. __('Disabled', self::ID) .'</option>';
			
		echo '</select></p>';
		
		echo '<input type="hidden" name="'. self::ID .'['. $location .'][inc_pages]" value="'. $post->post_type .'_'. $post->ID .'"/>';

		echo '<textarea name="'. self::ID .'['. $location .'][code]" rows="5" class="large-text code" placeholder="'. __('Input code', self::ID) .'">'. $code .'</textarea>';

	}

	public static function save_meta_boxes($post_id, $post, $update) {

		if ( ! current_user_can('edit_theme_options') ) return false;

		if ( !( isset($_POST[self::ID]) && is_array($_POST[self::ID]) ) ) return false;
		
		foreach ( $_POST[self::ID] as $location => $propertie ) {

			if ( isset($propertie['code']) && ! trim($propertie['code']) && $propertie['id'] == q2w3_table::NEW_MARKER ) continue;
			
			$object = new q2w3_include_obj(self::ID);

			$show_sys_msg = false;

			if ( isset($propertie['code']) && trim($propertie['code']) ) { // save include
					
				$propertie['description'] = $post->post_title;

				$propertie['location'] = $location;

				$object->load_values_from_array($propertie, 'php2db');
				
				$include_id = $object->save($show_sys_msg);

				if ( $include_id ) {

					if ( $propertie['id'] != q2w3_table::NEW_MARKER ) $include_id = $propertie['id'];

					update_post_meta($post_id, self::ID.'-'.$location, $include_id);

				}

			} elseif ( $propertie['id'] != q2w3_table::NEW_MARKER ) { // delete include

				$include_id = (int)$propertie['id'];

				if ( $include_id > 0 ) {
				
					$object->id->val = $include_id;

					if ( $object->delete($show_sys_msg) ) {

						delete_post_meta($include_id, self::ID.'-'.$location);

					}

				}

			} 
		
		}

	}
			
}
