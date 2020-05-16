<?php

/**
 * @author Max Bond
 * 
 * Table search by row title filter class
 *
 */
class q2w3_table_search_title_filter extends _q2w3_table_filter {
	
	protected $obj;
	
	protected $search_str;
	
	const VAR_NAME = 's';

	
	public function __construct($plugin_id, $obj) {
		
		if ($obj instanceof _q2w3_table_obj) $this->obj = $obj;
		
		if (key_exists(self::VAR_NAME, $_GET)) $this->search_str = strip_tags(trim(urldecode($_GET[self::VAR_NAME])));
		
		parent::__construct($plugin_id);
						
	}
	
	public function controls() {
		
		$res = '<form method="get" action="'. $_SERVER['PHP_SELF'] . '" id="q2w3_table_search_form" style="float: right;">'.PHP_EOL;
		
		$res .= new q2w3_hidden_input(array('name'=>'page', 'value'=>$_GET['page']));
		
		if (key_exists(q2w3_table_status_filter::VAR_NAME, $_GET)) $res .= new q2w3_hidden_input(array('name'=>q2w3_table_status_filter::VAR_NAME, 'value'=>$_GET[q2w3_table_status_filter::VAR_NAME]));

		if (key_exists(q2w3_table_location_filter::VAR_NAME, $_GET)) $res .= new q2w3_hidden_input(array('name'=>q2w3_table_location_filter::VAR_NAME, 'value'=>$_GET[q2w3_table_location_filter::VAR_NAME]));
		
		$res .= '<div class="search-box">'.PHP_EOL;
			
		$res .= '<input type="text" id="post-search-input" name="'. self::VAR_NAME .'" value="'. $this->search_str .'">'.PHP_EOL;
			
		$res .= '<input type="submit" name="" id="search-submit" class="button" value="'. __('Search') .'">'.PHP_EOL;
		
		$res .= '</div>'.PHP_EOL;
		
		$res .= '</form>'.PHP_EOL;
			
		return $res;
		
	}
	
	public function sql() {
		
		if (!empty($this->search_str)) {
		
			return $this->obj->title->col_name." LIKE '%". $this->search_str ."%'";
		
		} 
		
		return false;
		
	}	
	
}

?>