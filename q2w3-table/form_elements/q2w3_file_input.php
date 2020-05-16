<?php

/**
 * @author Max Bond
 * 
 * <input type="file"/> element
 *
 */
class q2w3_file_input extends _q2w3_input {
	
	
	
	public function html() {
		
		return '<input type="file" name="'. $this->name .'"'. $this->class.$this->id.$this->style.$this->js .'/>'.PHP_EOL;
		
	}
	
}

?>