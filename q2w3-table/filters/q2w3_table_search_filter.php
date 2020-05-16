<?php

/**
 * @author Max Bond
 * 
 * Table search filter class
 *
 */
class q2w3_table_search_filter extends _q2w3_table_filter {
	
	protected $obj;
	

	
	public function __construct($plugin_id, $obj) {
		
		if ($obj instanceof _q2w3_table_obj) $this->obj = $obj;
		
		session_start();
		
		parent::__construct($plugin_id);
						
	}
	
	public function controls() {
		
		$res = '<div id="poststuff" class="metabox-holder">'.PHP_EOL;
			
		$res .= '<div id="q2w3_table_search" class="postbox" style="width: 100%; margin-bottom: 0px;">'.PHP_EOL;
			
		$res .= '<h3 class="hndle" style="cursor: normal;"><span>Поиск</span></h3>'.PHP_EOL;
			
		if (!empty($_SESSION['search'])) $show_form = 'style="display: block"'; else $show_form = 'style="display: none"';
		
		$res .= '<div class="inside" '. $show_form .'>'.PHP_EOL;
		
		$res .= '<form method="post" action="'. q2w3_table_func::folder_url().'/q2w3_post.php' . '" id="q2w3_table_search_form">'.PHP_EOL;
		
		$search = new q2w3_table_search($this->plugin_id);
		
		$res .= $search->html();
		
		$res .= new q2w3_hidden_input(array('name'=>'wp_nonce', 'value'=>wp_create_nonce('q2w3_table')));
		
		$res .= '<table>'.PHP_EOL;
		
		foreach ($this->obj as $pname=>$propertie) {
			
			if ($propertie->search == true) {
				
				$propertie->input->value = $_SESSION['search'][$propertie->col_name];
				
				$res .= '<tr>'.PHP_EOL;
				
				$res .= '<td>'. $propertie->name .'</td>'.PHP_EOL;
				
				$res .= '<td>'. $propertie->input .'</td>'.PHP_EOL;
				
				$res .= '</tr>'.PHP_EOL;
				
			}
			
		}
		
		$res .= '<tr>'.PHP_EOL;
				
		$res .= '<td><input type="button" value="Сброс" class="button-secondary"/></td>'.PHP_EOL;
				
		$res .= '<td><input type="submit" value="Поиск" class="button-primary"/></td>'.PHP_EOL;
		
		$res .= '</tr>'.PHP_EOL;
		
		$res .= '</table>'.PHP_EOL;
		
		$res .= '</form>'.PHP_EOL;
		
		$res .= '</div>'.PHP_EOL.'</div>'.PHP_EOL.'</div>'.PHP_EOL; //'</div>'.PHP_EOL;

		$res .= '<script type="text/javascript">jQuery("#q2w3_table_search .hndle").click(function(){jQuery("#q2w3_table_search .inside").toggle()});';
		
		$res .= 'jQuery("#q2w3_table_search_form .button-secondary").click(function(){jQuery(":input","#q2w3_table_search_form").not(":button, :submit, :reset, :hidden").val("").removeAttr("checked").removeAttr("selected");jQuery("#q2w3_table_search_form").submit();});';
		
		$res .= '</script>';
			
		return $res;
		
	}
	
	public function sql() {
		
		if (!empty($_SESSION['search'])) {
		
			return $this->create_query($_SESSION['search']);
		
		} 
		
		return false;
		
	}
	
	protected function create_query($sarray) {
		
		foreach ($sarray as $scol=>$svalue) {
			
			if (is_array($svalue)) {
			
				foreach ($this->obj as $pname=>$propertie) {
					
					if ($propertie->col_name == $scol) {
						
						$svalue = $propertie->conv->php2db_search($svalue);
						
					}
					
				}
				
				$search[] = "$scol LIKE '$svalue'";
				
			} else {
			
				$search[] = "$scol = '$svalue'";
			
			}
			
		}
		
		return implode(' AND ', $search);
		
	}
	
	
}

?>