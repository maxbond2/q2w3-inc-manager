<?php

/**
 * @author Max Bond
 * 
 * Table pages support. WP style
 * 
 */
class q2w3_table_page_filter {
	
	protected $plugin_id;
	
	protected $cur_page;
	
	const VAR_NAME = 'cur_page';
		

	
	public function __construct($plugin_id) {
		
		$this->plugin_id = $plugin_id;
		
	}
	
	/**
	 * @return number Current page number
	 */
	public function cur_page() {
		
		if (!$this->cur_page) {
		
			if (key_exists(self::VAR_NAME, $_GET) && intval($_GET[self::VAR_NAME])) $this->cur_page = $_GET[self::VAR_NAME]; else $this->cur_page = 1;
		
		}
		
		return $this->cur_page;
		
	}
	
	/**
	 * @param integer $total_rows Table total rows
	 * @param integer $rows_per_page Table rows per page value
	 * @return string Buttons html
	 */
	public function controls($total_rows, $rows_per_page) {
		
		$total_pages = ceil($total_rows / $rows_per_page); // Count total pages

		if ($total_pages <= 1) return false; // No buttons if there is only one page
		
		$cur_page = $this->cur_page();
		
		if ($cur_page > 1) $prev_page = $cur_page - 1; else $prev_page = false; // Previos page number

		if ($cur_page < $total_pages) $next_page = $cur_page + 1; else $next_page = false; // Next page number
						
		$res = '<div class="tablenav-pages">';
		
		$page_begin = $cur_page*$rows_per_page - $rows_per_page + 1; // Row number of fist row on a page
		
		$page_end = $cur_page*$rows_per_page; // Row number of last row on a page
		
		if ($page_end > $total_rows) $page_end = $total_rows; // Corrects $page_end value for last page
		
		$res .= '<span class="displaying-num">'. $page_begin .'&#8211;'. $page_end .' '. __('from', $this->plugin_id) .' '. $total_rows .'</span>';
		
		if ($prev_page) { // left arrow button
			
			$res .= '<a class="prev page-numbers" href="?'. q2w3_table_func::change_qstring(self::VAR_NAME, $prev_page) .'">&laquo;</a> ';
		
		}
		
		$printed = false;
		
		$printed2 = false;
				
		for ($page = 1; $page <= $total_pages; $page++) { // page cycle
					
			if ($page == 1) { // first page
				
				$res .= $this->page_button($page, $cur_page, self::VAR_NAME);
				
			} elseif ($cur_page - $page >= 3 && !$printed) { // hide unnecessary pages with dots
				
				$res .= '<span class="page-numbers dots">...</span> ';
				
				$printed = true;
				
			}
			
			if (abs($cur_page - $page) <= 2 && $page != $total_pages && $page != 1) { // display two nearest pages
			
				$res .= $this->page_button($page, $cur_page, self::VAR_NAME);
							
			}
			
			if ($page == $total_pages) { // last page
				
				$res .= $this->page_button($page, $cur_page, self::VAR_NAME);
				
			} elseif ($cur_page - $page <= -3 && !$printed2) { // hide unnecessary pages with dots
					
				$res .= '<span class="page-numbers dots">...</span> ';
				
				$printed2 = true;
				
			}	
					
		}
		
		if ($next_page) { // right arrow button
			
			$res .= '<a class="next page-numbers" href="?'. q2w3_table_func::change_qstring(self::VAR_NAME, $next_page) .'">&raquo;</a> ';

		}
		
		$res .= '</div>';
				
		return $res;
				
	}
	
	/**
	 * @param integer $page Current page number in a cycle
	 * @param integer $cur_page Current page selected by user
	 * @param string $var_name $_GET variable name
	 * @return string Button html
	 */
	protected function page_button($page, $cur_page, $var_name) {
			
		if ($page == $cur_page) {
				
			$res = '<span class="page-numbers current">'. $page .'</span> ';
				
		} else {
				
			$res = '<a class="page-numbers" href="?'. q2w3_table_func::change_qstring($var_name, $page) .'">'. $page .'</a> ';
						
		}
			
		return $res;
			
	}
		
}

?>