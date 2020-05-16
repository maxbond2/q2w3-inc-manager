<?php

/**
 * @author max Bond
 * 
 * <input type="password"/> element
 *
 */
class q2w3_password_input extends _q2w3_input {
	
	
	
	public function html() {
				
		return '<input type="password" name="'. $this->name .'" value="'. $this->value .'"'. $this->class.$this->id.$this->maxlength.$this->style.$this->js .'/>'.PHP_EOL;
		
	}
	
}

?>