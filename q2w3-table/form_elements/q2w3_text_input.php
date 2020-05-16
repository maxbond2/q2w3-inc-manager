<?php

/**
 * @author Max Bond
 * 
 * <input type="text"/> element
 *
 */
class q2w3_text_input extends _q2w3_input {
	
	
	
	public function html() {
		
		return '<input type="text" name="'. $this->name .'" value="'. $this->value .'"'. $this->class.$this->id.$this->maxlength.$this->style.$this->js .'/>'.PHP_EOL;
		
	}
	
}

?>