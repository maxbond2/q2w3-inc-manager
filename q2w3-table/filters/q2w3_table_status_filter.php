<?php

/**
 * @author Max Bond
 * 
 * Table activity filter class
 *
 */
class q2w3_table_status_filter extends _q2w3_table_filter {
	
	protected $inc_obj;
	
	protected $filters;
	
	const VAR_NAME = 'status';
		

	
	public function controls() {
		
		global $wpdb;
		
		$inc_obj = new q2w3_include_obj($this->plugin_id);
		
		// count number of sub filters
		
		$count_filters = $wpdb->get_results('SELECT count(DISTINCT '. $inc_obj->status->col_name .') FROM '. $inc_obj->table(), ARRAY_N); 
		
		if ($count_filters[0][0]) $count_filters = $count_filters[0][0]; else  return false; // if no data to be filtered return false
		
		// Add additional 'Select All' sub filter
				
		$filters['all'] = __('All', $this->plugin_id);
		
		$filters += $inc_obj->status->input_values;
		
		$count_filters++;
		
		$i = 0;
		
		$res = '';
		
		if (key_exists(self::VAR_NAME, $_GET)) $cur_filter = $_GET[self::VAR_NAME]; else $cur_filter = false;
		
		foreach ($filters as $filter_key=>$filter_name) { // sub filter cycle
		
			$condition = 'WHERE '. $inc_obj->status->col_name .' = '. $filter_key;
			
			if ($filter_key == 'all') $condition = false;
			
			// total records for sub filter
			
			$count = $wpdb->get_results('SELECT count(*) FROM '. $inc_obj->table() .' '.$condition, ARRAY_N);
		
			if ($count[0][0] > 0) {
				
				$i++;
				
				if ($i < $count_filters) $separator = '|'; else $separator = '';
												
				$link = q2w3_table_func::change_qstring(self::VAR_NAME, $filter_key); // change filter id
				
				$link = $_SERVER['PHP_SELF'].'?'.q2w3_table_func::change_qstring(q2w3_table_page_filter::VAR_NAME, '', $link); // reset page to number 1
				
				if ($cur_filter == $filter_key || (!$cur_filter && $filter_key == 'all')) $selected = ' class="current"'; else $selected = false;
				
				$res .= '<div style="float: left"><a href="'. $link .'"'. $selected .'>'. $filter_name .' <span class="count">('. $count[0][0] .')</span></a>'. $separator .'</div>'.PHP_EOL;
			
			}
			
		}
		
		if ($res) {
			
			$ul = '<div class="subsubsub">'.PHP_EOL;
			
			$ul .= $res;
			
			$ul .= '</div>'.PHP_EOL;
			
			$ul .= '<div style="clear: left;"></div>'.PHP_EOL;
			
			$this->inc_obj = $inc_obj;
			
			$this->filters = $filters;
			
		}
		
		return $ul;
		
	}
	
	public function sql() {
		
		if (key_exists(self::VAR_NAME, $_GET)) $cur_filter = $_GET[self::VAR_NAME]; else $cur_filter = false;
		
		if (!$cur_filter || $cur_filter == 'all') return false;
		
		if (!key_exists($cur_filter, $this->filters)) return false;
		
		return $this->inc_obj->status->col_name .' = '.$cur_filter;
		
	}
	
	
}

?>