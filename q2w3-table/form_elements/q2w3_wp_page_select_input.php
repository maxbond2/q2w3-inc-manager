<?php

class q2w3_wp_page_select_input  extends _q2w3_input {
	
	public $type;
	
	
	
	public function html() {
		
		if ($this->type == 'include') {
			
			$type = '&amp;type=include';
			
			$field_id = 'inc_pages_select'; 
		
		} elseif ($this->type == 'exclude') {
			
			$type = '&amp;type=exclude';
			
			$field_id = 'exc_pages_select';
			
		} else {
			
			$type = false;
			
			$field_id = false;
			
		}

		$saved_values = null;

		$pages = null;
		
		if ($this->value) {
			
			$value = (array) $this->value;
			
			$saved_values = implode(',', $value);
			
			$pages = '';
			
			foreach ($value as $id) {
				
				$pages .= q2w3_select_page_conv::page_title($id).' // ';
				
			}
			
			$pages = substr_replace($pages, '', -4);
		
		}
		
		$res = '<div '. $this->id .'>'.PHP_EOL;
		
		$res .= '<div><a href="'. admin_url( 'admin-ajax.php' ) . '?width=640&amp;height=485&amp;action=q2w3_table_wp_page_select&amp;id='. $field_id . $type .'&amp;wp_nonce='. wp_create_nonce('q2w3_table') .'" title="'. __('Select pages', q2w3_inc_manager::ID) .'" class="thickbox" style="text-decoration: none">[...]</a></div>'.PHP_EOL;
		
		$res .= '<input type="hidden" name="'. $this->name .'" value="'. $saved_values .'"/>';
		
		$res .= '<div class="selected_pages">'. __('Pages', q2w3_inc_manager::ID) .': '. $pages .'</div>'.PHP_EOL;
		
		$res .= '</div>'.PHP_EOL;
		
		return $res;
		
	}
	
	
}

?>