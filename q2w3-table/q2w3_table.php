<?php

/**
 * @author Max Bond
 * 
 * Main Q2W3 Table class
 *
 */
class q2w3_table {
	
	protected $plugin_id;
	
	protected $object;
	
	protected $wpdb;
		
	
	protected $user_options_var;
	
	
	public $post_handler;
	
	public $get_handler;
	

	public $enable_user_settings = true;
	
	public $enable_bulk_actions = true;
	
	public $enable_create_new_button = true;
	
	public $enable_create_new_button_text;
	
	public $enable_page_filter = true;
	
	public $enable_row_actions = true;
	
	
	protected $filters;
	
	protected $bulk_actions;
	
	protected $buttons;
	
	protected $page_filter;
	
	protected $row_actions;
	
	
	protected $order_by;
	
	protected $group_by;
	
	protected $total_rows;
	
	protected $rows_per_page = 20;
			
	protected $selected_columns;
	
	protected $last_record;
		
	
	const NEW_MARKER = '_new_';
	
	
	
	public function __construct($plugin_id, $object) {
	
		if ($plugin_id) $this->plugin_id = $plugin_id; else return false;
		
		if ($object instanceof _q2w3_table_obj) $this->object = $object; else return false;
		
		if ($GLOBALS['wpdb']) $this->wpdb = $GLOBALS['wpdb']; else return false;
		
		$this->user_options_var = q2w3_table_func::safe_plugin_id($this->plugin_id) .'_table_settings';
			
		$this->user_settings_load();	
		
		$this->create_new_button_text = __('Add New', $this->plugin_id);
				
	}
	
	/**
	 * Loads saved user settings
	 *   
	 */
	public function user_settings_load() {
		
		if (!$this->enable_user_settings) return false;
				
		$settings = unserialize(get_user_option($this->user_options_var)); // get options from db
			
		if (!empty($settings)) {
				
			$this->rows_per_page = $settings['settings']['rows_per_page'];

			$this->selected_columns = $settings['columns'];
				
			foreach ($this->object as $pname=>$propertie) { // apply selected columns
				
				if (key_exists($propertie->col_name, $this->selected_columns)) {
				
					$this->object->$pname->table_view = $this->selected_columns[$propertie->col_name];
				
				} elseif ($this->object->$pname->table_view_change){
					
					$this->object->$pname->table_view = false;
					
				}
				
				$this->object->title->table_view = true; // title is always in table view
				
			}
				
		} else { // no options // load default options
				
			$default_columns = $this->get_columns_names();
				
			if (is_array($default_columns) && !empty($default_columns)) {
				
				foreach ($default_columns as $column) {
				
					$this->selected_columns[$column] = 1;
				
				}
				
			}
				
		}
				
	}
	
	/**
	 * Returns user settings form
	 * 
	 * This function uses special technic wich allows creation of custom screen options forms
	 * Bind it to WP 'set-screen-option' hook
	 * 
	 * @return array 
	 */
	public function user_settings_form() {
		
		if (!$this->enable_user_settings || (key_exists('deactivate', $_GET) && $_GET['deactivate'])) return false;
			
		$hack = '</label><script type="text/javascript">jQuery("label[for=\'screen_options_hack-hide\']").hide()</script>'.PHP_EOL;
	
		foreach ($this->object as $pname=>$propertie) {
				
			if ($propertie->table_view_change !== false) {
		
				if (key_exists($propertie->col_name, $this->selected_columns)) $value = $this->selected_columns[$propertie->col_name]; else $value = false;
				
				$hack .= '<div style="float: left; margin-left: 9px">';
				
				$hack .= new q2w3_checkbox_input(array('name'=>"wp_screen_options[value][columns][$propertie->col_name]", 'value'=>$value, 'value_to_select'=>1)) .' '. $propertie->name;

				$hack .= '</div>';
		
			}
	
		}
		
		$hack .= '<div style="clear: left; font-size: 0px">&nbsp;</div>'.PHP_EOL;
	
		$hack .= '<h5>'. __('Settings') .'</h5><div class="screen-options">';
		
		$hack .= __('Rows per page:', $this->plugin_id).' <input type="text" class="screen-per-page" name="wp_screen_options[value][settings][rows_per_page]" maxlength="3" value="'. $this->rows_per_page .'" />'.PHP_EOL;
		
		$hack .= '<input type="hidden" name="wp_screen_options[option]" value="'. $this->user_options_var .'" /><input type="submit" class="button" value="'. __('Apply', $this->plugin_id) .'" />';
		
		$hack .= '</div>';
	
		return array('screen_options_hack'=>$hack);
			
	}
	
	/**
	 * Prepares user setting input befor save
	 * 
	 * Bind this function to WP 'set-screen-option' hook
	 * 
	 * @param array $new_settings
	 * @return string Serialized array
	 */
	public function user_settings_save($new_settings) {
		
		if ( !is_array($new_settings) ) return $new_settings;
		
		$old_settings = unserialize(get_user_option($this->user_options_var));
		
		if ($_GET[q2w3_table_page_filter::VAR_NAME] && $new_settings['settings']['rows_per_page'] != $old_settings['settings']['rows_per_page']) q2w3_table_func::change_referer(q2w3_table_page_filter::VAR_NAME); // if number of rows per page changed switch to first page
		
		$new_settings['settings']['rows_per_page'] = intval($new_settings['settings']['rows_per_page']);
		
		if ($new_settings['settings']['rows_per_page'] < 1 || $new_settings['settings']['rows_per_page'] > 999) $new_settings['settings']['rows_per_page'] = $this->rows_per_page;
		
		return serialize($new_settings);
		
	}
		
	/**
	 * Registers new filter object
	 * 
	 * @param $object - instance of _q2w3_table_filter
	 */
	public function reg_filter($object) {
		
		if ($object instanceof _q2w3_table_filter) $this->filters[] = $object;
		
	}
	
	
	/**
	 * Returns table filter html
	 * 
	 * @return string
	 */
	protected function table_filters() {
		
		if (is_array($this->filters)) {
			
			$res = '';
			
			foreach ($this->filters as $filter) {
				
				$res .= $filter->controls();
				
			}
			
			return $res;
		
		}
		
		return false;
		
	}
	
	/**
	 * Registers new button
	 *  
	 * @param string $name - Button name
	 */
	public function reg_button($name) {
	
		$this->buttons[] = $name;
	
	}
	
	/**
	 * Registers new bulk action object
	 * 
	 * @param $object - instance of _q2w3_table_action
	 */
	public function reg_bulk_action($object) {
	
		if ($object instanceof _q2w3_table_action) $this->bulk_actions[] = $object;
	
	}
	
	/**
	 * Returns bulk actions, buttons and page controls html
	 * 
	 * @return string
	 */
	protected function table_actions() {
	
		if (!$this->enable_bulk_actions && !$this->enable_create_new_button) return false;
	
		if ($this->enable_bulk_actions) {
	
			$this->bulk_actions[] = new q2w3_table_delete_selected($this->plugin_id);
	
			foreach ($this->bulk_actions as $bulk_action) {
				
				list($action, $action_name) = $bulk_action->html();

				$actions[$action] = $action_name;
				
			}

			$actions = array_merge(array(''=>__('Bulk Actions', $this->plugin_id)), $actions);
	
			$select_action = new q2w3_select_input(array('name'=>'action', 'value_to_select'=>$actions));
			
			$select_action .= '<input type="submit" value="'. __('Apply', $this->plugin_id) .'" class="button-secondary do_action" />';
							
		}
		
		if ($this->enable_create_new_button) {
			
			$create_new_button = ''; //'<a href="#" class="button ">'. $this->create_new_button_text .'</a>';
		
		}

		$object_name = new q2w3_hidden_input(array('name'=>'object', 'value'=>get_class($this->object)));
		
		if ($this->last_record) $last_record = new q2w3_hidden_input(array('name'=>'last_record', 'value'=>1)); else $last_record = false;
		
		$res = '<div class="tablenav"><div class="alignleft actions">'. PHP_EOL.$object_name.$last_record.PHP_EOL.$select_action.PHP_EOL.$create_new_button.PHP_EOL.'</div>';
		
		if ($this->enable_page_filter) {
		
			$res .= $this->page_filter->controls($this->total_rows, $this->rows_per_page);
		
		}
		
		$res .= '</div>'.PHP_EOL;

		return $res;
	
	}
	
	/**
	 * Registers new row action object
	 * 
	 * @param $object - instance of _q2w3_table_action
	 */
	public function reg_row_action($object) {
	
		if ($object instanceof _q2w3_table_action) $this->row_actions[] = $object;
	
	}
	
	/**
	 * Defines table sort order
	 * 
	 * @param array $columns Associative array where keys are db table column names and values are order direction (ASC or DESC)
	 */
	public function set_order($columns) {
	
		if (is_array($columns) && !empty($columns)) {
			
			foreach ($columns as $column=>$direction) {
				
				if ($direction == 'ASC' || $direction == 'asc' || $direction == 'DESC' || $direction == 'desc') {
				
					$array[] = $column .' '. $direction;
				
				}
				
			}

			$this->order_by = implode(',', $array);
			
		}
		
	}
	
	/**
	 * Defines table grouping
	 * 
	 * @param array $columns Simple array of db table column names
	 */
	public function set_group($columns) {
	
		if (is_array($columns) && !empty($columns)) {
			
			$this->group_by = implode(',', $this->group_by);
			
		}
	
	}
	
	/**
	 * Returns final table html
	 * 
	 * @return string
	 */
	public function html() {

		$res = q2w3_table_sys_msg::display();
		
		$res .= $this->row_edit();
		
		$res .= $this->table_filters();

		$table_rows = $this->table_rows();

		$table_actions = $this->table_actions();
		
		$table_header = $this->table_header();
		
		$res .= '<form method="post" action="'. $this->post_handler .'" id="q2w3_includes_table_form">'.PHP_EOL;

		$res .= new q2w3_hidden_input(array('name'=>'wp_nonce', 'value'=>wp_create_nonce('q2w3_table')));
				
		$res .= $table_actions;
		
		$res .= '<table class="wp-list-table widefat fixed striped posts" id="q2w3_includes_table">'.PHP_EOL;
		
		$res .= '<thead>'.PHP_EOL;
		
		$res .= $table_header;
		
		$res .= '</thead>'.PHP_EOL;

		$res .= '<tfoot>'.PHP_EOL;

		$res .= $table_header;
	  
		$res .= '</tfoot>'.PHP_EOL;
	  
		$res .= '<tbody>'.PHP_EOL;
		
		$res .= $table_rows;
	  				
		$res .= '</tbody>'.PHP_EOL;
	  
		$res .= '</table>'.PHP_EOL;
	  
		$res .= $table_actions;

		//$res .= '</div>'.PHP_EOL;
		
		$res .= '</form>'.PHP_EOL;
		
		$res .= $this->js_code();
					
		return $res;
	
	}	
	
	/**
	 * @return array of column names
	 */
	protected function get_columns() {
	
		if (!is_object($this->object)) return false;
		
		foreach ($this->object as $propertie=>$definition) {
		
			if ($definition->table_view) $columns[] = $definition->name;
		
		}
		
		return $columns;
	
	}
	
	/**
	 * @return array of database column names
	 */
	protected function get_columns_names() {
	
		if (!is_object($this->object)) return false;
		
		foreach ($this->object as $pname=>$propertie) {
		
			if ($propertie->table_view) $columns[] = $propertie->col_name;
		
		}
						
		return $columns;
	
	}
			
	/**
	 * Returns table header html
	 * 
	 * @return string 
	 */
	protected function table_header() {
	
		$columns = $this->get_columns();
	
		$res = '<tr>'.PHP_EOL;
		
		if ($this->enable_bulk_actions) $res .= '<td scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></td>'.PHP_EOL;
		
		foreach ($columns as $id=>$column) {
		
			if ($id == 0) $class = ' column-title'; else $class = false;	
				
			$res .= '<th scope="col" class="manage-column'. $class. '">'. $column .'</th>'.PHP_EOL;
		
		}
		
		$res .= '</tr>'.PHP_EOL;
			
		return $res;
	
	}
	
	/**
	 * Returns table rows html
	 * 
	 * @return string
	 */
	protected function table_rows() {
	
		$columns = $this->get_columns_names();
			
		$col_num = count($columns);
		
		array_unshift($columns, 'id');
		
		$columns = implode(',', $columns);

		$where = false;		
		
		if (is_array($this->filters)) {
			
			foreach ($this->filters as $filter) {
				
				if ($filter_sql = $filter->sql()) $where[] = $filter->sql(); 
				
			}
			
			$where = @implode(' AND ', $where);
			
			if ($where) $where = 'WHERE '.$where;
			
		}
		
		if ($this->group_by) $group_by = 'GROUP BY '. $this->group_by; else $group_by = false;
		
		if ($this->order_by) $order_by = 'ORDER BY '. $this->order_by; else $order_by = false;
		
		$conditions = 'FROM '. $this->object->table() .' '. $where .' '. $group_by .' '. $order_by;
		
		if ($this->enable_page_filter) {

			$this->page_filter = new q2w3_table_page_filter($this->plugin_id);
			
			$limit_start = $this->page_filter->cur_page()*$this->rows_per_page - $this->rows_per_page;
			
			$limit = 'LIMIT '. $limit_start .','. $this->rows_per_page;
			
			$tmp_rows = $this->wpdb->get_results('SELECT count(*) '.$conditions, ARRAY_N);
			
			$this->total_rows = $tmp_rows[0][0];
				
		}
				
		$data = $this->wpdb->get_results('SELECT '. $columns .' '. $conditions .' '. $limit, ARRAY_A); //status, location, priority
				
		if (!empty($data)) {
		
			if (count($data) == 1 && $this->page_filter->cur_page() == ceil($this->total_rows / $this->rows_per_page)) $this->last_record = true;
			
			$i = 0;
			
			$res = '';
			
			foreach ($data as $row) {
							
				$this->object->load_values_from_array($row, 'db2text');
				
				$row_id = $this->object->id->val;
								
				$i++;
				
				if ($i & 1) $class='class="alternate"'; else $class = false;
			
				$res .= '<tr '. $class .'>'.PHP_EOL;
				
				if ($this->enable_bulk_actions) {
				
					$res .= '<th scope="row" class="check-column"><input type="checkbox" name="checked['. $row_id .']" value="" class="q2w3_table_checkbox" /></th>'.PHP_EOL;
				
					$col_num++;
				
				}
				
				$propertie_link = get_option('siteurl').'/wp-admin/admin.php';
				
				foreach ($this->object as $propertie=>$definition) {
			
					if ($definition->table_view) {
					
						if ($propertie == 'title') {
						
							$res .= '<td><a href="'. $propertie_link . '?' . q2w3_table_func::change_qstring('id',$row_id) .'" class="row-title">'. $definition->val .'</a>'. $this->row_actions($this->object, $row) .'</td>'.PHP_EOL;
						
						} else {
						
							$res .= '<td>'. $definition->val .'</td>'.PHP_EOL;
						
						}						
						
					}
		
				}		
		
				$res .= '</tr>'.PHP_EOL;
			
			}
		
		} else {
		
			$res = '<tr><td></td><td colspan="'. $col_num .'">'. __('No data', $this->plugin_id) .'</td></tr>'.PHP_EOL;
	
		}
							
		return $res;
	
	}
	
	/**
	 * Returns row actions html
	 * 
	 * @param object $object 
	 * @param array $row_data
	 * @return string
	 */
	protected function row_actions($object, $row_data) {
	
		$row_actions = $this->row_actions;

		$row_actions[] = new q2w3_table_delete_row($this->plugin_id, $this->get_handler);
		
		$action_num = count($row_actions);

		$i = 0;
		
		$actions = '';
		
		foreach ($row_actions as $row_action) {
				
			$i++;
				
			$actions .= $row_action->html($object, $row_data, $this->last_record);
				
			if ($i < $action_num) $actions .= ' | ';
				
		}
		
		return '<div class="row-actions">'. $actions .'</div>';
	
	}
	
	/**
	 * Returns row edit window html
	 * 
	 * @return string
	 */
	protected function row_edit() {
		
		if (key_exists('id', $_GET)) $id = $_GET['id']; else $id = false;
		
		if (is_numeric($id) || $id == self::NEW_MARKER) {
					
			if ($id == self::NEW_MARKER) {
			
				$title = $this->create_new_button_text; //__('Create New', $this->plugin_id);
				
				$action = new q2w3_table_new_row($this->plugin_id);
								
			} else {
			
				$action = new q2w3_table_update_row($this->plugin_id);

				$this->object->load_values_from_db($id, 'db2php');
				
				$title = $this->object->title->val;
				
			}
			
			$res = '<div id="poststuff" class="metabox-holder">'.PHP_EOL;
			
			//$res .= '<div id="advanced-sortables" class="meta-box-sortables">'.PHP_EOL;
			
			$res .= '<div id="q2w3_inc_manager" class="postbox" style="width: 100%; margin-bottom: 0px;">'.PHP_EOL;
			
			$res .= '<h3 class="hndle" style="cursor: normal;"><span>'. $title .'</span></h3>'.PHP_EOL;
			
			$res .= '<div class="inside">'.PHP_EOL;
						
			$res .= '<form method="post" action="'. $this->post_handler .'">'.PHP_EOL;

			$res .= $action->html();
							
			$res .= new q2w3_hidden_input(array('name'=>'wp_nonce', 'value'=>wp_create_nonce('q2w3_table')));
			
			$res .= new q2w3_hidden_input(array('name'=>'object', 'value'=>get_class($this->object)));
			
			$res .= new q2w3_hidden_input(array('name'=>'propertie[id]', 'value'=>$id));
	
			$res .= '<table class="form-table" id="q2w3_inc_options_table">'.PHP_EOL;
			
			foreach ($this->object as $propertie) {
	
				if ($propertie->single_view) { 
	
					$res .= '<tr valign="top">'.PHP_EOL;
					
					$res .= '<th scope="row">'. $propertie->name .' (<a class="tooltip" rel="'. $propertie->help .'">?</a>)</th>';
					
					$res .= '<td>';
					
					$propertie->input->value = $propertie->val;
	
					$res .= $propertie->input; 
	
					$res .= '</td>'.PHP_EOL;
					
					$res .= '</tr>'.PHP_EOL;
					
				}
			}
			
			$res .= '</table>'.PHP_EOL;
	
			$res .= '<p class="submit"><input type="submit" class="button-primary" value="'. __('Save Changes') .'" /></p>'.PHP_EOL;
			
			$res .= '</form>'.PHP_EOL;
			
			$res .= '</div>'.PHP_EOL.'</div>'.PHP_EOL.'</div>'.PHP_EOL; //'</div>'.PHP_EOL;

			$res .= '<script type="text/javascript">jQuery("#q2w3_inc_manager .hndle").click(function(){jQuery("#q2w3_inc_manager .inside").toggle()})</script>';
			
			$res .= $this->object->js();
						
			return $res;
			
		}
		
	}
	
	/**
	 * Returns table javascript
	 * 
	 * @return string
	 */
	public function js_code() {
		
		$js_code = '<script type="text/javascript">'.PHP_EOL;
		
		$js_code .= 'jQuery("#q2w3_includes_table_form").submit(function() {
	if (jQuery("#q2w3_includes_table .q2w3_table_checkbox:checked").length > 0) {
		var selected_option = jQuery("#q2w3_includes_table_form select[name=\'action\']").attr("value");
		if (selected_option) {
			if (selected_option == "q2w3_table_delete_selected") return confirm("'. __('Delete selected?', $this->plugin_id) .'"); else	return true;
		} else return false;
	} else {
		return false;
	}
});'.PHP_EOL;
		
		$js_code .= 'jQuery("#q2w3_includes_table_form select[name=\'action\']").change(function() {
	jQuery("#q2w3_includes_table_form select[name=\'action\']").attr("value", jQuery(this).attr("value"));
});'.PHP_EOL;
		
		$js_code .= 'jQuery("#q2w3_includes_table_form .new_include").click(function() {
	location.href = "'. get_option('siteurl') .'/wp-admin/admin.php?page='. $_GET['page'] .'&id='. self::NEW_MARKER .'";
});'.PHP_EOL;
		
		$js_code .= 'jQuery("#q2w3_includes_table .manage-column :checkbox").click(function() {
	if (jQuery(this).attr("checked")) {
		jQuery("#q2w3_includes_table .q2w3_table_checkbox").attr("checked",true);
	} else {
		jQuery("#q2w3_includes_table .q2w3_table_checkbox").removeAttr("checked");
	}
});'.PHP_EOL;

		$js_code .= 'jQuery("#q2w3_includes_table .delete_row").click(function() {
	return confirm("'. __('Delete?', $this->plugin_id) .'");
});'.PHP_EOL;
		
		$js_code .= '</script>'.PHP_EOL;
		
		return $js_code;
		
	}
			
}

?>