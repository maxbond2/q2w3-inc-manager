<?php

/**
 * @author Max Bond
 * 
 * <input type="button"/> element
 *
 */
class q2w3_button_input extends _q2w3_input {
	
	
	
	public function html() {
		
		return '<input type="button" value="'. $this->value .'"'. $this->class.$this->id.$this->style.$this->js .'/>'.PHP_EOL;
		
	}
	
}

?>