<?php

/**
 * @author Max Bond
 * 
 * <select> element
 *
 */
class q2w3_select_input extends _q2w3_input {
	
	
	
	public function html() {
		
		if (!(is_array($this->value_to_select) && !empty($this->value_to_select))) return false; // do nothing if there are no select items
		
		$string = '<select name="'. $this->name .'"'. $this->class.$this->id.$this->multiple.$this->style.$this->js .'>'.PHP_EOL;
				
		foreach ($this->value_to_select as $key=>$name) {
		
			if (is_array($name) && !empty($name)) {
			
				$string .= '<optgroup label="'. $key .'">'.PHP_EOL;
				
				foreach ($name as $opt_key=>$opt_name) {
				
					$string .= $this->make_option($opt_key, $opt_name);
				
				}
				
				$string .= '</optgroup>'.PHP_EOL;
			
			} else {
		
				$string .= $this->make_option($key, $name);
			
			}
			
		}
		
		$string .= '</select>'.PHP_EOL;
		
		return $string;
		
	}
	
	protected function make_option($key, $name) {
	
		if (is_array($this->value)) { // for multiple select
		
			if (in_array($key, $this->value)) $selected = 'selected="selected"'; else $selected = false; 
		
		} elseif ($this->value == $key) { // for single select
		
			$selected = 'selected="selected"';
		
		} else {
		
			$selected = false;
		
		}
						
		return "	<option value=\"$key\" $selected>$name</option>".PHP_EOL;
	
	}
	
}

?>