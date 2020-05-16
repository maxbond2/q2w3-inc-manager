<?php

/**
 * @author Max Bond
 * 
 * Describes db table for storing includes and provides methods for includes data manipulation 
 * 
 */
class q2w3_include_obj extends _q2w3_table_obj {
	
	public $id; // Include ID

	public $title; // Include title/description
	
	public $status; // Include status
	
		const STATUS_ACTIVE = '10';
		
		const STATUS_DISABLED = '20';
	
	public $location; // Include location
	
		const LOC_HEADER = '10'; // inside <head></head> section // displays with get_header() function
		
		const LOC_FOOTER = '20'; // bottom of the page // displays with get_footer() function
		
		const LOC_WIDGET = '30'; // sidebar // widget position could be set at widget settings page 
		
		const LOC_B_PAGE_CONTENT = '40'; // before wp loop 
		
		const LOC_A_PAGE_CONTENT = '50'; // after wp loop
		
		const LOC_B_POST_CONTENT = '60'; // before page/post content
		
		const LOC_A_POST_EXCERPT = '65'; // after post excerpt
		
		const LOC_A_POST_CONTENT = '70'; // after page/post content
		
		const LOC_MANUAL = '80'; // manual location
		
		const LOC_SHORTCODE = '90'; // manual location
		
	public $widget_title; // title of the widget // displays only on public pages
	
	public $priority; // prority of the include
	
	public $inc_pages; // list of pages where include will be shown
			
		const ALL = 'all'; // all pages
		
		//const WP_PAGES = 'page_all'; // all WordPress pages (not any generic webpage from your blog)
		
		const POST_TYPES_PAGES = 'post_types_pages'; // all posts pages
		
		const TAX_PAGES = 'taxonomy_all';
		
		const POST_TYPE_ARCHIVE_PAGES = 'post_type_archive_all';
		
		const DATE_PAGES = 'date_pages'; // all date-based archive page is being displayed (i.e. a monthly, yearly, daily or time-based archive)
		
		//const CATEGORY_PAGES = 'category_pages'; // When any Category archive page is being displayed
		
		//const TAG_PAGES = 'tag_pages'; // When any Tag archive page is being displayed

		const AUTHOR_PAGES = 'author_pages'; // When any Author page is being displayed 

		const ATTACHMENT_PAGES = 'attachment_pages'; // When an attachment document to a post or Page is being displayed
		
		const PAGED_PAGES = 'paged_pages'; // When the page being displayed is "paged". This refers to an archive or the main page being split up over several pages and will return true on 2nd and subsequent pages of posts. This does not refer to a Post or Page whose content has been divided into pages using the <!--nextpage--> QuickTag
		
		const FRONT_PAGE = 'front_page'; // When it is the front of the site displayed, whether it is posts or a Page
		
		const SEARCH_PAGE = 'search_page'; // When a search result page archive is being displayed
		
		const PREVIEW_PAGE = 'preview_page'; // When a single post being displayed is viewed in Draft mode

		const PAGE_404 = 'page_404'; // When a page displays after an "HTTP 404: Not Found" error occurs
		
		const FEED = 'feed';
					
	public $exc_pages; // list of pages where include will not be shown
	
	public $hide_from; // option for hiding include from logged in admin
	
	public $code_align; // text-align type of the included code

		const ALIGN_LEFT = '1';
		
		const ALIGN_CENTER = '2';
		
		const ALIGN_RIGHT = '3';
		
	public $code; // included code
	
	
	
	/**
 	 * Returns db table name based on plugin id 
 	 * 
 	 */
	public function table() {
		
		global $wpdb;
	
		return $wpdb->prefix . q2w3_inc_manager::ID;
		
	}
	
	/**
 	 * Returns db table version 
 	 * Needed for upgrading db table with the db_delta function
 	 * 
 	 */
	public function table_ver() {
		
		return '1.4';
		
	}
	
	/**
 	 * Returns name used by <input name="" /> attribute 
 	 * 
 	 */
	protected function input_name($name) {
	
		return "propertie[$name]";
	
	}
	
	/**
 	 * Main object init function
 	 * Here described all db columns types, form elements for data input and display behavior
 	 * Refer to _q2w3_table_obj_properties class for field options description 
 	 * 
 	 */
	protected function init() {
		
		$this->id->name = 'ID';
		$this->id->col_name = 'id';
		$this->id->col_type = 'INT NOT NULL AUTO_INCREMENT';
		$this->id->col_index = _q2w3_table_obj_propertie::PRIMARY;
		$this->id->input = new q2w3_hidden_input(array('name'=>$this->input_name($this->id->col_name)));
		$this->id->table_view = false;
		$this->id->table_view_change = false;
		$this->id->single_view = false;
		
		$this->title->name = __('Description', self::$plugin_id);
		$this->title->col_name = 'description';
		$this->title->col_type = 'VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL';
		$this->title->input = new q2w3_text_input(array('name'=>$this->input_name($this->title->col_name), 'class'=>'text_input', 'maxlength'=>255));
		$this->title->help = __('Name/description of the included code. Required field', self::$plugin_id);
		$this->title->table_view = true;
		$this->title->table_view_change = false;
		$this->title->single_view = true;
				
		$this->status->name = __('Status', self::$plugin_id);
		$this->status->col_name = 'status';
		$this->status->col_type = 'INT NOT NULL';
		$this->status->input_values = array(self::STATUS_ACTIVE=>__('Active', self::$plugin_id), self::STATUS_DISABLED=>__('Disabled', self::$plugin_id));
		$this->status->input = new q2w3_select_input(array('name'=>$this->input_name($this->status->col_name), 'value_to_select'=>$this->status->input_values, 'class'=>'select_input'));
		$this->status->help = __('Active - code will be displayed, Disabled - code will not be displayed', self::$plugin_id);
		$this->status->conv = new q2w3_select_conv($this->status, self::$plugin_id);
		$this->status->table_view = true;
		$this->status->table_view_change = false;
		$this->status->single_view = true;
		
		$this->location->name = __('Location', self::$plugin_id);
		$this->location->col_name = 'location';
		$this->location->col_type = 'INT NOT NULL';
		$this->location->input_values = array(self::LOC_HEADER=>__('Header', self::$plugin_id), self::LOC_FOOTER=>__('Footer', self::$plugin_id), self::LOC_WIDGET=>__('Widget', self::$plugin_id), self::LOC_B_PAGE_CONTENT=>__('Before page content', self::$plugin_id), self::LOC_A_PAGE_CONTENT=>__('After page content', self::$plugin_id), self::LOC_B_POST_CONTENT=>__('Before post content', self::$plugin_id), self::LOC_A_POST_EXCERPT=>__('After post excerpt', self::$plugin_id), self::LOC_A_POST_CONTENT=>__('After post content', self::$plugin_id), self::LOC_MANUAL=>__('Manual', self::$plugin_id), self::LOC_SHORTCODE=>__('Shortcode', self::$plugin_id));
		$this->location->input = new q2w3_select_input(array('name'=>$this->input_name($this->location->col_name), 'value_to_select'=>$this->location->input_values, 'class'=>'select_input', 'id'=>'select_location'));
		$this->location->help = __('Location where to place included code. If Widget selected, go to the Widget admin page to setup widget position. In Manual mode you must insert plugin function directly in to theme file', self::$plugin_id);
		$this->location->conv = new q2w3_select_conv($this->location, self::$plugin_id);
		$this->location->table_view = true;
		$this->location->table_view_change = true;
		$this->location->single_view = true;
		
		$this->widget_title->name = __('Widget Title', self::$plugin_id);
		$this->widget_title->col_name = 'widget_title';
		$this->widget_title->col_type = 'VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci';
		$this->widget_title->input = new q2w3_text_input(array('name'=>$this->input_name($this->widget_title->col_name), 'maxlength'=>255));
		$this->widget_title->conv = new q2w3_text_conv();
		$this->widget_title->table_view = false;
		$this->widget_title->table_view_change = false;
		$this->widget_title->single_view = false;
		
		$this->priority->name = __('Priority', self::$plugin_id);
		$this->priority->col_name = 'priority';
		$this->priority->col_type = 'INT';
		$this->priority->input = new q2w3_text_input(array('name'=>$this->input_name($this->priority->col_name), 'class'=>'digit_input', 'maxlenght'=>4));
		$this->priority->help = __('Determines display order of includes in the same location', self::$plugin_id);
		$this->priority->conv = new q2w3_priority_conv();
		$this->priority->table_view = true;
		$this->priority->table_view_change = true;
		$this->priority->single_view = true;
				
		$this->inc_pages->name = __('Insert on pages', self::$plugin_id);
		$this->inc_pages->col_name = 'inc_pages';
		$this->inc_pages->col_type = 'TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL';
		$this->inc_pages->input = new q2w3_wp_page_select_input(array('name'=>$this->input_name($this->inc_pages->col_name), 'id'=>'inc_pages_select', 'type'=>'include')); 
		$this->inc_pages->help = __('Pages where code CAN BE shown. Hold CTRL key for multiple select or deselect pages. Required field', self::$plugin_id);
		$this->inc_pages->conv = new q2w3_select_page_conv($this->inc_pages, self::$plugin_id);
		$this->inc_pages->table_view = true;
		$this->inc_pages->table_view_change = true;
		$this->inc_pages->single_view = true;
		
		$this->exc_pages->name = __('Exclude pages', self::$plugin_id);
		$this->exc_pages->col_name = 'exc_pages';
		$this->exc_pages->col_type = 'TEXT CHARACTER SET utf8 COLLATE utf8_general_ci';
		$this->exc_pages->input = new q2w3_wp_page_select_input(array('name'=>$this->input_name($this->exc_pages->col_name), 'id'=>'exc_pages_select', 'type'=>'exclude')); 
		$this->exc_pages->help = __('Pages where code CAN NOT be shown. Hold CTRL key for multiple select or deselect pages', self::$plugin_id);
		$this->exc_pages->conv = new q2w3_select_page_conv($this->exc_pages, self::$plugin_id);
		$this->exc_pages->table_view = true;
		$this->exc_pages->table_view_change = true;
		$this->exc_pages->single_view = true;
		
		$this->hide_from->name = __('Hide from user', self::$plugin_id);
		$this->hide_from->col_name = 'hide_from_admin';
		$this->hide_from->col_type = 'TEXT CHARACTER SET utf8 COLLATE utf8_general_ci';
		$this->hide_from->input = new q2w3_checkbox_multi_input(array('name'=>$this->input_name($this->hide_from->col_name), 'value_to_select'=>q2w3_table_func::wp_user_roles()));
		$this->hide_from->help = __('Code will be hidden for all users with the selected roles. Visitor - is a virtual user role for all not registered visitors of your site. Use example: if you select Visitor role only, code will be shown only for registered users.', self::$plugin_id);
		$this->hide_from->conv = new q2w3_checkbox_multi_conv('', self::$plugin_id, q2w3_table_func::wp_user_roles());
		$this->hide_from->table_view = true;
		$this->hide_from->table_view_change = true;
		$this->hide_from->single_view = true;

		$this->code_align->name = __('Align', self::$plugin_id);
		$this->code_align->col_name = 'code_align';
		$this->code_align->col_type = 'INT';
		$this->code_align->input_values = array(''=>__('Align Not Set', self::$plugin_id), self::ALIGN_LEFT=>__('Align Left', self::$plugin_id), self::ALIGN_CENTER=>__('Align Center', self::$plugin_id), self::ALIGN_RIGHT=>__('Align Right', self::$plugin_id));
		$this->code_align->input = new q2w3_select_input(array('name'=>$this->input_name($this->code_align->col_name), 'value_to_select'=>$this->code_align->input_values, 'class'=>'select_input'));
		$this->code_align->conv = new q2w3_text_conv($this->code_align, self::$plugin_id);
		$this->code_align->table_view = false;
		$this->code_align->table_view_change = false;
		$this->code_align->single_view = false;
		
		$this->code->name = __('Include code', self::$plugin_id);
		$this->code->col_name = 'code';
		$this->code->col_type = 'TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL';
		$this->code->input = new q2w3_textarea_input(array('name'=>$this->input_name($this->code->col_name), 'class'=>'textarea_input', 'id'=>'inc_code'));
		$this->code->help = __('Included code. Required field. Any type of HTML, CSS, JAVASCRIPT and PHP code. PHP code must begins with php open tag. Align option allows you to wrap code in a div with selected text-align css propertie. Useful for aligning Google AdSense blocks and so on', self::$plugin_id);
		$this->code->table_view = false;
		$this->code->table_view_change = false;
		$this->code->single_view = true;
		
	}

	/**
 	 * Here user input is checked 
 	 * 
 	 */
	protected function check_input_data() {
	
		if (!$this->title->val) {
		
			new q2w3_table_sys_msg(__('You must input Description field!', self::$plugin_id));
			
			return false;
		
		} elseif (!intval($this->priority->val)) {
		
			new q2w3_table_sys_msg(__('Priority number must be integer!', self::$plugin_id));
			
			return false;
				
		} elseif (!$this->inc_pages->val) {
		
			new q2w3_table_sys_msg(__('You must select at least one page where to place included code!', self::$plugin_id));
			
			return false;
				
		} elseif (!$this->code->val) {
		
			new q2w3_table_sys_msg(__('You must enter included code!', self::$plugin_id));
			
			return false;
			
		}
		
		return true;
	
	}
	
	/**
 	 * Returns array of special page selectors 
 	 * 
 	 */
	public static function page_selectors($plugin_id = false) {
		
		if (!$plugin_id) $plugin_id = self::$plugin_id;
		
		return array(
			self::ALL => __('All', $plugin_id), 
			self::FRONT_PAGE => __('Front page', $plugin_id), 
			self::POST_TYPES_PAGES => __('All Post Types pages', $plugin_id),
			self::TAX_PAGES => __('Taxonomies pages', $plugin_id),
			self::POST_TYPE_ARCHIVE_PAGES => __('Post Type Archive pages', $plugin_id), 
			self::DATE_PAGES => __('Date pages', $plugin_id),
			self::AUTHOR_PAGES => __('Author pages', $plugin_id), 
			self::ATTACHMENT_PAGES => __('Attachment pages', $plugin_id), 
			self::PAGED_PAGES => __('Paged pages', $plugin_id), 
			self::SEARCH_PAGE => __('Search page', $plugin_id), 
			self::PREVIEW_PAGE => __('Preview page', $plugin_id), 
			self::PAGE_404 => __('Page 404', $plugin_id)
			//self::FEED => __('Feed', $plugin_id)
		);
				
	}
	
	/**
 	 * Changes status of the include 
 	 * 
 	 */
	public function change_status($status) {
	
		global $wpdb;
		
		if ($this->id->col_name && $this->id->val && $this->status->col_name && $status) {
			
			if ($wpdb->update($this->table(), array($this->status->col_name=>$status), array($this->id->col_name=>$this->id->val)) !== false) {
								
				new q2w3_table_sys_msg(__('Status changed', self::$plugin_id));
				
			}
			
		}
			
	}
	
	/**
 	 * Returns javascript needed for include edit form 
 	 * 
 	 */
	public function js() {
		
		$this->widget_title->input->value = $this->widget_title->val;
		
		$this->code_align->input->value = $this->code_align->val;
		
		$zcbpath = q2w3_table_func::folder_url() .'_js/zeroclipboard/';
		
		$zcbptext = __('Copy to clipboard', self::$plugin_id);
		
		$manual_inc_code = '&lt;?php if ( method_exists(\'q2w3_inc_manager\', \'manual_inc\') ) q2w3_inc_manager::manual_inc('. $_GET['id'] .'); ?&gt;';
		
		$shortcode_inc_code = '[include id=&quot;'. $_GET['id'] .'&quot; title=&quot;" + get_inc_title() + "&quot;]';
		
		$js = '<script src="'. $zcbpath .'ZeroClipboard.js"></script>
<script type="text/javascript">

jQuery("#select_location").parent("td").append(\'<span id="manual_include_code"></span>\');
jQuery("#inc_code").parent("td").prepend(\'<span id="code_align"></span>\');
jQuery("#select_location").change(function() {
	var location = jQuery(this).attr("value");
	if (location == '. self::LOC_WIDGET .' || location == '. self::LOC_MANUAL .') {
		jQuery(this).parent("td").parent("tr").next("tr").hide();	
	} else {
		jQuery(this).parent("td").parent("tr").next("tr").show();
	}	
	if (location == '. self::LOC_MANUAL .') {
		jQuery("#manual_include_code").text("");
		jQuery("#manual_include_code").append("<span id=\"text_to_copy\">'. $manual_inc_code .'</span> <input data-clipboard-text=\"'. $manual_inc_code .'\" type=\"button\" value=\"'. $zcbptext .'\" id=\"copy-button-manual\" class=\"button-secondary\" />");
		var clip = new ZeroClipboard( document.getElementById("copy-button-manual"), {
  			moviePath: "'. $zcbpath .'ZeroClipboard.swf"
		} );
	} else if (location == '. self::LOC_WIDGET .') {
		jQuery("#manual_include_code").text("");
		jQuery("#manual_include_code").append(\''. $this->widget_title->name .': '. trim($this->widget_title->input) .'\');
	} else if (location == '. self::LOC_SHORTCODE .') {
		jQuery("#manual_include_code").text("");
		jQuery("#manual_include_code").append("<span id=\"text_to_copy\">'. $shortcode_inc_code .'</span> <input data-clipboard-text=\"'. $shortcode_inc_code .'\" type=\"button\" value=\"'. $zcbptext .'\" id=\"copy-button-shortcode\" class=\"button-secondary\" />");
		var clip = new ZeroClipboard( document.getElementById("copy-button-shortcode"), {
  			moviePath: "'. $zcbpath .'ZeroClipboard.swf"
		} );
	} else {
		jQuery("#manual_include_code").text("");
	}
	if (location != '. self::LOC_HEADER .') {
		jQuery("#code_align").text("");
		jQuery("#code_align").append(\''. str_replace(PHP_EOL,'',$this->code_align->input) .'\');
	} else {
		jQuery("#code_align").text("");
	}
}).trigger("change");

function get_inc_title() {
	return jQuery("input[name=\'propertie[description]\']").attr("value");
}

function htmlDecode(value){ 
 	return jQuery(\'<div/>\').html(value).text(); 
}
</script>';

		return $js;

	}
			
}

?>