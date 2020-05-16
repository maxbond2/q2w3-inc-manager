<?php

/**
 * @author Max Bond
 * 
 * Output text instead of <input type="text"/> element
 *
 */
class q2w3_text_wo_input extends _q2w3_input {
	
	
	
	public function html() {
		
		return '<span '. $this->class.$this->id.$this->style.$this->js .'>'. $this->value .'</span><input type="hidden" name="'. $this->name .'" value="'. $this->value .'"'. $this->class.$this->id .'/>'.PHP_EOL;
		
	}
	
}

?>