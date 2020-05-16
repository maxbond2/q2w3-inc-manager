<?php

/**
 * @author Max Bond
 * 
 * <input type="hidden"/> element
 *
 */
class q2w3_hidden_input extends _q2w3_input {
	
	
	
	public function html() {
		
		if ($this->value) {
			
			return '<input type="hidden" name="'. $this->name .'" value="'. $this->value .'"'. $this->class.$this->id .'/>'.PHP_EOL;
		
		} else {
				
			return false;
		
		}
	
	}
		
}

?>