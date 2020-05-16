<?php

/**
 * @author Max Bond
 * 
 * <input type="submit"/> element
 *
 */
class q2w3_submit_input extends _q2w3_input {
	
	
	
	public function html() {
		
		return '<input type="submit" value="'. $this->value .'"'. $this->class.$this->id.$this->style.$this->js .'/>'.PHP_EOL;
		
	}
	
}

?>