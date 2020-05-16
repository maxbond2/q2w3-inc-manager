<?php

/**
 * @author Max Bond
 * 
 * <textarea> element
 *
 */
class q2w3_textarea_input extends _q2w3_input {
	
	
	
	public function html() {
		
		return '<textarea name="'. $this->name .'"'. $this->class.$this->id.$this->style.$this->js .'>'. $this->value .'</textarea>'.PHP_EOL;;
		
	}
	
}

?>